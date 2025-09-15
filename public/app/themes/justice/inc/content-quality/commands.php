<?php

namespace MOJ;

// Do not allow access outside WP
defined('ABSPATH') || exit;

/**
 * This class is related to WP_CLI commands content quality.
 *
 * Usage:
 * - wp content-quality exclude-news --dry-run
 * - wp content-quality exclude-news --dry-run=false
 * - wp content-quality fix-typos --dry-run
 * - wp content-quality fix-typos --dry-run=false
 */

use WP_CLI;

class ContentQualityCommands
{
    /**
     * Invoke method, for when the command is called.
     */
    public function __invoke($args, $assoc_args): void
    {
        error_reporting(0);

        if (!isset($assoc_args['dry-run'])) {
            WP_CLI::error('The --dry-run argument is required. Please use --dry-run or --dry-run=false.');
            return;
        }

        $dry_run = $assoc_args['dry-run'] !== 'false';

        WP_CLI::log('Running content quality command with dry-run: ' . ($dry_run ? 'true' : 'false'));

        switch ($args[0] ?? '') {
            case 'exclude-news':
                WP_CLI::log('Will do something with the content quality issues, excluding news pages.');

                // Get all page IDs, where the metadata _content_quality_exclude is not set.
                $get_all_page_ids = new \WP_Query([
                    'post_type' => 'page',
                    'fields' => 'ids',
                    'meta_query' => [
                        'relation' => 'OR',
                        [
                            'key' => '_content_quality_exclude',
                            'compare' => 'NOT EXISTS',
                        ],
                        [
                            'key' => '_content_quality_exclude',
                            'value' => 0,
                            'compare' => '=',
                        ],
                    ],
                    'nopaging' => true,
                    'post_status' => ['publish', 'private'],
                ]);

                // Loop through all posts, and if it is a news post, set the metadata _content_quality_exclude to 1.
                foreach ($get_all_page_ids->posts as $page_id) {
                    $path = parse_url(get_permalink($page_id), PHP_URL_PATH);
                    if (preg_match('/^\/news(-\d+)?\//', $path)) {
                        // If the path starts with /news/ or /news-<number>/,
                        // set the metadata _content_quality_exclude to 1.
                        WP_CLI::log('Excluding page ' . $page_id . ' with path ' . $path);
                        if (!$dry_run) {
                            update_post_meta($page_id, '_content_quality_exclude', 1);
                        }
                    } else {
                        WP_CLI::log('Not excluding page ' . $page_id . ' with path ' . $path);
                        if (!$dry_run) {
                            update_post_meta($page_id, '_content_quality_exclude', 0);
                        }
                    }
                }

                break;

            case 'fix-typos':
                WP_CLI::log('Will fix typos in the content quality issues.');

                if (!$dry_run) {
                    $database_export_path = '/tmp/export-' . date('Ymd_His') . '.sql';

                    WP_CLI::log('Exporting the database.');

                    $db_response = WP_CLI::runcommand(
                        'db export ' . escapeshellarg($database_export_path) . ' --add-drop-table --tables=wp_posts',
                        ['return' => 'all']
                    );

                    if ($db_response?->return_code !== 0) {
                        WP_CLI::error('Failed to export the database: ' . implode("\n", $db_response->stdout));
                        return;
                    }

                    WP_CLI::log('Database exported to ' . $database_export_path);
                }

                // Where possible, target words have spaces before and after them, so that we don't match substrings.
                // e.g. to fix the typo 'claiman' to 'claimant',
                // we need to match ' claiman ' and replace it with ' claimant '.

                $typos = [
                    ' 14 daysto ' => ' 14 days to ',
                    ' a roador other ' => ' a road or other ',
                    ' ABDUCTIONThis ' => ' ABDUCTION This ',
                    ' Act’means ' => ' Act’ means ',
                    ' ADULTThis ' => ' ADULT This ',
                    'Anamendment ' => 'An amendment ',
                    ' andmodifies ' => ' and modifies ',
                    'Annex ADraft ' => 'Annex A Draft ',
                    'Annex AReport ' => 'Annex A Report ',
                    'Annex BReport ' => 'Annex B Report ',
                    'AppendixA ' => 'Appendix A ',
                    ' APPLICATIONSThis ' => ' APPLICATIONS This ',
                    ' accordiingly' => ' accordingly',
                    ' addedafter ' => ' added after ',
                    ' adoptionpanel' => ' adoption panel',
                    ' amedment ' => ' amendment ',
                    ' andthe ' => ' and the ',
                    ' anyother ' => ' any other ',
                    ' anycomments ' => ' any comments ',
                    ' anyproceedings ' => ' any proceedings ',
                    ' aparty ' => ' a party ',
                    ' aperson ' => ' a person ',
                    ' applicaiton ' => ' application ',
                    ' applicationunder ' => ' application under ',
                    ' Architects’Registration ' => ' Architects’ Registration ',
                    ' arecompleted ' => ' are completed ',
                    ' asfollows' => ' as follows',
                    ' beenmade ' => ' been made ',
                    ' beenverified ' => ' been verified ',
                    ' beverified ' => ' be verified ',
                    ' bysection ' => ' by section ',
                    ' CASEThis ' => ' CASE This ',
                    ' categoryof ' => ' category of ',
                    ' Chelmesford' => ' Chelmsford',
                    ' CHILDRENThis ' => ' CHILDREN This ',
                    ' claimantmust ' => ' claimant must ',
                    ' claimfor ' => ' claim for ',
                    '>claimfor<' => '>claim for<',
                    ' claimis ' => ' claim is ',
                    '>claimis ' => '>claim is ',
                    ' claimsfor ' => ' claims for ',
                    ' considerthat ' => ' consider that ',
                    ' comeinto force ' => ' come into force ',
                    ' Compensator’sResponse' => ' Compensator’s Response',
                    ' complainedof ' => ' complained of ',
                    ' COSTSThis ' => ' COSTS This ',
                    ' courtmust' => ' court must',
                    ' court\'\'s ' => ' court\'s ',
                    ' daysbefore ' => ' days before ',
                    ' daysof ' => ' days of ',
                    ' deductionfrom ' => ' deduction from ',
                    ' directions ' => ' directions ',
                    ' Directionss<' => ' Directions<',
                    ' ddirections ' => ' directions ',
                    '-ddirections-' => '-directions-',
                    'defendent' => 'defendant',
                    ' DEFANDANTS ' => ' DEFENDANTS ',
                    ' deponentbefore ' => ' deponent before ',
                    ' digitial ' => ' digital ',
                    ' Directon ' => ' Direction ',
                    ' documen</a>t ' => ' document</a> ',
                    ' DRsfrom ' => 'DRs from ',
                    'Employ-ment ' => 'Employment ',
                    ' entry’has ' => ' entry’ has ',
                    ' EVIDENCEThis ' => ' EVIDENCE This ',
                    ' excercise ' => ' exercise ',
                    ' experts’reports' => ' experts’ reports',
                    ' f<strong>a</strong>mily ' => ' family ',
                    ' fille type' => ' file type',
                    ' FinancialRemedy' => ' Financial Remedy',
                    ' forCosts' => ' for Costs',
                    ' forestablishment ' => ' for establishment ',
                    ' forextensions ' => ' for extensions ',
                    ' forfiling ' => ' for filing ',
                    ' forrecognition ' => ' for recognition ',
                    ' givingt he ' => ' giving the ',
                    ' Graphis ' => ' Graphics ',
                    ' HCMTS ' => ' HMCTS ',
                    ' includinga ' => ' including a ',
                    ' inserted inrule ' => ' inserted in rule ',
                    ' insertedin ' => ' inserted in ',
                    ' INSPECTIONThis ' => ' INSPECTION This ',
                    ' inthe ' => ' in the ',
                    ' justices’clerk' => ' justices’ clerk',
                    'KIng' => 'King',
                    ' LINKAnti-social ' => ' LINK Anti-social ',
                    ' LINKAppendix ' => ' LINK Appendix ',
                    ' LINKCCR ' => ' LINK CCR ',
                    ' LINKCosts ' => ' LINK Costs ',
                    ' LINKIn ' => ' LINK In ',
                    ' LINKNew ' => ' LINK New ',
                    ' LINKO.' => ' LINK O.',
                    ' LINKPara ' => ' LINK Para ',
                    ' LINKPart ' => ' LINK Part ',
                    ' LINKPractice ' => ' LINK Practice ',
                    ' LINKProtocols ' => ' LINK Protocols ',
                    ' LINKRSC ' => ' LINK RSC ',
                    ' LINKRule ' => ' LINK Rule ',
                    ' LINKSchedule ' => ' LINK Schedule ',
                    ' LINKcross ' => ' LINK cross ',
                    ' LINKnew ' => ' LINK new ',
                    ' LINKpara ' => ' LINK para ',
                    ' LINKr.' => ' LINK r.',
                    ' LINKrule ' => ' LINK rule ',
                    ' LINKsub-para ' => ' LINK sub-para ',
                    ' LINKwas ' => ' LINK was ',
                    ' Linkid ' => ' Link id ',
                    ' madeconsequential ' => ' made consequential ',
                    ' madeto ' => ' made to ',
                    'MAGISTRATES’COURT' => 'MAGISTRATES’ COURT',
                    ' Mainenance ' => ' Maintenance ',
                    'MaintenanceOrders' => 'Maintenance Orders',
                    ' maintenanceorder ' => ' maintenance order ',
                    'nb this practice direction' => 'NB this practice direction',
                    ' necesary ' => ' necessary ',
                    ' newforms ' => ' new forms ',
                    ' notbe ' => ' not be ',
                    ' notice’means ' => ' notice’ means ',
                    ' o rreceived ' => ' or received ',
                    ' officer’sreasonable ' => ' officer’s reasonable ',
                    ' officerin ' => ' officer in ',
                    ' ofAmerica' => ' of America',
                    ' ofIreland' => ' of Ireland',
                    ' ofProceedings' => ' of Proceedings',
                    ' OFTRUTH ' => ' OF TRUTH ',
                    ' ofTruth' => ' of Truth',
                    ' ofmaintenance ' => ' of maintenance ',
                    ' Omiited' => ' Omitted',
                    ' omittedfrom ' => ' omitted from ',
                    ' Online Civil Money Claims to the CBNC ' => ' Online Civil Money Claims to the CNBC ',
                    ' ORDERSThis ' => ' ORDERS This ',
                    ' orally or in writting.' => ' orally or in writing.',
                    ' orderwas ' => ' order was ',
                    ' orderwhich ' => ' order which ',
                    ' paraghraph ' => ' paragraph ',
                    ' paragrph ' => ' paragraph ',
                    ' partiesmust ' => ' parties must ',
                    ' PARTIESThis ' => ' PARTIES This ',
                    ' party’means ' => ' party’ means ',
                    ' PD14ECommunication ' => ' PD14E Communication ',
                    ' PD maling document' => ' PD making document',
                    'PortalSupport ' => 'Portal Support ',
                    ' posession ' => ' possession ',
                    ' possesion ' => ' possession ',
                    ' Post Officce SAct ' => ' Post Offices Act ',
                    ' Powerpoint ' => ' PowerPoint ',
                    'P“ower ' => 'Power ',
                    ' PROCEEDINGSThis ' => ' PROCEEDINGS This ',
                    ' PROCEEEDINGS' => ' PROCEEDINGS',
                    ' Protocoll ' => ' Protocol ',
                    ' providesamendments ' => ' provides amendments ',
                    ' QBDRs ' => ' QB DRs ',
                    ' questionnaire’replaced ' => ' questionnaire’ replaced ',
                    ' registeredin ' => ' registered in ',
                    ' ‘reinstatement’or ' => ' ‘reinstatement’ or ',
                    ' relationto ' => ' relation to ',
                    ' relocateor dispense ' => ' relocate or dispense ',
                    ' REPRESENTATIVESor ' => ' REPRESENTATIVES or ',
                    'responden’s notice' => 'respondent’s notice',
                    ' revie w ' => ' review ',
                    'SECtion 5' => 'Section 5',
                    ' servicecs ' => ' services ',
                    ' shcedule ' => ' schedule ',
                    ' SOLICITORThis ' => ' SOLICITOR This ',
                    ' solicitorin ' => ' solicitor in ',
                    ' Statesof ' => ' States of ',
                    ' subsituted for ' => ' substituted for ',
                    ' substitutedin ' => ' substituted in ',
                    'Tesside' => 'Teesside',
                    'Textomitted ' => 'Text omitted ',
                    ' textinserted ' => ' text inserted ',
                    ' textsubstituted ' => ' text substituted ',
                    ' thatthe ' => ' that the ',
                    ' theCivil ' => ' the Civil ',
                    ' theclaim ' => ' the claim ',
                    'theclaim is ' => 'the claim is ',
                    ' thee xpert ' => ' the expert ',
                    ' thefollowing ' => ' the following ',
                    ' theHealth ' => ' the Health ',
                    ' theimplementation ' => ' the implementation ',
                    ' theLord ' => ' the Lord ',
                    ' theMaintenance ' => ' the Maintenance ',
                    ' thepurposes ' => ' the purposes ',
                    ' theRules' => ' the Rules',
                    ' thesection ' => ' the section ',
                    ' thestatement ' => ' the statement ',
                    ' thetable ' => ' the table ',
                    ' there isagreement ' => ' there is agreement ',
                    ' theTerrorism Act ' => ' the Terrorism Act ',
                    ' ThisPractice ' => ' This Practice ',
                    ' thos eproceedings ' => ' those proceedings ',
                    ' throughoutthe ' => ' throughout the ',
                    'timetabl</strong>e ' => 'timetable</strong> ',
                    ' tobe ' => ' to be ',
                    ' tochild ' => ' to child ',
                    ' togive ' => ' to give ',
                    ' toreflect ' => ' to reflect ',
                    ' TRUTHThis ' => 'TRUTH This',
                    ' tthe ' => ' the ',
                    ' UNDERTAKINGSThis ' => ' UNDERTAKINGS This ',
                    ' underthe ' => ' under the ',
                    ' wereomitted ' => ' were omitted ',
                    'whichis ' => 'which is ',
                    ' withoutParagraph ' => ' without Paragraph ',
                    ' ye tbeen ' => ' yet been ',
                    '<strong>claiman</strong>t<strong>’s</strong>' => '<strong>claimant’s</strong>',
                    '>C</a>ontents of this Part' => '>Contents of this Part</a>',
                    '>AIms</a>' => '>Aims</a>',
                    ')</a><br>rder ' => ')</a><br>Order ',
                ];

                $options = [
                    'return' => 'all',
                    'command_args' => [
                        '--include-columns=post_content,post_content_filtered',
                        '--format=count',
                    ]
                ];

                if ($dry_run) {
                    $options['command_args'][] = '--dry-run';
                }

                $running_total = 0;

                foreach ($typos as $typo => $correction) {
                    // Run a search-replace command for each typo.
                    $response = WP_CLI::runcommand(
                        'search-replace ' . escapeshellarg($typo) . ' ' . escapeshellarg($correction),
                        // 'search-replace ' . escapeshellarg($typo) . ' ' . escapeshellarg($correction) . ' wp_posts',
                        $options
                    );

                    if ($response?->return_code === 0) {
                        // WP_CLI::log("Successfully fixed typo '$typo' to '$correction'.");
                        // WP_CLI::log(print_r($response->stdout));
                        WP_CLI::log("Successfully fixed typo '$typo' to '$correction' $response->stdout times.");
                        // Add the number of replacements to the running total.
                        $running_total += (int)$response->stdout;
                    } else {
                        WP_CLI::error("Failed to fix typo '$typo': " . implode("\n", $response->stdout));
                    }
                }

                if ($dry_run) {
                    WP_CLI::log("$running_total typos fixed. Please run the command without --dry-run to apply the changes.");
                }

                if (!$dry_run) {
                    WP_CLI::log("$running_total typos fixed and changes applied to the database.");

                    if ($running_total) {
                        // Clear the cache for the content quality issues.
                        WP_CLI::log('Clearing the cache for content quality issues.');
                        global $wpdb;

                        $wpdb->query(
                            $wpdb->prepare(
                                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                                '_transient_moj:content-quality:issue:spelling%'
                            )
                        );
                    }

                    // Flush cache for the posts.
                    WP_CLI::log('Flushing cache.');

                    wp_cache_flush();
                }




                break;

            default:
                WP_CLI::log('ContentQuality command not recognized');
                break;
        }
    }
}



if (defined('WP_CLI') && WP_CLI) {
    $cluster_helper_commands = new ContentQualityCommands();
    // 1. Register the instance for the callable parameter.
    WP_CLI::add_command('content-quality', $cluster_helper_commands);

    // 2. Register object as a function for the callable parameter.
    WP_CLI::add_command('content-quality', 'MOJ\ContentQualityCommands');
}
