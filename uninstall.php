<?php
// 判断是不是从 WordPress 后台调用的
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit;
}
delete_option("traum_toutiao_setting_check_box_enble");
delete_option("traum_toutiao_setting_appkey");
delete_option("traum_toutiao_setting_account");
delete_option("traum_toutiao_setting_password");
delete_option("traum_toutiao_setting_cover");
delete_option("traum_toutiao_setting_log_enble");