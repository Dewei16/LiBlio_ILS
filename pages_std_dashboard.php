<?php
    /*
    *Handle Student DASHBOARD page logic
    */
    session_start();
    include('assets/config/config.php');
    include('assets/config/checklogin.php');
    check_login();

    
    /*
    Statics logic
        1.Books
            1.0 : Number of all Borrowed Books no matter what category and returned
            1.1 : Number of all Lost Books no matter what category
            1.2 : Number of Damanged Books
            1.3 : Number of borrowed book but havent returned

        
        2.Misc
            2.0 : Total amount of fine owed(For either loosing or damanging a book)
            2.1 : Total amount of fine already paid(For either losing a book or damanging it)
            2.2 : Total amount of fine owed for loosing a book
            2.3 : Total amount of fine for returing a damamnged book

    */
     //1.Books

    //1.3 : Number of all Borrowed Books no matter what category but aint returned
    $id = $_SESSION['s_id'];
    $result ="SELECT count(*) FROM iL_LibraryOperations WHERE lo_type = 'Borrow' AND lo_status = '' AND s_id = ? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($borrowed_books);
    $stmt->fetch();
    $stmt->close();

    //1.1 : Number of all Lost Books no matter what category
    $result ="SELECT count(*) FROM iL_LibraryOperations WHERE lo_status = 'Lost' AND s_id = ?  ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($lost_books);
    $stmt->fetch();
    $stmt->close();

     //1.2 : Number of all Damanged no matter what category
     $result ="SELECT count(*) FROM iL_LibraryOperations WHERE  lo_status = 'Damanged' AND s_id =? ";
     $stmt = $mysqli->prepare($result);
     $stmt->bind_param('i', $id);
     $stmt->execute();
     $stmt->bind_result($damanged_books);
     $stmt->fetch();
     $stmt->close();

    //1.0 : Number of all Borrowed Books no matter what category and returned
    $result ="SELECT count(*) FROM iL_LibraryOperations WHERE lo_status = 'Returned' AND s_id =? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($Returned);
    $stmt->fetch();
    $stmt->close();

     //$damanged_and_lost_books = $lost_books + $damanged_books;

    //2.1 : Number of all amount paid by students as a fine of loosing  any book
    $result ="SELECT SUM(f_amt) FROM iL_Fines WHERE f_type = 'Lost Book' AND s_id = ?";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($lostBookfines);
    $stmt->fetch();
    $stmt->close();

    //2.2 : Number of all amount paid by students as a fine of  damaging any book
    $result ="SELECT SUM(f_amt) FROM iL_Fines WHERE f_type = 'Damaged Book' AND s_id = ? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($damangedBookfines);
    $stmt->fetch();
    $stmt->close();

    //2.3 : Number of all amount paid by students as a fine of  damaging any book
    $result ="SELECT SUM(f_amt) FROM iL_Fines WHERE f_status = 'Paid' AND s_id =? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($paidFine);
    $stmt->fetch();
    $stmt->close();

    $totalFine = $lostBookfines + $damangedBookfines;


    /*
        The following block of codes implements Books Charts

        -->Books Category Will be HardCoded so my bad<--
    */


    //1.0.1 : Number Of Books which have returned successfully
    $result ="SELECT COUNT(*) FROM iL_LibraryOperations WHERE lo_status = 'Returned' AND s_id =? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($returned_successfully);
    $stmt->fetch();
    $stmt->close();

    //1.0.2 : Number Of Books which have returned but are damanged
    $result ="SELECT COUNT(*) FROM iL_LibraryOperations WHERE lo_status = 'Damanged' AND s_id =? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($returned_damanged);
    $stmt->fetch();
    $stmt->close();

    //1.0.3 : Number Of Books which are lost
    $result ="SELECT COUNT(*) FROM iL_LibraryOperations WHERE lo_status = 'Lost' AND s_id =? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($lost);
    $stmt->fetch();
    $stmt->close();

    //1.1.0 : Number of Borrowed Books Per Books in Non-fiction Category ->Piechart or Donought Chart
    $result ="SELECT COUNT(*) FROM iL_LibraryOperations WHERE bc_name = 'Non-fiction' AND lo_type ='Borrow' AND s_id = ? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($borrowed_non_fiction);
    $stmt->fetch();
    $stmt->close();

    //1.1.1 : Number of Borrowed Books Per Books in fiction Category ->Piechart or Donought Chart
    $result ="SELECT COUNT(*) FROM iL_LibraryOperations WHERE bc_name = 'Fiction' AND lo_type ='Borrow' AND s_id = ? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($borrowed_fiction);
    $stmt->fetch();
    $stmt->close();

    //1.1.2 : Number of Borrowed Books Per Books in References Category ->Piechart or Donought Chart
    $result ="SELECT COUNT(*) FROM iL_LibraryOperations WHERE bc_name = 'References' AND lo_type ='Borrow' AND s_id = ? ";
    $stmt = $mysqli->prepare($result);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($borrowed_references);
    $stmt->fetch();
    $stmt->close();

    
    
?>
<!doctype html>
<!--[if lte IE 9]> <html class="lte-ie9" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="en"> <!--<![endif]-->
<?php
    include("assets/inc/head.php");
?>
<body class="disable_transitions sidebar_main_open sidebar_main_swipe">
    <!-- main header -->
    <?php
        include("assets/inc/nav.php"); 
    ?>
    <!-- main header end -->

    <!-- main sidebar -->
    <?php
        include("assets/inc/sidebar.php");
    ?>
    <!-- main sidebar end -->

    <div id="page_content">
        <div id="page_content_inner">

            <!--1.Books-->
            <div class="uk-grid uk-grid-width-large-1-4 uk-grid-width-medium-1-2 uk-grid-medium uk-sortable sortable-handler hierarchical_show" data-uk-sortable data-uk-grid-margin>
                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Borrowed Books</span>
                            <h2 class="uk-margin-remove"><span class="countUpMe">0<noscript><?php echo $borrowed_books;?></noscript></span></h2>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Returned Books</span>
                            <h2 class="uk-margin-remove"><span class="countUpMe">0<noscript><?php echo $Returned;?></noscript></span></h2>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Lost Books</span>
                            <h2 class="uk-margin-remove"><span class="countUpMe">0<noscript><?php echo $lost_books;?></noscript></span></h2>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Damaged Books</span>
                            <h2 class="uk-margin-remove"><?php echo $damanged_books;?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!--2. Fines-->
            <div class="uk-grid uk-grid-width-large-1-4 uk-grid-width-medium-1-2 uk-grid-medium uk-sortable sortable-handler hierarchical_show" data-uk-sortable data-uk-grid-margin>
                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Library Lost Books Fines</span>
                            <h2 class="uk-margin-remove">₱ <?php echo $lostBookfines;?></h2>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Library Damanged Books Fines</span>
                            <h2 class="uk-margin-remove">₱ <?php echo $damangedBookfines;?></h2>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Library Total Fines Posted</span>
                            <h2 class="uk-margin-remove">₱ <?php echo $totalFine;?></h2>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right uk-margin-top uk-margin-small-right"></div>
                            <span class="uk-text-muted uk-text-small">Library Total Fine Paid</span>
                            <h2 class="uk-margin-remove">₱ <?php echo $paidFine;?></h2>
                        </div>
                    </div>
                </div>
            </div>
          
          

        </div>
    </div>
    <!--Footer-->
    <?php require_once('assets/inc/footer.php');?>
    <!--Footer-->


    <!-- google web fonts -->
    <script>
        WebFontConfig = {
            google: {
                families: [
                    'Source+Code+Pro:400,700:latin',
                    'Roboto:400,300,500,700,400italic:latin'
                ]
            }
        };
        (function() {
            var wf = document.createElement('script');
            wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
            '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
            wf.type = 'text/javascript';
            wf.async = 'true';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(wf, s);
        })();
    </script>

    <!-- common functions -->
    <script src="assets/js/common.min.js"></script>
    <!-- uikit functions -->
    <script src="assets/js/uikit_custom.min.js"></script>
    <!-- altair common functions/helpers -->
    <script src="assets/js/altair_admin_common.min.js"></script>

    <!-- page specific plugins -->
        <!-- d3 -->
        <script src="bower_components/d3/d3.min.js"></script>
        <!-- metrics graphics (charts) -->
        <script src="bower_components/metrics-graphics/dist/metricsgraphics.min.js"></script>
        <!-- chartist (charts) -->
        <script src="bower_components/chartist/dist/chartist.min.js"></script>
        <script src="bower_components/maplace-js/dist/maplace.min.js"></script>
        <!-- peity (small charts) -->
        <script src="bower_components/peity/jquery.peity.min.js"></script>
        <!-- easy-pie-chart (circular statistics) -->
        <script src="bower_components/jquery.easy-pie-chart/dist/jquery.easypiechart.min.js"></script>
        <!-- countUp -->
        <script src="bower_components/countUp.js/dist/countUp.min.js"></script>
        <!-- handlebars.js -->
        <script src="bower_components/handlebars/handlebars.min.js"></script>
        <script src="assets/js/custom/handlebars_helpers.min.js"></script>
        <!-- CLNDR -->
        <script src="bower_components/clndr/clndr.min.js"></script>

        <!--  dashbord functions -->
        <script src="assets/js/pages/dashboard.min.js"></script>
    
    <script>
        $(function() {
            if(isHighDensity()) {
                $.getScript( "assets/js/custom/dense.min.js", function(data) {
                    // enable hires images
                    altair_helpers.retina_images();
                });
            }
            if(Modernizr.touch) {
                // fastClick (touch devices)
                FastClick.attach(document.body);
            }
        });
        $window.load(function() {
            // ie fixes
            altair_helpers.ie_fix();
        });
    </script>

<div id="style_switcher">
        <div id="style_switcher_toggle"><i class="material-icons">&#xE8B8;</i></div>
        <div class="uk-visible-large uk-margin-medium-bottom">
            <h4 class="heading_c">Sidebar</h4>
            <p>
                <input type="checkbox" name="style_sidebar_mini" id="style_sidebar_mini" data-md-icheck />
                <label for="style_sidebar_mini" class="inline-label">Mini Sidebar</label>
            </p>
            <p>
                <input type="checkbox" name="style_sidebar_slim" id="style_sidebar_slim" data-md-icheck />
                <label for="style_sidebar_slim" class="inline-label">Slim Sidebar</label>
            </p>
        </div>
    </div>

    <script>
        $(function() {
            var $switcher = $('#style_switcher'),
                $switcher_toggle = $('#style_switcher_toggle'),
                $theme_switcher = $('#theme_switcher'),
                $mini_sidebar_toggle = $('#style_sidebar_mini'),
                $slim_sidebar_toggle = $('#style_sidebar_slim'),
                $boxed_layout_toggle = $('#style_layout_boxed'),
                $accordion_mode_toggle = $('#accordion_mode_main_menu'),
                $html = $('html'),
                $body = $('body');


            $switcher_toggle.click(function(e) {
                e.preventDefault();
                $switcher.toggleClass('switcher_active');
            });

            $theme_switcher.children('li').click(function(e) {
                e.preventDefault();
                var $this = $(this),
                    this_theme = $this.attr('data-app-theme');

                $theme_switcher.children('li').removeClass('active_theme');
                $(this).addClass('active_theme');
                $html
                    .removeClass('app_theme_a app_theme_b app_theme_c app_theme_d app_theme_e app_theme_f app_theme_g app_theme_h app_theme_i app_theme_dark')
                    .addClass(this_theme);

                if(this_theme == '') {
                    localStorage.removeItem('altair_theme');
                    $('#kendoCSS').attr('href','bower_components/kendo-ui/styles/kendo.material.min.css');
                } else {
                    localStorage.setItem("altair_theme", this_theme);
                    if(this_theme == 'app_theme_dark') {
                        $('#kendoCSS').attr('href','bower_components/kendo-ui/styles/kendo.materialblack.min.css')
                    } else {
                        $('#kendoCSS').attr('href','bower_components/kendo-ui/styles/kendo.material.min.css');
                    }
                }

            });

            // hide style switcher
            $document.on('click keyup', function(e) {
                if( $switcher.hasClass('switcher_active') ) {
                    if (
                        ( !$(e.target).closest($switcher).length )
                        || ( e.keyCode == 27 )
                    ) {
                        $switcher.removeClass('switcher_active');
                    }
                }
            });

            // get theme from local storage
            if(localStorage.getItem("altair_theme") !== null) {
                $theme_switcher.children('li[data-app-theme='+localStorage.getItem("altair_theme")+']').click();
            }


        // toggle mini sidebar

            // change input's state to checked if mini sidebar is active
            if((localStorage.getItem("altair_sidebar_mini") !== null && localStorage.getItem("altair_sidebar_mini") == '1') || $body.hasClass('sidebar_mini')) {
                $mini_sidebar_toggle.iCheck('check');
            }

            $mini_sidebar_toggle
                .on('ifChecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.setItem("altair_sidebar_mini", '1');
                    localStorage.removeItem('altair_sidebar_slim');
                    location.reload(true);
                })
                .on('ifUnchecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.removeItem('altair_sidebar_mini');
                    location.reload(true);
                });

        // toggle slim sidebar

            // change input's state to checked if mini sidebar is active
            if((localStorage.getItem("altair_sidebar_slim") !== null && localStorage.getItem("altair_sidebar_slim") == '1') || $body.hasClass('sidebar_slim')) {
                $slim_sidebar_toggle.iCheck('check');
            }

            $slim_sidebar_toggle
                .on('ifChecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.setItem("altair_sidebar_slim", '1');
                    localStorage.removeItem('altair_sidebar_mini');
                    location.reload(true);
                })
                .on('ifUnchecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.removeItem('altair_sidebar_slim');
                    location.reload(true);
                });

        // toggle boxed layout

            if((localStorage.getItem("altair_layout") !== null && localStorage.getItem("altair_layout") == 'boxed') || $body.hasClass('boxed_layout')) {
                $boxed_layout_toggle.iCheck('check');
                $body.addClass('boxed_layout');
                $(window).resize();
            }

            $boxed_layout_toggle
                .on('ifChecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.setItem("altair_layout", 'boxed');
                    location.reload(true);
                })
                .on('ifUnchecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.removeItem('altair_layout');
                    location.reload(true);
                });

        // main menu accordion mode
            if($sidebar_main.hasClass('accordion_mode')) {
                $accordion_mode_toggle.iCheck('check');
            }

            $accordion_mode_toggle
                .on('ifChecked', function(){
                    $sidebar_main.addClass('accordion_mode');
                })
                .on('ifUnchecked', function(){
                    $sidebar_main.removeClass('accordion_mode');
                });


        });
    </script>
    <!-- page specific plugins -->
    <!-- datatables -->
    <script src="bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
    <!-- datatables buttons-->
    <script src="bower_components/datatables-buttons/js/dataTables.buttons.js"></script>
    <script src="assets/js/custom/datatables/buttons.uikit.js"></script>
    <script src="bower_components/jszip/dist/jszip.min.js"></script>
    <script src="bower_components/pdfmake/build/pdfmake.min.js"></script>
    <script src="bower_components/pdfmake/build/vfs_fonts.js"></script>
    <script src="bower_components/datatables-buttons/js/buttons.colVis.js"></script>
    <script src="bower_components/datatables-buttons/js/buttons.html5.js"></script>
    <script src="bower_components/datatables-buttons/js/buttons.print.js"></script>

    <!-- datatables custom integration -->
    <script src="assets/js/custom/datatables/datatables.uikit.min.js"></script>

    <!--  datatables functions -->
    <script src="assets/js/pages/plugins_datatables.min.js"></script>
</body>

</html>