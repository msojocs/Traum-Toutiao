<?php

//设置
function traum_toutiao_menu() {
    $icon_url = plugins_url('/img/favicon.ico', __FILE__);
    add_menu_page(
        'Traum 头条',
        'Traum 头条',
        'administrator',
        'traum_toutiao_setting_page',
        'traum_toutiao_setting_page_dispaly',
        $icon_url
    );
}
add_action('admin_menu','traum_toutiao_menu');

function traum_toutiao_plugin_options() {
    //setting
    add_settings_section(
        'traum_toutiao_setting_page',                         // ID used to identify this section and with which to register options
        'Traum toutiao 设置',                               // Title to be displayed on the administration page
        'traum_toutiao_setting_page_callback',                // Callback used to render the description of the section
        'traum_toutiao_setting_page'                          // Page on which to add this section of options
    );

    //enble？
    add_settings_field(
        'traum_toutiao_setting_check_box_enble',                      // ID used to identify the field throughout the theme
        '总是启用插件',                           // The label to the left of the option interface element
        'traum_toutiao_setting_check_box_callback',  // The name of the function responsible for rendering the option interface
        'traum_toutiao_setting_page',                          // The page on which this option will be displayed
        'traum_toutiao_setting_page',
        array(
            '启用此处时,写文章页面的同步选项将失效'
            )
    );
    register_setting(
        'traum_toutiao_setting_page',//page
        'traum_toutiao_setting_check_box_enble'//field ID
    );

    //账号输入
    add_settings_field(
        'traum_toutiao_setting_account',                      // ID used to identify the field throughout the theme
        '账号',                           // The label to the left of the option interface element
        'traum_toutiao_setting_account_callback',  // The name of the function responsible for rendering the option interface
        'traum_toutiao_setting_page',                          // The page on which this option will be displayed
        'traum_toutiao_setting_page',
        array(
            '新浪账号'//传送给数组
        )
    );
    register_setting(
        'traum_toutiao_setting_page',//page
        'traum_toutiao_setting_account'//field ID
    );

    //密码表单
    add_settings_field(
        'traum_toutiao_setting_password',                      // ID used to identify the field throughout the theme
        '密码',                           // The label to the left of the option interface element
        'traum_toutiao_setting_password_callback',  // The name of the function responsible for rendering the option interface
        'traum_toutiao_setting_page',                          // The page on which this option will be displayed
        'traum_toutiao_setting_page',
        array(
            '新浪密码'//传送给数组
        )
    );
    register_setting(
        'traum_toutiao_setting_page',//page
        'traum_toutiao_setting_password'//field ID
    );

    //AppKey表单
    add_settings_field(
        'traum_toutiao_setting_appkey',                      // ID used to identify the field throughout the theme
        'AppKey',                           // The label to the left of the option interface element
        'traum_toutiao_setting_appkey_callback',  // The name of the function responsible for rendering the option interface
        'traum_toutiao_setting_page',                          // The page on which this option will be displayed
        'traum_toutiao_setting_page',
        array(
            '<br>新浪AppKey，需要到<a href="https://open.weibo.com/apps" >开发平台</a>获取，且该应用需获得头条文章权限'//传送给数组
        )
    );
    register_setting(
        'traum_toutiao_setting_page',//page
        'traum_toutiao_setting_appkey'//field ID
    );
    
    //封面表单
    add_settings_field(
        'traum_toutiao_setting_cover',                      // ID used to identify the field throughout the theme
        '默认封面',                           // The label to the left of the option interface element
        'traum_toutiao_setting_cover_callback',  // The name of the function responsible for rendering the option interface
        'traum_toutiao_setting_page',                          // The page on which this option will be displayed
        'traum_toutiao_setting_page',
        array(
            '在发布的文章没有任何图片时作为头条文章封面'//传送给数组
        )
    );
    register_setting(
        'traum_toutiao_setting_page',//page
        'traum_toutiao_setting_cover'//field ID
    );

    //日志
    add_settings_field(
        'traum_toutiao_setting_log_enble',                      // ID used to identify the field throughout the theme
        '启用日志',                           // The label to the left of the option interface element
        'traum_toutiao_setting_log_callback',  // The name of the function responsible for rendering the option interface
        'traum_toutiao_setting_page',                          // The page on which this option will be displayed
        'traum_toutiao_setting_page'
    );
    register_setting(
        'traum_toutiao_setting_page',//page
        'traum_toutiao_setting_log_enble'//field ID
    );

}
add_action('admin_init', 'traum_toutiao_plugin_options');

function traum_toutiao_setting_page_callback() {
    echo '设置页面';
}


function traum_toutiao_setting_page_dispaly() {
    echo '<h2>Let`s start!</h2>';
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('traum_toutiao_setting_page');
        ?>
        <?php do_settings_sections('traum_toutiao_setting_page');
        ?>
        <?php submit_button();
        ?>
    </form>
    <form method="post">
    <?php wp_nonce_field(plugin_basename(__FILE__), 'traum_toutiao_setting_noncename'); ?>
        <input type="submit" class="button button-secondary" value="删除日志" />
        <input type="hidden" name="traum_toutiao_setting_log_delete" value="log_delete">
    </form>

    <?php
    if (! isset($_POST['traum_toutiao_setting_noncename']) || ! wp_verify_nonce($_POST['traum_toutiao_setting_noncename'], plugin_basename(__FILE__)))
        return;
    if(sanitize_text_field($_POST['traum_toutiao_setting_log_delete']) == 'log_delete')traum_toutiao_log('delete');
}
// end sandbox_general_options_callback

function traum_toutiao_setting_check_box_callback($args) {
    // Note the ID and the name attribute of the element match that of the ID in the call to add_settings_field
    $html = '<input type="checkbox" id="traum_toutiao_setting_check_box_enble" name="traum_toutiao_setting_check_box_enble" value="1" ' . checked(1, get_option('traum_toutiao_setting_check_box_enble'), false) . '/>';
    echo $html.$args[0];
}
// end sandbox_toggle_header_callback

//账号
function traum_toutiao_setting_account_callback($args) {
    $html = '<input type="text" id="traum_toutiao_setting_account" name="traum_toutiao_setting_account" value="'.get_option('traum_toutiao_setting_account').'" />';
    // Here, we'll take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="traum_toutiao_setting_account">' . $args[0] . '</label>';
    echo $html;
}

//密码
function traum_toutiao_setting_password_callback($args) {
    $html = '<input type="password" id="traum_toutiao_setting_password" name="traum_toutiao_setting_password" value="'.get_option('traum_toutiao_setting_password').'" />';
    // Here, we'll take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="traum_toutiao_setting_password">' . $args[0] . '</label>';
    echo $html;
}

//AppKey
function traum_toutiao_setting_appkey_callback($args) {
    $html = '<input type="text" id="traum_toutiao_setting_appkey" name="traum_toutiao_setting_appkey" value="'.get_option('traum_toutiao_setting_appkey').'" />';
    // Here, we'll take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="traum_toutiao_setting_appkey">' . $args[0] . '</label>';
    echo $html;
}

//Cover
function traum_toutiao_setting_cover_callback($args) {
    $html = '<input type="text" id="traum_toutiao_setting_cover" name="traum_toutiao_setting_cover" value="'.get_option('traum_toutiao_setting_cover').'" />';
    // Here, we'll take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="traum_toutiao_setting_cover">' . $args[0] . '</label>';
    echo $html;
}

//日志
function traum_toutiao_setting_log_callback($args) {
    // Note the ID and the name attribute of the element match that of the ID in the call to add_settings_field
    $html = '<input type="checkbox" id="traum_toutiao_setting_log_enble" name="traum_toutiao_setting_log_enble" value="1" ' . checked(1, get_option('traum_toutiao_setting_log_enble'), false) . '/>';
    echo $html.'<br />';
    traum_toutiao_log('read');
}
// end sandbox_toggle_header_callback

//处理日志
function traum_toutiao_log($action) {
    $file = Traum_Toutiao_DIR."log/traum_weibo.log";
    if ($action == 'read') {
        if (file_exists($file) && get_option('traum_toutiao_setting_log_enble')) {
            $file = fopen($file, "r") or exit("Unable to open file!");
            //Output a line of the file until the end is reached
            //feof() check if file read end EOF
            while (!feof($file)) {
                //fgets() Read row by row
                $temp = fgets($file);
                if(strpos($temp, 'error_code'))
                {
                    $array = json_decode($temp, true);
                    switch($array['error_code'])
                    {
                        case 10001:
                            echo '新浪系统错误';
                            break;
                        case 11001:
                            echo '发布过于频繁';
                            break;
                        case 11002:
                            echo '微博发送失败';
                            break;
                        case 11003:
                            echo '文章关联微博失败';
                            break;
                        case 10008:
                            echo '参数不符合要求：'.$array['error'];
                            break;
                        case 21301:
                            echo '账号信息似乎出错了~';
                            break;
                        default:
                            echo '未知错误';
                            break;
                    }
                }
                else
                    echo '<br />'.$temp. "<br />";
            }
            fclose($file);
        }
    } else if($action == 'delete') {
        if (!unlink($file)) {
            echo ("Error deleting$file");
        } else
        {
            echo ("删除成功$file");
        }
    }else return;

}