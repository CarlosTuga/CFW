
				<div id='flashmem'>
				<iframe name='dlframe' id='dlframe' src='blank.php' class='dl-frame ui-helper-hidden'></iframe>
				<div id='dLoad' class='ui-helper-hidden' title='Load'>
				<fieldset class='df ui-widget-content ui-corner-all'><div id='dlDialog_Path' class='ldialog-path'>*.*</div></fieldset>
				<div class='scroll-dialog-box scbload'>
				<div id='dLTree' class='diag-dtree ui-widget-content ui-corner-all'></div>
				</div>
				</div>
				<div id='dSave_As' class='ui-helper-hidden' title='Сохранить как'>
				<fieldset class='df ui-widget-content ui-corner-all'><label id='lsDialog_Path' for='sDialog_FileName' class='diag-dldialog-path'></label><input id='sDialog_FileName' name='sDialog_FileName' type='text' class='diag-dsdialog-ipt ui-corner-all'/></fieldset>
				<div class='scroll-dialog-box scbsave'>
				<div id='dSTree' class='diag-dtree ui-widget-content ui-corner-all'></div>
				</div>
				</div>
				<h2 align='right' class='tab-header'>Менеджер флэш-памяти <span class='header-tiny-text'>v1.3.1</span></h2>
				<div id='treecontainer' class='fm-container'>
				<table id='fmbox' class='window'>
				<tbody class='window'>
				<tr class='window-header ui-widget-header'>
				<th class='logoptions window-header ui-widget-header '><div class='dir-table'><span class='dir-left header-normal-text'>CFW-совместимая PS3: <span class='fmm-compat'><span id='cfwcompat'></span></span></span><span class='dir-center header-normal-text'></span><span id='spanmode' class='dir-right-fixed' title='Отключение безопасного режима Менеджера флэш-памяти очень рискованно. Проверки и ограничения исправлений отключаются, когда безопасный режим Менеджера флэш-памяти отключён. Используйте на свой страх и риск!!!'><div class='switch-wrapper pointer' tabindex='0'><input type='checkbox' name='mode' id='admode' value='true' checked='true' ></div></span></div></th>
				</tr>
				<tr class='window-content-top ui-widget-content'>
				<td align='justify' class='window-content-top ui-widget-content'>
				<div id='fTree' class='fm-tree ui-widget-content'></div>
				</td>
				</tr>
				<tr class='pl window-bottom-small'>
				<td align='justify' class='window-bottom-small'>
				<div class='sizer height-5px'>XXX</div>
				</td>
				</tr>
				</tbody>
				</table>
				<br>
				<div align='center' id='accordion' >
				  <h3>Инструкции</h3>
				  <div>
					<div align='left'>
						<ul>
							<li>Нажмите на различные узлы дерева Менеджера флэш-памяти, чтобы открыть доступные элементы контекстного меню.</li>
							<li>Контекстное меню Менеджера флэш-памяти доступно в том случае, если консоль определена как совместимая с CFW.</li>
						</ul>
					</div>
				  </div>
				  <h3>Советы</h3>
				  <div>
					<div align='left'>
						<ul>
							<li><b>Всегда держите безопасный режим Менеджера флэш-памяти включённым. </b><span class='header-small-text'>Выключенный безопасный режим предназначен ТОЛЬКО для РАЗРАБОТЧИКОВ, желающих использовать свои собственные патчи.</span></li>
							<li><b>Выключенный безопасный режим </b><span class='header-small-text'>позволит вам исправить флэш-память с любым файлом, независимо от его обнаруженной достоверности. Вы были предупреждены.</span></li>
							<li>Из соображений производительности избегайте использования каталогов хранения, содержащих в общей сложности более дюжины элементов (файлов и папок).</li>
							<li>Для удобства хэши SHA256, отображаемые для каждой области ROS флэш-памяти, рассчитываются на основе диапазона байтов 0x6FFE0, используемого стандартными файлами исправлений no-FSM.</li>
						</ul>
					</div>
				  </div>
				</div>
				</div>
				<div id='dfmProgress' class='diag-fmProgress ui-helper-hidden' title='Прогресс операций'></div>
				<div id='ulog' class='ui-helper-hidden'></div>
				</div>
				<script>
				jQuery('.refresh-fm').removeClass('ui-state-disabled').addClass('ui-state-disabled');
				var ldiag=null;
				var sdiag=null;
				var ft1 =null;
				var pbfm1=null;
				var dl_object=null;
				var sha256_ros='';
				if(!helper.sm){
					helper.sm = new sysmem();
				}
				if(!helper.worker['fmm']){
					helper.worker['fmm'] = new workerThread('BGTOOLSET_WKR_FMM');
				}
				function updatePD(o,st){
					pbfm1.updateProgressDialog(o,st);
				}
				function updateBuffer(b,s){
					ldiag.addPatchInfo(b,s);
				}
				function validatePatchFile(buf_po,filename){
					var _nor = so.is_nor();
					helper.memory.upokes(buf_po.offset,helper.patch_ros_fragment_start);
					if(!_nor){
						helper.memory.upokes(buf_po.offset,getActiveNandROS(so));
					}
					helper.memory.upokes(buf_po.offset+helper.patchfile_size+0x30,_nor ? helper.patch_ros_fragment_end1:helper.patch_ros_fragment_end2);
					Logger.info('getSHA256hash 0x'+(buf_po.offset+0x30).toString(16));
					sha256_ros = getSHA256hash(buf_po.offset+0x30, helper.patchfile_size);
					if(dl_object && buf_po===dl_object.buffer){dl_object.sha256 = sha256_ros;}
					updateBuffer(buf_po,sha256_ros);
					ulog('Извлечение SHA256 завершено');
					Logger.info('Файл патча '+filename+' SHA256 контрольная сумма: '+sha256_ros);
					ulog('Операции проверки патча завершены');
					if(sha256_ros!==helper.nofsm_hash){
						if(dl_object && buf_po===dl_object.buffer){toast('Ошибка загрузки патча','warning',5);}
						Logger.warn('Обнаружен кастомный файл патча.');
						return 1;
					}
					else{
						Logger.info('Обнаружен официальный файл патча.');
						return 0;
					}
				}
				function updateValidationGUI(start,filename){
					var jQftree = jQuery('#fTree').jstree(true);
					if(!jQftree.is_disabled('flashbk')){
						jQftree.create_node('flashbk',{'id' : 'rosbk', 'type' : 'ros', 'text' : 'ROS' });
						jQftree.create_node('rosbk',{'id' : 'infobk', 'type' : 'info', 'text' : 'SHA256: '+sha256_ros });
						jQftree.open_node('rosbk');
						jQftree.open_node('flashbk');
					}
					setTimeout(function(){
						helper.sp.playOK();
						pbfm1.updateProgressDialog({'dlabel':'Ожидание','glabel':'Файл патча \''+filename+'\' загружен и проверен','dvalue':100,'gvalue':100,'istatus':'success-image'},start);
					},250);
				}
				function updateNoValidationGUI(buf_po,start,filename){
					helper.sp.playNG();
					pbfm1.updateProgressDialog({'glabel':'Загрузка операций не удалась','dlabel':'Ошибка проверки файла','dvalue':100,'gvalue':100,'istatus':'error-image'},start);
					Logger.info('Неверный файл патча '+filename);
				}
				function ulog(ht,clean){
					var u = document.getElementById('ulog');
					if(clean){u.innerHTML='';}
					else{u.innerHTML+='<br>'+ht;}
					Logger.info(ht);
				}
				function dl_cancel(){
					helper.swf.cancelDownload();
					dl_object=null;
					helper.sp.playNG();
				}
				var so =null;
				jQuery('.preloader').removeClass('ui-helper-hidden');
				jQuery('#admode').switchButton({
					labels_placement: 'left',
					//checked: true,
					clear: false,
					on_label: 'Защита Вкл. ',
					off_label: 'Защита Откл.',
					on_callback: function (){
						jQuery(document).tooltip('disable');
						helper.fm_usermode = 0;
						jQuery(document).tooltip('enable');
					},
					off_callback: function(){
						jQuery(document).tooltip('disable');
						function confirmMode(){
							helper.fm_usermode = 1;
							jQuery(document).tooltip('enable');
						}
						confirmDialog('ВЫ НИКОГДА НЕ ДОЛЖНЫ ВЫКЛЮЧАТЬ безопасный режим Менеджера флэш-памяти!!!<br><br>Только опытные пользователи и разработчики должны когда-либо использовать Менеджер флэш-памяти с отключённым безопасным режимом. Вы были предупреждены.','Вы уверены что хотите продолжить?',confirmMode,null,function(ck){jQuery('#admode').switchButton('option','checked', ck);jQuery(document).tooltip('enable');},true);
					}
				});
				
				var sDialog = function(_name){
					var sdef = null;
					var jQtree = jQuery('#dSTree');
					var jQpt_fname = jQuery('input[name=sDialog_FileName]');
					var jQlbl_fname = jQuery('label[id=lsDialog_Path]');
					var fname = _name ? _name : 'dump.hex';
					var sel_path = '';
					var jQftree=null;
					var sd = this;
					var sobj = {
						'sector_count': 0x77800, //nand:  0x78000 - nor: 0x8000
						'nsec_iter': 0x8000,//nand 0x8000 (16Mb) - nor: 0x2000 (4Mb)
						'dump_start': 0,
						'save_offset': 0,
						'file_path': '/dev_hdd0/dump.hex',
						'default_name': 'dump.hex',
						'buffer': null
					};
					var dialogButtons = [{text: 'Сохранить', icon: 'ui-icon-disk', click: function(event, ui){
						if(sobj.sector_count){
							sobj.file_path = jQlbl_fname[0].innerText;
							function confirmDump(){
								jQdialog.dialog('close');
								jQuery('.preloader').removeClass('ui-helper-hidden');
								ldiag.removePatch();
								setTimeout(function() {
									sobj.buffer = helper.sm.getBuffer();
									sobj.tls = helper.worker['fmm'].getTLS();
									if(!sobj.buffer){Logger.error('saveDump: Не удалось выделить буферную память!');toast('Не удалось выделить буферную память','error',5);return;}
									if(!sobj.tls){Logger.error('saveDump: Не удалось выделить память TLS!');toast('Не удалось выделить память TLS','error',5);return;}
									pbfm1.setTitle('Выполнение операций дампинга');
									pbfm1.open();
									setTimeout(function() {
										sdef.resolve(sobj);
									},1200);
								},1000);
							}
							if(fsitem_exists(sobj.file_path)){
								confirmDialog('Если вы продолжите, '+sobj.file_path+' будет перезаписан','Confirm',confirmDump);
							}
							else{
								confirmDump();
							}
						}
						else if(sobj.idps){
							//alert(sobj.idps);
							jQdialog.dialog('close');
							sobj.file_path = jQlbl_fname[0].innerText;
							function confirmSave(){
								setTimeout(function() {
								sdef.resolve(sobj);
							},250);
							}
							if(fsitem_exists(sobj.file_path)){
								confirmDialog('Если вы продолжите, '+sobj.file_path+' будет перезаписан','Confirm',confirmSave);
							}
							else{
								confirmSave();
							}
						}
					}},{text: 'Отмена', icon: 'ui-icon-close', click: function(event, ui){
						jQdialog.dialog('close');
					}}];//
					jQuery('#dSave_As').removeClass('ui-helper-hidden');
					
					var jQdialog = jQuery('#dSave_As').dialog({
						autoOpen: false,
						modal: true,
						closeOnEscape: false,
						resizable: false,
						height: 480,
						width: 720,
						buttons: dialogButtons,
						open: function(event, ui ) {
							jQftree = jQuery('#fTree').jstree(true);
							jQpt_fname = jQuery('input[name=sDialog_FileName]');
							jQlbl_fname = jQuery('label[id=lsDialog_Path]');
							jQlbl_fname.html('');
							jQpt_fname.val(fname);
							jQtree.jstree({
								'core' : {
									'multiple':false,
									'restore_focus':false,
									'dblclick_toggle':false,
									'data' : function (node, cb) {
										if(node.type!=='file'){
											jQtree.find('i.jstree-ocl').addClass('ui-state-disabled');
											var dat = getJSTreeData_fast(this, node, false, true);
											//var dat = getJSTreeData_wk(this, node, false, true);
											cb(dat===-1? [] : dat);
											if(dat===-1 || dat.length>0){
												jQtree.find('i.jstree-ocl').removeClass('ui-state-disabled');
												this.get_node(node, true).removeClass('jstree-loading').attr('aria-busy',false);
											}
										}
									}
								},
								'themes':{
									'dots': true,
									'icons': true
								},
								'sort' : function(a, b) {
									return (this.get_node(a).text > this.get_node(b).text) ? 1 : -1;
								},
								'types' : {
									'#' : {
									  'max_children' : 12,
									  'max_depth' : 128,
									  'valid_children' : ['root']
									},
									'root' : {
									  'max_depth' : 127,
									  'icon' : 'jstree-folder',
									  'valid_children' : ['folder','file']
									},
									'folder' : {
									  'icon' : 'jstree-folder',
									  'valid_children' : ['folder','file']
									},
									'file' : {
									  'icon' : 'jstree-file',
									  'valid_children' : []
									}
								  },
								'plugins' : [
									'search', 'types', 'changed', 'unique', 'sort'//, 'wholerow'
								]
							});
							jQtree.on('select_node.jstree', function (e, data) {
								var _path = data.instance.get_fullpath(data.node);
								if(data.node.type === 'file'){
									jQlbl_fname.text(_path);
									jQpt_fname.val(data.node.text);
								}
								else{
									if(jQpt_fname.val().length===0){jQpt_fname.val('dump.hex');}
									jQlbl_fname.text(_path+'/'+jQpt_fname.val());
								}
								sel_path = _path.substr(_path.lastIndexOf('/'));
								sd.enableSaveButton();
								sd.enableSaveText();
							});
							jQpt_fname.on('change',function(e){
								var v = jQpt_fname.val();
								if(validateFileName(v)){
									jQlbl_fname.text(sel_path+'/');
									sd.disableSaveButton();
								}
								else if(sel_path.length>0){
									jQlbl_fname.text(sel_path+'/'+v);
									sd.enableSaveButton();
								}
								else{
									jQlbl_fname.text('Пожалуйста, выберите папку назначения');
									sd.disableSaveText();
									sd.disableSaveButton();
								}
								change = false;
							});
							var change = false;
							jQpt_fname.on('input',function(e){
								change = true;
							});
							jQtree.parent().on('click', function (e) {
								if(change===true){jQtree.parent().focus();}
							});
							jQtree.on('click', function (e) {
								if(change===true){jQtree.focus();}
							});
							jQtree.on('after_open.jstree', function (e,data) {
								jQtree.find('i.jstree-ocl').removeClass('ui-state-disabled');
								data.instance.get_node(data.node, true).removeClass('jstree-loading').attr('aria-busy',false);
							});
							jQtree.on('load_node.jstree', function (e,data) {
								data.instance.get_node(data.node, true).addClass('jstree-loading').attr('aria-busy',true);
								data.instance.open_node(data.node);
							});
							jQtree.on('before_open.jstree', function (e, data) {
								data.instance.get_node(data.node, true).addClass('jstree-loading').attr('aria-busy',true);
								
								var nodes_to_close = jQuery.grep(data.instance.get_node(data.node.parent).children, function(elem,index) {
									return elem!==data.node ? data.instance.is_open(elem) : false;
								});
								data.instance.close_node(nodes_to_close);
							});
						},
						beforeClose: function(event, ui ) {
						},
						close: function(event, ui ) {
							jQpt_fname.val(fname);
							jQtree.jstree('destroy',true);
						}
					});
					this.setTitle = function(txt){
						jQdialog.dialog('option', 'title', txt );
					};
					this.open = function(obj,func){
						sobj = obj ? obj : sobj;
						if(sobj.default_name){
							fname = sobj.default_name ? sobj.default_name : 'dump.hex';
						}
						jQdialog.dialog('open');
						jQuery('.scbsave').mCustomScrollbar({
							theme: (Cookies.get('style')==='eggplant') ? 'light-thick' : 'dark-thick'
						});
						this.disableSaveButton();
						this.disableSaveText();
						jQlbl_fname.text('Пожалуйста, выберите папку назначения');
						sdef = jQuery.Deferred();
						sdef.promise().done(func);
						jQtree.focus();
						jQuery('#dSave_As').parent().find('button').blur();
						jQuery('#dSave_As').parent().find('.ui-dialog-titlebar-close').prop('title','');
						jQuery(document).tooltip();
					};
					this.close = function(){
						jQdialog.dialog('close');
						jQuery('.scbsave').find('.mCustomScrollBox').off('mousewheel wheel');
						jQuery('.scbsave').mCustomScrollbar('destroy');
						//TO-DO:
						//Reset dialog features...
					};
					this.enableSaveText = function(){
						jQuery('#sDialog_FileName').removeClass('ui-state-disabled');
					};
					this.disableSaveText = function(){
						jQuery('#sDialog_FileName').removeClass('ui-state-disabled').addClass('ui-state-disabled');
					};
					this.disableSaveButton= function(){
						jQuery('#dSave_As').parent().find('div.ui-dialog-buttonset:first').children('button:first').removeClass('ui-state-disabled').addClass('ui-state-disabled').blur();
					};
					this.enableSaveButton= function(){
						jQuery('#dSave_As').parent().find('div.ui-dialog-buttonset:first').children('button:first').removeClass('ui-state-disabled').focus().blur();
					};
				};
				var lDialog = function(){
					var jQtree = jQuery('#dLTree');
					var jQpath = jQuery('div[id=dlDialog_Path]');
					var lrosFile=null;
					var ld = this;
					var sha256_ros = '';
					var buf_po = null;
					var dialogButtons = [
						{text: 'Load', icon: 'ui-icon-folder-open', click: function(event, ui){
							jQdialog.dialog('close');
							var idx = jQpath[0].innerText.lastIndexOf('/');
							var filename = jQpath[0].innerText.substr(idx+1,jQpath.text().length-idx-1);
							var start = new Date();
							ulog(start,true);
							pbfm1.open();
							pbfm1.updateProgressDialog({'dlabel':'Подготовка буфера','glabel':'Загрузка \''+filename+'\'','dvalue':0,'gvalue':0,'title':'Прогресс операций загрузки'});
							setTimeout(function(){
								var jQftree = jQuery('#fTree').jstree(true);
								ldiag.removePatch();
								sha256_ros = '';
								buf_po = helper.sm.getBuffer();
								if(!buf_po){Logger.error('loadPatch: Не удалось выделить буферную память!');toast('Не удалось выделить буферную память','error',5);return;}
								lrosFile = new fileObject(jQpath.text());
								ulog('Opened File '+jQpath.text());
								ulog('Size: 0x'+lrosFile.size.toString(16));
								if(lrosFile.size===helper.patchfile_size){
									ulog('Проверка размера файла: OK');
									pbfm1.updateProgressDialog({'dlabel':'Чтение данных файла','gvalue':0},start);
									setTimeout(function(){
										Logger.info('loadPatch: загрузка файла '+jQpath.text());
										var err = lrosFile.load(helper.patchfile_size,{'offset':buf_po.offset+0x30,'size':helper.patchfile_size});
										if(err===0){
											ulog('Файл успешно загружен');
											pbfm1.updateProgressDialog({'dlabel':'Извлечение SHA256','glabel':'Проверка \''+filename+'\'','dvalue':100,'gvalue':75},start);
											//setTimeout(function(){
											if(validatePatchFile(buf_po,filename)===1){
												var tsttxt = 'Загруженный файл представляет собой кастомный файл патча. Применение его на этой консоли без аппаратного флешера в экстренных случаях рискованно и неразумно.';
												if(!helper.fm_usermode){
													toast(tsttxt+' Вы не можете использовать его в безопасном режиме.','warning',10);
													ulog(tsttxt+'<br>Вы не можете использовать его в безопасном режиме.');
													updateNoValidationGUI(buf_po,start,filename);
													closure();
													return;
												}
												else{
													toast(tsttxt,'warning',5);
													ulog('Тип файла патча: Кастомный<br>Использование этого файла для патча консоли сопряжено с риском.<br>Вам следует тщательно обдумать свои дальнейшие действия.');
												}
											}
											else{
												if(helper.kmode==='CEX'){
													toast('Загруженный файл является рекомендуемым файлом патча для использования на этой консоли с текущей версией прошивки.','success',5);
													ulog('Тип файла патча: Официальный CEX');
												}
												else{
													toast('Загруженный файл является рекомендуемым файлом патча только для режима CEX. Эта консоль находится в ('+helper.kmode+') режиме, использование этого патча брикнет её.','warning',10);
													ulog('Тип файла патча: Официальный CEX - НЕ совместим с текущим режимом ('+helper.kmode+') этой консоли');
												}
											}
											updateValidationGUI(start,filename);
											closure();
											//},500);
										}
										else {
											ulog('Ошибка ввода-вывода файла: 0x'+err.toString(16)+'<br>Операции загрузки прерваны');
											updateNoValidationGUI(buf_po,start,filename);
											closure();
										}
									},500);
								}
								else {
									helper.sp.playNG();
									pbfm1.updateProgressDialog({'dlabel':'Загрузка операций не удалась','glabel':jQpath.text()+' не является допустимым файлом патча','dvalue':100,'gvalue':100,'istatus':'error-image'},start);
									ulog('Проверка размера файла: NG<br>Операции загрузки прерваны');
									Logger.info('loadPatch: Файл поврежден '+jQpath.text());
									closure();
								}
								function closure(){
									err = lrosFile.close();
									delete lrosFile;
								}
							},1200);
						}},
						{text: 'Отмена', icon: 'ui-icon-close', click: function(event, ui){
							jQdialog.dialog('close');
						}}];//
					jQuery('#dLoad').removeClass('ui-helper-hidden');
					var jQdialog = jQuery('#dLoad').dialog({
						autoOpen: false,
						modal: true,
						closeOnEscape: false,
						resizable: false,
						height: 480,
						width: 720,
						buttons: dialogButtons,
						open: function(event, ui ) {
							jQtree.jstree({
								'core' : {
									'multiple':false,
									'restore_focus':false,
									'dblclick_toggle':false,
									'data' : function (node, cb) {
										jQtree.find('i.jstree-ocl').addClass('ui-state-disabled');
										var dat = getJSTreeData_fast(this, node, true, false);
										//var dat = getJSTreeData_wk(this, node, true, false);
										cb(dat===-1? [] : dat);
										if(dat===-1 || dat.length>0){
											jQtree.find('i.jstree-ocl').removeClass('ui-state-disabled');
											this.get_node(node, true).removeClass('jstree-loading').attr('aria-busy',false);
										}
									}
								},
								'themes':{
									'dots': true,
									'icons': true
								},
								'sort' : function(a, b) {
									var a1 = this.get_node(a);
									var b1 = this.get_node(b);
									if (a1.type == b1.type){
										return (a1.text > b1.text) ? 1 : -1;
									} else {
										return (a1.type < b1.type) ? 1 : -1;
									}
								},
								'types' : {
									'#' : {
									  'max_children' : 12,
									  'max_depth' : 128,
									  'valid_children' : ['root']
									},
									'root' : {
									  'max_depth' : 127,
									  'icon' : 'jstree-folder',
									  'valid_children' : ['folder','file']
									},
									'folder' : {
									  'icon' : 'jstree-folder',
									  'valid_children' : ['folder','file']
									},
									'file' : {
									  'icon' : 'jstree-file',
									  'valid_children' : 'none',
									  'max_children' : 0
									}
								  },
								'conditionalselect' : function (node, event) {
									if(node.type === 'file'){return true;}
									else {return false;}
								  },
								'plugins' : [
									'search', 'types', 'changed', 'unique', 'sort', 'conditionalselect'//,
								]
							});
							jQtree.on('activate_node.jstree', function (e, data) {
								jQpath.text(data.instance.get_fullpath(data.node));
								ld.enableLoadButton();
							});
							jQtree.on('load_node.jstree', function (e,data) {
								data.instance.get_node(data.node, true).addClass('jstree-loading').attr('aria-busy',true);
								data.instance.open_node(data.node);
							});
							jQtree.on('before_open.jstree', function (e, data) {
								data.instance.get_node(data.node, true).addClass('jstree-loading').attr('aria-busy',true);
								var nodes_to_close = jQuery.grep(data.instance.get_node(data.node.parent).children, function(elem,index) {
									return elem!==data.node ? data.instance.is_open(elem) : false;
								});
								data.instance.close_node(nodes_to_close);
							});
							jQtree.on('after_open.jstree', function (e,data) {
								jQtree.find('i.jstree-ocl').removeClass('ui-state-disabled');
								data.instance.get_node(data.node, true).removeClass('jstree-loading').attr('aria-busy',false);
							});
						},
						beforeClose: function(event, ui ) {
						},
						close: function(event, ui ) {
							jQtree.jstree('destroy',true);
						}
					});
					this.setTitle = function(txt){
						jQdialog.dialog( 'option', 'title', txt );
					};
					this.open = function(){
						jQdialog.dialog( 'open');
						jQuery('.scbload').mCustomScrollbar({
							theme: (Cookies.get('style')==='eggplant') ? 'light-thick' : 'dark-thick'
						});
						jQpath.text('*.*');
						this.disableLoadButton();
						jQuery('#dLoad').parent().find('.ui-dialog-titlebar-close').prop('title','');
						jQuery(document).tooltip();
					};
					this.close = function(){
						jQdialog.dialog('close');
						jQuery('.scbload').find('.mCustomScrollBox').off('mousewheel wheel');
						jQuery('.scbload').mCustomScrollbar('destroy');
					};
					this.getSHA256 = function (){
						return sha256_ros;
					};
					this.getBuffer = function(){
						return buf_po;
					};
					this.addPatchInfo = function(buf,sha){
						buf_po=buf;
						sha256_ros=sha;
					};
					this.removePatch = function(){
						var jQftree = jQuery('#fTree').jstree(true);
						var _node = jQftree.get_node('flashbk');
						var children = _node ? _node.children : [];
						if(children.length>0){
							jQftree.delete_node(children);
						}
						buf_po={'offset':0,'size':0};
					};
					this.disableLoadButton= function(){
						jQuery('#dLoad').parent().find('div.ui-dialog-buttonset:first').children('button:first').removeClass('ui-state-disabled').addClass('ui-state-disabled').blur();
					};
					this.enableLoadButton= function(){
						jQuery('#dLoad').parent().find('div.ui-dialog-buttonset:first').children('button:first').removeClass('ui-state-disabled').focus().blur();
					};
				};
				var fTree = function(close_toast){
					var jQtree = jQuery('.fm-tree');
					so = so ? so : new storageObject();
					helper.minver = getMinVer();
					var cfwminver = parseFloat(helper.minver)<3.60;
					var metldr = getMtldrVersion(so);
					function metldr_err(){
						Logger.error('Минимальная применимая версия прошивки не соответствует версии metldr.');
						Logger.warn('Если IDPS вашей консоли подделан, минимальная применимая версия прошивки, рассчитанная системой, больше не является надежной.');
						toast('Несоответствие, возможно вызванное спуфингом IDPS, было обнаружено в минимальной применимой версии микропрограммы, возвращенной системой.','warning',5);
						helper.minver += ' !';
					}
					if(metldr === 'metldr.2' && cfwminver){
						metldr_err();
					}
					else if(metldr === 'metldr' && !cfwminver){
						metldr_err();
					}
					var cfw_compat = metldr!=='metldr.2';
					var compat = jQuery('#cfwcompat');
					compat.parent().addClass(cfw_compat ? 'header-label on':'header off');
					compat.addClass(cfw_compat ? 'fa fa-check fa-lg fa-fw':'fa fa-times fa-lg fa-fw');	
					compat.css({'color':cfw_compat ? '#99c700':'#e95136','font-size':'20px','width':'30px','position':'relative','padding-top':'1px','text-shadow':'-1px -1px 0 #000,0 -1px 0 #000,1px -1px 0 #000,1px 0 0 #000,1px 1px 0 #000,0 1px 0 #000,-1px  1px 0 #000,-1px 0 0 #000'});
					var _nor = so.is_nor();
					var idps = getIDPS(so,_nor ? helper.idps_sector_nor : helper.idps_sector_nand);
					var XXX = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
					var idps_hidden = false;
					var jstree;
					jQtree.jstree({
						'core' : {
							'multiple':false,
							'restore_focus':false,
							'dblclick_toggle':false,
							'data' : function (node, cb) {
								if(node.id === '#') {
									var nodes = [{ 'id' : 'flash', 'type' : 'flash', 'parent' : '#', 'text' : 'Дамп Флэш-памяти' },
									   { 'id' : 'type', 'type' : 'ros', 'parent' : 'flash', 'text' : 'Тип: ?' },
									   { 'id' : 'sectors', 'type' : 'ros', 'parent' : 'flash', 'text' : 'Количество секторов: ?' },
									   { 'id' : 'idps', 'type' : 'ros', 'parent' : 'flash', 'text' : 'IDPS: ?' },
									   { 'id' : 'minver', 'type' : 'ros', 'parent' : 'flash', 'text' : 'Минимальная версия прошивки: ?' },
									   { 'id' : 'ros0', 'type' : 'ros', 'parent' : 'flash', 'text' : 'ROS банк 0' },
									   { 'id' : 'ros1', 'type' : 'ros', 'parent' : 'flash', 'text' : 'ROS банк 1' },
									   { 'id' : 'info0', 'type' : 'info', 'parent' : 'ros0', 'text' : 'Вычисление контрольной суммы SHA256, пожалуйста, подождите...' },
									   { 'id' : 'info1', 'type' : 'info', 'parent' : 'ros1', 'text' : 'Вычисление контрольной суммы SHA256, пожалуйста, подождите...' },
									   { 'id' : 'flashbk', 'type' : 'flash', 'parent' : '#', 'text' : 'Патч флэш-памяти' }
									];
									cb(nodes);
								 }
							},
							'check_callback' : function (operation, node, node_parent, node_position, more) {
								var ret = false;
								switch(operation){
									case 'create_node':
										ret= true;
										break;
									case 'rename_node':
										ret= true;
										break;
									case 'delete_node':
										ret= true;
										break;
									case 'move_node':
										ret= true;
										break;
									case 'copy_node':
										ret= true;
										break;
									case 'edit':
										ret= true;
										break;
									default:
									ret= false;
								}
								return ret;
							}
						},
						'themes':{
							'dots': true,
							'icons': true
						},
						'sort' : function(a, b) {
							return (this.get_node(a).text > this.get_node(b).text) ? 1 : -1;
						},
						'types' : {
							'#' : {
							  'max_children' : 2,
							  'max_depth' : 3,
							  'valid_children' : ['flash']
							},
							'flash' : {
							 'max_children' : 2,
							  'max_depth' : 2,
							  'icon' : 'jstree-folder',
							  'valid_children' : ['ros']
							},
							'ros' : {
							  'max_children' : 1,
							  'max_depth' : 1,
							  'icon' : 'jstree-folder',
							  'valid_children' : ['info']
							},
							'info' : {
							  'max_children' : 0,
							  'max_depth' : 0,
							  'icon' : 'jstree-file',
							  'valid_children' : []
							}
						  },
						'contextmenu' :{
							'show_at_node':true,
							'items': function(node) {
								var is_regmode = helper.fm_usermode === 0;
								var is_patch_rec = helper.nofsm_hash === ldiag.getSHA256();
								var is_cex = helper.kmode === 'CEX';
								var _node = jstree.get_node('flashbk');
								if(jstree.is_disabled('flashbk') && node.id === 'flashbk'){return {};}
								var is_patch_avail = _node ? _node.children.length > 0 ? true : false : false;
								var ret = node.id === 'flash' ? {
										'Save': {
											'separator_before': false,
											'separator_after': false,
											'label': 'Сохранить бэкап флэш-памяти',
											'icon' : 'fa fa-floppy-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open({
														'sector_count': _nor ? 0x8000 : 0x77800,
														'nsec_iter': _nor ? 0x2000 : 0x8000,//nand 0x8000 (16Mb) - nor: 0x2000 (4Mb)
														'dump_start': 0,
														'save_offset':0,
														'file_path': '',
														'default_name': 'dump.hex',
														'tls': null,
														'buffer': null
													},mt_dump);
												},0);
											}
										}
									} : (node.id === 'flashbk') ? {
										'Load': {
											'separator_before': false,
											'separator_after': false,
											'label': 'Загрузить патч из файла',
											'icon' : 'fa fa-folder-open-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													ldiag.open();
												},0);
											}
										},
										'LoadWeb':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Загрузить патч через HTTPS',
											'_disabled': is_cex ? helper.nofsm_url.length>0 ? false: true : true, //add more checks????
											'icon' : 'fa fa-cloud-download fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													jQuery('.preloader').removeClass('ui-helper-hidden');
													ldiag.removePatch();
													setTimeout(function() {
														dl_object  = {buffer: helper.sm.getBuffer(),file: helper.nofsm_url,start:new Date(),sha256:''};
														ulog(dl_object.start,true);
														if(!dl_object.buffer){Logger.error('loadPatch: Не удалось выделить буферную память!');toast('Buffer memory allocation failed','error',5);return;}
														pbfm1.open(false,dl_cancel);
														pbfm1.updateStatusText('Инициализация операций загрузки');
														pbfm1.updateProgressDialog({'glabel':'Установка соединения с сервером','title':'Процесс операций загрузки'});
														setTimeout(function() {
															helper.swf.downloadFile(dl_object.file);//
														},500);
													},500);
												},0);
											}
										},
										'Download':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Скачать файл патча',
											'_disabled': is_cex ? helper.nofsm_url.length>0 ? false: true : true, //add more checks????
											'icon' : 'fa fa-download fa-fw',
											'action': function (obj) {
												document.getElementById('dlframe').src = 'file3.php?tk='+ftoken+'&file='+helper.nofsm_url;
											}
										},
										'Patch': {
											'separator_before': true,
											'separator_after': false,
											'label': 'Применить загруженный патч',
											'icon' : 'fa fa-cogs fa-fw',
											'_disabled': is_regmode ? is_patch_rec && is_cex && is_patch_avail ? false : true : is_patch_avail ? false : true , //add more checks????
											'action': function (obj) {
												function confirmPatch(){
													jQuery('.preloader').removeClass('ui-helper-hidden');
													var def = jQuery.Deferred();
													def.promise().done(mt_patch);
													setTimeout(function(){
														var patch_object = {
															'sector_count': 0x7000,
															'patch_start': _nor ? 0x600 : 0x400,
															'data_buffer': window.ldiag.getBuffer(),
															'offset_data':{'ros0':_nor ? 0x20 : 0, 'ros1': _nor ? 0x20 : 0x10}
														};
														pbfm1.setTitle('Процесс операций патчинга');
														pbfm1.open(true);
														def.resolve(patch_object);
													},0);
												}
												if(!is_patch_rec){ //check against offcial no-fsm patch sha256 ??
													confirmDialog('Патчинг флэш-памяти PS3 с помощью этого файла патча разрешено, поскольку вы отключили безопасный режим. Вас предупредили, что установка патча с использованием этих данных может быть очень рискованной. Невозможно приостановить или отменить процесс пропатчивания, кроме этого диалогового окна подтверждения. Вы уверены что хотите продолжить?','Подтверждение пропатчивания',confirmPatch);
												}
												else{
													confirmDialog('Патчинг флэш-памяти PS3 может привести к поломке вашей консоли, это никогда не следует делать случайно. Невозможно приостановить или отменить процесс пропатчивания, кроме этого диалогового окна подтверждения. Вы уверены что хотите продолжить?','Подтверждение пропатчивания',confirmPatch);
												}
											}
										}
									}: node.id === 'ros0' || node.id === 'ros1' ? {
										'Save_ROS': {
											'separator_before': false,
											'separator_after': false,
											'label': node.id === 'ros0' ? 'Сохранить данные ROS0 как файл патча noFSM.':'Сохранить данные ROS1 как файл патча noFSM.',
											'icon' : 'fa fa-floppy-o fa-fw',
											'_disabled': is_regmode ? true:false,
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open( node.id === 'ros0' ? {
															'sector_count': 0x3800,
															'nsec_iter': 0x3800,//nand 0x8000 (16Mb) - nor: 0x2000 (4Mb)
															'dump_start': _nor ? 0x600:0x400,
															'save_offset': _nor ? 0x10:0x30,
															'file_path': '',
															'default_name': 'ros0.hex',
															'buffer': null
														}: {
															'sector_count': 0x3800,
															'nsec_iter': 0x3800,//nand 0x8000 (16Mb) - nor: 0x2000 (4Mb)
															'dump_start': _nor ? 0x3E00:0x3C00,
															'save_offset': _nor ? 0x10:0x20,
															'file_path': '',
															'default_name': 'ros1.hex',
															'buffer': null
														},mt_dump);
												},0);
											}
										}
									}: (node.id === 'idps') ? {
										'Toggle': {
											'separator_before': false,
											'separator_after': false,
											'label': idps_hidden ? 'Показать IDPS': 'Скрыть IDPS',
											'icon' : idps_hidden ? 'fa fa-unlock-alt fa-fw': 'fa fa-lock fa-fw',
											'action': function (obj) {
												idps_hidden ? jstree.rename_node('idps', 'IDPS: '+idps.toUpperCase()) : jstree.rename_node('idps', 'IDPS: '+XXX);
												idps_hidden = !idps_hidden;
	//u64 value=0;
	//lv2_ss_update_mgr_if(UPDATE_MGR_PACKET_ID_READ_EPROM, QA_FLAG_OFFSET, (uint64_t) &value, 0, 0, 0, 0);
		
	// var rvalue = helper.heap.store(8);
	// var scret = helper.rop.rrun(syscall32(863,0x600b,0x48C61,rvalue,0, 0, 0, 0));
	// alert('syscall returned 0x'+scret.toString(16));
	// alert('value 0x'+helper.memory.upeek32(rvalue).toString(16)+helper.memory.upeek32(rvalue+4).toString(16));
	// helper.heap.free([rvalue]);
		
		
											}
										},
										'Save':{
											'separator_before': false,
											'separator_after': false,
											'label':  'Сохранить IDPS как файл',
											'icon' : 'fa fa-floppy-o fa-fw',
											'action': function (obj) {
												setTimeout(function(){
													sdiag.open( {
															'file_path': '',
															'idps':idps,
															'default_name': 'idps.hex'
														},idps_dump);
												},0);
											}
										}
									}:{};
								return ret;
							}
						},
						'conditionalselect' : function (node, event) {
							if(node.type === 'flash' || node.id === 'ros0' || node.id === 'ros1'|| node.id === 'idps'){return true;}
							else {return false;}
						},
						'plugins' : [
							'search', 'types', 'changed', 'contextmenu', 'unique', 'sort', 'conditionalselect'//,
						]
					});
					jQtree.on('select_node.jstree', function (e, data) {
						var evt =  window.event || e;
						var button = evt.which || evt.button;
						if( button != 1 && ( typeof button != 'undefined')) 
							return false; 
						else if(data.event){
							setTimeout(function() {
								data.instance.show_contextmenu(data.node, evt.offsetX,evt.offsetY, data.event);
							}, 0);
							return true;
						}
					});
					//alert('fmm ros hashing');
					jstree = jQtree.jstree(true);
					jstree.rename_node('type', _nor ? 'Тип флэш-памяти: NOR 16Mb' : cfw_compat ? 'Тип флэш-памяти: NAND 256Mb':'Тип флэш-памяти: eMMC 256Mb');
					jstree.rename_node('sectors', _nor ? 'Количество секторов: 0x8000' : 'Количество секторов: 0x80000 (0x77800 в дампе)');
					jstree.rename_node('idps', 'IDPS: '+idps.toUpperCase());
					if(!cfw_compat){jstree.disable_node('flashbk');}
					var ros0_ref = '';
					var ros1_ref = '';
					var ros0_new = '';
					var ros1_new = '';
					var sha256_ref = '';
					var sha256_pending = false;
					this.refreshFM_node = function(cb){
						//alert('fmm refreshFM_node');
						sha256_pending = true;
						jstree.rename_node('info0','Вычисление контрольной суммы SHA256, пожалуйста, подождите...');
						jstree.rename_node('info1','Вычисление контрольной суммы SHA256, пожалуйста, подождите...');
						var sbuf = helper.sm.getBuffer();
						if(!sbuf){
							helper.sp.playNG();
							Logger.error('Ошибка извлечения SHA256. Нет доступного буфера.');
							toast('Если набор инструментов продолжает получать ошибки при выделении буферной памяти, вам следует перезапустить консоль.','error',5);
							jQuery().toastmessage('removeToast', close_toast);
							jQuery('.preloader').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
							return;
						}
						//alert('sbuf: 0x'+sbuf.offset.toString(16)+' - size 0x'+sbuf.size.toString(16));
						var tl = helper.worker['fmm'].getTLS();
						if(!tl){
							Logger.error('Извлечение SHA256: не удалось выделить память TLS!');
							toast('Не удалось выделить память TLS','error',5);
							return;
						}
						//alert('fmm ROSHashObject');
						var rosH = new ROSHashObject(so,{'dump_start':so.is_nor() ? 0x600: 0x400,'data_buffer':sbuf,'tls':tl});
						if(rosH.error.code>0){
							Logger.error('Извлечение SHA256: создание ROSHashObject не удалось!');
							toast('Ошибка извлечения SHA256','error',5);
							return;
						}
						//TO-DO:
						//Show spinner
						function sha256_cleanup(){
							//alert('sha256_cleanup');
							so.close();
							delete rosH;
							enable_GUI();
							jQuery().toastmessage('removeToast', close_toast);
							jQuery('.refresh-fm').removeClass('ui-state-disabled');
							jQuery('.preloader').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
						}
						function sha256_error(str){
							//alert(str);
							jstree.rename_node('info0','SHA256: ошибка извлечения');
							jstree.rename_node('info1','SHA256: ошибка извлечения');
							jstree.rename_node('minver','Минимальная версия прошивки: '+helper.minver);
							jstree.open_all('flash');
							sha256_pending = false;
							sha256_cleanup();
						}
						ros0_ref = ros0_new;
						ros1_ref = ros1_new;
						//alert('fmm workers call 1');
						helper.worker['fmm'].run(rosH.sfx[0],'Извлечение данных ROS',function(){Logger.info('Извлечение данных из областей ROS флэш-памяти');},function(){
								//alert('fmm ros data extraction');
								function checkArr(arr,val){
									var good=true;
									for(var st=0;st<arr.length;st++){
										if(helper.memory.upeek32(arr[st])===val){
											//alert('problem at index 0x'+st.toString(16)+' Offset: 0x'+arr[st].toString(16));
											good=false;
											break;
										}
									}
									return good;
								}
								if(!checkArr(rosH.rlen,0xFFFFFFFF)){
									sha256_error('ROS Extraction error');
									return;
								}
								else{
									//Logger.trace(rosH.log[0]);
									jstree.rename_node('minver','Минимальная версия прошивки: '+helper.minver);
								}
						});
						//alert('fmm workers call 2');
						helper.worker['fmm'].run(rosH.sfx[1],'Хеширование данных ROS1',function(){Logger.info('Хеширование данных ROS1');},function(){
								//alert('fmm ros1 hashing');
								ros1_new = helper.memory.upeeks(rosH.hash_r1, 0x20, false).toUpperCase();
								if(ros1_new ==='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF'){
									sha256_error('Ошибка хеширования ROS 1');
									return;
								}
								//Logger.trace(rosH.log[1]);
								//Logger.info('ROS 1 hash: '+ros1_new);
								jstree.rename_node('info1','SHA256: '+ ros1_new);
						});
						//alert('fmm workers call 3');
						helper.worker['fmm'].run(rosH.sfx[2],'Хеширование данных ROS0',function(){Logger.info('Хеширование данных ROS0');},function(){
								//alert('fmm ros0 hashing');
								ros0_new = helper.memory.upeeks(rosH.hash_r0, 0x20, false).toUpperCase();
								if(ros0_new==='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF'){
									sha256_error('Ошибка хеширования ROS 0');
									return;
								}
								//Logger.trace(rosH.log[2]);
								//Logger.info('ROS 0 hash: '+ros0_new);
								jstree.rename_node('info0','SHA256: '+ ros0_new);
								jstree.open_all('flash');
								sha256_pending = false;
								helper.sp.playOK();
								if(cb){cb(this.changedROS());}
								sha256_cleanup();
								Logger.info('Контрольные суммы ROS SHA256 <br>ROS0 = '+ros0_new+'<br>ROS1 = '+ros1_new);
								//setTimeout(helper.sp.playOK,750);
						});
					};
					this.isSHA256Pending = function(){
						return sha256_pending;
					};
					this.checkFMSHA256 = function(){
						var fp_hashref = ldiag.getSHA256();
						Logger.info('checkFMSHA256: Хэш файла патча: 0x'+fp_hashref);
						Logger.info('checkFMSHA256: ROS0 хэш: 0x'+ros0_new);
						Logger.info('checkFMSHA256: ROS1 хэш: 0x'+ros1_new);
						return {'ros0':ros0_new === fp_hashref, 'ros1':ros1_new === fp_hashref};
					};
					this.changedROS = function(){
						return !(ros0_new === ros0_ref && ros1_new === ros1_ref);
					};
					this.refreshFM_node();
				};
				var pbfmDialog = function(){
					// jQuery('#fm_cont_status').remove();
					// jQuery('#dicon_status').remove();
					// jQuery('#dfm_status').remove();
					// jQuery('#plfm_gstatus').remove();
					// jQuery('#gfmprogressbar_val').remove();
					// jQuery('#gfmprogressbar').remove();
					// jQuery('#plfm_dstatus').remove();
					// jQuery('#dprogressbar').remove();
					// jQuery('#dprogressbar_val').remove();
					jQuery('.pbfmDialog').remove();
					var f = document.createElement('fieldset');
					f.className = 'df ui-widget-content ui-corner-all';
					var d0 =  document.createElement('div');
					d0.id = 'fm_cont_status';
					d0.className = 'diag-fm-cont-status pbfmDialog';
					var d1 = document.createElement('div');
					d1.id = 'dfm_status';
					d1.className = 'diag-fm-status progress-label ui-widget-content ui-corner-all pbfmDialog';
					d1.innerText = '....';
					var ic = document.createElement('div');
					ic.id = 'dicon_status';
					ic.className = 'icon-status hourglass pbfmDialog';
					d0.appendChild(d1);
					d0.appendChild(ic);
					var d2 = document.createElement('div');
					d2.id = 'plfm_gstatus';
					d2.className = 'diag-plfm-gstatus progress-label ui-widget-content ui-corner-all pbfmDialog';
					d2.innerText = '....';
					var d3 = document.createElement('div');
					d3.id = 'gfmprogressbar_val';
					d3.className = 'progress-val pbfmDialog';
					var d4 = document.createElement('div');
					d4.id = 'gfmprogressbar';
					d4.className = 'diag-gfmprogressbar pbfmDialog';
					d4.appendChild(d3);
					var d5 = document.createElement('div');
					d5.id = 'plfm_dstatus';
					d5.className = 'diag-plfm-dstatus progress-label ui-widget-content ui-corner-all pbfmDialog';
					d5.innerText = '....';
					var d6 = document.createElement('div');
					d6.id = 'dprogressbar';
					d6.className = 'diag-dprogressbar pbfmDialog';
					var d7 = document.createElement('div');
					d7.id = 'dprogressbar_val';
					d7.className = 'progress-val pbfmDialog';
					d6.appendChild(d7);
					jQuery('#dfmProgress').removeClass('ui-helper-hidden');
					var container = jQuery('#dfmProgress').append(f);
					container.find(f).append([d0,d2,d3,d4,d5,d6]);
					var cancel_ops=false;
					var progressbarg = jQuery('#gfmprogressbar');
					var progressbard = jQuery('#dprogressbar');
					var progressStatus = jQuery('#dfm_status');
					var progressgLabel = jQuery('#plfm_gstatus');
					var progressdLabel = jQuery('#plfm_dstatus');
					var pbg_val = jQuery('#gfmprogressbar_val');
					var pbd_val = jQuery('#dprogressbar_val');
					var setIcon = function(_class){
						jQuery.each(jQuery('#dicon_status'),function(idx,el){
							jQuery(el).attr('class','icon-status '+_class);
						});
					};
					var dialogButtons = [{text: 'Отмена', icon: 'ui-icon-close', click: function(event, ui){
						function confirmCancel(){
							cancel_ops=true;
						}
						confirmDialog('Вы действительно хотите остановить выполняемые операции?','Cancel',confirmCancel);
					}}];//
					var jQdialog = container.dialog({
						autoOpen: false,
						modal: true,
						closeOnEscape: false,
						resizable: false,
						height: 395,
						width: 500,
						buttons: dialogButtons,
						show: { effect: 'fade', duration: 1500 },
						hide: { effect: 'fade', duration: 800 },
						open: function(event, ui ) {
							//TO DO:
							// disable both trees & other tabs
							setIcon('hourglass');
							progressgLabel.text( 'Генерация данных рабочего потока' );
							progressdLabel.text( 'Ожидание' );
							pbg_val.text( '' );
							pbd_val.text( '' );
							progressbarg.progressbar('value', false);
							progressbard.progressbar('value', false);
							progressStatus.text( 'Инициализация операций' );
							cancel_ops = false;
						 }
					});
					progressbarg.progressbar({
						value: false,
						change: function(event, ui) {
							var val = progressbarg.progressbar( 'value' );
							var txt =  (val !== false) ? val + '%' : '' ;
							pbg_val.text(txt);
						},
						complete: function(event, ui) {
							pbg_val.text( 'Готово' );
							pbd_val.text( 'Готово' );
							jQdialog.dialog( 'option', 'buttons', [
								{text: 'Лог',  icon: 'ui-icon-info', click: function(event, ui){
									function showuLog(){
										jQuery('#dfmProgress').parent().find('button:last').focus().blur();
									}
									infoDialog(jQuery('#ulog').html(),'Лог',showuLog);
								}},
								{text: 'Закрыть', icon: 'ui-icon-check', click: function(event, ui){
									jQdialog.dialog( 'option',{ close: function(event,ui){}});
									jQdialog.dialog('close');
								}}]
							);
						}
					});
					progressbard.progressbar({
						value: false,
						change: function(event, ui) {
							var val = progressbard.progressbar( 'value' );
							var txt =  (val !== false) ? val + '%' : '' ;
							pbd_val.text(txt);
						},
						complete: function(event, ui ) {
						}
					});
					this.setIconStatus = function(val){
						setIcon(val);
					};
					this.updateGlobalValue = function(val){
						progressbarg.progressbar('value', val > 0 && val < 100 && Math.floor(val)!==val ? Math.floor(val)+1 : Math.floor(val));
					};
					this.updateDetailValue = function(val){
						progressbard.progressbar('value', Math.floor(val));
					};
					this.updateGlobalLabel = function(txt){
						progressgLabel.text( txt );
					};
					this.updateDetailLabel = function(txt){
						progressdLabel.text( txt );
					};
					this.updateStatusText = function(txt){
						progressStatus.text( txt );
					};
					this.getStatusText = function(){
						return progressStatus.text();
					};
					this.setTitle = function(txt){
						jQdialog.dialog( 'option', 'title', 'Менеджер флэш-памяти: '+txt );
					};
					this.updateStatusStyle = function(obj){
						progressStatus.css( obj );
					};
					this.updateProgressDialog = function(obj,st){
						if(obj.istatus)pbfm1.setIconStatus(obj.istatus);
						if(obj.title)pbfm1.setTitle(obj.title);
						if(st)pbfm1.updateStatusText(getElapsedTime(st));
						if(obj.dlabel)pbfm1.updateDetailLabel(obj.dlabel);
						if(obj.glabel)pbfm1.updateGlobalLabel(obj.glabel);
						if(obj.dvalue)pbfm1.updateDetailValue(obj.dvalue);
						if(obj.gvalue)pbfm1.updateGlobalValue(obj.gvalue);
						jQuery('#dfmProgress').parent().find('button').blur();
					};
					this.open = function(noCancel,cb){
						if(noCancel===true){
							jQdialog.dialog( 'option', 'buttons', [{text: 'Отмена', icon: 'ui-icon-close', click: function(event, ui){toast('Текущие операции не могут быть отменены','warning',3);return;}}]);
							jQdialog.dialog( 'option', 'classes.ui-dialog', 'no-close' );
							jQdialog.dialog( 'option',{ close: function(event,ui){}});
						}
						else{
							if(cb){
								jQdialog.dialog( 'option',{close: function(event,ui){
									cb();
								}});
								jQdialog.dialog( 'option', 'buttons', 
									[{text: 'Отмена', icon: 'ui-icon-close', click: function(event, ui){
										function confirmCancel(){
											jQdialog.dialog( 'close');
										}
										confirmDialog('Вы действительно хотите остановить выполняемые операции?','Cancel',confirmCancel);
									}}]
								);
							}
							else{
								jQdialog.dialog( 'option', 'buttons', dialogButtons);
								jQdialog.dialog( 'option',{ close: function(event,ui){}});
							}
							jQdialog.dialog( 'option', 'classes.ui-dialog', 'ui-dialog-titlebar-close' );
						}
						jQuery('.preloader').removeClass('ui-helper-hidden').addClass('ui-helper-hidden');
						jQdialog.parent().find('.ui-dialog-titlebar-close').prop('title','');
						jQuery(document).tooltip();
						jQdialog.dialog( 'open');
					};
					this.close = function(){
						jQdialog.dialog( 'close');
					};
					this.cancel = function(){
						cancel_ops = true;
					};
					this.cancelled = function(){
						return cancel_ops;
					};
				};
				var cleanup = function(obj){
					ulog('Очистка операций дампа флэш-памяти');
					var serr=so.close();
					if(serr!==0){ulog('Ошибка закрытия объекта хранилища флэш-памяти: 0x'+serr.toString(16));}
					var ferr= obj.f.close();
					delete obj.f;
					obj.f=null;
					if(ferr!==0){ulog('Ошибка закрытия файлового объекта: 0x'+ferr.toString(16));}
					obj.d.log=null;
					obj.d.rret=null;
					obj.d.wret=null;
					obj.d.rlen=null;
					obj.d.wlen=null;
					obj.d.sfr=null;
					obj.d.sfw=null;
					delete obj.d;
					obj.d=null;
					obj=null;
				};
				var result = function(obj){
					helper.sp.playOK();
					cleanup(obj);
					setTimeout(function(){
						toast('Процесс дампа завершен успешно','success',5);
						pbfm1.updateProgressDialog({'gvalue':100,'glabel':'Созданный файл дампа \''+obj.filename+'\'','istatus':'success-image'});
						toast('Действительность дампа всегда должна быть подтверждена соответствующим инструментом проверки, таким как pyPS3checker.','notice',5);
						//setTimeout(helper.sp.playOK,250);
					},100);
				};
				var failed = function(obj){
					helper.sp.playNG();
					ulog('Ошибка процесса создания дампа флэш-памяти<br>'+obj.error.toString(16));
					pbfm1.updateStatusText(obj.status);
					pbfm1.updateProgressDialog({'dlabel':obj.error,'glabel':'Сбой операции дампа','dvalue':100,'gvalue':100,'istatus':'error-image'});
					toast('Произошла ошибка в процессе создания дампа. Подробности смотрите в логе.','error',5);
					cleanup(obj);
				};
				var inProgress = function(obj){
					pbfm1.updateStatusText(obj.status);
					pbfm1.updateProgressDialog({'dlabel':obj.dlab,'glabel':obj.glab,'dvalue':obj.dval,'gvalue':obj.gval});
				};
				var deferred=null;
				var mt_dump = function(dump_object){
					if(!dump_object){return;}
					var _nor = so.is_nor();
					var start = new Date();
					ulog(start,true);
					Logger.warn('Dump start'+getElapsedTime(start));
					try{
						var idx = dump_object.file_path.lastIndexOf('/');
						var filename = dump_object.file_path.substr(idx+1,dump_object.file_path.length-idx-1);
						var szout = dump_object.save_offset>0 ? helper.patchfile_size.toString(16): (dump_object.sector_count*helper.sector_size).toString(16);
						var norout = _nor ?  'NOR': 'NAND';
						ulog('Параметры дампа:<br>Общее количество секторов: 0x'+dump_object.sector_count.toString(16)+
						'<br>Позиция начала дампа: 0x'+dump_object.dump_start.toString(16)+
						'<br>Путь к файлу дампа: '+dump_object.file_path+
						'<br>Размер файла дампа: 0x'+szout+' bytes'+
						'<br>Объект хранения флэш-памяти создан'+
						'<br>Обнаруженный тип: '+norout
						);
						var f = new fileObject(dump_object.file_path,helper.fs_flag_create_rw);
						Logger.warn('fileObject создан'+getElapsedTime(start));
						ulog(f.size>0 ? 'Перезапись файла ввода-вывода '+dump_object.file_path : 'Создание файлов ввода-вывода '+dump_object.file_path );
						//Logger.error('Socket Handle 0x'+so.device_handle.toString(16));
						var d = new dumpObject(so,f,dump_object);
						Logger.warn('dumpObject '+getElapsedTime(start));
						var max_it = Math.floor(dump_object.sector_count/dump_object.nsec_iter);
						var rem_sec = dump_object.sector_count - (max_it*dump_object.nsec_iter);
						var inc = rem_sec===0 ? 100/max_it : max_it>0 ? 100/(max_it+1) : 100;
						var tsz_written=0
						var pbdetails = 0;
						var pbglobal = 0;
						var pblabdetails = 'Подготовка целевого файла';
						var pblabglobal = 'Запуск операций дампа';
						deferred = jQuery.Deferred();
						deferred.promise().then(result,failed,inProgress);
						Logger.warn('Promise '+getElapsedTime(start));
						var sread = function(obj){
							if(deferred.state()!=='pending'){
								return;
							}
							else if(pbfm1.cancelled()){
								deferred.reject({'f':f,'d':d,'error':'Операции дампа отменены пользователем','status':getElapsedTime(start)});
								return;
							}
							pblabdetails = obj.index!==0 ? 'Сохранение извлеченных данных в файл' : 'Извлечение данных из флэш-памяти' ;//
							pbdetails = 0;
							setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							},0);
							ulog('Флэш-память IO текущий сектор: 0x'+(obj.index*obj.value + dump_object.dump_start).toString(16)+'<br>Чтение ввода-вывода флэш-памяти 0x'+obj.value.toString(16)+' секторов');
							for(var t=0;t<d.rlen[obj.index].length;t++){
								var rlen = helper.memory.upeek32(d.rlen[obj.index][t]);
								var err = helper.memory.upeek32(d.rret[obj.index][t]);
								if(err!==0 || rlen === 0 || rlen > 0x800  ){
									deferred.reject({'f':f,'d':d,'error':err === 0xFFFFFFFF ?  'Ошибка синхронизации потоков' : err === 0 ? 'Неверная длина чтения флэш-памяти 0x'+rlen.toString(16) : 'Ошибка чтения флэш-памяти 0x'+err.toString(16),'status':getElapsedTime(start)});
									return;
								}
							}
							helper.worker['fmm'].run(d.sfw[obj.index],'Запись данных в файл',function(){Logger.info('Запись данных в файл');},function(){check_write(obj);});
							return;
						};
						var check_write = function(obj){
							if(deferred.state()!=='pending'){
								return;
							}
							else if(pbfm1.cancelled()){
								deferred.reject({'f':f,'d':d,'error':'Операции дампа отменены пользователем','status':getElapsedTime(start)});
								return;
							}
							var err = helper.memory.upeek32(d.wret[obj.index]);
							var fnl = rem_sec > 0 ? max_it : max_it-1;
							var size = dump_object.save_offset!==0 ? helper.patchfile_size: obj.value*helper.sector_size;
							pblabdetails = obj.index!==fnl ? 'Извлечение данных из флэш-памяти':'Операции по дампу завершены';//
							pbdetails = 100;
							pbglobal += inc;
							pbglobal = pbglobal===100 ? 99 : pbglobal;
							ulog('<br>'+new Date());
							var szw = helper.memory.upeek32(d.wlen[obj.index]+0x4);
							if(err!==0 || szw!==size){
								f.size += szw;
								var errstr = err > 0 ? ' 0x' + err.toString(16) : '';
								Logger.error('Ошибка объекта дампа mt_save: '+ errstr + '<br>Объект дампа mt_save размер данных:  0x'+szw.toString(16)+' байтов, записанных в файл - ожидается: 0x'+size.toString(16)+' байтов');
								deferred.reject({'f':f,'d':d,'error':err === 0xFFFFFFFF ?  'Ошибка синхронизации потоков' : 'Ошибка ввода-вывода при сохранении файла 0x'+err.toString(16),'status':getElapsedTime(start)});
								return;
							}
							f.size += size;
							tsz_written +=parseFloat(Math.round((szw/0x100000) * 100) / 100);
							pblabglobal = 'Сохранено '+tsz_written.toString()+' Мб в \''+filename+'\'';//
							setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							},0);
							Logger.info(d.log[obj.index]);
							//Logger.trace(d.log[obj.index]);
							ulog('Файл IO Общий размер, записанный в файл '+tsz_written.toString()+'Мб');
							if(obj.index===fnl){
								Logger.warn('Дамп завершен '+getElapsedTime(start));
								setTimeout(function(){
									ulog('Флэш-память успешно сдамплена в<br>'+dump_object.file_path);
									deferred.resolve({'f':f,'d':d,'filename':filename});
								},0);
							}
							else {
								
								var ix = obj.index+1;
								ix = ix < max_it ? ix : rem_sec > 0 && ix === max_it ? ix : 0;
								if(ix>0){
									helper.worker['fmm'].run(d.sfr[ix],'Чтение данных из флэш-памяти',function(){Logger.info('Чтение данных из флэш-памяти');},function(){
										sread({'index':ix,'value': ix < max_it ? dump_object.nsec_iter : rem_sec});
									});
								}
							}
						};
						helper.worker['fmm'].run(d.sfr[0],'Чтение данных из флэш-памяти',function(){Logger.info('Чтение данных из флэш-памяти');},function(){sread({'index':0,'value':max_it > 0 ? dump_object.nsec_iter : rem_sec > 0 ? rem_sec : 0});});
					}
					catch(e){
						Logger.error('<h2><b>JS исключение: </b></h2><br>'+e);
					}
				};
				var idps_dump = function(iobj){
					var idps_offset = helper.heap.store(iobj.idps.toUpperCase());
					var fo = new fileObject(iobj.file_path, helper.fs_flag_create_rw);
					var iret = fo.save({'offset':idps_offset,'size':0x10},0x10,null,null);
					fo.close();
					delete fo;
					if(iret==0){
						infoDialog('IDPS сохранено в '+ iobj.file_path,'Сохраненный IDPS',function(){});
					}
					else{
						infoDialog('Ошибка 0x'+iret.toString(16)+' сохранения IDPS в '+ iobj.file_path,'Не удалось сохранить IDPS',function(){});
					}
					helper.heap.free([idps_offset]);
				};
				var mt_patch = function(patch_object){
					if(!patch_object){return;}
					var _nor = so.is_nor();
					var start = new Date();
					ulog(start,true);
					try{
						var norout = _nor ?  'NOR': 'NAND';
						ulog('Параметры патча:<br>Патч общего количества секторов: 0x'+patch_object.sector_count.toString(16)+
							'<br>Позиция начала патча: 0x'+patch_object.patch_start.toString(16)+
							'<br>Объект хранения флэш-памяти создан'+
							'<br>Обнаруженный тип: '+norout
						);
						var po = new patchObject(so,patch_object);
						var cleanup = function(){
							var serr=so.close();
							if(serr!==0){ulog('Ошибка закрытия объекта хранилища флэш-памяти: 0x'+serr.toString(16));}
							delete po;
							ulog('Очистка операций патчинга флэш-памяти');
						};
						var result = function(){
							helper.sp.playOK();
							ulog('Флэш-память успешно пропатчена');
							cleanup();
							setTimeout(function(){
								toast('Вы можете перезагрузить консоль.','success',5);
								pbfm1.updateProgressDialog({'gvalue':100,'glabel':'Патч успешно применен','istatus':'success-image'});
								
							},750);
							//setTimeout(helper.sp.playOK,1500);
						};
						var failed = function(o){
							helper.sp.playNG();
							ldiag.removePatch();
							ulog('Ошибка процесса патчинга флэш-памяти<br>'+o.error.toString(16));
							pbfm1.updateStatusText(o.status);
							pbfm1.updateProgressDialog({'dlabel':o.error,'glabel':'Сбой операций патчинга','dvalue':100,'gvalue':100,'istatus':'error-image'});
							cleanup();
							if(o.recalculateSHA===true){
								ulog('Проверка изменений флэш-памяти');
								ft1.refreshFM_node(function(changes){
									if(changes === true){
										ulog('Данные были записаны во флэш-память. НЕ ПЕРЕЗАГРУЖАЙТЕ, не исправив предварительно регионы ROS.');
										toast('Произошла ошибка во время процесса пропатчивания и данные были записаны во флэш-память. НЕ перезагружайте консоль с флэш-памятью в текущем состоянии. Подробности смотрите в логе.','error',5);
										Logger.error('Пропатчивание не удалось и данные были записаны во флэш-память. Вы должны устранить ущерб. НЕ ПЕРЕЗАГРУЖАТЬ.');
									}
									else{
										ulog('Данные не были записаны во флэш-память.');
										Logger.warn('Пропатчивание не удалось, но данные не были записаны во флэш-память.');
										toast('Произошла ошибка во время процесса пропатчивания, но данные не были записаны во флэш-память. Перезагрузка должна быть безопасной. Подробности смотрите в логах.','warning',5);
									}
								});
							}
						};
						var inProgress = function(o) {
							pbfm1.updateStatusText(o.status);
							pbfm1.updateProgressDialog({'dlabel':o.dlab,'glabel':o.glab,'dvalue':o.dval,'gvalue':o.gval});
						};
						deferred = jQuery.Deferred();
						deferred.promise().then(result,failed,inProgress);
						var patchROS = function(idx){
							var pbdetails = 0;
							var pbglobal = idx*40;
							var pblabdetails = 'Пропатчивание области флэш-памяти ROS'+idx.toString();
							var pblabglobal = 'Операции патча флэш-памяти';
							if(deferred.state()!=='pending'){
								return;
							}
							var cp = 0;
							for(var i = po.ret[idx].length-1;i >= 0;i--){
								if(helper.memory.upeek32(po.ret[idx][i])=== 0xFFFFFFFF){ret++;}
							}
							if(cp>0){
								deferred.reject({'error':'Сбой операций записи во флэш-память','status':getElapsedTime(start),'recalculateSHA': cp===po.ret.length ? false : true});
								return;
							}
							var offt = idx===0 ? patch_object.data_buffer.offset + patch_object.offset_data.ros0 : patch_object.data_buffer.offset + patch_object.offset_data.ros1;
							Logger.info('Пропатчивание ROS'+idx.toString()+' с буферизованными данными в 0x'+offt.toString(16));
							setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							},0);
							var cnt=0;
							for(var t = po.wlen[idx].length-1;t >= 0;t--){
								var wlen = helper.memory.upeek32(po.wlen[idx][t]);
								if(wlen === 0 || wlen===0xFFFFFFFF){cnt++;}
							}
							if(cnt>0){
								deferred.reject({'error':'Операции записи во флэш-память завершились неудачно, некоторые секторы были без ошибок','status':getElapsedTime(start),'recalculateSHA': cnt===po.wlen.length ? false : true});
								return;
							}
							pbglobal = idx*40 + 20;
							setTimeout(function(){
								deferred.notify({'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails,'status':getElapsedTime(start)});
							},0);
							ulog('ROS'+idx.toString()+' операции патча завершены без ошибок');
							Logger.info(po.log[idx]);
							//Logger.trace(po.log[idx]);
							if(idx === 0){
								if(!_nor){helper.memory.upoke32(patch_object.data_buffer.offset+0x14,0);}
								helper.worker['fmm'].run(po.sfp[1],'Патчинг данных ROS1',function(){Logger.info('Патчинг данных ROS1');},function(){patchROS(1);});
							}
							else{
								ulog('Вычисление контрольной суммы SHA256 для банков ROS 0 и 1');
								Logger.info('Расчет хэшей SHA256 банков ROS');
								pblabglobal = 'Проверка данных после пропатчивания флэш-памяти';
								pblabdetails = 'Вычисление контрольных сумм SHA256';
								pbdetails = 0;
								deferred.notify({'status': getElapsedTime(start),'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails});
								ldiag.removePatch();
								ft1.refreshFM_node();
								pbglobal = 80;
								pbdetails = 50;
								function checkSHA256(){
									deferred.notify({'status': getElapsedTime(start),'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails});
									if(ft1.isSHA256Pending()=== true){pbglobal = 90;pbdetails = 75;setTimeout(checkSHA256,250);return;}
									setTimeout(function(){
										var status = ft1.checkFMSHA256();
										if(status.ros0 && status.ros1){
											ulog('Патч применен к банку ROS 0: ДА<br>Патч применен к банку ROS 1: ДА');
											pbglobal = 99;
											pbdetails = 100;
											pblabdetails = 'Ожидание';
											deferred.notify({'status': getElapsedTime(start),'glab': pblabglobal,'dlab': pblabdetails,'gval': pbglobal,'dval': pbdetails});
											setTimeout(function(){
												deferred.resolve();
											},200);
										}
										else{
											var r0 = status.ros0 ? 'СООТВЕТСТВУЕТ' : 'НЕ СООТВЕТСТВУЕТ';
											var r1 = status.ros1 ? 'СООТВЕТСТВУЕТ' : 'НЕ СООТВЕТСТВУЕТ';
											Logger.info('Контрольная сумма SHA256 для банка ROS 0 по сравнению с контрольной суммой файла патча : '+r0+'<br>Контрольная сумма SHA256 для банка ROS 1 по сравнению с контрольной суммой файла патча : '+r1);
											var u_r0 = status.ros0 ? 'ДА' : 'НЕТ';
											var u_r1 = status.ros1 ? 'ДА' : 'НЕТ';
											ulog('Патч применен к ROS банку 0: '+u_r0+'<br>Патч применен к ROS банку 1: '+u_r1);
											deferred.reject({'status':getElapsedTime(start),'error':'Ошибка проверки SHA256.','recalculateSHA':false});
										}
									},250);
								}
								checkSHA256();
							}
						};
						helper.worker['fmm'].run(po.sfp[0],'Патчинг данных ROS0',function(){Logger.info('Патчинг данных ROS0');},function(){patchROS(0);});
					}
					catch(e){
						Logger.error('<h2><b>JS исключение: </b></h2><br>'+e);
					}
				};
				
				if(helper.worker['fmm']){
					sdiag = new sDialog();
					ldiag = new lDialog();
					pbfm1 = new pbfmDialog();
					var ft_toast = toast('Извлечение данных из флэш-памяти. Пожалуйста подождите...','warning',120);
					jQuery( '#accordion' ).accordion({
						event: 'mouseover' 
					});
					setTimeout(function(){
						ft1 = new fTree(ft_toast);
						jQuery(document).tooltip();
					},1500);
				}
				else {Logger.error('Не удалось создать рабочий поток в Менеджере флэш-памяти');}
				</script>
			