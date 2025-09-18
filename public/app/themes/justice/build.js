import { copy } from "jsr:@std/fs";
import * as path from "jsr:@std/path";
import { encodeHex } from "jsr:@std/encoding/hex";
import * as esbuild from "https://deno.land/x/esbuild@v0.25.10/mod.js";
import { sassPlugin } from "jsr:@tsukina-7mochi/esbuild-plugin-sass@^0.2.3";
import { globalExternals } from "npm:@fal-works/esbuild-plugin-global-externals@^2.1.2";

/**
 * The complete Triforce, or one or more components of the Triforce.
 * @typedef {Object} StaticAsset
 * @property {string} src - The source directory or file to copy.
 * @property {string} dest - The destination directory or file to copy to.
 */

/** @typedef {import('@fal-works/esbuild-plugin-global-externals').ModuleInfo} ModuleInfo */

/**
 * Build tasks for the project.
 * @typedef {Object} BuildTask
 * @property {string} src - The source directory or file to copy.
 * @property {string} dest - The destination directory or file to copy to.
 * @property {Record<string, ModuleInfo>} [globals] - Global externals for the build.
 */

/**
 * Builder class for managing the build process of a project.
 * It handles static asset copying, clearing the dist directory, and executing build tasks.
 * @class
 * @property {StaticAsset[]} staticAssets - An array of static assets to copy during the build.
 * @property {BuildTask[]} buildTasks - An array of build tasks to execute.
 * @property {Array} results - An array to store the results of the build tasks.
 */
class Builder {
  /** @type {StaticAsset[]} */
  staticAssets = [];
  /** @type {BuildTask[]} */
  buildTasks = [];

  /**
   * constructor description
   * @param  {StaticAsset[]} staticAssets - An array of static assets to copy during the build.
   * @param  {BuildTask[]} buildTasks - An array of build tasks to execute.
   * @returns {void}
   */
  constructor(staticAssets = [], buildTasks = []) {
    /** @private */
    this.staticAssets = staticAssets;
    /** @private */
    this.buildTasks = buildTasks;
  }

  /**
   * Bundles the project by clearing the dist directory, copying static assets, and building files.
   *
   * @returns {Promise<void>}
   * @throws {Error} If an error occurs during the build process.
   */
  async bundle() {
    console.log("Starting build...");

    const results = [];

    // Clear the dist directory
    await this.clearDist();

    // Copy static assets
    await this.copyStaticAssets();

    // Build files
    try {
      for (const buildTask of this.buildTasks) {
        const result = await this.build(buildTask);
        await this.phpManifest(buildTask);
        results.push(result);
      }
    } finally {
      await esbuild.stop();
    }

    return results;
  }

  /**
   * Clears the dist directory by removing all files and directories within it.
   * @returns {Promise<void>}
   * @throws {Deno.errors.NotFound} If the dist directory does not exist.
   * @throws {Deno.errors.PermissionDenied} If the process does not have permission to remove files.
   * @throws {Error} If an error occurs while reading or removing files.
   */
  async clearDist() {
    // Clear the dist directory
    for await (const dirEntry of Deno.readDir("dist")) {
      await Deno.remove(`dist/${dirEntry.name}`, { recursive: true });
    }
  }

  /**
   * Copies static assets from the source directories to the destination directories.
   * @returns {Promise<void>}
   * @throws {Error} If an error occurs while copying static assets.
   */
  async copyStaticAssets() {
    try {
      // Copy static directories
      for (const { src, dest } of this.staticAssets) {
        await copy(src, dest, {
          overwrite: true,
          recursive: true,
        });
      }
    } catch (error) {
      console.error("Error copying images:", error);
    }
  }

  /**
   * Build a single task.
   * @param {BuildTask} buildTask - The build task to execute.
   * @returns {Promise<void>}
   */
  async build({ src, dest, globals }) {
    const plugins = [];
    if (src.endsWith(".scss")) {
      plugins.push(sassPlugin());
    }
    if (globals) {
      plugins.push(globalExternals(globals));
    }

    const result = await esbuild.build({
      entryPoints: [src],
      outfile: dest,
      //   target the browser - what version of JS?

      //   minify: true,
      sourcemap: "external", // TODO make this conditional
      bundle: true,
      platform: "browser",
      format: "iife",
      loader: {
        ".jpg": "file",
        ".gif": "file",
        ".png": "file",
        ".svg": "file",
      },
      plugins,
      assetNames: "[dir]/[name]",
      external: Object.keys(globals || {}),
    });

    // In dest, do we have the string "_.._"?
    const destContent = await Deno.readTextFile(dest);
    if (destContent.includes("./_.._/")) {
      // Replace './_.._/' with '../'
      const updatedContent = destContent.replace(/\.\/_.._\//g, "../");
      await Deno.writeTextFile(dest, updatedContent);
    }

    if (!dest.endsWith(".js")) {
      // If the destination is not a JS file, we don't need to generate a manifest
      return result;
    }

    return result;
  }

  /**
   * Generates a PHP manifest file for the given build task.
   * @param {BuildTask} buildTask - The build task to generate the manifest for.
   * @returns {Promise<void>}
   */
  async phpManifest({ dest, globals }) {
    if (!dest.endsWith(".js")) {
      // If the destination is not a JS file, we don't need to generate a manifest
      return;
    }

    const manifestFilename = `dist/php/${path
      .basename(dest)
      .replace(/\.js$/, ".asset.php")}`;

    const destContent = await Deno.readTextFile(dest);

    const hashBuffer = await crypto.subtle.digest(
      "SHA-256",
      new TextEncoder().encode(destContent),
    );
    const hash = encodeHex(hashBuffer);

    const dependencies = Object.values(globals || {}).map((g) => g.wpId);

    const content = `
    <?php

    /**
     * This file is auto-generated by the build process.
     * Do not edit this file manually.
     */

    defined('ABSPATH') || exit;

    return array(
        'dependencies' => ${JSON.stringify(dependencies)},
        'version' => '${hash.slice(0, 8)}',
    );
    `
      .replace(/^\ {4}/gm, "") // Remove leading spaces
      .trim();

    await Deno.mkdir(path.dirname(manifestFilename), { recursive: true });
    await Deno.writeTextFile(manifestFilename, content);
  }
}

/** @type {StaticAsset[]} */
const staticAssets = [
  { src: "./src/img", dest: "./dist/img" },
  { src: "./src/archived", dest: "./dist/archived" },
];

/** @type {Record<string, ModuleInfo>} */
const globalsForBlocks = {
  "@wordpress/dom-ready": {
    varName: "wp.domReady",
    type: "cjs",
    wpId: "wp-dom-ready",
  },
  "@wordpress/hooks": {
    varName: "wp.hooks",
    type: "cjs",
    wpId: "wp-hooks",
  },
  "@wordpress/blocks": {
    varName: "wp.blocks",
    type: "cjs",
    wpId: "wp-blocks",
  },
  "@wordpress/data": {
    varName: "wp.data",
    type: "cjs",
    wpId: "wp-data",
  },
  "@wordpress/element": {
    varName: "wp.element",
    type: "cjs",
    wpId: "wp-element",
  },
  "@wordpress/block-editor": {
    varName: "wp.blockEditor",
    type: "cjs",
    wpId: "wp-block-editor",
  },
  "@wordpress/components": {
    varName: "wp.components",
    type: "cjs",
    wpId: "wp-components",
  },
  "@wordpress/editor": {
    varName: "wp.editor",
    type: "cjs",
    wpId: "wp-editor",
  },
  "@wordpress/plugins": {
    varName: "wp.plugins",
    type: "cjs",
    wpId: "wp-plugins",
  },
  "@wordpress/i18n": {
    varName: "wp.i18n",
    type: "cjs",
    wpId: "wp-i18n",
  },
  "@wordpress/url": {
    varName: "wp.url",
    type: "cjs",
    wpId: "wp-url",
  },
  "@wordpress/compose": {
    varName: "wp.compose",
    type: "cjs",
    wpId: "wp-compose",
  },
  "@wordpress/date": {
    varName: "wp.date",
    type: "cjs",
    wpId: "wp-date",
  },
  "react/jsx-runtime": {
    varName: "ReactJSXRuntime",
    type: "cjs",
    wpId: "react-jsx-runtime",
  },
  react: {
    varName: "React",
    type: "cjs",
    wpId: "react",
  },
};

/** @type {BuildTask[]} */
const buildTasks = [
  {
    src: "src/js/app.js",
    dest: "dist/app.min.js",
    globals: { jquery: { varName: "jQuery", type: "cjs" } },
  },
  { src: "src/js/login.js", dest: "dist/js/login.min.js" },
  { src: "src/js/admin/index.js", dest: "dist/admin.min.js" },
  /** Blocks **/
  // Need to replicate `@tinypixelco/laravel-mix-wp-blocks` functionality
  // https://laravel-mix.com/extensions/wp-blocks
  {
    src: "src/js/block-editor.js",
    dest: "dist/block-editor.js",
    globals: globalsForBlocks,
  },
  /**  SASS **/
  { src: "src/sass/app.scss", dest: "dist/css/app.min.css" },
  { src: "src/sass/admin.scss", dest: "dist/css/admin.min.css" },
  { src: "src/sass/editor.scss", dest: "dist/css/editor.min.css" },
  { src: "src/sass/login.scss", dest: "dist/css/login.min.css" },
  { src: "src/css/editor-style.css", dest: "dist/css/editor-style.css" },
  /** patch code for CCFW **/
  {
    src: "src/patch/js/ccfw-cookie-manage.js",
    dest: "dist/patch/js/ccfw-cookie-manage.js",
  },
  {
    src: "src/patch/js/ccfw-frontend.js",
    dest: "dist/patch/js/ccfw-frontend.js",
  },
];

const buildResult = await new Builder(staticAssets, buildTasks).bundle();
console.log("Build result:", buildResult);
