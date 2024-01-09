<?php
/**
 *
 * Template name: Left - Centre - Right
 * Template Post Type: page
 */

$post_id = get_the_ID();

get_header();
?>

    <main role="main" id="content-wrapper">
        <div class="container-wrapper">

            <div id="content-left">
                <ul class="menu-left">
                    <nav>
                        <li class="level1"><a href="/courts"
                            >Courts</a>
                        </li>
                        <li class="level1"><a href="/courts/procedure-rules"
                            >Procedure
                                rules</a></li>
                        <li class="level1"><a class="selected"
                                              href="/courts/procedure-rules/family"
                            >Family</a>
                        </li>
                        <li class="level2"><a
                                href="/courts/procedure-rules/family/fpr_foreword"
                            >Foreword
                                and summary of the rules</a></li>
                        <li class="level2"><a
                                href="/courts/procedure-rules/family/rules_pd_menu"
                            >Rules
                                &amp; Practice Directions</a></li>
                        <li class="level2"><a
                                href="/courts/procedure-rules/family/magistrates"
                            >Magistrates
                                Courts Rules</a></li>
                        <li class="level2"><a href="/courts/procedure-rules/family/glossary"
                            >Glossary</a>
                        </li>
                        <li class="level2"><a href="/courts/procedure-rules/family/formspage"
                            >Forms</a>
                        </li>
                        <li class="level2"><a href="/courts/procedure-rules/family/update"
                            >Updates
                                &amp; Zips</a></li>
                        <li class="level2"><a href="/courts/procedure-rules/family/stat_instr"
                            >Statutory
                                Instruments</a></li>
                        <li class="level2"><a href="/courts/procedure-rules/family/contact"
                            >Contact</a>
                        </li>
                    </nav>
                </ul>
            </div>

            <div id="content">
                <ul id="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="separator">»</li>
                    <li><a href="/courts">Courts</a></li>
                    <li class="separator">»</li>
                    <li><a href="/courts/procedure-rules">Procedure rules</a></li>
                    <li class="separator">»</li>
                    <li><a href="/courts/procedure-rules/family">Family</a>
                    </li>
                    <li class="separator">»</li>
                    <li><a href="/courts/procedure-rules/family/magistrates">Magistrates
                            Courts
                            Rules</a></li>
                </ul>
                <div class="device-only">
                    <div class="anchor-link anchor-top">
                        <div class="bar-left"></div>
                        <a href="#phonenav">Menu ≡</a>
                        <div class="bar-right"></div>
                    </div>
                </div>
                <div class="print-only">
                    <img src="/app/dist/img/logo-inv.png" alt="" title="">
                </div>
                <article>
                    <h1 class="title"><?php the_title(); ?></h1>
                    <div class="share-this"></div>


                    <!-- PAGE CONTENT -->
                    <!-- ------------------------------------ -->
                    <div class="article">
                        <?php the_content() ?>
                    </div>
                    <!-- ------------------------------------ -->
                    <!-- end/ PAGE CONTENT -->


                    <div class="share-this bottom">
                        <span class="right">Updated: Monday, 30 January 2017</span>
                    </div>
                </article>
            </div>

            <div id="content-right">
                <div id="rhs-banner" class="phone"><a href=""><img
                            src="/app/themes/justice/dist/img/moj-logo.gif" width="161"
                            height="86" alt="Ministry of Justice" title="Ministry of Justice"></a></div>
                <div id="panel-mostPopular-wrapper"></div>
                <div id="panel-relatedContent-wrapper"></div>
                <div id="panel-STContact" class="grey-box">
                    <div class="content">
                        <h3>Contact</h3>
                        <p></p>
                    </div>
                </div>

                <div id="panel-emailAlerts" class="grey-box">
                    <div class="header"><span>Get email alerts</span></div>
                    <div class="content">
                        <form class="styled" action="https://public.govdelivery.com/accounts/UKMOJ/subscribers/qualify">
                            <label for="rhs-email-alerts">Enter email address:</label>
                            <input id="rhs-email-alerts" name="email" type="text">
                            <input class="go-btn" value="Subscribe" type="submit">
                        </form>
                    </div>
                </div>
                <div id="panel-findForm" class="grey-box">
                    <div class="header"><span>Find a form</span></div>
                    <div class="content">
                        <form class="styled" action="/search">
                            <label for="rhs-find-a-form">Form name:</label>
                            <input id="rhs-find-a-form" name="query" type="text">
                            <input class="go-btn" value="Search forms" type="submit">
                            <input type="hidden" value="moj-matrix-dev-forms" name="collection">
                            <input type="hidden" value="simple" name="form">
                            <input type="hidden" value="_default" name="profile">
                        </form>
                    </div>
                </div>
                <div id="panel-findCourtForm" class="grey-box">
                    <div class="header"><span>Find a court form</span></div>
                    <div class="content">
                        <form class="styled" action="https://hmctsformfinder.justice.gov.uk/HMCTS/GetForms.do">
                            <label for="court_forms_num">Form/leaflet number:</label>
                            <input id="court_forms_num" name="court_forms_num" type="text">
                            <label for="court_forms_title">Form/leaflet title:</label>
                            <input id="court_forms_title" name="court_forms_title" type="text">
                            <label for="court_work_type">Available types:</label>
                            <select id="court_work_type" name="court_forms_category" style="display: none;">
                                <option value="">- please select -</option>
                                <option value="Administrative Court">Administrative Court</option>
                                <option value="Admiralty">Admiralty</option>
                                <option value="Adoption">Adoption</option>
                                <option value="Appeal">Appeal</option>
                                <option value="Appeal Notice">Appeal Notice</option>
                                <option value="Attachment of Earnings">Attachment of Earnings</option>
                                <option value="Bankruptcy">Bankruptcy</option>
                                <option value="Chancery">Chancery</option>
                                <option value="Children Act">Children Act</option>
                                <option value="Commercial court">Commercial court</option>
                                <option value="County Court">County Court</option>
                                <option value="County Court Bulk Centre">County Court Bulk Centre</option>
                                <option value="Court Costs - Other">Court Costs - Other</option>
                                <option value="Court of Appeal Civil Division">Court of Appeal Civil Division</option>
                                <option value="Court of Appeal Criminal Division">Court of Appeal Criminal Division
                                </option>
                                <option value="Court of Protection">Court of Protection</option>
                                <option value="Courts Charter">Courts Charter</option>
                                <option value="Criminal">Criminal</option>
                                <option value="Criminal Court costs">Criminal Court costs</option>
                                <option value="Crown Court">Crown Court</option>
                                <option value="Divorce / Civil Partnership Dissolution">Divorce / Civil Partnership
                                    Dissolution
                                </option>
                                <option value="Enforcement">Enforcement</option>
                                <option value="Family">Family</option>
                                <option value="General">General</option>
                                <option value="Housing">Housing</option>
                                <option value="Insolvency">Insolvency</option>
                                <option value="Jury Service">Jury Service</option>
                                <option value="Legal aid">Legal aid</option>
                                <option value="Magistrates' Court">Magistrates' Court</option>
                                <option value="Mediation">Mediation</option>
                                <option value="Mercantile Court">Mercantile Court</option>
                                <option value="Pilot forms">Pilot forms</option>
                                <option value="Probate">Probate</option>
                                <option value="Queen's Bench / Chancery">Queen's Bench / Chancery</option>
                                <option value="Road Traffic Act Personal Injury">Road Traffic Act Personal Injury
                                </option>
                                <option value="Technology and Construction Court">Technology and Construction Court
                                </option>
                                <option value="Traffic Enforcement Centre">Traffic Enforcement Centre</option>
                                <option value="Young witnesses">Young witnesses</option>
                            </select>
                            <div id="nselect0" class="nselect" style="z-index:1000;" tabindex="0">
                                <div class="current">- please select -</div>
                                <ul class="inner-list" style="display:none;">
                                    <li class="option0"><span>- please select -</span><input type="hidden" value="">
                                    </li>
                                    <li class="option1"><span>Administrative Court</span><input type="hidden"
                                                                                                value="Administrative Court">
                                    </li>
                                    <li class="option2"><span>Admiralty</span><input type="hidden" value="Admiralty">
                                    </li>
                                    <li class="option3"><span>Adoption</span><input type="hidden" value="Adoption"></li>
                                    <li class="option4"><span>Appeal</span><input type="hidden" value="Appeal"></li>
                                    <li class="option5"><span>Appeal Notice</span><input type="hidden"
                                                                                         value="Appeal Notice"></li>
                                    <li class="option6"><span>Attachment of Earnings</span><input type="hidden"
                                                                                                  value="Attachment of Earnings">
                                    </li>
                                    <li class="option7"><span>Bankruptcy</span><input type="hidden" value="Bankruptcy">
                                    </li>
                                    <li class="option8"><span>Chancery</span><input type="hidden" value="Chancery"></li>
                                    <li class="option9"><span>Children Act</span><input type="hidden"
                                                                                        value="Children Act"></li>
                                    <li class="option10"><span>Commercial court</span><input type="hidden"
                                                                                             value="Commercial court">
                                    </li>
                                    <li class="option11"><span>County Court</span><input type="hidden"
                                                                                         value="County Court"></li>
                                    <li class="option12"><span>County Court Bulk Centre</span><input type="hidden"
                                                                                                     value="County Court Bulk Centre">
                                    </li>
                                    <li class="option13"><span>Court Costs - Other</span><input type="hidden"
                                                                                                value="Court Costs - Other">
                                    </li>
                                    <li class="option14"><span>Court of Appeal Civil Division</span><input type="hidden"
                                                                                                           value="Court of Appeal Civil Division">
                                    </li>
                                    <li class="option15"><span>Court of Appeal Criminal Division</span><input
                                            type="hidden" value="Court of Appeal Criminal Division"></li>
                                    <li class="option16"><span>Court of Protection</span><input type="hidden"
                                                                                                value="Court of Protection">
                                    </li>
                                    <li class="option17"><span>Courts Charter</span><input type="hidden"
                                                                                           value="Courts Charter"></li>
                                    <li class="option18"><span>Criminal</span><input type="hidden" value="Criminal">
                                    </li>
                                    <li class="option19"><span>Criminal Court costs</span><input type="hidden"
                                                                                                 value="Criminal Court costs">
                                    </li>
                                    <li class="option20"><span>Crown Court</span><input type="hidden"
                                                                                        value="Crown Court"></li>
                                    <li class="option21"><span>Divorce / Civil Partnership Dissolution</span><input
                                            type="hidden" value="Divorce / Civil Partnership Dissolution"></li>
                                    <li class="option22"><span>Enforcement</span><input type="hidden"
                                                                                        value="Enforcement"></li>
                                    <li class="option23"><span>Family</span><input type="hidden" value="Family"></li>
                                    <li class="option24"><span>General</span><input type="hidden" value="General"></li>
                                    <li class="option25"><span>Housing</span><input type="hidden" value="Housing"></li>
                                    <li class="option26"><span>Insolvency</span><input type="hidden" value="Insolvency">
                                    </li>
                                    <li class="option27"><span>Jury Service</span><input type="hidden"
                                                                                         value="Jury Service"></li>
                                    <li class="option28"><span>Legal aid</span><input type="hidden" value="Legal aid">
                                    </li>
                                    <li class="option29"><span>Magistrates' Court</span><input type="hidden"
                                                                                               value="Magistrates' Court">
                                    </li>
                                    <li class="option30"><span>Mediation</span><input type="hidden" value="Mediation">
                                    </li>
                                    <li class="option31"><span>Mercantile Court</span><input type="hidden"
                                                                                             value="Mercantile Court">
                                    </li>
                                    <li class="option32"><span>Pilot forms</span><input type="hidden"
                                                                                        value="Pilot forms"></li>
                                    <li class="option33"><span>Probate</span><input type="hidden" value="Probate"></li>
                                    <li class="option34"><span>Queen's Bench / Chancery</span><input type="hidden"
                                                                                                     value="Queen's Bench / Chancery">
                                    </li>
                                    <li class="option35"><span>Road Traffic Act Personal Injury</span><input
                                            type="hidden" value="Road Traffic Act Personal Injury"></li>
                                    <li class="option36"><span>Technology and Construction Court</span><input
                                            type="hidden" value="Technology and Construction Court"></li>
                                    <li class="option37"><span>Traffic Enforcement Centre</span><input type="hidden"
                                                                                                       value="Traffic Enforcement Centre">
                                    </li>
                                    <li class="option38"><span>Young witnesses</span><input type="hidden"
                                                                                            value="Young witnesses">
                                    </li>
                                </ul>
                            </div>

                            <input class="go-btn" value="Search court forms" type="submit">
                        </form>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </main>

<?php
get_footer();
