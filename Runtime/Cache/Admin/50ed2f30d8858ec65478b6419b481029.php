<?php if (!defined('THINK_PATH')) exit();?><script language="javascript">$(function(){
	var th = $(".top").height();
	th = 111-th
	var wh = $(window).height()-th;
	var cw = $(window).width()-177;
	whs = 125;
	$("#newcomm").panel({
		title:'系统更新',
		doSize:true,
		height:whs,
		collapsible:true
	});
	
	$("#comm").panel({
		title:'系统信息',
		doSize:true,
		collapsible:true
	});
	
	whs = wh-25-125-116;
	$("#copy").panel({
		title:'程序团队',
		doSize:true,
		height:whs,
		collapsible:true
	});
	
	whs = (wh-33)/2;
	$("#today").panel({
		title:'工作管理',
		doSize:true,
		height:whs,
		collapsible:true
	});
	
	whs = (wh-25)/2;
	$("#info").panel({
		title:'信息总计',
		doSize:true,
		height:whs,
		collapsible:true
	});
});


function onCheckVer(){
	$.messager.progress();
	var url = 'http://'+window.location.host;
	var x = $.getJSON("http://server.piocms.com/dwuss/index.php/Admin/project/upload?callback=?",{mode:'Dream', serial:'<?php echo $serial ?>', ver:'<?php echo $ver[0] ?>' ,domain:url, key:'e1a111321d2cc0c2ba779e7ccd43994d'}, function(data){
		//alert(data.version);
		$.messager.progress('close');
		$.get('__APP__?m=index&a=upver',function(ver){
			$("#vertext").html(ver);
		});
		if(data.soft=='False'){
			$.messager.alert('提示','当前已经是最新版本！','info');
		}else{
			$.messager.confirm('提示','以检测到新的版本，是否下载？',function(r){
				if(r){
					document.location = '__APP__?m=index&a=downver&soft='+data.soft;
				}
			});
		}
		return;
	});
	$.messager.progress('close');
}
</script><div class="con"><table width="100%" border="0" cellspacing="1" cellpadding="0"><tr><td width="56%" valign="top"><div id="newcomm"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td width="18%" height="96" valign="middle"><img style="margin-left:3px;" src="__ITEM__/__ADMIN.IMG__/main-logo.jpg" width="92" height="92" /></td><td width="82%" valign="middle"><p style="font-weight:bold; color:#039" id="vertext">当前版本：<?php echo ($ver["0"]); ?>&nbsp;&nbsp;&nbsp;&nbsp;最后检测时间：<?php echo ($ver["1"]); ?></p><p style="margin-top:12px"><a href="javascript:void(0);" onclick="javascript:onCheckVer()" id="su" class="easyui-linkbutton" data-options="iconCls:'icon-reload'">检查更新</a></p></td></tr></table></div><div class="tb-line"></div><div id="comm" style="padding:2px"><table class="infobox table-border" width="100%" border="0" cellspacing="0" cellpadding="0"><tr style="height:23px; line-height:23px;"><td width="18%">服务器名称</td><td width="32%"><?php echo $_SERVER['SERVER_NAME']; ?></td><td width="18%">服务器IP</td><td width="32%"><?php echo strstr($_SERVER['SERVER_SOFTWARE'],'Microsoft')?$_SERVER['LOCAL_ADDR']:$_SERVER['SERVER_ADDR']; ?></td></tr><tr style="height:23px; line-height:23px;"><td width="18%">服务器域名</td><td width="32%"><?php echo $_SERVER['HTTP_HOST']; ?></td><td width="18%">操作系统</td><td width="32%"><?php echo PHP_OS; ?></td></tr><tr style="height:23px; line-height:23px;"><td width="18%">PHP版本</td><td width="32%"><?php echo PHP_VERSION; ?></td><td width="18%">运行状态</td><td width="32%"><?php echo C('CFG_ON')==1?"运行中":"维护中"; ?></td></tr></table></div><div class="tb-line"></div><div id="copy" style="padding:2px"><table class="infobox table-border" width="100%" border="0" cellspacing="0" cellpadding="0"><tr style="height:23px; line-height:23px;"><td width="29%">主程序开发</td><td><a href="http://www.d-winner.com/" target="_blank">梦赢科技</a>、<a href="http://www.95cms.cn/" target="_blank">九五互联</a></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">赞助商</td><td><a href="http://www.075595.com/" target="_blank">95数据中心</a></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">鸣谢</td><td>ThinkPHP、EasyUI</td></tr></table></div></td><td width="1%" valign="top">&nbsp;</td><td width="43%" valign="top"><div id="today" style="padding:2px"><table class="infobox table-border" width="100%" border="0" cellspacing="0" cellpadding="0"><tr style="height:23px; line-height:23px;"><td width="29%">待审核日志</td><td><?php echo $app->getTotal('Trip_daily','status=2') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">未解决问题</td><td><?php echo $app->getTotal('Report_table','status<5') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">规划中项目</td><td><?php echo $app->getTotal('Project_table','status=1') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">调试中项目</td><td><?php echo $app->getTotal('Project_table','status=2') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">已完成项目</td><td><?php echo $app->getTotal('Project_table','status=3') ?></td></tr></table></div><div class="tb-line"></div><div id="info" style="padding:2px;"><table class="infobox table-border" width="100%" border="0" cellspacing="0" cellpadding="0"><tr style="height:23px; line-height:23px;"><td width="29%">用户总数</td><td><?php echo $app->getTotal('User_table') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">角色总数</td><td><?php echo $app->getTotal('User_group_table') ?></td></tr><?php if(C('MORE_COMY')){ ?><tr style="height:23px; line-height:23px;"><td width="29%">子公司总数</td><td><?php echo $app->getTotal('User_company_table','type=0') ?></td></tr><?php } ?><tr style="height:23px; line-height:23px;"><td width="29%">部门总数</td><td><?php echo $app->getTotal('User_part_table') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">项目总数</td><td><?php echo $app->getTotal('Project_table') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">客户总数</td><td><?php echo $app->getTotal('User_company_table','type=1') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">BUG百科总数</td><td><?php echo $app->getTotal('Bug_table') ?></td></tr><tr style="height:23px; line-height:23px;"><td width="29%">出差日志总数</td><td><?php echo $app->getTotal('Trip_daily') ?></td></tr></table></div></td></tr></table><div align="center" style="line-height:23px">Copyright © 2010-2014 D-WINNER. <a href="http://www.d-winner.com/" target="_blank">梦赢科技</a> 版权所有</div></div>