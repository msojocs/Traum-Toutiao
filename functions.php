<?php
/*
    Plugin Name: Traum 新浪头条
    Plugin URI: https://www.jysafe.cn/3632.air
    Description: 同步文章到新浪头条
    Author: Traum
    Version: 1.0.2
    Author URI: https://www.jysafe.cn
    */
?>
<?php
if (!defined('Traum_Toutiao_DIR')) {
    define('Traum_Toutiao_DIR', plugin_dir_path(__FILE__));
}

require plugin_dir_path(__FILE__) .'options.php';

add_action('post_submitbox_misc_actions', 'traum_toutiao_publish_metabox_add');
function traum_toutiao_publish_metabox_add() {
    //global $post_ID;
    // 使用随机数进行核查
    //wp_nonce_field(plugin_basename(__FILE__), 'traum_toutiao_publish_checkbox_noncename');
    echo '<div class="misc-pub-section"><input type="checkbox" id="traum_toutiao_publish_checkbox" name="traum_toutiao_publish_checkbox" './* checked(1, get_option('traum_toutiao_setting_check_box_enble').*/' />  同时发布头条文章</div>';
}
function traum_toutiao_active(){
    if (false == get_option('traum_toutiao_setting_check_box_enble')) {
        add_option('traum_toutiao_setting_check_box_enble');
    }
    if (false == get_option('traum_toutiao_setting_appkey')) {
        add_option('traum_toutiao_setting_appkey');
    }
    if (false == get_option('traum_toutiao_setting_account')) {
        add_option('traum_toutiao_setting_account');
    }
    if (false == get_option('traum_toutiao_setting_password')) {
        add_option('traum_toutiao_setting_password');
    }
    if (false == get_option('traum_toutiao_setting_cover')) {
        add_option('traum_toutiao_setting_cover');
    }
    if (false == get_option('traum_toutiao_setting_log_enble')) {
        add_option('traum_toutiao_setting_log_enble');
    }

}
register_activation_hook( __FILE__, 'traum_toutiao_active' );
/* 写入数据*/
//add_action( 'save_post', 'traum_toutiao_save_publish_checkbox' );
/* 文章保存时，保存我们的自定义数据*/
/*function traum_toutiao_save_publish_checkbox($post_id) {

    // 首先，我们需要检查当前用户是否被授权做这个动作。
    if ('page' == $_POST['post_type']) {
        if (! current_user_can('edit_page', $post_id))
            return;
    } else {
        if (! current_user_can('edit_post', $post_id))
            return;
    }
    //ntraum_toutiao_loginfo('开始验证nonce');
    // 其次，我们需要检查，是否用户想改变这个值。
    if (! isset($_POST['traum_toutiao_publish_checkbox_noncename']) || ! wp_verify_nonce($_POST['traum_toutiao_publish_checkbox_noncename'], plugin_basename(__FILE__)))
        return;

    // 第三，我们可以保存值到数据库中

    //如果保存在自定义的表，获取文章ID
    $post_ID = $_POST['post_ID'];
    //过滤用户输入
    $mydata = sanitize_text_field($_POST['traum_toutiao_publish_checkbox']);
    // 使用$mydata做些什么
    // 或者使用
    add_post_meta($post_ID, 'traum_toutiao_publish_checkbox', $mydata, true) or
    update_post_meta($post_ID, 'traum_toutiao_publish_checkbox', $mydata);
    // 或自定义表（见下面的进一步阅读的部分）
}
*/

/**
 * WordPress 同步文章到新浪微博头条文章 By 无主题博客
 * 完善修正 By 祭夜
 * 修正内容：
 * 1.部分代码错误
 * 2.修复同步到头条文章时HTML代码被去掉的问题
 * 3.增加头条文章封面
 * 4.添加博客文章的标签关联到新浪话题
 * 原文地址: http://wuzhuti.cn/2715.html
 */
function traum_toutiao_publish($post_ID) {
    if (!get_option('traum_toutiao_setting_check_box_enble'))
        if (sanitize_text_field($_POST['traum_toutiao_publish_checkbox']) != 'on')
        return;
    //if (wp_is_post_revision($post_ID))
    //修订版本(更新)不发微博
    $get_post_info = get_post($post_ID);
    $get_post_centent = get_post($post_ID)->post_content;
    $get_post_title = get_post($post_ID)->post_title;
    if ($get_post_info->post_status == 'publish') {
        $appkey = get_option('traum_toutiao_setting_appkey');
        //key
        $username = get_option('traum_toutiao_setting_account');
        //用户名
        $pass = get_option('traum_toutiao_setting_password');
        //密码

        $request = new WP_Http;

        //获取文章标签关键词
        $tags = wp_get_post_tags($post_ID);
        foreach ($tags as $tag) {
            $keywords = $keywords.'#'.$tag->name."#";
        }

        $status = '【' . strip_tags($get_post_title) . '】 ' . mb_strimwidth(strip_tags(apply_filters('the_content', $get_post_centent)) , 0, 132, ' ');
        $api_url = 'https://api.weibo.com/proxy/article/publish.json';

        //头条的标题
        $title = strip_tags($get_post_title);
        //头条的正文
        $content = get_post($post_ID)->post_content."\r\n原文:<a href=" . get_permalink($post_ID).">点击查看</a>";
        $content = traum_toutiao_handle_content($content);
        //头条的封面
        $cover = traum_toutiao_mmimg($post_ID);
        //头条的导语
        $summary = mb_strimwidth(strip_tags(apply_filters('the_content', $get_post_centent)) , 0, 110, '...');
        //微博的内容
        $text = mb_strimwidth(strip_tags(apply_filters('the_content', $get_post_centent)) , 0, 110, $status).$keywords.'原文地址:' . get_permalink($post_ID);

        $body = array(
            'title' => $title,
            'content' => $content,
            'cover' => $cover,
            'summary' => $summary,
            'text' => $text,
            'source' => $appkey
        );

        $headers = array('Authorization' => 'Basic ' . base64_encode("$username:$pass"));
        $result = $request->post($api_url, array('body' => $body,'headers' => $headers));
        $res = "\r\n===============LOG START===============\r\n\r\n--------Picture:--------\r\n{$body['cover']} \r\n\r\n--------KEY Words:--------\r\n$keywords\r\n\r\n--------Sina Response:---------\r\n{$result['body']}===============LOG END===============";
        if (get_option('traum_toutiao_setting_log_enble'))
            traum_toutiao_loginfo($res);
    }
}
//给发布文章增加一个分享微博头条文章的动作
add_action('publish_post', 'traum_toutiao_publish', 0);

//获取封面
function traum_toutiao_mmimg($postID) {
    $cti = traum_catch_that_image($postID);
    //得到$first_img的值，并赋值给$cti
    $showimg = $cti;
    //将$cti的值赋值给$showimg
    has_post_thumbnail();
    if (has_post_thumbnail()) {
        //判断是否有特色图片，有则将$showimg的值替换为特色图片的地址，否则不变
        $thumbnail_image_url = wp_get_attachment_image_src(get_post_thumbnail_id(),'thumbnail');
        $shareimg = $thumbnail_image_url[0];
    } else {
        $shareimg = $showimg;
    }
    ;
    return $shareimg;
}
//调用代码：mmimg($post_ID)

function traum_catch_that_image($postID) {
    $first_img = '';
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',get_post($postID)->post_content,$matches);
    $first_img = $matches[1][0];
    //将文章第一张图片的地址赋值给$first_img
    if (empty($first_img)) {
        //文章第一张图为空，也就是整篇文章没有图片，将默认设置的图片的地址赋值给$first_img
        $popimg = get_option('traum_toutiao_setting_cover');
        $first_img = $popimg;
    }
    return $first_img;
}

//处理content内容
function traum_toutiao_handle_content($content) {
    if (!strpos($content, "<h1>") && strpos($content, "<h2>") && (strpos($content, "<h3>") || strpos($content, "<h4>") || strpos($content, "<h5>") ||strpos($content, "<h6>"))) {
        $content = str_replace("<h2>", "<h1>", $content);
        $content = str_replace("</h2>", "</h1>", $content);
    }

    $content = preg_replace("/\[\/?[a-z]+_[a-z]+\]/","",$content);
    $content = str_replace(array("<br>", "<br />"), "&lt;br&gt;", $content);
    $content = str_replace(array("\r\n", "\r", "\n"), "<br>", $content);
    $content = str_replace('code>', "b>", $content);
    return $content;
}

//写日志函数
function traum_toutiao_loginfo($msg) {
    $logFile = Traum_Toutiao_DIR.'log/traum_weibo.log';
    //日志路径
    date_default_timezone_set(get_option("TIMEZONE_STRING"));
    file_put_contents($logFile, date('[Y-m-d H:i:s]: ') . $msg . PHP_EOL, FILE_APPEND);
}