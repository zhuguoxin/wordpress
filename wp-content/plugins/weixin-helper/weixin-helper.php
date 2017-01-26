<?php
/*
Plugin Name: 微信群发助手(WeChat Helper)
Author: 水脉烟香
Author URI: http://www.smyx.net/
Plugin URI: http://blogqun.com/weixin-helper.html
Description: 使用微信、易信、微博粉丝服务的[高级群发接口]实现WordPress自动群发给用户
Version: 1.0
*/

define("WEIXIN_HELPER_URL", plugins_url('weixin-helper'));

if (!function_exists('installed_zend')) {
	function installed_zend() {
		if (version_compare(PHP_VERSION, '5.5', '>=')) {
			$zend_install_tips = __('Sorry, you cannot use this paid plugin, ZEND is not support for php 5.5.x or above.', 'wechat');
		} else {
			$zend_loader_enabled = function_exists('zend_loader_enabled');
			if ($zend_loader_enabled) {
				$zend_loader_version = function_exists('zend_loader_version') ? zend_loader_version() : '';
				if (version_compare($zend_loader_version, '3.3', '>=')) {
					if (version_compare(PHP_VERSION, '5.4', '>=')) {
						return '/zend2/';
					} elseif (version_compare(PHP_VERSION, '5.3', '>=')) {
						return '/zend/';
					} else {
						return '/';
					} 
				} else {
					$zend_install_tips = __('Sorry, you cannot use this paid plugin, ZEND version is not up to date, please update to at least 3.3.0<a href="http://www.zend.com/en/products/guard/downloads" target="_blank">View</a>', 'wechat');
				} 
			} else {
				if (version_compare(PHP_VERSION, '5.3', '>=')) {
					$zend_install_tips = __('Sorry, you cannot use this paid plugin, please contact your server company <a href="http://www.zend.com/en/products/guard/downloads" target="_blank">Zend Guard Loader</a>.', 'wechat');
				} else {
					$zend_install_tips = __('Sorry, you cannot use this paid plugin, please contact your server company <a href="http://www.zend.com/en/products/guard/downloads-prev" target="_blank">Zend Optimizer</a>.', 'wechat');
				} 
			} 
		} 
		return array('error' => $zend_install_tips);
	} 
} 

function weixin_helper_init() {
    load_plugin_textdomain( 'wechat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'weixin_helper_init' );

add_action('admin_menu', 'weixin_helper_add_page');
function weixin_helper_add_page() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('WeChat Helper', 'wechat'), __('WeChat Helper', 'wechat'), 'manage_options', 'weixin-helper', 'weixin_helper_do_page', WEIXIN_HELPER_URL .'/images/weixin-logo.png');
	} 
}
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'weixin_helper_plugin_actions');
function weixin_helper_plugin_actions ($links) {
    $new_links = array();
    $new_links[] = '<a href="admin.php?page=weixin-helper">' . __('Settings') . '</a>';
    return array_merge($new_links, $links);
}
// 群发
function weixin_helper_do_page() {
	$tag = !empty($_GET['tag']) ? $_GET['tag'] : 'setting';
	$weixin_url = WEIXIN_HELPER_URL;
	?>
<div class="wrap">
  <h2><img src="<?php echo $weixin_url .'/images/icon_weixin.png';?>" /><?php _e('WeChat Helper', 'wechat');?></h2>
<ul class='subsubsub'>
	<li><a href='?page=weixin-helper'<?php echo ($tag == 'setting') ? 'class="current"':'';?>>群发设置</a> |</li>
	<li><a href='?page=weixin-helper&tag=hand'<?php echo ($tag == 'hand') ? 'class="current"':'';?>>手动群发</a> |</li>
	<li><a href='?page=weixin-helper&tag=log'<?php echo ($tag == 'log') ? 'class="current"':'';?>>群发记录(包括素材)</a></li>
</ul>
<br class="clear" />
<?php
if ($tag == 'setting') {
	$installed_zend = installed_zend();
	if (is_array($installed_zend)) {
		echo '<div class="updated">';
		echo '<p><strong>' . $installed_zend['error'] . '</strong></p>';
		echo '</div>';
	}
?>
<p>以下功能仅限微信认证用户、易信需要满200粉丝才能申请接口权限，新浪微博加V用户可以使用，包括：</p>

<p>1. 使用微信、易信、微博粉丝服务的[高级群发接口]实现WordPress自动群发给用户。</p>
<p>2. 支持手动群发和自动定时群发，目前仅支持文本和图文，图文根据文章自动生成，免除您手动上传素材的烦恼。</p>
<p>3. 微信群发内容支持在正文头部和底部插入文字或者图片，用来引导用户关注等。</p>
<p>4. 支持从微信公众平台网站同步素材到本地，方便在您的网站一键群发，也支持在本地创建群发素材，其中永久素材能同步到微信公众平台网站。</p>
<p>5. 支持客服群发，即不限制群发次数，但是只能群发给48小时内跟您的公众号有互动的粉丝（比如用户发送消息，关注/订阅事件，点击自定义菜单，扫描二维码事件等）。</p>
<p>零售价：199元（人民币）或者 $37 USD。购买地址: <a href="http://blogqun.com/weixin-helper.html" target="_blank">http://blogqun.com/weixin-helper.html</a></p>
<p>购买后，请在<a href="http://blogqun.com/download" target="_blank">这里</a>下载安装包上传。可以先删除这个插件的目录<code>weixin-helper</code>，在wp后台上传zip安装插件或者ftp使用<a href="http://blogqun.com/help/ftp.html" target="_blank">二进制上传</a>。</p>
<?php } elseif ($tag == 'hand') {
?>
	<table class="form-table" id="handSetting">
		<tr>
          <td width="200" valign="top">消息类型</td>
		  <td><select id="handType">
			  <option value="1"<?php selected($type === 1);?>>图文</option>
			  <option value="0"<?php selected($type === 0);?>>文本</option>
			</select></td>
		</tr>
		<tr id="content1"<?php if($type === 0) echo 'style="display:none"';?>>
          <td width="200" valign="top">发送内容</td>
		  <td><select id="handMsg">
			  <option value="1"<?php selected($msg == 1);?>>今日最新文章</option>
			  <option value="2"<?php selected($msg == 2);?>>今日热门文章(需要安装WP-PostViews插件)</option>
			  <option value="9"<?php selected($msg == 9);?>>自定义内容</option>
			  <?php if($msg == 3) { ?>
			  <option value="3"<?php selected($msg == 3);?>>微信图文素材(<?php echo $getid;?>)</option>
			  <?php } ?>
			</select></td>
		</tr>
		<tr id="content2"<?php if(!$content) echo 'style="display:none"';?>>
          <td width="200" valign="top">自定义内容</td>
          <td><textarea id="handContent" cols="60" rows="4"><?php echo $content;?></textarea><br>如果选择“图文”，自定义内容处请填写文章ID，多篇文章用英文逗号隔开，如: 1,2,3</td>
		</tr>
		<tr>
          <td width="200" valign="top">
			  <?php if($msg == 3) { ?>
			  <input type="hidden" value="<?php echo $getid;?>" id="getid" />
			  <?php } ?>
		  </td>
          <td>
		  <p><label><input type="checkbox" value="weixin" id="site0"<?php echo $site0;?> />发给微信粉丝</label> （<label><input type="checkbox" value="1" id="material"<?php echo $material;?> />创建永久素材<code>(不推荐)</code></label>） <span id="ret0"></span></p>
		  <p><label><input type="checkbox" value="yixin" id="site1"<?php echo $site1;?> />发给易信粉丝</label> <span id="ret1"></span></p>
		  <p><label><input type="checkbox" value="weibo" id="site2"<?php echo $site2;?> />发给微博粉丝</label> <span id="ret2"></span></p>
		  </td>
		</tr>
	</table>
	<p><input class="button button-primary" type="button" id="send2" value="测试群发" /> <input class="button button-primary" type="button" id="send1" value="立即群发" /> <input class="button button-primary" type="button" id="send3" value="客服群发" /></p>
	<p><input class="button button-primary" type="button" id="send0" value="预览" /> <input class="button button-primary" type="button" id="send4" value="创建临时素材" /> <input class="button button-primary" type="button" id="send5" value="创建永久素材" /></p>
	<div id="massPreview"></div>
	<h2>使用说明</h2>
	<p>【临时素材】和【永久素材】的区别 - 永久素材会在<a target="_blank" href="http://mp.weixin.qq.com/">微信公众平台</a>网站的[素材管理]中显示，而临时素材不会显示，并且在微信服务器仅保留3天，如果下次还需使用，会再次上传素材。</p>
	<p>【测试群发】 - 顾名思义就是在APP中预览群发后的效果，需要在[群发设置]绑定测试帐号。</p>
	<p>【立即群发】和【客服群发】的区别 - 目前微信订阅号每日可以群发1条，服务号每月4条，易信和微博每日1条，发完就不能继续群发了（提示：跟公众平台网站共用配额）。而客服群发没有限制次数，但是只能群发给48小时内跟您的公众号有互动的粉丝（比如用户发送消息，关注/订阅事件，点击自定义菜单，扫描二维码事件等）</p>
<?php } ?>
</div>
<?php }