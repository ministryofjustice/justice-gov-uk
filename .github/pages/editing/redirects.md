---
title:  "Redirects"
nav_order: 4
---

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

## Overview

<div class="annotated-screenshot" >
  <img alt="Screenshot of the document edit screen" src="../assets/redirect-edit.png" />
  <ol>
    <li id="annotation-from" class="label" style="top: 21%; right: 83%;" >From</li>
    <li id="annotation-to" class="label" style="top: 41%; right: 83%;" >To</li>
    <li id="annotation-notes" class="label" style="top: 77%; right: 83%;" >Notes</li>
    <li id="annotation-publish" class="label" style="top: 44%; right: 1%;" >Publish</li>
  </ol>
</div>

Due to the dynamic nature of the content on this website, it may be necessary to change or delete a page or document. When this happens, it is important to create a redirect to ensure that users are not met with a 404 error when trying to access the old URL.

## Creating a redirect

To create a redirect, follow these steps:

1. Click Tools > Redirect Manager in the Sidebar.
1. Click Create Redirect Rule
1. In the redirect from field, enter the old URL that you want to redirect from.
   It should be the part of the url after `https://www.justice.gov.uk` e.g. `/my-old-page`.
1. In the redirect to field, enter the new URL that you want to redirect to.
   If the new URL is on this site, you can type the part of the url after `https://www.justice.gov.uk` e.g. `/my-new-page`.
1. Leave the Status Code as 301 Move Permanently, unless you have a specific reason to change it.
1. Optionally add a Note to describe the reason for the redirect.
1. Leave the Post Attributes > Order field as 0.
1. Click Publish.
1. Verify the redirect by visiting the old URL in a new browser window.

{: .highlight }
See the screenshot for the location of: the 
Redirect [From](#annotation-from) field,
Redirect [To](#annotation-to) field,
[Notes](#annotation-notes) field and the
[Publish](#annotation-publish) button.

## Gotchas

1. Be cautions not to create 2 redirects that direct to each other. 
   This will create an loop and cause the page to fail to load.
1. When updating a redirect, be aware that if you change the 'Redirect From' URL, 
   this may result in a 404 error error for the old URL.
1. Similarly, deleting a redirect rule could cause a 404 error for the old URL. 
   It is usually best not to delete a redirect rule, but to update it to point to a new URL instead.
