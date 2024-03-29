<?php 
    session_start();
    require_once('assets/config/config.php');
    require_once('assets/config/checklogin.php');
    check_login();

    //transaction checksum
    $length = 15;    
    $checksum =  
    substr(str_shuffle('qwertyuiopasdfghjklzxcvbnm0123456789'),1,$length);

    /*
        > generate random 10 digit payment number
        > this is used only in sandbox mode or development environment
        > When deploying to live server please comment the following two lines.
        > This will be dummy output
          1. ZGSJYNDBKR
          2. KXOCAVYEZU
          3. LOQZWXAMCY
          etc

    */
    $SandboxCodeLength = 10;
    $SandboxPaymentCode = 
    substr(str_shuffle('QWERTYUIOPLKJHGFDSAZXCVBNM'),1,$SandboxCodeLength);
    
    //pay library fines
    if(isset($_POST['payLibraryFee']))
    {
            $error = 0;
            if (isset($_POST['f_payment_code']) && !empty($_POST['f_payment_code'])) {
                $f_payment_code=mysqli_real_escape_string($mysqli,trim($_POST['f_payment_code']));
            }else{
                $error = 1;
                $err="Payment code cannot be empty";
            }
            
            if (isset($_POST['f_checksum']) && !empty($_POST['f_checksum'])) {
                $f_checksum=mysqli_real_escape_string($mysqli,trim($_POST['f_checksum']));
            }else{
                $error = 1;
                $err="Payment Checksum cannot be empty";
            }
            if(!$error)
            {
                $sql="SELECT * FROM  iL_Fines WHERE  f_payment_code='$f_payment_code' || f_checksum='$f_checksum'";
                $res=mysqli_query($mysqli,$sql);
                if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($f_payment_code==$row['f_payment_code'])
                {
                    $err="Payment Code already exists";
                }
                else
                {
                    $err="Payment Checksum already exists";
                }
            }
            else
            {
                $f_payment_code = $_POST['f_payment_code'];
                $f_status = $_POST['f_status'];
                $f_checksum = sha1($_POST['f_checksum']);
                $fineId = $_GET['fineId'];

                //---Post a notification that someone has cleared some fine//
                $content = $_POST['content'];
                $user_id = $_SESSION['s_id'];

                
                //Insert Captured information to a database table
                $query="UPDATE iL_Fines SET f_payment_code = ?, f_status = ?, f_checksum =? WHERE f_id= ?";
                $notif = "INSERT INTO iL_notifications (content,user_id) VALUES(?,?)";

                $stmt2 = $mysqli->prepare($notif);
                $stmt = $mysqli->prepare($query);
                //bind paramaters
                $rc=$stmt->bind_param('sssi', $f_payment_code, $f_status, $f_checksum, $fineId);
                $rc = $stmt2->bind_param('si', $content, $user_id);

                $stmt2 ->execute();
                $stmt->execute();
        
                //declare a varible which will be passed to alert function
                if($stmt && $stmt2)
                {
                    $success = "Payment Confirmed" && header("refresh:1;url=pages_std_manage_finances.php");
                }
                else 
                {
                    $err = "Please Try Again Or Try Later";
                }
            }
        }      
    }
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
    <?php
        $fineId = $_GET['fineId'];
        $ret="SELECT * FROM  iL_Fines WHERE f_id = ? "; 
        $stmt= $mysqli->prepare($ret) ;
        $stmt->bind_param('s', $fineId);
        $stmt->execute() ;//ok
        $res=$stmt->get_result();
        while($row=$res->fetch_object())
    {
    ?>
        <div id="page_content">
            <!--Breadcrums-->
            <div id="top_bar">
                <ul id="breadcrumbs">
                    <li><a href="pages_std_dashboard.php">Dashboard</a></li>
                    <li><a href="#">Finances</a></li>
                    <li><a href="pages_std_manage_finances.php">Manage Finances</a></li>
                    <li><span>Pay Library Fee</span></li>
                </ul>
            </div>

            <div id="page_content_inner">

                <div class="md-card">
                    <div class="md-card-content">
                        <h3 class="heading_a">Please Fill All Fields</h3>
                        <hr>
                        <form method="post">
                            <div class="uk-grid" data-uk-grid-margin>
                                <div class="uk-width-medium-2-2">
                                    <div class="uk-form-row">
                                        <label>Penalty For</label>
                                        <input type="text" required readonly value="<?php echo $row->f_type;?>" name="f_type" class="md-input" />
                                    </div>
                                    <div class="uk-form-row">
                                        <label>Penalty Amount (₱)</label>
                                        <input type="text" value="<?php echo $row->f_amt;?>" required name="f_amt" class="md-input" />
                                    </div>
                                    <div class="uk-form-row">
                                        <label>Payment Code (Enter Payment Code Given By LiBlio.info )</label>
                                        <input type="text" required  value="<?php echo $SandboxPaymentCode;?>" name="f_payment_code" class="md-input label-fixed" />
                                    </div>

                                    <div class="uk-form-row" style="display:none">
                                        <label>Payment Status</label>
                                        <input type="text" required  name="f_status" value="Paid" class="md-input label-fixed" />
                                    </div>
                                    <div class="uk-form-row" style="display:non">
                                        <label>Payment Checksum</label>
                                        <input type="text" required  name="f_checksum" value="<?php echo $checksum;?>" class="md-input label-fixed" />
                                    </div>

                                     <!--Notification Content-->
                                     <div class="uk-form-row" style="display:none">
                                        <label>Content</label>
                                        <input type="text" required name="content" value="Ksh <?php echo $row->f_amt;?> Has been paid as a fine for <?php echo $row->f_type;?>" class="md-input"  />
                                    </div>
                                
                                </div>

                                <div class="uk-width-medium-2-2">
                                    <div class="uk-form-row">
                                        <div class="uk-input-group">
                                            <input type="submit" class="md-btn md-btn-success" name="payLibraryFee" value="Pay Library Penalty" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    <?php }?>
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
</body>

</html>