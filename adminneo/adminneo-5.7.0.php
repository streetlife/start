<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.7.0
 *
 * Compiled with
 * drivers:   all
 * languages: all
 * themes:    default-blue, default-green, default-orange, default-purple, default-red
 * config:    no
 *
 * @link https://www.adminneo.org/
 *
 * @author Peter Knut
 * @author Jakub Vrana (https://www.vrana.cz/)
 *
 * @copyright 2007-2025 Jakub Vrána
 * @copyright 2024-2025 Peter Knut
 *
 * @license Apache License, Version 2.0 (https://www.apache.org/licenses/LICENSE-2.0)
 * @license GNU General Public License, version 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 */namespace
AdminNeo;use
Exception;use
stdClass;use
PDO;use
PDOStatement;use
mysqli;use
mysqli_result;use
SQLite3;use
SQLite3Result;use
DateTime;use
MongoDB\BSON;use
MongoDB\Driver\BulkWrite;use
MongoDB\Driver\Command;use
MongoDB\Driver\Cursor;use
MongoDB\Driver\Manager;use
MongoDB\Driver\Query;abstract
class
Plugin{protected$admin;protected$config;protected$settings;protected$locale;function
inject($Ca,Config$pc,Settings$N,Locale$zh){$this->admin=$Ca;$this->config=$pc;$this->settings=$N;$this->locale=$zh;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$pc=[],array$jk=[]){if(self::$instance)die("Admin instance already exists.\n");$Ca=new
static();if(!$pc&&file_exists("adminneo-config.php")){$pc=include_once("adminneo-config.php");if(!is_array($pc)){$pc=[];$rh="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$Ca->addError(lang(0,"<b>adminneo-config.php</b>")." <a $rh>".lang(1)."</a>");}}$pc=new
Config($pc);$N=new
Settings($pc);if(!$jk&&file_exists("adminneo-plugins.php")){$jk=include_once("adminneo-plugins.php");if(!is_array($jk)){$jk=[];$rh="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$Ca->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $rh>".lang(1)."</a>");}}self::$instance=$jk?new
Pluginer($Ca,$jk):$Ca;$Ca->inject(self::$instance,$pc,$N,Locale::get());foreach($jk
as$ik)$ik->inject(self::$instance,$pc,$N,Locale::get());return
self::$instance;}static
function
get(){if(!self::$instance)die("Admin instance not found. Create instance by Admin::create() method at first.\n");return
self::$instance;}protected
function
__construct(){}function
getConfig(){return$this->config;}function
getSettings(){return$this->settings;}abstract
function
getOperators();function
getLikeOperator(){return
Driver::get()->getLikeOperator();}function
getRegexpOperator(){return
null;}function
init(){}function
addError($j){$this->errors[]=$j;}function
getErrors(){return$this->errors;}abstract
function
getServiceTitle();function
getCredentials(){$M=$this->config->getServer(SERVER);return[$M?$M->getServer():SERVER,$_GET["username"],get_password()];}function
verifyDefaultPassword($D){$Cf=$this->config->getDefaultPasswordHash();if($Cf===null||$Cf==="")return
lang(2);elseif(!password_verify($D,$Cf))return
lang(3);return
true;}function
authenticate($U,$D){if($D==""){$Cf=$this->config->getDefaultPasswordHash();if($Cf===null)return
lang(4,target_blank());else
return$Cf==="";}return
true;}function
getPrivateKey($Dc=false){return
get_private_key($Dc);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($M,$fl=true,$we=null){if($M==""){if(!$fl)return"";$M=Connection::exists()?Connection::get()->getDefaultServerName():"";if($M=="")return$we!==null?$we:lang(5);$Xl=null;}else$Xl=$this->config->getServer($M);return$Xl?$Xl->getName():preg_replace('~^https?://~',"",$M);}abstract
function
getDatabase();function
getDatabases($Se=true){$g=$this->filterListWithWildcards(get_databases($Se),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());if(DB!=""&&!in_array(DB,$g))array_unshift($g,DB);return$g;}function
getSchemas($Gi=false){$Gf=$this->config->getHiddenSchemas();if($Gi&&!in_array("__system",$Gf))$Gf[]="__system";$Dl=$this->filterListWithWildcards(schemas(),$Gf,false,Driver::get()->getSystemSchemas());if(isset($_GET["ns"])&&$_GET["ns"]!=""&&!in_array($_GET["ns"],$Dl))array_unshift($Dl,$_GET["ns"]);return$Dl;}function
getCollations(array$Ng=[]){$Io=$this->config->getVisibleCollations();$Je=$Io?array_merge($Io,$Ng):[];return$this->filterListWithWildcards(collations(),$Je,true);}private
function
filterListWithWildcards(array$Y,array$Je,$Pg,array$Vm=[]){if(!$Y||!$Je)return$Y;$s=array_search("__system",$Je);if($s!==false){unset($Je[$s]);$Je=array_merge($Je,$Vm);}array_walk($Je,function(&$X){$X=str_replace('\\*',".*",preg_quote($X,"~"));});$ck='~^('.implode("|",$Je).')$~';return$this->filterListWithPattern($Y,$ck,$Pg);}private
function
filterListWithPattern(array$Y,$ck,$Pg){$H=[];foreach($Y
as$u=>$X){if(is_array($X)){if($Lm=$this->filterListWithPattern($X,$ck,$Pg))$H[$u]=$Lm;}elseif(($Pg&&preg_match($ck,$X))||(!$Pg&&!preg_match($ck,$X)))$H[$u]=$X;}return$H;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$Ic){}function
printFavicons(){$Yb=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Yb.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Yb.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Yb.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$oo=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$n){if(file_exists($n))$oo[]="$n?v=".filemtime($n);}return$oo;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($Oc){$li=$Oc?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$mi=$Oc?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$Fe=file_exists("adminneo-$li.css");$Ge=file_exists("adminneo-$mi.css");if($Fe&&!$Ge)return
true;return$this->settings->getColorScheme()==$li&&!($Fe
xor$Ge);}function
getJsUrls(){$oo=$this->config->getJsUrls();$n="adminneo.js";if(file_exists($n))$oo[]="$n?v=".filemtime($n);return$oo;}abstract
function
printLoginForm();function
getLoginFormRow($_e,$Yg,$k){if($Yg)return"<tr><th>$Yg</th><td>$k</td></tr>\n";else
return"$k\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n",h($_GET["username"]),"<input type='submit' class='button' name='logout' value='",lang(6),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$an){return
h($an["Name"]);}abstract
function
getFieldName(array$k,$pj=0);function
formatComment($gc){return
h($gc);}abstract
function
printTableMenu(array$an,$pg);function
getForeignKeys($Q){return
foreign_keys($Q);}function
getBackwardKeys($Q,$Ym){if(!$this->settings->isRelationLinks())return[];$K=backward_keys($Q);$Sg=[];foreach($K
as$J){$q=$J["table_schema"].".".$J["table_name"];$Sg[$q]["schema"]=$J["table_schema"];$Sg[$q]["table"]=$J["table_name"];$Sg[$q]["constraints"][$J["constraint_name"]][$J["column_name"]]=$J["referenced_column_name"];}foreach($Sg
as$q=>$u){$_=$this->admin->getTableName(table_status1($u["table"],true));if($_!=""){$Gl=preg_quote($Ym);$Rl="(:|\\s*-)?\\s+";$Sg[$q]["name"]=(preg_match("(^$Gl$Rl(.+)|^(.+?)$Rl$Gl\$)iu",$_,$y)?$y[2].$y[3]:$_);}else
unset($Sg[$q]);}return$Sg;}function
printBackwardKeys(array$hb,array$J){foreach($hb
as$u){foreach($u["constraints"]as$wc){$Uh=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($u["schema"])."&",ME);$x=$Uh.'select='.urlencode($u["table"]);$p=0;foreach($wc
as$c=>$W){if(!isset($J[$W]))continue
2;$x
.=where_link($p++,$c,$J[$W]);}$_=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$u["name"]);$En=implode(", ",array_keys($wc));echo"<a href='".h($x)."' title='".h($En)."'>".h($_)."</a>";$x=$Uh.'edit='.urlencode($u["table"]);foreach($wc
as$c=>$W)$x
.="&preset".urlencode("[".bracket_escape($c)."]")."=".urlencode($J[$W]);echo"<a href='".h($x)."' title='".lang(7)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($F,$Dm,$ve=false);abstract
function
formatMessageQuery($F,$Bn,$ve=false);abstract
function
formatSqlCommandQuery($F);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($Q);abstract
function
fillForeignDescriptions(array$K,array$Ve);function
getFieldValueLink($W,$k){if(is_mail($W))return"mailto:$W";if(is_web_url($W))return$W;return
null;}abstract
function
formatSelectionValue($W,$x,$k,$Aj);abstract
function
formatFieldValue($X,array$k);abstract
function
printTableStructure(array$l);abstract
function
printTablePartitions(array$Sj);abstract
function
printRelatedTables(array$S);abstract
function
printTableIndexes(array$t,array$an);abstract
function
printSelectionColumns(array$L,array$d);abstract
function
printSelectionSearch(array$Z,array$d,array$t);abstract
function
printSelectionOrder(array$pj,array$d,array$t);abstract
function
printSelectionLimit($w);abstract
function
printSelectionLength($wn);abstract
function
printSelectionAction(array$t);function
isDataEditAllowed(){return!information_schema(DB);}abstract
function
processSelectionColumns(array$d,array$t);abstract
function
processSelectionSearch(array$l,array$t);abstract
function
processSelectionOrder(array$l,array$t);function
processSelectionLimit(){if(!isset($_GET["limit"]))return$this->settings->getRecordsPerPage();return$_GET["limit"]!=""?(int)$_GET["limit"]:0;}abstract
function
processSelectionLength();abstract
function
getFieldFunctions(array$k);abstract
function
getFieldInput($Q,array$k,$Xa,$X,$if);function
getFieldInputHint($Q,array$k,$X){return
support("comment")?$this->admin->formatComment($k["comment"]):"";}abstract
function
processFieldInput(array$k,$X,$if="");function
detectJson($Ae,&$X,$vk=null){if(is_array($X)){$Qe=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$X=json_encode($X,$Qe);return
true;}$Qe=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($vk?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$Ae)){if($X!=null&&$vk!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode(json_decode($X),$Qe);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($X)&&$X!=""&&preg_match('~varchar|text|character varying|String|keyword~',$Ae)&&($X[0]=="{"||$X[0]=="[")&&($Lg=json_decode($X))){if($vk!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode($Lg,$Qe);return
true;}return
false;}function
getServerVariables(){return
show_variables();}function
getStatusVariables(){return
show_status();}abstract
function
getDumpOutputs();abstract
function
getDumpFormats();abstract
function
sendDumpHeaders($Sf,$pi=false);function
dumpDatabase($Rc){}abstract
function
dumpTable($Q,$Km,$Fo=0);abstract
function
dumpData($Q,$Km,$F);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();function
printNavigation($ji){$fh=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n",$this->admin->getServiceTitle()."\n";if($ji!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if($this->config->isVersionVerificationEnabled()&&$fh&&version_compare(VERSION,$fh)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($fh)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if($this->config->isVersionVerificationEnabled()&&!$fh)echo
script("verifyVersion('".js_escape(ME)."', '".get_token()."');");}echo"</div>\n";}abstract
function
printDatabaseSwitcher($ji);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(8)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase()).");")."</div>\n";}abstract
function
printTableList(array$S);function
getSettingsRows($tf){$N=[];if($tf==1){$B=get_language_options();if($B)$N["lang"]="<tr><th id='label-language'>".lang(9)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$B=[""=>lang(10),Settings::$ColorSchemeLight=>lang(11),Settings::$ColorSchemeDark=>lang(12)];$N["colorScheme"]="<tr><th>".lang(13)."</th>"."<td>".html_radios("colorScheme",$B,($ua=$this->settings->getParameter("colorScheme"))!==null?$ua:"")."</td></tr>\n";}elseif($tf==2){$B=[""=>lang(14),true=>lang(15),false=>lang(16),];$i=$B[$this->config->isRelationLinks()];$B[""].=" ($i)";$N["relationLinks"]="<tr><th>".lang(17)."</th>"."<td>".html_radios("relationLinks",$B,($ua=$this->settings->getParameter("relationLinks"))!==null?$ua:"")."<span class='input-hint'>".lang(18)."</span>"."</td></tr>\n";$i=$this->config->getRecordsPerPage();$B=[""=>lang(14)." ($i)","20","30","50","70","100",];$N["recordsPerPage"]="<tr><th id='label-records'>".lang(19)."</th>"."<td>".html_select("recordsPerPage",$B,($ua=$this->settings->getParameter("recordsPerPage"))!==null?$ua:"","","label-records")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$i=($ua=$this->config->getEnumAsSelectThreshold())!==null?$ua:lang(21);$B=[""=>lang(14)." ($i)",-1=>lang(21),0=>lang(22),3=>lang(23,3),5=>lang(23,5),10=>lang(23,10),20=>lang(23,20),];$N["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(24)."</th>"."<td>".html_select("enumAsSelectThreshold",$B,($ua=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ua:"","","label-enum",true)."<span class='input-hint'>".lang(25)."</span>"."</td></tr>\n";}return$N;}abstract
function
getForeignColumnInfo(array$Ve,$c);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$Ca,array$jk){$this->plugins=$jk;foreach(get_class_methods('\AdminNeo\Origin')as$gi){$this->hooks[$gi]=[];if(!(isset(self::$InternalMethods[$gi])?self::$InternalMethods[$gi]:false)){foreach($jk
as$ik){if(method_exists($ik,$gi))$this->hooks[$gi][]=$ik;}}if(isset(self::$AppendMethods[$gi])?self::$AppendMethods[$gi]:false)array_unshift($this->hooks[$gi],$Ca);else$this->hooks[$gi][]=$Ca;}}function
getPlugins(){return$this->plugins;}function
__call($_,array$C){$Ra=isset(self::$AppendMethods[$_])?self::$AppendMethods[$_]:false;$H=$Ra?[]:null;assert(isset($this->hooks[$_]),"Calling unknown plugin method: $_");foreach($this->hooks[$_]as$ik){$X=call_user_func_array([$ik,$_],$C);if($X!==null){if($Ra)$H+=$X;else
return$X;}}return$H;}function
updateCspHeader(array&$Ic){$this->__call(__FUNCTION__,[&$Ic]);}function
detectJson($Ae,&$X,$vk=null){return$this->__call(__FUNCTION__,[$Ae,&$X,$vk]);}}class
Admin
extends
Origin{function
getOperators(){return
Driver::get()->getOperators();}function
getServiceTitle(){return"<a href='".h(HOME_URL)."'><svg role='img' class='logo' width='133' height='28'><desc>AdminNeo</desc><use href='".link_files("logo.svg",[])."#logo'/></svg></a>";}function
getDatabase(){return
DB;}function
getQueryTimeout(){return
2;}function
printToHead(){echo"<link rel='stylesheet' href='",link_files("jush.css",[]),"'>";if(!$this->admin->isLightModeForced())echo"<link rel='stylesheet' ".(!$this->admin->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("jush-dark.css",[]),"'>\n";echo
script_src(link_files("jush.js",[]),true);}function
printLoginForm(){$zd=Drivers::getList();$Yl=$this->config->getServerPairs($zd);$M=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($Yl)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($Yl,$M,true)."</select>");else{$xd=DRIVER?:$this->config->getDefaultDriver($zd);if(count($zd)>1)echo$this->admin->getLoginFormRow('driver',lang(26),html_select("auth[driver]",$zd,$xd).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$xd));echo$this->admin->getLoginFormRow('server',lang(5),'<input class="input" name="auth[server]" value="'.h($M).'" title="'.lang(27).'" placeholder="localhost" autocapitalize="off">');}echo$this->admin->getLoginFormRow('username',lang(28),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(29),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$Yl){$Rc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(30),'<input class="input" name="auth[db]" value="'.h($Rc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(31)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(32)),"</p>\n";}function
getFieldName(array$k,$pj=0){$T=$k["full_type"].($k["null"]?" NULL":"");$gc=$k["comment"];$Rl=$T&&$gc!=""?": ":"";return'<span title="'.h($T.$Rl.$gc).'">'.h($k["field"]).'</span>';}function
printTableMenu(array$an,$pg){echo'<p class="links top-tabs">';$sh=[];$Nl=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($Nl)$sh["select"]=[lang(33),"data"];if(support("table")||support("indexes"))$sh["table"]=[lang(34),"structure"];if(!$Nl)$sh["select"]=[lang(33),"data"];$Q=$an["Name"];$Gg=false;if(support("table")){$Gg=is_view($an);if(!$Gg){if($Q!="")$sh["create"]=[lang(35),"edit"];}elseif(support("view"))$sh["view"]=[lang(36),"edit"];}if($pg!==null)$sh["edit"]=[lang(7),"item-add"];$C=$pg?"&".http_build_query($pg):"";foreach($sh
as$u=>$W)echo" <a href='",h(ME),"$u=",urlencode($Q),($u=="edit"?$C:""),"'",bold(isset($_GET[$u])),">",icon($W[1]),"$W[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($Q,$Gg)],icon("help").lang(37)),"\n";}function
formatSelectQuery($F,$Dm,$ve=false){$Qm=support("sql");$Mo=!$ve?Driver::get()->warnings():null;if($Qm)$F
.=";";$Tm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$I="<pre><code class='jush-$Tm'>".h(str_replace("\n"," ",$F))."</code></pre>\n";$I
.="<p class='links'>";if($Qm)$I
.="<a href='".h(ME)."sql=".urlencode($F)."'>".icon("edit").lang(38)."</a>";if($Mo)$I
.="<a href='#warnings' class='toggle'>".lang(39).icon_chevron_down()."</a>";$I
.=" <span class='time'>(".format_time($Dm).")</span>";$I
.="</p>\n";if($Mo){$I
.=script("initToggles(qsl('p'));");$I
.="<div id='warnings' class='warnings hidden'>\n$Mo\n</div>\n";}return$I;}function
formatMessageQuery($F,$Bn,$ve=false){restart_session();$If=&get_session("queries");if(!isset($If[$_GET["db"]]))$If[$_GET["db"]]=[];if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$If[$_GET["db"]][]=[$F,time(),$Bn];$Qm=support("sql");$Mo=!$ve?Driver::get()->warnings():null;$ym="sql-".count($If[$_GET["db"]]);$No="warnings-".count($If[$_GET["db"]]);$I=" ";if($Mo)$I
.="<a href='#$No' class='toggle'>".lang(39).icon_chevron_down()."</a>, ";$Ik=support("sql")?lang(40):lang(41);$I
.="<a href='#$ym' class='toggle'>$Ik".icon_chevron_down()."</a>";$I
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($Mo)$I
.="<div id='$No' class='warnings hidden'>\n$Mo</div>\n";$I
.="<div id='$ym' class='hidden'>\n";$Tm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$I
.="<pre><code class='jush-$Tm'>".truncate_utf8($F,1000)."</code></pre>\n";$I
.="<p class='links'>";if($Qm)$I
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($If[$_GET["db"]])-1))."'>".icon("edit").lang(38)."</a>";if($Bn)$I
.=" <span class='time'>($Bn)</span>";$I
.="</p>\n";$I
.="</div>\n";return$I;}function
formatSqlCommandQuery($F){if(preg_match('~^DELIMITER\s~i',$F))return"";return
truncate_utf8($F,1000);}function
getTableDescriptionFieldName($Q){return"";}function
fillForeignDescriptions(array$K,array$Ve){return$K;}function
formatSelectionValue($W,$x,$k,$Aj){if($W===null)$vn="<i>NULL</i>";elseif(!$k)$vn=$W;elseif(preg_match("~char|binary|boolean~",$k["type"])&&!preg_match("~var~",$k["type"]))$vn="<code>$W</code>";elseif(is_blob($k)&&!is_utf8($W))$vn="<i>".lang(42,strlen($Aj))."</i>";elseif($this->admin->detectJson($k["full_type"],$Aj))$vn="<code class='jush-json'>$W</code>";else$vn=$W;if($x)$vn="<a href='".h($x)."'".(is_web_url($x)?target_blank():"").">$vn</a>";return$vn;}function
formatFieldValue($X,array$k){return$X;}function
printTableStructure(array$l){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(43),"</th>","<td>",lang(44),"</td>","<td>",lang(45),"</td>";if(support("comment"))echo"<td>",lang(46),"</td>";echo"</tr></thead>\n";$uo=Driver::get()->getUserTypes();foreach($l
as$k){echo"<tr>","<th>",h($k["field"]),"</th>","<td>";$T=h($k["full_type"]);if(in_array($T,$uo))echo"<a href='".h(ME.'type='.urlencode($T))."'>$T</a>";else
echo$T;if($k["null"])echo" <i>NULL</i>";if($k["auto_increment"])echo" <i>".lang(47)."</i>";$i=h($k["default"]);if(isset($k["default"]))echo" <span title='".lang(48)."'>[<b>",$k["generated"]?"<code class='jush-".DIALECT."'>$i</code>":$i,"</b>]</span>";echo"</td>","<td>",h($k["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($k["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$Sj){$jm=isset($Sj["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$Sj["partition_by"]} ({$Sj["partition"]})</code>";if(!$jm&&isset($Sj["partitions"]))echo" ".lang(49).": ".h($Sj["partitions"]);echo"</p>";if($jm){echo"<table>\n","<thead><tr><th>".lang(50)."</th><td>".lang(51)."</td></tr></thead>\n";foreach($Sj["partition_names"]as$u=>$_){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($_)),"'>";echo
h($_);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($Sj["partition_values"][$u])."\n";}echo"</table>\n";}}function
printRelatedTables(array$S){echo"<ul class='links'>\n";foreach($S
as$J){$x=preg_replace('~ns=[^&]*~',"ns=".urlencode($J["ns"]),ME);echo"<li><a href='",h($x."table=".urlencode($J["table"])),"'>",icon("structure");if($J["ns"]!=$_GET["ns"])echo"<b>".h($J["ns"])."</b>.";echo
h($J["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$t,array$an){$Yc=first(Driver::get()->getIndexAlgorithms($an));$Pj=false;foreach($t
as$s){if(isset($s["partial"])?$s["partial"]:false){$Pj=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(52)," (",lang(53),")</td>";if($Pj)echo"<td>",lang(54),"</td>";echo"</tr></thead>\n";foreach($t
as$_=>$s){ksort($s["columns"]);$yk=[];foreach($s["columns"]as$u=>$W)$yk[]="<i>".h($W)."</i>".($s["lengths"][$u]?"(".h($s["lengths"][$u]).")":"").($s["descs"][$u]?" DESC":"");echo"<tr title='",h($_),"'>","<th>",h($s["type"]);if(isset($s['algorithm'])&&$s['algorithm']!=$Yc)echo" (",h($s['algorithm']),")";echo"</th>","<td>",implode(", ",$yk),"</td>";if($Pj){echo"<td>";if($s['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($s['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$L,array$d){print_fieldset_start("select",lang(55),"columns",(bool)$L,true);$L[""]=[];$p=0;foreach($L
as$u=>$W){$W=isset($_GET["columns"][$u])?$_GET["columns"][$u]:[];$c=select_input("name='columns[$p][col]'",$d,isset($W["col"])?$W["col"]:null,$u!==""?"selectFieldChange":"selectAddRow");echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$p][fun]",[-1=>""]+array_filter([lang(56)=>Driver::get()->getFunctions(),lang(57)=>Driver::get()->getGrouping()]),isset($W["fun"])?$W["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($u!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($c)";else
echo$c;echo" <button class='button light remove jsonly' title='",h(lang(58)),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$p++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$d,array$t){print_fieldset_start("search",lang(59),"search",(bool)$Z);foreach($t
as$p=>$s){if($s["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$s["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$p]' value='".h(isset($_GET["fulltext"][$p])?$_GET["fulltext"][$p]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$p]",1,isset($_GET["boolean"][$p]),"BOOL");echo"</div>\n";}}$Cb="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$p=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$p][col]'",$d,$W["col"],($W?"selectFieldChange":"selectAddRow"),"(".lang(60).")"),html_select("where[$p][op]",$this->getOperators(),$W["op"],$Cb),"<input type='text' class='input' name='where[$p][val]' value='".h($W["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $Cb }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".h(lang(58))."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$pj,array$d,array$t){print_fieldset_start("sort",lang(61),"sort",(bool)$pj,true);$_GET["order"][""]="";$p=0;foreach((array)$_GET["order"]as$u=>$W){if($u!=""&&$W=="")continue;echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$p]'",$d,$W,$u!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$p]",1,isset($_GET["desc"][$u]),lang(62))," <button class='button light remove jsonly' title='",h(lang(58)),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$p++;}print_fieldset_end("sort",true);}function
printSelectionLimit($w){echo"<fieldset><legend>".lang(63)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='$w'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($wn){if($wn!==null)echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($wn)."'>","</div></fieldset>\n";}function
printSelectionAction(array$t){echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(55)."'>"," <span id='noindex' title='".lang(66)."'></span>","<script".nonce().">\n";$d=new
stdClass();foreach($t
as$s){$Kc=reset($s["columns"]);if($s["type"]!="FULLTEXT"&&$Kc)$d->$Kc=null;}echo"const indexColumns = ".json_encode($d,JSON_UNESCAPED_UNICODE).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$d,array$t){$L=[];$rf=[];foreach((array)$_GET["columns"]as$u=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],Driver::get()->getFunctions())||in_array($W["fun"],Driver::get()->getGrouping())))){$L[$u]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],Driver::get()->getGrouping()))$rf[]=$L[$u];}}return[$L,$rf];}function
processSelectionSearch(array$l,array$t){$I=[];foreach($t
as$p=>$s){if($s["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$p]!="")$I[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$s["columns"])).") AGAINST (".q($_GET["fulltext"][$p]).(isset($_GET["boolean"][$p])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$Tb=$Z["col"];$ej=$Z["op"];$W=$Z["val"];if("$Tb$W"!=""&&in_array($ej,$this->getOperators())){$oc=[];foreach(($Tb!=""?[$Tb=>$l[$Tb]]:$l)as$_=>$k){$tk="";$nc=" $ej";$Si=DIALECT=="pgsql"&&$ej=="="&&$k["type"]=="oid";if($Si)$nc
.=" ".$this->admin->processFieldInput($k,$W)."::regproc";elseif(preg_match('~IN$~',$ej)){$Yf=process_length($W);$nc
.=" ".($Yf!=""?$Yf:"(NULL)");}elseif($ej=="SQL")$nc=" $W";elseif(preg_match('~^(I?LIKE) %%$~',$ej,$y))$nc=" $y[1] ".$this->admin->processFieldInput($k,"%$W%");elseif($ej=="FIND_IN_SET"){$tk="$ej(".q($W).", ";$nc=")";}elseif(!preg_match('~NULL$~',$ej))$nc
.=" ".$this->admin->processFieldInput($k,$W);if($Tb!=""||(isset($k["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$ej)?',':'').']+$~',$W)||!preg_match('~'.number_type().'|bit~',$k["type"]))&&(!preg_match("~[\x80-\xFF]~",$W)||preg_match('~char|text|enum|set~',$k["type"]))&&(!preg_match('~date|timestamp~',$k["type"])||preg_match('~^\d+-\d+-\d+~',$W))&&(!preg_match('~^elastic~',DRIVER)||$k["type"]!="boolean"||preg_match('~true|false~',$W))&&(!preg_match('~^elastic~',DRIVER)||strpos($ej,"regexp")===false||preg_match('~text|keyword~',$k["type"])))){if($Si)$oc[]=$tk.idf_escape($_).$nc;else$oc[]=$tk.Driver::get()->convertSearch(idf_escape($_),$Z,$k).$nc;}}if(count($oc)==1)$I[]=$oc[0];elseif($oc)$I[]="(".implode(" OR ",$oc).")";else$I[]="1 = 0";}}return$I;}function
processSelectionOrder(array$l,array$t){$I=[];foreach((array)$_GET["order"]as$u=>$W){if($W!="")$I[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$u])?" DESC":"");}return$I;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$k){$I=($k["null"]?"NULL/":"");$lo=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$u=>$jf){if(!$u||(!isset($_GET["call"])&&$lo)){foreach($jf
as$ck=>$W){if(!$ck||preg_match("~$ck~",$k["type"]))$I
.="/$W";}}if($u&&$jf&&!preg_match('~enum|set|bool~',$k["type"])&&!is_blob($k))$I
.="/SQL";}if($k["auto_increment"]&&!$lo)$I=lang(47);return
explode("/",$I);}function
getFieldInput($Q,array$k,$Xa,$X,$if){return"";}function
processFieldInput(array$k,$X,$if=""){if($if=="SQL")return$X;if(isset($k["full_type"]))$this->admin->detectJson($k["full_type"],$X,false);$_=$k["field"];$I=q($X);if(preg_match('~^(now|getdate|uuid)$~',$if))$I="$if()";elseif(preg_match('~^current_(date|timestamp)$~',$if))$I=$if;elseif(preg_match('~^([+-]|\|\|)$~',$if))$I=idf_escape($_)." $if $I";elseif(preg_match('~^[+-] interval$~',$if))$I=idf_escape($_)." $if ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&DIALECT!="pgsql"?$X:$I);elseif(preg_match('~^(addtime|subtime|concat)$~',$if))$I="$if(".idf_escape($_).", $I)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$if))$I="$if($I)";elseif($k["type"]=="boolean"&&DIALECT=="elastic")$I=$I=="0"?"false":"true";return
unconvert_field($k,$I);}function
getDumpOutputs(){$Ej=['file'=>lang(67),'text'=>lang(68),];if(function_exists('gzencode'))$Ej['gz']='gzip';return$Ej;}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
sendDumpHeaders($Sf,$pi=false){$Dj=$_POST["output"];$re=(str_contains($_POST["format"],"sql")?"sql":($pi?"tar":"csv"));if($Dj=="gz"){header("Content-Type: application/x-gzip");ob_start(function($P){return
gzencode($P);},1e6);}elseif($re=="tar")header("Content-Type: application/x-tar");elseif($re=="sql"||$Dj=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$re;}function
dumpTable($Q,$Km,$Fo=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Km)dump_csv(array_keys(fields($Q)));}else{if($Fo==2){$l=[];foreach(fields($Q)as$_=>$k)$l[]=idf_escape($_)." $k[full_type]";$Dc="CREATE TABLE ".table($Q)." (".implode(", ",$l).")";}else$Dc=create_sql($Q,$_POST["auto_increment"],$Km);set_utf8mb4($Dc);if($Km&&$Dc){if($Km=="DROP+CREATE"||$Fo==1)echo"DROP ".($Fo==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($Fo==1)$Dc=remove_definer($Dc);echo"$Dc;\n\n";}}}function
dumpData($Q,$Km,$F){if($Km){$Nh=(DIALECT=="sqlite"?0:1048576);$l=[];$Uf=false;if($_POST["format"]=="sql"){if($Km=="TRUNCATE+INSERT")echo
truncate_sql($Q).";\n";$l=fields($Q);if(DIALECT=="mssql"){foreach($l
as$k){if($k["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Uf=true;break;}}}}$H=Connection::get()->query($F,1);if($H){$ng="";$rb="";$Sg=[];$lf=[];$Nm="";$Bc=0;while($J=($Q!=''?$H->fetchAssoc():$H->fetchRow())){if(!$Sg){$Y=[];foreach($J
as$W){$k=$H->fetchField();if(!empty($l[$k->name]['generated'])){$lf[$k->name]=true;continue;}$Sg[]=$k->name;$u=idf_escape($k->name);$Y[]="$u = VALUES($u)";}$Nm=($Km=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($Km=="table"){dump_csv($Sg);$Km="INSERT";}dump_csv($J);}else{if(!$ng)$ng="INSERT INTO ".table($Q)." (".implode(", ",array_map('AdminNeo\idf_escape',$Sg)).") VALUES";foreach($J
as$u=>$W){if(isset($lf[$u])){unset($J[$u]);continue;}$k=$l[$u];$J[$u]=($W===null?"NULL":($W===false?0:unconvert_field($k,preg_match(number_type(),$k["type"])&&!preg_match('~\[~',$k["full_type"])&&is_numeric($W)?$W:(!is_blob($k)||is_utf8($W)?q($W):Driver::get()->quoteBinary($W)))));}$vl=($Nh?"\n":" ")."(".implode(",\t",$J).")";if(!$rb)$rb=$ng.$vl;elseif(DIALECT=="mssql"?$Bc%1000!=0:strlen($rb)+4+strlen($vl)+strlen($Nm)<$Nh)$rb
.=",$vl";else{echo$rb.$Nm;$rb=$ng.$vl;}}$Bc++;}if($rb)echo$rb.$Nm;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Uf)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$Ii=isset($_GET["ns"])?$_GET["ns"]:null;if($Ii==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(69),"</a>\n";if($Ii!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(70),"</a>\n";if($Ii!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(71),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(72),"</a>\n";echo"</p>\n";}function
printNavigation($ji){parent::printNavigation($ji);if($ji=="auth"){$Dj="";foreach((array)$_SESSION["pwds"]as$Bo=>$cm){foreach($cm
as$M=>$vo){foreach($vo
as$U=>$D){if($D!==null){$Wc=$_SESSION["db"][$Bo][$M][$U];foreach(($Wc?array_keys($Wc):[""])as$h){$Zl=$this->admin->getServerName($M,false);$En=h(get_driver_name($Bo,$M)).($U!=""||$Zl!=""?" - ":"").h($U).($U!=""&&$Zl!=""?"@":"").h($Zl).($h!=""?h(" - $h"):"");$Dj
.="<li><a href='".h(auth_url($Bo,$M,$U,$h))."' class='primary' title='$En'>$En</a></li>\n";}}}}}if($Dj)echo"<nav id='logins'><menu>\n$Dj</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($ji);$ya=[];if(DB==""||!$ji){if(support("sql")){$ya[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(40)."</a>";$ya[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(73)."</a>";}$ya[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(74)."</a>";}if(DB=="")$ya[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(75)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$ji)$ya[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$ji)$ya[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(77)."</a>\n";if($ya)echo"<p class='links'>".implode("\n",$ya)."</p>";$S=[];if($_GET["ns"]!==""&&!$ji&&DB!=""){Connection::get()->selectDatabase(DB);$S=table_status('',true);}if($_GET["ns"]!==""&&!$ji&&DB!=""){if($S){$this->admin->printTablesFilter();$this->admin->printTableList($S);}else
echo"<p class='message'>".lang(78)."</p>\n";}if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$S){$sh=[];foreach($S
as$Q=>$T)$sh[]=js_escape_re($Q);$Zm=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$Zm.'=$&'),': /\b(?<!\$)('.implode('|',$sh).')(?!\$)\b/g';if(support('routine')){foreach(routines()as$J)echo",\n",js_escape_key(ME.'function='.urlencode($J["SPECIFIC_NAME"]).'&name=$&'),': /\b'.js_escape_re($J["ROUTINE_NAME"]).'(?=["`\]]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$W)echo"jushLinks.$W = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$ln=array_fill_keys(array_keys($S),[]);foreach(Driver::get()->getAllFields()as$Q=>$l){foreach($l
as$k)$ln[$Q][]=$k["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($ln)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($ji){$g=$this->admin->getDatabases();if(!$g&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($g)echo"<select id='database-select' name='db' title='",lang(30),"'>".optionlist([""=>"(".lang(79).")"]+$g,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(30),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(80)."' class='button ".($g?"hidden":"")."'>\n","</div>";if(support("scheme")&&$ji!="db"&&DB!=""&&Connection::get()->selectDatabase(DB)){echo"<div>","<select id='scheme-select' name='ns' title='",lang(81),"'>".optionlist([""=>"(".lang(82).")"]+$this->admin->getSchemas(),$_GET["ns"])."</select>".script("mixin(gid('scheme-select'), {onmousedown: dbMouseDown, onchange: dbChange});"),"</div>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}foreach(["import","sql","schema","dump","privileges"]as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</form></div>\n";}function
printTableList(array$S){$Fd=$this->settings->isNavigationDual()||$this->settings->isNavigationHover();$Wh=($Fd?"class='dual".($this->settings->isNavigationHover()?" hover":"")."'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><menu $Wh>";foreach($S
as$Q=>$O){$Q="$Q";$_=$this->admin->getTableName($O);if($_==""||(isset($O["Partition"])?$O["Partition"]:false))continue;echo"<li>";$za=in_array($Q,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$Qb="primary".(is_view($O)?" view":"");$Rm=support("table")||support("indexes");$Kl=h(ME)."select=".urlencode($Q);$bn=h(ME)."table=".urlencode($Q);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$Rm)echo" <a href='$bn' title='",lang(34),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$Kl'",bold($za,$Qb)," data-primary='true' title='$_'>$_</a>";if($Fd&&$Rm)echo" <a href='$bn' title='",lang(34),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$Kl' title='",lang(33),"' class='secondary'>",icon("data"),"</a>";if($Rm)echo"<a href='$bn'",bold($za,$Qb)," data-primary='true' title='$_'>$_</a>";else
echo"<span data-primary='true'",bold($za,$Qb),">$_</span>";if($Fd)echo" <a href='$Kl' title='",lang(33),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase()).");");}function
getSettingsRows($tf){$N=parent::getSettingsRows($tf);if($tf==1){$B=[""=>lang(14),Config::$NavigationSimple=>lang(83),Config::$NavigationDual=>lang(84),Config::$NavigationHover=>lang(85),Config::$NavigationReversed=>lang(86)];$i=$B[$this->config->getNavigationMode()];$B[""].=" ($i)";$N["navigationMode"]="<tr><th>".lang(87)."</th>"."<td>".html_radios("navigationMode",$B,($ua=$this->settings->getParameter("navigationMode"))!==null?$ua:"")."<span class='input-hint'>".lang(88)."</span>"."</td></tr>\n";$B=[""=>lang(14),0=>lang(34),1=>lang(33),];$i=$B[$this->config->isSelectionPreferred()?1:0];$B[""].=" ($i)";$N["preferSelection"]="<tr><th id='label-links'>".lang(89)."</th>"."<td>".html_select("preferSelection",$B,($ua=$this->settings->getParameter("preferSelection"))!==null?$ua:"","","label-links",true)."<span class='input-hint'>".lang(90)."</span>"."</td></tr>\n";}return$N;}function
getForeignColumnInfo(array$Ve,$c){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($yc){if(!$this->handler)return;$this->size+=strlen($yc);fwrite($this->handler,$yc);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$H,$e=null,array$vj=[],$w=0){$sh=[];$t=[];$d=[];$nb=[];$bo=[];$I=[];for($p=0;(!$w||$p<$w)&&($J=$H->fetchRow());$p++){if(!$p){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($Kg=0;$Kg<count($J);$Kg++){$k=$H->fetchField();if(!$k){echo"<th></th>";continue;}$_=$k->name;$uj=isset($k->orgtable)?$k->orgtable:"";$tj=isset($k->orgname)?$k->orgname:$_;if(isset($k->table))$I[$k->table]=$uj;if($vj&&DIALECT=="sql")$sh[$Kg]=($_=="table"?"table=":($_=="possible_keys"?"indexes=":null));elseif($uj!=""){if(!isset($t[$uj])){$t[$uj]=[];foreach(indexes($uj,$e)as$s){if($s["type"]=="PRIMARY"){$t[$uj]=array_flip($s["columns"]);break;}}$d[$uj]=$t[$uj];}if(isset($d[$uj][$tj])){unset($d[$uj][$tj]);$t[$uj][$tj]=$Kg;$sh[$Kg]=$uj;}}if($k->charsetnr==63)$nb[$Kg]=true;$bo[$Kg]=$k->type;$En=trim(($uj!=""?"$uj.$tj":($k->name!=$tj?$tj:""))." ".Driver::get()->getTypeName($k));echo"<th".($En!=""?" title='".h($En)."'":"").">".h($_).($vj?doc_link(['sql'=>"explain-output.html#explain_".strtolower($_),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($J
as$u=>$W){$x="";if(isset($sh[$u])&&!$d[$sh[$u]]){if($vj&&DIALECT=="sql"){$Q=$J[array_search("table=",$sh)];$x=ME.$sh[$u].urlencode($vj[$Q]!=""?$vj[$Q]:$Q);}else{$x=ME."edit=".urlencode($sh[$u]);foreach($t[$sh[$u]]as$Tb=>$Kg)$x
.="&where".urlencode("[".bracket_escape($Tb)."]")."=".urlencode($J[$Kg]);}}$T=($nb[$u]?'blob':($bo[$u]==254?'char':''));$k=['full_type'=>$T,'type'=>$T,];$W=select_value($W,$x,$k,null);$Qb=$bo[$u]<=9||$bo[$u]==246?"class='number'":"";echo"<td $Qb>$W</td>";}}if($p)echo"</table>\n</div>";else
echo"<p class='message'>".lang(91);echo"\n";return$I;}function
referencable_primary($Pl){$I=[];foreach(table_status('',true)as$en=>$Q){if($en!=$Pl&&fk_support($Q)){foreach(fields($en)as$k){if($k["primary"]){if($I[$en]){unset($I[$en]);break;}$I[$en]=$k;}}}}return$I;}function
textarea($_,$X,$K=10,$ac=80){echo"<textarea name='".h($_)."' rows='$K' cols='$ac' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($Xa,$B,$X="",$cj="",$fk=""){$pn=($B?"select":"input");return"<$pn $Xa".($B?"><option value=''>$fk".optionlist($B,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$fk'>").($cj?script("qsl('$pn').onchange = $cj;",""):"");}function
json_row($u,$W=null){static$Le=true;if($Le)echo"{";if($u!=""){echo($Le?"":",")."\n\t\"".addcslashes($u,"\r\n\t\"\\/").'": '.($W!==null?'"'.addcslashes($W,"\r\n\t\"\\/").'"':'null');$Le=false;}else{echo"\n}\n";$Le=true;}}function
edit_type($u,$k,$Wb,$We=[],$ue=[]){$T=isset($k["type"])?$k["type"]:null;echo'<td><select name="',h($u),'[type]" class="type" aria-labelledby="label-type">';$yd=Driver::get()->getTypes();if($T&&!isset($yd[$T])&&!isset($We[$T])&&!in_array($T,$ue))$ue[]=$T;$Jm=Driver::get()->getStructuredTypes();if($We)$Jm[lang(92)]=$We;echo
optionlist(array_merge($ue,$Jm),$T),'</select><td><input name="',h($u),'[length]" value="',h(isset($k["length"])?$k["length"]:null),'" size="3"',(!(isset($k["length"])?$k["length"]:null)&&preg_match('~var(char|binary)$~',$T)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Wb?"<select name='".h($u)."[collation]'".(preg_match('~(char|text|enum|set)$~',$T)?"":" class='hidden'").'><option value="">('.lang(93).')'.optionlist($Wb,isset($k["collation"])?$k["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($u)."[unsigned]'".(!$T||preg_match(number_type(),$T)?"":" class='hidden'").'><option>'.optionlist(Driver::get()->getUnsigned(),isset($k["unsigned"])?$k["unsigned"]:null).'</select>':''),(isset($k['on_update'])?"<select name='".h($u)."[on_update]'".(preg_match('~timestamp|datetime~',$T)?"":" class='hidden'").'>'.optionlist([""=>"(".lang(94).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"CURRENT_TIMESTAMP":$k["on_update"])).'</select>':''),($We?"<select name='".h($u)."[on_delete]'".(preg_match("~`~",$T)?"":" class='hidden'")."><option value=''>(".lang(95).")".optionlist(Driver::get()->getOnActions(),isset($k["on_delete"])?$k["on_delete"]:null)."</select> ":" ");}function
process_length($v){$Yd=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$Yd(?:\\s*,\\s*$Yd)*+\\s*\\)?\\s*\$~",$v)&&preg_match_all("~$Yd~",$v,$z)?"(".implode(",",$z[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$v)));}function
process_type($k,$Ub="COLLATE"){return" $k[type]".process_length($k["length"]).(preg_match(number_type(),$k["type"])&&in_array($k["unsigned"],Driver::get()->getUnsigned())?" $k[unsigned]":"").(preg_match('~char|text|enum|set~',$k["type"])&&$k["collation"]?" $Ub ".(DIALECT=="mssql"?$k["collation"]:q($k["collation"])):"");}function
process_field($k,$Zn){if($k["on_update"])$k["on_update"]=preg_replace('~current_timestamp(\(\))?~i',"CURRENT_TIMESTAMP",$k["on_update"]);return[idf_escape(trim($k["field"])),process_type($Zn),($k["null"]?" NULL":" NOT NULL"),default_value($k),(preg_match('~timestamp|datetime~',$k["type"])&&$k["on_update"]?" ON UPDATE ".$k["on_update"]:""),(support("comment")&&$k["comment"]!=""?" COMMENT ".q($k["comment"]):""),($k["auto_increment"]?auto_increment():null),];}function
default_value($k){if($k["default"]===null)return"";$i=str_replace("\r","",$k["default"]);$kf=$k["generated"];if(in_array($kf,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($i)".($kf=="VIRTUAL"?"":" $kf");else
return" GENERATED ALWAYS AS ($i) $kf";}if(stripos($i,"GENERATED ")===0)return" $i";if(preg_match('~char|binary|text|json|enum|set~',$k["type"])||preg_match('~^(?![a-z])~i',$i)){if(DIALECT=="sql"&&preg_match('~text|json~',$k["type"]))return" DEFAULT (".q($i).")";else
return" DEFAULT ".q($i);}else{$i=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$i);return" DEFAULT ".(DIALECT=="sqlite"?"($i)":$i);}}function
type_class($T){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$Qb=>$ck){if(preg_match("~$Qb|$ck~",$T))return"class='$Qb'";}return"";}function
edit_fields(array$l,array$Wb,$T="TABLE",$We=[]){$l=array_values($l);$kc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$hc=$kc?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<td class='jsonly'></td>";if($T=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($T=="TABLE"?lang(96):lang(97)),"</th>\n","<td id='label-type'>",lang(44),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(98),"</td>\n","<td>",lang(99),"</td>\n";if($T=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(47),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",]),"</td>\n","<td id='label-default'>",lang(48),"</td>\n",support("comment")?"<td id='label-comment' $hc>".lang(46)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($l)),"]' value='1' title='",h(lang(100)),"' class='button light'>",icon_solo("add"),"</button>",(support("move_col")?"":script("qsl('button').onclick = onAddLastFieldRowClick;")),script("row_count = ".count($l).";"),"</td>\n","</tr></thead>\n";$Qb=support("move_col")?"class='sortable'":"";echo"<tbody $Qb>\n";foreach($l
as$p=>$k){$p++;$wj=$k[($_POST?"orig":"field")];$od=(isset($_POST["add"][$p-1])||(isset($k["field"])&&!(isset($_POST["drop_col"][$p])?$_POST["drop_col"][$p]:null)))&&(support("drop_col")||$wj=="");echo"<tr",($od?"":" hidden"),">\n";if(support("move_col"))echo"<td class='handle jsonly'>",icon_solo("handle"),"</td>";if($T=="PROCEDURE")echo"<td>",html_select("fields[$p][inout]",Driver::get()->getInOut(),$k["inout"]),"</td>\n";echo"<th>";if($od)echo"<input class='input' name='fields[$p][field]' value='",h($k["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$p-1])?"autofocus":"").">";echo
input_hidden("fields[$p][orig]",$wj);edit_type("fields[$p]",$k,$Wb,$We);echo"</th>\n";if($T=="TABLE"){echo"<td>",checkbox("fields[$p][null]",1,$k["null"],"","","block","label-null"),"</td>\n";$Kb=$k["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$p' $Kb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$p][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$k["generated"]);else
echo
checkbox("fields[$p][generated]",1,$k["generated"],"","","","label-default");$Xa="name='fields[$p][default]' aria-labelledby='label-default'";$X=h($k["default"]);if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Xa rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Xa value='$X'>";echo"</td>\n";if(support("comment")){$Mh=Connection::get()->isMinVersion("5.5")?1024:255;echo"<td $hc>","<input class='input' name='fields[$p][comment]' value='",h($k["comment"]),"' data-maxlength='$Mh' aria-labelledby='label-comment'>","</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$p]' value='1' title='".h(lang(100))."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$p]' value='1' title='".h(lang(101))."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$p]' value='1' title='".h(lang(102))."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($wj==""||support("drop_col"))echo"<button name='drop_col[$p]' value='1' title='".h(lang(58))."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$l){$A=0;if($_POST["up"]){$dh=0;foreach($l
as$u=>$k){if(key($_POST["up"])==$u){unset($l[$u]);array_splice($l,$dh,0,[$k]);break;}if(isset($k["field"]))$dh=$A;$A++;}}elseif($_POST["down"]){$bf=false;foreach($l
as$u=>$k){if(isset($k["field"])&&$bf){unset($l[key($_POST["down"])]);array_splice($l,$A,0,[$bf]);break;}if(key($_POST["down"])==$u)$bf=$k;$A++;}}elseif($_POST["add"]){$l=array_values($l);array_splice($l,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($y){$W=$y[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($W[0].$W[0],$W[0],substr($W,1,-1))),'\\'))."'";}function
grant($of,array$Ak,$d,$aj,$to){if(!$Ak)return
true;if($Ak==["ALL PRIVILEGES","GRANT OPTION"]){if($of)return(bool)queries("GRANT ALL PRIVILEGES ON $aj TO $to WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $aj FROM $to")&&queries("REVOKE GRANT OPTION ON $aj FROM $to");}if($Ak==["GRANT OPTION","PROXY"]){if($of)return(bool)queries("GRANT PROXY ON $aj TO $to WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $aj FROM $to");}return(bool)queries(($of?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$d, ",$Ak).$d)." ON $aj ".($of?"TO ":"FROM ").$to);}function
drop_create($_d,$Dc,$Bd,$un,$Dd,$Ah,$ai,$Yh,$Zh,$Yi,$Ci){if($_POST["drop"])query_redirect($_d,$Ah,$ai);elseif($Yi=="")query_redirect($Dc,$Ah,$Zh);elseif($Yi!=$Ci){$Gc=queries($Dc);queries_redirect($Ah,$Yh,$Gc&&queries($_d));if($Gc)queries($Bd);}else
queries_redirect($Ah,$Yh,queries($un)&&queries($Dd)&&queries($_d)&&queries($Dc));}function
create_trigger($aj,array$Sn){$Dn=" $Sn[Timing] $Sn[Event]".(preg_match('~ OF~',$Sn["Event"])?" $Sn[Of]":"");return"CREATE TRIGGER ".idf_escape($Sn["Trigger"]).(DIALECT=="mssql"?$aj.$Dn:$Dn.$aj).rtrim(" $Sn[Type]\n$Sn[Statement]",";").";";}function
create_routine($pl,$J){$gm=[];$l=(array)$J["fields"];ksort($l);$Zf=implode("|",Driver::get()->getInOut());foreach($l
as$k){if($k["field"]!="")$gm[]=(preg_match("~^($Zf)\$~",$k["inout"])?"$k[inout] ":"").idf_escape($k["field"]).process_type($k,"CHARACTER SET");}$cd=rtrim($J["definition"],";");return"CREATE $pl ".idf_escape(trim($J["name"]))." (".implode(", ",$gm).")".($pl=="FUNCTION"?" RETURNS".process_type($J["returns"],"CHARACTER SET"):"").($J["language"]?" LANGUAGE $J[language]":"").(DIALECT=="pgsql"?" AS ".q($cd):"\n$cd;");}function
remove_definer($F){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$F);}function
format_foreign_key($o){$bj=implode("|",Driver::get()->getOnActions());$h=$o["db"];$Ii=$o["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$o["source"])).") REFERENCES ".($h!=""&&$h!=$_GET["db"]?idf_escape($h).".":"").($Ii!=""&&$Ii!=$_GET["ns"]?idf_escape($Ii).".":"").idf_escape($o["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$o["target"])).")".(preg_match("~^($bj)\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^($bj)\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").(isset($o["deferrable"])?" $o[deferrable]":"");}function
tar_file($n,TmpFile$Hn){$Ef=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($Hn->getSize()),decoct(time()));$Mb=8*32;for($p=0;$p<strlen($Ef);$p++)$Mb+=ord($Ef[$p]);$Ef
.=sprintf("%06o",$Mb)."\0 ";echo$Ef,str_repeat("\0",512-strlen($Ef));$Hn->send();echo
str_repeat("\0",511-($Hn->getSize()+511)%512);}function
doc_link(array$bk,$vn="<sup>?</sup>"){if(!(isset($bk[DIALECT])?$bk[DIALECT]:null))return"";$Co=doc_version();$oo=['sql'=>"https://dev.mysql.com/doc/refman/$Co/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$Co)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$Co)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$Co/",];if(Connection::get()->isMariaDB()){$oo['sql']="https://mariadb.com/docs/server/";$bk['sql']=isset($bk['mariadb'])?$bk['mariadb']:str_replace(".html","",$bk['sql']);}return"<a href='".h($oo[DIALECT].$bk[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$Co":""))."'".target_blank().">$vn</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($h){if(!Connection::get()->selectDatabase($h))return"?";$I=0;foreach(table_status()as$R)$I+=$R["Data_length"]+$R["Index_length"];return
format_number($I);}function
set_utf8mb4($Dc){static$gm=false;if(!$gm&&preg_match('~\butf8mb4~i',$Dc)){$gm=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($ae,$j){return(bool)preg_match('~^Undefined (array key|offset|index)~',$j);},E_WARNING|E_NOTICE);;$Ie=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Ie||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$W){$io=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($io)$$W=$io;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$C,$u=null){$this->params=$C;$this->key=$u;}function
getKey(){return
isset($this->key)?$this->key:substr(md5($this->getDriver().$this->getServer()),0,8);}function
getDriver(){return$this->params["driver"];}function
getServer(){return
isset($this->params["server"])?$this->params["server"]:"";}function
getDatabase(){return
isset($this->params["database"])?$this->params["database"]:"";}function
getName(){return
isset($this->params["name"])?$this->params["name"]:(isset($this->params["server"])?$this->params["server"]:"");}function
getUsername(){return
isset($this->params["username"])?$this->params["username"]:"";}function
getPassword(){return
isset($this->params["password"])?$this->params["password"]:"";}function
hasCredentials(){return$this->getUsername()!=""||$this->getPassword()!="";}function
getConfigParams(){$C=isset($this->params["config"])?$this->params["config"]:[];$nf=["servers"];foreach($nf
as$Kj){if(isset($C[$Kj]))unset($C[$Kj]);}return$C;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationHover="hover";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$C){$this->params=$C;if(isset($this->params["servers"])){foreach($this->params["servers"]as$u=>$M){$Xl=new
Server($M,is_string($u)?$u:null);$this->params["servers"][$u]=$Xl;$this->servers[$Xl->getKey()]=$Xl;}}}function
getTheme(){return
isset($this->params["theme"])?$this->params["theme"]:"default";}function
getColorVariant(){return
isset($this->params["colorVariant"])?$this->params["colorVariant"]:"blue";}function
getCssUrls(){return$this->parseList(isset($this->params["cssUrls"])?$this->params["cssUrls"]:[]);}function
getJsUrls(){return$this->parseList(isset($this->params["jsUrls"])?$this->params["jsUrls"]:[]);}function
getNavigationMode(){return
isset($this->params["navigationMode"])?$this->params["navigationMode"]:self::$NavigationSimple;}function
isNavigationSimple(){return$this->getNavigationMode()==self::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==self::$NavigationDual;}function
isNavigationReversed(){return$this->getNavigationMode()==self::$NavigationReversed;}function
isSelectionPreferred(){return
isset($this->params["preferSelection"])?$this->params["preferSelection"]:false;}function
isJsonValuesDetection(){return
isset($this->params["jsonValuesDetection"])?$this->params["jsonValuesDetection"]:false;}function
isJsonValuesAutoFormat(){return
isset($this->params["jsonValuesAutoFormat"])?$this->params["jsonValuesAutoFormat"]:false;}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:false;}function
getRecordsPerPage(){return(int)(isset($this->params["recordsPerPage"])?$this->params["recordsPerPage"]:50);}function
getEnumAsSelectThreshold(){if(array_key_exists("enumAsSelectThreshold",$this->params))return$this->params["enumAsSelectThreshold"]!==null?(int)$this->params["enumAsSelectThreshold"]:null;else
return
5;}function
isVersionVerificationEnabled(){return
isset($this->params["versionVerification"])?$this->params["versionVerification"]:true;}function
isSqlAutocompletionEnabled(){return
isset($this->params["sqlAutocompletion"])?$this->params["sqlAutocompletion"]:true;}function
getHiddenDatabases(){return$this->parseList(isset($this->params["hiddenDatabases"])?$this->params["hiddenDatabases"]:[]);}function
getHiddenSchemas(){return$this->parseList(isset($this->params["hiddenSchemas"])?$this->params["hiddenSchemas"]:[]);}function
getVisibleCollations(){return$this->parseList(isset($this->params["visibleCollations"])?$this->params["visibleCollations"]:[]);}function
getDefaultDriver(array$zd){$xd=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$xd&&isset($zd[$xd])?$xd:key($zd);}function
getDefaultServer(){$M=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($M===null)return
null;$Xl=isset($this->params["servers"][$M])?$this->params["servers"][$M]:null;if($Xl)return$Xl->getKey();return$M;}function
getDefaultDatabase(){return
isset($this->params["defaultDatabase"])?$this->params["defaultDatabase"]:null;}function
getDefaultPasswordHash(){return
isset($this->params["defaultPasswordHash"])?$this->params["defaultPasswordHash"]:null;}function
getSslKey(){return
isset($this->params["sslKey"])?$this->params["sslKey"]:null;}function
getSslCertificate(){return
isset($this->params["sslCertificate"])?$this->params["sslCertificate"]:null;}function
getSslCaCertificate(){return
isset($this->params["sslCaCertificate"])?$this->params["sslCaCertificate"]:null;}function
getSslTrustServerCertificate(){return
isset($this->params["sslTrustServerCertificate"])?$this->params["sslTrustServerCertificate"]:null;}function
getSslEncrypt(){return
isset($this->params["sslEncrypt"])?$this->params["sslEncrypt"]:null;}function
getSslMode(){return
isset($this->params["sslMode"])?$this->params["sslMode"]:null;}function
hasServers(){return
isset($this->params["servers"]);}function
getServerPairs(array$zd){$mm=null;foreach($this->servers
as$M){if(!isset($zd[$M->getDriver()]))continue;if(!$mm)$mm=$M->getDriver();elseif($M->getDriver()!=$mm){$mm=null;break;}}$Yl=[];foreach($this->servers
as$u=>$M){if(!isset($zd[$M->getDriver()]))continue;$Wl=$M->getName();if($mm&&$Wl)$Yl[$u]=$Wl;else$Yl[$u]=$zd[$M->getDriver()].($Wl!=""?" - $Wl":"");}return$Yl;}function
getServer($Vl){return
isset($this->servers[$Vl])?$this->servers[$Vl]:null;}function
applyServer($M){$M=$this->getServer($M);if(!$M)return;$this->params=array_merge($this->params,$M->getConfigParams());}private
function
parseList($uh){if(is_array($uh))return$uh;return
preg_split('~\s*,\s*~',(string)$uh);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$pc){$this->config=$pc;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($u){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$C);return
isset($C[$u])?$C[$u]:null;}function
getParameter($u,$i=null){return
isset($this->params[$u])?$this->params[$u]:$i;}function
updateParameter($u,$X){$this->updateParameters([$u=>$X]);}function
updateParameters(array$C){$this->params=array_filter(array_merge($this->params,$C),function($X){return$X!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ua=$this->getParameter("navigationMode"))!==null?$ua:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationHover(){return$this->getNavigationMode()==Config::$NavigationHover;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$Xo=$this->getParameter("navigationWidth");if($Xo===null)return
null;return
min(max((float)$Xo,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ua=$this->getParameter("preferSelection"))!==null?$ua:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ua=$this->getParameter("recordsPerPage"))!==null?$ua:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$X=$this->getParameter("enumAsSelectThreshold");if($X<0)return
null;return$X!==null?(int)$X:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($v,$u,$ig="",$wl=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$u,$v,$ig,$wl);if($wl=="")$wl=str_repeat("\0",20);$Bk=self::hmacSha1($u,$wl);$Ui="";for($Qg="",$ob=1;!isset($Ui[$v-1]);$ob++){$Qg=self::hmacSha1($Qg.$ig.chr($ob),$Bk);$Ui
.=$Qg;}return
substr($Ui,0,$v);}static
function
hmacSha1($f,$u){if(!extension_loaded("hash"))return
hash_hmac("sha1",$f,$u,true);if(strlen($u)>64)$u=sha1($u,true);$u=str_pad($u,64,"\0");$xg=($u^str_repeat("\x36",64));$fj=($u^str_repeat("\x5C",64));return
sha1($fj.sha1($xg.$f,true),true);}}class
Random{static
function
strongKey(){return
strtr(rtrim(base64_encode(Random::bytes(32)),"="),"+/","-_");}static
function
bytes($v){if(PHP_VERSION_ID>=70000)return
random_bytes($v);$H=self::tryAlternatives($v);if($H!==false)return$H;$H=self::lastResortRandom($v);if($H!==false)return$H;throw
new
Exception("Error generating random bytes");}private
static
function
tryAlternatives($v){if(extension_loaded("libsodium"))return
\Sodium\randombytes_buf($v);$ho=DIRECTORY_SEPARATOR==="/";if($ho){$H=self::readDevUrandom($v);if($H!==false)return$H;}$sb=$ho&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$sb){$H=mcrypt_create_iv($v,MCRYPT_DEV_URANDOM);if($H!==false)return$H;}$tb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$tb){$H=openssl_random_pseudo_bytes($v,$Im);if($Im)return$H;}return
false;}private
static
function
readDevUrandom($v){static$m=null;if($m===null)$m=@fopen("/dev/urandom","rb");if(!$m)return
false;$al=$v;$H="";do{$f=fread($m,$al);if($f===false)return
false;$al-=strlen($f);$H
.=$f;}while($al>0);return$H;}private
static
function
readCapicom($v){$dc=new
\COM("CAPICOM.Utilities.1");$al=$v;$H="";do{$f=base64_decode((string)$dc->GetRandom($v,0));$al-=strlen($f);$H
.=$f;}while($al>0);return$H;}private
static
function
lastResortRandom($v){static$u=null;static$wl=null;if($u===null){$f=$_SERVER;$f[]=uniqid("",true);shuffle($f);$u=sha1(serialize($f),true);if(extension_loaded("openssl"))$wl=openssl_random_pseudo_bytes(20);else{$wl="";for($p=0;$p<20;$p++)$wl
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($u)%2===0)===(ord($wl)%2===0))$u=Hash::hmacSha1($u,$wl);else$wl=Hash::hmacSha1($wl,$u);}return
Hash::hkdf($v,$u,"$v",$wl);}}if(!function_exists("str_starts_with")){function
str_starts_with($Df,$zi){return
strpos($Df,$zi)===0;}}if(!function_exists("str_contains")){function
str_contains($Df,$zi){return
strpos($Df,$zi)!==false;}}if(!function_exists("password_verify")){function
password_verify($D,$Cf){return
false;}}if(!function_exists("ini_set")){function
ini_set($lj,$X){return
false;}}function
version(){return
VERSION;}function
idf_unescape($r){if(!preg_match('~^[`\'"[]~',$r))return$r;$dh=substr($r,-1);return
str_replace($dh.$dh,$dh,substr($r,1,-1));}function
q($P){return
Connection::get()->quote($P);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$Y,$Ie=false){$I=[];foreach($Y
as$u=>$W)$I[stripslashes($u)]=(is_array($W)?remove_slashes($W,$Ie):($Ie?$W:stripslashes($W)));return$I;}function
bracket_escape($r,$gb=false){static$Nn=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($r,($gb?array_flip($Nn):$Nn));}function
min_version($Co,$Hh=null,$e=null){if(!$e)$e=Connection::get();if($Hh&&$e->isMariaDB())$Co=$Hh;return$Co&&$e->isMinVersion($Co);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($_,array$He){switch($_){case'favicon-blue.ico':$n='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__aff407a3.ico';break;case'favicon-green.ico':$n='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__aff407a3.ico';break;case'favicon-orange.ico':$n='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__aff407a3.ico';break;case'favicon-purple.ico':$n='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__aff407a3.ico';break;case'favicon-red.ico':$n='favicon-red-c2ebb34a8df5aba28e15d87728a151df__aff407a3.ico';break;case'favicon-blue.svg':$n='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__aff407a3.svg';break;case'favicon-green.svg':$n='favicon-green-bb254c95a033f67e3d433a3df63e160d__aff407a3.svg';break;case'favicon-orange.svg':$n='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__aff407a3.svg';break;case'favicon-purple.svg':$n='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__aff407a3.svg';break;case'favicon-red.svg':$n='favicon-red-a006e401273230fd6be80568c8361b57__aff407a3.svg';break;case'apple-touch-icon-blue.png':$n='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__aff407a3.png';break;case'apple-touch-icon-green.png':$n='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__aff407a3.png';break;case'apple-touch-icon-orange.png':$n='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__aff407a3.png';break;case'apple-touch-icon-purple.png':$n='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__aff407a3.png';break;case'apple-touch-icon-red.png':$n='apple-touch-icon-red-507228751d2170d047e72142d2c02390__aff407a3.png';break;case'logo.svg':$n='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$n='jush-b3a93b18444da26820ff61746521dede__72e4fe51.css';break;case'jush-dark.css':$n='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$n='jush-31ccd1ce96536a294822e952872683d1__788c27ad.js';break;case'icons.svg':$n='icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg';break;case'default-blue.css':$n='default-blue-564b3ff62703b0741b8754503c621af3__0c4866a9.css';break;case'default-green.css':$n='default-green-8facfae54345a3eb358848ed4141060f__0c4866a9.css';break;case'default-orange.css':$n='default-orange-4fd2276ffa8eaad143aec2dba3782911__01284cf1.css';break;case'default-purple.css':$n='default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__01284cf1.css';break;case'default-red.css':$n='default-red-9c7de6d1d78ea798bfef943c92b6b611__0c4866a9.css';break;case'default-blue-dark.css':$n='default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css';break;case'default-green-dark.css':$n='default-green-dark-d7e561f7fc07f913992951110461fd8c__7a7f64b1.css';break;case'default-orange-dark.css':$n='default-orange-dark-e6668a1545546a87b40acb95390b5283__3549fa11.css';break;case'default-purple-dark.css':$n='default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__3549fa11.css';break;case'default-red-dark.css':$n='default-red-dark-aa471f32fb495651c17bba291cd8b147__7a7f64b1.css';break;case'main.js':$n='main-eaf2ce2c3d91edbef355936903e47e59__e62e765a.js';break;default:$n=null;break;}if(!$n)return
null;return
BASE_URL."?file=".urldecode($n);}function
ini_bool($lj){$W=ini_get($lj);return
preg_match('~^(on|true|yes)$~i',$W)||(int)$W;}function
ini_bytes($kg){$W=ini_get($kg);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
sid(){static$I;if($I===null)$I=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$I;}function
save_driver_name($xd,$M,$_){restart_session();$_SESSION["drivers"][$xd][$M]=$_;stop_session();}function
get_driver_name($xd,$M=null){return
isset($_SESSION["drivers"][$xd][$M])?$_SESSION["drivers"][$xd][$M]:Drivers::get($xd);}function
save_login($xd,$M,$U,$D,$h=""){$u=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$xd][$M][$U]=$u?[encrypt_string($D,$u)]:$D;$_SESSION["db"][$xd][$M][$U][$h]=true;}function
delete_login($xd,$M,$U){unset($_SESSION["pwds"][$xd][$M][$U]);unset($_SESSION["db"][$xd][$M][$U]);}function
get_password(){$D=get_session("pwds");if(is_array($D))return$_COOKIE["neo_key"]?decrypt_string($D[0],$_COOKIE["neo_key"]):false;return$D;}function
get_vals($F,$c=0){$I=[];$H=Connection::get()->query($F);if(is_object($H)){while($J=$H->fetchRow())$I[]=$J[$c];}return$I;}function
get_key_vals($F,$e=null,$hm=true){if(!$e)$e=Connection::get();$I=[];$H=$e->query($F);if(is_object($H)){while($J=$H->fetchRow()){if($hm)$I[$J[0]]=$J[1];else$I[]=$J[0];}}return$I;}function
get_rows($F,$e=null,$j="<p class='error'>"){if(!$e)$e=Connection::get();$I=[];$H=$e->query($F);if(is_object($H)){while($J=$H->fetchAssoc())$I[]=$J;}elseif(!$H&&!is_object($e)&&$j&&(defined("AdminNeo\PAGE_HEADER")||$j=="-- "))echo$j.error()."\n";return$I;}function
unique_array(array$J,array$t){foreach($t
as$s){if(!preg_match("~PRIMARY|UNIQUE~",$s["type"])&&!$s["partial"])continue;$eo=[];foreach($s["columns"]as$u){if(!isset($J[$u]))continue
2;$eo[$u]=$J[$u];}return$eo;}return
null;}function
escape_key($u){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$u,$y))return$y[1].idf_escape(idf_unescape($y[2])).$y[3];return
idf_escape($u);}function
where($Z,$l=[]){$oc=[];foreach((array)$Z["where"]as$u=>$W){$u=bracket_escape($u,true);$c=escape_key($u);$De=isset($l[$u]["type"])?$l[$u]["type"]:null;$gf=isset($l[$u]["full_type"])?$l[$u]["full_type"]:null;if(DIALECT=="sql"&&$De=="json")$oc[]="$c = CAST(".q($W)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$gf))$oc[]="$c::jsonb = ".q($W)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($W)&&strpos($W,".")!==false)$oc[]="$c LIKE ".q($W);elseif(DIALECT=="mssql"&&strpos($De,"datetime")===false)$oc[]="$c LIKE ".q(preg_replace('~[_%[]~','[\0]',$W));else$oc[]="$c = ".(isset($l[$u])?unconvert_field($l[$u],q($W)):q($W));if(DIALECT=="sql"&&preg_match('~char|text~',$De)&&preg_match("~[^ -@]~",$W))$oc[]="$c = ".q($W)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$u)$oc[]=escape_key($u)." IS NULL";return
implode(" AND ",$oc);}function
where_check($W,$l=[]){parse_str($W,$Gb);remove_slashes([&$Gb]);return
where($Gb,$l);}function
where_link($p,$c,$X,$ij="="){return"&where%5B$p%5D%5Bcol%5D=".urlencode($c)."&where%5B$p%5D%5Bop%5D=".urlencode(($X!==null?$ij:"IS NULL"))."&where%5B$p%5D%5Bval%5D=".urlencode($X);}function
convert_fields(array$d,array$l,array$L=[]){$H="";foreach($d
as$u=>$W){if($L&&!in_array(idf_escape($u),$L))continue;$Va=convert_field($l[$u]);if($Va)$H
.=", $Va AS ".idf_escape($u);}return$H;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($_,$X,$nh=2592000){header("Set-Cookie: $_=".rawurlencode($X).($nh?"; expires=".gmdate("D, d M Y H:i:s",time()+$nh)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($no,$zc){$I=@file_get_contents($no,false,$zc);if(function_exists('http_get_last_response_headers'))$http_response_header=($ua=http_get_last_response_headers())!==null?$ua:[];return[$I,isset($http_response_header)?$http_response_header:[]];}function
get_settings($Ac="neo_settings"){parse_str(isset($_COOKIE[$Ac])?$_COOKIE[$Ac]:"",$N);return$N;}function
get_setting($u,$Ac="neo_settings"){$N=get_settings($Ac);return
isset($N[$u])?$N[$u]:null;}function
save_settings(array$N,$Ac="neo_settings"){cookie($Ac,http_build_query($N+get_settings($Ac)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($Te=false){$ro=ini_bool("session.use_cookies");if(!$ro||$Te){session_write_close();if($ro&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($u){return$_SESSION[$u][DRIVER][SERVER][$_GET["username"]];}function
set_session($u,$W){$_SESSION[$u][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($Bo,$M,$U,$h=null){$mo=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($h!==null?"db|":"").($Bo=='mssql'||$Bo=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$mo,$y);return"$y[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").urlencode($Bo)."=".urlencode($M)."&".($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($U).($h!=""?"&db=".urlencode($h):"").($y[2]?"&$y[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Ah,$Xh=null){if($Xh!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Ah!==null?$Ah:$_SERVER["REQUEST_URI"]))][]=$Xh;}if($Ah!==null){if($Ah=="")$Ah=".";header("Location: $Ah");exit;}}function
query_redirect($F,$Ah,$Xh,$Qk=true,$ie=true,$ve=false,$Bn=""){if($ie){$Dm=microtime(true);$ve=!Connection::get()->query($F);$Bn=format_time($Dm);}$xm=$F?Admin::get()->formatMessageQuery($F,$Bn,$ve):"";if($ve){Admin::get()->addError(error().$xm.script("initToggles();"));return
false;}if($Qk)redirect($Ah,$Xh.$xm);return
true;}function
queries_redirect($Ah,$Xh,$Qk){$Hk=implode("\n",Queries::$queries);$Bn=format_time(Queries::$start);return
query_redirect($Hk,$Ah,$Xh,$Qk,false,!$Qk,$Bn);}class
Queries{static$queries=[];static$start=0.0;}function
queries($F){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql")){Queries::$queries[]=(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";";return
Connection::get()->query($F);}else{Queries::$queries[]=$F;return[];}}function
apply_queries($F,array$S,$ce='AdminNeo\table'){foreach($S
as$Q){if(!queries("$F ".$ce($Q)))return
false;}return
true;}function
format_time($Dm){return
lang(103,max(0,microtime(true)-$Dm));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($Kj=""){return
substr(preg_replace("~(?<=[?&])($Kj".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($u,$Xc=false,$fd=""){$m=$_FILES[$u];if(!$m)return
null;foreach($m
as$u=>$W)$m[$u]=(array)$W;$I='';foreach($m["error"]as$u=>$j){if($j)return$j;$_=$m["name"][$u];$In=$m["tmp_name"][$u];$xc=file_get_contents($Xc&&preg_match('~\.gz$~',$_)?"compress.zlib://$In":$In);if($Xc){$Dm=substr($xc,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Dm))$xc=iconv("utf-16","utf-8",$xc);elseif($Dm=="\xEF\xBB\xBF")$xc=substr($xc,3);}if($fd){if(!preg_match("~$fd\\s*\$~",$xc))$xc
.=";";$xc
.="\n\n";}$I
.=$xc;}return$I;}function
upload_error($j){$Qh=($j==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($j?lang(104).($Qh?" ".lang(105,$Qh):""):lang(106));}function
repeat_pattern($ck,$v){return
str_repeat("$ck{0,65535}",$v/65535)."$ck{0,".($v%65535)."}";}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
format_number($W){return
strtr(number_format($W,0,".",lang(107)),preg_split('~~u',lang(108),-1,PREG_SPLIT_NO_EMPTY));}function
format_rows(array$R){$K=$R["Rows"];$Sa=($K&&(DIALECT=="sqlite"||(isset($R["Engine"])?$R["Engine"]:"")==(DIALECT=="pgsql"?"table":"InnoDB")));return($Sa?"~ ":"").format_number($K);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($Q,$xe=false){$I=table_status($Q,$xe);return($I?reset($I):["Name"=>$Q]);}function
column_foreign_keys($Q){$I=[];foreach(Admin::get()->getForeignKeys($Q)as$o){foreach($o["source"]as$W)$I[$W][]=$o;}return$I;}function
fields_from_edit(){$I=[];foreach((array)$_POST["field_keys"]as$u=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$u];$_POST["fields"][$W]=$_POST["field_vals"][$u];}}foreach((array)$_POST["fields"]as$u=>$W){$_=bracket_escape($u,true);$I[$_]=["field"=>$_,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($u==Driver::get()->primary),];}return$I;}function
dump_headers($Sf,$qi=false){$Sf=friendly_url($Sf).date("-Ymd-His");$re=Admin::get()->sendDumpHeaders($Sf,$qi);$Dj=$_POST["output"];if($Dj!="text")header("Content-Disposition: attachment; filename=$Sf.$re".($Dj!="file"&&preg_match('~^[0-9a-z]+$~',$Dj)?".$Dj":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$re;}function
dump_table_order(array$wi,array$Wk){$Wg=array_flip($wi);$rj=[];$Ko=[];$Nc=false;$Jo=function($_)use(&$Jo,&$rj,&$Ko,&$Nc,$Wg,$Wk){if(isset($rj[$_]))return;if(isset($Ko[$_])){$Nc=true;return;}$Ko[$_]=true;foreach(isset($Wk[$_])?$Wk[$_]:[]as$Uk){if(isset($Wg[$Uk]))$Jo($Uk);}unset($Ko[$_]);$rj[$_]=true;};foreach($wi
as$_)$Jo($_);return($Nc?null:array_keys($rj));}function
dump_csv($J){$Yn=$_POST["format"]=="tsv";foreach($J
as$u=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Yn?'\t':'[,;]|^$').'~',$W))$J[$u]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($Yn?"\t":";")),$J)."\r\n";}function
apply_sql_function($if,$c){return($if?($if=="unixepoch"?"DATETIME($c, '$if')":($if=="count distinct"?"COUNT(DISTINCT ":strtoupper("$if("))."$c)"):$c);}function
get_temp_dir(){$ak=ini_get("upload_tmp_dir");if(!$ak)$ak=sys_get_temp_dir();return$ak;}function
open_file_with_lock($n){if(is_link($n))return
null;$m=@fopen($n,"c+");if(!$m)return
null;@chmod($n,0660);if(!flock($m,LOCK_EX)){fclose($m);return
null;}return$m;}function
write_and_unlock_file($m,$f){rewind($m);fwrite($m,$f);ftruncate($m,strlen($f));unlock_file($m);}function
unlock_file($m){flock($m,LOCK_UN);fclose($m);}function
first(array$Ua){return
reset($Ua);}function
get_private_key($Dc){$n=get_temp_dir()."/adminneo.key";if(!$Dc&&!file_exists($n))return
false;$m=open_file_with_lock($n);if(!$m)return
false;$u=stream_get_contents($m);if(!$u){$u=Random::strongKey();write_and_unlock_file($m,$u);}else
unlock_file($m);return$u;}function
get_random_string(){return
Random::strongKey();}function
select_value($W,$x,$k,$yn){if(is_array($W)){$I="";if(array_filter($W,'is_array')==array_values($W)){$Sg=[];foreach($W
as$V)$Sg+=array_fill_keys(array_keys($V),null);foreach(array_keys($Sg)as$Mg)$I
.="<th>".h($Mg);foreach($W
as$V){$I
.="<tr>";foreach(array_merge($Sg,$V)as$wo)$I
.="<td>".select_value($wo,$x,$k,$yn);}}else{foreach($W
as$Mg=>$V)$I
.="<tr>".($W!=array_values($W)?"<th>".h($Mg):"")."<td>".select_value($V,$x,$k,$yn);}return"<table>$I</table>";}$Al="";if($k&&$W!==null&&($yn===null||strlen($W)<=$yn)&&($Y=Driver::get()->explodeArrayValue($W,$k["full_type"],$Al))){$_l=$k;$_l["type"]=$_l["full_type"]=$Al;$I=select_array_value($Y,$W,$x,$_l,$yn);return
Driver::get()->implodeArrayValues($I,$k["full_type"]);}if(!$x)$x=Admin::get()->getFieldValueLink($W,$k);if($k)$W=Connection::get()->formatValue($W,$k);$I=$k?Admin::get()->formatFieldValue($W,$k):$W;if($I!==null){if(!is_utf8($I))$I="\0";elseif($yn!=""&&is_shortable($k))$I=truncate_utf8($I,max(0,+$yn));else$I=h($I);}return
Admin::get()->formatSelectionValue($I,$x,$k,$W);}function
select_array_value(array$Y,$W,$x,array$k,$yn){$H=[];foreach($Y
as$X){if(is_array($X))$H[]=select_array_value($X,$W,$x,$k,$yn);else{$Xg=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($W),"~")."~",'${1}'.urlencode($X),$x);$H[]=select_value($X,$Xg,$k,$yn);}}return$H;}function
is_blob(array$k){$bo=Driver::get()->getStructuredTypes();$T=lang(109);return
preg_match('~blob|bytea|raw|file'.(DIALECT=="mssql"?'|binary|image':'').'~',$k["type"])&&!in_array($k["type"],isset($bo[$T])?$bo[$T]:[]);}function
is_mail($X){return
is_string($X)&&filter_var($X,FILTER_VALIDATE_EMAIL);}function
is_web_url($X){if(!is_string($X)||!preg_match('~^(https?:)?//~i',$X))return
false;$lc=parse_url($X);if(!$lc)return
false;$no=$X;if(isset($lc['path'])){$Td=array_map('urlencode',explode('/',$lc['path']));$no=str_replace($lc['path'],implode('/',$Td),$no);}if(isset($lc['query'])){parse_str($lc['query'],$C);$no=str_replace($lc['query'],http_build_query($C),$no);}if(!isset($lc['scheme']))$no="https:$no";return(bool)filter_var($no,FILTER_VALIDATE_URL);}function
is_shortable($k){return$k&&!preg_match('~'.number_type().'|date|time|year~',$k["type"]);}function
host_port($M){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$M,$y)?[(isset($y[4])?$y[4]:"").(isset($y[5])?$y[5]:""),$y[2].(isset($y[8])?$y[8]:"")]:[$M,'']);}function
count_rows($Q,$Z,$Cg,$rf){$F=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($Cg&&(DIALECT=="sql"||count($rf)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$rf).")$F":"SELECT COUNT(*)".($Cg?" FROM (SELECT 1$F GROUP BY ".implode(", ",$rf).") x":$F));}function
slow_query($F){$h=Admin::get()->getDatabase();$Cn=Admin::get()->getQueryTimeout();$qm=Driver::get()->slowQuery($F,$Cn);$e=null;if(!$qm&&support("kill")){$e=connect();if($e&&($h==""||$e->selectDatabase($h))){$Ug=$e->getValue(connection_id());echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Ug,'&token=',get_token(),'\');
	}, ',1000*$Cn,');
</script>
';}}ob_flush();flush();$I=@get_key_vals(($qm?:$F),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$I;}function
get_token(){$Nk=rand(1,1e6);return($Nk^$_SESSION["token"]).":$Nk";}function
verify_token(){list($Jn,$Nk)=explode(":",$_POST["token"]);return($Nk^$_SESSION["token"])==$Jn&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($tm,$Mn="\n"){return"<script".nonce().">$tm</script>$Mn";}function
script_src($no,$bd=false){return"<script src='".h($no)."'".nonce().($bd?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($_,$X=""){return"<input type='hidden' name='".h($_)."' value='".h($X)."'>";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($P){if($P===null||$P==="")return"";return
str_replace(["&","<","\"","'","\0"],["&amp;","&lt;","&quot;","&#039;","&#0;"],$P);}function
truncate_utf8($P,$v=80){if($P=="")return"";if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$v).")($)?)u",$P,$y))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$v).")($)?)",$P,$y);return
h($y[1]).(isset($y[2])?"":"<i>…</i>");}function
icon_solo($q){return
icon($q,"solo");}function
icon_chevron_down(){return
icon("chevron-down","chevron");}function
icon_chevron_right(){return
icon("chevron-down","chevron-right");}function
icon($q,$Qb=null){$q=h($q);return"<svg class='icon ic-$q $Qb'><use href='".link_files("icons.svg",[])."#$q'/></svg>";}function
checkbox($_,$X,$Kb,$Yg="",$dj="",$Qb="",$ah=""){$I="<input type='checkbox' name='$_' value='".h($X)."'".($Kb?" checked":"").($ah?" aria-labelledby='$ah'":"").">".($dj?script("qsl('input').onclick = function () { $dj };",""):"");return($Yg!=""||$Qb?"<label".($Qb?" class='$Qb'":"").">$I".h($Yg)."</label>":$I);}function
optionlist($B,$Ml=null,$so=false){$I="";foreach($B
as$Mg=>$V){$oj=[$Mg=>$V];if(is_array($V)){$I
.='<optgroup label="'.h($Mg).'">';$oj=$V;}foreach($oj
as$u=>$W)$I
.='<option'.($so||is_string($u)?' value="'.h($u).'"':'').($Ml!==null&&($so||is_string($u)?(string)$u:$W)===$Ml?' selected':'').'>'.h($W);if(is_array($V))$I
.='</optgroup>';}return$I;}function
html_select($_,$B,$X="",$cj="",$ah="",$so=false){static$Yg=0;$Zg="";if(!$ah&&substr(isset($B[""])?$B[""]:"",0,1)=="("){$Yg++;$ah="label-$Yg";$Zg="<option value='' id='$ah'>".h($B[""]);unset($B[""]);}return"<select name='".h($_)."'".($ah?" aria-labelledby='$ah'":"").">".$Zg.optionlist($B,$X,$so)."</select>".($cj?script("qsl('select').onchange = function () { $cj };",""):"");}function
html_radios($_,$B,$X=""){$H="<span class='labels'>";foreach($B
as$u=>$W)$H
.="<label><input type='radio' name='".h($_)."' value='".h($u)."'".($u==$X?" checked":"").">".h($W)."</label>";$H
.="</span>";return$H;}function
confirm($Xh="",$Ol="qsl('input')"){return
script("$Ol.onclick = () => confirm('".($Xh?js_escape($Xh):lang(110))."');","");}function
print_fieldset_start($q,$jh,$Rf,$Ho=false,$sm=false){echo"<fieldset id='fieldset-$q' class='closable ".(!$Ho?" closed":"")."'>","<legend><a href='#'>$jh</a></legend>",icon($Rf,"fieldset-icon jsonly"),"<div class='fieldset-content".($sm?" sortable":"")."'>";}function
print_fieldset_end($q,$sm=false){echo"</div>",script("initFieldset('$q');","");if($sm)echo
script("initSortable('#fieldset-$q .fieldset-content');","");echo"</fieldset>\n";}function
bold($pb,$Qb=""){return($pb?" class='$Qb active'":($Qb?" class='$Qb'":""));}function
js_escape($P){return
addcslashes($P,"\r\n'\\/");}function
js_escape_key($P){return'"'.addcslashes($P,"\r\n\t\"\\/").'"';}function
js_escape_re($P){return
addcslashes(preg_quote($P,"/"),"\r\n");}function
pagination($Hj,$Jc){return"<li>".($Hj==$Jc?"<strong>".($Hj+1)."</strong>":'<a href="'.h(remove_from_uri("page").($Hj?"&page=$Hj".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($Hj+1)."</a>")."</li>";}function
print_hidden_fields(array$Ck,array$Wf=[],$tk=""){$H=false;foreach($Ck
as$u=>$W){if(!in_array($u,$Wf)){if(is_array($W))print_hidden_fields($W,[],$u);else{$H=true;echo
input_hidden($tk?$tk."[$u]":$u,$W);}}}return$H;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Xa,array$k,$X,$Qd=null,$Jb=false){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$z);$Y=$z[1];$An=Admin::get()->getSettings()->getEnumAsSelectThreshold();$L=!$Jb&&$An!==null&&count($Y)>$An;$T=$Jb?"checkbox":"radio";$_a=$L?"selected":"checked";$H=$L?"<select $Xa>":"<span class='labels'>";if($L&&$k["null"]&&$Qd!==""){$Kb=$X===null?$_a:"";$H
.="<option value='__adminneo_empty__' disabled $Kb></option>";}if($Qd!==null){$Kb=(is_array($X)?in_array($Qd,$X):$X===$Qd)?$_a:"";if($L)$H
.="<option value='$Qd' $Kb>".lang(111)."</option>";else$H
.="<label><input type='$T' $Xa value='$Qd' $Kb><i>".lang(111)."</i></label>";}foreach($Y
as$W){if($Qd===""&&$W==="")continue;$W=stripcslashes(str_replace("''","'",$W));$Kb=is_array($X)?in_array($W,$X):$X===$W;$Kb=$Kb?$_a:"";$af=$W===""?("<i>".lang(111)."</i>"):h(Admin::get()->formatFieldValue($W,$k));if($L)$H
.="<option value='".h($W)."' $Kb>$af</option>";else$H
.=" <label><input type='$T' $Xa value='".h($W)."' $Kb>$af</label>";}$H
.=$L?"</select>":"</span>";return$H;}function
input($k,$X,$if,$db=false){$_=h(bracket_escape($k["field"]));$bo=Driver::get()->getTypes();$Dg=isset($k["full_type"])&&Admin::get()->detectJson($k["full_type"],$X,true);$el=(DIALECT=="mssql"&&$k["auto_increment"]&&!$_POST["clone"]);if($el&&!$_POST["save"])$if=null;if(in_array($k["type"],Driver::get()->getUserTypes())){$Zd=type_values($bo[$k["type"]]);if($Zd){$k["type"]="enum";$k["length"]=$Zd;}}$Xa=" name='fields[$_]' ".($db?" autofocus":"");$jf=(isset($_GET["select"])||$el?["orig"=>lang(112)]:[])+Admin::get()->getFieldFunctions($k);$Af=(in_array($if,$jf)||isset($jf[$if]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($k)." ";if(count($jf)>1){$Ml=$if===null||$Af?$if:"";echo"<select name='function[$_]'>".optionlist($jf,$Ml)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($jf));echo"</td><td>";$lg=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$k,$Xa,$X,$if);if($lg!="")echo$lg;elseif(preg_match('~bool~',$k["type"]))echo"<input type='hidden'$Xa value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked='checked'":"")."$Xa value='1'>";elseif($k["type"]=="enum")echo
enum_input($Xa,$k,$X);elseif($k["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$z);echo"<span class='labels'>";foreach($z[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$Kb=$X!==null&&in_array($W,explode(",",$X),true);$Kb=$Kb?"checked":"";$af=$W===""?("<i>".lang(111)."</i>"):h(Admin::get()->formatFieldValue($W,$k));echo" <label><input type='checkbox' name='fields[$_][]' value='".h($W)."' $Kb>$af</label>";}echo"</span>";}elseif(is_blob($k)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$_'>";elseif($Dg)echo"<textarea $Xa cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($vn=preg_match('~text|lob|memo|json~i',$k["type"]))||preg_match("~\n~",$X)){if($vn&&DIALECT!="sqlite")$Xa
.=" cols='50' rows='12'";else{$K=min(12,substr_count($X,"\n")+1);$Xa
.=" cols='30' rows='$K'";}echo"<textarea $Xa>".h($X).'</textarea>';}else{$Th=!preg_match('~int~',$k["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$k["length"],$y)?((preg_match("~binary~",$k["type"])?2:1)*$y[1]+($y[3]?1:0)+($y[2]&&!$k["unsigned"]?1:0)):($bo&&$bo[$k["type"]]?$bo[$k["type"]]+($k["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$k["type"]))$Th+=7;echo"<input class='input'".((!$Af||$if==="")&&preg_match('~(?<!o)int(?!er)~',$k["type"])&&!preg_match('~\[\]~',$k["full_type"])?" type='number'":"").($if!="now"?" value='".h($X)."'":" data-last-value='".h($X)."'").($Th?" data-maxlength='$Th'":"").(preg_match('~char|binary~',$k["type"])&&$Th>20?" size='44'":"")."$Xa>";}$Hf=Admin::get()->getFieldInputHint($_GET["edit"],$k,$X);if($Hf!="")echo" <span class='input-hint'>$Hf</span>";if(count($jf)>1)echo
script("qs('select', qsl('td').previousSibling).onchange();","");$Me=0;foreach($jf
as$u=>$W){if($u===""||!$W)break;$Me++;}if(count($jf)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Me);");}function
process_input($k){$r=bracket_escape($k["field"]);$if=isset($_POST["function"][$r])?$_POST["function"][$r]:"";if($if=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?idf_escape($k["field"]):false);if($if=="NULL")return
Driver::get()->getNull();if(is_blob($k)&&ini_bool("file_uploads")){$m=get_file("fields-$r");if(!is_string($m))return
false;return
Driver::get()->quoteBinary($m);}$X=isset($_POST["fields"][$r])?$_POST["fields"][$r]:(isset($_FILES["fields"]["name"][$r])?$_FILES["fields"]["name"][$r]:null);if($X===null)return
false;if($k["auto_increment"]&&$X=="")return
null;if($k["type"]=="set")$X=implode(",",(array)$X);if($if=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}return
Admin::get()->processFieldInput($k,$X,$if);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$kl=$be=[];foreach(table_status("",true)as$Q=>$R){$en=Admin::get()->getTableName($R);if(!isset($R["Engine"])||$en==""||($_POST["tables"]&&!in_array($Q,$_POST["tables"])))continue;$H=Connection::get()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($Q),[])),1));if($H&&!$H->fetchRow())continue;$x=h(ME."select=".urlencode($Q)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($H)$kl[]="<li><a href='$x'>".icon("search")."$en</a></li>";else$be[]="<div class='error'><a href='$x'>$en</a>: ".error()."</div>";}if($kl)echo"<ul class='links'>\n",implode("\n",$kl),"</ul>\n";if($be)echo
implode("\n",$be),"\n";if(!$kl&&!$be)echo"<p class='message'>".lang(78)."</p>\n";}function
help_script($vn,$lm=false){return
script("initHelpFor(qsl('select, input'), '".h($vn)."', $lm);","");}function
help_script_command($ec,$lm=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $ec; }, $lm);","");}function
edit_form($Q,$l,$J,$lo){$en=Admin::get()->getTableName(table_status1($Q,true));$En=$lo?lang(38):lang(113);page_header("$En: $en",["select"=>[$Q,$en],$En]);if($J===false){echo"<p class='error'>".lang(91)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Ld=false;if(!$l)echo"<p class='error'>".lang(114)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$db=!$_POST;foreach($l
as$_=>$k){echo"<tr><th>".Admin::get()->getFieldName($k);$u=bracket_escape($_);$i=isset($_GET["preset"][$u])?$_GET["preset"][$u]:null;if($i===null){$i=$k["default"];if($k["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$i,$Yk))$i=$Yk[1];if(DIALECT=="sql"&&preg_match('~binary~',$k["type"]))$i=bin2hex($i);}$X=($J!==null?($J[$_]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$k["type"])&&is_array($J[$_])?implode(",",$J[$_]):(is_bool($J[$_])?+$J[$_]:$J[$_])):(!$lo&&$k["auto_increment"]?"":(isset($_GET["select"])?false:$i)));if(!$_POST["save"]&&is_string($X))$X=Admin::get()->formatFieldValue($X,$k);if(($lo&&!isset($k["privileges"]["update"]))||$k["generated"]){echo"<td class='function'></td><td>";if($lo||!$k["generated"])echo
select_value($X,'',$k,null);else
echo"<code class='jush-".DIALECT."'>",h($X),"</code>";echo"</td>";}else{$Ld=true;$if=($_POST["save"]?isset($_POST["function"][$u])?$_POST["function"][$u]:"":($lo&&preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$lo&&$X==$k["default"]&&preg_match('~^[\w.]+\(~',$X))$if="SQL";if(preg_match("~time~",$k["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$if="now";}if($k["type"]=="uuid"&&$X=="uuid()"){$X="";$if="uuid";}if($db!==false)$db=($k["auto_increment"]||$if=="now"||$if=="uuid"?null:true);input($k,$X,$if,(bool)$db);if($db)$db=false;}echo"\n";}if(!support("table")&&!fields($Q))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");}echo"<p>";if($Ld){echo"<input type='submit' class='button default' value='".lang(115)."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' class='button' name='insert' value='".($lo?lang(116):lang(117))."' title='Ctrl+Shift+Enter'>\n",($lo?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(118)."…', this); };"):"");}echo($lo?"<input type='submit' class='button' name='delete' value='".lang(119)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($Xe,$mg){$Lh=ini_get("max_file_uploads");$Qh=ini_get("upload_max_filesize");$Rh=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($Xe)."', '".js_escape($mg)."', "."$Lh, '".lang(120,$Lh,"\'max_file_uploads\'")."', "."$Rh, '".lang(121,$Qh,"\'upload_max_filesize\'")."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($P){$Pa=array_flip(str_split(compress_alphabet()));$v=strlen($P);$zo=($v?13*($v-1)/2-$Pa[$P[0]]:0);$lb="";$il=0;$jl=0;for($p=1;$p<$v;$p+=2){$il=($il<<13)+$Pa[$P[$p]]*93+$Pa[$P[$p+1]];$jl+=13;while($jl>=8&&$zo>=8){$jl-=8;$zo-=8;$lb
.=chr($il>>$jl);$il&=(1<<$jl)-1;}}if($lb=="")return"";return
function_exists('gzinflate')?gzinflate($lb):inflate($lb);}function
inflate($lb){$kh=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$lh=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$qd=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$sd=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$I="";$nk=0;do{$Ke=inflate_bits($lb,$nk,1);$T=inflate_bits($lb,$nk,2);if(!$T){$nk=($nk+7)&~7;$v=inflate_bits($lb,$nk,16);$nk+=16;$I
.=substr($lb,$nk>>3,$v);$nk+=$v<<3;}else{if($T==1){$wh=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$td=array_fill(0,30,5);}else{$vh=inflate_bits($lb,$nk,5)+257;$rd=inflate_bits($lb,$nk,5)+1;$pj=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$ei=array_fill(0,19,0);$di=inflate_bits($lb,$nk,4)+4;for($p=0;$p<$di;$p++)$ei[$pj[$p]]=inflate_bits($lb,$nk,3);$fi=inflate_table($ei);$mh=[];while(count($mh)<$vh+$rd){$Sm=inflate_symbol($lb,$nk,$fi);if($Sm==16)$mh=array_merge($mh,array_fill(0,inflate_bits($lb,$nk,2)+3,end($mh)));elseif($Sm==17)$mh=array_merge($mh,array_fill(0,inflate_bits($lb,$nk,3)+3,0));elseif($Sm==18)$mh=array_merge($mh,array_fill(0,inflate_bits($lb,$nk,7)+11,0));else$mh[]=$Sm;}$wh=array_slice($mh,0,$vh);$td=array_slice($mh,$vh);}$xh=inflate_table($wh);$vd=inflate_table($td);while(($Sm=inflate_symbol($lb,$nk,$xh))!=256){if($Sm<256)$I
.=chr($Sm);else{$v=$kh[$Sm-257]+inflate_bits($lb,$nk,$lh[$Sm-257]);$ud=inflate_symbol($lb,$nk,$vd);$A=strlen($I)-$qd[$ud]-inflate_bits($lb,$nk,$sd[$ud]);for($p=0;$p<$v;$p++)$I
.=$I[$A+$p];}}}}while(!$Ke);return$I;}function
inflate_bits($lb,&$nk,$Bc){$I=0;for($p=0;$p<$Bc;$p++){$I+=((ord($lb[$nk>>3])>>($nk&7))&1)<<$p;$nk++;}return$I;}function
inflate_table(array$mh){$Q=[];$Sb=0;for($mb=1;$mb<=max($mh);$mb++){foreach($mh
as$Sm=>$v){if($v==$mb){$Q[$mb][$Sb]=$Sm;$Sb++;}}$Sb<<=1;}return$Q;}function
inflate_symbol($lb,&$nk,array$Q){$Sb=0;$mb=0;do{$Sb=($Sb<<1)+inflate_bits($lb,$nk,1);$mb++;}while(!isset($Q[$mb][$Sb]));return$Q[$mb][$Sb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($n){if($n==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$re=pathinfo($n,PATHINFO_EXTENSION);switch($re){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($n){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__aff407a3.svg':$f='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-bb254c95a033f67e3d433a3df63e160d__aff407a3.svg':$f='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__aff407a3.svg':$f='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-4cfd57d31ab991e8071fe34060cd3123__aff407a3.svg':$f='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-a006e401273230fd6be80568c8361b57__aff407a3.svg':$f='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-507228751d2170d047e72142d2c02390__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg':$f='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
R?vPex0x2ynqlE!VMsqy.7^Mtiv[hKzB^oh,VovqjM1XCS0v]mXW-smT}3TK7IVEL2YtHsc^Dne,}uyaN:]l/HJnieEbYSTw;KD$c_8p_B2y&,]pd?W+OvtUWi,FjFuW3Gsr=[=,k5ZhU;]w50sP*<)SM
tcO5=+WoZrY8Iq)IW=_gPo=RG*5hngIJV?j"daOWXS`x~L$e])]A/t{9it,:r%.89Z!;1rZhBw]6K6fQlvHN$Hw,QuiFcFpKmc{y#sO=!8QV,<+O&P/25]6vLiFL^ILo%v=7LZHx2=IpuT_qcxR7puVAY]-[aZk-!Hsk3@pU2?.=/khk7TY+8^U^mMe^&3|d[5+h9;Y
kr~/LPx3%=u>(#a3Hf@EX)<u
hpxoYBBVp`W(PvmMW
B#sK.gGL@Vd{:",35}yAFD8*Arm#eht>.nM#/VX$c0nfYn>@aFR7y~^p#M;>Hr]/"5-YOhURoN?g"zr)rf03v&=U+I-CNf2fyI`@2rCNwy$T>{3b.C"<mw^pUpNV.:1gW1HboUDhY6rSWb#t&3^ZZCWe([&88L?Tb:rJC{:,[0cUZh4Z?E>_4(eVbK+W4cj3K
6JZ,1OCPNi-r:-0+h9c@$6(OPFO,>/K_<D>?aD4|c[qNng
#]abQba^dg.vgT
jO4.nVHH3Y??RBOkYeEql7Z%i$fv:!`8=ol
<6HDyKdV^.GOQE<w848Z0)$;-[WOZ($QN.)/E#@[UhS3g@bs8$w@iRav#q,^!">riV0ad4mzAx-tm;I$7+G<hFV$knOjWB`9D:,!6.B`@~D~lLM@<M0y2w8SF<2z*Q?8suZ!O(%O"i>PX9(r?[=%/{TBK"Y5o,?wUbppvc%SDB9:2sH.!E?uV/?
m,@iTyWH"kU~.Qf,)]TyKwNyoX6LeQ(^HfM@6j
4o+qU-cQZ:uU]TVg=la`BE{x<YgRQys@]DNHkxs-[I/xZDH(tx~I,OKPNZ/@fA]-^.jOn630BkZbx.P^-,m);cooD1IAp.,``B4+,etGxX"U8fa;-m84^sKe*v>@/HAeYMWEKTQ)eqhf~:)bj!p<2bBA{<+-LC46:QPR:9CjzQATX#[YXUysw]
N.c{F{GlQ+bj=,TT-!C{[nb4XXv@IXBg4/"YW.M7"&I]1:iT"%EKDl:j![3j6cJm@H6qxXW2/Z3Cbs2d^_Mps>DM!ccnZ<i*Bk_oLtHcB*IHFOrym<(YWVBvJs)l@)0Z
=r:E0<}*va7n8dz1"9z&IAIi9Vql_/_GmWkv_:7+J@p:0<f]@QLtEi=rp`*wKM:5vfI1|nK.ne&[~?Dw9$GKV(o;/%`Hmip$>""Ue?0@$iQ%0E@-8u^"L:b>FLzv@>2F,<8Oa+M=?1oWnKWe[PvjmLPP1h}>?=m6-g]sv1UozX%`5v(*-1kTxb9=scVhWiuXQq$+!BPCVI)xDF&Cnc4ACZZ;UYX0(]s_GY!vk8WEz/4F"DLf=_6%>e[r;9[xM
*??SKd):Aiccqb{<(e68*v9Xya1
}IiKS_We9OJP11tEgIuGCfq=227bEC06#b8:]191/`0PF4dN3NCRTej;PMj)t1HQ
Jk-U9uH!E]5fjhHQ[+SE@:i^g{tA^Al~K<U3Js9&fM#B=^50#vEFbxFZ5L?Y3#pI^GKK[GYdMVSZS-kM<^><@^4f#(*V&b>jq3*^KjD3*Rj:sZUT"F5[bbKNE?X;A{TeBBBDDh+O^.lXKwEfA")l6+[^TWA?4gsuw|<
F:E?URQb2aF,p$7S90=|txQTehv2K|GQ]/#8t!]{/N<29Gp"TPCb9HnMc}q@$*7z?v`WcA(@>t%Q%t2zFCg.^la~3eLCq_$QqZ>erybXCLsr`Q)1Xvng-<,eXT8Gismd[Kh5k)PClZRUu<<uVag@F,=B#6wrEv$LS(Zs^CXo2:d/o%A%n/ZC1%4vJi9[DJ7|ViE>Q+(A:M5wRMExVg">y+d/OirXu6Z@>[`*:xk1k:,a64QavY*$xgkb=eYrj?%BUFsiBT>VTy`OsXZ8T]!(4(9)TVb`f![p</Q,?n.6x;Vcy,ezD|@X0Xca@ad["tI%:wj?P}^e*sm]oP?U`&OhkEg+TWAAc5FL5H.DImYqS
4fIvQ%7XhX2^!kthV*<ddA1ed`@9m6@mZ7)ocp_a%uQl@q0U??@Nf?_0.+DqepA/LGctQ1X(#=m3EmLVkn?I+7r~foFQN=BUF~8$nF"4
{9LE>C+$c%w8vSvNB?}93S#K4kkm/+t;`RE%e
;(Yq`=YE$3,5|@/mXG|%z7YbGWWmFH+IC;f:U$J6vpyr)hV7nU62e3PYwL~yj.31;lgq)EK$.>bGaY5`n6mlwn3@/u`vQ:9lc4J6.&&ga%^j!0joCA$LU&#g7trww.(CN=l2:G1CfPS:KH=d>Hfpi7[$X`,7wJZoJvRF?YKmSVzNW0`Q0FdOqAIS
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__72e4fe51.css':$f='+UEmPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M)rQq_opI7=E.gy$2_wn3[@yoG6r~P5/:mrvY<e>#2+8qezLLv^&nr;/Kkr(>?R(rf#PZ<Kx
br^LS(>/*E-?WzeLSW_J
;*l
(asND-)j;m4/f-BIQ%S$]jg`lK"7X[Woi<6n<ErGn[ASke
cM6fo
Ky:?d|y4Z`/MKF8_iz@9f#<b1@MaLgh0efOIpYz&+xn<6xNY
d<~>ajCRq4s@jh
caxtV~2DNi9ioWoHqA9#OBh[!x5h*jN+q=`bmxSYd@yVW[J$)|db!4XItVe2/XU=San(wzD4EHl0a(LT*+#/I{HkOQ@+p7OU>7LCKJ)XgXJ0ht%5=cOF]A#h3y>xj
CPbQe)?*P`i3V~D/=qVG-dKwTh&h0H`Bh6D#U{g+4O4p2=9CtsQ/6U+vL<<[BwoX?2A6[c[V]D4-0UY0<f68Rw&}-5Kr^"[Lrv)&Bo_Q>]coooyj>sL9EEvT;B"HxR.k8B
^Q5llt~q7xBV~/n!91bSK$-ui1OQU0Jb$`Vf`/xBJPix,!jg:C6a0@xf^+|r7RpN*I/2:M%^huBD0`%<qSsC;K:6QF=r``duu$_:GGnAJ+yY4!,e.H+17juw;`Qv?UzH/[xK7OTM3Z[qLq^Z^+TawmRd!sSIPOxE!SvhF<|rj:/l,BOJ
mSuF$F"Zd+H*kq9$y!*@F1uY
f-gLsy-15W-N0hLvuJRq9Wpsq]/I!y*0G?:_rlbTt6D;G*GS~^a@HY-C!&62>2?z$:C?FDZ<faV50J@TPaw$ho!P$-okZoO1r^E-sl/oF>NEK8#EKBBKVZ_<mqh4swu)jYp;|+Kvi!"N+01_T-&=mH9s.pB9Qu!OD3m.Qc<m(T*j6SBmlJy+%v{79w|"Bn(V=Rj"ND,Ek(pjZKL^zAr1(P>e8nI&&y=E]uD6uOTPjvG[(AR]Kbr`]M|A(;|wf`C%Khwq200kz[t6)?ZIb&Trvi7%2NO?:O%Ht2"ee:3Fvl!s
VMlfy|MRtX';break;case'jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css':$f=',Gjwm6?!R"-YJmoGR`r@~cEv;#i.*-_KUyr[0$CF,>/n=#+liP*01.(73:+G.C]Ek+^-h&|hnGDq1:ccpxU98SxFh5MU%c+]DCcezAcUOWmDiL$
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-31ccd1ce96536a294822e952872683d1__788c27ad.js':$f='&hk^;xqDE.!v]yOYu=(i
Y)vs/y6g*Flg$jPml~(:A{"*hUQ?DHkp(ew8qFkv9z/bpcXBv}iImX?wn[G_
ekWbA6Z5:wmClJDXdv%Xk,VM[XSj&7tHp4O6
e
IPhUwoG~hnaCQ
#64+yF*h]Px#$6t%S+tPr1?zqmt&/1aIf^tZZXZ=xn$</gG`Z<`bt&UpdKt?BlePMu+lt<5jn>"1oUfa#3RP5:b5YX5vSXT^pFb}FNnEHJE?Yx!OB*yj4}L?
s%jO*MZVn;dqZUQ_gRv^N3Wbk6ZGx,V,CRmu*a}K,dKR0&J:bw.fhMePp,p7eLxPfit/meE*BE}5[B3iGXsk)yjv?I[qcT8%2Sv8:OfR96BHB"|Y8yaU7NP[2xUy57!,FFk(*u^]J;~2U^@mbrS
nRTcuEFrBQWG"4!p5sxm[cWdcNdXfB?+SafBL,_1>OE(]47oF5^6/N>13m
jYqP4J6|f/=j2h%Kt9iFB$4mNxZ!JVt;i8g(q$00gUsw?X=Bj<!fUM0KfU=E:Eh(0S/Rqdnp5]JWD$5xhw9$1ZhgH-m~KwLg[;,au(CHJ8RIkD=PireJw([0CMM.IthuL]X_yn:gG9nSX;$R[xOmC1D(HDOL8ql</<dqx+3Cnl2#t:1Uz)Aj({_V&PKrP2!w07?RCZ>wNY"YJZs2e<,+gO6yZ4aJkwEx[g,<*]JX%/7+D0+VpS)v`^eR(PfXJlvchIdqp9DEq_AU[90HWR=,ok_l(tSP-rn463#X=.ue$`bBI"X{z#qGx/fURd5z4u&Ua%z$"wp-UJGXrEvD%{0&HX/auoy$Y7tyG[x9UEYp=N,!%_
[[1ss?6yC8[Xoq4reQ%9a
D0tpLLOH8<h6&`rWl^+tPvz]VW$;_i|EiB-=b$,EnkZg>]2SK[jKsIGu$?p/aW+^/[?pSV]U|qH1sfkqPL]E-mZ#Pm`>F/-)*?+MhSXu9``mqq=r/M(.Cfbw60qPAv3L]N%Nr2l+Px^2N902&(LZ1[tbP#3!sS&h0UU;7^Q7i?lxQ&i=DGKE@t:nX[j$xGP^>ju=qsO]"sYUc_%8ow*F+$oZyB{.8+lcj%
_v@l5H**`KE|S(3j6n.p#69437DVc@m1O@ulMmmTA;X4]-k97z[`OD=NN[-|YJw5k$$aF}^Mu7-S,rUhYfd~!RaiaJ^7:Mtx<(]!J+Wix*S3M!Vi9zRV*[*bq=
|v`AJjv({D%6~#)#mn.X8={fxkZ2vq~t-96*)u9^g<uP&4DUd7Gp)6KjU0nSO#/bll3b9;QY^l7
)ag8n0kxJX1!xl@VG&C[J4F=|(RR]]fG9=o)c,$?`>6PjJWHgcBugfD7$3I$>%~9iaI;%I@^$>~Z}j4=$D"4su|[7"5VKd@KHieQzTD]TmBg;1*$b
5RQc
]6cxY?76VH2G),&N*xfDFQr>yqH=IsjhQFLrZZ)@F!w1YPtZu*q7Z"9Ri[tXhAw*e.[(SX,Ne`U@LaYs1s396|4V%+m+)FHg1zf%HR#3(bym__D%*A].4.5g7#gg)?v#=49p"<^VZh
/Q$
*^6D$4(x;MI;:]5UA)r:yBvG0PLb@()y{L("L/i-%?>iJfDM?1rY2UJrDTXbF]]Fod=hK:pD7dFB[8*l@)@)U3&P-=Y*Bj2e_AZpca3($VG/[XaeEevv+wih6u.=6w(v&Q4
#))X-9u0L09oMZZyLC;yX-g2J9i4.NER*)~ngc}aC]nW:"_9$bh"(2cyN!-IR/jPxs99ID:/NvKDug3RHu$`!^gS8-COR.cVg.z
:@qy<j=Z+jCN
6pKqBCS7IxX(0]?BU[A}`HkKHf
1A;_D(TGx4&?#OW)H_zTj`rR[ZgLpSQ.eC=J!5fMX^I%|"H+6y)F"g<v/esU*yf)~ux+;HL$DUQFEMo+$gY
oogH7,dp>v&byN9*u(N7WS"#:F[Q9M[
bZw%JrlNCj(%=Y2Fn[z*Zyk$PAua+*DE+iet(()+rs2j6y=EvIlnYqJl1(dIKdCdjV*ppI=jB?o7$i<"b(u1r5^gZ/;GH)$>RfV"inW^k:KQ/?=azOP"+C`@^6%jLk]CVLocuIpSQ7yRV&*t7%57A"wC*_w-=Q<-l9>1^CKIMP5TZ8D<E>`0uHqqk%HUc;d
#qyIOxl#IWw2qo{]4ECHQA6,D)6%3c7!T<>s@Ca=$XDwxO)O}yBS_p~>-aaG]9nTfQ"xiFYtx`WT3blywFj6QH8!@Rrt5aK!iBI@L*&h8yqv6?;,TlglSGytv;"SkPim1p[r@i:AeHp]C%q$wY.P$J,Dxf@v5OT33V,Ycjc^JS,eL!%tyx,!ePwKeP"`9+8k,09+m!x44)jHqg1hCsDp
GF=9_n
<8Mn`XKWS&LW6ag61510ssa3c3=U&DC4-[$>UHoD_C<]mu:L7c]7`XV46+~?4gcQ-g1M0"GRNSy1]rp.iusB"$^7Z-Wc"f;/+DJ
A<*o)(y_E;R$Yd<D,1#SY-h*rdnn}2IbvTWN(!~>V3%[cuMO,+#7L2f&ux}(ps3qynCJRu<+O8_wG5|*wUCg`HIv(HPB/R*xoc[fe52&tsQx#(pC[
{Sn:RbPC5]-:bD@@;SDg"(4h|?`eHA3"D,h($Ord$Rm$|%j]Yd&5X&qQRm/?+:y6?@`"p`qUWnLWVrl_~e<Sl;
Oc,aD{#Ohn>^ArikR0+D?,t[9@&8u"AnC!3U_nVwRH:$WF-DkUPHpm]^fS^U^,%(l38525k)Fo$kcz<DcSa2q$PwAO(sV~j~,J<cD|Kn;>-veb
%P7N0EoYiwL@,Nzc"x,82`c
z$z@3.l>gCN^KFryB;2xI0W
*6M(T$2[zO%dkP#:69RN+mQ0NiW/<k%_krlSDoLp=c/7Zj#:
SM(p@AaY+E`"*F<2J?NRW9PQHF?%t.
xI(Eh9,^(9{HXUx:lYvTc_"qGQU&4LiXH:i7s]7.UW>oTu-)XdTVrtw<8te,ZRgO=%6sfpi,S1gXvH.SU6c`NY0L9&p;86`Nn<s3Xl-lSaS.PT^Q<[3fz:?ek"tdzsD>;#3wZZG=&k|Z]h``
(I^m`:`1qy@]N7a$C5@C/S/:8*KS7EyF>G((jL02k6!HQ-No)XgvO:25&E[:2$EWs,H,M}
"S%RCJB$NR`7tpqZ```3(G0T14EaDB^yH`&c}"~0]R9$j<o.q<sSlkj&@E$btZrjHo%+W?$"z^[Qy:P%p"PASo^
a[Q^NSfPn1mdc9]1-_SRhc4x#
n<AZb6^JC+-nq&2P"GLtMAqIT,W]ytP)E2P:k-IsdM*WpWHf;bM(Bik-PX=6iS8I5&DkSt|5cBT)&&sZC),^f9G6su&>_izKeGP9Uhzcw1!ckY,_j`6@G0kcW;|UiwH;_hYA_k,cJ(h85%a=z*DLe$&V~nSB]Y*@I&#Fjl[Ce@.Q>C#Gk5BP"g4.tA@Q>D%AB,&)KlW5$I:?-TyBKYVy@+5
6=#wrMwZl2L4}i:Kq]uHa+R;g.b]:4mft!aMXkJ%0_{K6nU13TP!CTxY/A-YBT_xsMAW[QG$KU~r}Z(>[2x*%r*w@I4h%,ZYO?Y.>r7o,l"m.[To$>u&J.H@Gf.P`?fd|&pD.d3>|,>S#-0)dW0=t?GFMN
&[LJdz$R_=.pa(Z`1PAbi|.9XM%/e?Vw-KQlU]uC6)j`#Wt]Sh/],re%-JO)a"@<J4ntME@Or%#}S):h#@(l(E*X2P;AdmQT
maoC^0]:TF;vYx_a$C]ZbBplLeQQPA"b:v)3NhlF.5<.ZT(Yzx38brhvl9#`ZQdvmFzykxF,5bcC(tF-B-/n[.~hKA{5~fw.(BEC;Re=wrK3@b*0CPKW!Rd^"VGeH2}2wTEWnd!yZ[L[vxJ;C%mx[Gfq{bDI,=H3=1O
A5yu{(cBmkt6supTw%VlsCp=Mk/[N&<X@Z&wyOJbuA2Lza(6ZiLlqny6FBCp/t#"BJX#UR%qHFNZCU3AZgkiE[WwPP[sP(X1yDw:[:
yOuUaXpv0`5hM#]Q>@5CKyB1tkJYdp>/LB^k(E)z6}XA8}_pNyfAc47
-l9wCL*=Zqz$vY`UBfa:ykg6h/0rSO6)k,(9@20e+KXuyg+FPf)>QJSc@>r$w:Be,KGIrm)Js.J*F=K</[
)?CU=U6mVC_H|n{&D8ac1&$Th9)AIB8ZaCJ9X9a@V(1ZxTH?A;5QDx.svj5l#:rpY)4M`mSxx6q:SKL/8hwW7I8jb#MjRn%*^k`Pc<8ot-1,b9ZBU=>5N(iX:K}0?DE7O%0:E!n]6am,(y~]=f0F0,zW&c{)F1-PCu)dDR_c`_Y^ka1]l39vtS&/xWl>_,k>>h$L6`p+$wGJwT&[!>QDT0$T]JVQH@u;3SkU]*PI~]Y]Jg=hT<7M9;5Z^6H5VG>9cD{0SR(a/>Ef:PV9xq3
P,vHg!./(g9mwKVc^)Cy+Q),fThg0;@FjPX/-`2XJF6[(Tr`W8m*)6iFJI_Iz1A4wPru5-
1lppv*oF-i7K8ITtyjI/Y$.
Cw5`F#=TR-0e&bqbHP5ceVX-kU0c77flN<;&+RF=2$F:&7KD@p>6DsQ@Oy`;>+]yB1a=psG9h|3b9kC500Qi9c@ovhr`
qM&rxDoPw-McxH_YLLU[YB.gVY}cX>@/<9/DA>{>T)xrL$-$8XYVA8)aYU!g=?NL
Ba-"t0[pWz@7T86w`c6zwukIC82,Locz!1a@kt+uM_>_[,TS^O#oc7JG5lVsQf$P?crL`C-hiL5pqh&au3axd4aD?11[(udd1D
"VQF[tnVp0q<gs}]F7Ts`9m`=wM`o
Ke>Mw8)*^L|V!?0Z[uu3TG[*?ZSqlnwa}tz_TG]5=]wB$/h/{jT6MC<D|&or~!iIgP"`A7e<l8Y/&A)>q6ql)7<`K9y
;kK7pUF7J>t(J8mcm[;_|uScMfd?aqvBN0dwz(yQ$o^B%,lTX_DH*T*r"4K,DFZsK[[DVQafgu]Er+V"k&Hy^Z:RK>ZGc/4Fg[a_$<DdCcYe5P<_@<p=MW__IY.N$oc@Wj/c
e0GGV(TKJNBIg=d2]@OhsT5=YGtto;8G$d,>=C43u/.D,UQHl{F@T6gKj+h*^up$`&pe0/c&=?]JPVS>])U^2(nFlFTbD*#nsxGNULsTSv:o_@BNCLrqD^a11YC_tnCHCY.Lee8>u5(yS-$P[fMdlk06B;b9f)Hj)UO;VqQ^:eDP4q6O&eZuQ3NwbJ?c,bOo;9!sa1o_^%lT@yoHe-$LJ8-f,1rdNcPZtZ@piCel`l.R>A0M)4Iub}mh(!#h70e$PRkUPdY[qx8#gDf_iI*NFb+}r4dbWm-g%=#]"#+
%;#QK^nZg+E&5/1#(],|j|KP5biVLZ;44PK%G(7kw=tL,{`ny<`KpSr7+7Qd;3Wr?Y4-9TJTHMA
wGg.&yT+CYk#pTi:CJ:}CPWR%`jD/+YN(M.;:jt$9B"*Eh#ZN$w~eu=sS?4+G73SIzI+BK>wD>/li.WPm{S:L^21R*p*)>=*W_W.M3A,)is/[[vI:_A9,luW?LA4Ye";Zhy1bQ6;^$.@X-L:[?L%YVo=z%GSl}E;0gUKBA</(I?08N?b$ua4`vX;8Zw>MYG|vLSz<wtEKt1l&"[HH+[Wrp3xFqBW^TYecWBaNi!(l*x#18*nv$<v#mDMna!.n+:?s&Ol"G9:oL>Ki*;p/XMZgVbLM0u9>H!/589@jiu-ax8AIYhmW.s4?X4O@0bU7PA]m)#bDu@KQf]TD624tIB3B@s;gzc6d3d|drQ6#|sWd`xwHpM2^5:
;WxF5WK"4s:Zij;`[cfcL]B9mo3]`~B!86_pOr;@u6KxsnI$ArSS5*FK``<&qDb]$$lg&mjJ-6:ZE~,PdC]Umv$O-%atr_i?
Fp?F[M:"y,wW!q4*6L1ghR};
O#QkLJ@FL87_
g3Il3mygav%PZCi
dKfqM%*Xva.X
9dteOo</"wrLsU_O(?l?$a^w!i4ga_c{y2LGBW#m?*Ep@mDYHixG93$2*n[:fnYf5C)Ay#J^E;3:SFfW*wS4Ir0xsrMb^J$)!HP6UlCNA~R0$V!i"GZJXwx)W23gs5.3=9@;<^8kwz[<[XMa4i<3Oj&]4GBS7ok(Jqhq:6_a]L%j2[RE7
!w*P-o)C$I<8u[3q1EULA>:g:iQ-?aQchJy^7D%^hEI"j{hHOf!@KNtmOe_#%:9xDSg$KfKjs7!2C707@hj]s>h"r6G?6
mW-=]=F?._BkX$HSfuBU?d
nVyQh?zqCnVptp}@:uKheh*@u.>Uz!b@Ag%Vs0GWZ!.^^;mSG.oE0`GtQ=+c>,:U0;x9>1oB/F2mnqIh,Cb-Y$IC>-|eup8R
f&9HRD]r!/(}pS^?FB;^KO-TuB;XeuucA0tmss_X?BjHL~$W%_(9_D8$H|n%dK#rK{^AYJBk)dAdZ&A/j$Hq?(Ib1#2|>%;b!w?)04+"jLl++}7U3uiT;o@&Usc(U3EigS)^RGCi@n39%,,j??8wM->PT=%LU9pg[b").SR|p~#3M"!04}:hPT!5eYM<?$h=@6"@;;FP6>89_FHa=n6W.[^@F@@5w?3:p?BzxCBwuJruGzI>4wEL&g0Py`S9rf#(FErNEAm?"2luqnxhF39RR/M*[LXLZ.I0yd:,^A>n%qH-FK[,tAoHQqr$7I7glTQ:?libdlNFOq("9>Rm%[C2ErU+G@/OZv7[<ItU!64q+=#N"B(!Ika)duYa_/vN-^e.?Hg/v15)EChpp)_6Ez]
aq!Kt:)rd-7xCab]*nRQN+o}ffHi`JU4>83Iy1t]ISEm":"GBW"qY$E9UWW@;egYXftu&zh}]W4i`~Xi]u>0YhSTL<?B>H/2$!_TQ]ZNoffY[(iwz&wt
A`52#uV[d)A
%kZB^yF!MK*==m8hZdbl2XMW"-%LcG#Y>R$Y%oQ+a9sxxjiY*S960U|]1sviyeU-+V/Bab.*!h$u/oD4Z9%/I*h.3`uiPc
&us3]@v(8=E:<#y!Q*hzB
csGfL[j:w"MEIBerdOGD9He@QanPMMkzrdlTT.ghKryxNt`CrkU^Iw135&#[wgN
$gDt^T[[;ZF8#0R!P/4ic{uc>zl?!jDLWCFYlHih49C#w@@gfM[)>:Oe,:"KaGEk9dTb*pMxi/3@ORwdbsBF6(;wla$M]#_?cY`jOTFM@Z<BG[LF=|4
`^rL00B}ZP5}W4;f_aW`yuyxcs2X5D%Tn7EB/fn`>,uPneLO>9
aFgU:xr9D(6?*.nlNANR4IZ4%NGjxN<JYjYJ.ZuUT+(UKR;(El;DQLG_ZqXgCIbctecll^-w)$#>7&MX#r8YX8&AV8]kQAYp-y=h<k[aWB"dvCAjzQ"L<,/1|.>I7_nJV@@&fj
-DRwk8rrVb%`4Y)z_+8<kCYK]30;m7&8U`1+hEQI#XA~s^*5@<mRuv*ha!O,M?68+
/[SL@JMC3}^T+jd1c]ntV/$98eYUqiFe-zGa]us3,|Z)_^=i2_wa_=B#3DBUCB,VK}FCF|*g)m
oUBg>U0u(DKFN>`6lcb$xaEul"0*%oJBd[nk?g#RqB}hXN"VR=BBpV,]ML^A9&PtKgtv2cITb
V>,6m]vKZ+[H5^<TacOh0hb]vSIePNZKMB+0K>]8wm09E!
vh`.uDH7W[kkAy(l?b*Xoj5oWCdQhy8xPtiS7{Ay<4t$gjBB#3q(cL7+`&Rp>0w1xVaFYA,Ivfbq5(1."o%Sb+;UX"b#ODCb::<l2zjU.WP)_Sa:@+mU7XPaG(r{K&>aBfU}aiHzVX(>ZnShswID(#:cqQPcwr((eS$|QT6|D!?mJV]:QZp~<{ecY{.lbiYkcg8GA(,TP?yI,u_Zk}aA5IQax-aRG~NuJVm20A!tNV:=)A
$A*:?$cmc9AR{ePVt:L!@>XY2uct2YY2#iJ>q+JyO7j2wJc&6uDl&4xWWE*DbPI<;>4@&K?PXK)Zx5n
gY$#{"n2wl)c*Cz!N.9Alj25Uk$D{"
:|rr#trzFlcylyu~ab$yO@ibGhyT$
$x_eYM#W(mg~po)`;9`44z-ACo-ko];#yR&<lys9(+!A*KpJCa-qq0aa!F;3jKq9&2lnmg:>t%*fm.9{"R_ZOA"FtGaKPOwr!=$Y$86NtSdwI0W!.abYt{U~bZfRS1w>o?:QwPINtS^]vD0,c1-<:Nd,eW4Be&6hY293+c3b/|Ou!N<~08)Qt}0.xh3i/@gYaM_YbY5eF$q6oulyX9v:uW"1z(Vd2k-"27U:7WAKHHu)fJI@D#5MG1OIXzIu@X_YA<R=@lMxo1+a32OGiX0wHktW$M%~LSl%[_9&2[X<_nJ.0kI%E$x6kX4&%&x2u,/Xo3EA&Qgf[B-q^XLwiCj!GND*8q&"2O5P_v"Xa%0UQ8VXXq66C5!2.S`Xd1nTsNAu5zF+.q+^#5YH$[B<Q<!fg?7$!aoP+%0Y<lMVZ4!3
PDr:E,j,&(Zv|(`EIw.WiD>T4KX`]e|B/#$ld/(UDSx>j3xJATMb2*iZb@ICt7!kZ@CW
>KFxKT.+lGverf,la-/=EBAgCju)b~<b&FB6RHC*?F?0u$"K?#$6aSA6GYC1=!
~W[GBp*O)gVHM$AQ7K]8*.0</xRH(ha+]TbB-SAK|T^@1%K#HtRxz4Tym;xDkLX!=]Dkw
}TEaehp%l-@
mp#Lo:02$(g
o]/y2S![WQ@Y_4d8F?CckKMh[)E)p#`8SUk=E!
YL,(:*HNjjxF731Xpcu]b,hMD`!)o$N/X8]J8x3XTAj,+_a{]<y@x
UcZ71z)YfXQOPw0TJj?#W>j>+dF,/|!aypF,Mewsb_jL-x*Gmd=6K!coC;_?>G
Y*c.aO"_BhvP*6Gn5QiplFTJ<]I8_RD6@d&uynsVC<M8v3O+PtE=lu"Pb"zPqAJIWm9JKZ<i0r6QJDc._p}4kB`.gk6I7oA@$FkKiTEQzFBrAiK[G.)_{8|K.j>aB.^:+i~p>$Rru;SdM]*E5Q@Ok_jI7R%U5XzZ$[$h*Ti9]0
*^
O</H#![1N+s;N]Oq$hhE}>DFCIN;b?`ciO[>Oh9B<h
L]${!1=AW|2A2p@;t[,];tq(ArZF,QZpkF3Ffxr^jiJ`0Xf
A4qZ.OK`*m@i_"fKbn+x:HZW0FowL$-jmywG3kc$)AP9C}AAQ,
)"tCelXN<
nOnbm0#Oy>i$XW%J}lr
2UP3T2}KHU%$4/)K^`h?-wzq}[^D750;~8FQ)t>GVi#s{Vq(g[w6|ICdp@m!BJ2d:N~`0B:8jBs/zY;la3P!U
P]TlV$NQrDsOcs/J=eaJ-?pnnZZoz*tiD225Mdq"k>CnncTir.<`]Lvk6=UZ*XfW!N]e7rmnrRmS==))]-ik%Jj@l,7yd&$+$Rmpoh<v"txg&2g$B]^>+tV]`3RDkF>aKUA;^^t!O0b5<Q").R_8t5<Zb<;v$Po[YK3!{Q/UG3eq(L?05%m#iY"?}Xi&YX
H<g3Qq)S.8<f,Tn1?VWwHOwMcT,O.a/PAihI=`+Ss[x:a`3f9qlvM6W-e^j|uB.J9~kHGv"fE]r#/$M001KYtm)<VUQ6s&cIbh!<a
!PcsM(SVm&=+C"^>S-vJOOB]OY9z9^ojMfF)`7(exr(m>0IlcmHKB@xSB{PM[f[cwj7!X.PZk}J5AQC2XObT#=>`f#A7]$bH
`Wi7y[`ZoU%G/8)YoL#:,/SUsW!c;64hg@0a+X*7<C3@f1C>R
qt&k=jADGj>s2%flSf)W~xoChH6?.IGMqInkB@~(VL^=Ao/Gf6W0``4:-`f@DU)IXZ1@XG5PbTQ1*ezJn(aE%qyG<Itb;>hsgGxBo]i?!_nW8_@ur$AEh"#EfW!mG<,q<C8i<0:j;8?CzEJa?4[_wsmA8M=sEu"19){?qOtxoC1p6SaLec]d.5?G1s9oQ+F>)d=
q;~l3v2d,D8iMAIjXIayaTpK@!2Yj7aZM;#_w4$b>,0Xf:zOo=jw.4QA]yQ8lyBk$WlPE!8ve_LEe2pqw/-"d"N9hP&[^PU?#kE!FCn8BpG,V)l^"I;r$M42g[D!Cm5O@>NNP
:-$-;6A`CjE(8?/C4YP"FYc)<TL#U@86ngAu0"_v=*!MH#;B}n
E:U0"=YCPKGs)2t02A&4kE=6m_Oz/Fz$q[$*dalHw)?JYdj8C7O]vgusqwNJou1qJ/e02_2>jXlIKF"KPBxsNv]_&1D
nHJEOxf0yXVnF_dpB%7`?:DH8.<4.XEfyli.:(
x>y_ymI0f$HWE"]>XN*`+Vh9IkKdK-H(V/|BD]1#N86RWce9K%3>Qt
dw8Sr7Q.[F()1Z)a;8!oy[4
*aTJu6!BSA7"r:aXA0:g59%?h_]K[zMpk%YYTZV
*0,eL0&!BY*hIykf!o0h]&nV3^%us[>D7XguSR)u"t)Nx.AQ""GmA/gYr9D)1|XAu$SDiY8d)h,7<*eYyj
lyxvvl[F{?XQNpBPBt88=FtqyYJKhSr7Bn{ElAW+-vP:NR#,AC_mh&!+|gy[=/LG+T800(~NDa>LT7htMATFbw6qroJLJnCitmV$@6if^skHK;B^vM=vKk|9~->$Qayv@jWwA+lw=7{qlynqLd}yu*.L;hjM2kfDJGE^Ca8UL6V]4]&x:vg:6/xLxGPPi$3XgEgLLMZd#Rxx,lW/hq>$C^8OT-+$E162}9[x9m]1$qEpFn3`tbS[
gWd"u&3aSi$CvPg8Sx7!e@>Yg"*3DO+WY][c9E84Sv^OVDvb_z@8?0#0([
GB.jV_bj!uiqg-|DfY_@:
DP%tTCa.baoV[G~NXw>x@S{NbJ2FfM&CxHL>Wa/F{VDs~;.RV#`X^=29|CvT8oW&;vPT/?<!FSm3w9sS
Hgvq)PMQC1kM0X_c4V^Y6=E~?$Qroyl8tND{r$67[)JK&;^_J=98H%d`pOvK1(o>b]`B!W:nZtj4_FpE:g^juno~TelpW!)xX)c8+S=%ncRltOhaxxCC2)70
ly^"*FSdgb7H+
cW7s/Y]Ws1!lgrTV;.4Q0y_Vy+1-doOZuI$Q
5*%pq;Ju$8bPp
(,@2>d5tEr%C,OqSU,:#B/(I4k1:cy26Z-"w=d2,n`T%jMC+
!ZHZr8OG5Lr,[.Otomi5
#zU
HhJVt?(?@65dEi[G2];2Pj@~mS&<#0HQJPL*(1t~CIyF]n?IUZRx,WVDO9)%>V&*+-@h]dbGnELY&]0!BO`y
NR-K*m^`y4_,WX<b}l!GXaZ1[1x
d<Yy3q?y)bG(OclY9gpSyXOp4KaHo]ay>Mx!*0v,;Y2#(/Fni[Dl5kH]^BQS4vCf;@7Bf$:r[k
`j+"]NsKxGe~M.<
V[ps.RkP,H]pF(+rsGX;v$-~h&nJ$]
YVnVM$u*.7]3W1vB9=PGGAnmf@)Uj1[Dl;;A@]1bv^jnQ/BXUc#P3IUkL>+G:dfy^<_B64BGI1"y:
YKw&
i.>@B[v%7"7d=:Odb-G_ji,hBjC>;/NZ=}Mdm%Wh3[s_S4h3_4dc&^;n
GJ(GxEQG543cNNdjvbNT[tGDQxA8sn5
Yc3^n/=18nw3:J?w)4Qe:Ma8l";Jy[DP,g+p6JQU[T+v-,%6oWLQ?NcacTP[qVsTH07&4!v&9/FJpDcN9kj3B0k#vsb
-9)r>H
f&B:i?Z>m0h2L;BAYMd_d&]}xXYU
`LYJg>-n^=eEBg@5Scq73O5f<UzL&z$V=lEc*c9JnM%32bHKrSgiEns`c:av2ls;FB~YdB]]1`W4I]ItMIeb-uG$fr4YrcN4WfLvs5
h,+yB<3OaWA0IqVwx[!X)gJ9iiQ3r,_jZopnANhgS@I"aOu0l3%:tQtllN6^rvfxr"13QT<aMnlP^.XcJ!<nHJ[+r6lEh[_?t$!x8%g]hV`?wiGwCM$W^zlwFKQy.n%jw>]mboe:.WRn45Rps;`[=u*#AlE&uy5|g-hu&O;|762/kHj01@vR@-+0BoRL,_uYF,^XFRd/N>%{WM>D#?0%FZvbhubhO8yXL:Jp2SxZNhoSsbCEltnP.A`/M2Rpb7"v+"xa.{[R_TB-(F88Xna}GKqXkR]ab="
`83QJqE,);DNey29U{*MgGQMTeEHcKGnK<:%wbT=m?hlD.WVR"#:uA1HCHV!?n_*u"2%0!y]jwovD5I;4(Hr39;eOqwmL/tS3Sv9:T?#,5>JA*tdElH#C,X|$6O1F$SU<6nT:cd?(yZD<TnSh%L+.L>k2]q~Y5YxIlWG8y@"YaB6)AJw+-L(ebPH1pC{io%d,w#/ADD@0IFI#6T@bp!t^@UC97W]woN2c?E{Fo9(-ZjT$vM0jyBs;*cUBO*(!RM)$1Y2w~r9v4[0PL?.&#[Ll=VU9|"M$gv@h:[f`72k44lgj1,tV1Bwet,o_*#+o,J%8
Zk^,9`f%NdPeLJ+3YsUnSg
dU|Arw;;/[B5bv
KWO^*q2I,8%
(]r?+~<<>Zio
{9>L^+Elz]A3QovBktle#$k2s8:h.`@Q3VusT>^d
-2"M1go]L0Ue-}+,3.Y%6[cBpI@-w}F$=Y#WSQ4wb!c![9]^-Mrd:]TC3BWl_rLX>+"3(1T;PubnBzD*psCL;z+]Jl4Pkb68<bw6n|fPFpN5=Zdca|WGd:w|oqI7v]Wv<m*-,_D
-pmvV)D}i@e{ryv0<A
-<VhL<=jHI8+Ox!4C[6xeh0A!^YNuB*Vzp^+zWHD8r[^Y]2)oRml8k.uav:>1Tvr+S][d*0v{KI$`%n&N=OC,J|#K[Q8:vI-`05j<IMj%exj<^"L@]SUDlY(b^F!F(T
hDk9)5`V
udcCyimo:`6aHj%_EK/Z#Obg1G5`wo7<N{^W<$/)!x9$0;Tc4,P,gPP;_97ig3Pt:/%:5lcNCw[{SVmTeU:ef/e~Hm6FS8QQNoT+h|^zYH,!><;rj}+p2[p(2IB:Id">,Sw}$r8_/b*b3FBy.>_xM:kRia]c6Xx(/>BzHybC@5iY8d(]TZWj-@bSB%Zc&BR{>(J&,a,8<L>?fU5lhVHhyD/9o|<>RrJ</*rD%DHlv^Th3y,lcxrvIa"AOz22uk(c!qB+dXyMa53XU-ae=y$]^{$hhr&QN8&ugqxBMf:qk1,cg?#cVyjdMe%:v:2:*I[l!-WGTl?;KV5vGfOf_R&:z%5@au#:vhQc/1;cX|F:ZK=`e
Q]9>YNM[P|<v7rRBE1^z+U]S<;I)+{^}I$PrhKT:As[/S
V/e?,6!M<X);7y`}]ZMFQI%@0wP[N{32S2mTf|E<m/*~SXc)<suzB:2cChwt+ohN0h]G@bOkGc%8:vYZ,#49tr2&f>#C!]8M(,;WRR:x9G:z?+[URd4))>g$4L"!8G[3>bMu)DM$q{1?"G+U,Ek7!MH5cF>)l.p6&>8FFYdoVUO?$cjz`}+7?J_:pmoYEiz#jy#I%Cpn(S,dKwFw(/$kj_,txhe*hY#:onRM8D.mSmP)6j==PxBr!GSjxFx).66M.3V7_K@]xy130E-VCj*Z+gt_xZ9at_3u3xKPpv`G4{#sNVqvQC!HDdkoa2YY+I_YQ#;C&XraW4Iz.mRLsgeaa1WUIF@9V~#M/@q@FEg+WN8eQh2-?2cNETY~XA[{gQ^vv"CEqbSV2WM^D&Z?Nd;N+bc_[Z(Mb2_q[9e_hS1)xu.2DZH3kPD%eLb5jt;YkpStsSP%O71RkTg3kHWvmqm,Z4ZJ$CGhn9=*Nq6]lM3jl{i?a6;Cb)_,Ra-5;wVCiy)H<)Suki]G]9E+rG-Mi*pq8|(VSfjeVwPIf<L37elTLy<x;9Gu;PG_e)MTG{_0?a0CWIJhe[`1(~_^"[S$pc!a_Q`!p]n;t2J*+pH=23QC
sn?Gy&VY`<^xptM:`+P]omlX%qDABkzE?2_?RoZYdpz9$Sr#q=yvVCn-y`~1#!f>L<%=h
B^e^>,"kv5l
]NQ!N^G<y&J[0;Py=m2sW[SNDuXext}seR)D5%oX!*nWXsl>cBU3X#p`Qvt=EUI[n
zdN:Vood8/oygV&r[b6cx[LRwp|pcv[
$T{5"^Rr7E<@gF%W=)f+uw?4sUxN0s/^D@Jt/
R7W7GBd2+y0c:g}gM4fsZ,ce9kr@bf(>1W7s?o4fo>ZWI)m$cPOv<Vqx:.c]_V^_o+lJ-g)=-FP40h4gKcab"
.o*Qb9YPFC7T8dkOUS;mVlwa`b4erDE
"<Qja]6PI4JK>[V..OZf
*wa!o$>m/mIn+Zs9[KA"6.8vUFBE>9@y6KG&p]
x]HNNu=*C`@gO>GVVO!A2Yc9D[{idaIrwt=Xz$;&_,<jC?@k-&PCPFxH=Cp"6.B@wH}0.oc-G.;5eS43I8Mt#_&F=r[knTy-0BxC_F|6".T)&,<FL/W,?hS3xdk/-3)nY2[&lddqSW817f!H2e5B(<J<Kn&[DU*.rP^Ur,s?V9}vs[9&:ZAc8/=Qz3B2o.+:^j_@_;:x.(;2M:mZo7C<sc#g3rm;
jO&yY3.{eav.tg7Ua{>j<zl(@NQM5!D5:VFCc43`_1j_t$V_pDJji0!rDM%8(}-1L"kMB%E$CK`-kjL)h**ItJsAf33kc}_!q=9]r?iW%vG]Z$R>#z#;$TUoW%*CL9QwKcR#pnPWCkb`QTmHw,W<u_tg8t8ST?p9#!]XV5`w=G8;Tl^!j[f?ST
q
gO$"AQ-oRo]M}W&Wdlk>n@5sY29a#[O$1=3>5APfXvi5i!H/Gb
,=nW;z`e&@B.YG-UV2Z$hX9#*GWByMZW={W7M[&ih{qx^BKZPsWTMU6@i*rmeI9BC4l4:~M]OTqyEXJL7-^QEecr^O/DOW79,n/&EZAMJ1$B4K4Q.`Tkqc.k9C;g8C3ol/yaLHSaM.L
28R*U5<:jIR]0Mz!iJdx:7]iE$/CC";hBm6)f,"AQIFbeq%:tFGzgt$W*Gc9"J9EV-lA"3.PcMo5C8-WyHJ7"4q,XDo&U[l*0d<2u>R,`[i
-jQ{F.;|M`e^Mq!(cL&ZtkkE"LM!Zh1Jb(
-[r)"!?=JXUAsk`x_l`02_l:}JRQmp3T
#~70oK/;[+V2PnJSsH]{".=Se"N`0y/.ECcK0b^~f@Hqw|yLNn
1/aZnT#N5r>pqd-Ot]&&nFW<0KL8P#XyGx39%/Go;Yz"07P6VWhXT+0)7Kq1W5c
&!RHlU"*$7{:>SLV$]&)|VHCO%@SRQNK_RM2Z%oX#??uvj*eQ)j#ygWw;;~Z0+$T{t=$PM-(v?5l>B9#FrA9[OjfB-eSm11C:8Zr&yrU1/Z+g>VX~Z50-R/#QQYD
C%$KdZ3Y1a,G5EEk!1SQ2X9M-],1XnD+P{htPd0|JAxt)[nLc&Y#p75GWy&4$H8Mwg
Vj#,^qL8^?2l{Q|<isjkNW6p]vf?pb6yIwHX^G_u^Yb95u5XRV,c6FJxE(_51`*rnBhf),3g<*pDkLg]RFQGJh[fT>S+RQ44Knb*k2)O}jwnaOsRx&SI"i68e`6g-pqMHC!B#!FjiA1uq/w+
$?JOQ"Q%rY$(Y7BMlS
{bFE%?WqoFJe&FToJq?J9-^L2-!xa;GiW0R7g!ObCZ#Q)rFrQ.)B/b![]1?f{[xy&`R#rYyNAh!hm^"x8m*[h&o-CeHvcK2`{1KLghXyZI%9>Tb<Cd:kog^s&KhGk"vGJ:&L=CcDoa?v0xX!Z!Wax&-QlpI[U,r6a9`vd5TdLf7""i{%LG#E4:jRQ6`:GHJ/&s]?("%07o|dWxPo
sluTqmoG4sCcP5CaYd!]a1;)`)oe0rShV_E}a,GnZxF"C5Yh^0nJp,=C&|i|tjUQdD4d`=n{jn_-g_,
-`Vt7t"
MnwOSSKz[Ib^nM*!]^0)P5M,u-/)0Vfi]Q44$J9nT%i=:?]-tLVb[%oNwrIe7i575`J&VLJ-Q[rFC`QXGq=_ivf$DT5TMQHt+<fI
&){!,=,E~1`TIlZN:>rR(a<fR3f9L5;%UZW37>zQpPvJH-5&^bW".lA/#/3p@FZwwf~d*+VRzKxqsboM+2acQfA:`1CU4Pker;|t>N69Q_!CvQh%<,,ko^@&-!8kvCD];^jq^``e+/75hXKLP@:")yBwYy.J)!O&k4u`#T{uRQHD9)wj^si>pg|"S]W%~-g^?Cco1s0O.E`tMgFAV:rMD]
bK-T%%By@|e=&_N01>0_q0ahI~A/U^a[q9+z
5905P"0aH/{e|BS!af_u@#>H@@,Gujb+.1A,+W&d+"+CIwu^w`-f.BC
qxYk^lVOB@h@Ly]=jG_Lo0q)`v-Cav-`QRf*N9C.r4!<QvWCC_:H,a<"S98,BIuYle,WqLuwLNXq478Jp&|)yG1bcmBX+VbuT?wF#"5N@U}>[D<K77!5z[v!:yp":j]qb,D(V$2://29dbzXLM%D+!.vfp0)/Pb;/dhH%oji"6~33X}"jYjM_"R,]7i=RfH>)p78#:v_:[8<&3<63o?S+TkNR,O)_Uq(hZvtbT=j@5{urO.i0gV.<-D0P.8ZI+{On#(X%gWP;f?6hIi%GyHv6P,2Z-c5~NBs|>Q<CcsO>
F4y*n$AV|jNsP@iQWOhy2-A^KA+up7T$c=yhxS"6<lSBp-&4Xfe)GvCEr@`=q6z+UWu8dl"]sn6N._t3inf.6x&tKjPsegu>|p[CsokO7dKw[d|cGKb,O,zDG2Tv>
3R@a_VTMg"R%ie@L"*`ipYsC(<%%OEb%JuWn~`~@ytd=sM{,j$Ejrd+VBc)<B*vw2ZSwCRBtBRQY
NDYTv4.jps2Q0U>_yBEvJdGAgS/TDCjb6U(:nz>;qO2Ie,8AwP74EiN|CnVU*]%i0M#6P~.M5<hf[qG.m_4vLiFD+I"_:`$YCAd+
yk/je.
%zt{K_E-a-i4DIU_,3d6o`ee-!VA:m+bAfB$OJ.$bTFF<?v13o"+%_@>5!Q6[C0%]L=#E9=OMMVAfz^GK
a=5]a|nrCiS%W*WWx-K,=xlrBm59[;A!OO6V-EU}V2KyvU!C^{GaFAx,T#]~yb3E*`YcV[XhjW[4r=D43`po<]CoMU[EI]bxCc+f!.mV[$xiGDr.qH^[pJ_MaHac0X(c0X^U01Clo^l|ia8tq>B2bmt+W4%]qD&u3W,]G*tM5h=Ba?Qy51A
3]W5G]<jh7:
G?[1&k&Zw?!fsDt8S|^Bf_7.p[CE
3!I.(a_/QK(9;"~TT`+A*VDGxIm.8r-e8!<aEx<m&-ePNgZO)#Yd.jDg+%aTcQSV7:f`laKju(2u_$9EhPa_"bV"MpOTYdDI!(v"!OW4axNY9/.8vEN#`_*`g%;>smpY6m[.Ih@XbC@>mQI1BaU@vOkTj?IgydpWoqT=Zn%[vJJp%]&TjEk<zUlDD-aI4uaCXM:V:WEiCbE1a2aqBHt?ic;N/0z">"[p7EVe6.>Q%Itl7.(${VE5]c}+-cXfzie"`Zfv0c!SW7E
u1Oi.C81`QJHwrW+^oa-GLgBYcwp%^`C*.8TxQW/}!)8r7qU+hSkkJZr}gQ51*m>eroE
`KEYKmW+IISzQ%6]hB1Gx0gcx&kSO^!}4XSO/{[vC
L4mcV4OZl+7~/g_Gt^9xh7R,-:F&ej;M-{3=?.pYH_"=-qy9=,[VVJI#")X^8IFl`[7Goqr
v=M]y^.Iox0[nx<D`J9Ukh&3HEpT[+GD*^i~O
KEoku4]Ic)pi*@Jxnl=|GaV6g12S>3tU1C2l3_SvYB2sW]-tfs@s[KdyTG9]+&MqD2<{r-xDbChaP]Wm.ZqI&:7x*g*z6(c!L)?3tn1s_$Uoe_3p)#@Hfg1S5igVjA&y]qh!N_U+
g)^aql^??Ktv{NkHPx/Hh[3-_V;Lf4+[P:<#Op^gDw6RSZ^pOiOkIRi<qGg(|Ic%0w0PbF_t*liKaU8D8B$v,]Is!?c";9b>;>eW[<$Bel{Ud#7Z_(eT+fHpzJYcCJu?OGS93jo/9UHGP7<G>lth4Upi5<:J3rfuve@vX7Z4h>zmk/f7uM{9"P@>"T$R`&erh-K)-5i$h*Gt^?0!>mPPqifGQ0/FnZ7"?^Xa[v$=#
+!%
1WlM2N#^Vj/E~wW^3ex?#$Q,CTw=omw$Or{"1LjT>N(f^xF7<b{y%Uo&5pm0m0"<T-^F9SoL+dR?0PAa7!MU{mrGYke:t.-$5OMHzCj
vwUd5;2wGn$Q;V,Ns0J,?#GSqk"p(*$78#Fc35qcI"wDzPHHU1;"v/>.9ZiLe+".(8IqUQKwV7UTCi("2S0rxd,$KXKSG*Wio9jCV.4-YWu9##5VXxTqGG@yKQ{^Lq
Vf(IvMdb
^Cw#e
3%1)O
1peC5b;a:#3+WXV5kw@m2g|[0u!F)?,Z(KjRrfjLCD?lP;`gh$>]33Moc:9eS2-%`Cc9Z$p

pnd4@*l7V4m8>+GUem8%y%%hG1G3Bre*LS
|CSTC$P]1leCp&:Jw;I81"<U/N`n:JTPP@J"
4If#f{Z,cF<+KR7EI@;t2vP+]Wpd035.[lv^Ngg+/Cf~[9$8u]:Q)|_gd_oMn3G|l=*g0:Dzl^Lpv>F@WM:=%plThHjC?,$4RTx%d86$9IJqZ.-Am0s|0gs13kcOHdsD
C,PKo"I^dnU,55Yrqa:LU!l`q:pnE5),+w6&5I0&]<>$;nd17lT59aY1&lT2?e5rc$U?4v$$_)@QA[%5)r0_=-:T.!$kU;f-/rEMU71m!Gl@A5>HEZilB^4IrBNx|5|,RMilY?-ub-Gwee,MCq@vb97HU
4HN]xTV1$1j9>8i>TWEX9Wq6_TJK,3V1Q0#*LE$y_bx6JjNrM-"T*Y$2AdS*/h-7.B9B|D,Ts1w#(7<%{9i4X_2i6>)PYn<r{iZk#u,E4,`S}:Fxq<*y^M26D</jV>=S;^Q9c?"7zMf;A&k;?amO"!k3ko([ZWYe4=DF9@_aTx)`=HCaj,*dz*W4.V]!6h,
5g1aIZE3iBGFvP<LJ>XO0pguC*^k-C#c8A06mq>Q8kwj><`79Y5ZUK3TXq"H$M_+x><$RLAJ+nPNKcB&=r&ai!k^O@[M/wW
&AEt<ukZsiA<#?Bn?#^a(b`2b2VD:X,4?-ca_-[L,TD^i,ky
YE^hh
.zi!G/O[>=rUvvrrFN^TKk#RWZuk
J.7(g@L@Qy+Q=Zfm?l2HNow/X9MAX?)f6S{y7y7Yr^]"N,(K
Cp@>)oB0-9O8[Tlw+Sk7Gsn4R0__2"UJx=9&rA^8PKD}[~mZq>_0R0=mERTRJExBRZ9m89^pNMis5tJ<CG+XL_G#aZif-$P/=`30y2vT%i0zNzK;5(L`qZezHRA`S
gwFC$P3gBwV
`90
^kytx<P1]H>9ZIlpFZ.0N=pVI-csJ6[("ZVWXL1lC<)cFJ,npUij4ZYj`dj7&FF-KV"5oxh{wr&87</{$J9}o{ZEmrAZou3*5g"%D+%^L79M"!FKnau.caC~s8j78.OqyUQCY0if9.pR"([m/~xx+4?UIWs{DE+7(k$]+m/-+9)X,?iEs/IFePLU3*1.QI/QyW36n*/HPp^k>_"hFFY,cVdXE7/f@SsNSvk_(}`{b*&*bv4pb5sN];*H*aojY"#8y{hbJe*M5E:Aemo&Q{:T;#U1O2CMgu>U=W4+w?3qO=u7
T1(H8`O>miLkJEwbo"it1?zmOAhwd/nn#[nlNHqgN_&p/`Zp
r#p^HYB~.UClxqc_NQ^Tk^t-vOs:g/dqdx^5py>~KPV1W11X:&cNA*Bbe)o]$W)MH/!Q=&uFX2!4;xZbiTpk8I_usVLI75<O)f38*TlYyy.1P]wx?k=N(?iE*5&T`u^ong_gxvM7<Hw09i2"vsJ2owTA#&<j6=oR9eOh({5T+kXF@P%L0kaaPF-/FLDVbSH0voAqDfP~t;Z$O-?Uexq2W1`.FNb2z%-0E3j|k5n.(53Uc.=wZ>p,
(.eA|k8e2
Mb1VQ&Q?y"[V>l*8X@$"V3!`?(f-h*rF/18&ygg4Tsg
:S`I)IFrHuCmvkfHg/6(V!D"4-B<(?CfJ%
:PM8&!;IQAuw^(%E9:hEU?&23iMRCu+rVW!G/iiZaK@m4k$%dgXe;c_2I`m^
5Ag"?e-pLxt%0t[&cWf(B:j/D2{07Q-E,(Z/{:N$8r#eBb781"RYqKQs%NG&PX"+z4XvZD$(eue2g%v,TTH618n:Xp`75xR[-_7;nIk!?"v>8P-L1I>3:NUPlZbEN89H|Mj=$#A4yCejp])1&EWs
-
$dpb;ag$rF-WJR_;GI2vx(qcVGN&m?GVl`"km#1|%cx
M[:J/KSIfuZ2Rui9e1m3@-H19HqKiI2x
Z;%p_mY31sIe9*nv@7HYR"F3e<t4(OkAPv5""QXitR|f8w@<iYQ"`+)#Lt=Ze3AlF_|-2/69:j$EnVTGU9wfZ#d_}BU=z[?C{0utm.@sT$tW&$E6.yFq7X1$+QinLquE{)xhAn5Kzqhf#%C%H&>c/ksa@9Bl@&Ri?^n"H3X_Wlc?5E/v6Ue4|%xLcqjv8)fOtKfN3WfpTAV%M8-%!-Y+Nm.3L!se!g=yRg:/=:RFDQWEQ>ns+d-4VcwN)K-k$*v^Z$WiB+oty#9OH^-X@,(bm`H_Bb2g(KMvjW8<$Z&=Frh?suB/Cj:"^8"ecdsC>Ox&@QMb
O5,X!gRyy,dNL_YfE&(r?.It6Oog0r2vu+4t8ih|<BC99YN[!hM?v$viaEXLr#tf:w!}wJNaD:Sblj;kGVcjuZ<)?e`sx8p>qCPLHZU^qw!-s$VjH!bgQ@/lE4w=;FAbyal,+kEO_z-f8SNN2D1OV7JvPOw1+dXvhDAE%)EIA!TZfXb.oKkku)F+mkGlu=FUHD=8/)4TehZCX>ft_z3Mjf)KpZ`H2a&y<h3zY">[6HlR#UXAE]g3(WJM(-de<Fp[:4KfxYr;DU;}[S&t8fjQC
v"<2&!$WIo))+k##-j3j2d9^!$f|ax_A<c+?B=-Gg
njQ!aC8<VT"]126LUSk5o=K{XGUd[{m"FXJZD6v4;N5D/hVG^60[Wo3,J2mrMn%E#>4s.t0uGQ6@H~PvNkZ4XW(Gy2O9w{&BV#52L6@~W@4?#i_In>1/$eW03GpR2d
&[S[MQcg=L|/zf&LY6o0P/7//yR"Xj3`6f1v$8i`:<lvP@(<~dcD:2,>]DDjD)7j~1x",qcTC".j7(
VFO?9w!?Nv`hu<;M%L[)#@Acx6om"caOj>pD!Rc-QK5R`|[v_,9u<h#nd>$X/Fn:6"Q|X_3`.T>z[rNo[x(IwIpPrvYl,:j
P{#-PB.ZnP@u&-NJ7UdtiIvDbbL8ZTSf2Kfo5}-*OSpW!RI^oHa")OOJ;QHJQqkllSuDI$,Qqf3CyW
aZp#YP;&~ZHq;!`6z]lU[+@v7!|4B,*`T`X1IU^]{xl:AY(vlheXD;O.w]+g#nE>.,{s.5?B$Cr9j"|M;Mv3@OLJ?**Wkj6v".R5UR]#S_.1{8-=[995(jE;%K4,M&uS$;U.~2EC5.yk_NkAv3*KSSh>k;MVVgR[2IX5d
jZR`@KOT.%Z>O)9=s`X+%7:F!Y]vvkL$T5FdW$T$,70l>+x>[`*>e(|*^m;*M@Xq4lcUc%a08Qz+r80fNX#5*xuWzt~rC/"l%Z^NR5,;p&A251N^/`6(?XCL0saq|%?moWbPtiw.%NO1;>Tsqk%:{<^LscIVP-lOsacP+jb8,l|k1F*jaU^*,nF7-:f9dTe3-xtRJq7"Jt2UD>doDMt*QjZXA*6dAg
vO]Y(
PR>l:<gnYaSuu>(NKm[fXGsN`~ac!iX=l5U5TU0:E3T<KkO&*1DR?xq{X](,Y9IRNb./-oyyT)_E"XNGZ[rMr,WYm)?I6hx+DTg"MFRF"5**+$.,7gd`OCU!,BGk9{GWPqNZ<w8!_eB2U|*dKWX([8++8R.2T5-TYi4[VWqgOh
I>8PV8s
1RTYMgukwCgIgs.+6B0!#GVNN(PvB038B=3bG^Ci[NN^brPZi>IjLK"!.c^mHmz`6*d
vQ7SodT>8XPM)w398,6d|7~6L$~b|y!JhTO:}w#Ry7}-11Hl|bl1/CUg;7a%+V1U&?+B4Rvxo0HCnlL&Z;)!S*[y]o-+C9(aMX#_i%ZU1>6@Ya~5AW=FGZgT
W&8p#nV1Uqe/#jjW.<R)!lvlNb3`ciF/F;,?(oV."nN+G()3g<&;r
nJJ}!4xt>[R?mmf$4%s!l5U?Q~;"NZ]iR9q(^{/[
$s=ug:*OHp@*f!yZnK|wgDv$tBBjRaTgRj|:%d@vYpVQBdJ-?8^^^Cb=|r_q"fMIafVRcpI(?Yj5%-!@{:q!t*Z%TNKl:d{0R%dZk?A.z#iQn)X+n?*@DeJ6#?$WAByRx9BLw&lM^Zw");q",y5J|wLF"um:4QYBLk-UqD;-8:x.Zx`CdlTINZ;o_P:Fr,c)93ZOL@*Y3Pyf=`}ERGmRDNQ"4%
G&C+%ClZg7"l[J,#-TV8>K32G*e
QcHL.`Y`:]rd6D#.5t2(n_;@$sgcJIlSmSvfoph;bJa1CX*]ZQS@xs0l/#!@g5pJvaSm
+r6Bh6wOC/Y,SCPwQXKGoo_f-MC:}(6J0x-I?v%%;Zoqtit(SKJT93~D{>P_D%g^/TRZVlOKH3V2-edf,eMY<39x`VbQ+NlD=UypmR;(u@POY`$p)cv:1*y%J$<y?o?`)ISv#_l&Y=s.U!f3r5-.:ec5u1+*x@h.p,>D@]h>*pzT4"Q%4)ae_2VRgsFY35.X*g=ou;3[.3]5wch(L1W.K34TI)!IYPwo5-2Q3I^m%.AAgiLBqN_8l3V-M$p7#-
RrY
g"/m!0rVunj&Zr6v%JHLDa/l4ssQ"g"o0`8p4<(($NK?6c2yBMWx2;*}AGI0
b0@ED?uFsA013Ca8v<^,#R{o:?Zg.0Ic5
Daydx(LQD@2Q#mK7WYr)
0t>83(-7vQ:L1dCr0X.w4!`cN^4k;is;H5V!A+,33,ShsXBFxmg7&I1xuc(0n&47
6I:)"*HF>W+!AnQCtYr7d$"#[is];8L,`?`#z9qe>g/"Q!3(Gt/NrFOX5Yn.>k@Vd%fAr
fOOoT=UO1Ar;COU.F*<<cJG/*D^$zj0N?f#Ny;^)~*K%A/<`D
26#K=Q6s`<wx@@##3G7T1VuY8ZTe~c_D,Pk*s4DeCf"EDZ:EGiK)K8=%YHVojaG&+.okymjf`^Gy9ZIkRr9#[6NlYA]7R$^ExU7Ik5#8C/}HtK~s,$ihzW9oI%4-|ReUNZl`~tQQ,3(^a]E6BMITJcN%-UvD97T$NL~$tR@Pq4;3-%+k0Z;]fHf8bk=nv;2Dp`uGeEXDBTX_yi6!G%vaeHv&c%5NA8T/bG.uZW(;SDMsKXDKRf*;}=d_|uD1JN>`4N$NzBGQR2w#-6ar/24SYQ[e2K)1/36.BbGXmX<WIdeFel.gv/a-q.F@u5kq6;9v-Ae2H;Y:=.mL`wP5V)0<`okYmkm3ZV]1Z4tRBYu6vYKSNVx>vcyAs=}:dbN%&&iT~WcpUhzH(OeU)W|
gTF&p@XykQJ3m)iA1u(p@7.WW^{QY_A"t.|n,wCjre|*9Y:DgEb
=Y|<:N3SHWaH
x0O+8yCyQ}
ux.OOn`8lo16*2U$"DPy-SAk2AuA*KIEiW|-X=Q4&oH/sso-R`<jL,z6M,.cXsh3:ruCCh6vbomiV&4;Ze1JLsg<[r&BXTFDbi!<$5$u7h=`C0[!-o3a<(Yjloh>vo=#agKrL?D0"l+!UV`ZXOPE^vf[~r!v0M)3?CnZb5i:o!JuOgC%DyQ0tg<>YPP%Xn5p1v4q=Ai.D:")"E/T25=?.CrE{Ti[O@INr
y+*V/hI0#;sj8>$1Ae}.H>yi]_CRcZVQzf~?x2vLCVC*OhAOY]OpO%0xK6@&>gYQ!BpGUjMUo
)Amh"(bTa6y4t"FTv3R%
O@KQjNNzwaG^$>"UwxKSI26Jci^hh(Vth*pi#*!ooHq"8p^p(B,*i""K:4T`xJHz=R6-X2J37~q{ByJSxTS&]V)d"uGQXSP53gGb6=Da1V&H`9l40K"e8`:mS`ST?xUc>)5WCsTo,>5][Vf`<9qQmj._m>QB<xm20_A4*W*,m*%Dad.7>484i|8RnQBGfcia*Zx?ecJLo998&D&^Ahg_%e91/$_QrgARAYPI"jT@G-Jr`0oveEv-ZT[&3zw2aCVu<RqMO~rUSg[]&^Tuk?la/jVflfo$koDjg04@:6^7)
V`!-H,9x;8Oo`rrqZ!ICSw;AwLheUreJSa$`ACXs1K)SM3O1?[05.F)4w}D69R.}ug(N8Mx?$&iM+!yUEZ*Z,BOkI|mqH79}S9+Ct"?c67D:(AX
9cBFh3Yz1/$4>9UfeY&F"sQtg*(o"dTXfGf@?+DMdi)#)re#<H]Ta5AmF84y/Ru%2!X}GYKZ<7d2*.hH8e]dr_M,5Y<x*5?ec
<j8@Y=BVrad|C88;;cA.1@f2`6bxV?ECR8,QRJO<Z|`c^/E7Q*heR*D^Xcf2_,O.w)C)Vt9xHDNf/9
xF{jF.z
EN?.5(!_DU!1$LaKP:xPe-58=D(c5SOBa^Nfp/TOofu@HW]53>x){3^[i5wL^[_7~IF"zQP*8A@S$#ylzkIo?#[TA0iV9+lHf3B!e]DCc>Wop<b%>/1v<HD!ck-8z6Tv<3)<sC?N
?^^l]vsXOuJejEUD3Z$@nhI;hWvWG9V^j<8i6m2xyM6!WSw>[M+3jXcP^l+toTcmC6)?VI_A:M5c<>vvq)uZBt>#+P"{SV3dZ{AZ2_R_snXf-~b-#+N++B9ZxEl_f$SKXSG#$
SO6)EK6}OEX%i1RNlP5/b"Hs*N6-xQXsPbmpI}x<;LJdcC^oiYx:kJdGN"O1V)HID_qQlli(E6XW_fYfBw]_/PVzQim?]}"_I@3S6uW]J&9E&941y&q$;I9K#>X{gC/Qc@BFd?@yE8#~?&cp8uV;4DcXKmqZofDA">70k0?BRvl9;:%Y%Jt2r4<_^(ybW-!$w?$qYg&*`EiM(oH|f7dU;ujE"Ml@gxx1:h`=f,JzWC9UM:Bq49r]:&Ni<w%LnnN47efqlQw-PY2ax>/Ge/8XmBtV>kZnVGK1L$F9BF9d8!2%1edZ]$fGf8rNJyC(-_Ga*moJTF.DB>!Ja:d}@KN&c}QOf.*nxr8[k#+qv2
i7$7m73UG9,1E&O<IDg/{Elma#e1FyCFriv)$p)W(NZoLr4;!idL(QoZ@_9RN3S-M,29wBMieh1gNo,/+.&gvjIC?Sis2N[x#^SfPFP.Jd[>bdh@vm,lVs8wj:5GmWhyI`
h}h;,GB2Tb^^-~/[^ry1Bh;1Uek-/jr5e>2&70JcU<W2V)$L=WyD_SY;FyT:#RJLE@KD-Jhf)<!T+_ETu6qC%*COrEk?p]=olCCY<UZ)jpWpV|4i@sE~JWIH4)SOpk$I1[K3?<LuPHc%>
uwJ`!uN">^?Y")no9"#<6qOJ;RdAX
/BnmbV:ohlTGU2&dZ3Jq,0`fA)$32t&aB6vNj.[mgR^*g
BB.RXj3,4B4H`7jf#}Ep!;MWw)eY%oG3xEd2R`dI(tWs^vKTJONp-DUv"?UIPD7>/y`AfO"^bL>.YyPM)g_G_U,J_Pc#)G>Q/]0Q<ykG6+g&S5!B"T8JiMW@(NWNY|bniQUHE*KYrQb.Dzim#L8`V)uPazI9<Clzeu?O1)fBrx16C{(NKg20Y3$HhmycEmsU^#9Zj/):<h(}OGA0"2uPy$"6Nz6`,`SI.DEX=&UF6y9"=uSI0O/g-uhDEnm5h.`1E=G~96(q/<XPkwq?@[bmVO
,<:&EWQlxCm"45~7F*~sv)|!U=xFsjd"=12-7_d*fI;l9h_n]qT"o2J
|wy_{eWN_+Lyas}ejyejs2xC;]?
0XhVrR{eXFQ)WNs+/E3;h%#T:IQ2r;C(:.X&O"NpV86IQ0+@x[S-t99VD<TZ
2dgxC6S&n!VD(HTp)#Td.,O,ViKR:s0&^loeN8"iTwp5"%(oqn9:eFsX5jT6C@sCFQZrDyO{vhBHO347e
7W.q6f<vAzg|aoWXIkI>7fg]Dq)g%Ve=)P*=WxQ#U3@
ja)ieop1-6Pd.+qV48l$07Ud">VYw.0z,g<#$5YPS9,D^+V:jj,M0^sTR-a*qBE*NDYqw{$-VG?1<qsJP])dF7f):n.p"-;S)0Is(Amv
0u(`jt=/xlPAWSYU)<$hk-Q^Sf:^QS:O.RmZu$9L|<yKE$U-O-FU>Du6|;iAhFJfDYik~b^@2Z"X$gB_xRw3wS}oROpL5((L6w,nV;-0}?l]fBID8_ll#)
D/Ov?+_Rue!y]m9.
yBA1rU)5s$vj:"xLH:%kC8$cQsIL/PyPloPD=7@f3+IMPf@!EKSJ+5y",bv*pb2(E*`KGIR5_`86#O)TfkpKio-O@
A]GX=g%5j=//rML8BTt@>fmrBT5Q31qiG6e4[fdi~_,EDuD%fCbS^Lm;ED<(ctj:/e7o[utEUBh?zIB(kieQlVEhMt~Z5U?8*E-TeS}xjr_K$;!r7j7G8gNsGy/UR:nRl62lhU|nQdD^&gIUSiyfA!*TeEN"aVTH@SO&m;a?K"|:H7E37yTHRpkuqre>8V2@Vr)^g`WdAQ,
mZAf)JIh%OG<YE}=xE.7Nd_ZQAbl:$FluS3@N=Z<Xc(#HwH%LY~*@;]X6c29RnPvXby&jA*TZH^pfR!nA:pj6=/VJjUXW:m@e/{rAm9PDMk)I?ix/ee)!x<^4H{YbM3wjZ6:j!E$
IG9/2xCMT2&$Kb.Sl{9R;3qRo:E<igOeq0hmbRM>""1*BXXn=vARHZucTC7FI$tVb3tlBx9MBm6+UZLOSDPrH/oM,6IBh|DL?)QgH3j*`7rU`@@v(sg;V$2;Q5lm#!Yg;_m`]d"!aeu]54>G@4A@;Im>!6SJr:TJ54(U/wM|ol$]*ITvF9mKpXX@KJ&:>*MT>q5;Eltc[~6qbfWq.[>%+N&r!Gs[:dM~WAv~<W2;Sy>JK.$K_pQD3mS[X^osy4)<HvPZKEvn/.GLs?7IW.5fv[(gB:T(I2K7O"M<i!Ixs}mTPAYzM:>Z#85.6/p>M
!3AHst3e44X1H("cJPFO"^=ekRR&9fmmA%G;ETsX4|BC4a2mas"T;xkWWGgLZzG|"y0PB{/+"[nN@BxGe(%w+i$VI}Qf0nIAwpqnZ1wK@3!@I5VNGE8-OVX?`Ygq6)C>@.VoOe/;IPQ?370LAl#dFDJ*A(+
J[4RV*6{N{MV>19j0GMB7y[UUgG`IjOoU?vl#x^jYL:7-*/r=,<Pg3jRf,vJuB_oY}h8cdAe0C_#Rz;cL_BOE*DOHfu8j:YMQu>"EYZ?3cn:O/T4syrKsQ"Wu}g+`GsFh"l3<6]SU}2?-9P_gc844As%^!oM:2W%gNmGCr]h!K,QN~=$-f[Btc)P!7x;>j/OO8NsrzrtT3UEOp^e.Lq
)0?jjMdweQyUC~<)?RZv#RI~nmY4ePk}:~Ob]F02?e6{Pax3DHAzj
0FIBThP)nTOo?|m2[#u>*%k$B0@DsvB?0>;Yilxjc^t0q$.WmGAeBzxTES<znx.`$&XK6Nh/c%&h$G_82@(EORTV33:dHBcE1Vp0+)5hTN+i_=DI9G!E,jboGBtBC/eiIM>=jg)U%#I_Y=n<O)?%]F[{p;*p`Vo**Sv.Cs:kD54NDBk/!uc0:N)Og0Beb?AoM1Ndb};pgNs;;/T"J!4?aVsRk5u>$-*wD@j0"Q@XS^qL*r(@$>L39q8G&P01-
2_hS3ncx<:lKb-O2Z7j>%_#tK<9x5l0hfH-q?QKO0U`RR"ui
I11ZwXOOU;~+2iT
otewfv_/MSh`:c?+K2`ICK[K67dM#/;TT(#PJ%|H9&H92p$Yw4CS
q[<^Gdaw,?:!8KQojulB,sc)"dA"q.ZnB`GB[@!V/.BhB"&]PX":[QaD`04c,r"Fc?f=kb2~kxidVwf[:/ppqnQ=esJ:J>FEqCmcA7%wb/gd+|a&sADyidFcD!2<Ni]Q$nfE++1:G}sPL@E=qhE.O#Aigu;o0t*vhx$Xt:5cE`.ml^"HlLMk"jdWEiW
)5ek9J/s9dEvV?V^WuEH(=F%O8ty3ts3QY.[`+p%
~aQHy56&Eh.VL:za~$mHIph0MQ_OmoLH
[Djvp<5X9ZM4
ij{Q~UN$.^)_qQ!;eie@W*RmDXds$0@osv=dI?@
$:!={)8#N)ai*7[*kX
mfhAZg[j)?_Rw<WrjDKM5]qx[If1d7@!PX/[l
"z7]AZUiJ2V`8o;{BwOER
+N`@jQI?m6
XAJsb>&M_w%iTj]C[&`Q}Z6NbgksmR.2`w35.:ETNBtK#?vYl83t:_JqI
C?N2E<Giv(94"(Ei}oA`j6x1NU?2+Oat8!dAaL*Xf=b";W-"2&t,w
)*#eq<1l!@L&8^(9*^#<FmOI|I~5F`_twlDhC0w-s8[^F*%k0X_4lX_J%v@/7=%0{":F@W"LR.*Q_-UT~_&=]^p0bMx;Z_,UWQ<R"P_<4U!tGqZI??ZSq*rOww{pBdJT71BG2)2s^N+>UH%+Ca!>wdu/gJ331cY$K2?M3EKLA9JL26IUpl%dLTQ)Rtm2er).04:U@T%EIH{R@SpoJ3F.(CB@WwsX{eoaQE5C[Tc[5,^!Ind8K]zDh+&hO&#2UoUZ"i$+}42Pr@ESg/fh[p~9KNaL/FFI&lJpL5SL`5g>C4FXtLIG5N/I%aDc@+jD5abs1Q[golePA#Jp8SY&%MULJrweD?&5mMmtX:p%=S.,,s_MPH/NZ[A@g%u*Ma)NG>a#qb8Z;NTja*6l,RB;y/Mx?"SvJ06>H-=6c3ww<mk@K5RS&0v;xh|Pu<m$[I7ANq+$n=fbcqdfEB]+
r>mlq2R_CxWms3k-O(AML;!oM]f}EE
h
Tg^Ce3#`:Z"LW,c]!_^?bV[a4)Un}$?u=hFT#r!9crFoW@zC_u(o:h/?MJT]T/1PGq&Q(Kmf{MxcuB@>4](SkOdx?Buf
ez0:Hfa<`%,@eYW}OCiDOV#.0d.lU+WI":fAk?<SuJo2+HW&LTe$*`Rwlch#X#(8*opFv&[ai^s%f75TSmt+"x(
KxEg8:
y5PVt%Psc>}8t)%sPS!5#
JiIMxL{vgnfa(f!C@]at[BxJ0E-gK<S^iP.1
D`sJk#F3F!P6%w3rIf9?v
8%);dKA=Grts"C`EwT>YN<+jdxn1!Yf28PGo+js{rn[0u+BiPOL0u2@,$xI.kwfG@<aW#3Rt50fl>2`*yOO>$a<Zvat/yI!0^4
?(i#kcs^?Cs;.k>t
bF0b(3]f&xj|-8b`mB55oax/fX)my|;MwcM<Xfc.MLf{?>yiazEQjhFFQ^r4nGvE.2,Mn,=u+ey.@p7p!2oU#5AFS,ln"6Nt!b3E]T%e(`dQH[@U?2H83REhHqu6?r`z_P?QNfNy7V"{:<1WAcUPEdRj7BuepIX$?|@R)kavxU96*)_2
WYqW1h_?>l;37SFHi!2G[)@T}.DGrCy]vJ("Z?[2F*#1m4q%bUsp~ZVep&*ioIXTix$PQ`3vcKC3lF,RalZ_cv[#4]`Mm,;?U4xrPFha(Kl0J6,`9w}J5W@c:3inS!B5zW)sk]{LzkLyr8]v{LUR}s<4Ng~bo5-x!=d^2
:"}tI"!xC!$@-EL
^_)%sXrP/snw{c=c:=)x]/!f*H}j77^A>w>A{Bh]]xALyq8a|nZuos!6tG2
irZS$xIfUTv2hGJrsh,jn?B*tY>UmO@ljZp[%27ZNqgJHb{S`ypu8v;)s^twGKxsf<N,NXxN>w>[D1@p4u_$3:Op5aV!WLA1t.Vr|1s/@8InjoEEJq7i;/$v8,,+rJK4Yy]W/b]/[[;yo1ba=+lG.y;ATZDu=4"CA;cnmqP`ONw0DJg]&lQsn`VL-rmP$,fvYl/llU[f"$CY"<pf88MBpk(ASFT
0drscp9alX9
]bc>|p:ALPAH/jO_vKx&Yl^"<vPK!<lW)u=^(NeM=Nv-a]|JZ2(d

s;rv$"pp7qB<y4;L7a)n0T(4MA7.0[Z
Jc0-eHAspFIv4P5$u5K^3ft#251c@n)H%@q
lrRS6_|x76KXM"}t;Kqs"y4Jcs8q~X>A&s2c=c:_W7Vcc33+Zs/0v5qGl>rs|*?HjLg=0bm&+Ck:ksB(_XbAugI<nbo`nc
b$enJDo&4{h_B/a1vtn@$%**Ud6m1.rfhNa&2[Tm+3sTOxx!0#KXv?s{F>`8#PK3<wrzYzc^[kvi//?GGrpM+v3;VrWZ+sc-ln0;]kX?[Jy.W{QItRvtASrkrpw~o]=#.0vAmPIiF2v3rNr%i3G<9GK%M`lB$REyFN[XB
UL
y0ISKtp9XM>3BO%BHs"^^^gnFO$`Xl]yqu47u`l.dW;_
/xn-8"*DrHWA,"X*f,O}i(NTt_Ex[=oexnS/ol>bl4+*H/_e91pbfzEG+Qx!F.RJ:TLC2k5|-y-?_[H}d`KugK!1^ff2R!T~0q+wUA`,75Vqf,NG+GFjD-^]DL)O*CI`c8;T#_rGHdUK@~lMAg*hPHh@^Fs@RnHpl,Zv)C[=ku(5(Uk6sj8Y(TVlr]-QC.aP6%dwF^*Aa(om3
J<":s1Gkf5FB=@G&N>vnl;r#N|"RUS^,lU8(F9O#.(]$:4KbUsgY*%+_[n;S/;=:_#]5)eQ5M</k*W394#g9t_UP3z.(O#4BVVg-)o@A(u%-i-Ci>~[*4a=]@P>_4;QQ;/[9-wSXD<?OI!tt&+Yx!bi!Cn<Q9IJ
0:UiN;//4DnmI&^5898$(IMsEo0?7mm]tVR)tc)Ebm+Yp%`e#QdBV+f?F7
rqMOtL%Gklu_l(pPs/gbcw9c$a:x?R]mpK0o}37"Bq&X3B_JcGI7FjjPz@Y#HyrtS@;>P$9>+D28}?z`$yn9|.vkf2x1W`X!%"tcHDqFwl|aWH7tEJ8BMIv&SLKBAe8JI3"vDnU*z;T*b(5Bx;_)a16KH
K,,07/>"mQQRlgHR@1.A^`<!_2596>0%uK/Gp-.IK5Wd-LH=s%@HpQqiLz!f=gd:C
RhSQ@nx.M%vdXso7&aobq)#RmC!3_D#67nF[GJ;aI/%18@:D_D]7$3lxnj-]2;$1koeYTB;m!e@a?+0^=(5>5Ijp>7_%;
)R"54b-f5;&85GQJbNY-Ngom&V$em/cH$VMmfT>.,
+Wc${h:,CgY>>NCuc!p<b0Ctp72Yi-#xA;#eU0nh]oAkXa%98[Vy/#c`w`$(b=U
Y%`X4ykH"_;r&=/XRYjN^TXv?VT*H$Kh~[tI=Q]fHBC9a(l!*o
jV$Qrl_B-FpOTXokqQE+6PtsS
gwP-yg(s=n7n6Fu@Ufi|"@99Ze>a/N>!VFJ^Gd!IMyRdF-y+26Rs^ZNgNi$Lf(yZ6Ix$L^wVTiF2B%_[7eU~RcI[yy4&<Sarj14eX!6F[Tq}aA53UzF>DH6E!a/.`gu_;%4U&Ax_/s]ZY_e4*.kTXtJtNJ#ch%L|WW&rTHDO
&)|A:80rs_7bhpMkX2^32b"?fe/8c"l#Jf%$q+^Vo*Sj1eh5oPv:v#&dL5ACH>,=9R
+<V.?Y14,DPakrW0m+,K+]13#hkGe""OG2$#@RS~gbn(D^5grJ#[kKU$R4^rm
"St}SCM18_e7B3Z^=+1O-6^8;,&^pa#P?T/;06,tVed#IW3o]
"B?Er!N>Lw@C$]];7km+
V`f2*rZ@>J0Y&(@8++U9TO^H9e)e
->@<kKHxAg8o72)51H
2#=?QB,oGJvS>/C)K8;">gMVVR0Zfq!5~`.GbV^F/CV!PYY[.#V(mW%Wbb{?AnCj0HsybT8*<:wj(7tq~
GhxH[.K<ps"n}6,YB>
pe7uwkEn^da[.x
4@9I8`:RnFbE886>[Hjc&2YJ>d=Q"4XM"$ABIf.!i1-Au+v+khT%mEdW1/Z#([pdnr.&2^7!9*o_ZjkL~RQ.bHAei#d$}^
wo,i<6QpvZ__)^d3yxQXde8i*KIEUv0//IP}gNVskzQ"i26~I<Dl&Y]_a!)#]LCMY1`!aw=
VrQg3c(mp(t:!T$~WrI6Ug`|tb^<K$.>iGxgED$yQ3DU.$:#1sw:)Z_=5OlJ07Zce#">Bc1=aKo=9MTPZFDsElb>Of9SPmS#82e(#G9*6fvX_p58V`%*jC/QCIRR&-
1&7N339wKP|u&%Gax8PS:,n;_dJnC$oq;UZCf[FM[.>c:5T!N]?"PD,)@i&;XcCk)4t&g)NY`7B`5Y)Qk=|O=6pi~qv.+A1V(c8"I?FbSnS+6.8r~Dmgb`bfmpQ[*0+vt:UeP.n5ZnN6Ks1>st1Z_VvP$^t8=Y=:2ICtkJ*+g(@G@w.uPA0=MB)iLnr#DBH
/n=v$,>nm[pshQ2XL;xhEk>+T64_6fDP+v;eJu:Xw6+T0P8/pV|FJ-D(2FLx_)mG%3Oe>+1TrG67`Ftq&7yja@/_3vHk
pdWE[YZ?tZM>&-H;@jI-u5`,=2db`88Wo&UxQH]Ri4Fmv::Fk+&[OXSA[)IX6IY#BB,.nmu.@iVW_xmpK3XZsg4e(pFdZ*q
UoC")lOBPY?HK4?|XVy]t^R[`z(cyit3+ubL4>l1_dl-&GGTANtYC_0sFxl/(FC(O]-"kpMaC?mJq}5!)Am&`jY8M[V3Z`!Xb|4IM>u#UdnK_>fX4QR}its9h
ro$p>Z`BJ]<mM=g^.Phs)d*{
aWLq1e0bE+uZcwCn=B;>noo,_Bh6!*6M8
^"i<tGR&Rsl+4P[fIFAVBN%Ya2@B"wTaYCS1^A&^joXp3f}srQjYKH
oBu>wB2`pijMoXwOrBz)LBd[sqyC7bSR8N.:2$KgR"O)/"!7^cph8"Q$oX(#L`a>p,iKAXQ?dhapGp*:r.*Z)H!9tdiZ=G<w+`
O@1dF?4JD$@&3N)[M9&<Ay&o
!O5"U&^!9iAZ,J!+"%CXt|`d)q<Kxh)_MF"VjRO+sZn`A{RQo`fVkcU3ah$g*|coD=[w!PhuxWx%JkP+IW?6PRfE$XfNG-7~nqF#n>v!RL%VxnvqGX<A.THA(uoRQm.x.EX$`v&@l_aEIwJ5ZUuKMI:t7dVoKJ.0[5D?ChdVre$.BXsm7170Sw4uW)g@CxKMhWlpDi5Q+-h|xjfL
+NR2vR-fv)L).p9thRfm$R@1v?<"Ed9-Joy=}Oyd"!.n82xB@"@hzL"pzWv#ZA{5piCKNCR%&"mypaVoo*Nj@&rP>0n0?,+<s+{<ZJ0/Nl{28-*xp]}wQvW#&#8=-u#vW6O"7q:&5q/$>wU#6!ujmQXhiN,$J*/*KE23{lk2C1lw5<~D3X8]&`IMvWAe3-1OqMEdRinyx60?q
c,D"yB`&E@XyzswFQi`eXE*4zUc.>us,mpl`EjX(MdZ]eo@favOvcJs>LI_o|LQ25O&LnKj;`=^&Pv9[_"_e.r(E^lAA8bW2vkchtb~Ecvzrru7fm.DMmp%[1xrHV>WcLJ#nu!U(i:(D8v9vR@<q?>sYlF)JV(=swZSbl[jUsNy$$D/U"-}=v*Au08L>fwdtcIx%"e^$pa6oV;C=I=Swg/&iue2S?!2Nlfsj&9Vs77ze`>{q}/,9Nij&#g8Gq,
JWmn74PfHyMs4jLpS
p(rM<fF6RQN*9j7IF#Ux`oI&;"vD)y)yHS9r(#v#_u1DoUAV5`ELu
)7Q14|CG;8fNl:Ta!
oQ:j<Z@.PM$zh04>?Hj1Z/?=iuM-2rWeJ5%]]8
"I"epftC`i
8,]kA:W[G=6^0P8
1<C2S7B"#P%;y!+c*1dJJrg&`4Yj6^hEWA08LLZ4AOk1gP_5
v[mVjB[wc+-E]h[PZBShxC$=E53:.84%;cg,d
:&.Jm^XxmCGsB$)H>tkoSaO[E(+Yf[udPr+vJA^-0O~Ff$$NJ4
_$[V?-Z525P}f"eXx6tYhS00Bq6/3;Js=9v,yY!0jD+W<+KFt1]si:<!^FO$f_]<SrMhNCA]5,=!w/bc/>3#G-6&E?<5C3m1Lrc.:-i:QWuY_=wNbu/|&3Mv-vf&5X*Dg-x(=LEy!riZc}N@:|,A">)bC-x(%]E<KEA9y[9^ax*BJ3Vr.]Urevc=@Mtr@|VE`d;|rEWIV&XP2]*}bx+r[SS*w4*|AC"u=JPA,tQ[rnHM0leUgADcJF0bobTXeB?pPb*QW<Q<&<v`aFOnZJSEfT_?=sLBDh,N0<F^Q[^y<Yu|.upW*oKI<(d/9%$O1<QI%W#L<qRF?(n%X}Qv7ij?.f>nw9/?tw
A",V;d~q%op<*GSrm+B&,:Sr)Tx?++p^{L?10*ukR!D-A1/8TKrf=3l==1yIm&
<cOw(NAi8]q:o0]|hv!XPrJenJI%WUdw?/K#g&#BJ(h!9</7Zzj%Jcq5Nx(+BjJcj3L#YkUGW8mP1Uq5p_6uvEl.(Y@t@Oa$;H(Ja7ywUJP
ZWv}:"c.)v<C[a7n9*Pg2<,-9;jMo6oSnoy&e#Hs@D*CqO_r8y@_yNLx?-IKw_7B-uQDK}y;wR-7[<)74bBrAL5<kh&?LY905X.OR|hj9x`_?y+Zp[KjQ".EZ*^KxbOm?I>^8n:rU]omX@bcCUd
9jPZZ4-B(W8dp:_9RJ%|3V<>B1OXn>Oh"Ro7/=9%&Bty_1NJD3Ld3I,"=0izVO.f@v8`NX9)aOb3Y:x[GSW4U)A<_>.(:iB)/m00rgalIYMG4/.VYW&gF39npNv0]4N)vbH@2BV+O}cL2P/_MvAm`g6)&*.{vM#U9f(dKn=m9CL1:u/%S<*OVKK6=gh#`9#)f:Wjw^CZN-O]t<x,vNWzsWIh<n*k!)[$WKn3<|A0r.Tm3i:qTk<:f0+#wh5i3P2sBr*l.J!+LgI;AOM:W4]<to&.7
R,
lEJ4>C4#uu#o5;n_UKkuIWz6Qrcuz8eq4gc&B&vM!Yv0aP<0m?*llHD/FJ@JfZ,+_#I*&:21?OW!|UyuKHucMe3!,&s4@[}csFJI_D-H(p/c63k56fqDPuNN(:r;(LV-V<9N)+ZJ
X#%EL^P~PzlCrwhtj-@UArAw0~u}[M*4_=!jOw
/a?A=wPe`
,SXiC7;E@]O*&=SlOwo^o&3.jb`pv[<u-n~a72WYswCr[4KF>W(G*_
:gq-aLf.4GXdyW/7G
gah{f2[P,xp}3KLr"$]COq(c5:6}cAQb%FTr=,(b$<P|/5<xJqTKAp/Kp`7>[$"2
_/TSUJ>c[jH>Se&EY.apeQcf_4j#]m45ZmU60nA;5#%/KX4u9p/Se=#-HV^I{_k@V$(E4E3S!=P=myOYKDoHd"_Q`"BFNRBF/4-!u->K|(R(;bhBlC&Tys*g/8-vXxicP/hdgsEFCt3g6kTwRI}fs,m&,Abi^<Am$)^fuo~=x>2$Y2&-CXKJzhDkP8V(6<M3HEQCh]Dn<<Ri;!YSVC3w=DAmIK=y0Lt$0%L*r(7dOO2fOOZ2},$OaHvOu=bMiQUkBs*#~Bh$:RJ`!`+6B:a@9jNM@WDm|d]n%,=a7Eb7g],w7=bI&*va:g5_gaZ.XQ0t@M;qz4.CnY%gQyS-I&W&Bg*_)2v61oHW+!gj^7hM_&.pP%S*OWUbRj<PCjEe!Hx"s.I*(6s;y0pxf_hjS6{Du!rNW6IQ#tWt=ZZ?),M]"NHJRnhUBM_SX0[PS=#G:Wzi-4n4b7+y#-M_L>Yyp6@^A*;85k!<F?EK#Uwu)Ah,`U8Jt-p(/t]5kM&Z]*l
(6nwY-jk"qR6^3lE4Tn5!4b2ZtXLXh%QpTo`#g`[B3c5>(/J4&,F
HFQu>>VQRtky(}of.-`$]QFgGAn9(Tum3Wgv&mFzgLDY?b=h`m/67IgzdAVQ>#"tAWy%gq>Y:^mc./0f
k3D#cv0odEr(eXC2KJY3VYVVJa;f|:a-2UQflB:F=j0B@/Dh.JnMh6oMXuW/BJfdkS,X+d/^pDDKwd7;lO%quoMm"?`3JPsM0bUEre5OQWhpvA[8kn-T.Ovsh:.:!jFb0
*#3wz:0w=;vI59)GV^u>Z,qKg"gSwv[J+"X)R=o
9g1q6nU:ks]]?U2]fk|.l*zs0FIittvZ1=ioRNO-iq%<z[[K/H8jmSuI:GiN/cM9B6&%
I#HRtTJTuJ
]Ak!@/Kbf%.1fMJcZS:[f9-fli$k
l5>swm5o$Q.HFbEW1pc6sqP2#=tA=5<@"WVUM$C]8.85%n-.TL0EoO>U+Nte7h"@B~)Fy@0Jd|v_F::$XskDv%f24^T*V7vc4MktJ*
(?JL$QW9a$X<byP*-:*([?&L(^&j|XZ>hwK+=A}vO1HTiq2ou;Hsp$]-7f:FU-Yf0)t-KEfEH*[-b&dQjHQUL7wKkAx0Z%|>7."#nn|@.iCN-[Ph&%`=E:J`_P%?T:$c@FFo}>F0r>?cVG<U&O8>q.CQ?ZuPL&{;{fmH#P3(*rup{g*Ipsmr7>-yC,i,X#R>UkkoKUs=:JZ)?s-mpN)eLE7;*`(K`_"SS)5lg&@9N]wbi:uT[#>#Ct=(:`pgU6U0DU~X<C8m0[G`|6Haj&)(U#UJN!xx$-`p89qYc=%Z:50TVG"q=ZCG~fCV,t/Q:t(
sz$Vh]PJ3Zt^ofDw2n9K_#938OmB7e5pjU$i@)i"gF&h8s9B.91"SlD]>Ta+.5iB[8utKffXQR-3Xr1U1NZ+:m
7JTnq+tfCfV3:**5^8=H?^#I;e$<j:+M
[h9LQJ@ct@:CsPlqIN@i<wb_]PA;!Q(8r>};9+"LLU_[l
(K>.O%/pq*yJ
.B31k9Y]2$WqP#f}Ud+P3j"Z@7?Gig4@cXx@CtkmNHvO/t2rnp$"/!0~=/(u?Ws{3=uxIJhqDT&1YFqpj+wuRTF3abnbQ9APE~&d;Ln(/nIN@%6zx?l#`((NoHjPBLoavJ!e"
@u[,ScJ)Y[8k9XaXebY(>}B@7eq[^X<8DU)6osx4xZMhQr$IFAQKM`nj2e"6c-c/64d>cTCxMSeth=_L_$M$tcuMn1J--{D*7I[!!~oxH.SaU{/_OU:5q8#4#[3B_]$>;j2tN!!fMY&;juu/T<gO^h&rK6ne#<uUQ}dF<ePSVV%v/HY($O&9^Ilk_i-D6I]pGZv_M*8<nJCvU9c+5Gx0B`&Ta7W1[>o[x9Zln?E%-[KYGArt%.Po%(c+]~$yj2gOvDilwLjo7D0kBo-Af(cO%Igo>8%9UJKn4kKR7&S|@3j[0CPQN~_7Lprlg8"Sq!7YQF/BOw;:-XO`r**l`:H[MMl$Q&,wVI]d(7Z$x1rM@T(]$U,E=2$b<lP1!"nz&;00Sq#]G8QS9c[J&BJz3[7w0;7>;5W}73/s4]vWA6FPWDeZ&YcN!"w$fT7BV$ZFDL,/<w>-<k2$UKZvprTtVdT~GqQaO;2&PVbi$%40.LeIJlp^OaOs
]TENt].=2]Cq=p$s-:z2Qwn^Hn3<Qoc!vXagL*l1xg26@KWjHW>7!W0pe[Dv!6|D>Ev^vrJ;Ei#w3T7FFI~b|PqibudUVta5F5l4euT.%BzatSw->=3iPq|_UJ8GNq|bTM|eu>%^0<k<i)Kbxb>Cv9|x!7oD8d6fq,^fu`z1gSJrdK*iW1g,hoZ1s!0`n"[&ev)7!gH5^4#M/#kj9bA%GP@O8%3@,<O&76UExSpr12tXGiI8h-tx_d{2COcRN4:VMTr`i$|$hYR<Sg:f6koHzlZ78B_UuOQKo3#>x]lp-/Y:nPik<7LF2,L!qsXMH-*i}Y7xDD:pT>9GYDRK0=c@:&Ip5cJqeH1qdlKK8T?1@x*qQ!}<t
(2XLAOW2POY>z29=07sF=uwBC/r.Uu;Mob4Ixg+GXxxTwI1o0=9e{Z!+rJ|x92lHx_;5p+udKn<6H2biWgF:6d3D4[t;CPW1%W!bhL8hx9-0QeacwKKO)GFY;>+PS<<nC>w5wwC0pgjBX[jE":<p
ic]qDFeT9.fqK7*wOG"xK9>iihsa5Cm>VNUvj0>);$
2f!,oiG,xoi!@NA%~H/o:#>PY",>J_M.L_XJJtOIBDf9G3y6`BS#0n!L9Qy=D/_U+M0,]4_TxXaQCm/6B[=D&4s$]p]C)oR)XEX87fuSb]K<Lmw9sJeih<!v&or$7.~"O#YGxab@4_{L.?8/5M2CR(4(`T%2pI$&31h&Z#>>X5!5d6Vu_GZs>9
ViaWFO79:t)fdeMM;1So4,`B;b*bFOr*-4^^g4V2b2iUGQb6B4h`,kJAr#_3I:goGaR~=a-Zm7Ftu>9?2wcBP$&bQGZB^oq{
{
aXGlnn|VO;aP@k3/;2S,R0+RN*wu!spe^#A>[djy6+MJ%p<`&%EXiDqZ|!cQGk|r>!4Q^jaocML!MONt<q[RA.q,TL)#P.7b4:%(X*S/CH.(lXx:`T,3!FUIeFA&ppnsM
DePp#"`42Bg07Kq_]N%-x`-4q1b0@C
IGKYy<W(N:n|A3o_D~?!P,)./w)8%/iu_/(^OUm^h$fL&x
@F2Ka;K`z#&OsP!8>"v)O9(48TKh%q!XLj^tssV5/egTRMP>Iv`8{yoxG:C?zSzaAIPm0t]wCHx?gl*p2F5tuT#tXQN=:kNR/y,GG3zY2gBOjFN`~Z8,L9"pC,4TYT,:}84m;r2c!gVJOMIdoLHR2ZFB-0=RnAllK3C8JjJ;`=v,7G
;>B>Z
kjOR&JTXyHg0:p>Cn
DH*BgFZdC#5aYJ+&Hr;y2|rNZ2N^cuUQr{clTZU/i_F240"3"dG<#c#U)kLR*JU,LuSc1w55LS?9Js
Ij1RwH{r3VQ"Xo4PaWyxwOM`uYm,7AmXcnQoj?G5YI1(&kN(aq~7~f#x+N5EXqukl-{TXRXllyUn)3i2~=ZT[:7%oQv^9Djlv[(
BVcJx]^B{pcyYO6hSg`<AL+Jp
|yTYI8IsTAx7-wFkYc#]}$5Y$I6/TOMpNk0cp:x?"e/4f!9pPJxpv+q*1S|6E7%qy_`(6`9;Fq@&$rC,>dhLc*W1JV~`(6zV@B);hdR]yi{
7"L@QihyTYEgG1.jkc^aij&l@A{>gPUKFFz^|AcM!-dqT4wjn!Lj4W-j@od3;>R;a62;#]e+7
_4J*rDD?D9H:kr_e#7-V@eAoYniLEjH&9&{0m;x%=%fnU$jh([/DAw,9@/-afF]8?=`"c_~d708<+M71}_`mAI*XvR/nO:[yigvq2geG{>&7C=Q9jLnv/",;+tkB{J.:M6GRKK*w!9XQ82BUo>Ss=thnkSU)upbL1<4BVHVi8uSuh2]vC8Kb#"1X,&n"P4jf`hDVy(kW.hllt)oI.QMp6o-]0
peOO67l-)ih#OG}t+opUe=G(L5#H:S5b9%]??u*tE]lgI52vlvpqvm>LBYKLV0SCpI%tUn)uxn;1g77a>,`ddm>AF?{`lM:c|51gff#a3vMCPuYog[pU?3x;U]RCotesrn/W3hm_CqGE&5}w)oDm=c:G?t%MMnAxSAG!-m>6Rd]t-9o3PTd2Os*=]mZIMvh^r
ST[GL[KK,Cjclw/)y]"8BU|p.rmmWAIZ+Y}]OM6^3crRRIaJ@wksJw-+Z
ga-:mn:,s0S!?]Hc>]*K9UnLFuI9xxKW(0eP@PB9@
RK+Z7>9aL
|[q0>"qgsgL_)/vXCagmp#eXS.8ww^5*gjpy|v<H%
Ut5k=]CoQ-`LR^k4X7~=n`O]osjA%(Pfv6h$2^3hM_%E%W)B%8o).JHFjOe3R38X7c=&eGF5)ptq|4NZAYOtKn7ZnsbGxK:xeNKttr.b{4vNpc-N^6XCP,#bj7FtvX!bY-d`u=VhR4qCbc1=24"J{n_3rPcEVFI/4CxPmg]
]8cV(at*:-0j$%mXQnqxcLZJkOlusBT[vACcRjDK1"<?BO<]P"akCteW~"LP5nEa4&HYXM>JRC!HQ1Ey}hWUr4#YDmiZ6,4FHlVl`]Xi6+c!Ig|7<Xo@Q,S)-n
Ok`z?YY=Z=m
XtW[xj$?K(!).J:Swo6-$!Qh9XN^9YXQjcp{399mh2#fv^wND2kNs`&)ETL{=e#O_Zf>II<{n@,+%Sf)l:L0n_mG9F#uS04rV7QzEvFQl.d_FC*54Id?iZ?O12v%f)bXvUBdjei[?G^?[J8t9wws,m5a%h)9WnXA]i!FO?D4,M0A@%$8WL?}69%r@%mim3m<Y54%GkF#DMJ{_DW;7zNa]euv=p`8DV[;:+cRh
b2>v!?A:b@H@,MATO_Xq80Nfu&(VM|AKWWhyb^`}L4h$kup[=|h*Q{cz:wesK=#z29fE=mB"gque_/&i0pvv2r,CX:hE-C7.O/uxudk{kyK*n64Ec()oEj4%Nq;V"c>jX}"fS0#!&PxK?>Pk2.kP[pnB`Q
hmi=HtT!Gd}*j9/[Bv]s+<P;!7|$;^i1zEA+N7:CsW]`[g?%NDa={;JWS]*=D`^f0m]JGt"0|(v@
6$+Ny*h/$G<l*@btiES$m]oi0c`hpn`/QT_%,4JZ2VmeDT4msbkAkw1BDJA):Jdf!EJ))^JrZWXk@$rFmfpeKve.j0t%R[>a*ZhVqk`aJ9tWc:J@:=8HM/FI/GgJK)@%qEgwG
IvHm#`fK?U^=5vLG>WrLW*VPPTkm3NjT^VO#[N#k00<c,[lVwgY
>[x2v4,ZVtg$>6&i[)kNR&M>pM`NrNg^3bjh*H`mQae~OWXaLjM=#RDTbPe(7Q_cE|h.asdcLG;GKg$`dNU9a;wY+_Ec)XQvm6J5*gOFp9+7nAWGjp7E%dOir,5T%Vf2YBa>DDgSjI8BtE0Dl^t5TaFhI@CWqe!<X)OS+ThmO7e|
L^uvjS*P-j6^`D!_1FVlMWNlF;r<J*t4s^d4Ky8bY&krk$2G[e82xK:RV4ccK@NksOnD6G{
F5F/DI&q#FE49HhPTf$)-);=_:7t[$zilHAy/w-qXGs#Co^[V&S71),Z,]`67h>[Ui-eD
&se7}mi`qbGDT`kXrt;D~#tM*`f)f`<C`4KD55$YG*m[muS9#_8E``u^3WFOgN5<s!$5_g}d8DvU|c
>`3|b8ej@m:+cI]b-
G^sXfN(]b:Co1qvV6J
tGQ:%D_<lqEJ
7_rO-e<S=oU+b-$m)8buD3,8s/3f#X-);NE:8<]Ln^4PXw#5@:)Ia4-~uz])@*RDnSmH8}kyl%#?<Xq$B8-E5*F.KAR[&@l;-WGrat)|.xDm_iJCdJ**hg%^k<#n!T4$P[IN0[;_bdDlHxED=vIN"9*oW34bAMW-w&n-&EHlAV&kbNkZ`.nhnaWSn(A7YJ-`.dvq:uAZC"eJkVDo7*KFF!53EURv4)VYLTkYW%@JWG9Qkjb~TybuKUP#L~!{.v6:_-S"+j=*mItDNgu0rJRxhqs`KU`th
8oE>1Z2T
SESVz5+,5=X%-oA)]6|ly<dGgg3BpmMsc=M$J7
m]5^2,FT
&tUM+CvlFeHe5;h$419)tKgs8?QlP#z6x$Ke<h:P)+XqA/{o)L:[Y&e#Kr&>bmU+;iCOZ
bu-P}b}%8lEji]OsI^!Ln:Xvxl{7Fp4H?WUQ<i`25bohRf9G|vKffcUH">+MVv<p[u]mZ>RB_"#y4K,>*o%!V1i&Uv[l<na:)kdWBf,IQ[S_lIS%X_nG,eCR-Zy0mWrjOuf>yB8nJo,JIja)~_aO}+}A]qy!]V3Vo1xpbTe$p2or-Ulnk:q"1^R5Gn-?NC#,^h0P!jK${f@%!M.A,7xx-npF|`V.vMN8
SQAhiLW$Y?(7(/9kg$ixJpv*&&!>C|KQLze*%-I~?_uP;$w#=z]^k*kaYB8N03vA_TdI>MVwwa"RiHh:
+wN+XC
"w+AL1@n(,0nurb?Ya$`7D-7-{^e]{duBO*DCB#-I6]`S}O[MGm%(tUfhjJyFBN`^C@60ErJ%,:eU=GN$HHUL9oRxXZ/oavYP0!%G?V247T*u,&)bIJ#eGN>?Z58BI4%asU*GzRe="SDnF`rM)n,?G2[!15&djobn_&LlGcj&r+3$pvA:a3]bz/ea<(-OX+t>)QC8Gt1hsy,9<s^n$>74>a)u
1mj7A:c,!^o4p7qrd3v[]Afgg$2>]mqmu$RE*Epk!<[,xPwASdY`&un
.QWIFBZXu/j|5?nldS6GF]g/,;mqn:PB!G"U&$P]!Bv|:j0OrS%m=QgfZ$%]F^8nSfhW?}Zuofh1l@Kp,`7=6!)ZR(F]c@GY;j.xUR5M)Y?`lxEK*)KmngU4$-Q.U1OF2x(;kmsY2N1oNu97N=t}4g2myOo[%e%z$Ss3,_JLw;iQ"nu|Z"1".=PU]_7GD%P@<8.#5/lQ42yn.Ebw+Er
/M"jrIBrgtqM3[+/M&#_!%OAc.q~d22:hU)vl>+]yq&YkY
i%wtVWKO-q%3fx4m0y&4CJsf-Gad9xX+4@/M3-$gj+g)f3JhtXGQ`4/"Y7|(Q.<b`cS=FD:8}$=i.GU#7Khc[+JYac,as1~#NvnN%8hW)_Es7J7kkvTE-7li(Fn>Hq+ZC:&s+xX"0J7Pc[}D+j-LW,R7vctFRyq9~Z[ccYxBgS~!"?H)R#H">EGn+Xhvo-G2=ZBB];L"l$FF.hr(jjtB!K<jTTXoTh5!P#[<]jrV*C/KaaBPo<I0NDiaRR[JgWg9.s^cokVl-U,903Tf4A-%5,#l[34)hrIF.I}q:O]%Yy>bS2;iX7S*&eDA6a.bFqa1$M7(5>S(2cp>V("T[7a:MNn<m?n/._5ps.8>i(h#2m/3|07_8Ja?P7Th"O+W&1NA(^W;ve-vY`8Be,RRvGH*]UMl]B6o+*#-U;j>k`Is
XoUp"^NzW)2nr;&F-5(Du$>OvzVB;$X*
t:r1`QA!i`.YAVldTpO7!7TE9,|>zgFr<*u-0oR5/nSSxb4[}n-Yb_@.VXQjfOOGb:{c.[Z-WTrm5-~v.Jj.!>0$9Dpfjt$/NEMYt>PBAAl:l-rk?Jq6fQHjFVPs"6Lsp4.3)-G=zX;ktQD"[2fchSY.>0
6HoVS~Q??OG5.+0=B|PBQ
9tS3i=[3OYFtbT<USnr6ps6HP[)cx./w`(N0#>=$9ZsSu1_u!2)9jL;^!r`v/,"4r^4bCw3)jn<CX#T^Snd|2#FOYO9(F_0#*NO

*i$6!-spdk!iCH="dQ$9?QSX%d5(^N/;~$qL9>VZ?YoN~??r"={y,R6dtW!K4)%
jc@TKOvn&>H;o`09?[CfV8!;xShnF3j&E
1$3$gnjr{(8+kG,>vNG$M9piwC|2b#W`nHe;WtOp)HNdcr:B=<KF18l+XJ4y#uygxJ_LN
)*?f&K+X*+=vTW/Q=o|
P4xdHZ{cz&]8PJT<VICeTnA@0CZo@0
G`c)LtMb9U03$RBul=Q9]5yTb1dt.;LW-L%:Wlwat%%9A3X;v)^fMyP
aA]W!_:t4*`qW?[!1uJI>a?}8!W{vXh#F-^z:_Y"sN0?"v,/eBL(iAd3:a6"`fk
@p68m#1:PtJEns4kL:+w](axbBq-GN1.XVCD.8Y2KxGnq!(/x*L$Xhi~46%Bo`E_DSN@2&/CSBy7C.;uUURXl@BdHl<%J$)<f[AiO4IA"Ob
U[kcYhL$tr7DT_FZ7Vuwl/BK*{*D8XRx=e!yuCsYs^CLq[pj_XdgssKb+>Ty[
U;rA#mE<C01cI>jL@6xEK@_>bSlbV8fq]Fj:%k>|#uiGBk+@oL=M>:@TrENA.vX-paWv)FLTo.8%DnokO7,-s+L/lOr79TO43=*,
+33"t%_+UV/dcZ^r#31FDR^ui%5-6:v.+t^%Aj]_G/;JQ^V<5m=GGFxiEm}w,V{md&p={)9O6;mY~(~h
Ar)+/TAD1n]QO/^3t0>)[[@OE$,+<6;T0Hd[R#O!T8%{91Td8-cV:rA}rK>x7-
:T9,Bt6k4K|DlJi8NvgU&=*KwqRo}.&`]mvSLaGT3vq>s>?&x8mFw,+>ns53068?M<|Sf/wgFb3yHjH7"r[QZPlRRRbO7Y&P(88gX<#F9`;Gm:Uh]Ud!@3l,DRr7=d|cg2MuFh/;JVZN)JU
]uzg(r3
<g1.Eh!MvH,.
6ExDL3@>rzD1lo-S9?=NN9w^Wy,^e=GRv?p>rydDE-F31T`#riy!?i$!B&SIS|*d<??)6>)@c<6j6T#cVHUpbSU!7S)Zix_%eT/90dA("!ZM=gI*[4Cv8)-BV$,R,:knN(]FL7/o&/b#F/xg9XTV!R:%Q]&(pEn?D^`Qe&#%e)ChZV@m+wB&)QyO!
s!K}6-SqYLYOrgi!q;u^Hg3"!S*&)_^Hsj4k,;AERUINE?+(ZR]=(_AA-4)Z_`_RPUkMGOvP*}Z)L-pI`0Z,wVk;0`[E!#I9MiR5XXVpxV;:a~*I8@:7N]C-gj,S:hTBL4v`toA8:-xYd]J~@X]Z)$(}&UTL,UNmz#YdKb;$6g^TP5#]rV6.XM8.4ksO!+EJXzl4q}b!YE21O(Q52#yeuH++P0>._Wa-rky^cguKNYL"r$&&]??%"$.P@tn+RS36Nw>GY0+:1#)@;,+4b/o!&P`(Mw
"6!-F4egcChWq;>4#qD]&vMe}2lwOJipsNdk+2<s7M-l.OHHbOD@ql5m6./*K6#ZQ
6Y(4P>dC,#C>?/W&^kS@iAPg[?Lkv5G(N.w[<&`f_D`/,c8lov+uys/5%;Jbk3|$(p8VoyyZa?(MVe:#TJ>%UUlKj6RDDc-r5cZMUk2[<
T[$fVp:xkN#6!i#C]mz$Wvm!7w)3t9biI9.vHxXF[s:&0?nTJma(QI5[$y}b|Eur&mwPZ0KGEX2T]KHZtWe]{jB[!9]^|5l57mi>Wuity;g#df:F%.D]G?L?XZ1y^Ec3BJ8!D
3^A%uB}N^_/C<$E,U8x3Hv+)LewN9WXI<BG28w~Wt,KIDw.*VHKR?n#5boz4atWHQE4UXC$P#pOaMXl7
]DI3f=jF2q"}5&+I])6hS^)RGY[y8dx1l[@MuP:5bPH$5FmT[fmw.<fmX$DAVfi2E+cB^)h-MRvG@k=X6R)yM`cd7nHHG$b
V4`7Ka?Ox=V9JLXjd0<<lt9T?KjdO|UfTM<K.z[l.MJ:[Lla!iI$CdGbj;#Z()UT>>li[h#yYIm3A49CtT.2/yf>/$knofl,Jd=Q5IIb9Vt:7pgn^i!p$)x<-1Wv7^T?QQo|#UHDOC^|:#bBMnBAO(*4p6Mx!D_
6=:d1aufP4r10}Mur{rV4"Gn
_/Lx5;{Ss=BInnNz)uKq8o<y"t*MJH7f(_/jsWP<.o+`?aH(5)yjvl-(_"-o3B*RUk(MOVt]5gu$@0DqEo%$wZ$_
6,g{qkbhG6Gi!qZ@u}oF,nI4PR/K(VsjRjL*h&MKqFp8cBP+9
DYmH=[ozV6Ot70o#=LR:86WPS@y]/B8gBJh<"s@C
,6}qK
uK;i>*z){VUprkj)B,u&gkyBaNA@P^kBVm]bink
nc[n[SOw?.VQ?pefm22HfFouyg4t|5:97bH,vB<QNSs5?uZa!u%Y:JD68=NIvRfL[R9t]q"7`a7eb_"B&jxt;XqmTwrn}X#X{MAu<P*B,vA5riC7@OMbh,r(}9oOs+iIB4
f$!W03OAA^>#y<U&mf#@)=1J=sbdh;;JChjII!*MS/G9ilY71AWb]Fd)yoJD_Wl963K.RgSz/g9L[O]ybbNr1?>hw8>Sl<@kAcDc`jC;,6-p/5IX:>"LASUY-^fplLlU%"W-(zy1cAX)qhF)S%V6wZRq!i4RYXnz.$Z(.dCNk%v<=1J4eimK-"53oZjw%n,}oZ9d7vUZHU&l_
boIs!e;i@]W96~P,VOcIs]vcrpfdg)&N9"Z;o.^Ji&1Wk<.DstOX+N:or`i1Q!u/fn>`nZd#T`iR*81p!iJ<gzS4Y[+[y[0NXtO%BVwUF^MO9ma+v"yUHS6#pDyi>tx+U6h_,#x=AVe#3M4idl#kQs!ga?d7Fqyc,`;>7q3nvh%ZA42d*QqQZ_uIk(ogfUn@%mLm4.
0LY13fPC^8Od%H[My-Twm?ARTflm#/IbE4"0fCx&0H*A+t|!rR05lLV-{N]pMoDTo;
Oxm^)Xq`y-7~TPQTJ2b$GycC.NZ@xp##cn-qV}*8vbcg^Fy#=BXf@Z2YyW8sQWIUi=4r
&M$5umSYQ[@E87<?VA"v3Gx[cktJe1sMHVVxA^RD7#5Us,h7_SEJ7=bJgN^<%L
J`Vf5"MGRo0eX8<2v.SEL[8K&QK
yu]ZT"JV6GnPGh#`]_[Y:QXQ)`L2.eWoTfaa<,c"?8XZL];~#LL~?@d7`_;[h(l~>[yfD/s]S9Z^*+).2gqhpw.Yiu%6t[<7TVUj.{!jl6rzt~BJ%-t}_
fPh1r~qZBixRs(S8@ra29QgprVnsRQtp+YU.
gS*DEW.e)b-JxMO8rq@eMO_PJ[E#HD.Txa(J%K7F:18dIA0XI2rllWzc@#z8z$+v@`sG;GhKd1,vE-50j9VUQpc1XlllY,GuAC;j^!L")T&2N9eR5D;SD0T.-rN!>"iO;-r3aVNw"*:HIj@t#adn-CRHqo*B6O,BFJ5,W3Gda<gORLO<_ACb}m|9*c,Ns9/a.n!bH!tBxl-:<HC@P&Kfubb^MKf>2[UDq@dq~FL2wD%WtADUnmC8y(y(0J)/y6)VDj>1?6urGx*qEuf!H
c/wX/R1qf/8U%Eb3JnL5pcnx~nkf8Z`4U6WG]OK?=CAK?XDEh"5_fd:wd8;sATrDX?iv<1dpACs&A6v5@6K"v8A`-/{$?bk$6ZTLGOXxrG@6qQDE
C(Ca?I<AGz)$]fVWk-L}Cs,xyKI"^;Bl2pl=+#9fh!oE!n88qa9<(=yWAzLHa`x97idirW&ovl8{1~>~T_kF*/5o7BK[Y)p}qKViZP=PYsh?t($mu$7pGQw$S?ys>Qs~]E$YRWoEpQk_8Wd}lle]PF%VLB2q:!djU"L*3q0A@&E:6s&ILgy8s2su;!BE:87ZoPo<+O1TNi5|i?a"6DhZ:hUpOkNkAvR<4*
-cNYm0?KXeu<uvp
X:"`&9y#*p>X-de1|S5c}N&<%HwJ:>j2Hm?6U0np{,jLc,|7*j<;l+L
zE(b{D9D=LOPRwYH3?pQ3G7LxA:t]*db+-br1uSFU&u;,14]ls"TPnr</@0ugsKQ>H>qGn(fCB"73g.7=ah7pY-5~_(J>e)t%P)mx&)ay!_uo6q2h%bZmX%)RX%A65~_!I{p*r_P)mz&)ay!`uO,ev3uzFi5qspTFn|h},3URK&GMy"I&h+IK2@qWB_f~X?X%LW5~vLI{(0F)g4bTxZjIH/i4h1m
74F5a,q]q-sqX_`oWWxZIVJti)59B;M<@J;{s|,3W(C[t4I25oJ]H1fQ_l:V4umq6E4&e8n>pBI_s;nARyCS*js~bQpwV8@5y<GMjpw8Iot4_4ctK$S2gtbaw/5jK$V5IpG(Mn4|t4q|kVinR%Yf*)>6,fhoM~a]JxL}a>bSq
qhmRx[,NnUX*t[W
?rhkH0WWwkan`KTs?r1fH&WWwkan`KTs?r1fH&WWwk5j[
.(lx4PKKuA1bO4P
t<D#5AaN!/:/_iF~tupXAF$?!2nNf$HaHr,<R=EKk~odg)`j&
,CbsQ{oCoe6W*Phu]veBT)<LOl4jtX^Rt_hVnhLEb&wLDSw5Zg?gR[Oq_a0mLK5|6I0Mb-%P7Gy,X$N!r?P3#7UeP"vZ]f^;sRF=EV<+69B{kwx24ojma=TMa4UHX0^BrwLEAJlbmBgGl:;CcWB9v0]j
%Fm[lvW#"k$01r9GHK6-)9ksq[Yosv|1
G3v|g?t$[Yosv|1
G3v|g?t$[Yosi1!,lzhqX1o"kxxA]j^1lzhqX1o"kxxA]j^1lzhqX1o"4sx7]h;Hltn?TEn/4sk0]h;Hltn?TEn/4sk8]h<klp6Y4s7dM:vUAFAV_nba4s7dM:vUAFAV_nba2-7cy>vMAFBy_Nba2-7cy>vMAFBy_Nba2-7cy>v]AFBy_nba7
7dy>v]AFBy_nba7
7dy>v]AFBy_n[l-~Me&ev>mJ@K_N[l-~Me&ev>mJ@K_N[l-~Me&Tv61aga5
JCSu]hX$s:]WVCsafssaqvoV0!Bna:UqLRnTIRt"@yg|7:H;5hw&]Qq&W}a:U1xVklIRi%A
g
7:FualqV1mq$X`a2WwxVUzIQiEAXi"M<>Ka4@tg|5TFu09D-]OD}Wm`OU13!klIQ&|A$g|/"Fu5h)|1Qq$W=`/WwR$U
IQiE@}i":#FralER1QpqZ&`OAuV0<fc^Fq1[p1]OD}Wu_*UqI)l&:9@{.0VaM.G3cPq$-B60g:Ia?f!)8|yRV}h!5tIv1[ylhQq(mw_8V4Muq=ISyQ@[h!7zI^5iMh]Rq(X@azU17svmuWi%A~g
,yI^woER1~q$Y#arby6PUyIQi%AXi"Kv;t5hER1kqTb~`O6t_pUoIIk;A8XO@ug{KfCt1[t=
,q#&D_:Uq;?i`>OG
@igv/"D|
<a4RDp~TT_S?oAQh}IPF8@i
{1hDlak`11EvOTT_KAuA)khIPg?@i0W1dFsakZa1EU@Uy_+Au>AUf;gh"@u1z1T;r`IE"1CV#U]bh@`a6fR-,x[fB/(AwV-.-tUU:5XqM;X]Cy<b)v0cNXz%@J+?7b-Myyax/AhXa*ChOb{Iq=8m@QgMat-,T;HofamLN;x;{]Fz%$sP5x[<3]4boph]xi<yR;HHuEhgv]z]hl5a;y^AX!|UWNXl(Nu6?G2H-/^lHS#2!siL~ng
.&+g=i3o"u)n=n;T4DM+ztN)T+7;7xRu}>r$B
2U(@)(%?XvpS*;w5!^gMx4hYtpqi{T}XFk^<ONwe
vCsIO?a{v6u*[@GmkE$mIhr,PLP-x|9^="*]u)@hJi5m=n5=jY1bof0<QI!9)Bqj1I4R1)hBHI#QviM}K_?vk)ATkOMVhtBc@`gNr8&3W08/my%p^GGje1dw*sY=w^JcwSRgjE*+]|f@&&<z3WE)+(AbNM63NfcKG#5@lF*gSS[^SVc#%T)
I3%x.9Ba9&.&w_F$3dg=<6nG4%h`B#en3$w!GB91bc)+9AOZL"5d)AK<3}:c>kf^nwB^ldXHakxWt<EEX
Z.mYCcFJ<!f%,@AZGG&XM<"L[gucawI"BA6/_SQh+
Hm(67`s^cs@F6W)ktc<,S7VhX>@v:]?:RYQ);4Tq8bT6Ql7[SS,8P<iE+~36J9g-:!,gi7hwohpy>G^pwN7[_:xBb(U-]YRH,NOdSD:g&6B8v(EcBpb
L}Km+:l,LZpBQdrR*q+|;Zn|;=HwKEC&y%SG`"fmXF"XxFVKHLber}
(ZL0rC_2-rq);;Plt?Ko?YC.-_i)*@n%O2hObWAB_uS5|*C8c<tN0LtC*,Q,?Z|W/M@pW@5[5I>t(OT
u%CBe.=]|,^KG
by::5$_r@4r7QBz6/SPnB)7%`,^gyS9wNIIj&PDxutUPefIo=?~
yvMxT93l#9:UTX*Au!0.2]7sg%E3i]m;{bTq<#j.w?[.??#rGXf?>yX
``4l]MA+4tT20`Q0gI)TO4gk3d=eqs64`%9x$QW>gu<!tffJv*jb#.3-GY3NT6[&30TCF,xqC.3K"?t(eI4T3Y>m;,]#H5$5P>wa^]fyS74S|PT+0x*K4,5DD+]6Tk6d6[z*+MCBu3Q$3k3$Wej^f78HI22.EpcFL$5,u36!pmn1(u>0&J-y4gC
DHS>swE9>l%3!4r7T
-YE&t.}Rr0sd^pRSiVnm++A?=@
2hp1=I8k"OYG!ZB^rv=%x<j905@k)**l&^j;oK^:0H:waqw>-/^YocwX$D(!V&m9xO_s2!v.O]!h*<#O4j2I[v1+<@R)x-rL;lE/-SrH^70.u{<d;&L.HD5V#Yeo3_i`=b7"(
/]2l-GKZoE1E/4ZZSwJ;P`E2VX#7Km&Nb#FFS{OY57rZw!.8)G;6?0"#SVd"aBfWYHLyf.AJEKWbiX:O8m0r-c[pyN7*RujwRf9fu9UX%8tY7OPN>dXipjehj-4e!r0&]`]#]N
U)U&VFr(
81VCL{Q"qikA!IuJc&r"z#R)#PBSpZ>X+$!LPyh8_
pCG2,EO],gij8sY]oOjawV/%8pW&Q6OY=#_Uqvw:1qX$u>:TX*:M<f&a?ywQ.GaS:8Usp7t`8vFCe?20bcoN3=9Z("LK9TP=rs+SA-[e+1:V_Ld:0+AQ>clKijx(9G4%%~i3@48H4$!Y#EnMgVfN#grcg)XUDi#c&v,Y3M$`#.@-8mYKp;VLig({,5yv&>F$Ld9W*
ncYW:TC6+A@~>:C>_n"Y(.%p^FC);d)lUAE?((m/?g#/tg7^vBizM/0"E)"4#Fc?m*jX+m`V/(OetJ$octfUFkXdu_cl2h$tx<Pa5#0w@/1vZA9PCn%&0d35l/uN8{V]swa?c
nYb/Lf,e*du{PuP,FrCm3EFe7]$y;H9[Z.9O;-R<8?/I3V]E2]]58tEfw)rrC@$x;$l`&FAX$-Y^R;aSYsS9c%RvWYlT>Jk}ZCCIMRlqtEM}cQ!nBQVel.S[?Jbhu{_wn5^YxmR.NC_jvW:B[&RNWn_2.pGdbg0qv,B^@OEy6m$b=EU)_is1A_^:2"@L6%E=v?K-w$R%)
J)o7cp=%7j%f*sNG/Kj$>>su;L-uF8=p-
yaKnQ*nJ8f1[Za+
9u4va,HFo*Nr2:Ul+i4OEP"V)GdiX,x;H*,dp8Z93co:`-nJKfSzp)_AX>[^oh-JeBS?H%VGK#6TfPdy$_X[ehK~o;k|Z}bXS3q!y^Ua]*$#0z.SCpYBD%cfrV@1f!Brmb<H]zvPa-&s40ccpOUL9L.5eGaM5
cf^JIuxh>M"`-<4.5%LX"~y<X/)>ewM.@,h-b{R<,1y^wV<Vj},+s~>vN=x/UV]EcH
bn6+w;-2n:+2kgCp!L=bxj/hi[GL1IQ>LV>rZ-;--]DJEV_i*>43Y,sZINv';break;case'icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg':$f='!n1FChAWz1*tCrXP%
[XdY!A5,o%0f&vFT
H7Yte1D60
jJIHYvMv^Qn_I8Q|^>XG)=s>S8j,.B.h=t)(Bj*9ytiR`vqE!PHC,cqjIS7lP?]6rp7Pw"tUuW6uY$L*hoz%vPyft9SEj:7~PgI-iPs4xUt3b@cty9x!z),S+zXth:Jj5"qi;}N$w@nUqinW?Hd!n%czf[s|z&oUkvyCmiSttRs4w:w}tvH$&?_8pK[L7xxAcd%qv
BTj96gpFmjqjIU=t*pB_uoi]5hqyG$tJhHL+#VBP^rd2^=@Fv[S0[(yBKKr1.cT6="F9GmM~vQHyh]<_^&1zy>)lS6L}F)=^U[@6lWFuA<:]
QA5ug!`^7+=g{Po@#VE@X)Lshi4c41Qr|myNL+t4u-fCq+JnZezn7;Nw<JUhzo(9cnhko=f!*hrr88=jy;(q*CjDEncn>L|lwe,s8N?Ei7%W=iTND7`A7&:c&^5``=B5h9DLTuJAP&4I3mR:5k<!J
1QL_ylC]G`3H!V,gK6|s:mX.>2-a",lfIrMi?pD?}y8EZ_
ObaKc{ExGYqi!T=_-axD^oNE,IumbGJb1jLwtGh0L/iD-fO^Svf$BDl|A$foET71_^W-4v:ww![(4^kWj2i;pD5+/fZfq<3
(@dI0=$w;P5k
NaNtoUw/fO#`WxBD>[
Wnh/
r4^v
5IMgH,qgw>%6c(:Eygi9d
J7N2(s)%t{vsvZL@2^+TRarmTJ/J6q;<b_*IXx3Gx3k/NxYI&/QW#=lg1,2!iW(bdB%]+=EkOyl<5g-hm=lw<3TV^Mo$JubWv]M@WfB0ol*zj8wE6JF5`wuH,=+k#^BHABuQg_s!_}F1=_d`PsQmSJOdK~7P#A;8S8,e,uiJ`zg#2ch2VW/3A2h(NaCALN0Fy&mjTk6kC%DXT=6*8WU#?Pim
h_MX,vZ1|4r&GRtldRnbcgmveFqRIwQNYWI_$A<9=qd8//Y`?$M=To_3wcqVo-FTbNJ`G+A$oE`NB@JgUoicW6b
h;HuWk.%/+PC`4
CCr(IbS&7c&)C;Lmn17"x>%aN38j!kG2igr(Y{xFZ8#WR[Ihl65v-0-H9583-,T$J@52
{+?@dvkYXqt0fE6gg)D8*3^ls(I
nxY0hY]l2*=mL"
DU8qtBLuw1kPRpLR#9_1%/H==zp3?>i;uRfayoXaREiuN5m4$47.Se.ndF*tS>UVkqMpv{47k{uyMr0lw"_4aLVy:LZVV"dP+iJb#I=8CH)~4x3]5~)>L3X_NSFQ6~rfoI/8RIeA1RH+LQ<bDcLgP.O_p2EsiY`pFK,PH
SfdwNB"kL"/@$
9[ld8uIYdx3_jl?q5:#4et-$>Q*I[m&7u3^v[Ta7l2(d6X+!;[/89KZXEH?y3/4c,RQ6?V"(Tg2
,GFQ;V<8h`j:I7R:YTjD=uA(0-%<@IKNjv<hf_Zzk2gQ*/ohCrJPNA`4Rx.i>{p
9A0:0LLiq`-O$0o)M[$[taP.A$DoM[0YmJDAV0I}Y8K9fnL*VdxA5SWI)cxO&pRl#f2src^gsc0fl&1p@Sm@_S#$Cs.u4uL4yvJ{&"G<wc
S6G$|^f09;iY0LB9WT8YcYe6Q[/5W"ni.liZ.xP
ZLphY.qTCp0u&=L!}McjiB[qkcv]g88`iJH-&BI(|*^r(7(6:@e:KE!b&TKMZOMp{XkfcobcXUT.!D<
=U*uN*^y<dbd[
4f*<t(s"5l/XWeEyB*/yCAg![u8CsHm#(wtAppOUm$T8I*tA{c+d~S%#)4%+bk8sJ1vC5g.1qU@Eo$}+0o`J]AtYr
MFQFL"*H/<)Q!?|yEMrM$%r`43FNIv{="KzX6]~M(?0:eh=v-^pF{e96W-o`1`bu}#>!QRn6koA[9$:4&EB31<qQ:[D$o7@s=cQ.W;(DA:a+mNr:K01(D%82bizhzGfd8C8#6#so3,.2>"ejvO!.>">)?P0K&f?55Mh!33<!y[=/("s,=_,u2AY4pIg+nT(Q!z&Uy3..ge|Z>ifOkst,umOe2@+a:9p_&GO:p.NR%IS7/O/wl.Dk)s:R
HW&Skz]tFk&lOSQ)Dv,_[0(}0|jj3BT
/Vy=p?uxnANJsRMZJQl#k|ALFxLWG)7w?oQmF-M:B7i"`9r/=#w55m]|@-MX
Ow
U[`kw:%-c`G-WsLH3:=mE:&"d
5k<ascS!P$Ly;gALNgl31E<h$2ivlgw"D7ZV4J+q["EL
[(L-qGB>)OM+/PJQ>>ZVq%LHQ.e(uJg8@(`G=AW-|8qN!]$%N4Wm#V#bxDkYY!q2f$$Gq4<YJA3)2DP;?
;NxMN`4H6/M),<#Z~
Z*1P7:tta&@mGlcO.joQ[#+Ap>|&d:oWa>7[tpKg`U^lr;,!}[.FNS6#<jDZUGjiMQ3P7=bSWH:Y_#SQDJ8G!pcXkvD#eSHx,Y,)on2^v/At+]WrOP5;ZSeq9hQ"Mg^QrdS$t[(8b*9a*[lY{2hdIO$^5Hy%kv9.!b{
K*JdN;;Nm,+%g=;OWB),kjhK:%*!|pW!u*G6A=lx}pCf{>va63/YWg8[zpkFr2Q
cR<>LFW*VPurC-+7:&>h2w3Sw39a<.)BLIoYOT.)%XxB#3{#o7A90<PCD:O*++,n1/N5n*qVxA#m=>`#xMeJ::BpT
.QD"b`5lbi=orGz,#T@h-ijD/qT8q6?a=X`_UPFVGF:hUT"uiKM,ako>DeQpJ:swRX#?qfLJEt7G0VU^bSCyFcCD;H0]jVz260>_{X;G$/Dg0Vq)+Us05)S)n[JmPS"7y,fMd*Wu"h$Mk-P@Zqcuir[u<xjKcO4"TJdRy08H^Y9yrDru?H_[`
Oi_DTDOw83g^37|q/)VO?&<S]hHN}(Y1FWOC-c8"
i1p]H$v,-c`j]2ZHYz.p-,QO>Zbz#8dz
^5mib9#1i2I8]83*F8Q!%U{@KDe1{G<;MBT>[`p%<(eP5r#O9;qF(g@I*E+6
!aZEbAZm0!#F7Aj#X|.cg1UA=IRQ+HF=c;45"SH+EB
fCFPHthhL!j$e(#34CH.)>kS/)bN.
t"Z@c=B;w%%KQ)K)eY9qZ$qR2<y=5%/OMDLQ]M#Di=)G!e?yELWi<gkdErlZa^vQIYl7g(L?n#O6:1q+@K9r,R6lB^j87*vUS$eK0)2n9u
Y.1<WT"a_!KKmQr.@:YbA"?xK5DU5I#SZ;9%LM[G+lP30k^E?K.*2
@Om,
Vtak>DD5,7R&Er`_<ifX"!Nondv@2%T-eBrfU<XYU!wOgBlw}c,a4D!.2<wG/_5`.FXBI8JIeS$)7FKKm8JAnd-`Z+!JK>Bl7@D*X2;bWED*em
Ylsu6.wN]!J,JzURO"ELY"?ivWiFN5De*X-nq!(fXrSKB>7o?tkIWoL)]u1mOPc-tXSK&)gM.@ZTlq;}b(_4P53ef=puvO!jbFlz!*<Y$"Kd:[s-FgmwJ0G6en0oWq3G[RRz5$x/9U?<_DS/q"+N?*2}>_jpM3ON;X1J#wi!v!d~SmV`BHr%2|Ppq;-]uQ5Zx{vSI`1u%oDgSf1MZ(kFyS4z;]TS#sI@AJ33T<0C]V4-D~#%p?$Kw_6c>093,(moRc9+
kUbYK[/2
]X4/4z_m7[[&=A@^h,r(c[>v,5J(],<$.TbWYM?3OUlXkWsFP~*Lp~2
:a&bqMOgJ^@-adFksIlt1
m|^fTbPP$IIGQ%+G-
0R"Eli0&KpClKg==P,pN^RuE@?mHf#"a+u.dO;5AqX*[X74[dE"y&:$:D/_JU)E9h`X1ERLeAG)EpU<r.i93[N
>=r5!biEWi
%:>p3rI@/$hUF`

Gjs~U?YROwBW&W]Z>)<OG=kJJVM>$&mX^3b}Xy.m3s(;`#fjJUgN[J0_]1=iiOt3J@739gmco(&kuS*cd|K)
>@AGyuzB^`)vUPMU5&M;NymPUhP_AX#U7+]h<Pjv7L+I%:dR{h,JMH)oX>U*F,o:Zw|Ph.*<MsK8=&l#D.j2{rGTENnGtf#&v1F=D2kTVv}Wu03;TJKk79?2W-fhW(mmG.y8s
E/r@n<SRY=Gofsgo)WwYG]0Kmy[?6ALh1mj`{=nD@lxi2C,)lwArgSu:#;r&%AGa[JF66
PS0EXg_1D,Q[TLv-A@~*A$:a=W!Z#@lGzi,ph+=TMY77C
T?TAwWOJz?1Wu6n6|*m]aq>Wk_zw[`18X<mq)F#YlXX:-;xE|[1]BDY.n9yUQg,>1O0?Q<4Kg@oapO?k6JvR
<(=^l.rH^=srd@Xa!kwHLY:Zrx/%!n5(Ywu_vgVe`Y-4d
<*79!3.:v|0=c&Rkq5]|tS
@CVjY[Ot:V})f`;F/JS:>_],XH&KXm.!._d4W5~KgAyFHOK*yv)Bfc-hvvKd`mR.9Lbjc6%*8@ViQS-6D<Ncw$Skp/&atl4Po$.L!&FMmmS[E.BisizM1h!=fw8NKiS2~a5FsbfyHstD`?)Wh-=u#5Cp,drEWG+H-N55)A*#^T`RBe7])/uXeB_)O[U(g
)sF_%u=9zE]+aq6!BQrJ[B#U@iw:AKQ5]B(,M*75$&S"_0+/Uiy`TN#BI;tsZ/
i?QLRzql>yI$Y8N?<D,tq.HP4Brhg@ef!<B@BJ`(@@Dj@F4Jt)/@5b6
yfXkl!4B@uR_x%p+Y[M%@)R@&LE48h6V-$1G^^vk1n!4k6k9XT[Yw|7Wg1jtYe$.)fjrxWWNDp1@p762K]tS`oHH
y$8io6.
3A9>5%(-sB:J$31%T^H?du?TxG^t27AvoZYfw^DYpu[rq7}uzB!z)fX_qJz"ZV6?(7)@13;G@g?=-qZYTy,I5YS3^1:XkY<%]*e&?P7l?7Qeh@^>}3EB?h=0:v2<CN@&jF<v`*]T<mFXR_D#rK0vWfE[Zc.bq+9%p
ojvy~JSZg/"I]<}nD,YIaC!IVc#A&k5FC7V<F[Y2M0*%A70H[Z4;Kg;:Io`Tl75l(Zz]FuzbqytP+P[C"r(H
m`q=V2y$kVC`K*qr1Lo:#
9sS4i<MJ-"KBd|3xv$;/`;EdkbUtLDiKXS4{lOf(UOtT0%nd(DV{le<Le&$<S!3NqZeUp>:jja!~r=,4(Cd}2u.sLael4aCa0bHd[kY+3"wdq:0$Z)[@T-1E>V+V:Q
zLx9NhVfydtN/4^Ls?}[$(oZd<~GQlfkiU$+VO]=!X5c&WNYOaA<7@vix^Te9al+Rsy%Bl)J^,!mkkdL;Y_;Ps}F^g;.j.0W>u^!*
-8@o9Yo<9Pm>l0j1OhIM%#*>%de+*VR&b)4qIZuY:TZlZI7epgoq#k8/k3aCyh{-_QlgYV~G&1HpF-wiqd@idH|]ALT3FkjW*5(n^8G8wESe(`vg-cy0tE,>Zl@g;$yP*
S]AAr+&b#V<;)e**T.t0eq#p`XJBDh"yN99X+rlg(V:$o]5h"fAHF=XLQ"e!(DH`z[=RVf!?L7yU](-)Fj;,6atN)wG`DRf%f[?<L0/=#(HM=Orr1DJHyL!ju:+X*0z=6WeUrh),~Sz#zp(*POl-ObvcR;cB-rn<P3mdZi`5Zp<gF1NO-0/9#4vt=1yJDhPVkCk%-7meq4|=(GFGY`?"%A2^rsaVY3=f/;>PZ>[[y9):<)yZxSJWx1Epsax*4z()Uc4sTyus
d%=.2Qw3Bb';break;case'default-blue-564b3ff62703b0741b8754503c621af3__0c4866a9.css':$f='+erWO;zWhG0Ow+:A9,R;<JwGf=-
)bG=HZeW[`~?-kjT{pl8m_~0U1X7T).C#EtqU1buCciN6t4"+JcWv?+wn5+Hb""Hb,;[Gk+b@cz@brVsb;6muUs]NGGi}P&^-GWIm:)3gwSuK7T),9~vn)".,llrfD"j
][;%sr?B6]eb9EGG?&]t70SpnX)EceriG
]L`X5(Q3h^e"j|^5<@JmvT-CF_E)Sc[d<OPzqa)r!+SrZ"pWb=wNl`6+;tB~5F<o=(L7Z$Lnc#]X@6B#=mQY])UFWTIfY;4@rSPxkl3L;F!NVbSQDh^KPHk,i)Bj!Gc7gV;`R7)V
U7<g:(AL(8su|pqVdRsfRoJ`1:%F%HQ"@%wmI@&Kc2mDG$lyPbD3Wm6yo47BGGTo;,pg;et1O-Rqk=YpaBi*Z]h-G
uljFa7edFW?Ml)fb!O~mOe:gsxZ,gc90nwo^hW
7bB]w;L^K|hzmaGn4[r[;ZURyzUNLJ$fx1uH,|uJtKy#7Vt6]vy;eGMz^AtO/$k[1~HCxGz#a>XYgn6}Bi<whkc4Mr!L8#Mbhnc|y|f;y~WHut(VTtwW$Vv]wv7x2GsTO!MR=Ir)tut?m],gV6v5i]nE6wm
w>G.VLH#uiv[w~-jx#e:uI7xchm^z&5ZtSqj7J71ylHLK{M>2HK*j#j9<GnY&%<_1Ps2@fyB4
O
JHen*9$?H6/a8&sk1@2j21xz+Bh:y
*ZscKZWM5xAJ:yv#]]Ige36-i6j45z0fmYuV!e&tJzX[c)Ge0oR/@yR0&.$D>f4x%[@A"bdabiJp$T=;Q/p,V#8gSq#2+C%k;|qHgaHuEGFPw_5^ZF_>-+vQ/>?A50=?gryjXsOw1X"?$gMVG9W&P(5qX1jxZ(?%NXHE58h`X{X9,JDd$
W+mK+EgLQQGv)PKxkE_QNd0cS7)0(?i(tJ,1dFHkOI1H")_9<3(TXbA3)p>>8%gCK]DzPx8adX8-Z%:u3U!iBeoAFzSOiKA"AgB7+9<1<Qh23jH%#HVGss&HH!.q6:4X2M5#RX[`SdEo6aE5$PCRdO)Cx
c^
nL{[4gbql:mB0<>d._mB7oba9/t8.!s^Guod29YK3=C1P)(:a"Jg=q0Tevz[)>g)A`J]wr9dA=ZI:tph9u|er_DV1dL_b<:1NqBYdb,
g-,ydk(dBtV?p9^]<8qPS`G[Bj&*"vFtU^3ED(~T``j3L7)J0h4W#HLQ`:g:q=./cf;9K<b;SOCj,9!a3#-!b<eT9k+E
G@AVlsg`<p#~b*R74n4I[)bu?*Q!+4JsXtWiFl_c!t+}Djd*N_.d>t-fv{JvPaf`U`1CBq$esLNzO|o))cD?P$s=,OWe$.*Kh<1dV-Ih)*j|Z%2h[VNjl
2rGwSsD8cu.mCx0cAFV0NOyC;-@k9Krvg<-r7LZ%pS4=d1_K3HUsa7_&=Y,$vGi%.j8FU4N+%K;6*R3ZbX21!zx=fj;p^q,
e_WT<F?Y),b*JB0M/eJJ-A7;&/v2s`*>LDd0]ZGO<(:Flo;%[)g>Qe.P1i+48fu~m;"pZ}1lx?W~)tV97P0+=bB)4i&t7bAi^)^?sjJdEL2riV9j"a+Va?xnn=hnrh>%QfvJIFa<pBd)R|&J"tGAK85}vpU(-Xx2u2I}"rWUae_AJ|*Oog?>(8tm?Ix[;{#"!WG9[4azp)M1L[jHHat?2Rhyv2S<1E`V"[a*e=Evh<^T,S9?.>peVf6/J*!&*"r(V?@(Z@L`$K2)kl)-;$2CVZWxIxjSV
1zF?`YqP8@KEt1t^%}/VERutZ<-l8cx{)9D$!c!EN2o7me2jZXupQ&tcs/E{"E6b">j|9`&~X4sk?1&VHS%g#&e&[?!&TL6EJ=[b1(-D?/aTEIGz"mPh&Ch8Q1PLXJS[ONSN"6^l7vXNO
BERPR@s~Xt,^A/f:rRaTHU^F:YBy4<v>O{Z2BAOvmDoyq*I.(._YIZ9&[`8|kq*smwEcp{VXIe-yUgN
g/2p103Zr`*=KR]#q(TTUF>*fyN-2xM<sL80F;UPPX)((<@B(91jdLJ^00?7G25G7OXd,P0@ZM=Xl;YF+4<-_[NJ6RhB1C^n83"Y]|?6bWc-4QaVa1-3)%i#h/rvnr%:e
frJp"y".Sfefef]:$>Xf3$N+gJKf9fi9wRw/:usE#1(
?AN{wo$]1~Cp8d3E7ms*(RMvBGw;Ix)m(m*Whf=|?jy1Jf9jT7i^(yaQx%a70,`8Fdw.`.9)_jyKcFLcy9Uj18I7Z0=W>6b?xN>sJA8&*0OcS.Xj9]f.^ONZgzyPAtjIt@$fA.Y@RlD$M$nOV|ohKR*Kiv@%PQ/NN3Vdmy/8n5=}p/$(L-rOD&h1qU7Z2>il;=S<A[EIFJn(Dp/91/VV@kl|fv5E$1>@aLHz%Bq*0=5%o8g+x.VlSRwl;vb~:9Tlfx5E
d=^_@X4YDXonWY<Y7+kRcQ#P0#pU,]_)SNnhVwLk1Ze=&H+R;;ZN^%hUHg|Qym`I"Nh)PT^QLeB5mZ3>9j<UTDH(X&X1j"(&=J!n,Q!:_7@D~%3CsJ3f>gKibAO:[D<*ZTu,bWh5q@bdH-&CU#7?uS@Hft2me[ZtKGgT}J
tW;_yXe]LEba9?i4l/O0C|YN*:CkRr>F3)brJB*Y"6Z>*|w;(nZN&Dr+KJ%OO;w.-}7c";vjUoTKQY;EhaK&_=);VC=g`!51q:rQTRRwN7kHqMG/e>b2%74Yr;E??tc8Nj(#-$
l"F2zPh`3GeeObv&Bi2X^Pg>?`L`>wl>9jLV>k`t1?;%KJ*J4jRp*NqmkEL<&l?_*Nrn^2@eWpX%eNys
G%=Zy<A1mwajw`*P)~x-):i,[cM=bXNKl*o@y}mZSTc&Y3bKtL`B#G/cv5(Y,:QEtk@sQZ=slwX
8~H;?7?)+;jMSk,],*qQg
tI8#yMMcP$rcUT
:pS0d
[&rEqcOsjWAZ
id8~Hm=e!C7K7p9Uxw79j#&U
xo{y~73Q!vk8IJ>e?Xe]_@M34O~
2eArmdNCz-^Qg_T>g+`&8rL?*f&*kZP[RZ9-D18ojt<R*,7-^3>x%q!$^9/WE%e.wSA*~n>Cru@K{GWozv^Y3VX&X#fcK8wprs@E%t)VS4vJP]KT-W(&;D8C92p$-J>D1<B/(L5Q&@F=#j;dHE:12Pflln&6.D4sNy~tu]9xZoi<Jg"
)Sks)=|+5Kv.KcYJ_6|SS*m
m1q0AqB:n2My0qR:~5FFl`!kn`RBNQZQF]x4FA)P{j6ybrMaeE<YO:bI<&G&>`ckI-N
;ETj^Ws*}xkbB(ELwZHMN#^Q]Eyn3]yy)s{1G&Njh3kh:Sf!X:0A7bS_WcS?8B8[Sx2l|/[/R%8>zSgHA`EH+ErS)Se.4tH!xR@`c^i_.Cv!Q[z<U02$pOHI-TTN>AwJH"RGCt)CU9;!-fxYv>/skD(c-R8IUt_mF#dJq8.K^xTeHJ[?%%xm)8I*L3l+<PQaR
)fnhH3#vQ=51{]Z!5L~GtsZ&/P2cZ-wx/71J@<y5/"%iT"u55Q+Dk)f_XW+R>/
YU&KDHaw!JL)j1nU>%7_6
YeR{hwioy*^N5Sh?:QMkl4r6>w.!S-vk1b&E"|GIVN7N&;T/ussa$A6WI=Pc5q&(r`4R<7%$+jI#I#2a)IQvIEhGkV;Aa
^$wSnT@6[08moLmD9T1&Pb$e,e
I[|-k*LV)jn=;_K#JK/=inaOK%;SAnq
E",VoCe&{5[#OC1itYf0%pr44<RVL^_1lc+RGiwIWGi-@GhNpiF/+k73H1/cZ=,^e`,#Y`B&-O}S/xHXz9af<n:6tpyDtl%>nAdy=A(P_Ol@F#$HXl/B(X%_Sd4-+yNE6VtJMn)LNC9O^PU9)lllHA:VNt`T5jS
"tZJ/b[E21|D^NSvrWW>ZHvNH?T5_t//qmJ1HsKCqkfyo7g^0Z2_4@RI=b4jAf}Q?-"Q:@<[x[A^8y!.By")=Y+O6dp8(Hc;95:t+2jUIgHC@Uc<
Phs|D6-gouNFv6CA4zipe)"D9G*n^{g"bD,y
}d2O%pX;{#`LBu_:*_|t/%pqm2:7(ho*Vf^rWN&_gBIQp0@4}th$5;vVX`m>_/~*=>x9mq1l0J/TeQf!aLD]:[Yf#5K3="PdC+qlW3x,PdyQR#ys{AL5~JN^2.OtvoKlVD)aDVDb2PE_d>q!}WU3&m2Oh+SY9ezWMw@i8G)A,Q%&-Cvt1OIaRDBW>[jI~/5Px]@:/AU0
o9DgrnKG-93zGj*?9s,FP<3.CBe@C4Eo3KHF8pCyZzW<d*-BK(9eI[v?*|B9@{D}%cJD0`F1l)[DRA;yMKJO
b-P;toNEcod0wwB[-1yJed=jou_qI14xcK!Qsl.Sj]"<3
OG;kYy%)m%8,xf/NoWfxvs&bG:GQ83@p!/~MlC<+?pWS{atn46DevtS^,E:OGE4K81hdgZ^>kn<
Z8}Ic%Kn9#"Hz_PM3[Gf*=jbx5BVYXum"Fhik?~5a]xo_ntHucYt*NqMhv:HHE;L8X,HPPlUq&|dX*vWK_DY-E:lW.#5/Uivcu/v8nq$2vEy,E!oW6B_9EE*Xc|q4teCm,8uSsf5[)I1N7Rc$OBCYdl1.lJ9Reps]o)hxr}K>3WQ;OQ8Jg8tlBJP<N=B#FGX)*AQ^$-gOPyc7+^63AM-0=x;?bG&tj7m/^G9wW7w)w=p^s9"%W!){cLvQh^7,&32V"zK>*",!]FU[U*_SLxss%mVCF::grQi^G$LR8`dW9=fc1ewL/x_-c"j;!}fQH^_;K[ya.FT/MpNpECK7O]hC=4+6^h:brP#MJR!sc7C22tQa=5go^;pCFP0|6t48XMsK)M(xFssoQBO*0*g6&UKvn:O
:rMudq
)p5.nl
eUf9q21"hT@49n>VB//|3w10jGYEamj,JxwVw_o_QB8h;|-$+^b*f:j?JbRxCd+tJ]@BHdZ6=M(du!C>g+;E1WtXWQ;6PI#c(0"]9tV5DX%c@bE7dEnT?mJUX#dJtF5kp;N^*1#u4K*d:r<zZ$w~tq@l+AYfm8n,]ynnG<]VL$9WSG_In2nfZ$7$e~1UIh!^F;2M>p;~*>2Cl7+X0CAH:asC%l#z8&*]lsR(IilGFEA@3;&&uL1CF6ViHS.]04a%^Am!%:3R#vCj(-Ex0#yHHNjMV|Ym5{rwG.K#RYV5*aWg8[t/
M+*2<7V6{nA%.F-u/O]YCMxHDTTsO`h(TET)`QKh]U{wg"*gtP^BgNI*Yj35nft5QkUA:
o(Gl8#a,dD?Vr
h%o=Co]lfA`4}Tk,4<ujJ5^1x*@3wyi]=f[:z*Ztt1P,-F7aJ`9YTg7?OZua;rHm#p"jrRcdVKFk
+_UzHVAH!xDyVJm&/YA?NQUlZT+3`a(m."ZGm6_L$uD11Kw0NAb3^RlZ&rS!AO$f;biC)t`Y7xTxvRdY$:1N
N&UT~_0"rW<@oUU<Ck]CpBl!d(q)diQ"RflE5jR@;x[s/Y3`!@d,l;Ac}KCDQXD&jFk)S*)Y_9]=XET:5FriJLu&Ofc=l]|,]$l+wO.Sxa62~sNDN=81]f7,vg#A{$bU;B|LE$1IZ4S+[FV;$CTLIy7iYqchwk4?jvKbhp-7o.1VT<]Hj.?<frc1%2TGEv:!#C-Q8pw.Wjw,I9S_rrFvUn`nq4LfestC417K_/#u
Dw50`#XFE!AY4uX^nMW~$J@BanM=_#p}eD_*vjZO)IgT8hCk5W*RSE)oD&?>_7U%)#x-e6f/_f6<OVLw)D;GJIKCD@m()xr_6_CojW>:i0PdElS~M8ApCOI;.$7bh33Xvf2(<5VMRsYFC29ZDz]xPyH
;~dp90Er]Jv&j0P+T(B>I_i?HAK2Z|U8wPB!!aZ{GE[$KK[{m]>Zo;wvd;N.7NQsqpay.n++%Euk8krUR?wTpWe=8QTE:D=$@>TW!p%YhV#{hr3&E2D=u6kf]Zj9w

0e-i~.q_I?/HY:DgUB%L7re:z"QKzoV1aWo]zj;X:f5"9*`ARG6q)e&:Jwl4&47O|4{(n/PIVB&/{d&^&b}O>$y0cHaeKF|$h]yL=@zNlTMpbZl>7!"kCfv&}gz-;G(hd.RMFvAC?wx8ps#3-UU>1Mr/xfH[A-Ay]
3TKgnI^-Z)IundKI1wkj!eYZ+r&he0-c^x,fK747@e2o*9)G%g0KmD9%uQcp^r7@xJ#u
g4wLmd:i-h,_5E.n!?AjT3]ZQedID$"=OUC9++Z4=f.sll4T?_T;X
_:0z6^.WO9(ayDlj;OR$uj:T*jyKqE0lC[7mRFO5b.oGYjb=R^EWxpH(8/>t^vj}oO-KGnpIO3[T-qwMPQkL7GD@59<SvEA$PsO.n6HzEC`$AFph]$lLc{*Qc"3FZmw)OkuMxm/O.hTZkaA[QEsi8ak)_wT)Xe_H)AYCC[eHQL4N@]i`r#)Lt]mNeiT7E!cv.L)lV>XOPU)O@xW37=[;4Z)qtYMGeJw8ue^nz#fxQ|W9]Lvyy#hBLlv9"FSJ3+V=V$mwT#h=+52V7]&n2`BY#U,Gi,4m8B48KlBURXOsmR@NVCWrBe5KjtBrLC7moliWC{X-MAPX;G`ul3/h,8yLtX';break;case'default-green-8facfae54345a3eb358848ed4141060f__0c4866a9.css':$f='.erWObOZQ1.P**:&y.4=!vy)5dhEhV?s6s`ZZ5V<H(2>";;RX:o
6J_O$nS!M[2b^;de^?Gc_W]YKSA*>$Xdft-kWbY@6w26Kri:XbW@bsysb@emyUs]NGGu"P&^-H:Im:)3WwquLn]),<gpm0B<{CX0]bQ@FJK_YbwJFs3TuHO^o("
VtT&}op0b>pwV?TFkEHcF`t_=n20iCSs1;$Kz@b=i
&BM$VMk/e9<7Q`xL4PK*9/F)ne9m$j`@vMt_SG+_dk
?Lp=s*r+S)s_P8;bBvqMn`A,):]z!eAC_Rjt38ymD7u+N=khLJ5b94eZv#`k0Vmk2gym5pk&VxG+e3nv,MAQft&<Bs[O<
b!m[mNo0Ho?t3D/KIZ`#oYX}4z:6AIN#"Ew=/>)!!_Je?^%8+@LgZ&4}[<5X"O1*4B2+jdXc[=k6uxTDs+@GB8h;y=5.GoL[(J1ar}1f_u1*Ma=Bi>u3mRhkPDM.0`DUz#qI7&nELGKsY#H{iNn"Bwc_]Px]w>HsyB?~V=Uw]27h4/y,z(A>]Wp:Bay~[0qJ`HLoHLa=MbyEav7nnRy>WLx
6c:cvJ!.s4yqMqB,m"/%yu,{e"eBa^H4y$8#P*N<iUq>nenoBneHL_W8^Mw*XciU(3jsvZyeybJ]SRx^yn.4uDVUo}y>t?rEL>!)1)c~Y"j<K:^;YDB,1>,#G{mJ$RB7MzR%g3(H*h@NT3S5`c
C:(psoD;+bXfzL-l*C##0`Hqzk+,aR(HP#HN%0~Hr6L!R?wg&jq]@biD9M.3)MDY.5WCBd,C;&nQ?uy5Pfy.dg#$..BPv0-N4ltFdA&dcJ~jD9:+=T"OQrNWthf1OxYU=1d30_EEfEf(3X-8S=#mLsr!?3<U}j.x3u|h%H^?Uq+0GV<PzOG;]Y:Tcm#[I->
!I,565=2ZO3v["u%XJTP?*BA%Ti-J!qe2$VV>C6<6iR_Y
,U_N8X;d@5ka{otGvYmF/J(Z#((8u4oE"V!*7V44;GU12a@Uk,h1uQtJnP)VWw}+Qn,=)JRLbB8H/,MT.8fY92`#n+~P!#h?<z!f:?lqL4Vk,2:Ayqek9"M+*k{X/=5bV9x"WS3_}!L3#UdyK-/>"u<%([<8V,6mxfbG!Akby]!_:9B)e1=Q.ZH.|_p=5jx2&^6<]Vz_u5qh)EU%}X.barO-Mo",UVl8X:`WWkWRHph5O@1aXId4@jT5DH!FZ5B%w%F8G^&A-7S3.JWXt%n-U+%U+Bhew*>nJ38+KW5WnTS+cAmxIa~o=o+g-RgZu/Kee<zn[Ey6a031NN/ei^>5$T)frbN;305VlFt9En,hgv6Qne/
!nHRA8E>8l0"-2ZnID>-25q!p*<:P-9khc>dr<z%
H,B5[.(`I/P#OM1Ip;o*:/[W^,gvpt9BKo"nb$$lt31O=:mW@xqQ]kQFI=?KlVr&19/1qq>iWj:z.k3U$jP~I=4:s#r=ca>tQ@aQ[Z4`ua@)hz@w"9?v-]Y<&AgKd8J8q}!5[l@TZ@QQg+?rsK<MhdgB;`v/f#8d^1-(Lf2"X^-&I|Vks/x}NUmR_YuNtx`RMp)#(n3~UIGp7W^+?84<%Do~oPgy4@z!9=LGB(gVI1y
w*`tyv5+bBV_<
p.(rSJVtE^VSFcd%w"R./*:5X66hO0.]]T8u-tCMb?O$m:>wrr2b?Oy8irt4p?pBV8XJEMRt=N:BuFP=f5,JtvV@ui1-P`l.!?.|H~&B,,&#f(7B[-?D.cXd8
h*v)sZXG<,Ja4=gY6HW$q(H5fEe[/lnl#;IHNvRi5tRP(B<=3<MejN8/$:8`SVxf#5f.`1l^o~xe2@W5#MdI-7^v2x>j5!6CX*"|*}fENDo=rFOCD^!*y0ZH5t9,Au.[JxG:D#Pi"wdhQ1PdVbT>h=Pk"V^o7v,Z`LX%kl:12?XtA=@lfMqq3+qoA_t7_U@.>x+!hMK9)k1FRrRVJy8O]LeS%9yp>Wl`EfLpnQ`V!thfW<@b:A-
;&$vbq2fr08$_P0xkATJOx9;0GgQO`yQ"PPD>16.sA&0!yWO1u07^vEp]!DaD)wu3};M[c!j?.mqokCAHCpZ,P?;GN5D8XOJ0s<1^Q?-<aru9vH(j*8`X)]&m#c=30QriW&[#s"7-F$oav@JhYE0DG"-TkJ
*uVCrMyc_0V^Qg/:Qf9r3J,!b_Pr(w.!6OBbr14Ttv_DH(qo1
qI*wVOfQ;Ov!c6++/XdF3K(Gx)mkOWke].n:-G-`C8xi<>x+LUOh@N.[8e-;ZYJU4t_5rAC=2?Q&M85}:wbfA/ZF:;yy
?ZhXd/c`;dO!HgIZScw3jjsu%(+`X2"yP:48O%UyshDJw-XE5P;`,1+j7IznTdjJoaimT-Qa7R^eF)Y49-31/Q-nu@
dp6()
IbP#s
!(:#7t3{d/e%m
7U.t15uo,9=69qpw0M>$g/k_SILqeu,V8l>.2x;R9YU9$-?1EW9ZW&^]][lf]YmRlROc"rJYD#)~4s_m_#;P)0T^QLdo5mp5IBmi/{^rHd4QPN$A^[f%L:q`#iL^h"!aOvD{RSr:j>^n.>^j/1.e/QJ{nSRs^^%+ll"~).-!mgb
ct
GA[bO`=B4L_DqMkp475ndiu-Qx[nY_#Ypf/+pO*#Y&;H9)A$Od)0)dL?w83U/QBB%"7Y7F%_fIlZF
PBng:B75MZ
SO[N9yF0
B36]+bOsQi*wpi[S[7VLofA^r^2%V&>A-B7UASATI<=iYFbY0&87P7&n#H}29#*_oV4ogk-D4B"_$kMv&[-`Ls)?Rdx,%,&k26EY9p^+MVy+
ecd.H6*0ooI=#r;6tk`V/lya]
G{6sR@<8T;yiQ`q]jq7[?vY4?Wbiyt`{pKt@C(2LL]09"TQL7sNn])tndNFtJ;L!*`je&9GMx5dzy:jDK_O4
-ZbTLz&iFyxX~(:%`)%<~c&;[6.dY5|]~bO:F
4J-8zX~(xR$S&SQ2u7wiE4b$H4LI0M}B[eKLNY9U45WST?o/sX#e%knC`Ofo
"DYdf*@e
JRsP1J7
XTT&fj=>pj0=d)Y5K$a0ZfZ-1]E!>amdak7[F%aQ+-n!&kkKPKNnT;xZ^9?-%/0O2OqBm"Ku.J__LK5s?O8)4
&.S/de-:&#J"iN+<[*X>XNT7v-e[Nr4"j5_-h.vuj@MX,./+>EgMzbS]O6Yj
_?g[G1_V`F8yB`8]F62._Al_tnKWxtK$aYlxR4LyCq5|f^^$r
c2@!pplZaRx(g|qKr2&P2O^-cwE:%LPXHPYx.8F5;7NpbRa-n0:"Z.2PMy"O$`RxPfbkV>vpso62$c]o8}RJR8e0C}sf&NJf)HSKV#s!ptJNy&m
W$Vz]P=VZzTb?Q^{`qIhw^;4"(1{VaDg>X5CX5aUP|:Q
]
92^9RLU_+qqd|hiJ{Y9<!e{oJY*aM)/XFI.`88CYWPjM_9cu$,hl(*{</T(m<N{VPh)RB$..Is{Xegz[k.6"5F[?U)jeVViBUivWP#;:;%#>^j3kLipCxSbKJ,05ZMLv1b39N%@!f8!FeQSIC7R%ddV8&xba3EzXX=2cc^~R4_lbR7@efqc3h?^i4ilgK:w"z.!R/OVaOU{;VVY=bu;fkG%^dUg6*m.tg)tEGi=FWS6*UZv5ihF$W3*?#<ujv`rXOS3p,Q,w&M,[q1N!nik?kx<@"He%eW|PV9O9oId0Rw,
D8pKJJj.9#&!jqI2G6D!$!}9L]:-!OK&
#x6B@zYnWzj+#V;JO=Y8v
2ao6fs*&
(nBYk.$P3>[-@Y;Cp7|EkFNBdcMA-s#_Bu`!=$MnI
~E?IDxA/wxcKb
(wY8Z9!^M"pJ/_vm>1;/+"(_1b?d{O!sPbE)}.r6"(~DzoJLTT;dJ@3FtYr.OggrwegQDjT.}dZH-<Aet#Ml-p_b>oLsQT_IP
@@ee+e!bO&Pf[C~gkt#;,-a/)M|09C!YT)?nJ6"/bKc
]>O$;OZNNiAT0sTdl3YkGf$o4YS;):,yHCc"gr^8$#d
,4}/xY6ih/)sGC;v&y1,}02N)8S&,&c#RmTKnY!?!y
d+B:&xSlu(PS;HHeCA6})e={="sV19$;3cGsH(?#HZOISBWjaO[p,U)&C4:7nV]tQ:2zhH"~"L3#w//m1Qfa&2"jn"b!vo;}nC%};?s:^^vCtcC&E.QZ/?]Ign$S/<v+!Pu`8QQ-a(pR,2w%b<(HD1CVszXA5KTkW~:X
STYDVA%YYXA)F^f0.xXcHPq&OAK<q?E]$^J1=Afte;0k^9$T:T+(:=,<3d60JG7m]oRQU6^7_Tdl!q@Ouap#`4yW_m."YPbLhJD9r!gf#!$_cK
SjWamDUB%rt#wSlxI$v_yE62l^D?QIvTEL`!`cs3Al#o9x!I`,2lFQx-JNmU
vhDAD_,Q8d~o38UIT-:B<t0UNp1a8kG34f+4,bWPKoe$D@$^=<U.t5e)7HQdQb5aBX?>hYSVDG=4_x{[/?flvZU>oAheHMOv`Lis
&$cXnCB+srKnx6[g+>-c.cP
_fER3XSZDWlG6<`@[KuPtty&nQ/GoNDBxMlP`%&;hH;B8cnMdKwg^yuwacqHsu?7QV`z"kJQeOQzevfYepr:Y^BCr=aL^6$4k18K$wl
BJW9Ww>7FCX!*jCmNJ%EOXfSEwq`1[
D=
3@>B,]e<`7D=4pCiB-x`AY8,4f/is8s3?,Kvv{(Hf@Pw"$A_a6S2k`e+f]g9cq4c(%dZX"/JJIrO`u,q.cC|_.*45&.K(W9".k%78G*FY*KK2-[M>);{OC4qc@eik!Q?hb-!j86-Z0B(#^^0/sSeet/`DtV0rM`]FV$}Rl4`KrexNi3_knhMY7ZYF`dMnRK/!cF.SSKV4{3B/>+@O^5TLuDtKy%j$}r^&#[SE;9w6!_}J``>c5L%MSExouSnA9/iEK(]*sWI^O($iRp>smFni9(wQ@hP4#,&L"UiPV-s-?$6h"N50&"?oox_`$9c/:2xCRt<1-0ii&Y@Mt,XI2hpQ^O!*W&736(FaU:9Lv<|#^!pkrJc](a3&_JVH,P~!r?_+Rm_4&S=%7QpXN-d]`q.F]F<-sP"5yekr_uUdb4g$Xdz_6&?]D1jKVCl`3GcSw$dKbXO4,ArQNW<U(A2*7u_P#Q)OAtzT4`VU*u>HNZ)maFt)Wo.%hH;X[l5+Cn,;=5Jjp0+Bv=(4jX4afrQ=tWwYCBuXiEd[$G)Y/f>5n:]Ny)gvB$`gtVFAddE!p=v5r28*<hnZghaQ8qS*Y@`Tv;C
x+H8TYclfA]l1P4XPGxlRaJ$4,&3wp
2Lk.;"V^dD1pX8oRZuaOimpO7%
"vyX*mK+gm#T.5Pi85rp@&Cnf.
`>K(*iB62^e|$jaL+%CZs1=ZT}g`nNWkVLFq%=qeMRM4Ouqgqc/7(OSZueRyGHsG0Ibv$Uv^QuvDu(%8&D%#,);{HPh~rx&v
B03`"2VxAYjxeSbsRkAtoz)]whL)EU|9GX=.^xP9cP)e/sHCLM-fR3]51;3Fw0LmA^e&NFfkK;Kg-2/+`+-+t5hK&:~(A[Zw!KUQ7uSfuZT_"iugRm?/6?[x~So7[A~TvrG9bgG:_2!_x9v&fjIxlw#*MZh)[E$`v=e6Q!vf}="CK#m`6<bq|49trD-W"<?c+vtKnc,6=K8(lChe`-z@g/nc"N.wS]ty^gVG^uxhK_&Qykty2)El
GL$G-M;>@HN.):!rk}.J6fMF)iadQWT4;=HKCpNI[S:)Rf]E$IkwH`l5`F1rj4%PQ2:_;@/K,Qo@?=v53>R
f~b
pS2FdweKxA47-1wGPkdQ!vgv6m!kC&U}::f?-*<;rYFi8{oAGs_ai(a5aEjS;{w;^HPp>;`v8{bq({Uy?B5=aL^jwD!D8u35H7_)!8OKDdSe+1r%wog5%gSp&>>,R&BB>$KhPSE<(QEJ@TbEQIlZjl<,v#x|Cv/p]IDT,bW"_+
bV&ZG&W:>&6eZKXiDDkR.qw3se%p-TXe9sVRIBmqq!7E`[HpbE7FY9cvCyA8U#TqLQ{<2;1DXlx4z"WwE.%%HICm$,il2;}QH#L=>mG!X$MZJ%BlfN@33vxRgG-n).:L8Cor;smLp7lr]=eK|m5R{<tvYVNAix]i9hds(
ZU|7R!;[:Q(&_iO:0w~V{oL6n&+(_[9xaO+:u?#[QXm1(r:/bZZQQ2P$IyX?(J70>d+wo0Dy,<2R&9"a?PM*RquP%ky:/>l5h/~Q3:QT!O3Wl$q#&o.EJB}1O:L)SjKB8B_Pq11d.G@lk0:lOVz#-SSZ
ADl|U5F.2sKc"2l|jl*=RoL@KoqcH#&>q)ch-0wOnSYh/TB7$?P%aXhaNU)niL5cpB#hqu=JRW@09Av<Z+8{N[/=>L(NU&E}lfuU+:q:!@X]3@2B?KBS!}y_W5nk-8#cs.(F/h?=7{;CtAs?8V"5s(r4.I4)@#p$dny9m;70eKN%w?;lsSwl64rE*O
Nl?jWNfRcbB-x4N7SyXfMWt
h;3VA"eq/oL/Kj29OCCLTrg6VU+goclA.$=a-NG2mgVLnq28=J[mon9[|85QiK$w56H=&`jbg7ys|rAN&';break;case'default-orange-4fd2276ffa8eaad143aec2dba3782911__01284cf1.css':$f='#erWO6KZQG0Ow19$3.42)`W0IOKho2]iYa-u?p835Eh8U.X.a-yZ]?$P#]M,y<;M|U*Q46Hk9b4)`JcW)i]+O^s6<Zs
zA|L.rg<X7fkGS)O5w&NS;~=EvRus6a?#`z%5nBlUiP;n=r@oE&=RPS;K1n;FGL8<B/vP"BxR
f;4vcmFn2k4<^NdN$c:CQ]2n0
F?K/DvYx|1Yj`kV1%u_5oamVfA4Tgs8-WZ}tym}8?D"@9oG5wFtyHJsUn9"?T,MN~/SvM?>7
H->IG"4+Q<@^B,hIhoav!S+6`=OP]-T#j7ssIr[ePOnV@&n/0S[5N"&dB-?a%>]B-b[B4@h_T]EFStqzf0>C3<Ru-Ppwgd!!ge!14qYlL!ZE&d8"Q.eBkCD`V5HI2FD>wUOY_?<F;vfq5:Bnvu1UKn:qG
*@AUS_n]k)JhcJvODya(TEx0l=_83VyBb0m/*7/vb57
xaxSny2Kfk6iclc;@ke2SLt?og@skUK&.aDavm/gx"f1IN=IpKx@7Hx^t1JsK0,{w<h1jWyl^8uHl.qhz!^miVw"wxcho"xcw;vvrImVxC
>HDym+dq.n|H#h_x]7$c|UnpFHH6]iAJ9ngpFMrId,nBev9`;yUctPR[j5!n%X`2QY#8#f1WPsta^yNSTy%=(pc2awny6C$_]xaAO*.MV7|ZLtSAjL{FjSs6DhE_Cv~bLyn@+T<.N(`aD.-&qH#(ZR.e:o[T5w.N>vy^&a;$_>O?,/u7D*&n~/pMqpy5JB9$i
_9}rmkcRs_:vI3)MCY)lN2pC%2]$H<Ia^n5@>Q6
TNwSl-f%cHU`QW.WCySbw4*!]qK--oc6-/MTXS>3*VhvL<nt:1-V|f|qI=TjnvxLLd|F@hrHF@ccIptay0b2p+yR/9j8c/18/gF]
-=4OwEYz6_6SZa@
F1@,Z&ab<,;#yo_HJ((wu?!ed+jZ3vx#:Hb&(O=c850?L`<bOFbYD00M5TR,AaPq]j*ZX3Eyr9j!!?Y>>9#k#+PQwZkT9JJf/_ww>rSZAI[C&]r7Avjzlxoy8|)a.T$*mcHv=JU^-n^OLKrmsN.gsZvTa_4XW6`GUy&1nRWdWh%^s@+eYrfe?1-m3.pTIIsEfzlvWLQ?x2(XADv](Tv$Aj):7ik)G]Gs4Nwt
:"5L&@pxZhgCx?]`C<fx>A#?PILbF?)-Tr9hj?S0=7o;bq[(cATBFlNqJ[7V0vzlJ&~:5N<WpZfITLG[_h|KX!~-:RS1)InfrP,mf%0+Ub)pd,nhZBz0D*04[Fw:3f_4)UQ]M*U"lS27a(70b[wpT[5%/^baKdw5YSvy5
^iAp@;mUrnu!F6^On#,8D40f]&!d:7}V>5H3(Q#+H1}fO/p/nKtC2/>"k>&u>me!kD97a4?!d;jAYWCi$q+I)(M&rTYD]XKnkTA_jTNomn/*eQ^W@S[[p#A%4Rx1T86U,N!%O;F*T%0_o[l!zyL:YT[cpOBNQa%T%#J;sQAl`F")`jZ.>u&-zv]tpevVJqW=[mYa{,IRcTRa1QtQi)C@LASkLOOiz+1&;9qE!wO@Wn82LWr$%ZHrV9*mw=LI"ye`B[RHyS
en2K)c:_ERrUcPb9k*QxiU_H?L=Ato*a$3;dEJ#XIzb{_,b~Z51>jq4Co[,DsadZ>9aOezvb
:TX9s^PsML
*$"DCk_vUkcpG$i2`,v|j-#Qp+gYh&r}:J+lFqbWO2Q&yJT`KvLRVn[t!wt[l$
/,S",v4`j"2w&Y<S2&=,+e54N2<-
@gBGe72xyCUCWp"Ma1R<U#aX]7wRu;e;L
S.LeaK#Hpl*b&]"nu,$%
wn~0{FB2lPm(%9/%1:lW:N?_fA5d(ExHAMEn&(jme^pKJDv$VwO3-oz0{
nV+N.l%xef~
?;-=ZRjPoO,rS]/Al
s._B&ePMaVi*qo<byo:OD!-)O)le.PW`0.[1H)|dupEF)!;POmAb3k(O,&zn-W3d|Y`kyZ1fSLfi*4:^mG1Udtlp$(aBVCXjkA3R|@a06`?BRaCLCC`9ASURUTM59dAoKO}E/A:bd;y8v90qW#5y@GN"0NP]_m{=)6^OgcPtU#G-@>Yk,>>=y#Ct74AJw)mDbEH=.0XdBLG%/w<Tg^jMc88ib8&BC_9;pH!`wt%+$Smc%5!FK/%ABw=fSang+"OaHCqTN@hb=k66s&1"[PKLHrFq_`ZBp/Q>#CLT)n#&u68#:!.aUH[cMrB`nBd3T9{$9SGm8le.G0pY#1+$`7|GUuS);%Q)7&V$QcRNqcZwaAyu.o/@:9l2rjx!~;_Ij@1e`z$8SP?uAD*WxXsE=E:w`?^:N`%n0V!;LDK-D^;R%
wd3.^j,re$G8y_iwzR=;x$#@(Z7f>+4x+3Fh9#_#u=f#ibE
=$I8=1z?*XHd^xi:(D!ms*[BkD2pkiibxVw[ir8:|ZA0qg1bF5v%Lm"$bPu@}"Fr-*O:L8/a_+/dOfBRL2Bc&;ealqLKUT|VANKmMVRVTshZ;j<"7hG54yIahU~=EXKg<U:#9PBhk-$;=t)bZkDPy.AviC7ONq
Wo&7/C088!wsYMddx(9>H&G?&.H_";-/r!bB/E1jk,,/c`DsJN%xvSyz.Uc;ZHj]ETrtx_l!<xb2.)1E9%O{-/3k?#wAX$ZN
sS`K_2
>je}R2)W?NnO`[k-3r!~jZOkk;;?A,r$9io_Rv/v)lofV0LF`~;}3iAx]O,ai@3gaYP0Y[AjF4hWSv
p!9(%>&9a.Zd&[QJRp[`"TQm*mUKG"Sa.8tXmee"y!+xzO>f1"O5X4lt|c%U_ivvClkyN8e_x*O_{(n2ZFWHHW6BRHRl.I;>#]_G9:5,w6iR9?p`[><n,=(vZ2QGGyrxRe;*ijuCNC(V!
CVAef]9<3EAK!)KI3CL^n`uh)+r1h7%qa-)b#m,Q42Qn"z)*qYIC:I#phr|)+o^&1(i+YbXk*;O:U4]0?YP;D%(*Ye=.EH2Z.^L"mERPkZwWkP"FW04TItsJGCm
AyL]`,"`enI3W
Ch=e}m6YC*F@)jN%@dARVJI1QDi!y.7N5N|pBe#p$-@6To,,)
_Sj/zdl_6B*q``}&jqEb+9NO)&:KC*TUW^XXgBoAD5!+n=`o|`He^YWvvi~"C85o*@&:1OaYrSJ85
HA+O[&o$~F:6qkbE5%)8|@{!Ot40{!/JRRC`n+=6sGSil@2S_L*&%11eRy`nL^z,)hz`jZ*S9A2<O_50_W7^A:$r=4]t$q?(nwt7F#-OFHItTVqe;C_,w/m:xVGW+Y:^2&i`~(25D%JY$N18B.;Ye24J[7%X*x:"`s$)-Z0.!^o*P6J9/,7"FDE[(KYai)DnymN3]I^V1[t`2(A;d)[4r]sL!(=d1R$/Qwc!iS!5!s=#U12[zF74%:5%|cw[7m3g&Tm]C:qf,r2-&^^*2nVI+`hHg[ph8[,R+#)7S^F42x`C~q++!Hi/QrrG58)X)MCV0H,FuN<"6b]G`N:3+xoUW`!G+S6T(fDZ
0[`OeR=gpgK}8`yXgDM+S+>t1aB}F^RjjB2#],YQ:mx*X{H#XD>TcCwkN#(gTn5:$Ox[2U$7gmT5w{;:8d.)$u`+^gU{>BVy2UrSf;+
6lka(=nqS`,]qK^@Esi@2nYSKse
P#8a?cRKh/_OWLTQCUl]t}b{
51NSfd?k_QiBi2k%eVyAOAh9_#*1va*Vs9sVGH5bu#4!i/+7t7!^%"P:/R3.JeM)?"UmW:&Z1m|Tn#FC_NZY4v
3Td(h9V"YEn""v4tOP>_."Y+J$7<F*GqB#<|?h0{+2x)2F%pn#jhD]uXgj2HbaJA
xaQ=J"bSI#sv!a=A*6h.INDN^cb"mW?G<lc)}Dd"g*$O{sWKJYte-?`;sZueTfCF3l
R!TZ74e]sz=6Kw3yj!.ab<[PR6XkuDrL??9!x@aI)CnuoR
jqz;L0G.Fo#2I^W=cQctB&N.Bn%??0(,-d0".LV;)4[sXVJ<G9a^[g%/79(Jz34!st~C5"nk+6PS[=jaGU;4a/rL$H-w?UN=VY>#l)rN@hlb{>&*{M-oBca#j$wwX9*V]bj^
)du;r./Ov^`_"kT!?{4b0Y3we+E[frm{A`R~_pbh-iMo?9#bVT/S"L"!VIFA)YknDBOK-KCsn8g}[e2(UV._4ZmgLBf{`
__kDSb52I%"gX/b($Wu.Z0eNA4y*X]Ko,-%43)=gw$=1Wi<)&tbW>wu27G1<8A;k%hia*+,2Bc9y`l
F/YY{JR_o+$2%qo[>s%akfo;"QR/U[1wCgVTvb`5EE<!1,u.YJlpg2gV$-6EG3dm6SXKK/aPTZTu$DS>36De=wb;E!G*
HkMOcz0?PNnt,;pA9rq6KP
{q=(
loclrOkgOX!p%t(VZad$Xm<g0NGJPB;n6TmXE+Zm/JnG?C`qQ:MQANWkZ1wt>o<!@IcPPtey
jHn>Pp<Z4A6mfW6+Q5v4O0Nh$/9g~M|L,.rB%FVa&kUWgl<tD/aX^HiG.74kWS0vQ&#0k_-(88mlN%qkg3[a_$0sfupp5;zt<Bc=<yE^c]}Ae^,X9!9S]hO9Z>ywzME,rFp52lp<}S1#nrFAHo0V)17;cxd`*bl,RRuJV,RbRTN$th=9IJ*M$:&R!Q0Dl)jF@3($<Pl8wqotf@,i&/C;P?9Sy`Hddmj+KaF>cP0@vd!f$ZjO1q?S-4mDvJuSAi|&;/y2R4R%z8
LI^bl]yLEhPECk5A>ieg2!y*`w.D.Y]=id:(RddI:"tjg:ds"Rf1/qk__orN)#>Q^tRhFtlg_{Ys5&Pm4%S(r*XmN(G(!-ZN7I&+lc1lb0hgXV"gD4RnA$2}CDGK_-ImEo0/W5aGJm7=N*W958-/APUap4hqd2,<XL4nLn#tOSv@&hjJ4t<`6/OJJ`)Q`LLU!)G}.*aXA9/qEK!j%HTa2;(+gl#;yAF>SA*F:{v:2]X6CeV,l(-4C1$3ied72kNSdjyAvFJ3-4T2Dts96Z.ciBahLq7]C`<|x+NElM#P2s3?Z.$g<IS)f!$Ujov8`qacnEI32"Q"!r?_&"mO4&xMgMO.Vh8~Zvoh6%G`p,O{5Ye6M(w^Bn,!O1I"@Jf.jC)tWka"W$c^;^.82eiLQ[0U9fn5g7]
P!6ZHjt{=b,D>=Ur>FyV]r?$xSh`e#+]YR]ousxBYsmWhz$IuIens/QI[G>l){y.rV(fj}R0Go>EW<4mQOR?"u4G*;3"!K80,,IX%{aBYjrV/Oq+e+@s?,at^{6zO,rzSdu/kR#rEq({ry<%jX2k(mRjxF?zC>O=f>Aq$!A10._]*=P$3kWf4-J|*|msE_U=B]3lAT&_Fv+#fNa{/OxrNpb^"4kq=0D,s+P-VT-C?zZ3&4`Ku?_P5dx](e>Wh`CLX"iOtN2gW~+kObd3I7`aXcH6ws#la1NCc]!}n[BG-HYYdK,41
hsVwB.da)=9kvnf.Xg`*Ms36c8b6XFz)vV@o"wjV/y(k.Y,mQ"SlaKX6G5tL@XP9$Fg5ha.n+Hk^o]&Y+2g8r2:%Z:D2#7qT^30.No`9cai:!v,K*Zv/?^5~lh6IOb
hBu>9:e<#uJn,
#Kcg/[!`kr!o`+.cz,
9)J-e#S<)r;O:XCia`rJs9d=)j;CB#[A7D<<_a0=w5n`i=5/qZt/C4&6KOZTrwDha2^W,b?Q7{vsUunmmt3Xlv
=LZ2|y8fG^[u=Z])KfnN`-C,.#5<)Ph,I=E@PQ0fC$?J_aa6,DSKH;f-q,%^6hCAIZ&7$-;A14!?+3cob*{JDBk>ZlZ]D%bRzIVhLxn!FTYx%q_EzwCT*Au>.^w]t%9.O$uT8J4bv4u_}MdPH4~RO+LF>1TWxtCQj/y&VUv:tw2oyuVgf/I?z`^XdCcEu/1&fJ`"^th<CT:#0,,Me@`NQT;CW%QkkR*&3w"HhsMC;F4fH2*owcL`8TS5
y{:~;N
}V`$`[AG=0
`V0+8f@q"d2ynL>q@fp+6(#c_/aWD`2mZ4$32Ou{$;3sl-IB3U4}p"IHz!8LOPIEvR-$pf-m^2+N"<Lb!zO!wwJ:RfR/1h9cNiY
Gk*<NXuG%%HA>i*YbW:C`:=!X>6j"GJn4l7L,uUH];c4]^9k/cISi?1pE!q_?wJS)LAOX,W^BheWUsoe.ADdi5ihXL$&QDi{c3gDeL
Q?L<tP*uOTldq:+*1#6yOW&bpk&HWx{4(IJ[^?Q-PA_AOOU(w=/F
(X]nAeOSf@9:ea8IMJN*XTtZ_HiTU|bs%iF6sx5*eMk^C!4cDg)j]JhP"V:aA(]?sS<=v&"4cx""CDFG!LdF7942Iq5*P4uX=FT>6eMgiwjoZ$OBjVlO/Og!PwqmWvI2"yt[/
fA
*B_`giwYVee>X2{%8FSaGsHF9!?_^Xxh@wnTp
i!;RH7e<Y<c=YN5JV
?(3+g*8a45+yKY@-.u44)*|W)].vJC(=0G]!(ok6}W`1^v}GAXo`-R=k
<#sddH?ulp(&O",iMotG<yvF-vRCQ/tX2oXtEw-e4!c!DsS1;QF=BURYOsmP@>VSP*cKIhY3c%^$M[kDY!exABs4tME
e0Bnz"IZwXtZ';break;case'default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__01284cf1.css':$f='%erWO;zWhG0Ow+:A9,R;<JwGf=-
)bG=HZeW[`~?-kjT{pl8m_~0U1X7T).C#EtqU1buCciN6t4"+JcWv?+wn5+Hb""Hb,;[Gk+b@cz@brVsb;6muUs]NGGi}P&^-GWIm:)3gwSuK7T),9~vn)".,llrfD"j
][;%sr?B6]eb9EGG?&]t70SpnX)EceriG
]L`X5(Q3h^e"j|^5<@JmvT-CF_E)Sc[d<OPzqa)r!+SrZ"pWb=wNl`6+;tB~5F<o=(L7Z$Lnc#]X@6B#=mQY])UFWTIfY;4@rSPxkl3L;F!NVbSQDh^KPHk,i)Bj!Gc7gV;`R7)V
U7<g:(AL(8su|pqVdRsfRoJ`1:%F%HQ"@%wmI@&Kc2mDG$lyPbD3Wm6yo47BGGTo;,pg;et1O-Rqk=YpaBi*Z]h-G
uljFa7edFW?Ml)fb!O~mOe:gsxZ,gc90nwo^hW
7bB]w;L^K|hzmaGn4[r[;ZURyzUNLJ$fx1uH,|uJtKy#7Vt6]vy;eGMz^AtO/$k[1~HCxGz#a>XYgn6}Bi<whkc4Mr!L8#Mbhnc|y|f;y~WHut(VTtwW$Vv]wv7x2GsTO!MR=Ir)tut?m],gV6v5i]nE6wm
w>G.VLH#uiv[w~-jx#e:uI7xchm^z&5ZtSqj7J71ylHLK{M>2HK*j#j9<GnY&%<_1Ps2@fyB4
O
JHen*9$?H6/a8&sk1@2j21xz+Bh:y
*ZscKZWM5xAJ:yv#]]Ige36-i6j45z0fmYuV!e&tJzX[c)Ge0oR/@yR0&.$D>f4x%[@A"bdabiJp$T=;Q/p,V#8gSq#2+C%k;|qHgaHuEGFPw_5^ZF_>-+vQ/>?A50=?gryjXsOw1X"?$gMVG9W&P(5qX1jxZ(?%NXHE58h`X{X9,JDd$
W+mK*`w!$#r3aV>[[d@
;O@,Z(c%;q;$y(gM=7$TH{+^lb$nC&wS(4b~.|5,.Fiq-+pecE-t;Hn{D0&P_,){d)^]PN5V*}n:t.EN!cQRLe)i8.sCtSO24x!lhC2*E<Jcb/dKuD?lc*r7+w"-"[v7tp@|&};I_`mH`{@-[b-
A-,VAzGc#1%BB|vK#Q,Q?u#er/Ew+s.#1Bxn8)FfpL3/;K#)6KbMbF
RAkby
M;-9%$FP((3$DD5D6()V:B0!t+g3NEgK**$nZ)T6+K;3O+rcW]n."O+!>7n
e5hgIjE
wc^Id4@jFWe_BkZsX#k9Jiz^&A)7S3.JSXV%v.XR2?MAueh*>mg-f#/RfWnp!+cHNM5m~iaHb2a(@09_jG^/ND23{<qU~q6t
Z9@0,@;%pL02Hm&6q9PaTW`}
uy-&IaHeMx_7pZj$L16*BchM;(;qO#%sW3eU&FohPZG,x#M`p)@kM5g2`e.RU3-NYmph</C`1<mn2A}*HGC@=gKd[?9h}Tav{)f;=Z8YtZ_T$vkb&$jgNp3QY;}1Q5;
^N6=Y8,f[]^nG@:TEk3:V?v[]rx[A1CCV3]UHiYQXKJ5>6m)J2J/t[3%gg1@b9"M=p^7G7G0vL"0r
x?LC&2"kX(+^nk=D368^=cZr{vjX^Js*0SN
QRAIH*dqBuxFkJCuF8!RyBD6|Qw!0k$D>vXGG?;^S=rWNW]y>#x#R35EN!dv-ZcS|Wy
xRqtM&9YM$nB/T5Tz5sy4%MQKQt:$s7m}b&!<"ENu57S4BpqaXbWTP;%>QT2yh_M<DI_NSRrl)j"PL!Jjv(36+D?gdi(w]8H|6E8YdRs4"Soq/~Mb3E]fe46di</
^
WnPED%<-iH5E$#_ELno1@Md#wjOUXUL/!09_aS(tpLKa"Y#AoZ$e0~,|)RU9+`9V%#-W$I-e[3C,GH4KY(rULXjjaG#d`|1]XE@g
*b+=(&8/Fc03GTX696N#gDmg5$ly<"B2zKe.uR%c)Y~/d2rBup3ooxf`NWk2x2odSNCmy2KM<dIZnhI7BG-h,d5?xH(P$i4OIXnq`2kT~vEf}q*,F1KkfRcU?rt&D/2O=pEVe:
#0pu>
^:V"0F+}=/8=u6t&#_+B;yn*.&ZZQSgbI(;jPo8YB%/4*IhUj>o+Hu&c0ee9c[!)vL:R*>rEn--L48RtD0(1Iaq^j!Fw^#eg6c+`Y]uu;,lFmD9E#cGQ`
);#*$^4s2/YK#yMd%}(b=&b%/!5zmm7s-dK=lPj:
t>ys(c^Ti":S^mmx*b~[dx^u8YxY!vTWJEA=hR,?6wRIYKQS4"NZxe2tcY(%(^CAV6/O]WVRAp33po>t-(Q8lU0%?NHTul$m6BUP)Oi7#3Lv2
A-ZtyFXR(W8rZ[kjkL`Xnuv#acd#(
vx,i-1voU:Hk}
]xE*`&[/sn=4vAWPAVV*Dm1^I6OmrajwI64po<tw[yi3(=Kti[v#KC):`x^:p(5jJ_#W=h>FFJT0Um|c["`)mn;s1@&g{6/ygu/UI9wY|?=PYCMx+fbZ"xP.K+`D,1/;2IW-~[zFnT[)M*Og./>/DM~)!A!=_cIAMrx>LA5BePI(ThvWUi%E#d|WWOf(H&s/l@DIGGJ">A^.t5k;PM
tJ-3Qn3fYeJ.?od:pT5EB&wHX,$vso<bd`$(C(.KV`l<DcP#$As*x?GNFbNac[c?.Wv,ezy/lzcYMox@0D,
%#h7SiY3Po&Kk#]s/Q02X4t]`-+b06C{$+gykQml`Gj{^s%:_]ZlU7IIA$r}QTV^$|r?$-p%a5I^5.WU3)(}]Rw2:`Wfmp9!=nF[`~E,r.>>OC$t?e!rQ32M
I1]0/r3ZpJsvrXPN3Gnikfh^|N;fd^kDR
(-0Zl[gcEV2(LokwsM?_uRf3XvO+U5aYG/D,^r)<W#$]ig!Ehb8hMf*T5@4[uG`M.t(mXgK^-P+sWo(MpVL#*xS<Q1/tu2&q/tzsi1F]Mi38zRyRF`g,S5QZ@Zz[QRQP,l)q%VxD!z#SToZKy/.>b=L=%YPU^;!2}(/xZh
pP-y_u(D#GpkJ+jKo(/0J/uB;u>.?@v-q)FBUl
y;@r9(Y@QD,:SH>.rgO4HA}[x4BQ;`qO$,@SoD_2D^d0l&71#x19B#-OD$rwEGyw9SNNoift((*VBag8ws9%ri]ST%y)2=LD=UJVD#J:*8{]PY_&M0kIR.<4$y44;7&FTRvVvC/6m7"Nv8]_
*DL~1)4,?TdZOYN3QZ#6E!Bxe1r#k8([qnc@R^odd[)I/3>B$$si>8
lu<;vYdru2sy}K-]<:TIh]f0!eD?rq=5I?;ev*/Z!J/X6w%I_;JJ3WCnZ8ba)w?=,oY2ox@0O(4/6H9=]U}!.m8/x*>Oho!d-o0*%v]*+3U,JB591iFJQPwI*Dx@HX5,&CY$@N
3!l-bqWv3K^OsjVuluh=?+6!%qZmO,AZ]}4{0@HXf!.:Mc*fHH3bsMNp)?VA8n;0>&e!HHQC4l3:DKG+#H[LDH$kc5?yfCIqR>s`=Vs9YfSKP:5W2J429%dVy%#22
jp<
>8-Oi!c%h27n4+83"0B/PCh>@OJ_gOM&[h:}D1NR0<u4UJdAGDT5KE,05SyOu~aT:-*Vd$U{m4qPAb+N.qHWdhX{t!S3>dX>vGyz#:TnHs$OwPHW$>;l30x^Q4;Od+!^`kp/H,Zb3KC)T{xLDDvYVE

3An1oW1ihl,]?*&pI68|>sV^%Lp>^*Vq[bD&H"AFP).=hKueP<_s8{#"+!FoA|xh%uLM,I>0!qk1a^n4E,(Do^]FPRP>-Thh?{L-,&"vR5*HH]%#+1#+K((m9V5nZl%7jm#.=6s)Dfd3SW2+2:sqjBE$2`[HT1Y8u!K*.![nDMwr@y]o;dmw716uHb]
ri<!%oaY!/sc^8oj4t$jy[(/j0]nFz^VVT#B&ow,D$faH2lXls.f!yc?@Ri}O+[IdND<_c)T#bQaqLR}673X*oQ>q#gA@_d=$:=LsWI0drju.e,5([jIhwy,kj)]&HRpUOUMX>80xfw?%5S^;JkZ84rC7fsM01@
!_(R,"",x_[i49nH^,1V(O2tx^26tc&&*`>r2R-&fc_MOO:["4CQg3%yeRjLy|(T%@)j,aBRfKg&Vh"-^4kUM.a@;`w?1_qY6D!}+dXfdR:siPYHQOj`Jgd}k!QY$8N7]fQF-86Ak?SA)/h?nkdY0r/rTyWa%Q2(s|#JY?uaOI;(]J@zkfI"a`Ih3NP"%)f,+I"NoZj^krUsB"r~jZRTz(Zr1`,VrNxG_WwX@~?%vV9BV%$6/-ps6/jW!:ay2~+>FXtboY=j435y)"
m%
C,;V8.Qoo]JscE%Y+x&*e
p}KMT#Dn/M)a6sPG6:0=A&N-nXGKs/
(?d
"@/=lcr)!A^lSCpZv?j;uA>rM4ZSN%Td7^o(Db`q-Q8Wh$GMpn)gfB(6FX/BlHo?RCccjsE>zI`xJS[%Z-bpjA,aa(`iP_D%ffI
V#~*a!/&#B0sfA]IRL}U>7zW4uF_QHB2F0oM-kMjLk`NI><b~,*
-Y`sjJn25#RRhfkqcxnho/=QqKfQGM2hReBQLZkJqjuaj[pc$2Y=gx!CJN*X4HjGUl$nk"{,r3CcB*cX@R&3478`qaw;l
oFI,fQ0QHTz4@tQxb!lTg($c`NlG2o4^![Zxlp(H%%1jk*+fa9A!yFEN@K#+(d*Vs%[&p!G0CSv^z16"|F5;[H<Su<*Hz-"9T&:q}k!+R%u;7<Cn.I-WLjOG714k
x-`C#H9mOUdV^;Ink<E<%W;5N6$-ibLHY
Cb!}a>(!J]Dz+9la;oMz=MPGsFlP=)G"+4M@?K:SD7WE[3lAP2*iPB)Ja(o9D$n6v+_X4Y5o)IDBbJ[.NXMF>k-&<~O]1z9)O^kOLsjorM$"i`1<oZQGt%DH&h
s@cE;9#5l<#=+Ktf{2&)69iX.3=dwA65@#{(M4vu,8Ar$=!-^A)Z79`"W@D)D@1f#Rm^ThbEN.vg+j"9B#-SVlfTVT#`0H{!J[@Q+dZe
4|wqAxTZ#[Ju8D%rY)AAb"7)DHw"&)rjYaY7:Blp3k,ewTJAc]E)lOCUi>Il&oLl.L1G+/>v_6M_dvE1fnBw^Lhc
E>1w$9$;&=upXC<?Oe3v#@-pg1Hr7W+8oI[J4yM:ue<s;a9BS?Jnq+`98A7&T$:;ssQ?K+wssg[PoT(XS]=CG;ey`ML?F<7xY%]nF
QZ2
[I1W+#>$#vHckbzh9NagWm>=6czqsRq<LC_!OJ_,kk&b/wpE9=-BZ9n][/nqJoUwu5(KQtpi
3]Ig>[rN:kj,e1C<6P@@6z8rH!hR"SvT[O`aEhSc7G0,xGGkd,kmCEKt<fe<p-oPVWRhCS)XVEG:"[dCvSdz.48T5FO,Ffjh:W./WK&LF6a4rOt>8Z_03(+QA}(e8jPat,)nSiBZF55INto?2t@`-|4%Ysh(uCcM+Acspf(Lje*CqV_w#BvSJ}dHY]C6ms2E@KbNbQ^?&j[qGw.Q7qE,=HG]CFN`UoqDeh2H8<`oj>q?f.Xg`*Ms6_c8_MXFz)vV@q"wTWf~F|=y6yT/-0HlLE=[nn_6&@&gNqW;%fn
12p5rh4CNs_9h#9gf2$Ug^nANm$;%>M<WG196t6opmBV`"x!Vz/2@}yaii3OB@R-FcpQd_#jMb[+VM1ldW+exE0_gc0W_,,:]^<8+v
-Er[E=e#9A(-{q5U|wkpE
]=Jc;vsKns`7_&y(rDKKZ-xA*G.crN>wW]5cL`Ka%appT*hn=<[B[NztFE8Q>(9.p^,N>&P!Ra"d-bzcJ(VKbUP-[TXl/WO*HX-*82;E;uh=%%fu1W!dbb1(MSY+3V0^wtt&s>E;`ZrpVQZc,TiwM:cVM
SLR"BeCJw*+$QDs&@L,"uYgl^jT&y#B)K]ZGU.E$znyS
x?Fw`I1kXs8n04Nm1ZG_1i#:K1lr0K%_!7)6%kjr8J9TBI6`>.E;XfSx@ZEj)(.bBd+>U&`r9Sf24zX?=YE~1EH-R:_tM8TWIlK{Pj:[9K,F@i-@)1RcOsUq9mtSZ}8<:BLXg`FsArqdNLR?]y0B&Fr,g*E*cU"<L/W>^GE7#abji47|?#YV<ecrIwou1Y<x*-O311+E<6uNJzhhTt]
.ZPnY4JX*
BDuI3?!4Lj@C[`8=`6:AX>+cLE``2$nQz#NT2*7"[Xf"iS`8=;@}E![cA}46&eA/X0pI/l8j<]Hbu:_G/THEiO(RdB/{BXEddjn&0e??9^Kj<#d{P:&%Nwt"XI7/J#>96a6@uVSQ
@8TPw4x*V-&,(k16(V
/wLU]S_]^ru?id98D)pP*}AVl"e}u7ApWkFlfs^e:"AbhUrroMWZn*PY2el]h3+@o<)K+uB7N.]Q;&5{s$nn!%ax+Ngv@JSMt[yZ[^FeQAnrd
-OGDal2XpFay.*,.t>Mc4Ep5@9=d`@
OSfpU;2.[VTwcn+GDKb%9u`O@iCVCUS0e22QuMo<ZHFS_NuE!Q=Pj6"7v<[ETxmYKNUfRj>2
GS[
lqS"$]`L$GPtuyHP`Z6[_;M1j@>%*74ga~$FATE2:,%oM$y<cB5ak@%Y_EfqN!PXLaYioh[]YSFv4":h]v
rhMGem9f($LYNq95pv%VPhClXbY":RU?5yPr&LL>Kvlx{3.STdP';break;case'default-red-9c7de6d1d78ea798bfef943c92b6b611__0c4866a9.css':$f='(erWObOZQ1.P**:&y.4=!vy)5dhEhV?s6s`ZZ5V<H(2>";;RX:o
6J_O$nS!M[2b^;de^?Gc_W]YKSA*>$Xdft-kWb9@6vqt*[Bl/S**(_Emxkz_
F91amQS}scs*I&AK@.T7ZW
}ruAu*h*YW%9u,6
{?|V$VQEnfEK!
yc{1j,S?KZ7u|fh,w]8B/PAn4^,E%dcLk/&E3b6?Wf;PbXq_[QY
}u+`3R>MGAZeUGXFfJK7;20Gm5(Of_~]`uSt>pp58MXFLbPgSUvrHX-FS%dkgtEhqbrSo]*&23`4174Y
psvm83#B#t]YUoJh:iNAHO]?9^66Hwx1>QWw`@5(VOyW6z_]i{5U5^nPO"`!a=_p"Xsh2HI}=ZEj*Ch5MiE0RZvnu68ktI:u%/.cFdWq!lw?[p8|HG<HJt_K?Or.4Q^JLo<W2%/X67UOl
_flcxRt>bRs+px3WmZ5~N%l&yCTW+l_ub`L!)Ix*:(m}M&kZMnbXm,vk,|x1nMw|c/n#MBJm!OyDL>yP20]dIrL^m"yPd$k[@%qkayK|GLl7s%w
nwtTnAxauwEFz#_h6ukqLo&qrp,{kDvsv[WRY&i@yc1#$gS;HsnWy?Mr%x,nC"sR`;yUcdPf[h6MA~t48#,|8#/,s$stMAxs,;w2vm9dOh0{fguilo_[soX$w:M47(WnKj!<
iB<sp(6hnbDNQye^5.T:z#~G|:8&2ntE8*3Uzp--^tS(&ts^*a;$_;fk4/u7N*)Bz%&z(@#Cavj-%]piz[
@WKGmEx:Iaxg"(KvdCN2dT+^"3qk2}VU;+$z$..8Pv0-Y5d
FdA&dcJ~jD9:+=T"OQrNWthf1OxYU=1d30_EEfEf(3s^8SH$mLrO!?3<U}j.x3u|s&H^?Uq+0GV<PzOG;]YBT[m--;4*p`R9b<D9S[Vb!^e;;gNJes0h";tdw1:&C&Jb"?T3D`*FyhI9il[s#wVai[lHVrqmGv
0PyWD^/""/@/7gG!KaGK%cv/oJ:(Q#IV?b%maU5s]$btkuw3^b.X2rBwEbOmy+|.H#-dI!l;e5}%L%I
kMNRL^=hnDD
.BVat=@]W"xJ5[)6TcJI_&("q,}Es(}D%1Bxld-"{q1>1;>O-+JajSMeEb;5j@$Dj[_1Kl](3:f%lEZXi[eB+igTo3n/aLLV(t((gb7K<j48y_kV3Q>9wfdR&kW]Epia
$[b;_^4AjTKFCwAZlA%G%&=u^FL"7T_"JSVn%~3%*Bv2?!ew5;C!&gTn:$<|dU=N1uMuW~o3Id`"9aT>%G`2_c_-wD@*g:%hfsJqG+#$ZV+%+74i_Ep7PiW>`}rsvD8oSt]8flD*SU(D_eC7xiqO;,T8&k_}f.@)]:3gSILp!sm3?8?YpTeF"p08(%96d58h(YBlE=uO2.b{2t@]e]CaUK6Yq)1m9:?}-pk.UG`acJQqQ?5;k9p_>:%4;%"rYkWrRR`Hn+K$Q^i}4z?6[F,LgXasgxC&69ZQ>xYYafDJ!$n!ddU:rrU)j"<S9"73gCg,1B!f7z&7?]%`b#@49$%HYg/S@nne[fV`n8xJSJ7c%v/_lh8Go[;buprO`Jgiu/-<yg
q7L@s9fQZa:b?)e2C@:^L=IfmM9#d)&!P[H"3vmROa&(L
~]rHS&6oOF
A{T$9T6BM8%KR4
u8^sGqGB,$SQ"eB6Ph]^%LVgbx?G}*wg`%F1:Iq/lV9+l
x@~#~w^m(j%lWYxA7Q#=a^kfNsT$<Ot^H$3Q1o~x`0IJfRVuV3MU0CbaV&^/$V4B$Hp"PlIu58IooivUa:k4~vW,:PuJR/lht_a%[$Aj
#@l(Ms?j>q^c!S.(P<(8Q)PENC_UkkN&Ext;=unf(bmc_s@AL~f3r.q`e-BUkPFA-4]~wA?:>TNhYsW**@&uu~>n.akk0~x-N1yF!g5*dOKje.:`&f+M,/Nl!0LJ;7*E1uUVpVj0Gv"I_|J=Q%+C6cP;4=R^d[XA:9<vpUWGQQ`3G2V!tjrj(aBNCU2[ASRt@_05`?BRaCKPodDBSVRU_F5,d@)9O}CJA:6`:W8v90qG(deCsR"/NO]_A71u6^Og7LtU#K-&>V6m=[=y#D,_)0J])mDbqL=&0Zd>LDQ3!LNu^bMc88^a8%BB3U;xHFH&,CA:Smc%5!ofZ|BeSOfQam1E"OwJCo_N>2L7@hKg*`#.#Ai(e5g2DDci[U>#CLT1m~RY+7#9i4Q%H{MCgAcWBd3T9{$:==n[ab.G1#Xv1+$`7|G]rjV2%Q)89k+Tn|Zn7kL`-RKFa|l~(|sA%jOL-ytTUXM!yeN=UOx%Vy[6fb76V{sxw3>+7"vLOW6,K=wKHMFZpDtzr8.RLTd[krsI7wChW7hs?4%DJNf;t+hH(W;3NR(tfGM
q:8
2X<#>:9T2_cjg<VWWQ!6F{.U*M64APk,DOX(
JG>)AaCAK&QV]#8>}]CTLH`<BR+.K12R!2cj3//
|%nj0<[<fn|1O3uY,M[`qnS`Hr>ArRw)whVxTSKE#j.X{&8.r6zY4D(/*_[#5H:b>tzZQXp`a/s?hlydDUh37j|7L0NIyiIDOHq[EoG#g^aT82!AP"n8qi,b?MAlUALY1t+o(-mx+%Nvq([=N$dtDb5!R^beV+eU;ESKUrf4Q"C:S$jle%j>P$3J&N|NhY-[KqYfg9Mc^TR_cJ?;2+Ti(Qsi
(`G2?jJwphi*wpi[S[7NLofAQ+^2%N&>A-B/UISATI<=iYG%Y(&87Pc*n#JC1F#:_oV5ohk-D4B"_$hds=YG`Ls)?RdX,5,.k26EC7p^+MVz)VouC(5,R-CF#h"xD
wjxA(uwN?ma$,Ff4/MDyxUeCo6G,nS.!i}
/BEy}g`u:w=3vT57?Uq"!?3XA"O?TKHC8JM6.c(RE0B%m6E!%%_MwU,XLeerYr84)8#u^z&DfOdQce^R%
x>9P:2^=!E]sp>+/rX0e_ZhO.27T.30PRfOk[:S2n"7=R,}<KKB95`.%h25>-rddAjus>@vLMD[7!?~>]@GR(`w"W41]~UG)Bd[(>rQA#f0-t:K#$.sdFO_O{;Gv|EpWJQZU10-Npb_6iMsa$1cuQ#~(uTYeA8(,zOua@XgB{ASJaO(W!j%m5ZBoj_?.Id3i[
$<K3.er24O+j:GO<t6d"D;U)B[wq1$k8H@tR1HP6A&JvZWt`N6F$|Gkpa>lSsI`&!?^(GMexu@F=!p_A6I!8n1d[95Y)8ev@1
lJ/mUqSLH@xJ3BdY[8behL^[92nVLfmgb(;[OE5
4s,#&soOXRx96qno+t_NCF=h,B2!>2+Y
C4O$pqaXQ!Lf:nf[Q,"Z2ll%lKl5h&C_uyKn3mP^cL,$#zHfdq77Z}h`uJ7"YGEHmw-O*TE1%dlRNOQ#K9Zd3x&3/H
c6M88)Zn]8($MM=8L$U!U3,ZfExl]/SDARGcVldL!3Ji]W1xh?uc%r@)Ks881OQ$#]a-UjCxqk})Y(K6`j>Qz0we@,K<dMK=d+)FhKo[4*f>l%9&a[r(#rPTo>AEP2tKRuq
Hou[/r"Z")+N]^EL$flontSSDAnO/IBJaykTtw"(VhmDe+;B&P<-e2RXr.29K5=;YfHOHS[7lB!Rf_Zj^jYOA[X2hKp>8KEf10mh*)!6W`Fs)e(mn2=G#nBdA,>E*rF?d,d6n*$jU@gq]!zA*+EuEQ7PnDifl+P3}RI<]i{;mGS*DgXu_EAgq]{e|N:OYNiV>1.J"62L[Vx2rNS6&4,%IcK@4#~O{sJ+xbv??AoPRF3,T#S;tb_*W83Y~VST"n,W^QZl@iN/WjS0xb,_Dn4fM==j()*_A9Xv.RxRgM^6B:nZ:$86un&<!BP&m_*IY<q;Orf_V.%+QVFe`R|hM@-)H
v;~5]Fbf=E
.~;`/:ID%??b6La8d;&Ui3X>Jw*[XAI3!~GrCescmHA8g:nj$/dWK^4E"61]YA#lG2vME<;7m#N&UdE|e!7D7q9LE5b2be;i.T,h"d3#7a?J8,dP[dF4l",i,T"KPf;]<K(3k4rZA_H(A"LcA&,vYm#-"/B5[^PCC&SitD>aiI)g.?an1rWmirPd8/3z[9AzZc^+?%&X"UWL+I-])X]xnN,QA6NIQqk98Y/ia?<>B_mm&99Xhy&.i~5i%YNSKXhXQ5+C(2#w$QNR.;;1)dX^BB
to!R3nxESsj$>s1f2IUDO@~?eF>b]<7++1Tv%)tl*$TjV*P1_HyKR=;b9$mjY0QsoO1^]`PY^;lIlJ7X0&J6<9TO)
rNLEGTH^iE[(~M>1au5>qQ-#$6!bLXW:@^r9QL{Oo73W"HEhO?6AablK_v,]f>nyW]5$D$0Wfn}2O;Woea7Pc1<aiaot:vxlsX^]~!FwoI|_=:yO}NVjd,z;Bjgd}<c!c
<qlkylp04kV`LB,s,ConyUvqQ
IyIk7>$FneIcUh;1Y>Fx5lG%Y]?M;*sB9d{T`J.c##]+JtO/WP#]#GZUwxZ/J_86Y%I/_Wf?x=?vL[cyDfK;dmp#V![J]O"`NWz]{(&d$+zKWjULY@4IqMr]6EuV/]*[Z9B.H3[i{k=y6,`SR6O,sq([SiE"vvH4qK>/#mM(Kce]V^@V3Z:c4$
S1/>Ni3_=tX(,hI&.#FZAH#iklR>N[Yg!ev^7$kY.9f#TIkEZS&YHcJ{i1GBn$gs.#n}f%ZZTdo9!)7Xpu52Xo>+A|/y^V.~;
@Yn2bx&:yYh+e8^Y"i0ECs){yzw,3t.ch6veD&n=d+;Vi}?,ZT!ch:2cb*H7fh?);S+Zta<Kvvimlpj(N[e{b1p4J.e<fA4C=sg30+.V]jUYbcse<"$2fC(Y7;fKNi3_knhOY9ZYQXC7t.6G$o4h:a6jbSVdTb$0t}<VBk>d7?.UNTrn*1;YB2WAJ%/ir_EpKMvhM3ipfce0FzYPWa8HEaB&Z.EF2>iBw*Tz79C--jutdVJsfoMyb##P#&3S82#>P8%}$kyr)!gA<SIce&lh?e9NXc8Vyi71jqX}U*$&3/3Qd,;7O",]Ip5m){#4;dmI^,kma4iHL$D`PvQ?B(mLZ>6,=!*fgb!~0o2Gj1G**>6qV*>eP+)BX7P`Ln0o$=.@%-lJ&$un9~9pB!I=(qu.<EF:Vz*6WLhLBuU@cm,.Ha(eJ_3^Eb*qxnnsZq_vio*=WvG&myJ9BDDaI0$@vZi?utJ*fBg`IY/dZ-fD(nNoyuCyXjvi`a")IxeebnZdtgj:G?un$kP$,"2<0sV`p/OEn(:Hl<U9J`S:TA7eWKbb`p6,+*T]^EL8.GXJuXViNVq`e.8RfxvJ:~QhVJ"Dv7m1)1?WQ:%z8WIL3SmV[HEnQj86Ja)PoP57IxUVo-UkZqv?V3x!E/@LPw^!ZPW/cL#>##[ipUZwV$aoVl9nqfSn5)AiK~EL6c#$48s+gor8W<f>EHCq_h?G)!UOgqR3k2a%cIX3
PpZX&(o>Y"q2,-ada/Ch1[+*9y|edVAcjNw%1L)3/m|a`G"h//9EJ?Jq}icG6KrY|e@C~7gNWp6aCguJy[y*uO5@iK1g9!i)==J1-et$Zg[62$
U{HH73jfbj6YAPE4FEu)M,bRl`sIr=fEnWl!Lmf0N":A:~V"ou:5X:TP8]A0<pL/xE-*>jQTS=Fc!Il&1])/x^HQK0*t3ryuu"U[5DjOK/_)@}lASE
h,}xx?oo$mtIYodJlcB@AM]T$eUv>>_
bDHd52b4O"B]o.D/yTm1=;2fA!(MHaa6,DSKL?O8&6=<558GSQXvLNwCm>#>Dh,WUHXkEc*h_f[@Vay.u,pezOQx@4w-%v%fUj)!V0s5U[Od/5O<iU@8Q@?iajXkTj+mh,PWc2GG>U]`sx[XA-/Yr%[]axBqr/&rD!
M]Y(s:Gwfjoic+4i#a(ed18d;vR<DSxm.P"N;3.3@(YhD<TS&|so*v<N>-vjZ}eH<ExW;Iv!v/D=/n
gD
J{Tyjp
bBS:h+7RZ${Q-u2UqgQV7k-@)UPeN/4PHmF4UM2Y/67XZWPTQVo
=Tk4/l)$,(3WW182S._T;L@n.$?o=R$/ZgDqsM*BK1q4|!T70GE8@+sSU/HDb#-fintG<BXr+WDhu(KF]Gtl#vPRgQ&^S
x_}x2B[qte9x=p#c"(6`GJ-[}y1ECRDC37C5qh~tG%$pLBjW>3dl,*l8~Y|5_rZnU0U/5Zn!bLP5ZU#].xM:A"ah%;TrLHhB$)ld-5Cf0d
^$`|6HhtqcZ(065w%w)vf^#U2p"x=Ly9j.=tB_0)e0tPZ%dT"hfkT*V3V7Wa3}w)gFO&XM[88$;8Q?"h<vWqe!25lBhkhUa{38XswZ#<eBQ
!^$BHx)u!wp34N$dA]M,W_6>@:#)u?jg+%7/iQ!ecv44eW=uAYQA$~*q?)l(b;L=klUtLbGAv&gxvbk#lm#m[l<K247dQQyMw[twe_*N&,5aSx8{;$L^lX$?azlO]wP#,fy2nSYf.-l-25)lMYqnBr8;5q-!M#
WjUkXizW`=[Y:/K8@".6EoC(#!+7WO<]b:$7jL.]^0QXzn<1w5]+VDOQQeC%7fiR=_m&V>+BQV<k$Rva}i.PSaXiS1o';break;case'default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css':$f='+O{Rg7nV?&=MEN7&/;#P]lROOX$][e
=(r*m<nMJt>RTo4cfrvUK{/2TfX999-vqfunc<t!S)E>wDW+#^gm-M,l-;&)c/0^6/YV@5i*JQZ1+9:[mJ!i@U"2:;ZPQ!k}Wdd
&KO(#7oB`q[@%tqat`-w/g_DPd>vd^-;:p@&VFfZWgHjC^_^Q>CP&6O|L#*Tq5-JQhAy%h$|&vZuI"m:7JDNH.p1-}.d.cpE!5n)/F;`fp:1v1!am*6$#/D(q6*:evN,3]pPO{ph.(--*|oEFJuul?.!I38*5><#/h3;3L)uuFnN)v3u9AwuaChZRvq<9w<fv_<<CR:%c^LW:d&^_:2agzcOXQ6pEjL7@7iEr!j]@-`>xj"`wYFZRUwFh0y*@:HkqxMAcwUt)X>Y4{%:p5EhF@<Fb*T+!63ZalX&G:p3kyMqrlR6x?Rn;5V<9F_,*6R)I$sp
uQC`hh/.`i1rv0r_L=AnajxA:v0:LVf]$B!"^+YN*hcZCl*TR[o2p"1J^TVK!r
Nj!DFg(l2$/*?[i$_^T.Si9q[UY9=X8q2vQyT]r7mQ>_9Z(B9w0ar??fKXA64qEZO&Yw+6$?m3J=CZQ|uw[WMG9~j[<UgEfS[9Ch9Z;>=&6&,6h.ae@<QX6pn5CO]gf6.71zd9)><PL_D_(88%2n3f`$<8ip/}-
E|@t&@;s_269<cgvb
K1R0SB-UN/*BJKgNvwGt9SOqI]1C(w3O6sW
[PF=@-&%ac!|2=!V5HJqPL?
GZ
ltZ-9-um)l$T{Z+c[STjp/u3Ab4My$&ro[>-R^!jarr2?fmC}soYXyr&:[KEV53,~SF6Yr*y$^wpF2/yW^V';break;case'default-green-dark-d7e561f7fc07f913992951110461fd8c__7a7f64b1.css':$f=')O{Rg7nV?&=MEN7&/;#P^@E8g:;@)9X=(r*jSnMJt>RTo2}frvUK{/2TZX^Y@?Fw<wfl;t!kuRWt_?-x*3EDtI!#},QrgZ|s?%f7W^o
e@rmF6|dbO+$@*;45NVOOa[io&.fc!CA}#IE>FXh:IFld/8c09CDO#:gr#-B3B.W5ktt%!be}huVMO$%[Ec]ztpebHu(#U%[@WIeZTu1ITF:#K6jSZt<4WUUSIZ`cch>~Tm0z!]/h/-VGYZZ*5v4^M4R>Dj$|jSg=R0He:j2h%;!oy_FYbv$gvN/u!2RVf<`Pt:dV&)fpnhN0WPQbQ
jbp?Y{VU<&C9P2$=B}hOmWukd1-V0BMJwQQ)c|G}n3t?SBj)qI_boPtk/VAC/8cWK0sRBGv*2hw}iC4sUd>]4{%:D0E`F@<FmSUV!5I[anXFGjp3fJMq+5Rvw
Rn;<-"$!mABsH@f*My6t(DshH8O"b-&J[W=*&qbYEW_NJ,EMVn]$Ad"^$dPd<W_7s*%x>v*Q$cbDQ>bYJ?"L!JFg(l1qf1J
iLc2T.Si9s[XY98)BO>6WK(YqtmQB+#X"hKMGFr?<}LJ;
4pq^O&Yw+F+<m3?JY?fTa}W.7C+|rV<Ug(;r[:Yj9Z;>:G5C,6h.ae>6))L%
nhi*SP|@(<gNh6
T9w?hb"*N8HlR8-
5[9Kp($LLu9(k&h=xK
=)f.]tRnZ1)IsNB"JNoVrhsf[Ju8hxhLPbi?dl,Ann-6GC0ZiOS$,E}3z%@Frb"lh`jvHx5d),)Gt0`OiKLg3xd$S7dSF?+t$STO&xM&*AmXC@=xR/bO,UH:LyM
,hpS(,|+kl?TuvT:Ynf_0+Jxd';break;case'default-orange-dark-e6668a1545546a87b40acb95390b5283__3549fa11.css':$f='#O{Rg7nV?$iyIN7&/Uh!6LdYH
y
z!s>+b87WGbc?U1.cn8u<yDt!S1/C":Qdadnmt!V6tUf;2*&"[.U8e/
JdoN-bW
MDM(N!5N%!k/DAyoNML5K8hZL8w.o&R*goP6W6~3"%C3`W><UVZpj%o?19}[=Er"f0n&8p0kFUGy:kg!]z(.7+a0yP|6Hj-RP6:7q<;x-%+.wr(YP
hh[m%vc3Pe_<TryX2U,xYj8@p-_Sm34[EWgj#j*u(eNVZD;,&":1n_l)Jp<P5N/dt:ZEVg#MX(?oA#6qd,MH(x>Qn5W1=?:us2Sa:o]ozC~lZlU2-_IbB
AN.kgsKdF6jC7:nk{2LtyI,&KX%QhB*wHe(=6EA.QD
eR.6F@_Hw_/,pfB5n99bv!Y7_H3hVgr+Rq,gAzvz?Y-g]n?;X!]Gg$-2.tJws%q;MIi2]TI<7so&]R7%lGunG2F+Z7#*,Y)-C{Dh76)4:$ro,-smfT@dm0ajZJ+ayW$]B}Ljx:A3"u-DR`5UBpm`:B_yALw~l]C1ZS#"WsmAkmm,=o;dk=F.L%[:(O`7?"5%^1_AYr3QU%#b7f2=;&AJGHLbxIoQg!s2!74ZWkresY>t-(cs+117JhnNka6fM)0/ER"8_(A$
`09.S4k@i[P-P7T(@]6(^4*!Fq<H
9GZ;e0*ng-OkZ@C6>!):Q*qB4_g7A4vMh?n}U&v*r3Wa;&iL]a^,p9)9(BkV*SL,u3
vu;cEr@mruMhw)S]wWrNnsE27ZSCda^>TD""BQfN42s`CB(M6B(vk^+.Ra
Men9`Au!Ql7V?0?Bxco";t^$hB`pKy[|0XSM8"EE]ZXpPh-:EfnMG-jx';break;case'default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__3549fa11.css':$f='(O{SRcRV?$uMU`I>H]#8<+F;W]X`%)h+r4#?fl%d$HA!etXhsF@cAx%aW"zYl?eSD,[s{x_``KU;Topg=>BJjPllS*Sb}UI
uaIR)6}FE?Smgk./_*b;-F"2|(]$s$pF?*;S?+A5y!
C0!zIJ6Uxv([XQC]38Yep|NJ7H^,"*$UXLTY*5h:QDalQ+P[W-=e89Pdi}rC&FJGD;k<<FYzZyB@J/FI(U[F[VT|M^J&$E+%.K=z.5;QH]n9,C%{I@SG&7%_HwUV&]T!<v%AOo"`V`A{Kwr!NUn1%W$<f7yU8:BtdV]-19nLYOMb`RRKDQd@n>_zU9ip%U&F_px"K%q2YE-J!JKtg
i:SbtDl*x_i$*4Iav
Z%$7n85UX$>ux]IVZ4yf)Z%zXJxvmEdR>]p57qtqL>J,bp.kMU1;gzLF)"L[gJ`;k$ab%J3HwS="yPM{6iu8xEDf+h7&
7-VhYjgV95T_;h;ITnAb<n:>bVBP!b6-$T_v^k/ydqYFi;]"fb)kA(]M5HKFo/j
{g<lj!Wm]"pt+%`bj-ZB"A_vr3QF/8e[vV"*(b#ZP:*Ch)v8Va6.?,-+2E!nfbJYp*^#B
!d^.rFyV#[@i|eI-v09<v)83eQ<3=.+V>v?Ju2<oaR:94t.&!S=^Y=u<m,&6oHqJ!w=*<0CNK:L.MN*PK+mQO"{wV33A-)flgAX_>DxxZxuMTX(:,+)jUC?G"b=Q>!$^dE0Nz0fVgl(BBJ>_lbvFfk?t}!W!:>:/ujn&OPQPF]0Aw"[Um3P9aN8OJcfnXL!!3
5n_-"$#+*Q5TI@3vvL8&,K<lv#0^2X|j<<CeI!xj8/XM&X~bB,Gp)o%$(';break;case'default-red-dark-aa471f32fb495651c17bba291cd8b147__7a7f64b1.css':$f=',O{Rg7nV?&=MEN7&/Uh!5^^8g:;@)9X=(r*jSnMJt>RTo4cfrvUKzq!;Ee/
&DwL^cVMBwte@*oKMP>UAIN*R:%$n&M^@b4W5
_meIkB$s#(?O`5?8i"V(]10[24P.veX:*C11m5AGbG9[*1LXEDRSU`tvVbyN|*PeR)IhA94,Z>Fq0g8UgYNG>"])n,XD-@tOMy1FA"G^<YP4+FS6H[((]JmYN(am1UV=)MB,49ZDrq/o`y+OU4hnWg#Wy5
f/2|C6Wz6CCP4^$laCN.#d$77B/]={cCo0+nZoRN23UYuTyq>=&9:B,2G4(g<&H8Co?tJ#EY&6q<hNm^Z?L>=SVcun4sl~j5/G!&LnD6p=sl.iB`K?0-w%p6DUXx20pKW*hoASJW$X#b`p&"u&/3,xjUwYE;an!f?o0Nh%C9:EDEh)cd1i`/
>[kE^I"u$yxW8U&bWEXn.]
]iD[ldu(5*-<4/^6D6HEkE^S3~1RI"Y}3vtej<+6iEs*`a]U"$y),]RLM>LUEl=b[Xf~"NF[4$&jnlk@K^9U
VNg1OI{j
`)<]HZ^-Ks+N/{C^r&%5M5y$k>?Ex{pkEo@_-=McBWkO!r`kU@LfQ%6"7JAr<9A+f23~2=/sEV"8a{mG;]3"/76#pR?JwWZ&g7N4=;TP7^bGon*<&u+mCHZD#QS.eT8h?7#-VBmqJkj(h"IVX3>LDo$t[n9QB:D5u"]"/:(;(rCcPWGSy8ZZcRuK
qK0xP2a4*9wXbPI&uk*o-8N96KNAlByg+v1,zi?Tua|nKN%`7m.(?Y;^}%b#$f509vGhIs$:
1?u_ebu:Ik+(l`wi77+KB?*(yG8$';break;case'main-eaf2ce2c3d91edbef355936903e47e59__e62e765a.js':$f='(`K]`nsZ3GrtW"v=@)G"bSgb;ws_mG23kp]kyK*_,TsT`@|lb-$:.-;"$O_:f^UL]?M-K"6W|l6WyOX]aAUspJw@3.rh#)Q_>3JIjfXUD`FT_Xmb1-iu"u!i<Bdm(=zg}`M)f0iV</=
i,
T)Wyx9DU32H;Jokc0!HBOVyQ4yW0p73IAfA^<<?$b`J>atsQm.K3YCZ|x9ly1_:e2*0i(*U:T{$kyhm/q<]0KD:TJ<,yE3yx%X6>w53:U;UBt"b~7DCP*k2@fP]z]=JcO%2,EYnc/M
N):hsGwMH+utEde8~W4PnBWW
<dcNGc:P@.-lBAh>wU_tK!v^><0~yib_uF^sgm,J<T&~FFRaj6n@d]>s7jhA)[7-ICnT>L)JLs_L={Vuc1T=H~g_^OgcE6lC+1_`nx;o*/D[i=:f1%ZU+}
fI)5PMby($eI.f;xjDsb&V|qMAfkqt=O$g5$@Gij1f=L%a8J,.+o7XKQWnbn>g&hox0h&>zr~
s+<QYUZghwWE&l=^k:$6>3soBWi^mH1w_n%nz"{#2!5EM
,w.LXJ_q<fG+lw*P/CY5Iz&N]!@ofc3Ib
g2K38892DAcuOl5F:D=^{<9V-$L2#-|3xVN*8HH$d`,eQ?JgYCa]FW]a0-!e0f-ZPT(3Ycu-dz)7}shDONN&H_}PQM?&d$zs3(^v_
Q9CYY
HyySk1k.IXaj2p7lQn)yn,Mf$,g&|mi+QK``TcvU@dVEfg"l5=*ZfRihyh!w5]i7L7>d2./h(03:4C84{E`E>UZkC-MPN^$&&jy[:2}lVeLnMXc)v9Fdu,WlbR/
~ihTg^pi:!:
r=ZTk/!eI5x1XY<Ln
e
(?Y$qc0VBFF?SLfpRIsUF.WgJk-poX!7T/(?B.b^seBD+&Hodb00/.UpK7ha(L=qcB1k-mQw1XKK/;)O>O9H^@A;n>*";M[+H)[*L"Ijbv
BVq/,X_ds`G.qh5<`$:AKo;)#Vt0hVe`27hCE6j|G>`a8.]@jl[.!fJ(w:huUJ+%u/4!q%fL+L-*3$=CoiX+
6E[K0GCeehKjn6MLZ&-TM?w//kr/a!P(Vi<#Y-$"]B7xA3gr5#@5G`8?6
X^}if8^U-`{H{WA>[W*h?n@5Ph/*~)RyAu:@zu#t"xAqdE63FK`f{(UT?D)1RUpjz4|2((*d1N@i}/X/4?Q]Q=4nxM-bNZ=j_Fl>;>TU3(:&
bpW|l$T[]2&#xvq/+TfeVB]Ho5NUon"5!aC5plaJ51%2>gQhbe.f2Sxpwf/&ZMF&pyuL<XNTkH89=x,lC1=#3e3{o:/I2:UgQVx>[?lzg_.DBmxyKJ>?C)3`IVMh
g9kro.n
x3!8?GS61P@^_P;d|%1fn#3jzTw9]RUIxe0M?H;K:[!vzq:.&*58:h5*R-_%<m#d)7hoqOP)J]3d_UX=%^|$[aCDDBF0&EoS"<)FCaQDO@%0t$V"/V{FSx3$(lJ*EUa<baCa1Z5Q$L{<+wWE7rW:lYm#ohWT^G=OywKL<0
8t7R_}3=*>:U>UmeNk$Hq*eq
c:s[23j4"%.ZP?O1}fTHth#IMT1xsYXSrx5q|;rSy949Xsi@v+?&9cohcCIVov&P_?2T}nRk#gs8H/0$@VnDBR>X?W;Nkf?/zU-!xX1dNaTsz-#89$|8{CTso!MSa"`<Mups/);T)5h(9*:I?)#B9(`B<%``<Xi(DRtcM2Bm"%FAAFhRqMZcW!rG[g5F-SYFZNt[Nrc+?%@1eN|mzA@N&&k<s]d[gqq>m/d/Z*4BqCs:/v74E-8?ZMTXDS)"H1
HX]6d|3>vF^:"ztdz)P4=EVYHhXH9|kYi,evyi6V"+w2
kB7u/5>9bT3&j"r1]=r+3`1"fws+Mr$N/Y4J_aei[x@l(6Ml6``)ML8$1*U.j1jp4Re?5oRTMw
GCC^Aukn=P1OvIHHDy4[@4(.Mu1@*ntlg3;-uK_jc?32Fh#VXg6u^pz$dxhPK2q8-tieHWIb>tcNesRn0?kT4yobH`#7(ufrsPaAbb7
%+<AsyF~l!-y@7)>SLql+VhxKI%eU@D1X,I
>3;~KP-ebBto!$&YO1>v0cvjNmLr5G0J=8g_V
0&XA^Q#@p%+wR!/]b3)Tuy35efU(wImzcFoaoEqEI9]9VTPlz!0X#U(XL.+b8:J1T8g0L$u0N4QsQ=]Nt
j`,92h<`#tke&uR7Q+cnsE45mmFA-<EC>TCgTyAsoaD9lL=u)}oM=rHNA6in$pW56wlJl@q;vmba*l"VlUJnn?=r28z$J<rm<.(Ox(6yt^XIKqGz`7N1!4UfeHromqUrh=cb/.+(v]SRq;Z1G?*N#%Q6@Y,ZOsfhWarU]of|frKzP9!_Dlk2CTT0Gupf_E0h@Pq5&="AMm)6f;_vFp11t^8<>B!{th
tG7_)"V
%)pF3R,dB)JG6^f)teraww^,Z?$_p%=r@YMcaZ8E=)1rMEB(UXngHB_`|d*<sI&>&"Rha#."a5+lW@!3IdY-:Y"5y+y9a$+^r)FC^p#kVk[aoKe!g)sCy]99m@:aB7C<I>51(7Wl0!{V6
n@VfyI,o37qx;kuC0q(u}FO8co9lxJNx3:?x#$_%DNuw[!|ox/y!Y+dun!yr8oTqOZ{P{8*A}%t[/b0v_j47@mNiBspcCJ$6ss8cu8$O7[+>jxbu~_ZYVIet^:}fwOu<x#"0L&FSvo+k!H-C`4{vC5&aMEwKBnTEC"SyCc{]>L`6;sA=Oci*to^HK;$dYZBReQgGrG,;$r^!<7te*>2[EG`EdlT0Rp$uL8(Z2h8dFYbQkVF#EiI+?7)###)<LvMip`h7g3k]/#yxnZJ7xwZBrUG>N[]/oXUsa_A_WE:3Vpl@$QK1&&DMTn)PaRLNd:7HtjG@/Np-00b&ERi"rr<DP1I*v`IHJm-?zJh2ECfIJuv%Khi53UWnY6<^A5~/a8]eemXZC:H2NsGs{.G=z6NmWDU=lH<A5FQC"6gc_9Zxa>+t(B0:uwtxP7[!#up={Au>D/N7f^hA[QTe?7H5>AwRGI-c2`Z3&,sIR`{l54!pv;R2_cZ,bg?jN^#rG"9o/lC,cV6wOxOT8h0)rjzpT32QH+dl=j1!j_,hF3NEZ?7i8JQuj+CDhJGPQr:f]T~h$X,vmPx"a]>$`PF/LxWTwJQO6Udy@t8A6W:xXnGp.:`@yQRHE/Z85CSedZ_d<Vzu4w$TW<LfA*VRu1&:bC2_e!#b`ltb^mp5f,"wp6NxT,ovW]gw`sKRfvI#SmKEq)sdCM+7]eI3!bY+-bUkJ*p!zLlI-_}
kq9k7_z[HfP2M]Sg
IXB|fF:yE~[t_uc}!*s$+{547eQdF1UH7h&QLOLq_NOZKQcj3:h.(&P-2f#~r=,/e-cW6N+*4IV?-mlNtiZ~W;Fr>W7&smBYKy]8N}w{vrdJ7{o,^1@r4C!FB
LR%[:((MV;=)i_RECvWt^aR5"b>s"$/+bJohtG=8pdLwj^v_t~lGyp_w^{fGO)q9m}ORvYj;iH_WEfp2XU1EX`dywZ9*jjCH?/c!$m`9+ic37cv8o1wM-f(5SEy!dEv^_B.#P$%z@];]TAg%vkKg){Ee+6Q1gisVBDow9g<q&]:J@D/eaoM6_<8_bu*f0*#jmt:pr4f#VB(p&IlqW+!&+pGISZ04cT`IvZ[]16Myey3}lM]0q
0p>2WHtEh.5}9E1$A<
_:]RkudfqbZVvRR!ySuR(Iz<t<gfZyWj6ahpTuZ

-)OJ8(>$ln`RL2bzW%$_6SFprW>v0tgqEdkFLipR%umFh^Uqy}HY!`@$8%/*iZ]gP~Y2VBH3)>7A@M=qXDkGHbT]5mkB;1;Kg(bkv3P~(P<*$^9AI8e|QT$JHUn|N@;8%wQn-NCDk.+QRiyGPfKjGC+yu_u|P-u.gm?G0PYVug>|.]Dpe}:~jO^len*$hC;DK<Xx/w
"wv3jL"0%.<[="]:e!~?i5[C
?hB#$l37PB^0a#w_$e!ycDgh.|fHlFq#k(NJBzEkc{9)UhBCUt6%rA-<S="u=-!Fv>UCo0Z0hF1
Z8Hxxs!0S>/WZe#^/Bw^_i_i^ex026e(:)u>`:,#YnxQ1!Lf%fbv&oW(:>V+x@w|b<+fha["^4dJHi
x$e;nSM37Yhbx_{NbJ*SHe+;MOv18dD:`?Y+:8hQf/&#~:k[A5zb;0rDSopPb$j?Y$Z&k+el}>W@F[#-foxUb/<=f<VX!ON
"qp?Ik!;zqEbR.BwAH;@+xfae[x=$<3$swW(2EyvnGIOueA>z4X<4;)@Q_7]oi1Dhao1o(*nv4BxcYEWsW}"|FkM*.h
h?5t(/fEEK
cY3L^q%f!Lh
v}"~X`"GyhTWYyRp/^nhVV"YjHf^$KAl1u_$yQ/Hg5bDT@^D1Wn-Y0@QZ$AiVJ?M"::T)O])nQX@Y4Iol-BO1&+Bi}N0+&i78SK)!Ka%XQS:aCox8(7~b:whtn5^)S%%#~8RJ_xS0:Q8uQc8G&=1D;J<Vl.msVH4rqQrXs$JZu3+l/a<UyK`.jh;HXHYOqu$Cr13)x1
RS<eW3Aoyu<)cE`KY!W0g@;=mo=9(|yw3K_U+9CmX0!*M+#kAt*oYCo-*p,9^jD(PVk)!W`Jj<T"F*TB
SM#>Q*
x7d4F1l-l~D9m
YGj1?ooU"]NfCs"2^AMB&*S+3M2L[8Q`O5%3y;6<!nd2hFM4pS5|W~lc:zy_(3e&g$1R]/:N.h=)O#ts;5onuZOcyN)

J[%({j~b
CN#YF7$5=~
81([_k?=F1+]HuZ&AV4$M:tdjol_2v`Tr8=+uj|ULG=S*[FS{s]ihvK)n&"G1gVaG<<iw?&ZZ#YRTN8Q4=v+@@jPdpKH8m;(=ZL;1VnUEF=rLu=U{P3eh_;MvujKxF[;<;P&V4F?aR9?>V~*g8oW2ZS&:.7FQ9f6IB)e34i?ABZW8:NDNYC7H+1?l/EN6hu1rX}cPm
mlA~iY-NO8w"-;1y!bx~Dk94GESz[A"hB
s(bJ+0=V?bE;",wd
W)KBmM.9%XQf`m@Gt):T7V8T4H]/h_)kP2eKR[B
<j@_9seiQPep"KW
R=KR2I1wP?Sf86/R)V6rwIWPSOHs6U3P,wOg%1J2m:|%]fAe;HcsgM*CuW
<w1(XCM#^O>4F|)DoL.4J?Xj=9:z2OX4H;@I/4yBQCmPj*
4Ls
wqC(.%fJEH7Hq+LR@1/dyZ
J1HRq@ZIG"%zy!H^#/^X$Hn+bl(0fm.;NX96m
N)d-^|)E:"VG1jJU,G-c-QkkrxH!TWX|)&ST%*63[!ivpvj.Z3X7:ec[0%Nw=I9=bE1=De5I&<Y+&X^!QSD2gYA|X7`i3<*~A8K|"Y`@BBUxh^nwxf0F8agGx3A<NVU^*pQ%o7PX.c3#hO4/YVnJ(2pH_!]RdlFJn,^F
yg{e#?:
)YJuuy&JSj/3
>*@cJ:57/=.5vUR[<1LHnIhHM"_AM#x_,&_stf,._Sy>hjm`IzrrGTxV5da&b,&qt/^w?ZC*9F"QO)Upk!osO0`R,S/_?qU[iz2BL%Zbmu<xZGiz1:hS!F![d}C"Bfq#l!5rlVsxFp*A9/_&0^nk`03nDJ."6.7{O
k%;<SrD5r(/C1L<t3QWGf#_ypE!W86WW.v,ofTl61KtA4X0yhe!d<lhckEALEB+>.72[tcK?%5F^$`A;f`6!Ca5a3fJ*NE?[3O$>500C8/OIiM3}/h30WTO2s~e#7)vrF*1Xd;TP9!?Cim4
FdbO_=_%y*mV_ch`1;M{S10*Qv!|OrnAOyi8150)--3Pg+%z<D^SS(Z?R|QTeyU]s9qJkx`7U1!2^R7;],y/73r
SJc&.7vSW>A(;9Na5G&;f`RFuR:uI1cg
$,jF=(VaD
qD^#k@CTt[DIICATBW+`fW
Y0J9%qQfQel.1<,ot#C?4oSD2<uYYaT07L+>n[M=J_a>A3^*_Mc`"eDuQ;__`V7jmDGqFX_W
?C+g*"DBO1+<l!,#IgF!.5~q*<HM72NL)P;.-@hq`f5koV9X#O!"3AK@}:MiOZz(oZFP(ycc*[VbR=n6aU7fMOC9ib.$x0<_H)lU7EfUI0g5h`kdp!>[=9/@bF"$M6Qm(Zvp`mR49Z|BSF<(9&"<?Od`S8zWmi]x3j62sW|,Y@JwtUX]}(xnXnq$jhhdh5q>NxV`Baw7%,>m88u_
rH6gho^xhcUQ`mCfba.L)J@li]>;Cocnkgu49@i2usT6dp?0[C;]a1.!y.Z0"I"<Qjra0nt:Gh4]$Qtf6?c.Z2KoGJi/T6E9vJLwu=#e&w%+7g*jVf"GYCJ.rKGSPWJakE@_nggje[2yPA1|"qI|@<w+?P1j[3E1Bw2W[g2T7>7fxBw;hcl*WXoGv6ZDLPheGCb,)O[&38a/GBSi:CRkLJr0wRYGu"5-QuI[.8b09OilO#^?aA/,O*>eRcDqed&B?}:hAtoH,1cf;]yDv0rMC}JCE>:TWN@)9&QZ8YC28kC<Xd"Ar)fe<ev[M~N42%L!0?TTPK-{m|@%04@SJy+X8BWZO=hcV(/Ai.6Etvksedg3m|HL$}K9m}WS9KmD:*gIP|B;_D[faM"d([mc/mI;RWUF,RVjG^dw<qTY!o`hjr:F,p6[Ah/v<4m#GA%I7$%8b8?|@x&Ny*TbFvtRDgU5yiUW@ft2M"L9
wi?G4eYF8la*uVp<rAz4_#5X+[C1v;Dv]Jd
-b~PR%-Df052>Sf:>j^2x`+p<%7W&4X+&U%QxD&(k;WjHX[iFDq`)5vKqGN2^L`ilqE:d>JFclPBN0K>YZC)2sa]4k@d6/WD^b@`9]n?B^yJoket1QRp(rZD<Pg?+ZKRmg-AtJG,)uMnC!]N6!>Y>%Gt)VJMRj0h>!B:MmS4t${XQoy^BvJ%wOey3S69)vw#fLwrlZt.`3mMzRnW85|]|Iv:E)KnH(S&
C[ciain^Ta2k9dwFFVXsp1]xJ$r`VH?`:IHwD2E~8vM5]F3{i,PY
CdrM8m@rbeKNWcE#i#/K9^V
=V*FyFp4w<U25iy)2:EKWZt^fmbO70)`V/zA+>(J@3}#e3qp"HQAf
cQrh*cX@sQh%T^Y8KMfA8pjC0;Le:y)j]O^ME.M^shG?V.=<6m8Jro8-xA4g7yrtoJ+b}V$TciNJJ
]53?6fasw4P+;NwM]L<y[H.M?y+-(,g6jR<mK<O4nAh5)1HGYJw=b/y9q/X-nKzK{d!k?]~w4P}tfeJ9G/4:#+}O]w{t3ulwy/bx_5Z+voGe6>T$9.pSS0mOSl*xk(6*V&`%4f,DFwE#&e4)nczZ0e@!O!c!d*IObOtZG,[dbv[LcpY"<d36B8h:GS@Y3q~=#,5gI`7/UZoC(Uc!U!
JzA^utOzLK3q,cdpI-($(gz%l8:v[BE{Ul"aJRNx!,<@R==x0KQ?PIFe:^K88{0>TGL-cMx.Ww?|$(YXWX>SFyhCE<]^Di-dYK9_BmY.U_hkS%L%ORaZ#+FOf2?Sgm);`:eqqkB@1c(OLPxSBj]Y7VJ)k_$dK{j[:E(X>ilV8H8l#I+*D5e|G!9
(rKc@Zk-9Q=4HUVI/5KOy<8ot|;*.p??63Sp-#g*qkDZ688LgUQV<sR_7*_gGpGs:~nA>a6@W=_h]I.|cos<[!K9w
K}I}Y]HU5o_3n%3L]qtFV-qY[}s~oeTnGd05l4<NO}-5#K,/h/XPUThD6QDydH-#^hr/3eJ0w|.jESIY0hB>ooHZ10iSHD
do7cutDO&7JvCS~AaD%o_.M9<Y):t%TNuc@u,(d%/RzoWnAfexiR1,pr4W>6,)G!Q5SO",EHGdLLD9;lub?D_ON=}9TH!P)RHMTTlfGbD[`GS9coA&UP{Sqy}8!+ax%MB8<L:?Aq_%FH#ua7zVWhgXT6)mZcvz"-fy{N/.je+.g
J5>9Bt=mv1-@taW5//w(fty^{>[0jgadWC(+NLQ@tuF%MB6%/Z/3m,iCAKe

c`X[uP*UfG3RtXTRK$WvuI,vS}V0>."mGH`bG%[|p<#ohh`Zbc3t=BJ10/t=XLZ&n!b#h|:e44Kr<n;1e08L;mEbTql,2)m+vt3T&];`)FO|&D+J4R+c-Y.+
R-LgbI@X0
$dlH61-b=u=Lm2g/1(.sbne(uvr(,/MOK<K"w
U<@SB-iW%h{9{[h+(E/U{Sc*nEfYhvrfQd@$g^Pu}-JhK#r^::EHddTL}<Rp"(iYwP,=ZNYBxyC:L+x!jRfo9%GT[p
O;wkb+utFr-)9|DL2bRdVi8X4`^~a6_Lo0W-!r=YDBGC>N]+oom1R%aff39R
iV2
3F[7(w$J%)P9}A[*;q~pS&I2zjF*-P)c;qmyh*[Sg.Mu4J%Ln-[GNhq
+tg"IPuNs"&UglhDT`uPEYw.]aJGiZZ8%Wu1qK$M[/Ag1dpOYXGI

Fo>.xt4d>#s>92eoKZP<WXwJvV:WfQHxF*02~Xg9(ocSGkq5O35s$$*>L>4@XE>YVig0j0Y@+T8T0%(<S1^7`imZi^!U@R.R[DSKT;v.6%QD.l/5?E
l+8i9_g)a3"]t"s>=e0gQjSZRK.:!GfA9.:*`L>IjjnTwY1/7k7EDK!oi9ih`+D%;|CO2xjj%_3KH+VX
E$./{p.K|IA2~14=#j13nkg-lB4B=")V^Mu&ohxn,d*ckCgKCH7V)J+)hgC
{5mZj;C:8dL/$-lt9!2R-bM6+e2J`C`GB.eB82xD1
UW.<s?rjsi3CG,&=|!T`~q=*b+vhj^_:_(5i9w[-Cl`T+U@j:O?X9n#;alh!{6ZOm$%4YrbKOYU<so_%q@$0o=kSIRdKDajx9f)$3p3k,f8RJX`%oN%x,u0L}1tU(LH
_)UC"Aj>YV#1T/_-g+QWc#sn<mYmWjs3#"iW[?GZpQ"0od6Bg*xGfNe;B,28"_!4^E},kI
%mpIY^)%l{k:"MqS3cMb/S&:v2XwwWn^cB=E<V+_m>.(q!8@8BACn"k7nJ
1!6n3dSOn9GL;H^y+;B"J+Ef>_vB9_<qjo&-=`2JitT69el:5JrX3R$F]eu;^ZP]!mDpRD~U$VKvN3EPa6e%K52;I<Hkr,OEMb;p&*ICDHNMrjW@,vrvscMh-$Y@K_Z"$6_;j?8"V5;8)af*5p![5>:j_!q<@PfOT^gThv^Z*w,q~H@*(4?`N,.hMJ$aZ*ggJ)Yfbqo(RLlRi%5%PrU.~))l5o%uYqoXligF|i(6<Ba%HM[!$TCG>pG]@Co,dKjmgJLGp#7k7/JrV9y;%B0gL?,(]sQ0#e/bYDkvC)I9&.<9Ru6c)(H^Tq)BC7+IT`3`H%iN,<_dmWrjdx3Uou/NwOa!Hm5pP..LNvW+IS6>b
3T(rA=%sSPxE}_+?Vf4_=sMl@PM^:VlU4_In^ZZ<.>|[?
/e=1Q1g!w-l_H0a%*WU@Xj(C;@.:j;SK9`@W/U^]3^+5Ct"VPu`kO)Dw[/v1-BT@>6jN)*,dj&,4C?mA"6((9P$1LUcjWRA"]2}U;0#2-2{^[G`C><-heCGU05`*CM%^1mtplUUTq&~H0BTr^%ben2g<s&Nn5_.h~o@MHNpu<.E9VElcD3LD-;7j)R]/dEY.7T@6s`uFWq-ig-/r]!rtLgt-0".H8;d>zlrQNw#cg_P<))yY*AXvJ
{u{=Zqf2v(e9O,edOr)KUF[<N7KJN3m50$b>"a~ZHbx5iS=sK2|<kW]xawO^15"Z`IgPL2(<>0}m}EkHw[aU8_f(/_`9%r~XGabJrUygzk+X*i1<IHJr69BIcCA,Nu6#$&k3y)fjuS#
=`0Z>c"rVnO
;8aAWVct^]eM7NrV_9Zg{%}bdSOhK(2Hb!TAq4,ak[^`SjhCV$FD9jb7G)ygm:T,:oiny-nq$)}k_j
^8p-S:-Gs]F`_rPLsF$}6L/|S9eHI~s0+}ErYd45p<5,slR~47`V.r
%RZj)saZQF//q)GV=;Gx2,%3|
V&UUlA9FSF;f=*m=1-gU]waa{=$jBq,N$_7iQyB6]7@7[I?E}m`s;Mm/#%gq[!TEwR|/UI6jY<q@Q0@N)r76b#.K[rtoeM--!7A!]"/w*O9#_MV&;m{JphsyAPcZ:0]GZT!lD_Yn0pO9-G+T$X1gkF*[,Fpc4LYGZYg>7_yIio=#Ituv~>P"eX6+Qa=C[I[*k<{]O(TRke-FH2wN-0%+y9o%`dT_BATNaq;$:q0Qkb(t6<:uF)YrX?OpsZwhq@TsxZ7*S_{k*W}
`0A47ta;FxHW;aMm{(0$QB64f@Tc%V!R|#*&ehF5B(/KdFZh.6MaEo4$PTI8m_tF37cb?*.6]Ut,1rQFARXY<>P.N
b"}pYGX1jRVbF94&i,C*I6N9BCweXyHHwZ[5VCM@BJ0,6U/>yGs]O(e1>;%r#DuGAnPY>Qm^R-T9uTPo<IDpO@I"G7KsmtsQT+Au@3PU_mgWs",
<0=)!Oq@fkP8QhLL%AKV^COE_#KUTEhT>-orgmVA,efK]ha"hlPWfDyhUu;i]"RvB]k5u)>O%=>8IV*V@6
)&,RI)&%qIu]mAeu7/p>m/BETK.X#$3fw2o63AGYqGP<x+9zA*Z[CQt>ad
<&#Y!?t5SYiW^^9itExCo2S#>,F/-4t*(es&[<v=R.VazO;BUP<.*;_PVZ..@v"=&4|*{nY@fT1G;K[I<oV=C(RTF]Fpg&JINb"1k^=E!HG2/s58:]B1ki64{oeN/f,6c*6r5sEgMq7J>CE<^Z=cXo<[KuB@WOmi"
:Vj7cd=Dn)zHU_<amtQOCQ5xVm+JY$!]_H~0JGM;snM:,@<7v#EG_NdhWq,B6JkoC5)[Cg<_fP"+`On96yJcrELti2(.)
Lp4%Zc"[3"%A:CPL=@!6_Dc3OC/:`A33NU5+J)qhNGZ[>[A,A@M]*oAR1QpY%.?O+gkf*:AF%Qt!9t}A=&odsN,vno6SF%7U(W.;.BJ#+`?ll7}g="#dc>SNq21,i_-VH0O$].]LwiF3nq73)14I`]KHe-
bu,:
NYZje/AjCcq82VNrI:|FgiH7g_oK
QZ!neyUI-hlsvJe=
=GBL+Kfe"@@(`t{I|_#05*T0,t%OS+01:;NRTF(:@S954i[Na:XN7i^RC_=MlBwP_;-=~mbqaL"W`&3VyCt.^FRi@(&MZ-<FAf+M!@(4Jc!3E/@%J(``G)#y6G*)IQ}eE;Ri52V>O(0&ZT5x
"KQITn:ki0eM6k?vl5vY:8j413(QnQ&I%e=P
0%d_B;R6.<f(hAUHRL7#h5?Wmuof7$K%I-o>B8DXj"[:jhg2Du_HZFVNWRKi<:_Wq]"5r^QRg^L:dbxM+T+W0K4,4hPx:b>,b=^R/EqUaDwZkEnudA-;m@h1h#+`Ya_9zx~tE-XI>v[ur>%nDo@/7xi3`R+I}Fq,7nU"^W!uBDHvl#KO%R6NFKmMn6]Xnn!#_US?wL6@yu:m|1!51:O5E#SmP_pGd1Qs2%RjjG1apoE&g96S$0Han<x2ogBc]qmhw+q+8<%f*;Q+]PP6DXm%h`}9EcY/
mNJCW;vOlJlo/xp0#jS=!>jws4jNeD]~kzfxH/;z(.FzJITFxoAdo35(lb5&i6>ZN@^j!8;Ko.wA`!idvIJ-"wG}]~4W73]`l,w0"exhc!^x]oB%]mZ@fym%IkS>N[8Yto$(5i
YX-G)F6d0f#ZsE2y[g@-G]Ssg^7LHhr-3TZP#Ndj(qJZ&orb>u(8AM@R|ts0"Ijo^l8aRiN3H224ZLWD@AN&QaP)0]_-qV3UV=-^gyO>Eon(lT}ZOh|_X7=9(pg.bm)bn97k*dEDHBsy3#,EjOk6=+RZ1Wy5Db{R0hZp$)tdSEHeV
MV)s<_6jn1P(nj`U`sZfnCu#QRp/0v:Tp:|m=j^`aZf=gm@nM&z4789R`:b<8o4Z?B`?08$m_iFo44
uUp9c3gkHTKSqfhe)2QhXX"IrFjU5,@/b]8-:y$giCqe:ad2FP%.,T4&Y>X7l,q+x^&Vn=`wX%R#vFneFwJW6d/]nX1Y08p3iIY/)7$6Za]@id$`r|TH$8VPr"5CAwgjKNT:I[d4+FY.@VGwx3IG`tt#Dm@?!$a3PZgmEXO5)$apW*`9jANDH2cqBh6lrLKNeY_whSSSk=B60:g"I?Y9U)nA?}/;$k?5es^VD1=%!.]BtC_2^)KUIGu~>.S"+h=J$_r%>hRvuju}y#tZc7c;n9<h@.
ze1xSD,fqQ;/Swh#-0f8/G<64eZkM0I%3R9pVDU+-@QF9c
/+k5Crh1Yc0&?EZmo23XqZYy4Ffvnh]Ju#n?o1<cwo<,m|dG7qE]13D!>xqy[Z4^`I25hA=Fs(#UN}B.QC(t;b:?subDd5q#A34+O7PeFgbsA=JS&R,l4njbR;gIo%wuP8_7&CMIjmJ39]j;=ds
1],G^LL/rDRauuM]s6*tPVkcCCd&+xcxvSMbX3y!r|iuxX90ETf(L=1a%MH0czW$p>jla09LJuWf#9ryLa]NslYm5Zfv*7R)i?qr3H2Z[@=sypVrqztC;t#]BYh;
>f$$
@6%VW)Z`%uf`
Cj
=x(BM(toGN;c+IJ@v#hBFUO<,c<|?/c3GjUwvNADUQN8Kai*GJjpV.TSd^h#wz>$*sF{H@t9UBFpNgs=:qjefRVyLUJ18kIr4b&),##mbmj&;Qeb<,=G>&_Uv:Z.uzp~%!*D7(.AfTQ:)2G;,7pHH`9)_XyFIW]MaWr&r~0<uyE0(?2JC>a+>U1q&eX^sf94Z*YF`G^#B("=QQqBbg8
sGb2IxS2$x7y`D3V18`5&RyA;ZgG[(`/c((8^&WQ,)up+R=(2S0f1L76q2fi`/]P_v
,PC=j@:,DVJ%S`A[wg5n`C1m+:139YCiki-&/UE.6WHm?ybD75_XWRbjq8XO(S#o{u~dB&My7KCkM-l?,MBn{1=c/dBC="+BychfdFwr&.{mlB9BxW=JG
uPeoVAIm:VwK4r~dPL1&yK>!Zg=IoM[!5b$#R>
o7MAsH!`5qc1OhKCR5A`e*`W,EP/lrBUJK2%EE+J<`b|4+S>
-&9d0yQBtn!3EBWv
Q~Z>_|=|R0O5eoxp?rx%NRBZ>X).PkYWc6_
:!fF3,g}rSB729s0LH7{4~k#4a)A6U@v:C]>2fMF8w0&]qEP[sc0^4XhIgv(.=O6LjLx0BRn+r]5`JKh?zTqGhd.7pH6=4JQP{c1o12<a/_B,^jG@8.-]RhRKa.SBSbL(6.f;@
-O>%l`nE)hY7khyfAN}[gTA@hCo7/YbY4hcW>ciDBKE]$AoW`#Zj!UY[xLHd1"`5I@pFK;jqh:E^"4)15Owx06?vd]`^MuXP&[L.!70WuR,L/Su6Ze,4z8S^vlKVJv}1=1#32$fF89>*|bm7Y#q;."1]S1KAigi@5AB.Yv*+L/1?PK.ELz(5%]*`<QT[s-_a@xsO<U*2}b=1Kk?Un2+gS
I)Q7YDx#:LTCI#R$mbK/(:0jtg~@hdq]-kc><3D(NCXE/PZC?YmVZ!`-gEK;WJ^%f)vP}/*ek^&JLdsqfyuCQ#8YPZ4+>w{0h.obcv!:5iP.)5#yr+m4>u-j^iK^,<Ws&tv<w-`1waV>$tw7l/Vtc?Buo1IB+tHf86n.;0);}%W9EPNfK"NBpWh@5heFOf46%=9#~@GaTB-[Zb^VUu!
N*3M_&oQtVaoJp!PU>6*/W{iH4hcUNvDok#u4hU@[%#%`U+IN8LOxZ(0FJ(o(`M62!;
2v-,[#b&Pm*7AmtY?!C2#8S3RU].]y2x^xo+Us$2AtsPwnzWvoxI`ITIFjut*7TwdgjQpxE4;K!5Ci9M{.gZ]-
O!#B[s
$
y?Ddgv~G80A7R8A^0nlkOm+^J3;5Ff/:i9]$hxgN&GA%B/4m;MSPLEr;-KY6iMVfzI#b^!I-IkE%Be6=6<4)?WB#)d*P%P-aZ#mK#Mol;hz)d)e0IS#?f4GZmQU8zR+Jz-r9^uWyoa:I;Grn@feRxZ)NuFrF|<5s:=QQwH_>j;Yd//(o
C^PBU)saY]BB_3=R07O%[|m0Z]Czf#buh@D)f1DT<hfaLce!Qw^d++DkPsAHO;du2:mOw+=jLCU@O|9P7O$efy9Ex6D/Fo[63-GhiXgE>Q:TYN:z6a,kh@:MfGK<vk?8u/7|=Bs^AjJ6_-v~(Gnvp#y|^OXF(,M)qs7vFmp922RvEp>xA_ijGoB]NEl}InmL
!mb*t6$4fE2g/AwexxXxtcBpIs*Ko;[_1s3tW]9u*=k[5wPa6K5fF*fA_Hhd?=@,dfHFT/y:3nmQs=kX{$s#<f}8[5Gc`JLK+;F`bGjTU!Q/}/XqsCyZ|+8%W<0:2;u(N#`5;?5GB:(yWi(g>#v)XySaTt#0@XN8r-mL/=Q+hEI
6^!PA,b%)y&wr8<+nc
ssF3_33ynpYxEqO,;+YQoK_$+MoHb8a.iNAuY"ZzEsKJ<ak5&kF_qdPqZ1$X?0XC!VeB/zQd3owrh;an`[2W;^^UJaDXnVbgL2og4e,qt.7d:Xeok15crR)"5mYJ,dT
^D"DJzT:[P9N"zF::=FxY,&5aLlK?4F~o#g~%nU*GfV]`B%mxsSZdE=3d~xUN,7?G/W4yYPP#.L+LfWe-8S<^Z)q^Z#q;~k?iw088te:B~%Xwig]*
0^&*+8]b4RI$.S,/?B"$rvZF^N;,L!U&M-(b.SJ{GX4gdr<8[8[>(kj%y-4$*FyKi@FTtC]
2fO2%@N78oeq
26nR`)K<Z2f=AN4tq!U
N=dEc$BY(J
$rd/[C;aHHnu;Q!`n
fB_evRIt^|p-"ZdHQ!LO(OIxxpR`V]N{0qB|/uKgxv*Uxp?|HWvVZi%IV=]#[MbPn##LGnFSdeH/;7v%FLA^/5[u7v%!4
tw/ptUbQS{Kf[L>9]kwznw@5p;S$`ry~Pr+g9%kSTrW|D8<Pk@AdX?B/"LM:C!M?p65p+P`ZqovY:~nvED]"r$rm-;@kgO^JQu0@cfs>DHxG0-^Ay1
<vTodfcPjZwp7L~p]IdX4LGE?x*1%;Y
+WCRtuz@yif2~
=[+5KvJg6f}l$JEa&W
cb2b&lxRjpIOCNnH8XNeEUL!kKc2C#ZLO%7to3Bz/UK#)y9*Ebf3b5xw7NaJ`mbF8Qi}DaC
)C@+%C)6E1]B:*Nfn-XPRh0.3E,_ZbL+%6S
LMCAZxTLtn.2&^@
pE#YeNs.hc!8o&!Z*sx58B$dP
w3%/)R/@ZKXNgwG{Q[C)h#0Zk"Vt(r6$rX
*n@<SufEy^03^we5/2
JamtTjA4vq#o$AZ{:A*TSBY:Ze@VSG>V-=:$U3O
%F]>(Zz"yUi8YVB@c]OV
V3,a^tp4Aw3U_G@+MT.(/F+.IYFKYiZVmtIMC';break;default:$f=null;break;}if(!$f){http_response_code(404);exit;}if(in_array($re,["png","ico"]))$f=base64_decode($f);else$f=decompress_string($f);echo$f;exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));if(!defined("SID")){ini_set("session.use_trans_sid","0");session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Ie);$_POST=remove_slashes($_POST,$Ie);$_COOKIE=remove_slashes($_COOKIE,$Ie);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-TW'=>'繁體中文','ko'=>'한국어',];private$language;private$translations;private
static$instance=null;static
function
create($ch){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($ch);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($ch){$this->language=$ch;}function
getLanguage(){return$this->language;}function
setTranslations(array$Pn){$this->translations=$Pn;}function
getTranslations(){return$this->translations;}function
translate($u,$Mi=null){$u=$this->convertTranslationKey($u);$On=isset($this->translations[$u])?$this->translations[$u]:$u;$ch=$this->language;if(is_array($On)){$nk=($Mi==1?0:($ch=='cs'||$ch=='sk'?($Mi&&$Mi<5?1:2):($ch=='fr'?(!$Mi?0:1):($ch=='pl'?($Mi%10>1&&$Mi%10<5&&$Mi/10%10!=1?1:2):($ch=='sl'?($Mi%100==1?0:($Mi%100==2?1:($Mi%100==3||$Mi%100==4?2:3))):($ch=='lt'?($Mi%10==1&&$Mi%100!=11?0:($Mi%10>1&&$Mi/10%10!=1?1:2)):($ch=='lv'?($Mi%10==1&&$Mi%100!=11?0:($Mi?1:2)):($ch=='ro'?(!$Mi||($Mi%100>0&&$Mi%100<20)?1:2):($ch=='bs'||$ch=='hr'||$ch=='ru'||$ch=='sr'||$ch=='uk'?($Mi%10==1&&$Mi%100!=11?0:($Mi%10>1&&$Mi%10<5&&$Mi/10%10!=1?1:2)):1)))))))));$On=$On[$nk];}$On=str_replace("'",'’',$On);$Ta=func_get_args();array_shift($Ta);$Ye=str_replace("%d","%s",$On);if($Ye!=$On)$Ta[0]=format_number($Mi);return
vsprintf($Ye,$Ta);}function
convertTranslationKey($u){static$Rd=null;if(is_string($u)){if(!$Rd)$Rd=get_translations("en");if(($s=array_search($u,$Rd))!==false)$u=$s;elseif(($s=get_plural_translation_id($u))!==null)$u=$s;}return$u;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($u,$Mi=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$eb=get_available_languages();if(count($eb)==1)return[];$B=[];foreach(Locale::$Languages
as$ch=>$En){if(isset($eb[$ch]))$B[$ch]=$En;}return$B;}function
language_select(){$B=get_language_options();if(!$B)return;echo"<form action='' method='post'>\n",html_select("lang",$B,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(80),"' class='button hidden'>\n",input_token(),"</form>\n";}$eb=get_available_languages();$ch=array_keys($eb)[0];$sk=null;if(isset($_POST["lang"])&&isset($eb[$_POST["lang"]])&&verify_token()){$sk=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$yl=($ua=Settings::readParameter("lang"))!==null?$ua:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($yl!==null&&isset($eb[$yl]))$ch=$yl;elseif(isset($_SESSION["lang"])&&isset($eb[$_SESSION["lang"]]))$ch=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$wa=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$z,PREG_SET_ORDER);foreach($z
as$y)$wa[$y[1]]=(isset($y[3])?$y[3]:1);arsort($wa);foreach($wa
as$u=>$Gk){if(isset($eb[$u])){$ch=$u;break;}$u=preg_replace('~-.*~','',$u);if(!isset($wa[$u])&&isset($eb[$u])){$ch=$u;break;}}}Locale::create($ch);abstract
class
Connection{protected$flavor=null;protected$version;protected$affectedRows=0;protected$errno=0;protected$error="";protected$multiResult;private
static$instance=null;static
function
create(){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static();}static
function
createSecondary(){return
new
static();}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}static
function
exists(){return
self::$instance!==null;}protected
function
__construct(){}function
getDefaultServerName(){return"";}function
openPasswordless($M,$U,$D,$Hm=true){$zf=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($D!=""&&($Hm||$zf)&&$this->open($M,$U,"")){$H=Admin::get()->verifyDefaultPassword($D);if($H!==true){$this->error=$H;return
false;}return
true;}return$this->open($M,$U,$D);}abstract
function
open($M,$U,$D);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($Co){return
version_compare($this->version,$Co)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($Fa){$this->affectedRows=$Fa;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($j){$this->error=$j;}abstract
function
selectDatabase($_);abstract
function
quote($P);function
formatValue($X,array$k){return$X;}abstract
function
query($F,$co=false);function
getQueryInfo(){return
null;}function
getResult($F,$k=0){return$this->getValue($F,$k);}function
getValue($F,$ze=0){$H=$this->query($F);if(!is_object($H))return
false;$J=$H->fetchRow();return$J?$J[$ze]:false;}function
multiQuery($F){$this->multiResult=$this->query($F);return(bool)($this->multiResult);}function
storeResult($H=null){return$this->multiResult;}function
nextResult(){return
false;}}abstract
class
Result{protected$rowsCount;function
__construct($ul){$this->rowsCount=$ul;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($A){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($Ed,$U,$D,array$B=[]){$B[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new
PDO($Ed,$U,$D,$B);}catch(Exception$he){$this->error=$he->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($P){return$this->pdo->quote($P);}function
query($F,$co=false){$Em=$this->pdo->query($F);$this->error="";if(!$Em){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(122);return
false;}$H=new
PdoResult($Em);$this->storeResult($H);return$H;}function
storeResult($H=null){if(!$H){$H=$this->multiResult;if(!$H)return
false;}if($H->getColumnsCount())return$H;$this->affectedRows=$H->getAffectedRowsCount();return
true;}function
nextResult(){return$this->multiResult&&$this->multiResult->nextRowset();}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$Em){parent::__construct(max($Em->columnCount()?$Em->rowCount():0,0));$this->statement=$Em;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($ki){$H=$this->statement->fetch($ki);return$H?array_map([$this,'unresource'],$H):$H;}private
function
unresource($X){return
is_resource($X)?stream_get_contents($X):$X;}function
fetchField(){$J=$this->statement->getColumnMeta($this->offset++);if($J===false)return
false;$T=$J["pdo_type"];$J["type"]=($T==PDO::PARAM_INT?0:15);$J["charsetnr"]=($T==\PDO::PARAM_LOB||(isset($J["flags"])&&in_array("blob",(array)$J["flags"]))?63:0);return(object)$J;}function
seek($A){for($p=0;$p<$A;$p++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($q,$_,array$se){self::$drivers[$q]=$_;self::$extensions[$q]=$se;}static
function
setName($q,$_){if(isset(self::$drivers[$q]))self::$drivers[$q]=$_;}static
function
get($q){return
isset(self::$drivers[$q])?self::$drivers[$q]:null;}static
function
getList(){return
self::$drivers;}static
function
getExtensions($q){return
isset(self::$extensions[$q])?self::$extensions[$q]:[];}}function
get_drivers(){return
Drivers::getList();}abstract
class
Driver{static$EnumLengthPattern="'(?:''|[^'\\\\]|\\\\.)*'";protected$connection;protected$admin;protected$types=[];protected$unsigned=[];protected$generated=[];protected$operators=[];protected$likeOperator="LIKE %%";protected$functions=[];protected$grouping=[];protected$inOut=["IN","OUT","INOUT"];protected$onActions=["RESTRICT","CASCADE","SET NULL","SET DEFAULT","NO ACTION"];protected$partitionBy=[];protected$insertFunctions=[];protected$editFunctions=[];protected$systemDatabases=[];protected$systemSchemas=[];private
static$instance=null;static
function
create(Connection$e,$Ca){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($e,$Ca);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct(Connection$e,$Ca){$this->connection=$e;$this->admin=$Ca;}function
getTypes(){return
call_user_func_array("array_merge",array_values($this->types));}function
getStructuredTypes(){return
array_map("array_keys",$this->types);}function
setUserTypes(array$bo){$this->types[lang(109)]=array_flip($bo);}function
getUserTypes(){$u=lang(109);return
array_keys(isset($this->types[$u])?$this->types[$u]:[]);}function
getUnsigned(){return$this->unsigned;}function
getGenerated(){return$this->generated;}function
getOperators(){return$this->operators;}function
getLikeOperator(){return$this->likeOperator;}function
getFunctions(){return$this->functions;}function
getGrouping(){return$this->grouping;}function
getInOut(){return$this->inOut;}function
getOnActions(){return$this->onActions;}function
getPartitionBy(){return$this->partitionBy;}function
getInsertFunctions(){return$this->insertFunctions;}function
getEditFunctions(){return$this->editFunctions;}function
getSystemDatabases(){return$this->systemDatabases;}function
getSystemSchemas(){return$this->systemSchemas;}function
getUnconvertFunction(array$k){return"";}function
select($Q,array$L,array$Z,array$rf,array$pj=[],$w=1,$Hj=0,$yk=false){$Cg=(count($rf)<count($L));$F="SELECT".limit(($_GET["page"]!="last"&&$w&&$rf&&$Cg&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$L)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($rf&&$Cg?"\nGROUP BY ".implode(", ",$rf):"").($pj?"\nORDER BY ".implode(", ",$pj):""),$w,($Hj?$w*$Hj:0),"\n");$Dm=microtime(true);$I=$this->connection->query($F);if($yk)echo
Admin::get()->formatSelectQuery($F,$Dm,!$I);return$I;}function
delete($Q,$Kk,$w=0){$F="FROM ".table($Q);return
queries("DELETE".($w?limit1($Q,$F,$Kk):" $F$Kk"));}function
update($Q,array$G,$Kk,$w=0,$Rl="\n"){$Y=[];foreach($G
as$u=>$W)$Y[]="$u = $W";$F=table($Q)." SET$Rl".implode(",$Rl",$Y);return
queries("UPDATE".($w?limit1($Q,$F,$Kk,$Rl):" $F$Kk"));}function
insert($Q,array$G){return
queries("INSERT INTO ".table($Q).($G?" (".implode(", ",array_keys($G)).")\nVALUES (".implode(", ",$G).")":" DEFAULT VALUES").$this->getInsertReturningSql($Q));}function
getInsertReturningSql($Q){return"";}function
insertUpdate($Q,array$Pk,array$E){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($F,$Cn){return
null;}function
convertSearch($r,array$Z,array$k){return$r;}function
getNull(){return"NULL";}function
getTypeName(stdClass$k){return
isset($k->native_type)?$k->native_type:"";}function
quoteBinary($P){return
q($P);}function
warnings(){return
null;}function
tableHelp($_,$Bg=false){return
null;}function
supportsIndex(array$an){return!is_view($an);}function
getIndexAlgorithms(array$an){return[];}function
getIndexOpclasses(){return[];}function
getInheritedTables($Q){return[];}function
getParentTables($Q){return[];}function
isPartition($Q){return
false;}function
getPartitionsInfo($Q){return[];}function
hasCStyleEscapes(){return
false;}function
engines(){return[];}function
explodeArrayValue($X,$T,&$zl){return[];}function
implodeArrayValues(array$Y,$T){return"";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$La=[];$K=get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(DIALECT=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->connection);foreach($K
as$J){$J["null"]=($J["nullable"]=="YES");$La[$J["tab"]][]=$J;}return$La;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($M,$U,$D){mysqli_report(MYSQLI_REPORT_OFF);list($Nf,$mk)=host_port($M);$u=Admin::get()->getConfig()->getSslKey();$Bb=Admin::get()->getConfig()->getSslCertificate();$zb=Admin::get()->getConfig()->getSslCaCertificate();$Bm=$u||$Bb||$zb;if($Bm){$this->mysqli->ssl_set($u,$Bb,$zb,null,null);$Qe=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$Qe=0;$rc=@$this->mysqli->real_connect(($M!=""?$Nf:ini_get("mysqli.default_host")),($M.$U!=""?$U:ini_get("mysqli.default_user")),($M.$U.$D!=""?$D:ini_get("mysqli.default_pw")),null,(is_numeric($mk)?(int)$mk:ini_get("mysqli.default_port")),(!is_numeric($mk)?$mk:null),$Qe);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($rc){$ig=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$ig);$this->flavor=str_contains($ig,"MariaDB")?"mariadb":null;}return$rc;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($_){return$this->mysqli->select_db($_);}function
setCharset($Eb){if($this->mysqli->set_charset($Eb))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $Eb");}function
quote($P){return"'".$this->mysqli->escape_string($P)."'";}function
query($F,$co=false){$H=$this->mysqli->query($F);return
is_object($H)?new
MySqlResult($H):$H;}function
getQueryInfo(){return$this->mysqli->info;}function
multiQuery($F){return$this->mysqli->multi_query($F);}function
storeResult($H=null){$H=$this->mysqli->store_result();if(!$H)return
false;return
new
MySqlResult($H);}function
nextResult(){return$this->mysqli->more_results()&&$this->mysqli->next_result();}}class
MySqlResult
extends
Result{private$resource;function
__construct(mysqli_result$gl){parent::__construct($gl->num_rows);$this->resource=$gl;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($A){return$this->resource->data_seek($A);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($M,$U,$D){list($Nf,$mk)=host_port($M);$Ed="mysql:charset=utf8".($Nf!=""?";host=$Nf":"").($mk?(is_numeric($mk)?";port=":";unix_socket=").$mk:"");$B=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];$u=Admin::get()->getConfig()->getSslKey();if($u)$B[PDO::MYSQL_ATTR_SSL_KEY]=$u;$Bb=Admin::get()->getConfig()->getSslCertificate();if($Bb)$B[PDO::MYSQL_ATTR_SSL_CERT]=$Bb;$zb=Admin::get()->getConfig()->getSslCaCertificate();if($zb)$B[PDO::MYSQL_ATTR_SSL_CA]=$zb;$Xn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Xn!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$B[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$Xn;if(!$this->dsn($Ed,$U,$D,$B))return
false;$Do=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($Do,"MariaDB")?"mariadb":null;return
true;}function
setCharset($Eb){return(bool)$this->query("SET NAMES $Eb");}function
selectDatabase($_){return(bool)$this->query("USE ".idf_escape($_));}function
query($F,$co=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$co);return
parent::query($F,$co);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(123)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(124)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(125)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(126)=>["enum"=>65535,"set"=>64,],lang(127)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(128)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$Gh=$e->isMariaDB();if($e->isMinVersion($Gh?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($Gh?"10.2":"5.7.8"))$this->types[lang(125)]["json"]=4294967295;if($Gh&&$e->isMinVersion("10.7")){$this->types[lang(125)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($Gh&&$e->isMinVersion("10.5")){$this->types[lang(129)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(129)]["inet4"]=15;}if($e->isMinVersion($Gh?"11.7":"9"))$this->types[lang(123)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($Q,array$G){return($G?parent::insert($Q,$G):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
getUnconvertFunction(array$k){if(preg_match("~binary~",$k["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($k["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($k["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
getTypeName(stdClass$k){$bo=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$T=isset($bo[$k->type])?$bo[$k->type]:"";return
parent::getTypeName($k)?:($k->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$T):$T);}function
quoteBinary($P){return"X".q(bin2hex($P));}function
insertUpdate($Q,array$Pk,array$E){$d=array_keys(reset($Pk));$tk="INSERT INTO ".table($Q)." (".implode(", ",$d).") VALUES\n";$Y=[];foreach($d
as$u)$Y[$u]="$u = VALUES($u)";$Nm="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=[];$v=0;foreach($Pk
as$G){$X="(".implode(", ",$G).")";if($Y&&(strlen($tk)+$v+strlen($X)+strlen($Nm)>1e6)){if(!queries($tk.implode(",\n",$Y).$Nm))return
false;$Y=[];$v=0;}$Y[]=$X;$v+=strlen($X)+2;}return
queries($tk.implode(",\n",$Y).$Nm);}function
slowQuery($F,$Cn){$Gh=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($Gh?"10.1.2":"5.7.8"))return
null;if($Gh)return"SET STATEMENT max_statement_time=$Cn FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$y))return"$y[1] /*+ MAX_EXECUTION_TIME(".($Cn*1000).") */ $y[2]";else
return
null;}function
convertSearch($r,array$Z,array$k){return(preg_match('~char|text|enum|set~',$k["type"])&&!preg_match("~^utf8~",$k["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($r USING ".charset($this->connection).")":$r);}function
warnings(){$H=$this->connection->query("SHOW WARNINGS");if($H&&$H->getRowsCount()){ob_start();print_select_result($H);return
ob_get_clean();}return
null;}function
tableHelp($_,$Bg=false){$Gh=$this->connection->isMariaDB();if(DB=="information_schema"){$_=strtolower($_);return$Gh?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($_,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$_-table":"information-schema-".str_replace("_","-",$_)."-table.html";}if(DB=="performance_schema")return$Gh?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$_-table":"performance-schema-".str_replace("_","-",$_)."-table.html";if(DB=="sys"){if($Gh)return"reference/system-tables/sys-schema/";return"sys-".strtolower(str_replace("_","-",preg_replace('~^x\$~','',$_))).".html";}if(DB=="mysql")return$Gh?"reference/system-tables/the-mysql-database-tables/mysql-$_".str_starts_with($_,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($Q){$ff="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$H=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $ff ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$H)return[];$ig=["partition_by"=>$H[0],"partition"=>$H[1],"partitions"=>$H[2],];$Wj=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $ff AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$ig["partition_names"]=array_keys($Wj);$ig["partition_values"]=array_values($Wj);return$ig;}function
getIndexAlgorithms(array$an){return
preg_match('~^(MEMORY|NDB)$~',$an["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$xb;if($xb===null){$zm=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$xb=(strpos($zm,'NO_BACKSLASH_ESCAPES')===false);}return$xb;}function
engines(){$Xd=[];foreach(get_rows("SHOW ENGINES")as$J){if(preg_match("~YES|DEFAULT~",$J["Support"]))$Xd[]=$J["Engine"];}return$Xd;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($r){return"`".str_replace("`","``",$r)."`";}function
table($r){return
idf_escape($r);}function
connect($E=false,&$j=null){$e=$E?MySqlConnection::create():MySqlConnection::createSecondary();list($M,$U,$D)=Admin::get()->getCredentials();if(!$e->openPasswordless($M,$U,$D,false)){$j=$e->getError();if(function_exists('iconv')&&!is_utf8($j)&&strlen($vl=iconv("windows-1252","utf-8//IGNORE",$j))>strlen($j))$j=$vl;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($E&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$M,"MariaDB");}return$e;}function
get_databases($Se){$g=get_session("dbs");if($g===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Dm=microtime(true);$g=($Se?slow_query($F):get_vals($F));if(microtime(true)-$Dm>0.1){restart_session();set_session("dbs",$g);stop_session();}}return$g;}function
limit($F,$Z,$w,$A=0,$Rl=" "){return" $F$Z".($w?$Rl."LIMIT $w".($A?" OFFSET $A":""):"");}function
limit1($Q,$F,$Z,$Rl="\n"){return
limit($F,$Z,1,0,$Rl);}function
db_collation($h,$Wb){$I=null;$Dc=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$Dc,$y))$I=$y[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$Dc,$y))$I=$Wb[$y[1]][-1];return$I;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables($g){$I=[];foreach($g
as$h)$I[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$I;}function
table_status($_="",$xe=false){if($xe)$F="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options, TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($_!=""?"AND TABLE_NAME = ".q($_):"ORDER BY Name");else$F="SHOW TABLE STATUS".($_!=""?" LIKE ".q(addcslashes($_,"%_\\")):"");$S=[];foreach(get_rows($F)as$J){if($J["Engine"]=="InnoDB")$J["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$J["Comment"]);if(!isset($J["Engine"]))$J["Comment"]="";if($_!="")$J["Name"]=$_;$S[$J["Name"]]=$J;}return$S;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support($R){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$R["Engine"]);}function
fields($Q){$Gh=Connection::get()->isMariaDB();$I=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$J){$k=$J["COLUMN_NAME"];$T=preg_replace('~\s?/\*.+\*/~U',"",$J["COLUMN_TYPE"]);$te=$J["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$te,$kf);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$T,$ao);$i=$Gh&&$J["COLUMN_DEFAULT"]=="NULL"?null:$J["COLUMN_DEFAULT"];if($i!==null){$Fg=preg_match('~(text|json)~',$ao[1]);if(!$Gh&&$Fg)$i=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($i));if($Gh||$Fg){$i=preg_replace_callback("~^'(.*)'$~",function($z){return
stripslashes(str_replace("''","'",$z[1]));},$i);}if(!$Gh&&preg_match('~binary~',$ao[1])&&preg_match('~^0x(\w*)$~',$i,$z))$i=pack("H*",$z[1]);}$mf=$J["GENERATION_EXPRESSION"];if(!$Gh)$mf=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($mf));$I[$k]=["field"=>$k,"full_type"=>$T,"type"=>$ao[1],"length"=>$ao[2],"unsigned"=>ltrim($ao[3].$ao[4]),"default"=>($kf?$mf:$i),"null"=>($J["IS_NULLABLE"]=="YES"),"auto_increment"=>($te=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$te,$ao)?$ao[1]:""),"collation"=>$J["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$J["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$J["COLUMN_COMMENT"],"primary"=>($J["COLUMN_KEY"]=="PRI"),"generated"=>($kf[1]=="PERSISTENT"?"STORED":$kf[1]),];}return$I;}function
indexes($Q,$e=null){$I=[];foreach(get_rows("SHOW INDEX FROM ".table($Q),$e)as$J){$_=$J["Key_name"];$I[$_]["type"]=($_=="PRIMARY"?"PRIMARY":($J["Index_type"]=="FULLTEXT"?"FULLTEXT":($J["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$J["Index_type"])?$J["Index_type"]:"INDEX"):"UNIQUE")));$I[$_]["columns"][]=$J["Column_name"];$I[$_]["lengths"][]=($J["Index_type"]=="SPATIAL"?null:$J["Sub_part"]);$I[$_]["descs"][]=($J["Collation"]=="D"?'1':null);$I[$_]["algorithm"]=$J["Index_type"];}return$I;}function
foreign_keys($Q){static$ck='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$I=[];$Fc=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if($Fc){$bj=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($ck) FOREIGN KEY ?\\(((?:$ck,? ?)+)\\) REFERENCES ($ck)(?:\\.($ck))? \\(((?:$ck,? ?)+)\\)(?: ON DELETE ($bj))?(?: ON UPDATE ($bj))?~",$Fc,$z,PREG_SET_ORDER);foreach($z
as$y){preg_match_all("~$ck~",$y[2],$tm);preg_match_all("~$ck~",$y[5],$rn);$I[idf_unescape($y[1])]=["db"=>idf_unescape($y[4]!=""?$y[3]:$y[4]),"table"=>idf_unescape($y[4]!=""?$y[4]:$y[3]),"source"=>array_map('AdminNeo\idf_unescape',$tm[0]),"target"=>array_map('AdminNeo\idf_unescape',$rn[0]),"on_delete"=>($y[6]?:"RESTRICT"),"on_update"=>($y[7]?:"RESTRICT"),];}}return$I;}function
backward_keys($Q){$F="SELECT constraint_name, table_schema, table_name, column_name, referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = ".q(Admin::get()->getDatabase())."
AND referenced_table_schema = ".q(Admin::get()->getDatabase())."
AND referenced_table_name = ".q($Q)."
ORDER BY ordinal_position";return
get_rows($F,null,"");}function
view($_){$L=Connection::get()->getValue("SHOW CREATE VIEW ".table($_),1);$yh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$L=preg_replace("~^$yh\\s+AS\\s+~isU","",$L);return["select"=>format_sql($L)];}function
collations(){$I=[];$F=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($F)as$J){if($J["Default"])$I[$J["Charset"]][-1]=$J["Collation"];else$I[$J["Charset"]][]=$J["Collation"];}ksort($I);foreach($I
as$u=>$W)sort($I[$u]);return$I;}function
information_schema($h){return($h=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$h=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($h,$Vb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Vb?" COLLATE ".q($Vb):""));}function
drop_databases($g){$I=apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$I;}function
rename_database($_,$Vb){$I=false;if(create_database($_,$Vb)){$S=[];$Go=[];foreach(tables_list()as$Q=>$T){if($T=='VIEW')$Go[]=$Q;else$S[]=$Q;}$I=(!$S&&!$Go)||move_tables($S,$Go,$_);drop_databases($I?[DB]:[]);}return$I;}function
auto_increment(){$cb=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$s){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$s["columns"],true)){$cb="";break;}if($s["type"]=="PRIMARY")$cb=" UNIQUE";}}return" AUTO_INCREMENT$cb";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$b=[];foreach($l
as$k){if($k[1]){$i=$k[1][3];if(str_contains($i," GENERATED")){$k[1][3]=Connection::get()->isMariaDB()?"":$k[1][2];$k[1][2]=$i;}$b[]=($Q!=""?($k[0]!=""?"CHANGE ".idf_escape($k[0]):"ADD"):" ")." ".implode($k[1]).($Q!=""?$k[2]:"");}else$b[]="DROP ".idf_escape($k[0]);}$b=array_merge($b,$Ue);$O=($gc!==null?" COMMENT=".q($gc):"").($Wd?" ENGINE=".q($Wd):"").($Vb?" COLLATE ".q($Vb):"").($bb!=""?" AUTO_INCREMENT=$bb":"");if($Vj){$Wj=[];if($Vj["partition_by"]=='RANGE'||$Vj["partition_by"]=='LIST'){foreach($Vj["partition_names"]as$u=>$W){$X=$Vj["partition_values"][$u];$Wj[]="\n  PARTITION ".idf_escape($W)." VALUES ".($Vj["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$O
.="\nPARTITION BY {$Vj["partition_by"]}({$Vj["partition"]})";if($Wj)$O
.=" (".implode(",",$Wj)."\n)";elseif($Vj["partitions"])$O
.=" PARTITIONS ".(int)$Vj["partitions"];}elseif($Vj===null)$O
.="\nREMOVE PARTITIONING";if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)$O");if($Q!=$_)$b[]="RENAME TO ".table($_);if($O)$b[]=ltrim($O);return!$b||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b));}function
alter_indexes($Q,$b){$Db=[];foreach($b
as$u=>$W)$Db[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return(bool)queries("ALTER TABLE ".table($Q).implode(",",$Db));}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($Go){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$Go)));}function
drop_tables($S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables($S,$Go,$rn){$dl=[];foreach($S
as$Q)$dl[]=table($Q)." TO ".idf_escape($rn).".".table($Q);if(!$dl||queries("RENAME TABLE ".implode(", ",$dl))){$dd=[];foreach($Go
as$Q)$dd[table($Q)]=view($Q);Connection::get()->selectDatabase($rn);$h=idf_escape(DB);foreach($dd
as$_=>$Eo){if(!queries("CREATE VIEW $_ AS ".str_replace(" $h."," ",$Eo["select"]))||!queries("DROP VIEW $h.$_"))return
false;}return
true;}return
false;}function
copy_tables($S,$Go,$rn){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$_=($rn==DB?table("copy_$Q"):idf_escape($rn).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $_"))||!queries("CREATE TABLE $_ LIKE ".table($Q))||!queries("INSERT INTO $_ SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$J){$Sn=$J["Trigger"];if(!queries("CREATE TRIGGER ".($rn==DB?idf_escape("copy_$Sn"):idf_escape($rn).".".idf_escape($Sn))." $J[Timing] $J[Event] ON $_ FOR EACH ROW\n$J[Statement];"))return
false;}}foreach($Go
as$Q){$_=($rn==DB?table("copy_$Q"):idf_escape($rn).".".table($Q));$Eo=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $_"))||!queries("CREATE VIEW $_ AS $Eo[select]"))return
false;}return
true;}function
trigger($_,$Q){if($_=="")return[];$K=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($_));return
reset($K);}function
triggers($Q){$I=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$J)$I[$J["Trigger"]]=[$J["Timing"],$J["Event"]];return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["FOR EACH ROW"],];}function
routine($_,$T){if($_=="")return[];$l=get_rows("SELECT
	PARAMETER_NAME field,
	DATA_TYPE type,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^(]+\\\\(?|\\\\)$', '') length,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^ ]+ ', '') `unsigned`,
	1 `null`,
	DTD_IDENTIFIER full_type,
	".($T=="FUNCTION"?"''":"PARAMETER_MODE")." `inout`,
	CHARACTER_SET_NAME collation
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND SPECIFIC_NAME = ".q($_)."
ORDER BY ORDINAL_POSITION");$I=Connection::get()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($_))->fetchAssoc();if($l&&$l[0]['field']=='')$I['returns']=array_shift($l);$I['fields']=$l;return$I;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return[];}function
routine_id($_,$J){return
idf_escape($_);}function
last_id($H){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$F);}function
found_rows(array$R,array$Z){return$R["Engine"]=="InnoDB"&&!$Z?(int)$R["Rows"]:null;}function
format_sql($F){$yh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Tg='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$F=preg_replace("~($yh)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$F);$F=preg_replace("~($yh)\\s+($Tg)~isU","$1\n$2",$F);$F=preg_replace("~($yh),~isU","$1,\n  ",$F);return$F;}function
create_sql($Q,$bb,$Km){$F=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if(!$bb)$F=preg_replace('~ AUTO_INCREMENT=\d+~','',$F);return!str_contains($F,"\n")?format_sql($F):$F;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
create_database_sql($Rc,$Km=""){$_=idf_escape($Rc);$ec="";if(str_contains($Km,"CREATE")&&($Dc=Connection::get()->getValue("SHOW CREATE DATABASE $_",1))){set_utf8mb4($Dc);if($Km=="DROP+CREATE")$ec="DROP DATABASE IF EXISTS $_;\n";$ec
.="$Dc;\n";}return$ec;}function
use_sql($Rc,$Km=""){return"USE ".idf_escape($Rc).";\n";}function
trigger_sql($Q){$xm="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$J)$xm
.="\nCREATE TRIGGER ".idf_escape($J["Trigger"])." $J[Timing] $J[Event] ON ".table($J["Table"])." FOR EACH ROW\n$J[Statement];;\n";return$xm;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$k){if(preg_match("~binary~",$k["type"]))return"HEX(".idf_escape($k["field"]).")";if($k["type"]=="bit")return"BIN(".idf_escape($k["field"])." + 0)";if($k["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($k["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($k["field"]).")";return
null;}function
unconvert_field(array$k,$I){if(preg_match("~binary~",$k["type"]))$I="UNHEX($I)";if($k["type"]=="bit")$I="CONVERT(b$I, UNSIGNED)";if($k["type"]=="vector")$I=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($I)";if(preg_match("~geometry|point|linestring|polygon~",$k["type"])){$tk=(Connection::get()->isMinVersion("8")?"ST_":"");$I=$tk."GeomFromText($I, $tk"."SRID($k[field]))";}return$I;}function
support($ye){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.8.1":"8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').(!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8")?'|fast_status':'').')$~',$ye);}function
kill_process($W){return
queries("KILL ".number($W));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}Drivers::add("pgsql","PostgreSQL",["PgSQL","PDO_PgSQL"]);if(isset($_GET["pgsql"])){define("AdminNeo\DRIVER","pgsql");define("AdminNeo\DIALECT","pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","PgSQL");class
PgSqlConnectionBase
extends
Connection{var$timeout=0;private$connection;private$connectionString;private$hasDefaultDatabase;function
open($M,$U,$D){$h=Admin::get()->getDatabase();set_error_handler(function($ae,$j){if(ini_bool("html_errors"))$j=html_entity_decode(strip_tags($j));$j=preg_replace('~^[^:]*: ~','',$j);$this->error=$j;});list($Nf,$mk)=host_port($M);$this->connectionString=($Nf!=""?"host=$Nf ":"").($mk?"port=$mk ":"")."user='".addcslashes($U,"'\\")."' password='".addcslashes($D,"'\\")."'";$Cm=Admin::get()->getConfig()->getSslMode();if($Cm)$this->connectionString
.=" sslmode='$Cm'";$this->connection=@pg_connect("$this->connectionString dbname='".($h!=""?addcslashes($h,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->connection&&$h!=""){$this->hasDefaultDatabase=false;$this->connection=@pg_connect("$this->connectionString dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}else$this->hasDefaultDatabase=true;restore_error_handler();if($this->connection){$Do=$this->getValue("SELECT version()");$this->flavor=str_contains($Do,"CockroachDB")?"cockroach":null;$this->version=preg_replace('~^\D*([\d.]+[-\w]*).*~',"$1",$Do);pg_set_client_encoding($this->connection,"UTF8");}return(bool)$this->connection;}function
quote($P){return(function_exists('pg_escape_literal')?pg_escape_literal($this->connection,$P):"'".pg_escape_string($this->connection,$P)."'");}function
formatValue($X,array$k){if($k["type"]=="bytea"&&$X!==null)return
pg_unescape_bytea($X);return
parent::formatValue($X,$k);}function
selectDatabase($_){if($_==Admin::get()->getDatabase())return$this->hasDefaultDatabase;$I=@pg_connect("$this->connectionString dbname='".addcslashes($_,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($I)$this->connection=$I;return(bool)$I;}function
close(){$this->connection=@pg_connect("$this->connectionString dbname='postgres'");}function
query($F,$co=false){if(!$this->connection){$this->error="Invalid connection.";return
false;}$H=@pg_query($this->connection,$F);$this->error="";if(!$H){$this->error=pg_last_error($this->connection);$I=false;}elseif(!pg_num_fields($H)){$this->affectedRows=pg_affected_rows($H);$I=true;}else$I=new
PgSqlResult($H);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$I;}function
getValue($F,$ze=0){$H=$this->query($F);return
is_object($H)?$H->fetchValue($ze):false;}function
warnings(){if(PHP_VERSION_ID>=70100){$Mo=pg_last_notice($this->connection,PGSQL_NOTICE_ALL);if(!$Mo)return
null;$Mo=implode("\n",$Mo);pg_last_notice($this->connection,PGSQL_NOTICE_CLEAR);}else{$Mo=pg_last_notice($this->connection);if(!$Mo)return
null;}return
nl2br(h($Mo));}function
copyFrom($Q,array$K){$this->error="";set_error_handler(function($ae,$j){$this->error=ini_bool("html_errors")?html_entity_decode($j):$j;});$H=pg_copy_from($this->connection,$Q,$K);restore_error_handler();return$H;}}class
PgSqlResult
extends
Result{private$resource;private$offset=0;function
__construct($gl){parent::__construct(pg_num_rows($gl));$this->resource=$gl;}function
fetchAssoc(){return
pg_fetch_assoc($this->resource);}function
fetchRow(){return
pg_fetch_row($this->resource);}function
fetchValue($ze){return$this->getRowsCount()?pg_fetch_result($this->resource,0,$ze):false;}function
fetchField(){$c=$this->offset++;$_=pg_field_name($this->resource,$c);if($_===false)return
false;$T=pg_field_type($this->resource,$c);if($T===false)return
false;$uj=pg_field_table($this->resource,$c);return(object)['orgtable'=>($uj!==false?$uj:""),'name'=>$_,'native_type'=>$T,'type'=>(preg_match(number_type(),$T)?0:15),'charsetnr'=>($T=="bytea"?63:0),];}}}elseif(extension_loaded("pdo_pgsql")){define("AdminNeo\DRIVER_EXTENSION","PDO_PgSQL");class
PgSqlConnectionBase
extends
PdoConnection{var$timeout=0;function
open($M,$U,$D){$h=Admin::get()->getDatabase();list($Nf,$mk)=host_port($M);$Ed="pgsql:".($Nf!=""?"host=$Nf ":"").($mk?"port=$mk ":"")."client_encoding=utf8 dbname='".($h!=""?addcslashes($h,"'\\"):"postgres")."'";$Cm=Admin::get()->getConfig()->getSslMode();if($Cm)$Ed
.=" sslmode='$Cm'";if(!$this->dsn($Ed,$U,$D))return
false;$Do=$this->getValue("SELECT version()");$this->flavor=str_contains($Do,"CockroachDB")?"cockroach":null;return
true;}function
selectDatabase($_){return
Admin::get()->getDatabase()==$_;}function
query($F,$co=false){$I=parent::query($F,$co);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$I;}function
warnings(){return
null;}function
copyFrom($Q,array$K){$H=$this->pdo->pgsqlCopyFromArray($Q,$K);$be=$this->pdo->errorInfo();$this->error=isset($be[2])?$be[2]:"";return$H;}function
close(){}}}if(class_exists('AdminNeo\PgSqlConnectionBase')){class
PgSqlConnection
extends
PgSqlConnectionBase{private$localhostServer=false;function
open($M,$U,$D){if($M=="localhost")$this->localhostServer=true;return
parent::open($M,$U,$D);}function
getDefaultServerName(){return$this->localhostServer?"localhost":"";}function
multiQuery($F){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$F),$z)){$K=explode("\n",$z[2]);$this->affectedRows=count($K);return$this->copyFrom($z[1],$K);}return
parent::multiQuery($F);}}}class
PgSqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(123)=>["smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20,],lang(124)=>["date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0,],lang(125)=>["character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0,],lang(127)=>["bit"=>0,"bit varying"=>0,"bytea"=>0,],lang(129)=>["cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0,],lang(128)=>["box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0,],];if($e->isMinVersion("9.2")){$this->types[lang(125)]["json"]=4294967295;if($e->isMinVersion("9.4"))$this->types[lang(125)]["jsonb"]=4294967295;}if($e->isMinVersion("12")){$this->generated[]="STORED";if($e->isMinVersion("18"))$this->generated[]="VIRTUAL";}$this->operators=["=","<",">","<=",">=","!=","~","~*","!~","!~*","LIKE","LIKE %%","NOT LIKE","ILIKE","ILIKE %%","NOT ILIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["char_length","lower","upper","round","to_hex","to_timestamp",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->partitionBy=["RANGE","LIST"];if(!$e->isCockroachDB())$this->partitionBy[]="HASH";$this->insertFunctions=["char"=>"md5","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",];$this->systemDatabases=["template1"];$this->systemSchemas=["information_schema","pg_catalog","pg_toast","pg_temp_*","pg_toast_temp_*"];}function
getNsOidSql(){return"(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";}function
getInsertReturningSql($Q){$ab=array_filter(fields($Q),function($k){return$k['auto_increment'];});return
count($ab)==1?" RETURNING ".idf_escape(key($ab)):"";}function
insertUpdate($Q,array$Pk,array$E){foreach($Pk
as$G){$lo=[];$Z=[];foreach($G
as$u=>$W){$lo[]="$u = $W";if(isset($E[idf_unescape($u)]))$Z[]="$u = $W";}if(!(($Z&&queries("UPDATE ".table($Q)." SET ".implode(", ",$lo)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($G)).") VALUES (".implode(", ",$G).")")))return
false;}return
true;}function
slowQuery($F,$Cn){$this->connection->query("SET statement_timeout = ".(1000*$Cn));$this->connection->timeout=1000*$Cn;return$F;}function
convertSearch($r,array$Z,array$k){$xn="char|text";if(strpos($Z["op"],"LIKE")===false)$xn
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$xn~",$k["type"])?$r:"CAST($r AS text)");}function
quoteBinary($P){return"'\\x".bin2hex($P)."'";}function
warnings(){return$this->connection->warnings();}function
tableHelp($_,$Bg=false){$sh=["information_schema"=>"infoschema","pg_catalog"=>($Bg?"view":"catalog"),];$x=$sh[$_GET["ns"]];if($x)return"$x-".str_replace("_","-",$_).".html";return
null;}function
getInheritedTables($Q){return
get_rows("SELECT relname AS \"table\", nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($Q).(Connection::get()->isMinVersion("10")?" AND relispartition::int = 0":"")." ORDER BY 2, 1");}function
getParentTables($Q){return
get_rows("SELECT relname AS \"table\", nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
isPartition($Q){return
Connection::get()->isMinVersion("10")&&$this->connection->getValue("SELECT relispartition::int FROM pg_class WHERE oid = ".$this->tableOid($Q));}function
getPartitionsInfo($Q){if(!$this->connection->isMinVersion("10"))return[];$J=$this->connection->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($Q))->fetchAssoc();if(!$J)return[];$Xa=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = {$J["partrelid"]} AND attnum IN (".str_replace(" ",", ",$J["partattrs"]).")");$vb=['h'=>'HASH','l'=>'LIST','r'=>'RANGE'];$ig=["partition_by"=>$vb[$J["partstrat"]],"partition"=>implode(", ",array_map('AdminNeo\idf_escape',$Xa)),];$Wj=get_key_vals("SELECT relname, pg_get_expr(c.relpartbound, c.oid) AS bound FROM pg_inherits JOIN pg_class c ON inhrelid = oid WHERE inhparent = ".$this->tableOid($Q)." ORDER BY 1");$ig["partition_names"]=array_keys($Wj);$ig["partition_values"]=array_map(function($X){return
str_replace("FOR VALUES ","",$X);},array_values($Wj));return$ig;}function
tableOid($Q){return"(SELECT oid FROM pg_class WHERE relnamespace = ".$this->getNsOidSql()." AND relname = ".q($Q)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
getIndexAlgorithms(array$an){static$hi=[];if(!$hi)$hi=get_vals("SELECT amname FROM pg_am".($this->connection->isMinVersion("9.6")?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->connection->isCockroachDB()?"prefix":"btree")."' DESC, amname");return$hi;}function
getIndexOpclasses(){static$hj=[];if(!$hj&&!$this->connection->isCockroachDB())$hj=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$hj;}function
supportsIndex(array$an){return$an["Engine"]!="view";}function
hasCStyleEscapes(){static$xb;if($xb===null)$xb=($this->connection->getValue("SHOW standard_conforming_strings")=="off");return$xb;}function
explodeArrayValue($X,$T,&$zl){$Ao=["oidvector"=>"oid","int2vector"=>"smallint",];$zl=isset($Ao[$T])?$Ao[$T]:null;if($zl)return
explode(" ",$X);if(preg_match('~^(.+)\[]$~',$T,$z)){$zl=$z[1];$X=preg_replace('~^\{(.*)}$~',"$1",$X);$_g=str_contains($zl,"json");if($_g){$Y=explode('","',trim($X,'"'));array_walk($Y,function(&$V){$V=str_replace('\"','"',$V);});}elseif(preg_match('~^(\{+)(.*)(}+)$~',$X,$z)){$Y=explode("$z[3],$z[1]",$z[2]);array_walk($Y,function(&$V)use($z){$V=$z[1].$V.$z[3];});}else$Y=explode(",",$X);if(!$_g&&$Y[0][0]=="{"){$T=$zl;array_walk($Y,function(&$V)use($T,&$zl){$V=$this->explodeArrayValue($V,$T,$zl);});}elseif(!$_g&&isset($this->types[lang(125)][$zl])){array_walk($Y,function(&$V){$V=preg_replace('~^\'(.*)\'$~',"$1",$V);});}return$Y;}return[];}function
implodeArrayValues(array$Y,$T){if($T=="oidvector"||$T=="int2vector")return
implode(" ",$Y);if(preg_match('~^([^[]+)(\[])+$~',$T,$z)){$Ag=isset($this->types[lang(125)][$z[1]]);$_g=str_contains($T,"json");array_walk($Y,function(&$V)use($T,$Ag,$_g){if(is_array($V))$V=$this->implodeArrayValues($V,$T);elseif($_g)$V="\"$V\"";elseif($Ag)$V="'$V'";});return"{".implode(",",$Y)."}";}return"";}}function
create_driver(Connection$e){return
PgSqlDriver::create($e,Admin::get());}function
idf_escape($r){return'"'.str_replace('"','""',$r).'"';}function
table($r){return
idf_escape($r);}function
connect($E=false,&$j=null){$e=$E?PgSqlConnection::create():PgSqlConnection::createSecondary();list($M,$U,$D)=Admin::get()->getCredentials();$H=$e->openPasswordless($M,$U,$D,false);$be=[];if(!$H&&$M==""&&preg_match('~connection to server on socket .+ failed: No such file or directory~U',$e->getError())){$be[]=$e->getError();$M="localhost";$H=$e->openPasswordless($M,$U,$D,false);}if(!$H){$be[]=$e->getError();$j=implode("\n",$be);return
null;}if($e->isMinVersion("9"))$e->query("SET application_name = 'AdminNeo'");if($E&&$e->isCockroachDB()){Drivers::setName(DRIVER,"CockroachDB");save_driver_name(DRIVER,$M,"CockroachDB");}return$e;}function
get_databases($Se){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$w,$A=0,$Rl=" "){return" $F$Z".($w?$Rl."LIMIT $w".($A?" OFFSET $A":""):"");}function
limit1($Q,$F,$Z,$Rl="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$Rl):" $F".(is_view(table_status1($Q))?$Z:$Rl."WHERE ctid = (SELECT ctid FROM ".table($Q).$Z.$Rl."LIMIT 1)"));}function
db_collation($h,$Wb){return
Connection::get()->getValue("SELECT datcollate FROM pg_database WHERE datname = ".q($h));}function
logged_user(){return
Connection::get()->getValue("SELECT user");}function
tables_list(){$F="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$F
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$F
.="
ORDER BY 1";return
get_key_vals($F);}function
count_tables($g){$I=[];foreach($g
as$h){if(Connection::get()->selectDatabase($h))$I[$h]=count(tables_list());}return$I;}function
table_status($_=""){static$Bf;if($Bf===null)$Bf=Connection::get()->getValue("SELECT 'pg_table_size'::regproc");$I=[];foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\",
	CASE relkind WHEN 'p' THEN 'partitioned' ELSE '' END AS \"Create_options\"".($Bf?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(Connection::get()->isMinVersion("12")?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".(Connection::get()->isMinVersion("10")?"relispartition::int AS \"Partition\",":"")."
	current_schema() AS nspname
FROM pg_class c
WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".Driver::get()->getNsOidSql()."
".($_!=""?"AND relname = ".q($_):"ORDER BY relname"))as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$R){return
in_array($R["Engine"],["view","materialized view"]);}function
fk_support($R){return
true;}function
fields($Q){$I=[];$Ka=['timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',];foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	a.attndims, pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	col_description(a.attrelid, a.attnum) AS comment".(Connection::get()->isMinVersion("10")?",
	a.attidentity".(Connection::get()->isMinVersion("12")?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".Driver::get()->tableOid($Q)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$J){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$J["full_type"],$y);list(,$T,$v,$J["length"],$Ba,$Ua)=$y;$Ib=$T.$Ba;if(isset($Ka[$Ib])){$J["type"]=$Ka[$Ib];$J["full_type"]=$J["type"].$v;}else{$J["type"]=$T;$J["full_type"]=$J["type"].$v.$Ba;}for($p=0;$p<$J["attndims"];$p++){$J["length"].=$Ua;$J["full_type"].=$Ua;}if(in_array($J['attidentity'],['a','d']))$J['default']='GENERATED '.($J['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$B=["s"=>"STORED","v"=>"VIRTUAL"];$J["generated"]=(isset($B[$J["attgenerated"]])?$B[$J["attgenerated"]]:"");$J["null"]=!$J["attnotnull"];$J["auto_increment"]=$J['attidentity']||preg_match('~^nextval\(~i',$J["default"])||preg_match('~^unique_rowid\(~',$J["default"]);$J["privileges"]=["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1];if(!$J['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$J["default"],$y))$J["default"]=($y[1]=="NULL"?null:idf_unescape($y[1]).$y[2]);$I[$J["field"]]=$J;}return$I;}function
indexes($Q,$e=null){if(!is_object($e))$e=Connection::get();$I=[];$gn=Driver::get()->tableOid($Q);$d=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $gn AND attnum > 0",$e);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname, pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($e->isCockroachDB()?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s) FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $gn
ORDER BY indisprimary DESC, indisunique DESC",$e)as$J){$Zk=$J["relname"];$I[$Zk]["type"]=($J["indisprimary"]?"PRIMARY":($J["indisunique"]?"UNIQUE":"INDEX"));$I[$Zk]["columns"]=[];$I[$Zk]["descs"]=[];$I[$Zk]["algorithm"]=$J["amname"];$I[$Zk]["partial"]=$J["partial"];$fg=preg_split('~(?<=\)), (?=\()~',$J["indexpr"]);foreach(explode(" ",$J["indkey"])as$gg)$I[$Zk]["columns"][]=($gg?$d[$gg]:array_shift($fg));foreach(explode(" ",$J["indoption"])as$hg)$I[$Zk]["descs"][]=(intval($hg)&1?'1':null);$I[$Zk]["opclasses"]=(isset($J["opclasses"])?explode(" ",$J["opclasses"]):[]);$I[$Zk]["lengths"]=[];}return$I;}function
foreign_keys($Q){$bj=implode("|",Driver::get()->getOnActions());$I=[];foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".Driver::get()->tableOid($Q)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$J){$J['deferrable']=($J['deferrable']?'':'NOT ').'DEFERRABLE'.($J['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$J['definition'],$y)){$J['source']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$y[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$y[2],$Ih)){$J['ns']=idf_unescape($Ih[2]);$J['table']=idf_unescape($Ih[4]);}$J['target']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$y[3])));$J['on_delete']=(preg_match("~ON DELETE ($bj)~",$y[4],$Ih)?$Ih[1]:'NO ACTION');$J['on_update']=(preg_match("~ON UPDATE ($bj)~",$y[4],$Ih)?$Ih[1]:'NO ACTION');$I[$J['conname']]=$J;}}return$I;}function
backward_keys($Q){$F="SELECT s.constraint_name, s.table_schema, s.table_name, s.column_name, t.column_name AS referenced_column_name
FROM information_schema.key_column_usage s
JOIN information_schema.referential_constraints r USING (constraint_catalog, constraint_schema, constraint_name)
JOIN information_schema.key_column_usage t ON r.unique_constraint_catalog = t.constraint_catalog
	AND r.unique_constraint_schema = t.constraint_schema
	AND r.unique_constraint_name = t.constraint_name
	AND r.constraint_catalog = t.constraint_catalog
	AND r.constraint_schema = t.constraint_schema
	AND r.unique_constraint_name = t.constraint_name
	AND s.position_in_unique_constraint = t.ordinal_position
WHERE t.table_catalog = ".q(DB)."
AND t.table_schema = ".q($_GET["ns"])."
AND t.table_name = ".q($Q)."
ORDER BY s.ordinal_position";return
get_rows($F,null,"");}function
view($_){return["select"=>trim(Connection::get()->getValue("SELECT pg_get_viewdef(".Driver::get()->tableOid($_).")"))];}function
collations(){return[];}function
information_schema($h){return
get_schema()=="information_schema";}function
error(){$I=h(Connection::get()->getError());if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$I,$y))$I=$y[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($y[3]).'})(.*)~','\1<b>\2</b>',$y[2]).$y[4];return
nl2br($I);}function
create_database($h,$Vb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Vb?" ENCODING ".idf_escape($Vb):""));}function
drop_databases($g){Connection::get()->close();return
apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');}function
rename_database($_,$Vb){Connection::get()->close();return(bool)queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($_));}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$b=[];$Hk=[];if($Q!=""&&$Q!=$_)$Hk[]="ALTER TABLE ".table($Q)." RENAME TO ".table($_);$Sl="";foreach($l
as$k){$c=idf_escape($k[0]);$W=$k[1];if(!$W)$b[]="DROP $c";else{$yo=$W[5];unset($W[5]);if($k[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($Q!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($Q!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($c!=$W[0])$Hk[]="ALTER TABLE ".table($_)." RENAME $c TO $W[0]";$b[]="ALTER $c TYPE$W[1]";$Tl=$Q."_".idf_unescape($W[0])."_seq";$b[]="ALTER $c ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($Tl).")":"DROP DEFAULT"));if(isset($W[6]))$Sl="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Tl)." OWNED BY ".idf_escape($Q).".$W[0]";$b[]="ALTER $c ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($k[0]!=""||$yo!="")$Hk[]="COMMENT ON COLUMN ".table($_).".$W[0] IS ".($yo!=""?substr($yo,9):"''");}}$b=array_merge($b,$Ue);if($Q==""){$O="";if($Vj){$O=" PARTITION BY {$Vj["partition_by"]}({$Vj["partition"]})";if($Vj["partition_by"]=='HASH'){$Wj=(int)$Vj["partitions"];for($p=0;$p<$Wj;$p++)$Hk[]="CREATE TABLE ".idf_escape($_."_$p")." PARTITION OF ".idf_escape($_)." FOR VALUES WITH (MODULUS $Wj, REMAINDER $p)";}else{$Rb=Connection::get()->isCockroachDB();$wk="MINVALUE";foreach($Vj["partition_names"]as$p=>$W){$X=$Vj["partition_values"][$p];$Qj=" VALUES ".($Vj["partition_by"]=='LIST'?"IN ($X)":"FROM ($wk) TO ($X)");if($Rb)$O
.=($p?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W)).$Qj;else$Hk[]="CREATE TABLE ".idf_escape($_."_$W")." PARTITION OF ".idf_escape($_)." FOR$Qj";$wk=$X;}$O
.=$Rb?"\n)":"";}}array_unshift($Hk,"CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)$O");}elseif($b)array_unshift($Hk,"ALTER TABLE ".table($Q)."\n".implode(",\n",$b));if($Sl)array_unshift($Hk,$Sl);if($gc!==null)$Hk[]="COMMENT ON TABLE ".table($_)." IS ".q($gc);if($bb!="");foreach($Hk
as$F){if(!queries($F))return
false;}return
true;}function
alter_indexes($Q,$b){$Dc=[];$_d=[];$Hk=[];foreach($b
as$W){if($W[0]!="INDEX")$Dc[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$_d[]=idf_escape($W[1]);else$Hk[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($Dc)array_unshift($Hk,"ALTER TABLE ".table($Q).implode(",",$Dc));if($_d)array_unshift($Hk,"DROP INDEX ".implode(", ",$_d));foreach($Hk
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables($S,$Ab=false){return(bool)queries("TRUNCATE ".implode(", ",array_map('AdminNeo\table',$S)).($Ab?" CASCADE":""));}function
drop_views($Go){return
drop_tables($Go);}function
drop_tables($S){foreach($S
as$Q){$O=table_status1($Q);if(!queries("DROP ".strtoupper($O["Engine"])." ".table($Q)))return
false;}return
true;}function
move_tables($S,$Go,$rn){foreach(array_merge($S,$Go)as$Q){$O=table_status1($Q);if(!queries("ALTER ".strtoupper($O["Engine"])." ".table($Q)." SET SCHEMA ".idf_escape($rn)))return
false;}return
true;}function
trigger($_,$Q){if($_=="")return["Statement"=>"EXECUTE PROCEDURE ()"];$d=[];$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q)." AND trigger_name = ".q($_);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$J)$d[]=$J["event_object_column"];$Sn=[];foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement" FROM information_schema.triggers '."$Z ORDER BY event_manipulation DESC")as$J){if($d&&$J["Event"]=="UPDATE")$J["Event"].=" OF";$J["Of"]=implode(", ",$d);if($Sn)$J["Event"].=" OR $Sn[Event]";$Sn=$J;}return$Sn;}function
triggers($Q){$I=[];foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q))as$J){$Sn=trigger($J["trigger_name"],$Q);$I[$Sn["Trigger"]]=[$Sn["Timing"],$Sn["Event"]];}return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF"],"Type"=>["FOR EACH ROW","FOR EACH STATEMENT"],];}function
routine($_,$T){$ig=get_rows('SELECT routine_definition, LOWER(external_language) AS language, type_udt_name
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($_));$ig=isset($ig[0])?$ig[0]:[];$l=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($_)."
ORDER BY ordinal_position");return["fields"=>$l,"returns"=>["type"=>preg_replace('~^_(.*)~','\1[]',isset($ig["type_udt_name"])?$ig["type_udt_name"]:"")],"definition"=>isset($ig["routine_definition"])?$ig["routine_definition"]:null,"language"=>isset($ig["language"])?$ig["language"]:null,"comment"=>null,];}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_name AS "ROUTINE_NAME", routine_type AS "ROUTINE_TYPE", type_udt_name AS "DTD_IDENTIFIER", null AS ROUTINE_COMMENT
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(Connection::get()->isCockroachDB()?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($_,$J){$I=[];foreach($J["fields"]as$k){$v=$k["length"];$I[]=$k["type"].($v?"($v)":"");}return
idf_escape($_)."(".implode(", ",$I).")";}function
last_id($H){$J=$H
instanceof
Result?$H->fetchRow():[];return$J?$J[0]:0;}function
explain(Connection$e,$F){return$e->query("EXPLAIN $F");}function
found_rows(array$R,array$Z){if(preg_match("~ rows=([0-9]+)~",Connection::get()->getValue("EXPLAIN SELECT * FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Yk))return(int)$Yk[1];return
null;}function
types($se=false){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".Driver::get()->getNsOidSql()."
AND typtype IN ('b','d','e')
AND typelem = 0".($se||Connection::get()->isCockroachDB()?'':"
AND oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"));}function
type_values($q){$Zd=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $q ORDER BY enumsortorder");if(!$Zd)return"";return"'".implode("', '",array_map('addslashes',$Zd))."'";}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
Connection::get()->getValue("SELECT current_schema()");}function
set_schema($Cl,$e=null){if(!$e)$e=Connection::get();$H=(bool)$e->query("SET search_path TO ".idf_escape($Cl));Driver::get()->setUserTypes(types(true));return$H;}function
foreign_keys_sql($Q){$I="";$O=table_status1($Q);$Ii=idf_escape($O['nspname']);$Pe=foreign_keys($Q);ksort($Pe);foreach($Pe
as$Oe=>$Ne)$I
.="ALTER TABLE ONLY $Ii.".idf_escape($O['Name'])." ADD CONSTRAINT ".idf_escape($Oe)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$Ii.\\2",$Ne["definition"]).";\n";return($I?"$I\n":$I);}function
foreign_key_checks_sql($Sd){return"SET session_replication_role = ".($Sd?"DEFAULT":"replica").";\n";}function
restart_sequences_sql($Q){$I="";foreach(fields($Q)as$k){if(!$k["auto_increment"]||preg_match('~^unique_rowid\(~',$k["default"]))continue;$Sl="pg_get_serial_sequence(".q(table($Q)).", ".q($k["field"]).")";$c=idf_escape($k["field"]);$I
.="SELECT setval($Sl, MAX($c)) FROM ".table($Q)." HAVING MAX($c) IS NOT NULL;\n";}return$I;}function
create_sql($Q,$bb,$Km){$ll=[];$Ul=[];$O=table_status1($Q);$Ii=idf_escape($O['nspname']);if(is_view($O)){$Eo=view($Q);return
rtrim("CREATE VIEW $Ii.".idf_escape($Q)." AS $Eo[select]",";");}$l=fields($Q);if(count($O)<2||empty($l))return
false;$I="CREATE TABLE $Ii.".idf_escape($O['Name'])." (\n    ";foreach($l
as$k){if($k['default']=="nextval('$O[Name]_$k[field]_seq')"){$k['default']=null;$k['full_type']=preg_replace('~int(eger)?~','serial',$k['full_type']);}$Oj=idf_escape($k['field']).' '.$k['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$O['nspname']).'.\2',default_value($k)).($k['null']?"":" NOT NULL");$ll[]=$Oj;if(preg_match('~nextval\(\'([^\']+)\'\)~',$k['default'],$z)){$Tl=$z[1];$K=get_rows((Connection::get()->isMinVersion("10")?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Tl)):"SELECT * FROM $Tl"),null,"-- ");if($K){$wm=first($K);$Ul[]=($Km=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Ii.$Tl;\n":"")."CREATE SEQUENCE $Ii.$Tl INCREMENT $wm[increment_by] MINVALUE $wm[min_value] MAXVALUE $wm[max_value]".($bb&&$wm['last_value']?" START ".($wm["last_value"]+1):"")." CACHE $wm[cache_value];";}}}if(!empty($Ul))$I=implode("\n\n",$Ul)."\n\n$I";$E="";foreach(indexes($Q)as$dg=>$s){if($s['type']=='PRIMARY'){$E=$dg;$ll[]="CONSTRAINT ".idf_escape($dg)." PRIMARY KEY (".implode(', ',array_map('AdminNeo\idf_escape',$s['columns'])).")";}}foreach(Driver::get()->checkConstraints($Q)as$qc=>$vc)$ll[]="CONSTRAINT ".idf_escape($qc)." CHECK ($vc)";$I
.=implode(",\n    ",$ll)."\n)";$Qj=Driver::get()->getPartitionsInfo($O['Name']);if($Qj)$I
.="\nPARTITION BY {$Qj["partition_by"]}({$Qj["partition"]})";$I
.="\nWITH (oids = ".($O['Oid']?'true':'false').");";if($O['Comment'])$I
.="\n\nCOMMENT ON TABLE $Ii.".idf_escape($O['Name'])." IS ".q($O['Comment']).";";foreach($l
as$Be=>$k){if($k['comment'])$I
.="\n\nCOMMENT ON COLUMN $Ii.".idf_escape($O['Name']).".".idf_escape($Be)." IS ".q($k['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($Q).($E?" AND indexname != ".q($E):""),null,"-- ")as$J)$I
.="\n\n$J[indexdef];";return
rtrim($I,';');}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
trigger_sql($Q){$O=table_status1($Q);$xm="";foreach(triggers($Q)as$Rn=>$Qn){$Sn=trigger($Rn,$O['Name']);$xm
.="\nCREATE TRIGGER ".idf_escape($Sn['Trigger'])." $Sn[Timing] $Sn[Event] ON ".idf_escape($O["nspname"]).".".idf_escape($O['Name'])." $Sn[Type] $Sn[Statement];;\n";}return$xm;}function
create_database_sql($Rc,$Km=""){$_=idf_escape($Rc);$ec="";if(str_contains($Km,"CREATE")){if($Km=="DROP+CREATE")$ec="DROP DATABASE IF EXISTS $_;\n";$ec
.="CREATE DATABASE $_;\n";}return$ec;}function
use_sql($Rc){return'\connect '.idf_escape($Rc).";\n";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(Connection::get()->isMinVersion("9.2")?"pid":"procpid"));}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){return$I;}function
support($ye){if($ye=="processlist")return!Connection::get()->isCockroachDB();elseif($ye=="materializedview")return
Connection::get()->isMinVersion("9.3");elseif($ye=="procedure")return
Connection::get()->isMinVersion("11");return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view)$~',$ye);}function
kill_process($W){return
queries("SELECT pg_terminate_backend(".number($W).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return(int)Connection::get()->getValue("SHOW max_connections");}}Drivers::add("mssql","MS SQL",["SQLSRV","PDO_SQLSRV","PDO_DBLIB"]);if(isset($_GET["mssql"])){define("AdminNeo\DRIVER","mssql");define("AdminNeo\DIALECT","mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"&&$_GET["ext"]!="dblib"){define("AdminNeo\DRIVER_EXTENSION","sqlsrv");class
MsSqlConnection
extends
Connection{private$connection;protected$multiResult;function
getDefaultServerName(){return"localhost:1433";}function
open($M,$U,$D){$tc=["UID"=>$U,"PWD"=>$D,"CharacterSet"=>"UTF-8",];$Ud=Admin::get()->getConfig()->getSslEncrypt();if($Ud!==null)$tc["Encrypt"]=$Ud;$Wn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Wn!==null)$tc["TrustServerCertificate"]=$Wn;$h=Admin::get()->getDatabase();if($h!="")$tc["Database"]=$h;$this->connection=@sqlsrv_connect(implode(",",host_port($M)),$tc);if($this->connection){$ig=sqlsrv_server_info($this->connection);$this->version=$ig['SQLServerVersion'];}else$this->resolveError();return(bool)$this->connection;}private
function
resolveError(){$this->error="";foreach(sqlsrv_errors()as$j){$this->errno=$j["code"];$this->error
.="$j[message]\n";}$this->error=rtrim($this->error);}function
quote($P){return(contains_unicode($P)?"N":"")."'".str_replace("'","''",$P)."'";}function
selectDatabase($_){return(bool)$this->query(use_sql($_));}function
query($F,$co=false){$H=sqlsrv_query($this->connection,$F);$this->error="";if(!$H){$this->resolveError();return
false;}return$this->storeResult($H);}function
multiQuery($F){$this->multiResult=sqlsrv_query($this->connection,$F);$this->error="";if(!$this->multiResult){$this->resolveError();return
false;}return
true;}function
storeResult($H=null){if(!$H){$H=$this->multiResult;if(!$H)return
false;}if(sqlsrv_field_metadata($H))return
new
MsSqlResult($H);$this->affectedRows=sqlsrv_rows_affected($H);return
true;}function
nextResult(){return$this->multiResult&&sqlsrv_next_result($this->multiResult);}}class
MsSqlResult
extends
Result{private$resource;private$fields=false;private$offset=0;function
__construct($gl){parent::__construct(0);$this->resource=$gl;}function
fetchAssoc(){return$this->convertRow(sqlsrv_fetch_array($this->resource,SQLSRV_FETCH_ASSOC));}function
fetchRow(){return$this->convertRow(sqlsrv_fetch_array($this->resource,SQLSRV_FETCH_NUMERIC));}private
function
convertRow($J){if(is_array($J)){foreach($J
as$u=>$W){if(is_a($W,'DateTime'))$J[$u]=$W->format("Y-m-d H:i:s");}}return$J;}function
fetchField(){if(!$this->fields){$this->fields=sqlsrv_field_metadata($this->resource);if(!$this->fields)return
false;}$k=$this->fields[$this->offset++];return(object)['name'=>$k["Name"],'type'=>($k["Type"]==1?254:15),'charsetnr'=>(in_array($k["Type"],[-2,-3,-4])?63:0),];}function
seek($A){for($p=0;$p<$A;$p++){if(!sqlsrv_fetch($this->resource))return
false;}return
true;}}function
last_id($H){return
Connection::get()->getValue("SELECT SCOPE_IDENTITY()");}function
explain(Connection$e,$F){$e->query("SET SHOWPLAN_ALL ON");$I=$e->query($F);$e->query("SET SHOWPLAN_ALL OFF");return$I;}}else{abstract
class
MsSqlPdoConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost:1433";}function
selectDatabase($_){return(bool)$this->query(use_sql($_));}function
quote($P){return(contains_unicode($P)?"N":"").parent::quote($P);}function
lastInsertId(){return$this->pdo->lastInsertId();}}if((extension_loaded("pdo_sqlsrv")&&$_GET["ext"]!="dblib")||$_GET["ext"]=="pdo"){define("AdminNeo\DRIVER_EXTENSION","PDO_SQLSRV");class
MsSqlConnection
extends
MsSqlPdoConnection{function
open($M,$U,$D){$B=[];$Ud=Admin::get()->getConfig()->getSslEncrypt();if($Ud!==null)$B[]="Encrypt=$Ud";$Wn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Wn!==null)$B[]="TrustServerCertificate=$Wn";$mj=$B?(";".implode(";",$B)):"";return$this->dsn("sqlsrv:Server=".implode(",",host_port($M)).$mj,$U,$D);}}}elseif(extension_loaded("pdo_dblib")){define("AdminNeo\DRIVER_EXTENSION","PDO_DBLIB");class
MsSqlConnection
extends
MsSqlPdoConnection{function
open($M,$U,$D){list($Nf,$mk)=host_port($M);$H=$this->dsn("dblib:charset=utf8;host=$Nf".($mk?(is_numeric($mk)?";port=":";unix_socket=").$mk:""),$U,$D);if($H)$this->query("SET ANSI_NULLS ON; SET ANSI_PADDING ON; SET CONCAT_NULL_YIELDS_NULL ON; SET ANSI_WARNINGS ON;");return$H;}}}function
last_id($H){$e=Connection::get();return$e->lastInsertId();}function
explain(Connection$e,$F){}}class
MsSqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(123)=>["tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20,],lang(124)=>["date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10,],lang(125)=>["char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823,],lang(127)=>["binary"=>8000,"varbinary"=>8000,"image"=>2147483647,],];$this->generated=["PERSISTED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL",];$this->functions=["len","lower","upper","round",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->onActions=["CASCADE","SET NULL","SET DEFAULT","NO ACTION"];$this->insertFunctions=["date|time"=>"getdate"];$this->editFunctions=["int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",];$this->systemSchemas=["INFORMATION_SCHEMA","guest","sys","db_*"];}function
insertUpdate($Q,array$Pk,array$E){$l=fields($Q);$lo=[];$Z=[];$G=reset($Pk);$d="c".implode(", c",range(1,count($G)));$wb=0;$ng=[];foreach($G
as$u=>$W){$wb++;$_=idf_unescape($u);if(!$l[$_]["auto_increment"])$ng[$u]="c$wb";if(isset($E[$_]))$Z[]="$u = c$wb";else$lo[]="$u = c$wb";}$Y=[];foreach($Pk
as$G)$Y[]="(".implode(", ",$G).")";if($Z){$Tf=queries("SET IDENTITY_INSERT ".table($Q)." ON");$I=queries("MERGE ".table($Q)." USING (VALUES\n\t".implode(",\n\t",$Y)."\n) AS source ($d) ON ".implode(" AND ",$Z).($lo?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$lo):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Tf?$G:$ng)).") VALUES (".($Tf?$d:implode(", ",$ng)).");");if($Tf)queries("SET IDENTITY_INSERT ".table($Q)." OFF");}else$I=queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($G)).") VALUES\n".implode(",\n",$Y));return$I;}function
quoteBinary($P){return"0x".bin2hex($P);}function
begin(){return
queries("BEGIN TRANSACTION");}function
tableHelp($_,$Bg=false){$sh=["sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",];$x=$sh[get_schema()];if($x)return"relational-databases/system-$x".preg_replace('~_~','-',strtolower($_))."-transact-sql";return
null;}}function
create_driver(Connection$e){return
MsSqlDriver::create($e,Admin::get());}function
contains_unicode($P){return
strlen($P)!=strlen(utf8_decode($P));}function
idf_escape($r){return"[".str_replace("]","]]",$r)."]";}function
table($r){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($r);}function
connect($E=false,&$j=null){$e=$E?MsSqlConnection::create():MsSqlConnection::createSecondary();$Hc=Admin::get()->getCredentials();if($Hc[0]=="")$Hc[0]="localhost:1433";if(!$e->open($Hc[0],$Hc[1],$Hc[2])){$j=$e->getError();return
null;}return$e;}function
get_databases($Se){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb') ORDER BY name");}function
limit($F,$Z,$w,$A=0,$Rl=" "){return($w?" TOP (".($w+$A).")":"")." $F$Z";}function
limit1($Q,$F,$Z,$Rl="\n"){return
limit($F,$Z,1,0,$Rl);}function
db_collation($h,$Wb){return
Connection::get()->getValue("SELECT collation_name FROM sys.databases WHERE name = ".q($h));}function
logged_user(){return
Connection::get()->getValue("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($g){$I=[];foreach($g
as$h){Connection::get()->selectDatabase($h);$I[$h]=Connection::get()->getValue("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$I;}function
table_status($_=""){$I=[];$om=[];foreach(get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats
GROUP BY object_id",null,"")as$J){$Pi=$J["object_id"];unset($J["object_id"]);$om[$Pi]=$J;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine, (SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($_!=""?"AND name = ".q($_):"ORDER BY name"))as$J){$Pi=$J["object_id"];unset($J["object_id"]);$I[$J["Name"]]=$J+(isset($om[$Pi])?$om[$Pi]:[]);}return$I;}function
is_view(array$R){return$R["Engine"]=="VIEW";}function
fk_support($R){return
true;}function
fields($Q){$ic=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($Q).", 'column', NULL)");$I=[];$cn=Connection::get()->getValue("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($Q));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($cn))as$J){$T=$J["type"];$v=(preg_match("~char|binary~",$T)?intval($J["max_length"])/($T[0]=='n'?2:1):($T=="decimal"?"$J[precision],$J[scale]":""));$I[$J["name"]]=["field"=>$J["name"],"full_type"=>$T.($v?"($v)":""),"type"=>$T,"length"=>$v,"default"=>(preg_match("~^\('(.*)'\)$~",$J["default"],$y)?str_replace("''","'",$y[1]):$J["default"]),"default_constraint"=>$J["default_constraint"],"null"=>$J["is_nullable"],"auto_increment"=>$J["is_identity"],"collation"=>$J["collation_name"],"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1],"primary"=>$J["is_primary_key"],"comment"=>$ic[$J["name"]],];}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($cn))as$J){$I[$J["name"]]["generated"]=($J["is_persisted"]?"PERSISTED":"VIRTUAL");$I[$J["name"]]["default"]=$J["definition"];}return$I;}function
indexes($Q,$e=null){$I=[];foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($Q),$e)as$J){$_=$J["name"];$I[$_]["type"]=($J["is_primary_key"]?"PRIMARY":($J["is_unique"]?"UNIQUE":"INDEX"));$I[$_]["lengths"]=[];$I[$_]["columns"][$J["key_ordinal"]]=$J["column_name"];$I[$_]["descs"][$J["key_ordinal"]]=($J["is_descending_key"]?'1':null);}return$I;}function
view($_){return["select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',Connection::get()->getValue("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($_)))];}function
collations(){$I=[];foreach(get_vals("SELECT name FROM fn_helpcollations()")as$Vb)$I[preg_replace('~_.*~','',$Vb)][]=$Vb;return$I;}function
information_schema($h){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
nl2br(h(preg_replace('~^(\[[^]]*])+~m','',Connection::get()->getError())));}function
create_database($h,$Vb){return(bool)queries("CREATE DATABASE ".idf_escape($h).(preg_match('~^[a-z0-9_]+$~i',$Vb)?" COLLATE $Vb":""));}function
drop_databases($g){return(bool)queries("DROP DATABASE ".implode(", ",array_map('AdminNeo\idf_escape',$g)));}function
rename_database($_,$Vb){if(preg_match('~^[a-z0-9_]+$~i',$Vb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $Vb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($_));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$b=[];$ic=[];$yj=fields($Q);foreach($l
as$k){$c=idf_escape($k[0]);$W=$k[1];if(!$W)$b["DROP"][]=" COLUMN $c";else{$W[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$W[1]);$ic[$k[0]]=$W[5];unset($W[5]);if(preg_match('~ AS ~',$W[3]))unset($W[1],$W[2]);if($k[0]=="")$b["ADD"][]="\n  ".implode("",$W).($Q==""?substr($Ue[$W[0]],16+strlen($W[0])):"");else{$i=$W[3];unset($W[3]);unset($W[6]);if($c!=$W[0])queries("EXEC sp_rename ".q(table($Q).".$c").", ".q(idf_unescape($W[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$W)][]="";$xj=$yj[$k[0]];if(default_value($xj)!=$i){if($xj["default"]!==null)$b["DROP"][]=" ".idf_escape($xj["default_constraint"]);if($i)$b["ADD"][]="\n $i FOR $c";}}}}if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (".implode(",",(array)$b["ADD"])."\n)");if($Q!=$_)queries("EXEC sp_rename ".q(table($Q)).", ".q($_));if($Ue)$b[""]=$Ue;foreach($b
as$u=>$W){if(!queries("ALTER TABLE ".table($_)." $u".implode(",",$W)))return
false;}foreach($ic
as$u=>$W){$gc=substr($W,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($_).", @level2type = N'Column', @level2name = ".q($u));queries("EXEC sp_addextendedproperty @name = N'MS_Description', @value = ".$gc.", @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($_).", @level2type = N'Column', @level2name = ".q($u));}return
true;}function
alter_indexes($Q,$b){$s=[];$_d=[];foreach($b
as$W){if($W[2]=="DROP"){if($W[0]=="PRIMARY")$_d[]=idf_escape($W[1]);else$s[]=idf_escape($W[1])." ON ".table($Q);}elseif(!queries(($W[0]!="PRIMARY"?"CREATE $W[0] ".($W[0]!="INDEX"?"INDEX ":"").idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q):"ALTER TABLE ".table($Q)." ADD PRIMARY KEY")." (".implode(", ",$W[2]).")"))return
false;}return(!$s||queries("DROP INDEX ".implode(", ",$s)))&&(!$_d||queries("ALTER TABLE ".table($Q)." DROP ".implode(", ",$_d)));}function
found_rows(array$R,array$Z){return
null;}function
foreign_keys($Q){$I=[];$bj=Driver::get()->getOnActions();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($Q).", @fktable_owner = ".q(get_schema()))as$J){$o=&$I[$J["FK_NAME"]];$o["db"]=$J["PKTABLE_QUALIFIER"];$o["ns"]=$J["PKTABLE_OWNER"];$o["table"]=$J["PKTABLE_NAME"];$o["on_update"]=$bj[$J["UPDATE_RULE"]];$o["on_delete"]=$bj[$J["DELETE_RULE"]];$o["source"][]=$J["FKCOLUMN_NAME"];$o["target"][]=$J["PKCOLUMN_NAME"];}return$I;}function
backward_keys($Q){$F="SELECT fk.name AS constraint_name,
OBJECT_SCHEMA_NAME(fkc.parent_object_id) AS table_schema,
OBJECT_NAME(fkc.parent_object_id) AS table_name,
COL_NAME(fkc.parent_object_id, fkc.parent_column_id) AS column_name,
COL_NAME(fkc.referenced_object_id, fkc.referenced_column_id) AS referenced_column_name
FROM sys.foreign_key_columns fkc
JOIN sys.foreign_keys fk ON fkc.constraint_object_id = fk.object_id
WHERE OBJECT_SCHEMA_NAME(fkc.referenced_object_id) = ".q($_GET["ns"])."
AND OBJECT_NAME(fkc.referenced_object_id) = ".q($Q)."
ORDER BY table_schema, table_name";return
get_rows($F,null,"");}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($Go){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$Go)));}function
drop_tables($S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables($S,$Go,$rn){return
apply_queries("ALTER SCHEMA ".idf_escape($rn)." TRANSFER",array_merge($S,$Go));}function
trigger($_,$Q){if($_=="")return[];$K=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($_));$Sn=reset($K);if($Sn)$Sn["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$Sn["text"]);return$Sn;}function
triggers($Q){$I=[];foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($Q))as$J)$I[$J["name"]]=[$J["Timing"],$J["Event"]];return$I;}function
trigger_options(){return["Timing"=>["AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["AS"],];}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
Connection::get()->getValue("SELECT SCHEMA_NAME()");}function
set_schema($Cl,$e=null){$_GET["ns"]=$Cl;return
true;}function
create_sql($Q,$bb,$Km){if(is_view(table_status1($Q))){$Eo=view($Q);return"CREATE VIEW ".table($Q)." AS $Eo[select]";}$l=[];$E=false;foreach(fields($Q)as$_=>$k){$W=process_field($k,$k);if($W[6])$E=true;$l[]=implode("",$W);}foreach(indexes($Q)as$_=>$s){if(!$E||$s["type"]!="PRIMARY"){$d=[];foreach($s["columns"]as$u=>$W)$d[]=idf_escape($W).($s["descs"][$u]?" DESC":"");$_=idf_escape($_);$l[]=($s["type"]=="INDEX"?"INDEX $_":"CONSTRAINT $_ ".($s["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$d).")";}}foreach(Driver::get()->checkConstraints($Q)as$_=>$Gb)$l[]="CONSTRAINT ".idf_escape($_)." CHECK ($Gb)";return"CREATE TABLE ".table($Q)." (\n\t".implode(",\n\t",$l)."\n)";}function
foreign_keys_sql($Q){$l=[];foreach(foreign_keys($Q)as$Ue)$l[]=ltrim(format_foreign_key($Ue));return($l?"ALTER TABLE ".table($Q)." ADD\n\t".implode(",\n\t",$l).";\n\n":"");}function
truncate_sql($Q){return"TRUNCATE TABLE ".table($Q);}function
create_database_sql($Rc,$Km=""){return"";}function
use_sql($Rc){return"USE ".idf_escape($Rc).";\n";}function
trigger_sql($Q){$xm="";foreach(triggers($Q)as$_=>$Sn)$xm
.=create_trigger(" ON ".table($Q),trigger($_,$Q)).";";return$xm;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){return$I;}function
support($ye){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$ye);}}Drivers::add("sqlite","SQLite",["SQLite3","PDO_SQLite"]);if(isset($_GET["sqlite"])){define("AdminNeo\DRIVER","sqlite");define("AdminNeo\DIALECT","sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","SQLite3");abstract
class
SqLiteConnectionBase
extends
Connection{private$sqlite;function
open($M,$U,$D){$this->sqlite=new
SQLite3($M);$this->version=$this->sqlite->version()["versionString"];return
true;}function
query($F,$co=false){$H=@$this->sqlite->query($F);$this->error="";if(!$H){$this->errno=$this->sqlite->lastErrorCode();$this->error=$this->sqlite->lastErrorMsg();return
false;}elseif($H->numColumns())return
new
SqLiteResult($H);$this->affectedRows=$this->sqlite->changes();return
true;}function
quote($P){if(is_utf8($P))return"'".$this->sqlite->escapeString($P)."'";else
return"x'".first(unpack('H*',$P))."'";}}class
SqLiteResult
extends
Result{private$resource;private$offset=0;function
__construct(SQLite3Result$gl){parent::__construct(0);$this->resource=$gl;}function
fetchAssoc(){return$this->resource->fetchArray(SQLITE3_ASSOC);}function
fetchRow(){return$this->resource->fetchArray(SQLITE3_NUM);}function
fetchField(){$bo=[1=>"integer","real","text","blob","null"];$c=$this->offset++;$T=$this->resource->columnType($c);if($T===false)return
false;return(object)["name"=>$this->resource->columnName($c),"native_type"=>$bo[$T],"type"=>($T==SQLITE3_TEXT?15:0),"charsetnr"=>($T==SQLITE3_BLOB?63:0),];}}}elseif(extension_loaded("pdo_sqlite")){define("AdminNeo\DRIVER_EXTENSION","PDO_SQLite");abstract
class
SqLiteConnectionBase
extends
PdoConnection{function
open($M,$U,$D){return$this->dsn(DRIVER.":$M","","");}}}if(class_exists('AdminNeo\SqLiteConnectionBase')){class
SqLiteConnection
extends
SqLiteConnectionBase{protected
function
__construct(){parent::__construct();$this->open(":memory:","","");}function
open($M,$U,$D){if(!parent::open($M,$U,$D))return
false;$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return
true;}function
close(){$this->open(":memory:","","");}function
selectDatabase($_){if(is_readable($_)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$_)?$_:dirname($_SERVER["SCRIPT_FILENAME"])."/$_")." AS a"))return
self::open($_,"","");return
false;}}}class
SqliteDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[["integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0,]];if($e->isMinVersion("3.31"))$this->generated=["STORED","VIRTUAL"];if($e->isMinVersion("3.37"))$this->types[0]["any"]=0;$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round","hex","unixepoch",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->insertFunctions=[];$this->editFunctions=["integer|real|numeric"=>"+/-","text"=>"||",];}function
getStructuredTypes(){return
parent::getStructuredTypes()[0];}function
engines(){$I=["table"];if(Connection::get()->isMinVersion("3.8.2")){if(Connection::get()->isMinVersion("3.37")){$I[]="STRICT";$I[]="STRICT, WITHOUT ROWID";}$I[]="WITHOUT ROWID";}return$I;}function
insertUpdate($Q,array$Pk,array$E){$Y=[];foreach($Pk
as$G)$Y[]="(".implode(", ",$G).")";return
queries("REPLACE INTO ".table($Q)." (".implode(", ",array_keys(reset($Pk))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($_,$Bg=false){if(preg_match('~^sqlite_(seq|stat.)~',$_,$y))return"fileformat2.html#$y[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$_))return"schematab.html";return
null;}function
checkConstraints($Q){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',$this->connection->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q)),$z);return
array_combine($z[2],$z[2]);}function
getAllFields(){$La=[];foreach(tables_list()as$Q=>$T){foreach(fields($Q)as$k)$La[$Q][]=$k;}return$La;}}function
create_driver(Connection$e){return
SqliteDriver::create($e,Admin::get());}function
idf_escape($r){return'"'.str_replace('"','""',$r).'"';}function
table($r){return
idf_escape($r);}function
connect($E=false,&$j=null){$e=$E?SqLiteConnection::create():SqLiteConnection::createSecondary();$D=Admin::get()->getCredentials()[2];if($D!=""){$H=Admin::get()->verifyDefaultPassword($D);if($H!==true){$j=$H;return
null;}}return$e;}function
get_databases($Se){return[];}function
limit($F,$Z,$w,$A=0,$Rl=" "){return" $F$Z".($w?$Rl."LIMIT $w".($A?" OFFSET $A":""):"");}function
limit1($Q,$F,$Z,$Rl="\n"){return(preg_match('~^INTO~',$F)||Connection::get()->getValue("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$Rl):" $F WHERE rowid = (SELECT rowid FROM ".table($Q).$Z.$Rl."LIMIT 1)");}function
db_collation($h,$Wb){return
Connection::get()->getValue("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables($g){return[];}function
db_status(){$e=Connection::get();$Ij=$e->getValue("PRAGMA page_size");$ef=$e->getValue("PRAGMA freelist_count")*$Ij;return["Data_length"=>$e->getValue("PRAGMA page_count")*$Ij-$ef,"Index_length"=>0,"Data_free"=>$ef,];}function
table_status($_="",$xe=false){$I=[];$K=[];if(!$xe){if($_=="")Connection::get()->query("PRAGMA optimize = 0x10002");$K=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1".($_!=""?" WHERE tbl = ".q($_):"")." GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($_!=""?"AND name = ".q($_):"ORDER BY (name LIKE 'sqlite_%'), name"))as$J){if($J["Engine"]=="table"){$Nm=preg_replace('~.*\)~s','',$J["sql"]);$J["Engine"]=implode(", ",array_filter([(preg_match('~\bSTRICT\b~i',$Nm)?"STRICT":""),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Nm)?"WITHOUT ROWID":""),]))?:"table";}unset($J["sql"]);$J["Rows"]=isset($K[$J["Name"]])?$K[$J["Name"]]:0;$I[$J["Name"]]=$J;}if(!$xe){foreach(get_rows("SELECT * FROM sqlite_sequence".($_!=""?" WHERE name = ".q($_):""),null,"")as$J)$I[$J["name"]]["Auto_increment"]=$J["seq"];}return$I;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support($R){return!Connection::get()->getValue("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($Q){$I=[];$xm=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));$Ak=["select"=>1,"where"=>1,"order"=>1];if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$Q))$Ak+=["insert"=>1,"update"=>1];foreach(get_rows("PRAGMA table_".(Connection::get()->isMinVersion("3.31")?"x":"")."info(".table($Q).")")as$J){$_=$J["name"];$T=strtolower($J["type"]);$i=$J["dflt_value"];$I[$_]=["field"=>$_,"type"=>(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric"))))),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~",$i,$y)?str_replace("''","'",$y[1]):($i=="NULL"?null:$i)),"null"=>!$J["notnull"],"privileges"=>$Ak,"primary"=>$J["pk"],];if($J["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$xm))$I[$_]["auto_increment"]=true;}$r='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$r.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$xm,$z,PREG_SET_ORDER);foreach($z
as$y){$_=str_replace('""','"',preg_replace('~^"|"$~','',$y[1]));if($I[$_])$I[$_]["collation"]=trim($y[3],"'");}preg_match_all('~'.$r.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$xm,$z,PREG_SET_ORDER);foreach($z
as$y){$_=str_replace('""','"',preg_replace('~^"|"$~','',$y[1]));$I[$_]["default"]=$y[3];$I[$_]["generated"]=strtoupper($y[4]);}return$I;}function
indexes($Q,$e=null){if(!is_object($e))$e=Connection::get();$I=[];$xm=$e->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$xm,$y)){$I[""]=["type"=>"PRIMARY","columns"=>[],"lengths"=>[],"descs"=>[]];preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$y[1],$z,PREG_SET_ORDER);foreach($z
as$y){$I[""]["columns"][]=idf_unescape($y[2]).$y[4];$I[""]["descs"][]=(preg_match('~DESC~i',$y[5])?'1':null);}}if(!$I){foreach(fields($Q)as$_=>$k){if($k["primary"])$I[""]=["type"=>"PRIMARY","columns"=>[$_],"lengths"=>[],"descs"=>[null]];}}$Am=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($Q),$e);foreach(get_rows("PRAGMA index_list(".table($Q).")",$e)as$J){$_=$J["name"];$s=["type"=>($J["unique"]?"UNIQUE":"INDEX")];$s["lengths"]=[];$s["descs"]=[];foreach(get_rows("PRAGMA index_info(".idf_escape($_).")",$e)as$sl){$s["columns"][]=$sl["name"];$s["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($_).' ON '.idf_escape($Q),'~').' \((.*)\)$~i',$Am[$_],$Yk)){preg_match_all('/("[^"]*+")+( DESC)?/',$Yk[2],$z);foreach($z[2]as$u=>$W){if($W)$s["descs"][$u]='1';}}if(!$I[""]||$s["type"]!="UNIQUE"||$s["columns"]!=$I[""]["columns"]||$s["descs"]!=$I[""]["descs"]||!preg_match("~^sqlite_~",$_))$I[$_]=$s;}return$I;}function
foreign_keys($Q){$I=[];foreach(get_rows("PRAGMA foreign_key_list(".table($Q).")")as$J){$o=&$I[$J["id"]];if(!$o)$o=$J;$o["source"][]=$J["from"];$o["target"][]=$J["to"];}return$I;}function
backward_keys($Q){return[];}function
view($_){return["select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($_)))];}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):[]);}function
information_schema($h){return
false;}function
error(){return
h(Connection::get()->getError());}function
check_sqlite_name($_){$se="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($se)\$~",$_)){Connection::get()->setError(lang(130,str_replace("|",", ",$se)));return
false;}return
true;}function
create_database($h,$Vb){if(file_exists($h)){Connection::get()->setError(lang(131));return
false;}if(!check_sqlite_name($h))return
false;try{$x=SqLiteConnection::createSecondary();$x->open($h,"","");}catch(Exception$fe){Connection::get()->setError($fe->getMessage());return
false;}$x->query('PRAGMA encoding = "UTF-8"');$x->query('CREATE TABLE adminneo (i)');$x->query('DROP TABLE adminneo');return
true;}function
drop_databases($g){Connection::get()->close();foreach($g
as$h){if(!check_sqlite_name($h))return
false;if(!@unlink($h)){Connection::get()->setError(lang(131));return
false;}}return
true;}function
rename_database($_,$Vb){if(!check_sqlite_name($_))return
false;Connection::get()->close();Connection::get()->setError(lang(131));return@rename(DB,$_);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$qo=($Q==""||$Ue||$Wd);foreach($l
as$k){if($k[0]!=""||!$k[1]||$k[2]){$qo=true;break;}}$Qa=[];$Bj=[];foreach($l
as$k){if(!$k[1])continue;if($k[0]!="")$Bj[$k[0]]=$k[1][0];$Qa[]=($qo?$k[1]:"ADD ".implode($k[1]));}if(!$qo){foreach($Qa
as$W){if(!queries("ALTER TABLE ".table($Q)." $W"))return
false;}if($Q!=$_&&!queries("ALTER TABLE ".table($Q)." RENAME TO ".table($_)))return
false;}elseif(!recreate_table($Q,$_,$Qa,$Bj,$Ue,$bb,[],"","",$Wd))return
false;if($bb){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $bb WHERE name = ".q($_));if(!Connection::get()->getAffectedRows())queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($_).", $bb)");queries("COMMIT");}return
true;}function
recreate_table($Q,$_,$l,$Bj,$Ue,$bb="",$t=[],$Ad="",$Aa="",$Wd=""){if($Q!=""){if(!$l){foreach(fields($Q)as$u=>$k){if($t)$k["auto_increment"]=0;$l[]=process_field($k,$k);$Bj[$u]=idf_escape($u);}}$xk=false;foreach($l
as$k){if($k[6])$xk=true;}$Cd=[];foreach($t
as$u=>$W){if($W[2]=="DROP"){$Cd[$W[1]]=true;unset($t[$u]);}}foreach(indexes($Q)as$Rg=>$s){$d=[];foreach($s["columns"]as$u=>$c){if(!isset($Bj[$c]))continue
2;$d[]=$Bj[$c].($s["descs"][$u]?" DESC":"");}if(!$Cd[$Rg]){if($s["type"]!="PRIMARY"||!$xk){$Rg=preg_replace('~^sqlite_~',"",$Rg);$t[]=[$s["type"],$Rg,$d];}}}foreach($t
as$u=>$W){if($W[0]=="PRIMARY"){unset($t[$u]);$Ue[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($Q)as$Rg=>$o){foreach($o["source"]as$u=>$c){if(!$Bj[$c])continue
2;$o["source"][$u]=idf_unescape($Bj[$c]);}if(!isset($Ue[" $Rg"]))$Ue[]=" ".format_foreign_key($o);}queries("BEGIN");}$Db=array();foreach($l
as$k){if(preg_match('~GENERATED~',$k[3]))unset($Bj[array_search($k[0],$Bj)]);$Db[]="  ".implode($k);}$Db=array_merge($Db,array_filter($Ue));foreach(Driver::get()->checkConstraints($Q)as$Gb){if($Gb!=$Ad)$Db[]="  CHECK ($Gb)";}if($Aa)$Db[]="  CHECK ($Aa)";$tn=($Q!=""&&$Q==$_?"adminneo_$_":$_);if(!$Wd&&$Q!="")$Wd=table_status1($Q)["Engine"]??null;if(!queries("CREATE TABLE ".table($tn)." (\n".implode(",\n",$Db)."\n)".($Wd!="table"&&in_array($Wd,Driver::get()->engines())?" $Wd":"")))return
false;if($Q!=""){if($Bj&&!queries("INSERT INTO ".table($tn)." (".implode(", ",$Bj).") SELECT ".implode(", ",array_map('AdminNeo\idf_escape',array_keys($Bj)))." FROM ".table($Q)))return
false;$Vn=[];foreach(triggers($Q)as$Tn=>$Dn){$Sn=trigger($Tn,$Q);$Vn[]="CREATE TRIGGER ".idf_escape($Tn)." ".implode(" ",$Dn)." ON ".table($_)."\n$Sn[Statement]";}$bb=$bb?"":Connection::get()->getValue("SELECT seq FROM sqlite_sequence WHERE name = ".q($Q));if(!queries("DROP TABLE ".table($Q))||($Q==$_&&!queries("ALTER TABLE ".table($tn)." RENAME TO ".table($_)))||!alter_indexes($_,$t))return
false;if($bb)queries("UPDATE sqlite_sequence SET seq = $bb WHERE name = ".q($_));foreach($Vn
as$Sn){if(!queries($Sn))return
false;}queries("COMMIT");}return
true;}function
index_sql($Q,$T,$_,$d){return"CREATE $T ".($T!="INDEX"?"INDEX ":"").idf_escape($_!=""?$_:uniqid($Q."_"))." ON ".table($Q)." $d";}function
alter_indexes($Q,$b){foreach($b
as$s){if($s[0]=="PRIMARY"||(preg_match('~^sqlite_~',$s[1])))return
recreate_table($Q,$Q,[],[],[],"",$b);}foreach(array_reverse($b)as$W){if(!queries($W[2]=="DROP"?"DROP INDEX ".idf_escape($W[1]):index_sql($Q,$W[0],$W[1],"(".implode(", ",$W[2]).")")))return
false;}return
true;}function
truncate_tables($S){return
apply_queries("DELETE FROM",$S);}function
drop_views($Go){return
apply_queries("DROP VIEW",$Go);}function
drop_tables($S){return
apply_queries("DROP TABLE",$S);}function
move_tables($S,$Go,$rn){return
false;}function
trigger($_,$Q){if($_=="")return["Statement"=>"BEGIN\n\t;\nEND"];$r='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$Un=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$r\\s*(".implode("|",$Un["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($r))?\\s+ON\\s*$r\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($_)),$y);$Qi=$y[3];return["Trigger"=>$_,"Timing"=>strtoupper($y[1]),"Event"=>strtoupper($y[2]).($Qi?" OF":""),"Of"=>idf_unescape($Qi),"Statement"=>$y[4],];}function
triggers($Q){$I=[];$Un=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q))as$J){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$Un["Timing"]).')\s*(.*?)\s+ON\b~i',$J["sql"],$y);$I[$J["name"]]=[$y[1],$y[2]];}return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE"],"Type"=>["FOR EACH ROW"],];}function
begin(){return
queries("BEGIN");}function
last_id($H){return
Connection::get()->getValue("SELECT LAST_INSERT_ROWID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$R,array$Z){return
null;}function
types(){return[];}function
create_sql($Q,$bb,$Km){$I=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($Q));foreach(indexes($Q)as$_=>$s){if($_==''||strpos($_,"sqlite_")===0)continue;$I
.=";\n\n".index_sql($Q,$s['type'],$_,"(".implode(", ",array_map('AdminNeo\idf_escape',$s['columns'])).")");}return$I;}function
truncate_sql($Q){return"DELETE FROM ".table($Q);}function
trigger_sql($Q){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q)));}function
show_variables(){$I=[];foreach(get_rows("PRAGMA pragma_list")as$J){$_=$J["name"];if($_!="pragma_list"&&$_!="compile_options"){$I[$_]=[$_,''];foreach(get_rows("PRAGMA $_")as$tl)$I[$_][1].=implode(", ",$tl)."\n";}}return$I;}function
show_status(){$I=[];foreach(get_vals("PRAGMA compile_options")as$lj)$I[]=explode("=",$lj,2)+["",""];return$I;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){return$I;}function
support($ye){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$ye);}}Drivers::add("oracle","Oracle (beta)",["OCI8","PDO_OCI"]);if(isset($_GET["oracle"])){define("AdminNeo\DRIVER","oracle");define("AdminNeo\DIALECT","oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","oci8");class
OracleConnection
extends
Connection{private$connection;private$dbName=null;function
open($M,$U,$D){$this->connection=@oci_new_connect($U,$D,$M,"AL32UTF8");if($this->connection){$this->version=oci_server_version($this->connection);return
true;}$j=oci_error();$this->error=($j?$j["message"]:lang(122));return
false;}function
quote($P){return"'".str_replace("'","''",$P)."'";}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getAndClearDbName(){$_=$this->dbName?:DB;$this->dbName=null;return$_;}function
query($F,$co=false){$H=oci_parse($this->connection,$F);$this->error="";if(!$H){$j=oci_error($this->connection);$this->errno=$j["code"];$this->error=$j["message"];return
false;}set_error_handler(function($ae,$j){if(ini_bool("html_errors"))$j=html_entity_decode(strip_tags($j));$j=preg_replace('~^[^:]*: ~','',$j);$this->error=$j;});$I=@oci_execute($H);restore_error_handler();if($I){if(oci_num_fields($H))return
new
OracleResult($H);$this->affectedRows=oci_num_rows($H);oci_free_statement($H);}return$I;}function
getValue($F,$ze=0){$H=$this->query($F);return
is_object($H)?$H->fetchValue($ze):false;}}class
OracleResult
extends
Result{private$resource;private$offset=1;function
__construct($H){parent::__construct(0);$this->resource=$H;}function
fetchAssoc(){return$this->convertRow(oci_fetch_assoc($this->resource));}function
fetchRow(){return$this->convertRow(oci_fetch_row($this->resource));}function
fetchValue($ze){return
oci_fetch($this->resource)?oci_result($this->resource,$ze+1):false;}private
function
convertRow($J){if(is_array($J)){foreach($J
as$u=>$W){if(is_a($W,'OCILob')||is_a($W,'OCI-Lob'))$J[$u]=$W->load();}}return$J;}function
fetchField(){$c=$this->offset++;$_=oci_field_name($this->resource,$c);if($_===false)return
false;$T=oci_field_type($this->resource,$c);if($T===false)return
false;return(object)['name'=>$_,'native_type'=>$T,'type'=>$T,'charsetnr'=>(preg_match("~raw|blob|bfile~",$T)?63:0),];}}}elseif(extension_loaded("pdo_oci")){define("AdminNeo\DRIVER_EXTENSION","PDO_OCI");class
OracleConnection
extends
PdoConnection{private$dbName=null;function
open($M,$U,$D){return$this->dsn("oci:dbname=//$M;charset=AL32UTF8",$U,$D);}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getAndClearDbName(){$_=$this->dbName?:DB;$this->dbName=null;return$_;}}}class
OracleDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(123)=>["number"=>38,"binary_float"=>12,"binary_double"=>21,],lang(124)=>["date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28,],lang(125)=>["char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295,],lang(127)=>["raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296,],];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->insertFunctions=["date"=>"current_date","timestamp"=>"current_timestamp",];$this->editFunctions=["number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",];}function
begin(){return
true;}function
insertUpdate($Q,array$Pk,array$E){foreach($Pk
as$G){$lo=[];$Z=[];foreach($G
as$u=>$W){$lo[]="$u = $W";if(isset($E[idf_unescape($u)]))$Z[]="$u = $W";}if(!(($Z&&queries("UPDATE ".table($Q)." SET ".implode(", ",$lo)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($G)).") VALUES (".implode(", ",$G).")")))return
false;}return
true;}function
hasCStyleEscapes(){return
true;}}function
create_driver(Connection$e){return
OracleDriver::create($e,Admin::get());}function
is_server_host_valid($Of){return(bool)preg_match('~^[^/]+(/([^/:]+)?(:[^/:]+)?(/[^/:]+)?)?$~',$Of);}function
idf_escape($r){return'"'.str_replace('"','""',$r).'"';}function
table($r){return
idf_escape($r);}function
connect($E=false,&$j=null){$e=$E?OracleConnection::create():OracleConnection::createSecondary();$Hc=Admin::get()->getCredentials();if(!$e->open($Hc[0],$Hc[1],$Hc[2])){$j=$e->getError();return
null;}return$e;}function
get_databases($Se){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($F,$Z,$w,$A=0,$Rl=" "){return($A?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $F$Z) t WHERE rownum <= ".($w+$A).") WHERE rnum > $A":($w?" * FROM (SELECT $F$Z) WHERE rownum <= ".($w+$A):" $F$Z"));}function
limit1($Q,$F,$Z,$Rl="\n"){return" $F$Z";}function
db_collation($h,$Wb){return
Connection::get()->getValue("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
Connection::get()->getValue("SELECT USER FROM DUAL");}function
where_owner($tk,$Fj="owner"){if(!$_GET["ns"])return'';return"$tk$Fj = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($d){$Fj=where_owner('');return"(SELECT $d FROM all_views WHERE ".($Fj?:"rownum < 0").")";}function
tables_list(){$Eo=views_table("view_name");$Fj=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$Fj
UNION SELECT view_name, 'view' FROM $Eo
ORDER BY 1");}function
count_tables($g){$I=[];foreach($g
as$h)$I[$h]=Connection::get()->getValue("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($h));return$I;}function
table_status($_=""){$I=[];$Gl=q($_);$h=Connection::get()->getAndClearDbName();$Eo=views_table("view_name");$Fj=where_owner(" AND ","t.owner");foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE t.tablespace_name = '.q($h).$Fj.($_!=""?" AND t.table_name = $Gl":"")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM $Eo".($_!=""?" WHERE view_name = $Gl":"")."
ORDER BY 1")as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support($R){return
true;}function
fields($Q){$I=[];$Fj=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($Q)."$Fj ORDER BY column_id")as$J){$T=$J["DATA_TYPE"];$v="$J[DATA_PRECISION],$J[DATA_SCALE]";if($v==",")$v=$J["CHAR_COL_DECL_LENGTH"];$I[$J["COLUMN_NAME"]]=["field"=>$J["COLUMN_NAME"],"full_type"=>$T.($v?"($v)":""),"type"=>strtolower($T),"length"=>$v,"default"=>$J["DATA_DEFAULT"],"null"=>($J["NULLABLE"]=="Y"),"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1],];}return$I;}function
indexes($Q,$e=null){$I=[];$Fj=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($Q)."$Fj
ORDER BY ac.constraint_type, aic.column_position",$e)as$J){$dg=$J["INDEX_NAME"];$cc=$J["DATA_DEFAULT"];$cc=($cc?trim($cc,'"'):$J["COLUMN_NAME"]);$I[$dg]["type"]=($J["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($J["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$I[$dg]["columns"][]=$cc;$I[$dg]["lengths"][]=($J["CHAR_LENGTH"]&&$J["CHAR_LENGTH"]!=$J["COLUMN_LENGTH"]?$J["CHAR_LENGTH"]:null);$I[$dg]["descs"][]=($J["DESCEND"]&&$J["DESCEND"]=="DESC"?'1':null);}return$I;}function
view($_){$Eo=views_table("view_name, text");$K=get_rows('SELECT text "select" FROM '.$Eo.' WHERE view_name = '.q($_));return
reset($K);}function
collations(){return[];}function
information_schema($h){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
h(Connection::get()->getError());}function
explain(Connection$e,$F){$e->query("EXPLAIN PLAN FOR $F");return$e->query("SELECT * FROM plan_table");}function
found_rows(array$R,array$Z){return
null;}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$b=$_d=[];$yj=($Q?fields($Q):[]);foreach($l
as$k){$W=$k[1];if($W&&$k[0]!=""&&idf_escape($k[0])!=$W[0])queries("ALTER TABLE ".table($Q)." RENAME COLUMN ".idf_escape($k[0])." TO $W[0]");$xj=$yj[$k[0]];if($W&&$xj){$Vi=process_field($xj,$xj);if($W[2]==$Vi[2])$W[2]="";}if($W)$b[]=($Q!=""?($k[0]!=""?"MODIFY (":"ADD ("):"  ").implode($W).($Q!=""?")":"");else$_d[]=idf_escape($k[0]);}if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($Q)."\n".implode("\n",$b)))&&(!$_d||queries("ALTER TABLE ".table($Q)." DROP (".implode(", ",$_d).")"))&&($Q==$_||queries("ALTER TABLE ".table($Q)." RENAME TO ".table($_)));}function
alter_indexes($Q,$b){$_d=[];$Hk=[];foreach($b
as$W){if($W[0]!="INDEX"){$W[2]=preg_replace('~ DESC$~','',$W[2]);$Dc=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");array_unshift($Hk,"ALTER TABLE ".table($Q).$Dc);}elseif($W[2]=="DROP")$_d[]=idf_escape($W[1]);else$Hk[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q)." (".implode(", ",$W[2]).")";}if($_d)array_unshift($Hk,"DROP INDEX ".implode(", ",$_d));foreach($Hk
as$F){if(!queries($F))return
false;}return
true;}function
foreign_keys($Q){$I=[];$F="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($Q);foreach(get_rows($F)as$J)$I[$J['NAME']]=["db"=>$J['DEST_DB'],"table"=>$J['DEST_TABLE'],"source"=>[$J['SRC_COLUMN']],"target"=>[$J['DEST_COLUMN']],"on_delete"=>$J['ON_DELETE'],"on_update"=>null,];return$I;}function
backward_keys($Q){return[];}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($Go){return
apply_queries("DROP VIEW",$Go);}function
drop_tables($S){return
apply_queries("DROP TABLE",$S);}function
last_id($H){return
0;}function
schemas(){$I=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return$I?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1");}function
get_schema(){return
Connection::get()->getValue("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($Cl,$e=null){if(!$e)$e=Connection::get();return(bool)$e->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($Cl));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$I=[];$K=get_rows('SELECT * FROM v$instance');foreach(reset($K)as$u=>$W)$I[]=[$u,$W];return$I;}function
process_list(){return
get_rows('SELECT sess.process AS "process", sess.username AS "user", sess.schemaname AS "schema", sess.status AS "status", sess.wait_class AS "wait_class", sess.seconds_in_wait AS "seconds_in_wait", sql.sql_text AS "sql_text", sess.machine AS "machine", sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){return$I;}function
support($ye){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$ye);}}Drivers::add("mongo","MongoDB (alpha)",["mongodb"]);if(isset($_GET["mongo"])){define("AdminNeo\DRIVER","mongo");define("AdminNeo\DIALECT","mongo");if(class_exists('MongoDB\Driver\Manager')){define("AdminNeo\DRIVER_EXTENSION","MongoDB");class
MongoConnection
extends
Connection{private$manager;private$dbName;function
getDefaultServerName(){return"localhost:27017";}function
open($M,$U,$D,$Tc="",$Za=""){$this->version=MONGODB_VERSION;$B=[];if($U.$D!=""){$B["username"]=$U;$B["password"]=$D;}if($Tc!="")$B["db"]=$Tc;if($Za!="")$B["authSource"]=$Za;$this->manager=new
Manager($M,$B);$this->dbName=$Tc?:"default";return(bool)$this->executeCommand(['ping'=>1]);}function
executeCommand(array$ec,$Da=false){try{return$this->manager->executeCommand($Da?"admin":$this->dbName,new
Command($ec));}catch(\MongoDB\Driver\Exception\Exception$he){$this->error=$he->getMessage();return
null;}}function
executeQuery($xi,Query$F,$B=null){try{return$this->manager->executeQuery($xi,$F,$B);}catch(\MongoDB\Driver\Exception\Exception$he){$this->error=$he->getMessage();return
null;}}function
executeBulkWrite($xi,BulkWrite$ub,$Cc){try{$kl=$this->manager->executeBulkWrite($xi,$ub);$this->affectedRows=$kl->$Cc();return
true;}catch(Exception$he){$this->error=$he->getMessage();return
false;}}function
query($F,$co=false){return
false;}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getDbName(){return$this->dbName;}function
quote($P){return$P;}}class
MongoResult
extends
Result{private$rows;private$charset;private$offset=0;function
__construct($H){$this->rows=$this->charset=[];foreach($H
as$Hg){$J=[];foreach($Hg
as$u=>$W){if(is_a($W,'MongoDB\BSON\Binary'))$this->charset[$u]=63;$J[$u]=(is_a($W,'MongoDB\BSON\ObjectID')?'MongoDB\BSON\ObjectID("'."$W\")":(is_a($W,'MongoDB\BSON\UTCDatetime')?$W->toDateTime()->format('Y-m-d H:i:s'):(is_a($W,'MongoDB\BSON\Binary')?$W->getData():(is_a($W,'MongoDB\BSON\Regex')?"$W":(is_object($W)||is_array($W)?json_encode($W,JSON_UNESCAPED_UNICODE):$W)))));}$this->rows[]=$J;foreach($J
as$u=>$W){if(!isset($this->rows[0][$u]))$this->rows[0][$u]=null;}}parent::__construct(count($this->rows));}function
fetchAssoc(){$J=current($this->rows);if(!$J)return$J;$f=[];foreach($this->rows[0]as$u=>$W)$f[$u]=$J[$u];next($this->rows);return$f;}function
fetchRow(){$f=$this->fetchAssoc();if(!$f)return$f;return
array_values($f);}function
fetchField(){$Sg=array_keys($this->rows[0]);$_=$Sg[$this->offset++];return(object)['name'=>$_,'type'=>15,'charsetnr'=>$this->charset[$_],];}}}class
MongoDriver
extends
Driver{static$NULL="\0";var$primary="_id";protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->operators=["=","!=",">","<",">=","<=","regex","(f)=","(f)!=","(f)>","(f)<","(f)>=","(f)<=","(date)=","(date)!=","(date)>","(date)<","(date)>=","(date)<=",];$this->likeOperator="=";$this->insertFunctions=["json"];$this->systemDatabases=["admin","config","local"];}function
select($Q,array$L,array$Z,array$rf,array$pj=[],$w=1,$Hj=0,$yk=false){$Ie=where_to_query($Z);$L=($L==["*"]?[]:array_fill_keys($L,1));if(count($L)&&!isset($L['_id']))$L['_id']=0;$B=$L?['projection'=>$L]:[];$rm=[];foreach($pj
as$W){$W=preg_replace('~ DESC$~','',$W,1,$Bc);$rm[$W]=($Bc?-1:1);}if($rm)$B['sort']=$rm;$w=min(200,max(1,$w));$pm=$Hj*$w;$B+=['limit'=>$w,'skip'=>$pm];$F=new
Query($Ie,$B);$Dm=microtime(true);try{$H=new
MongoResult(Connection::get()->executeQuery(Connection::get()->getDbName().".$Q",$F));}catch(Exception$Hd){Connection::get()->setError($Hd->getMessage());$H=false;}if($yk)echo$this->admin->formatSelectQuery('find('.json_encode(($Ie?["filter"=>$Ie]:[])+$B).")",$Dm,!$H);return$H;}function
update($Q,array$G,$Kk,$w=0,$Rl="\n"){$Ie=sql_query_where_parser($Kk);if(isset($G['_id']))unset($G['_id']);$cl=[];foreach($G
as$u=>$X){if($X==self::$NULL){$cl[$u]=1;unset($G[$u]);}}$Oi=['$set'=>$G];if(count($cl))$Oi['$unset']=$cl;$B=['upsert'=>false];queries('update('.json_encode(($Ie?["filter"=>$Ie]:[])+$Oi+$B).")");$ub=new
BulkWrite();$ub->update($Ie,$Oi,$B);return
Connection::get()->executeBulkWrite(Connection::get()->getDbName().".$Q",$ub,'getModifiedCount');}function
delete($Q,$Kk,$w=0){$Ie=sql_query_where_parser($Kk);$B=$w?['limit'=>$w]:[];queries('delete('.json_encode(($Ie?["filter"=>$Ie]:[])+$B).")");$ub=new
BulkWrite();$ub->delete($Ie,$B);return
Connection::get()->executeBulkWrite(Connection::get()->getDbName().".$Q",$ub,'getDeletedCount');}function
insert($Q,array$G){if($G['_id']=='')unset($G['_id']);foreach($G
as$u=>$X){if($X==self::$NULL)unset($G[$u]);}queries('insert('.json_encode($G).")");$ub=new
BulkWrite();$ub->insert($G);return
Connection::get()->executeBulkWrite(Connection::get()->getDbName().".$Q",$ub,'getInsertedCount');}function
getNull(){return
self::$NULL;}}function
create_driver(Connection$e){return
MongoDriver::create($e,Admin::get());}function
get_databases($Se){$Lc=Connection::get()->executeCommand(['listDatabases'=>1],true);if(!$Lc)return[];$g=[];foreach($Lc
as$Wc){foreach($Wc->databases
as$h)$g[]=$h->name;}return$g;}function
count_tables($g){$I=[];return$I;}function
tables_list(){$Lc=Connection::get()->executeCommand(['listCollections'=>1]);if(!$Lc)return[];$Xb=[];foreach($Lc
as$H)$Xb[$H->name]='table';return$Xb;}function
drop_databases($g){return
false;}function
indexes($Q,$e=null){$Lc=Connection::get()->executeCommand(['listIndexes'=>$Q]);if(!$Lc)return[];$t=[];foreach($Lc
as$s){$jd=[];$d=[];foreach(get_object_vars($s->key)as$c=>$T){$jd[]=($T==-1?'1':null);$d[]=$c;}$t[$s->name]=["type"=>($s->name=="_id_"?"PRIMARY":(isset($s->unique)?"UNIQUE":"INDEX")),"columns"=>$d,"lengths"=>[],"descs"=>$jd,];}return$t;}function
fields($Q){$l=fields_from_edit();if(!$l){$H=Driver::get()->select($Q,["*"],[],[],[],10);if($H){while($J=$H->fetchAssoc()){foreach($J
as$u=>$W){$J[$u]=null;$l[$u]=["field"=>$u,"full_type"=>"varchar","type"=>"varchar","null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary),"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1,],];}}}}return$l;}function
found_rows(array$R,array$Z){$Z=where_to_query($Z);$Lc=Connection::get()->executeCommand(['count'=>$R['Name'],'query'=>$Z]);if(!$Lc)return
null;return(int)$Lc->toArray()[0]->n;}function
sql_query_where_parser($Kk){$Kk=preg_replace('~^\s*WHERE\s*~',"",$Kk);while($Kk[0]=="(")$Kk=preg_replace('~^\((.*)\)$~',"$1",$Kk);$Vo=explode(' AND ',$Kk);$Wo=explode(') OR (',$Kk);$Z=[];foreach($Vo
as$To)$Z[]=trim($To);if(count($Wo)==1)$Wo=[];elseif(count($Wo)>1)$Z=[];return
where_to_query($Z,$Wo);}function
where_to_query($Ro=[],$So=[]){$f=[];foreach(['and'=>$Ro,'or'=>$So]as$T=>$Z){if(is_array($Z)){foreach($Z
as$pe){list($Tb,$ej,$W)=explode(" ",$pe,3);if($Tb=="_id"&&preg_match('~^(MongoDB\\\\BSON\\\\ObjectID)\("(.+)"\)$~',$W,$y)){list(,$Qb,$W)=$y;$W=new$Qb($W);}if(!in_array($ej,Admin::get()->getOperators()))continue;if(preg_match('~^\(f\)(.+)~',$ej,$y)){$W=(float)$W;$ej=$y[1];}elseif(preg_match('~^\(date\)(.+)~',$ej,$y)){$Sc=new
DateTime($W);$W=new
BSON\UTCDatetime($Sc->getTimestamp()*1000);$ej=$y[1];}switch($ej){case'=':$ej='$eq';break;case'!=':$ej='$ne';break;case'>':$ej='$gt';break;case'<':$ej='$lt';break;case'>=':$ej='$gte';break;case'<=':$ej='$lte';break;case'regex':$ej='$regex';break;default:continue
2;}if($T=='and')$f['$and'][]=[$Tb=>[$ej=>$W]];elseif($T=='or')$f['$or'][]=[$Tb=>[$ej=>$W]];}}}return$f;}function
table($r){return$r;}function
idf_escape($r){return$r;}function
table_status($_="",$xe=false){$I=[];foreach(($_!=""?[$_=>1]:tables_list())as$Q=>$T)$I[$Q]=["Name"=>$Q,"Engine"=>""];return$I;}function
create_database($h,$Vb){return
true;}function
last_id($H){return
0;}function
error(){return
h(Connection::get()->getError());}function
collations(){return[];}function
logged_user(){$Hc=Admin::get()->getCredentials();return$Hc[1];}function
connect($E=false,&$j=null){$e=$E?MongoConnection::create():MongoConnection::createSecondary();list($M,$U,$D)=Admin::get()->getCredentials();$Bh=$_SESSION["db"][DRIVER][SERVER][$U];if($M=="")$M="localhost:27017";$Tc=Admin::get()->getDatabase();$Za=getenv("MONGO_AUTH_SOURCE")?:key($Bh);if(!$e->open("mongodb://$M",$U,$D,$Tc,$Za)){$j=$e->getError();return
null;}return$e;}function
alter_indexes($Q,$b){foreach($b
as$W){list($T,$_,$gm)=$W;if($gm=="DROP")$Lc=Connection::get()->executeCommand(["dropIndexes"=>$Q,"index"=>$_]);else{$d=[];foreach($gm
as$c){$c=preg_replace('~ DESC$~','',$c,1,$Bc);$d[$c]=$Bc?-1:1;}$ec=["createIndexes"=>$Q,"indexes"=>[["key"=>$d,"name"=>$_,"unique"=>$T=="UNIQUE",]],];$Lc=Connection::get()->executeCommand($ec);}if(!$Lc)return
false;}return
true;}function
support($ye){return
preg_match("~database|indexes|descidx~",$ye);}function
db_collation($h,$Wb){}function
information_schema($h){return
false;}function
is_view(array$R){return
false;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){return$I;}function
foreign_keys($Q){return[];}function
backward_keys($Q){return[];}function
fk_support($R){}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){if($Q=="")return(bool)Connection::get()->executeCommand(["create"=>$_]);return
false;}function
drop_tables($S){foreach($S
as$_){if(!Connection::get()->executeCommand(["drop"=>$_]))return
false;}return
true;}function
truncate_tables($S){foreach($S
as$_){$ec=["delete"=>$_,"deletes"=>[["q"=>(object)[],"limit"=>0,]],];if(!Connection::get()->executeCommand($ec))return
false;}return
true;}}Drivers::add("elastic","Elasticsearch 7 (beta)",["json + allow_url_fopen"]);if(isset($_GET["elastic"])){define("AdminNeo\DRIVER","elastic");define("AdminNeo\DIALECT","elastic");if(ini_bool('allow_url_fopen')){define("AdminNeo\ELASTIC_DB_NAME","elastic");define("AdminNeo\DRIVER_EXTENSION","JSON");class
ElasticConnection
extends
Connection{private$serviceUrl;function
getDefaultServerName(){return"localhost:9200";}function
rootQuery($ak,$xc=null,$gi='GET'){$B=['http'=>['method'=>$gi,'content'=>$xc!==null?json_encode($xc):null,'header'=>$xc!==null?'Content-Type: application/json':[],'ignore_errors'=>1,'follow_location'=>0,'max_redirects'=>0,],];$Wn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Wn)$B["ssl"]=["verify_peer"=>false];list($m,$Ff)=get_url("$this->serviceUrl/".ltrim($ak,'/'),stream_context_create($B));if($m===false){$this->error=lang(3);return
false;}$zg=false;foreach($Ff
as$Ef){if(!strcasecmp($Ef,"X-elastic-product: Elasticsearch")){$zg=true;break;}}if(!$zg){$this->error=lang(3);return
false;}$I=json_decode($m,true);if($I===null){$this->error=lang(3);return
false;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',isset($Ff[0])?$Ff[0]:'')){if(isset($I['error']['root_cause'][0]['type']))$this->error=$I['error']['root_cause'][0]['type'].": ".$I['error']['root_cause'][0]['reason'];elseif(isset($I['status'])&&isset($I['error'])&&is_string($I['error']))$this->error=$I['error'];return
false;}return$I;}function
query($F,$co=false){return
false;}function
sendRequest($ak,$xc=null,$gi='GET'){if($ak!=""&&$ak[0]=="S"&&preg_match('/SELECT 1 FROM ([^ ]+) WHERE (.+) LIMIT ([0-9]+)/',$ak,$z)){$Z=explode(" AND ",$z[2]);return
Driver::get()->select($z[1],["*"],$Z,[],[],$z[3]);}return$this->rootQuery($ak,$xc,$gi);}function
open($M,$U,$D){$this->serviceUrl=build_http_url($M,$U,$D,"localhost",9200);if(!$this->serviceUrl){$this->error=lang(3);return
false;}$I=$this->sendRequest('');if(!$I)return
false;if(!isset($I['version']['number'])){$this->error=lang(3);return
false;}$this->version=$I['version']['number'];return
true;}function
selectDatabase($_){return
true;}function
quote($P){return$P;}}class
ElasticResult
extends
Result{private$rows;function
__construct(array$K){parent::__construct(count($K));$this->rows=$K;reset($this->rows);}function
fetchAssoc(){$I=current($this->rows);next($this->rows);return$I;}function
fetchRow(){$J=$this->fetchAssoc();return$J?array_values($J):false;}function
fetchField(){return
false;}}}class
ElasticDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(123)=>["long"=>3,"integer"=>5,"short"=>8,"byte"=>10,"double"=>20,"float"=>66,"half_float"=>12,"scaled_float"=>21,"boolean"=>1,],lang(124)=>["date"=>10,],lang(125)=>["string"=>65535,"text"=>65535,"keyword"=>65535,],lang(127)=>["binary"=>255,],];$this->operators=["must(term)","must(match)","must(regexp)","should(term)","should(match)","should(regexp)","must_not(term)","must_not(match)","must_not(regexp)",];$this->likeOperator="should(match)";$this->insertFunctions=["json"];}function
select($Q,array$L,array$Z,array$rf,array$pj=[],$w=1,$Hj=0,$yk=false){$f=[];if($L!=["*"])$f["fields"]=array_values($L);if($pj){$rm=[];foreach($pj
as$Tb){$Tb=preg_replace('~ DESC$~','',$Tb,1,$Bc);$rm[]=($Bc?[$Tb=>"desc"]:$Tb);}$f["sort"]=$rm;}if($w){$f["size"]=$w;if($Hj)$f["from"]=($Hj*$w);}foreach($Z
as$W){if(preg_match('~^\((.+ OR .+)\)$~',$W,$z)){$Yj=explode(" OR ",$z[1]);foreach($Yj
as$Oj)$this->addQueryCondition($Oj,$f);}else$this->addQueryCondition($W,$f);}$F="$Q/_search";$Dm=microtime(true);$Gl=$this->connection->rootQuery($F,$f);if($yk)echo$this->admin->formatSelectQuery("\"GET $F\": ".json_encode($f),$Dm,!$Gl);if(empty($Gl))return
false;$Xm=$L==["*"]?array_keys(fields($Q)):[];$I=[];foreach($Gl["hits"]["hits"]as$Kf){$J=[];if($L==["*"])$J["_id"]=$Kf["_id"];if($L!=["*"]){$l=[];foreach($L
as$u)$l[$u]=$u=="_id"?$Kf["_id"]:$Kf["_source"][$u];}else{foreach($Xm
as$u)$l[$u]=$u=="_id"?$Kf["_id"]:$Kf["_source"][$u];}foreach($l
as$u=>$W)$J[$u]=(is_array($W)?json_encode($W):$W);$I[]=$J;}return
new
ElasticResult($I);}private
function
addQueryCondition($W,&$f){list($Tb,$ej,$W)=explode(" ",$W,3);if($Tb=="_id"&&$ej=="="){$f["query"]["bool"]["must"][]=["term"=>[$Tb=>$W]];return;}if(!preg_match('~^([^(]+)\(([^)]+)\)$~',$ej,$z))return;$Jk=$z[1];$Jh=$z[2];if($Jh=="regexp")$f["query"]["bool"][$Jk][]=["regexp"=>[$Tb=>["value"=>$W,"flags"=>"ALL","case_insensitive"=>true,]]];else$f["query"]["bool"][$Jk][]=[$Jh=>[$Tb=>$W]];}function
update($Q,array$G,$Kk,$w=0,$Rl="\n"){$Yj=preg_split('~ *= *~',$Kk);if(count($Yj)!=2)return
false;$q=trim($Yj[1]);$F="$Q/_doc/$q";queries("\"POST $F\": ".json_encode($G));$hl=$this->connection->sendRequest($F,$G,'POST');if($hl)$this->connection->sendRequest("$Q/_refresh");return$hl;}function
insert($Q,array$G){$F="$Q/_doc/";if(isset($G["_id"])&&$G["_id"]!="NULL"){$F
.=$G["_id"];unset($G["_id"]);}foreach($G
as$u=>$X){if($X=="NULL")unset($G[$u]);}queries("\"POST $F\": ".json_encode($G));$hl=$this->connection->sendRequest($F,$G,'POST');if(!$hl)return
false;$this->connection->sendRequest("$Q/_refresh");$this->connection->last_id=$hl['_id'];return$hl['result'];}function
delete($Q,$Kk,$w=0){$Vf=[];if(isset($_GET["where"]["_id"])?$_GET["where"]["_id"]:null)$Vf[]=$_GET["where"]["_id"];if(isset($_POST['check'])){foreach($_POST['check']as$Gb){$Yj=preg_split('~ *= *~',$Gb);if(count($Yj)==2)$Vf[]=trim($Yj[1]);}}$Ga=0;foreach($Vf
as$q){$F="$Q/_doc/$q";queries("\"DELETE $F\"");$hl=$this->connection->sendRequest($F,null,'DELETE');if(isset($hl['result'])&&$hl['result']=='deleted')$Ga++;}$this->connection->sendRequest("$Q/_refresh");$this->connection->setAffectedRows($Ga);return$Ga;}}function
create_driver(Connection$e){return
ElasticDriver::create($e,Admin::get());}function
connect($E=false,&$j=null){$e=$E?ElasticConnection::create():ElasticConnection::createSecondary();list($M,$U,$D)=Admin::get()->getCredentials();if(!$e->openPasswordless($M,$U,$D)){$j=$e->getError();return
null;}return$e;}function
support($ye){return
preg_match("~table|columns~",$ye);}function
logged_user(){$Hc=Admin::get()->getCredentials();return$Hc[1];}function
get_databases($Se){return[ELASTIC_DB_NAME];}function
limit($F,$Z,$w,$A=0,$Rl=" "){return" $F$Z".($w?$Rl."LIMIT $w".($A?" OFFSET $A":""):"");}function
collations(){return[];}function
db_collation($h,$Wb){}function
count_tables($g){$I=Connection::get()->rootQuery('_aliases');if(empty($I))return[ELASTIC_DB_NAME=>0];return[ELASTIC_DB_NAME=>count($I)];}function
tables_list(){$Ka=Connection::get()->rootQuery('_aliases');if(empty($Ka))return[];ksort($Ka);$S=[];foreach($Ka
as$_=>$s){if($_[0]==".")continue;$S[$_]="table";ksort($s["aliases"]);$S+=array_fill_keys(array_keys($s["aliases"]),"view");}return$S;}function
table_status($_="",$xe=false){$Fm=Connection::get()->rootQuery('_stats');$Ka=Connection::get()->rootQuery('_aliases');if(empty($Fm)||empty($Ka))return[];$H=[];if($_!=""){if(isset($Fm["indices"][$_]))return[format_index_status($_,$Fm["indices"][$_])];else
foreach($Ka
as$dg=>$s){foreach($s["aliases"]as$Ja=>$Ia){if($Ja==$_)return[format_alias_status($Ja,$Fm["indices"][$dg])];}}return[];}ksort($Fm["indices"]);foreach($Fm["indices"]as$_=>$s){if($_[0]==".")continue;$H[$_]=format_index_status($_,$s);if(!empty($Ka[$_]["aliases"])){ksort($Ka[$_]["aliases"]);foreach($Ka[$_]["aliases"]as$Ja=>$Ia)$H[$Ja]=format_alias_status($Ja,$Fm["indices"][$_]);}}return$H;}function
format_index_status($_,$s){return["Name"=>$_,"Engine"=>"Lucene","Oid"=>$s["uuid"],"Rows"=>$s["total"]["docs"]["count"],"Auto_increment"=>0,"Data_length"=>$s["total"]["store"]["size_in_bytes"],"Index_length"=>0,"Data_free"=>$s["total"]["store"]["reserved_in_bytes"],];}function
format_alias_status($_,$s){return["Name"=>$_,"Engine"=>"view","Rows"=>$s["total"]["docs"]["count"],];}function
is_view(array$R){return$R["Engine"]=="view";}function
view($_){$I=Connection::get()->rootQuery("_alias/".urlencode($_));return["select"=>implode("\n",array_keys($I))];}function
error(){return
h(Connection::get()->getError());}function
information_schema($h){return
false;}function
indexes($Q,$e=null){return[["type"=>"PRIMARY","columns"=>["_id"]],];}function
fields($Q){$Eh=Connection::get()->rootQuery("_mapping");if(!isset($Eh[$Q])){$Ka=Connection::get()->rootQuery('_aliases');foreach($Ka
as$dg=>$s){foreach($s["aliases"]as$Ja=>$Ia){if($Ja==$Q){$Q=$dg;break;}}}}$Fh=isset($Eh[$Q]["mappings"]["properties"])?$Eh[$Q]["mappings"]["properties"]:[];$H=["_id"=>["field"=>"_id","full_type"=>"_id","type"=>"_id","null"=>true,"privileges"=>["insert"=>1,"select"=>1,"where"=>1,"order"=>1],]];foreach($Fh
as$_=>$k)$H[$_]=["field"=>$_,"full_type"=>$k["type"],"type"=>$k["type"],"null"=>true,"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>!isset($k["index"])||$k["index"]?:null,"order"=>$k["type"]!="text"?:null],];return$H;}function
foreign_keys($Q){return[];}function
backward_keys($Q){return[];}function
table($r){return$r;}function
idf_escape($r){return$r;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){return$I;}function
fk_support($R){}function
found_rows(array$R,array$Z){return
null;}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$Ek=[];foreach($l
as$k){if(!isset($k[1]))continue;$Be=trim($k[1][0]);$De=trim($k[1][1]?:"text");$Ek[$Be]=["type"=>$De,];}$Fh=$Ek?["properties"=>$Ek]:new
\stdClass();if($Q!=""){queries("\"POST $_/_mapping\": ".json_encode($Fh));return(bool)Connection::get()->rootQuery("$_/_mapping",$Fh,"POST");}else{$xc=["mappings"=>$Fh];queries("\"PUT $_\": ".json_encode($xc));return(bool)Connection::get()->rootQuery($_,$xc,"PUT");}}function
drop_views(array$S){$I=Connection::get()->rootQuery('_aliases',['actions'=>array_map(function($Q){return['remove'=>['index'=>'*','alias'=>$Q]];},$S)],'POST');return$I&&!$I['errors'];}function
drop_tables($S){$I=true;foreach($S
as$Q){$Q=urlencode($Q);queries("\"DELETE $Q\"");$I=$I&&Connection::get()->sendRequest($Q,null,'DELETE');}return$I;}function
last_id($H){return
Connection::get()->last_id;}}Drivers::add("clickhouse","ClickHouse (alpha)",["allow_url_fopen"]);if(isset($_GET["clickhouse"])){define("AdminNeo\DRIVER","clickhouse");define("AdminNeo\DIALECT","clickhouse");if(ini_bool('allow_url_fopen')){define("AdminNeo\DRIVER_EXTENSION","JSON");class
ClickHouseConnection
extends
Connection{private$serviceUrl;private$dbName='default';function
getDefaultServerName(){return"localhost:8123";}function
rootQuery($h,$F){list($m,$Ff)=get_url("$this->serviceUrl/?database=$h",stream_context_create(['http'=>['method'=>'POST','content'=>$F,'header'=>['Content-Type: application/x-www-form-urlencoded','X-ClickHouse-Format: JSONCompact',],'ignore_errors'=>1,'follow_location'=>0,'max_redirects'=>0,]]));if($m===false){$this->error=lang(3);return
false;}$yg=false;foreach($Ff
as$Ef){if(preg_match('~^X-ClickHouse-Summary:~i',$Ef)){$yg=true;break;}}if(!$yg){$this->error=lang(3);return
false;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',isset($Ff[0])?$Ff[0]:'')){$this->error=preg_replace('~\(version [^(]+\(.+$~','',$m);return
false;}if(!$this->isQuerySelectLike($F)&&$m==='')return
true;$I=json_decode($m,true);if($I===null){$this->error=lang(3);return
false;}if(!isset($I['rows'])||!isset($I['data'])||!isset($I['meta'])){$this->error=lang(3);return
false;}return
new
ClickHouseResult($I['rows'],$I['data'],$I['meta']);}private
function
isQuerySelectLike($F){return(bool)preg_match('~^\s*(select|show|with)~i',$F);}function
query($F,$co=false){return$this->rootQuery($this->dbName,$F);}function
open($M,$U,$D){$this->serviceUrl=build_http_url($M,$U,$D,"localhost",8123);if(!$this->serviceUrl){$this->error=lang(3);return
false;}$H=$this->query("SELECT version()");if(!$H)return
false;$this->version=$H->fetchRow()[0];return
true;}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getDbName(){return$this->dbName;}function
quote($P){return"'".addcslashes($P,"\\'")."'";}function
getValue($F,$ze=0){$H=$this->query($F);return$H['data'];}}class
ClickHouseResult
extends
Result{private$rows;private$columns;private$meta;private$offset=0;function
__construct($ul,array$f,array$ci){parent::__construct($ul);$this->rows=[];foreach($f
as$Hg){$this->rows[]=array_map(function($W){return
is_scalar($W)?$W:json_encode($W,JSON_UNESCAPED_UNICODE);},$Hg);}$this->meta=$ci;$this->columns=array_map(function($c){return$c['name'];},$ci);reset($this->rows);}function
fetchAssoc(){$J=current($this->rows);next($this->rows);return$J!==false?array_combine($this->columns,$J):false;}function
fetchRow(){$J=current($this->rows);next($this->rows);return$J;}function
fetchField(){$c=$this->offset++;if($c>=count($this->columns))return
false;$c=$this->meta[$c];return(object)['name'=>$c['name'],'type'=>$c['type'],'charsetnr'=>0,];}}}class
ClickHouseDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(123)=>["Int8"=>3,"Int16"=>5,"Int32"=>10,"Int64"=>19,"UInt8"=>3,"UInt16"=>5,"UInt32"=>10,"UInt64"=>20,"Float32"=>7,"Float64"=>16,'Decimal'=>38,'Decimal32'=>9,'Decimal64'=>18,'Decimal128'=>38,],lang(124)=>["Date"=>13,"DateTime"=>20,],lang(125)=>["String"=>0,],lang(127)=>["FixedString"=>0,],];$this->operators=["=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->systemDatabases=["INFORMATION_SCHEMA","information_schema","system"];}function
delete($Q,$Kk,$w=0){if($Kk==='')$Kk='WHERE 1=1';return
queries("ALTER TABLE ".table($Q)." DELETE $Kk");}function
update($Q,array$G,$Kk,$w=0,$Rl="\n"){$Y=[];foreach($G
as$u=>$W)$Y[]="$u = $W";$F=$Rl.implode(",$Rl",$Y);return
queries("ALTER TABLE ".table($Q)." UPDATE $F$Kk");}function
engines(){return['MergeTree'];}}function
create_driver(Connection$e){return
ClickHouseDriver::create($e,Admin::get());}function
idf_escape($r){return"`".str_replace("`","``",$r)."`";}function
table($r){return
idf_escape($r);}function
explain(Connection$e,$F){return
false;}function
found_rows(array$R,array$Z){$K=get_vals("SELECT COUNT(*) FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):""));return$K?(int)$K[0]:null;}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){$b=$pj=[];$bl=[];foreach($l
as$k){if($k[1][2]===" NULL"){$k[1][1]=" Nullable(".(ltrim($k[1][1])).")";$k[1][2]='';}elseif($k[1][2]===' NOT NULL')$k[1][2]='';if($k[1][3]==""&&Connection::get()->isMinVersion("20.10"))$bl[]="MODIFY COLUMN ".idf_escape($k[0])." REMOVE DEFAULT";$b[]=($k[1]?($Q!=""?($k[0]!=""?"MODIFY COLUMN ":"ADD COLUMN "):" ").implode($k[1]):"DROP COLUMN ".idf_escape($k[0]));$pj[]=$k[1][0];}$b=array_merge($b,$bl,$Ue);$O=($Wd?" ENGINE ".$Wd:"");if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)$O".' ORDER BY ('.implode(',',$pj).')');if($Q!=$_){$H=(bool)queries("RENAME TABLE ".table($Q)." TO ".table($_));if($b)$Q=$_;else
return$H;}if($O)$b[]=ltrim($O);return!$b||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b));}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($Go){return
drop_tables($Go);}function
drop_tables($S){return
apply_queries("DROP TABLE",$S);}function
is_server_host_valid($Of){return
strpos(rtrim($Of,'/'),'/')===false;}function
connect($E=false,&$j=null){$e=$E?ClickHouseConnection::create():ClickHouseConnection::createSecondary();$Hc=Admin::get()->getCredentials();if(!$e->open($Hc[0],$Hc[1],$Hc[2])){$j=$e->getError();return
null;}return$e;}function
get_databases($Se){$H=get_rows('SHOW DATABASES');$g=[];foreach($H
as$J)$g[]=$J['name'];sort($g);return$g;}function
limit($F,$Z,$w,$A=0,$Rl=" "){return" $F$Z".($w?$Rl."LIMIT ".($A?"$A, ":"").$w:"");}function
limit1($Q,$F,$Z,$Rl="\n"){return
limit($F,$Z,1,0,$Rl);}function
db_collation($h,$Wb){}function
logged_user(){$Hc=Admin::get()->getCredentials();return$Hc[1];}function
tables_list(){$H=get_rows('SHOW TABLES');$I=[];foreach($H
as$J)$I[$J['name']]='table';ksort($I);return$I;}function
count_tables($g){return[];}function
table_status($_="",$xe=false){$I=[];$S=get_rows("SELECT name, engine FROM system.tables WHERE database = ".q(Connection::get()->getDbName()).($_!=""?" AND name = ".q($_):""));foreach($S
as$Q)$I[$Q['name']]=['Name'=>$Q['name'],'Engine'=>$Q['engine'],];return$I;}function
is_view(array$R){return
false;}function
fk_support($R){return
false;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$I){if(in_array($k['type'],["Int8","Int16","Int32","Int64","UInt8","UInt16","UInt32","UInt64","Float32","Float64"]))return"to$k[type]($I)";return$I;}function
fields($Q){$I=[];$H=get_rows("SELECT name, type, default_expression FROM system.columns WHERE ".idf_escape('table')." = ".q($Q));foreach($H
as$J){$T=$J['type'];$Ki=str_contains($T,'Nullable(');$T=preg_replace('~Nullable\(([^)]+)\)~',"$1",$T);$I[$J['name']]=["field"=>$J['name'],"full_type"=>$T,"type"=>$T,"default"=>$J['default_expression']!=""?preg_replace('~^\'(.*)\'$~',"$1",$J['default_expression']):null,"null"=>$Ki,"auto_increment"=>'0',"privileges"=>["insert"=>1,"select"=>1,"update"=>0,"where"=>1,"order"=>1],];}return$I;}function
indexes($Q,$e=null){return[];}function
foreign_keys($Q){return[];}function
backward_keys($Q){return[];}function
collations(){return[];}function
information_schema($h){return
false;}function
error(){return
h(Connection::get()->getError());}function
types(){return[];}function
auto_increment(){return"";}function
last_id($H){return
0;}function
support($ye){return
preg_match("~^(columns|sql|status|table|drop_col)$~",$ye);}}Drivers::add("simpledb","SimpleDB",["SimpleXML + allow_url_fopen"]);if(isset($_GET["simpledb"])){define("AdminNeo\DRIVER","simpledb");define("AdminNeo\DIALECT","simpledb");if(class_exists('SimpleXMLElement')&&ini_bool('allow_url_fopen')){define("AdminNeo\DRIVER_EXTENSION","SimpleXML");class
SimpleDbConnection
extends
Connection{private$serviceUrl;var$timeout=0;var$next;function
open($M,$U,$D){if($M=='')$M="https://sdb.amazonaws.com";$Yj=parse_url($M);if(!$Yj||!isset($Yj['host'])||!preg_match('~^sdb\.([a-z0-9-]+\.)?amazonaws\.com$~i',$Yj['host'])||isset($Yj['port'])){$this->error=lang(3);return
false;}$this->serviceUrl=build_http_url($M,"","","");if(!$this->serviceUrl){$this->error=lang(3);return
false;}$this->version='2009-04-15';return(bool)sdb_request('ListDomains',['MaxNumberOfDomains'=>1],$this);}function
getServiceUrl(){return$this->serviceUrl;}function
selectDatabase($_){return$_=="domain";}function
query($F,$co=false){$C=['SelectExpression'=>$F,'ConsistentRead'=>'true'];if($this->next)$C['NextToken']=$this->next;$H=sdb_request_all('Select','Item',$C,$this->timeout);$this->timeout=0;if($H===false)return
false;if(preg_match('~^\s*SELECT\s+COUNT\(~i',$F)){$Om=0;foreach($H
as$Hg)$Om+=$Hg->Attribute->Value;$H=[(object)['Attribute'=>[(object)['Name'=>'Count','Value'=>$Om,]]]];}return
new
SimpleDbResult($H);}function
quote($P){return"'".str_replace("'","''",$P)."'";}}class
SimpleDbResult
extends
Result{private$rows;private$offset=0;function
__construct(array$H){$this->rows=[];foreach($H
as$Hg){$J=[];if($Hg->Name!='')$J['itemName()']=(string)$Hg->Name;foreach($Hg->Attribute
as$Wa){$_=$this->processValue($Wa->Name);$X=$this->processValue($Wa->Value);if(isset($J[$_])){$J[$_]=(array)$J[$_];$J[$_][]=$X;}else$J[$_]=$X;}$this->rows[]=$J;foreach($J
as$u=>$W){if(!isset($this->rows[0][$u]))$this->rows[0][$u]=null;}}parent::__construct(count($this->rows));}private
function
processValue($Pd){return(is_object($Pd)&&$Pd['encoding']=='base64'?base64_decode($Pd):(string)$Pd);}function
fetchAssoc(){$J=current($this->rows);if(!$J)return$J;$f=[];foreach($this->rows[0]as$u=>$W)$f[$u]=$J[$u];next($this->rows);return$f;}function
fetchRow(){$f=$this->fetchAssoc();return$f?array_values($f):$f;}function
fetchField(){$Sg=array_keys($this->rows[0]);return(object)['name'=>$Sg[$this->offset++],'type'=>15,'charsetnr'=>0,];}}}class
SimpleDbDriver
extends
Driver{var$primary="itemName()";protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","IS NULL","IS NOT NULL",];$this->grouping=["count",];$this->insertFunctions=["json"];}private
function
chunkRequest($Vf,$xa,$C,$ke=[]){foreach(array_chunk($Vf,25)as$Nb){$Lj=$C;foreach($Nb
as$p=>$q){$Lj["Item.$p.ItemName"]=$q;foreach($ke
as$u=>$W)$Lj["Item.$p.$u"]=$W;}if(!sdb_request($xa,$Lj))return
false;}Connection::get()->setAffectedRows(count($Vf));return
true;}private
function
extractIds($Q,$Kk,$w){$I=[];if(preg_match_all("~itemName\(\) = (('[^']*+')+)~",$Kk,$z))$I=array_map('AdminNeo\idf_unescape',$z[1]);else{foreach(sdb_request_all('Select','Item',['SelectExpression'=>'SELECT itemName() FROM '.table($Q).$Kk.($w?" LIMIT 1":"")])as$Hg)$I[]=$Hg->Name;}return$I;}function
select($Q,array$L,array$Z,array$rf,array$pj=[],$w=1,$Hj=0,$yk=false){Connection::get()->next=$_GET["next"];$I=parent::select($Q,$L,$Z,$rf,$pj,$w,$Hj,$yk);Connection::get()->next=0;return$I;}function
delete($Q,$Kk,$w=0){return$this->chunkRequest($this->extractIds($Q,$Kk,$w),'BatchDeleteAttributes',['DomainName'=>$Q]);}function
update($Q,array$G,$Kk,$w=0,$Rl="\n"){$ed=[];$ng=[];$p=0;$Vf=$this->extractIds($Q,$Kk,$w);$q=idf_unescape($G["`itemName()`"]);unset($G["`itemName()`"]);foreach($G
as$u=>$W){$u=idf_unescape($u);if($W=="NULL"||($q!=""&&[$q]!=$Vf))$ed["Attribute.".count($ed).".Name"]=$u;if($W!="NULL"){foreach((array)$W
as$Mg=>$V){$ng["Attribute.$p.Name"]=$u;$ng["Attribute.$p.Value"]=(is_array($W)?$V:idf_unescape($V));if(!$Mg)$ng["Attribute.$p.Replace"]="true";$p++;}}}$C=['DomainName'=>$Q];return(!$ng||$this->chunkRequest(($q!=""?[$q]:$Vf),'BatchPutAttributes',$C,$ng))&&(!$ed||$this->chunkRequest($Vf,'BatchDeleteAttributes',$C,$ed));}function
insert($Q,array$G){$C=["DomainName"=>$Q];$p=0;foreach($G
as$_=>$X){if($X!="NULL"){$_=idf_unescape($_);if($_=="itemName()")$C["ItemName"]=idf_unescape($X);else{foreach((array)$X
as$W){$C["Attribute.$p.Name"]=$_;$C["Attribute.$p.Value"]=(is_array($X)?$W:idf_unescape($X));$p++;}}}}return
sdb_request('PutAttributes',$C);}function
insertUpdate($Q,array$Pk,array$E){foreach($Pk
as$G){if(!$this->update($Q,$G,"WHERE `itemName()` = ".q($G["`itemName()`"])))return
false;}return
true;}function
begin(){return
false;}function
commit(){return
false;}function
rollback(){return
false;}function
slowQuery($F,$Cn){$this->connection->timeout=$Cn;return$F;}}function
create_driver(Connection$e){return
SimpleDbDriver::create($e,Admin::get());}function
is_server_host_valid($Of){return
strpos(rtrim($Of,'/'),'/')===false;}function
connect($E=false,&$j=null){$e=$E?SimpleDbConnection::create():SimpleDbConnection::createSecondary();list($M,,$D)=Admin::get()->getCredentials();if(!$e->openPasswordless($M,"",$D)){$j=$e->getError();return
null;}return$e;}function
support($ye){return
preg_match('~sql~',$ye);}function
logged_user(){$Hc=Admin::get()->getCredentials();return$Hc[1];}function
get_databases($Se){return["domain"];}function
collations(){return[];}function
db_collation($h,$Wb){}function
tables_list(){$I=[];foreach(sdb_request_all('ListDomains','DomainName')as$Q)$I[(string)$Q]='table';if(Connection::get()->getError()&&defined("AdminNeo\PAGE_HEADER"))echo"<p class='error'>".error()."\n";return$I;}function
table_status($_="",$xe=false){$I=[];foreach(($_!=""?[$_=>true]:tables_list())as$Q=>$T){$J=["Name"=>$Q,"Auto_increment"=>""];if(!$xe){$ci=sdb_request('DomainMetadata',['DomainName'=>$Q]);if($ci){foreach(["Rows"=>"ItemCount","Data_length"=>"ItemNamesSizeBytes","Index_length"=>"AttributeValuesSizeBytes","Data_free"=>"AttributeNamesSizeBytes",]as$u=>$W)$J[$u]=(string)$ci->$W;}}$I[$Q]=$J;}return$I;}function
is_view(array$R){return
false;}function
explain(Connection$e,$F){return
false;}function
error(){return
h(Connection::get()->getError());}function
information_schema($h){return
false;}function
indexes($Q,$e=null){return[["type"=>"PRIMARY","columns"=>["itemName()"]],];}function
fields($Q){return
fields_from_edit();}function
foreign_keys($Q){return[];}function
backward_keys($Q){return[];}function
table($r){return
idf_escape($r);}function
idf_escape($r){return"`".str_replace("`","``",$r)."`";}function
limit($F,$Z,$w,$A=0,$Rl=" "){return" $F$Z".($w?$Rl."LIMIT $w":"");}function
unconvert_field(array$k,$I){return$I;}function
fk_support($R){}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Ue,$gc,$Wd,$Vb,$bb,$Vj){return$Q==""&&sdb_request('CreateDomain',['DomainName'=>$_]);}function
drop_tables($S){foreach($S
as$Q){if(!sdb_request('DeleteDomain',['DomainName'=>$Q]))return
false;}return
true;}function
count_tables($g){foreach($g
as$h)return[$h=>count(tables_list())];}function
found_rows(array$R,array$Z){return!$Z?(int)$R["Rows"]:null;}function
last_id($H){return
0;}function
sdb_request($xa,$C=[],$e=null){if(!$e)$e=Connection::get();list($Nf,$C['AWSAccessKeyId'],$Il)=Admin::get()->getCredentials();$C['Action']=$xa;$C['Timestamp']=gmdate('Y-m-d\TH:i:s+00:00');$C['Version']='2009-04-15';$C['SignatureVersion']=2;$C['SignatureMethod']='HmacSHA1';ksort($C);$F='';foreach($C
as$u=>$W)$F
.='&'.rawurlencode($u).'='.rawurlencode($W);$F=str_replace('%7E','~',substr($F,1));$F
.="&Signature=".urlencode(base64_encode(hash_hmac('sha1',"POST\n".preg_replace('~^https?://~','',$Nf)."\n/\n$F",$Il,true)));list($m)=get_url($e->getServiceUrl(),stream_context_create(['http'=>['method'=>'POST','content'=>$F,'ignore_errors'=>1,'follow_location'=>0,'max_redirects'=>0,]]));if(!$m){$e->setError(error_get_last()['message']);return
false;}libxml_use_internal_errors(true);libxml_disable_entity_loader();$ap=simplexml_load_string($m);if(!$ap){$j=libxml_get_last_error();$e->setError($j->message);return
false;}if($ap->Errors){$j=$ap->Errors->Error;$e->setError("$j->Message ($j->Code)");return
false;}$e->setError('');$pn=$xa."Result";return$ap->$pn?:true;}function
sdb_request_all($xa,$pn,$C=[],$Cn=0){$I=[];$Dm=($Cn?microtime(true):0);$w=(preg_match('~LIMIT\s+(\d+)\s*$~i',$C['SelectExpression'],$y)?$y[1]:0);do{$ap=sdb_request($xa,$C);if(!$ap)break;foreach($ap->$pn
as$Pd)$I[]=$Pd;if($w&&count($I)>=$w){$_GET["next"]=$ap->NextToken;break;}if($Cn&&microtime(true)-$Dm>$Cn)return
false;$C['NextToken']=$ap->NextToken;if($w)$C['SelectExpression']=preg_replace('~\d+\s*$~',$w-count($I),$C['SelectExpression']);}while($ap->NextToken);return$I;}}$kk="adminneo-plugins";if(is_dir($kk)){foreach(glob("$kk/*.php")as$n)include_once$n;}function
get_translations($bh){switch($bh){case'_template':$mc='(JLxQ[z]D#OyX8d-<2l??
I`U2iVSdJ3n2Y(Qq;+bN()xcTb3Y$m
s8^yy7s+U0M-Ib1&w8Xrnet9uZIq7>nEl{&eda-kdz.?9Uw/mjn<y&7pt9nud!cxq`afux61h-Klf^w^z)MrtSqcxs=Ls47TSEafV0v=S;xRz)i@v[Xgm
7|k33RGy6T%f"yin<;G5/1!X3~"l%),|&N-."?k@#GKi+?NC)"prt9Hwd+T:tH1AiKX.-Kf.-L=
tX98"|N:Nf480&Ns"$6_Sm*[P^Rp6=x
A`aqA>iUr@#8")2R3U#,"(HTD("X"#CM0TUuOKP,1;"jd.Rr0(%[jhjR1R63k#5@.4>b$PlSQy:JQ(&<PDfzI}P,G=$0mpK`!)kTA2:GHWj:7%Yy"_-&jZ,""
=4OMJd[:hR/<y+-}/>Vb(Z"nd1V?3[.Y04uHWzwX"o74RUVLfm)b^s[&VLBgM-O&yRVW@;+ZE=QAi*FMp.8qD:wG+fGf@c2^0-"poE-E8g1[3iP(k?EG9U6P7kp8kEiJ=hr
O<E32FC>ebZQ5mIx$.#p)(NI:rc]P5=n1jssyQJO`v(ZNSf50n
{_R7{1HbxQ
xpl<+,vmetmwTPjS/1)3TAC]]jLFtt1~p|BN._D`]oA?
udZs1XV.FDRDR.X&?[MZh#^@b+#D)yCf8I&
7Rq&e.x>yd@
uUKt^;&1[Y{7IT*Qhpqk@HVb+?;]DUsh)E)TM9s.,4K/Rg,<=`$e5`Nr[EVZT&#,9RO:4rwn)g*viuc5HE3G7mfrqWI4hyfEnh8h0mW!(H$6Kb#-.7PGlw@5Vbev(Ko:[*s]Q<k$5.*d]p4S0$G@y<H3Ba_)!y7G:W

^B>-5rI`o=+
ykvO]VxWgyYR52IrBP0Q
sLH-)-w:-p!E:d)Gt+sXk3@GDZbkZu##WZ]6a5;Pna]H75XN8>-j8a.GTg7mZ9RXSt:S$SEPAvRLR,s!un4b.>)c>U&P4$q.UQ>_ho08qZV4>al"tz`>eO@LV`ylGFTkQH9s_KB-k0+r+2DKDy+zBL
5kWi10UF;q+gHUvOd2g/(bk3J4m4[0j
6)2%YOp-zVlfsl`bT
61K)eFy2odC6`yAf:yO,gbIVW3jcREW@3hjpRKaI]Do]}5.+YT/()fxdfP*Y~=~j=8?O(=|>h%pBF<LE%e[/FqkW,pf<)#pn,&,1T)QO%8SkRY=ibLeh3C~[#E30&_e&6@rEC5S_e<8A&E.nShGa&K8LPc$,?yG';break;case'ar':$mc=')c0;:bpD9,|?Yd8.2882-L9dN6_C~MF$@I;W~R/!U:N0<1?-tP-fr5y)[=yRU7a)Z@VF<azAt
l=mc/AEyP`Fx9K+V{6&C=f6NWvDbY@&q]]0iU_sFtx>
qad-_!JG7if
hM-X=/VxzdG;j8*p{4mJ(
]cPTi`q]hSk-BU!qHRT8
E(+GS9%:rPY.v-gw6g)]mW4UU7UOwA[t`9-t`Upl$&fV/MV^uV=c?0FqS7)ZFl,2R{KPj`>WsZ@rEsTA$X/G;P(n?lm2`XYV
<R+=fd4qyhU6+&+l])HnfycL_M^c$mrm5CcZ#^R-Q@gl[,"C5,fSi678QMdR*t%nMPPyMtWI%7@!:jrrzm~l&vZyDxJhzB=y-tU:MI5L^4w7vf
kwnU,Ga%nbmNtCxyABp;uzAVnRyr<2$hVfC?V/Gz%|GQa(0.E9O1i+F:R/h{T[8rGQGr%{gNdm,_^/l.RYbOKu@EUIS_p&^[nI$!LhHQqPPt1mhb_J</_i!v+n_%DDysr2E)i]LrRJLWO:.5Gd=MWci<J(V_8rJda9[<%1hQ-6:o4qrTivYW.MoJWG*;fvXRg&%g#L*B(^&oPI*diu`K@08`Q;P.hH)xYjw1-Dv%B2#)a9hh%@oP]0:BcgVI-,]=?PL~T)/8)4i8#pBZa_B4BxMLNRgmF##5-WfUp~,VaMKXMEuy:E@D93#D&cAVeMi7PaH3.&ZdU!#Nm|t>w<tWSg^/,3liVaGT,IbR779/lZ_pTKlXuCO|@NsP@5i]2eGjPtlVRNfg_|wd6$cr:<1?qD%}YpEl<t
}Mq[zwvB
/,V9u!E8s5t:d89cAtgBJ#fXJl/--pXXTn.MYB!J6bk$e-id;lo54:fd2"D]$DKOQ/x#2=)$,Ie4&@J28|eB931D/O=a;sgj&N4r[P]93!(0SfExma#)2h,g:us6<;4c8Lp0a4U/I+/o!AALMADj4YSV:c,xpejoG72tF.v"H}+7#CLw2d>&3f2[18!xg#9Lri+t<{2ZKn93L:Y`&g8=f0b6^WPyQCkcB&#p6/k:D%]4P]1tZ$C%qBT`Kg6hIb9h&O50*FQ(]e
uPG8EjJi{RL3gkZ[h#mKNtv;A
+pxdog@eITh=DG#+N/.Q$qtLm8OrYZ^D!_*=n,Uw$Udfek@6yORTqXU=8+IEdgg+?Hpp~AuPrT.Xtq{$(l=j7.1Iu`}D{9W
qi7*jg(
yV_dr)/XD+<esSq.LRCvM]%v0$6)N
f)JqGxvV:m
W.KK$_?BFr.UE{g4Qu:6B/[BM{hcwe93MTol/"pn>?Z0]/g.8Zlo-|#?DLs0e<hAgr>@3}SUD@.;DT?UEfl??A%ds=,U1GB-hZYh(f5Sxbf"&#V>wJqE>79.1cYP$b/Fc^R|3)c{Aghq3ZhRAbGM*ho:X
/$2id`OROF;T-p=4y>]6t$l{TXs$a@Tkd_7G+x;5b[5;7Ak9mAQkn3;.&obtB@j*OxBgIuac:/QthQkCHT-4O$T<Fx7h*tA*s}&}a`M@:wJgvq8"MBQvfal(eJJvDP#o"ia&qc[-/1yv+exQMIj06pxBG4:d]:ngk2H@T@Z9Eb-}^R057G.JH(MsV/:<mi6vyIEjo+fbIMeMLzZE+YAm_0ovt`_7(Y/,D*2M-PW;Gj2M_p]]GcXX2O
bu%r_sF/+b.u;qA)0KsKf0w+
r[[8!$%cTO=b)`_Z4YC}h)&mq0B?2c>Bj_Zb(%
5k5p|!&Oa=cwg,4qrptRYO"RK69_gA}NR)fRnLaHT<REKjA-iv]HT-801D8Nb:{PFRq";OO!gCM!tU8q}<;MgZj[OMUEwCH&wX|2r-0ftfU`q2*fo/t/?(dM*rge-Es`*.)EMGOy5s5Uaef*%M*`(]OS59:BRm};1@sSy7]
P>Kl(c]@E-TYao/Wq3C2tvp[-h&g"3X]J+jC?k;n&@VQg/`F)EW.~O/FxMx0PH;/zt2/{$[U
yVrV8uxEk$`X@iesJj^b;LaCh8__*c
/YY`A@pol^ZA}*V_(KdibF):r=Se^YIt0+{gauYO%-@o#T.){BzVE/n(7"$NrMh).5(HToteA/
4`K?&Xk<d|p%"iWs>WN]O1Wa($NQ!~?:HOC[RUB)p^X
Y,x[dWEepC-=k
Mw[w*>,`$*2CbpHDx
QZ#;7`2:a>/c9
enjzAB1;i+^jibNf[H?L0O++?3]4r=1,ElWvVr8k:2g6lq+]O8hYIa%P,WPt@d!O<O0_7CNo1W^?[N1qS)YP*h%F7Er[3*
D={kH;dh67L,`FRi4B~sza@+jH
E57=JJoHw1f`BM6AsBEep^=5J=DaZa:qO6?M7gO>36`LUn7.?0A.eSukcJ]ltcOA&WehF:$00(sx9"-BcSDp<Q%+hAF9&A&14N$9qI[@<J6odQs4L,.u]Ut1A4
+<o?-o$,~g]s,[c.q_1s}.m^<J)+ew-CuQW?q%{QDK.rNGH9-Ecp+a6wJ,~["Z)J`Y4UKqMK;0HMa$Io
ccc?`W(N[;/^D.!^6(/Ul5kk/mA$g5Z
q{*IHSyjREoLH[bmflQN%a2"dfl$d7!R`.j^THx*P{8tx46F/;op_@%Mi.Tfr#I0%~cESu2%6@6swx&):Fafq->8ZG=9S@5SoM_v:sC(hjQx;95/lD8VY]HMl-3:T00zK22[H#^Qs|5|/a_gCmF)5Suk(
s<XGg253nD7[`$;mGNhFg<7@XWm.iJ^RlR:DDKSRxV0`+=o-MJafv6gL*j]^LCN{3lUp]AZx^XGal,ns8,Jmf2EBE.(!YMc!Itam(A0V_rA$gre^xgFR8dBHssAY!8$M&mlUkMo.kFss-.A?UqHf*Temt6^ls__*664r[I!XwD+y(qn
`s"NhSdx3EDsL8R

v(#]U3A

"z$zf2<_U4aximW>NX63Hq%IpjQ@
/m:vKh(c`>%O$b-KsYUNXDIi!ipoWY+G^Z6]0PJat
a#Jn+76<lRJ,N[G5Y1sHg<}3aj&Kz_Bw4U5$pc"^.23`rvNkA-NIOmhvUbb`+=
@cL%vU+Ou/*~c55"*P3+r+k!v9$JVp<Zp/".SH>Zrp5&lK&9^$IANU3F;zipVrU,m%g:C767(aQ_Ty[@ye%yZz^porCbdM#A[+bq?_Z%,$]hLF_Bm0WS$9=m5>a]^wuC3TyrWA]cc30[A-3cBCyGD}52Cvs{o+1du$5+ios*c_4LQPb
5Ep
YD3cdMg5Q1w.BCb)LnHUC&A4e:Ko-K.QEvLom?Ei_LJ<J8Z;P@X|vENE
fF^V_/1g|k|S$Q=-ZJttxFy>^3VUpR0-s+|Wk[$JaN1O".~-_#G6HaYefhY0HTLgRTEV%A
B7!|:#3+ek4N??b3IzWbPb2&:O$/na_ape:b_[Zr1o#gvf)lPn({A6vH/&]+W,`~/=<#KB/v
cPcPwIyKJNai-*}p(hkcY_MFkU>Fz]"15c;X~Ck/J@B_w,jhhI=n~KK/vS0R!-ioW<Za0vnIxpzO1MCgDrdAfdZu`%E5UX+X>@MOtaoi&2>P%m;q83b?/sF66wZfgk-%iUCt(^V"b!wUnx9UU;Xa_FP+i-}a1TQm3,rZOwGqeWe!#?g:?5?A8@F
!1a)r.;ZxmI1fV+g:<crH/y<4=ahr4_s#dRbDetqHwDD7d.oT2huToavk^Udu^$+c["[h^<fY
>4lll:wG!?.a]CGB).ycwq)1Y^P@k=~`O)rKR%l%6QI7wT;@0b&kpBIs-.S,r)>VXDy__Y@`zgkveGV/T`mWp
[rgLN3c8E]~fs*u2pY;_-1$:S$_;=&>@W0$`}C{xOCm^g,o4r)k
D3Z5+h|Ig4+<KS}S`3GEH4Pp:3TX>5D1;%q3ARU.S
~JslAI[Eer(P-gHDid`9kGJOM(+idm@Q1
nI6I$!yF]=(-3lHjLg~7A<uEB[GvT;?bqjU`ch=*%73[.Tc=-)rD2oU](Vutv>Qme*oc?QiJ#V,nF;*[6[iqH>hELQ~E-nqP0%@m]X!sJ:WR3Mw9hp_Z5@
JX=*$3nS`^S1E<f5]lS/<Nav&q(vc99HCX2)MUpJ_1h]MBs/XRf$6#9iDTb5Ui!:p(Kk=->y",4-,=?<?mP5mlh#ku,*n1PFb5+:wi^;<9i2+bboq/mq[{*"&cGEB*76AM[s+.B<#0owg-o|>]1Q9I#Ipeh1IW^6+sVb*H176&0xz"GDxQ>^cO7Nr/w,3c9*7`Rn,G.P<3=Yg:&1SJ
|`,Ze9fN7k=Os:qFP":Y[FY:vBtkUVMK42[GzU`.lx6stxk<CHR_BMi2fM@2x;HkSKyx]DA)-;grOLr61">q:o5K7_jJi#"vZuy.B0XhsVQ0fK*&:9f<@mGRkM0m}sp2wibt=`&pdAUxMVMsrJ_qGK7EFB-4obO"<:F#Z^}cUA;ya_$Ca-Pa7r3XbUXbXNON0"
w}G"^;h"RE
9EvHX<Xh7HHy`E3$UFq68_L+Qc=uLeralq-+**I.{S"GQ]Z/~,~Ie6QI4eq/>MF</CZB[bE,@)kuw5@/1qhtW3gAz5LQnsrTY)@!9!X@z;iAkq7GpwY==wZ4DR*Mnk^y(ps[A(XFZe"TRUOx(9lX
Nri*nD93^Ns8MaTObhBNo<a>Iuv]MBN%$"K]RSj#<0
2K].S@M9yA&<N=iF2qwM>Y)8_?;yGi/Zmc/6K#`B9Pb1"5P[Zi9N
h.@l1Urae7mjrp+d6wpb9WkYo_Uqhq,qxs8a3g=KE+%*Vz=7;LOC<LnJPTXOr}shz&#E';break;case'bg':$mc='&evF;bpD9,|?Yd<!dO[i/KZPhAB-d&9o1"bsz61$
-lS%AEP|*w"bC5<)/<u?3*SX$Yf"xm,=MN.Xp?`UbQs<lOjXb9kDbVv7n8_=N)q,i4kXb7@&L/WXiJkzsbWLA:nGIM$YCt0VM.=YN

Iy-Fwx@,JP9
^0QJDog?Y
er1A:mN>zGYcZW{0FBEg^n2[>F-?!F#4ZK2MNpm"c*
xy#.A*kD>7qYNq63*x**1Iq&V)+~<$9!YL"_>gm$]W*FIfNk4<kV>#k5RA
[)Wu]#H)*?lW+(_s~GPxtE?sY;!qCv}N"ofnEb8LNl9A}2PY9KK+GNIt^;oftLDhCUJA>6+Rj+tsu]pJ7x"GPsBV4k|z(srWH:Clwt3w>z(Qyl1tKbY^3LWxbqgw+m^L^t7bPt=&TyKILKPngESn=BFW<sG8>y^mM
DZdyRh+h30P6jZ^RJ4"c:7lcJCp8]>%[Tk[=vEKU/k9VM?EEq4W
I_P?[%X]99x.hj`ArYET.;-F[$#YhD,3Q=@"OjmH%t^g>
;.j)h"UtHX.5x
53ofxCj#VLB4,Y9p`oxkN8@9A<8SJ`N%B8AkDZohL
-;Xb%QZpvMTtynpb^>+A1b60(c]X
A9:P
O-3QM.@8+E;;"45y=;-i&;twz(xH)X|x4mA9
O}HXdRw>8IIEE[u+730qcq.!McS"q40A>}GGEn^
h*S)01&AU2JAhO1Pq%EM-3seOnGwb&EdNBjt<hk:.-S_8tnMLGe=!|a-3;Px/~HYW3.ay?bVye%<&RP|=i(bU1V`gbBsG%`,ux1nfD.#=qQ2_LD=hxp<j]!{!!hiigC6
b`sLHkc"XeqO|=%
s0%H1cDa.nUA~6SW^yBLMz&3k+#ydm"x`Invo.tBXY]$.S{5ogdBz!DXjg7MW_l_aVuJ"6L7gKUgV`)YZ
Dk?9eAQpC+4@hUisM(RdGUp:c8
,e%+wq6&x!h)Zvl]8h6<%Fx26K#IdAxdxD6:EH<hE@83e*={Z4v_3Y!KxJ3:M=lx?hP+fYO8Uv3=:yV=qiV.4IfsI+u~cuU.nk4Rx^)3o6"Hmw5NpI;x.PI0
OO7kbr)hw!y-{McqF4zT*:{GNJlcWl"W|Nc^8r3kdmgvBI$=kTw?Xi6##2}xrqt"7]p(#P$k#)q>CIa3Fu0
yq=4-1:&Vqbb|Saq~4O=^AKhS
4;e)K8IOtFbmC_vq$"x!M>ia/[O/q/wy0xCFQ8Cr[t!)1>Lff1xVG=wQ0;hucHH@ijV(oF!rk.:/uEc((qu^jLD_Uj#5th5nmW}Ld,l_+L@jK*X>S>Xpj/iqeXSAbDpHaA1@t.X9TWx0JiG/^LpYyk/;0Uo:w:7lw%B)b!4W@?QC88TeWLTrl-4Q%1_FH2(Ve]dhL]Mt/CKhu>BDt9Z<%/g
Xg":`[[LXNq*0"lukHt1tWfjjdIJu`9;=):,/<(_7iMSj9:ThPQ
u,tAjYD$!=e@#gnP|p3("py1k/b-y+l4?(~S`;.16c(QXqp/
Kld4r1_}&[J*=eUrm2QRWMTgk!j@X"JTXMdxjq8VGk=%8{NP5630E:1TIZb-N
IwQG3yqu
v5=:7_}:<Sv%Pn|>:W^FHGUBIXg*h9z1SStCs2!br$&i>>:X[,xALQtD&Fd-=j)8|f675D0&i>ZWp>}Om.s"t.U(1gA:<EVs`ToW0"G][`D7LnpNLJW(<hb38oC]zi+^N=]=S&#MlPR;BFKs1>#+l=b,>iB^Xaxd,.D%yX4pL$yTq;%OP`]pJ1NJI6,*;Vf+EZ0`lTnv=-4Et43BYp2tdw?/3<
[;96hIu982lA<q"k3&w6h38659E|N$pO
7g)t9p[v@v9"DmF;&PRc{M_g<0Z
A?>O6H@yxj}X^oJebbb;V-yNmL2F.(7/-tma0V;.XBE*O.eqL;p8M[~,O9U#l.}.{j5:#Z`jFX-DYt,#WPVg^x|T`Y;2gS_*n<---sNh8J~gxwK!$++PApo=.#gO&Vw+lQ?BK#l9z:Jic&[(o$"8h0#"Tt"Dt-f1zwt<P1bTX3>77t9B+e1g@&?jma:?an.v,]cM]fxb@<RFR]|/Mx.;AgG"
N*6PwP.JdYPONZ!q6ct%3YE6f0aW&ug2!5U^V".MGF.[-V/KHu26N{WDG;0c.aaXZhPti-*P
ck^]HB?`SDsyrn>Z3Ltf~yi_YV97sC)p4>a77x4a/eCTUDi/~A7o>=#kWo@c7c^:RuE*c4q+0?er$5p]RWYTn6:!G$Nf$abnwI
&VDBPy_>n#sSD})IkKK0XZ=$XNF#2#iUUEqB$L97emm4W+5.$:k;$^0K1(/VwG`0x.(=D.`<mu;Ft_XXS+hzRD;}/L6>gMA_w2rTB{Z+[^v}l7u1pnbEH}?kpOol#o<C&-ioYp>`gTt|
83{;:%MPDGh.(uxLeA1Dp%<:z(RoA8HV[Ti>@pzHvCu0=Ab3bv/R6d[Mj64@EAaE+p?4!5EB^u.x26y+/:j<!$!7:RxtO*T
_`;lV!|CJ=D.0g_-~yfuXB.wB_B%[Vvj=$LNAd.C:v^HU&=(strgm_3KB%wpAvB^PuKL>,x.Z@|Z_x:&>MOA.aS:bx.mL<I2rCTJ>(Wp
=DWM,Uj^P}qVpdV:o5qPD*xNu>Q5LtrF9b5AKRy<8GJP<xEI9+@TV>yJWbjNC<"uI^
Vx
3N8@AamO@(TVj$@,h@L(SGJSNrjx!RjNmaG2iV6P`u+XVf,1m5")-$Q[x*lHZAE;M6eC+$Ot^MWL]0dIvWa=qxh_Ww<RYih(W
wxEG!nM5=(HaPz=Zm,Z~MK>Jwoxz:_>6;<;r/;V(%m/vh([m`i:?3q^0:Okw_|^4VK6/[09mK[r
!p"Wk1=7h$j=<mqCdS+0(R"i+^Gje`Y.for>,+
DcB&LX{-^!^R3((n
vS,]C4F>=yv;Bf9vp_m:QuO.kZ?e/}%KO"Q/fNxNo1>9]QyjKc.E
R,x$#o&IpV<a^_W#)]V-u*>C8Ib"dqHHeL+*[sk@VdL2#.BDVI
Ugk2?hm70]VEkX#yMzQ4QvFSe+3}56_Ibgu.6l-oS5t9RLubXdUcGbO+
^5[)$<aWxer"FU/Hw"*@e.Z74U
,_DcZ.GFxXM#;&NgBFH-*t`mGLq)0I4-G!rTWQJTXQcfo,_@*-IHguC72|.w[Ph/jRN>a0Vf&=:pbzE>MZG:]M#U`-[^p4&P9Lr"MV<6G1ny4sI78|p29}Zb9D]G3}!(EBUvyp"G3u3g_Y#-/D:[]+lWQrdL_.6&#AqF<;:ze%xmf1b{4EfA8r6Z2~*JCe6Bv%P1&|3kHD?MAv"/o<"4jm?BZ?uXmc.f3j2s$=l<x=$
u
e|:TN-Y4&8VSe<h3[fZSD#%/izLj<@QgaK68qpWEJ8.uSjQ[^bhqWF0XA,wd%91-8H^hbN5`0"]%pcOD$RR4iTJe78A1ixg
qi&?0SUqX!C*x!5
mliW@43^.F7&_9O{5R>^ET7Wj-fHlNhA*=8+GuY_D0u"jx1AKbZjr/"YYPl$DtFNVXq5FBW[Mt3@$M:s#TLcn%(m+GL?Up?[GZO7<=hdkRpZrkV_Exh3";R_DNVq)@#"2l)sxF`~J~*nsYAoP=9M)iB
vI@RHT=8q@R!B`L[<h1UP3HLNfYrxJeFOFHwOp$Qi#RD#Hqzd(&U+oV8V@?;2V5x3{9?ecM%l9Yz3|%>5v%Qc|YqNZmt@zjs<UKjDt+}E3wmlYr3o^N2?
N|,g(V-&M1Y-suOR1Wdy3}jMSh/2.*.^4*>FZ@-im&HH^j?Pky.*ee,&rZX=u^&7Dcaln"=Ev@ffZCD7mlg%"Nt(LLirXC-0bkqEvio2@1^&?N8ectxHN6*GuGE=@,:I+s@%p])p.hOa<"S0op@8Z~aZ>Z0Zo1V-0Am%?f.-%reMpu<i9wez*CLvF3Uc
VupM`O~

i<fOtb?Leo>8psq|q<)Z(w&iI+M]HNQuA~B:Y]
/q4/~ku1ikP>h*8s^c#oylaow<>)Im9fC#IiN@~/
YSc=G3SGy~di^_dr+RtOmMX%fLJ8NRNoPHEX=^WTnu6wR7cx
d&9Te)/`l%/^`YZE,@MN;DUpxA<lLPQ8TvrEbszh`r{J(;^*ke<[;(;(]7|4DMX6sXfQ+16+zWZC+bUT7N9xi+G0PU)U8b]IQe[P7l&kBCbOO.q:I%c)(t}BHKSlBR{0eTna+j,;l0~,P5UoV&}Kp2$<xmL+tHo3beQN_J|uxhL_c2$.$
?;4XMx;bsiyDGbzfpq9E>d-ZaN>T
$+:;P#3D)/fv
8
0rt_#kfxM0gEDfw%yrri2qBnqZM>)`TVMxBU[OH8hC8j)jVg^H=XL?~?If+sZ`S=QgjOqjoRnR563$iV<@Md@`QSOt3=j&Ut~K|=cJM=Cy^<^F3sChaR,J[QJAiz(_E^x:^EO!}lPN0g5V,Um"=sLV@kT>LqZ!7p#2=d>4t3v4_gIl%A%`zYr)lpCDxRgWFpg&v8ye^(Z4@?ium*d0tl#$Ng,=p.1=*<$qLl@@nUPi]S8!WnihaOV[C"gbJ&u>qKK?bA~6yi)^mjmhfdA0ioA_XC}TJdAL!KtEX*ejgy*TPfv4DRu3~svB<
q87J|O2-<!`teVgNIFe_W0.PkLQs84@#dG/Dvub/2`NLX.=VpRV`PN*Z@BvXK3`;yR,:-V,L~s@e:00Z5S
K.jD#lcTs1z%mOy4,`1f/_R$6k^:GT6To6h
%4+ClzhCKO-cmR[wSSu*LAtsL=K|,`wuBF,LUoNN9>#(B9E0fZZLTcrwaPW^]PMJK`ZCi1BJQ,*)<X(#%KJzj)E>nUMF,ZL^9aLl4fvB$`M4o7vEKPaAKMbp%dy{H{f=Nl*=*Vb&BN4[Krk<V@/ar4wz1nHyvDMLN,eCjC;FPOKXyp`FS#q(OV<%Z&FXk~bLoPVRK(T35%9Pbv4|dSq*M=n`*/QU&MJPFr])wHR76jAH^;I3NhT.!0lW$%qiy`X$ST[LGINM2n#~ocy]@cRu<i&;P8d0L[aQxA%q_X^!
Q^=L1^|h$xEx$T^[jCi&I)~M(6^g{La0fh`3~P,R=ub2$j91),0n8L:)/-8_}%.Yk&mKb0RD;Y-w;1zj71I4djFmB9![v]93GbxYk[7?tH+)Pt}*:i$T,0ZJ)SY9j]AC.jm]ZC;n^oTul6Tl;O"Y%XXmG3fVMI"$f&KwDo,!WE>kiJN
dNOh~fT6flot>R!f9L]P
';break;case'bn':$mc='*hWVkaMDG)E&/"HeSDa]@nA.-$@:{O)xp9XjL=GdX&@oi4qT<#l84#i&O-}!sO?(~2z^94
G~la?^H
,5#EB<c{>l<$cDE/vw*^fogi/-q]^+k[c@
o3S?QIT]H;z@=R.aB%CA@rNBSjRcH3&mh:bvKP;aVe#&:q25L_icHD@aa4!ghvEMF79BaP4`oQbhwUAN(^b,}UTJI9_Z(2B(-SEL4+y2X#FaXR>t86n$f#{aNECbS!M7FD#%zN$2-*/BZ$t<O7_!3a@d)RS5-duP/YrC%[PSIdt:Q%UNdO2y*!rd(6QZ$x2)oM9$p#RDSJ.o0qDsbz%jpa=y?bEayBzM$oG%`C^Ypn6utVWANA*22yBKBB}a.iIV3DuMqo(yB`IJGB-?|q`pDH6lKhanEJXK;x
M.l7q]kxLN!.LVrp1dz&$fAZs/v[v>.@h9g`2s?_(O$t
:SVpz&xSF`<s0+=Oj[~iX?h*&Ck^
KpN:Hn2FRFdgN4AmuDt~^tcaFkcIcv=|&Q3Aoko^9vId&iDKe;#9H*k"<NtpM*d.Ek*XAOl~MgIY`{=lH}
..Ugk^Yp9m|FA3kVJL}/m#pPm+#S`id"Z"OC=0uws[o_&w?n"DU3uoK,!I"d
QA(sku6d5CoRgSk%:^Oix|tcxWvh6^D}W)hGGi
yKC)5dC)0FPaw
|o+:vK>Nm)ZtL7<RMfh_k*)"U37puwLi|mWDOC7.65()nD&VN&T@C;`,54Z[8p~gSa/FmHVC5:S,2FTRMG"O*6~dJRm)
$ZsoST-UC%kUb/6PLV6h%zcTcSnM5ML@f?k?4Qj`dgXcj*q;_Nqo[HAw5|v*7ipzP]l0]IG8-z2=NkHdXR[U-,)Ald@IJ[*;>KH88NvU!FuH@@5bRKxd1
e@o0=r8)4
..juK>3P(K<13~)/FQ:8LTF[+]
:(I+d)?hAAeZ2E8`L`v@4cN2XKRh>7lNMCW>6s-:T/zbUpoUi
rL^f20h&@H/0H)i5Q
?[@&]81m&P6rh3XqKLk5wq4y0F8
CR}Pj*M/h/%cJwi.2["TqQl$Dxax$[f5GUyRGIyW5ycc$6S>YpH%F:7?{oiE`+:-E2rXR*$E9`Z/{ea^xTt-b6!,49oHmBwY
DfZHM5gr)Z$M/HUI6N,+!X[Uv_UmEDJgvmtzUD5848KMk`8Z,]J0EVg>O=ecsHWRgs%~d]p8aKf_Pj8QjFxF2`lcRy(jg1x|5>x{e9uA61oM=!a$xQbiqi[N<)OTqI9kY4n/C_wb8*:osp*m
#fQ[*0"KAGhF0bmwB3,=g;[T`RghW&vJ|``@w`W;_`2;}@jr)kq%%H}az#kYAyP_Ut~h5k-PVm[olJU.|%(xY3^mY`zGu.0^dZ_7i[=(lU=N}!WCr#;8&Sz:zV@g^p9gQD1EE,>(OEEc-gimQS`&W7(!O),
B<*ez<+>/c}ac+0xPbEi"BA(_Sjb:h]QoQu=(3=&j@pv7l*jc;coGdfo&tB.R4)>XKWo)"_^Uk
DX_+om]Pq+I@Dx8KcwW?D(*,Da-IW7$f_GLbi<6o.4.[.77[Jz2.up@UE^U![#*drUK<&c*A(MW4Jhn%^Y@92cRvx5[5#@J%J:qa]tUZVbhKe[go]K,`^oG7Yq,mPWp(+mHo6WTaUl^<(LcVe~E?]pa_s!7:-*i>TP:BoQaf_D,Vpi!]9u%^BLT]8:Kx+f?H[4tc2?Q>uXnlqrl
FGRVkW`qB9tJXH7cI-!]3aiLc<h@KghCZCGw%iM)x.,gH(;M_qlZAwN4U)hr"ZB;s/3&8ijAhL+LUXTGm.xI,q9S*qIU?{kxDU9KyCV-O/`|l1h:9"fvXdc9$&kPYo$,tZkQu[3+C3xCb)3AZ@;o0TqL,`>v[>ttQ3LyxdO"I,ys$:x=xJi
2`i4XPI$s4gTWf)"Mk08>,!5v(?:p?*id0VJ%_e+_|"x=RApNQ5P&neP[<5/C/eVqqjXDr){r1Qqe3[SDP37%2Hl.{:(%%uy<b+ec^%7QH"{hh#&u6fcNcwSRt9]*js0d4PD_^_5FfI@(2u4tHZ>!PbrWFuLRphq#hHFbK8-99PFFS-:EN*mEl={%;(|;y^|ZY-s2,+.?5YeZPi7I%1i;OmY%
n(k%C@[fI]fHQ}eM"-@q;_1}<>Y9B9R!EpIJ@*8~)mxNNCIO!FC_YlDi@2U4`.cGTDG<J+lPm-#SXjY<%S+4Mo&5M(2ayZushd)RZ"Sd@kZOm#m]1`=EL=AcYHdCdy;2wi;nRLjKmiO?nFtZ$AOc+5!#@@9.^.6b:A,Y!igU-^$93n;l>VWS9/_foRHU&M;2.4`SjEfN77(ej$a%6R!?;QJHa3TN-oY}:.tc>N+BSil]Is2YczU#Lh
mTx?oX%dT@XL7tJVu=yH.5+5H%Oq/b/<_tStfL}_X27-OODTi"^CeJDgK?ma&"<Y>
<prpU(E1UmO&tvCLnx-Etf^#~@rD#r+]_4t`j]QOAYIq(P;e_Aq(l+OjnN&djx4E#Z}Eopo
pbAJiXN"&TrWSsM4|2xG/lba[>xw.q&_j!9V
h*8h%dA"aQKQb{[M`=bSwku-Lg:V`#DzcM79
*,-hv*05]!=O,BK>FDd-dl@DN]{,RPmX~f?!z=oI.&6"">VPv?o"Gl.jTw+`%
cbGB_F/u`0*Qxg}`e3k!Mq%j^mwj}Xy.1<}eSj@3=9(Um5![[xLhNhu+sNF(-ZMJi,,bIXUl~`xxuXbW:q?ic@:@%Ijp1#V.`=|1-y9M0UL:|#sl=,9JGc*Dy0pc
H$R2hnJ!635xdd!U_Q,jlK="q"j7b`Mi7j7J>oooa;?:2_f&SvbIuH-6FbiBgTYN2nHQs[C[K;"%l_G0THn`JEvU<W/=@1WMuzE_p,X9):Fdxw_)ZXc2S(MsP|0Sy&2R4fCKku-+eMb-C4aJ)N2H;E`,IM]z"o=K0A-fmM?Ccu(0<~@(^5OFK;smF0xf#z0t?}hI"(uM;N@F+
d;74+47TwM)IhR_6;%*Je4P`BD/`yNE>YA3mO?C9Y*4AyE(YNQ=N1SL6a-8a]Lu50+l:f^hqf@mKUb1DgK[V=yLp;7-iQhsT@5)+<hUR8*&asB4zgRkyLL42Fb;
1OiyS$&3758!R~b+nm%vezuW?zSs
k
<N]S;Jf!]%^!0DZ^wM6A1D"^/O8/JlcY@?05&U:94%D9l9Bk!U}o@hRJ+k)Jj$5Z{Z
Q8:-L54lt,-m3tfQs9q=OvI:i,+*@/G{J"MXw&Y.FhDy/z8P?K2i+{@pHdc.JYo<tNj$HfP:@t@3.|GMElLNP8N6-kn=&AGW>@!{]LwNtX0Wt..bxT(WR=l)8oS"OaksW%Dk
.2:tRPt8z_i=)h!WS58H&4rk]Y[<Y7{G!T:Yg>-6ASIN?<OX!(3fNlJIk[uN}I2F;"m0ljgNyQeusxp4@TX0YRC"gqCj,t!_BTZ?.0EYk8b=w[z)7FG(WViFl$-V:cudf>E-8%iTdRc`rUgZS..Da9ax_,AepPVYajNQ96zUn7aB+2"F1!I)/7Zi[o~Qrq}]]"AZ7/<CQhM;[*2,7j~5hR(J"o7tGajn*
}lQ[wG&]*!}5X<0%G!|gwrFpt[uC`/Hgtyov,X,;hk.iWmP-x),lrlF-j&8_8v11jwN9myt$fp+J}1T8wIIX/
B@Kw{pfajt~re)lcHFA0W&&1<aaMNZC2_E4)FF^E@uZI~OP%h[Q<d07AxNbv.kIv:X=lFBA_1<aDn1cIl-)X*/LQg2mCnc1%k]yCMD@`PI/VT7:"@U0Z
th:!Myv^]:Yif32oQIj/!*cDDYekRvYM*A*!=^-=dd($qd&~c<[D201h1(QGGaZj426h-Ak{T5EB@<&Jl8Jr(|W(Q9e5w&&87-cfm%!W1iJa^A@$H|C_7v9GU*DEc+A%,#^8iuq@QcsE)(sKJrLDBcS|*ICQ;x<r(W.n."88##&N$!@hn`JJBC`F/4,6%j2_4|eUH@v6t[,7WiOYZ<>L$?@*YX9A^]={=N0@#wDDW{`p=,H`bT[3_YrgkvM
N|Fm3=BX>;]nA`
`jGL<DBd
h_rhM-GmOD,lliD#mR4V)`]/$;Q;*.d{+-<j

?>VjG8j@6/#]afi@]8fRByh;2<mun?Ntp%@~ZXRg4_FQu{1(.C0]%AC@@N/|;6gF_k<%WTy"GccmEGd*3lcCu5S[MPYY,)kBc^FBm;tNa0)(
7F:k//",+4@l$Qmo4ZUPxiT2h
$F7%C"QS?3=ul>Xs8&?bEtJX>HCxo(*?Bq0lE%Fh1SXt5%^n@eM")?3G
F:tLU&
)?.PF;Eo}N`@iGI*G@kkRb9+Sc)2CssF@Z:2?4jZqtx&_&rvd.w8tw7<KZ:#sZ-5GZ5+`MCdA]eL};)SWt5RmZc=g.7(uNGHyl]`L
a"5_Ygf`CxPkDc;R[;$DVwj^3q|Z,./HNr21Tl3HMnj?j1Gp1nbCRb6AQ4=r4qe2T./Sl.$OV9[h;hF4<&7R3V4Z}DACH#oG=t?_R=}`zk@;`#:_nQY6D_ybY1mSiN@s6:PFIB,-}>|IsY}WthM3ky{a1%f_d:9#mP`uKn76HN/idsv/4^1fGnO+JDHuO__ZOL=nS2h.Y-.ICw8jjm5Rj8c
Q4=ZHJVHWDiEZLF)OrL<Da7;$B4i`)BPyr>PC=k;PIO;z!-n0!&SrsUa/L5?^gY%_e-UynO4#QYHJ:87qe)uvH:>%o?L+F]g>QTSP19-Sk!*jl)l90/&Cr
6SFiL5f^j)G!)[4|3r,RFn][Q>$3y68p>[
[Cbm=Z*Ky99U2@1Y|:}_e4HNd`GuWU!7Qu/MrneMk@^rzap&e%/k7%MM?dmu+ugM2DK#k6:d36m
`a*G!q<r<4hcr(U_q,Lf%xuBwcwvMbX3jCa2mR?wgi$C0+%n04*&{:hAxAKGALiyZ^PSlo9c{.Bj6FiW3:+jy:2^uc%k5J)6~-bo=XTxZmh.XNi(i@t0O;642Y~QCD?rekj0=wb+lr$L+wDSDp9SvM`:5weCR8;=Na)Y*/M"Gt7,aFdFBu44-:D)bri<;DT=ULyl4aBMv9+BFyr[O8T8kyew65wX|Wu,C(P;]]"[;/Z?A:=j]g8dR,Jg*1bWrgjU-Lw,J$c;4=mX/BqV[w.kN^#<UG0M4#k:52RX{Wa<l/Wq4c7(;2Xf+a?4=wLJ"SSF1c26`4iVq",MpZTi|%epBN!3:A$kLCiT|g2ww<lOpHA)nUb6r-.,JB$j
T:FC6aONA7dZX|nRsbJIsm)a`PfO)y5TLf42N-%0)JRSr5;1Ey=vK_Y6KBiEkaH?8qs787@4T0d]uc^8,NE?Bs(XhDjDWSy["&A:X&&ZevNK!
yBu]I>D]TWypa?';break;case'bs':$mc='(Zu@aaM.72M0LN.%A:>l*]p%^ddS}Va;9QrT?>Vgqy:lTQln00mJpj:!=hf<wwqL&7u>EoXQMv)]DU/2S+~!"Vp3QF:-ng(iFZD6%!"c;A?qP8s_~
=RH!7!9r1mjp82Ff9iU2Shol!R%V$ha"F-v%<ar#Hn;
AU$%A9)yLvRvjnf`<r~UuQoEOpqLBA[ig8}=klTwX"K`Py%Vf+I>O)psL=OS9SOq]Flexl"Ww)A_m,Uar$#D2yN$7Q|twLmtglbMayen)w;_MBItKtHvv2n6mfNjgVVXCDRhoCCp`p|x@_(ex</l[uNb_mSx|,x*[?5WsFzEQ3<&"18Z2EQ!j-H1a_XC7h9REWB?g2}#n#4wY,MY/)8myDSLD^|(sc:E/-}iUt0G
yM"[q+l.2S)%nzyE@S.}Wq%F7#$VJ;o";!RTe)_NF{CzfkE1CC6PTI.~f;g?6w0uC3%Bt
"_)IQ`Vix(sqDK6Sqjy6a-_9/;eV6SU6(z.1W$v)u,5`bgY/WT%=G3u4aw_A4Ce8VPD%&Dd`h1XnS@KpVaYFF*(J_%Yc"Cpcigx4@_)l6Qr~+XG/0.NF;E`jP2joW[/=CF0(fcrbGdI6k0+fk,.{PN]$HG3,t%Zh[+0pO.?&J28P]pUgE9hycllqJBy[DVx:2!@`^,"3s3D`TFhqbzxm&+P!*TL/;1bd,M)hhoH[/Ah`c{Y8/AbHWGc@7-p[sy8&Qlq1(QOF4U>^CXkF0
B:>6U_M1d"Y=?wr_>/7dKpX;;IEBVNu+c2a-MO)~dikpaA2dqUt4*JHH8%6nqn]/c!b5adZ##_:
y#Ah3_M.t|,0/M=Hrd#*_+AD*ix}H(xVE<tV;O;"1JNU:xs:hr3?@cSyALFFUJ.53IKzlx
qUVPn!tge@9l(J{]zrj*A,uX$^KgBV{S515[T)<3Hs[pAj+S(q:-)cKd]p^7Q<6o7s+2IDfu$wX["<3iqGVWyP8,YE9TUp>[[K3>0i=s/KQh7k6Z[mMKT+1&?])VEVWaJLG=Uhg@kX~&i)"iVv4HmwOEO.#%cby1!+W7]<UM;#@/R9~8FZfU1VH6%"-D?:bQ2%kik4d+AuAUSThr:AOA5;~-|OBO+E&FkpoUQ+|#"(f,.y
qcC*E"*f7=wx!z_lPls2"v%BM3JaSAKU!lN,xw
["`NHV})SK0q^]
sk3(AbLtMp2+#5%)h";PYk4#"0
:fLbX_&<htv_.),"BGs4IWiNWso=q9;VR9^m*%|GwjUp.loMq=-EUC5t:+mA%%W_^9W5ljEfmy7lpU:w/m0Id#"Pjm5NpV/69)(gBu(r&N>bcu9-<!0g!gui|>,U;2;=,Kp`~]#h.$DGGq6wHX=v|&ua:TVAsFG$&5yDQdJ8No(%)u>:cM&lR^:-N=>o?B5T`p)7Ba3=D
2`40Zi#=MB*Koj&W~KK=&V!oE1Z^~/VZ-+.i/M%Kqega1K#[?"bcS2ic!a@e0&2ju`EdFRo!2g]dK.P!s!PyUOP6!]vj|PX/$AA/<.VPH-XxAXf!ia8mqnFZ`,HLci"m!W
Tj(l[wdN^4ENkC*461uRWX2*s5T&NVR6D_A*+RjipuMrULyWi;7k2[,/3SAK]UI}+ZyWB$RjR6m0EY?Yx7gy--<5^m4!?|8n
^#
DO&r#BU5b;71we8G1y#L#hf~^/.BHDV,=^n0W*t*WelUv;T%aPw~hpC[!
[`1+?)%fIV9ZGz4v5mpHc@f=8jX^&Q(42(4e_A3*C1?Nw
SQwJ)y!y(WA-B"geI*g;mk]0[Ohf1p+LJYT=H]FeO}uH1BIL&i`Z6_SSDSA650MJds0yd=F`tW!plwM$;$(M6dA:DQ5PB:IQ>c;"&t#2;1>1]Hum>%u=!M4;SgjE(Ced#
]4=5s&u&;p8j0*QSfPE?r2PuEYiTP:Vw,,%Ge-3(lI:rYm_2s=8v
,oiz(R5`TX7=v+?[`oU!Qi|vOe$q)iW4@mW0c*0T2ik*"#}nlFK>`t8:zMq"|-|>QLC"-Wi/P4
_6EoH7A$6fo[!Xe=Fw2"3|Ezm=)FN]9vt`l0*vHOY48Hox#0xzeH[H#OW0U2Y[/Eq[;z&q8
&pcLZ%g*)}y)du]4o]>.$t($1Ixe!gQZ.w#VvU_@vdiZfmJc#nE$+=w|Q3j5miY,JCLM8Ol*]1g1yb8[(Acm3EQ4SaszZs)v?vJvZ611g7$T<><,gLuS060lmKDFQ%7*!AQ=[sde)))?2^*JX<BC/zfJ2o1w1A5`(@aK8l%jl`[;>|e>
;^puwgSJ>7eN$<%!pUl&u5G%]d4Lv<#v2#dnGIK(-&rM0b=Ptd<8m0~:a`N)q/ahm.^4%AK=MZX#ds}=-09qaI]<KDC/XSi3*hwjx/fxp^G59wjbofb1qlLcpqU(s5#&J_/2&^*_5<!.E:^q.y[i47b27XFF2UqJL
#n/JPE|;|-D"raB)_B)Eu8fmRPBK]7:S#S0B8kd0;+k7iS)k.dmIBgk.FT8uYZnir1/GrtyY.9.NN!G4Mwybl@6W@J^I80U,3_tM]!Qt&[gb;P|Nk!|*g1+9iA!bdoF--XL$Tfolg6+
""epSn:%~h*JX3^%jsa)Xdxf"w58~Kl@Hbemh9uQ%Vi_96#&Z>SNj^*t#)EIP.7F
qDmm<#=)QoXHD]3uiZ4UD%i<=vd**Lf&;(8~@CI/is/9AjX{iNb@JjW:aO6i`t?oo?
9uylR!ss9L;?AC?P=5wKWpr7%fa38EGY-1M/l_]kwa],v7mu?C1i:F&/oy>gUKgoB1}i#=3dtsm-mna9&*+<DH$r@)2R]apoH6so2N)l`tn*^f{EZLu<|ISr)lk:2S1_1n,
aDxZ!*y-m,de+A}/Om3bI=nt%WL!nDEs>WNImn!EF?gR-n:=(d9m3]A
H=0Z0me&B8`.c<~H/I1U0#co4c!]IC]<OK6n0p$OG)Z@Z%FOjB<_mle!i$L*9*!*oe=77UHEJF@FA?_xmayl+mtE&[}pYg;Ax,"U?+wrW.%]?D[`x6_$TL
:>I|:deAV;tu_$f7ffU,^_)*h#*js)H%v~Br#7Q{*A-RxlF}E*I>q4^P&%^eu^:qDu!hPB<qTfY$)5*Fc#`>k1bAnNqRk=;e8Dn?CVO/^3cnlJ?3-V4&4Di@fMdO5Mv8%"a<$6H>
(qvkVvkC~U9hPCS4y5=]t28HPDXGz=voG1$l@$$+BW#1v[5p:KaOVTk?FH!8/<paH_pl[:1z%>|B73
EIO*IPv56zNgFz(j?Qs?sLn%.G?wWD965r/Ep9b
&b;@TcO7UmoIuFM:H
<)n8:kF%UnA4Z:n>x<rm_lB.<s]r`xr"feHo`B^.HH-LEb;Pp{&&m+Wu<$w&tJ,s_nkC+Q:%Xp:!e-yTIdhDO|>4F[E
^3@X]9Z0YXuzF-3`uNb"#*P$%v0bw)H6i-5+C,!b1qs18=v3M&A,p.n0C~^#]YU|AeXC+{woW
kr_I+zDRt?&dYdrvatO$bwXljjugcHiGjg+JK@f?6%j*DR35nsCTS
QEBs]tM1+efIt+`Zw]i/B!Adk/s#q6w&sZT"-|3l1m:U:trDG?U?]i&Cpfp}b?.:kp>")xK/$7YlP;E#f,hq-mC<
@pVPoYv0pnzmAWFZv5E
},cs6?`
hE8H*@oEG[lt2n-*bMn3$wL>QJWEz/4WjAzR?3Bf3_gpm]|24!>rEHi4;v
a_
c-|2l[oUcNZ
?t3x=]p8gB^fMYhQ@H[Vk77A!A=j]ZScLu9Q}Bzh61~esuES@Ie(Y>.Eh%pY=.X=&0j
vP09e%8c?u&Yt*4q.seTDK5VCk/lnrZvGus1)pV&/R-A2tC$i4#G,bap#P&.wV7g8eIP`g,,7RD9wchS;u,)]vnS3raTliKcH#&dC7I1FmCGFX"Kp:LF+c{D,8aq+h8SR)uCenO8Ivqmr+VdUq;KxsDHpvbBjML8qK{R?#h3Z7^O"57,;o8<@->^eZw
1%i,b"AE|_Wwa&NeGsVeGWbukOli[eJ/Faw)YK?LZa+CjlFtf8QE,6x,jN1J%t*.DxJV&7{p,T6Y|FjIyo,!My;]:a0q2x~5.2xY6C2Au
hTh/f.7r%u}Z}d]u*`0$8yc%Y27N?6SXcAg[_)GFE@OOYWHw_l2g6:Md.gSt<4`c)5Q!;&-J-s`S;7hS9rh:i)h)w38/Z89e5Kr_(8h<VADbMTF,+R*uxa!+Y*Hx!w6]HGwY!%XTSNAv3Zi4Py~Qbm^hv[ki^:&kJ(5D#<n4St3OzK:Im4Z]ZtTcb"e&Y)tV.qfxSM6o!9()^rdd.0vqL^db>MFPb=
k(ETX1<:"9dQH#L+F@E?Pp7r.<.A>{5PCT$xC|4hE/Tu"?I6d[G@UM+G"9B8q}"cu7.%2UO{<!9mv6N=eFcsJeeK&(:X^;T3L@&?=p${Yg5"wG7Nru<,<jYDPkqJf_Va-q*7cNdwUoM~STlNyds8E|b,O<[&pzZwW|&Io+jAsp
*v)f?7K1]F8+b6MyY2d1z8
Ys:3iQ]
rwROqo]
L@9+cH4%]}7D$>o8)"3v,CnYQ<&fQ3[6VpFfTZ1/R<k1pnpY
tB
bq+wSMQSo8)e=|7z+!r/216Z>inq2z-s^KT7=[^Gj.^KulN0%!v,bXBZq0;I@UH.CDt>yG';break;case'ca':$mc=')]^AM6LD),z0dY502Q%O3;&huWdD4Jn@a86u0lVNq!JY9y=JPh85X4QA&0,Xv!P:7i!Ch"a#F!A>rl];GkTN_OZY<rZd}n1bWJE
I@3^Fou!4eCH_tAi=/d?[qV4bL:]L[bQcMJ$Yobu,6i*1w9:9D~a,IKG_qjgMYE6}0i
(BOIY>I[NE"46;gV)jkXVTU/UO5Ay8p@7<k3~1iw/euegrWfB[`_tLS+GTHBhWC6W0dW^U27=lHg~Kcp!)4*w9i;CmuB
O0qVoHKppobIv[ZILoGzBM<N7h`mmKyFMbXyG)puc$,
^1fiFb=zQNa8%t=3uybd(Cn]m+Lyot2ogN/7QvVsn&s[D:A$.4D1q;],pirhVw1<1<+nF8U05jrPkh*,4Gyg<FAK
w#{2@!eZld0^RW&AX$[o~s}R%!OiFEScc?1aWXKKpAM$lkgO_m=Lda#i^VP;lDH`@$GsB"IG!aG,exi
^q{`pi^@TXP^|rpq~KE1
4<g#:|W`_|3Z$~mRh6J2i`FlSz+8<NgRAtdm>OCND(b^=.pi?%J{Dc9+M/E?D2[@:woRAdm$Kzo=a)ws>WB($UV:3e%fA,q#KVO_[a<-^t/D0`jCP1Vs:?Z~[I_%qw<:wfje$G7*gC?ban2.8&9;rAHRe|Jp<N&q^7ex@2P1iy>8-}y,?R_I!&r5q%:?ANn{q8Gp-XEfI)0+MxA?1p8~WaU>
e(G*"s;_GAIGH714knX`)#DV!Xp^nu=Fc7R9k-?.h%zVFpv(WCT)8wI-
wj)f;g@LZ(LTc(x)J{"n/K^W/Ol8c5`4!RYHGN+-PS!nEJWPVH&8(TIjYjGX7=qaT#mI(kN$ZiXbHX76U[mLv[7Xmv$E.kKLqohpxD7nmBf&$E(yiaFx`L
%3rQ<JdZ"M#mLrc
rDon&
LPxT>:}<YQ(=W+@nS=}uyc=RVo,8Qx!JtH+(G5>,
0UC/0]q9H.!~P?iLmu"1+D3|`(punS_+pn]vnpJ}cW<Cy]b&Z?S:)aW<_]dQrmSoxAUg@wUH]dE@asv%o1
y+;Bx-50j;w0^c.b0c+arq7g#rA93(J.IT@VRr7Ck"h2O7PJlm_2~(2K->C*c@9=A8P2S76,hYnQ7[2E9]n*vmwYmD-bEc/0~laK6DBMCK[4c"
bV3;7lqAD,&;*(3-]q,`l
X}N8x=^|p{*FSS-)MYsH%k!AtstK$m?bsG>)#/kG@.6mKKr[w1AbWWsNhxE2FR8z6j5nt>pH8Pyy
j?+W_iUD&K&wSK|;DAw&u4mRYxU0EhVb^x`iRhr2|<<o|6SI%1?0^kA=[1e+2TWZ~V%Y2CK?n`2`h(BH`u$3$eS
!+sfbLlRw^+&kk<8?#mW^VE3[y-&UBGSDE=ZHKeHn>[?L1~DSZd*2cC`;b*MxB6p-8%Zd?Dd|<BcgV!FgQ-9HU2]lFjcc@.sQXoune,/IGPXkqU8g?)S$[~4x1So`#W<#EYZ>-vCaag)0:E3J0lDc[^Fh9^KAPcx8]l3K"#LVcn!or;u>h8.m!AWz;P9MGsTkMRwFSr,pkwKSWh:8XR5D4<LsK/
,#T7FGZt>aUq~E3;iARHgxPc`e:g#SWbgdm2eBWQEcgQ8^e%&0P/2w(U8&f)}M2N&d!+M<OOSLFijN
"egx<Y&m`mH=#%uoAQw36eH)@av_0/o#XE?h_{O7&Yrp/imf6`L*iaN-OM:6Ya_[Z6;&n])`"!<aOC&kg#q$x<]qY1>
(HHKK]XB8V(Tp#$%/;d3_kDdJrM2&|&V0ev
rYS=IsG`$H0z,Z!x?e#Hacvv6B<.&#,|dDLS
A6ZvR7#C^%/-nFO(~(9).VqA?ogha-Q_$6mCJa78NMZ4ac~+<]0[jRYJ0w`3pb
reO:PFBcpkp`%zuvWKIPUs4gZfg]Zz"SE,XTAb4CDvb]Ty_A&lWk,S6l/+E<A)w:
oj<$,A7**68A|_bD1E+*81NURaWAgs.=4TCNkW7!(aj(%Y~8PnPM]QkdSs#K=<Jl+%(riH^80rg.RPZ9oH?0KW{K5*$U70U3Bt]X}G#i^"z"]T}"(#2DL*#%S"W&$Xj9VVCtXET!
o2tu]_F^QY(qRj
i%MiCe;;QmvoS/S4xalV4Np6B29([<G:jlR?!&;pdCQieL:U-@j/lS6v@]vbUTVjvgyrH5_>5Goiw*>r!+c;ZyzuWT,n|)zuT[f!b--Go>>eu.l,|
A_H
k[i;OvFGWgxZMH$nHlr=iYr@=A|BmcTC}a$V3bH<~V+IZbN#HEx)]eUjsc(@Im`VK4pXPWI]h5-UZtRDT&5g>9Lr:rE8XBnMHNg?&TQ0oT{-(g4p}9,Qc(x4zsN>Q"1mn!4s}soprf;5s^.<I3ovNkzROjKrMO`]_lMIUK0woXOtE!Stt=>vYeGB`>O#Kf65G5_gi2k^1<R9/,CB~xc;Ki,s0Lz7$^BB)H+[#4Mgf:Z[[)R4N>n(&%3W9AhsP)-c9(#s,G8c1&:w}?A#*$q9$B43$PwVjW(F&2Qx}TT=e
)SC_&&%;/>~]p>VncPeh9CP-s=:br-KpJL7?s0zfk<4OvvN-yPwQmrrPPY]RAy}tzgh1ANgD_+R=#8d2?rS)Z<y(@Bmh-h`$q4t6G2.qtr[gyIO1r+;[CJjZOjwEZQ[>mpZ3NMSl9)nl/J<.NBD+YIU(T^]b<5ZkW#/sWb$4;Z1jH"r0PQb?<5-T3TL:]X-OZ.>kXoObIN-3TZNeSd:wjRb5WkCX%Q~0**Q<H<;W^+8.&,kOR!aBF%7H[u:FPxRV$<8t>LJQLl0r;O!=r10cU"iD$J&U7S/G
oxe%""IO#?Xp
a<q!o9b@LGTc_;66a#N*t$I`;D7?WL_-]r$gwK;U%^v-P9Rr8N+k?AF&;$cPEsg]3N[-0RN4dKgSVAg>rt)=)7p$GU$;BJDLP9)t5+56.13X[E&@mr?VHdO>x,vmIJ)>{wbGWO19NnkTOU7_pwa%">rh%-EM#8_AsY4@(QNaRG.j/,jwLC//7ebgPu#
"TD1;SU8<J!>JLsd;.(u%.B#=0?;6q_1LiXrWKzc9R9=Ny
*/?=E_:=:BG*HE6y/=]kAd<SQgdl5*PkdEKxg|JiVe-"E7m(5u]JI*J6(c62@l:tQ>.X-0^+Rt/eruD?2B]UB.lt+AaNx
>l!Rn:9a,hvY:n&]Yw[0G`"1U+Z[do:A%K&g1_##s>^aF7q$W?/.I~27Fe>8Ncs5rIccnf*Qcw`z_
Y=!gKu(,oD#}3AEoFG[E%N@Jn<ZBaRZW_L,!Yb>$qeUTw0r6/oqq/TP?wC
+l&@lW%B47ab{j1`p,o.`l=&{6KIDUCZ(i=Ay$N
WDzWh8?aqMtv;-hPnpOc+4T$I[p2bhOOg+;r=i4QP0L?p1z":ZDHB8wga;E/Hk?pNPcp;%|jwmmF5SzBEXbuNKw+u5<LzDbr=LC0-+;[kf_
A4`,unO@&QoATb%2YjSQrRMSNN*jjCt^~/]vF&^Pt:1UV,,`L2K9f:6Q<8iN?ru@b(`iD/x2jZWB
YJS)``Y#/J_$$#7oPRh[f^6Ia).ldDOj(Ia[RZ.q1o^Ek2l~mRv[Pntk$4Mf`.Xm"|7v`4qJK&xusL@N<.TTE;J[V`o9.P[]YS#9_V<:hT)Ja)_0;kiO,NWJA
rHOdR
.ypdsGG?P`ict$*|*8fR$j`Ze
3#8Xm>JjL0JEfL6zvtojq&=Lhw=AU^DiKT#+3UYt4i)`V
R4248eRO7$3/v,%JDVu&8:T2VzIW5~E:RhS4g^Z4/6a3wtj@n`D"&AL%IWU;q<%v6XT|Y4KWxGu{Es,XX
n/(S#.KMFAKcWfp2+86{j{SAtmOD2#>?_(r2m0PFWFV"6I<bh!YeFL+
gfeBa;a<C*4xy_`S
IV9_(YM:,C`FQ%;&O<"!!J%&?r}!aY$d#E)p[H-nTH/a0:`B:fLmE+T_zidYOxZh:ePl0_gQDHtA
Z@"q3>TP`gcc*1QNS/>C"zZH4I!"e!)X5V%wSBs3*7XQ5=pM
Qx|ST0v!J_Qk?yMxkEdms$ce(`~=vVltNc(Y!)~ATPK8r0]xPmn!xG~4O4cT)U
SA3V,]"gD_[b?|<tnh3j&NJg#owk?CX(NP?rGpf"d*]$_UA)Y[dMqUn^J;;$=XV?Q5[MTUFP7O>e.>3m1[1G*Ni"h@3%l5QdvaEB,RRPj65SQ;`6^E7UYM5%#.r<Ej!eej,O7=S
L~saa7mx!S60!7P#Do..`RdMXNxABiR%-e>w,*F]IJJxH<Z@S@>"tN,KT&VC^]Z
r@L&cQ"?=ztFfb6
?p(?6V,}eJ;g:x=Nt^P>
,[FMNFf+;@p^Uowx
NW2c(s**%6S>MYU(!}Q"&hxe-%
TJY]V+P_>
j05KuFZDyld+&EnV.(6rLQR9BaSwh#R3ne-W}GJoT"SnMX.hJ&WK5[9u:d"s,1GU>:(rn>3V6>0@BMU!P=7M+BmPw@Fl)f3"$a:Wc:H"vb#X%`<a7PcnXfJ,Ym0<@U#y)g($*[:o#P+rY8+++8bxXiy0+)<2PPZ-g334C@?.-i*,u;=c!Yn*_>c5E"M9qE_S$
?K|LdBBPr_Rd(Nhj"pTju[oG`m"O{B!]Jmg<kR>h+KC2aR(7n_*ut1?^S[-&?IRFvTqW.$*UreCKf;n<>R+%+KN7zgTc{va;{;4uw"R&(OyItH0O@z"+]';break;case'cs':$mc='&]^;;7npM,|?|8,CIPrIQZ%l]Wbsfbku]=AhdM6=#3@GBel;CdZq#*6OH*465r$*HIZ,be&O%Eyof2LV3?WJ0DbdJ#3ros?gfB3yD^K@0L[:hj5b4`Um2g:%cWXYMCwGYSFy?LZ`:kguiI}?(BUGD`3Dla{t27r@sIsxyb7
j:1f_;C;dA$^*Ex%lV_SifZ6L)i
l
zZd!x.m$@^Eo(Kl95w5h7w+Ls7PB>2!&kI_+Ls]rPjnJn
/0qW-`b0V3rvu@*K4*7t#cY)lgyY$x2v
6)1@F]t/w,mXtQ_eniV,u!jVmGV1@:s=n,tQR:vo8Wx=+x,W)lcCrA@mCjY_P#f>D1taWO*ll/>]4+k^E>({M[:Vz"[%3|4//
w$dFD.`+8?K4CLCevkmAOtJ,GKU=y5Ijs?b1R/C^CQ2A*mK{BiC;3aKUvXQ4k(g2>N_CLQH3`#5LJI*#4d`36v!?MO6l4w8zEe
L&@BN/z1.<zH0p|l_<<%$#r?5UC3R5$<s3T!}J:D<!5njKd-QR?$|7p6aZJ.2=V7eu41&a^@alv;F%{?r*nm@A$=*2K,xlzm.H((v5gYH:bu4H<?jCU/rnrS)tSN~iwtOf!MwVJ$.nx_Pb6#HDGr9Iob{,{oJg[y2T{;Oj"/.fep;^rIoTo3:7tRTL/Y_?*0s6LqwFJx]N?%0u/>Ca)+=Xk;gK88*XmA&f6=.
yjKRe<Ot"MDV}P}R/7g_&"{u,l-rtyp7@HZD?fpB@tk<q@v1^X+/
$S=E"h-;Zk;IR$%+0WT$<|V,JyM[#PilL{5Y[:SV[]kSNv5wXWj.iH"#bFE~_d]0yK3yVZ6`w_I9GCk"WDPYRCBL;|d_RC-qn&9Gn701mY*+2G8WE:FAw?<v>LRvIdW+=Fqcwii#[6%~MJ*%S)m~cIx_1)HGIvyNY>TDKJ6?QCMxNisCS$512N>K_}n)IQw3J@xqFfrw.Qndu
OxxtEciKJ0Q3jQKKmK.o+s[Z`x-Xq_gjI9?%xBYj,X-_-(!D
:pNSR*Js2IGBKw{-frNv].+.h?$rX6j:~Mv
"%)O{Lz.ebH>Q_c6%YH&ShooE*Krx]s;:*By7@ZY6c4yDr.;`(K1IR&MTv>utO3nu70Dz[@qt7+`7M
>lVcFlftk)!qwhwRb~J@)^?P;&^p]B@Vuyg8b8)CXmNwp8=,[N<[Qu>Q1^_sTJfrP5$+x%0eTY<#lU>$#p.OX"2h3qcAh!R:loqIFRQJ6cYIPQpHmn7)v
F3XTpdHe5(^K,3)]ODN7VT!v`6jtP^4vl&Ww4?VOZd-XHR4k=b/N4!5P<L;)0ewT3*,V*U0F?MjM5P
M@qxg*.n!&gwmfrkgIt"5>">ptCw<QxAQqheH[ZVYn91a9oCCE]jg((6Hea;i=<d!hX7.NC_l5r4I1hYC.J<o1}oPcw#0Fn1D.V`gftjE!O_hmOP`>}C!b_(;7UR:<z;j9+u_:b^rDg&k^;=W($:Ylu(]2=w,F:fqiF/|0VQxXx`i1HRMSh0G91_j50kk"DD{YJXxg
m"bYirq|s77Rrm
^_b+pEgRg;}@ssSjecJ39++ct_#!/6al%@Qd<=_P
Az:bj$vuNTJ/-}REh}Sbyt_)2Oh#ll7$*Rk5NF.=v?%Nci"=Swa{?Yj-cWOg9-EuuUTOkJ=5[rdRo%JvYe2~52x=h4o6/gR:uU`SIOFF+#yKth]42+l!yDy%u
N5aG/FuO&<,6fmI3(~Zy!w-V3Rj^v=G9k)gGUe`RFwY<2o@R>8]~)q?)VJ#`*/[d+C]>XM-+/vP3_Y`ueVXr8p6LHkueX(>y8)evaySTSzcr#A3f;%!k,wN3*)wDkN*v/S@I6Xf?;7"IDbK!th8][78DWK&g;~L)_2%dS&Fmq`5qAF[l9gu
I-J)U[C=k,ep@-"&!8d[6MnYk|H+(_GPAzz(,p7(`0ZjR8rX1"3csK$/K|^5%zSl"s"!i_djBWl)xWT#/lItST@(x<D"/TR0QW36Q#ZJX!np:T$ti7Tw(|nT;8uv#agZMyyG8fB[3-Q>@v"eVyg3O+jK3.b8@B]
PpCM<X"jgaiSY#"<E|e962PRV]2|IG#sSaaU6!r}eF(6=l2@j28OR4BrW^d$WS]:tRJW%*lR)%4fA=#@E`kF[7m;hNdF_j?:0C0}>p.,p$P+u#=H3>[cvlDL6<Tb66;lJ!bn*bO>dy[LjuX?@YVrWb4|0<^fq/9^M.14T:;W]@]C1(&;Xb>^Sph:+|K<_SL{yarE"!N5OR1KwTQZ.sg$A2(^=%W{bS2C=C%ZKaReE~4|M&y|Cs.p^pvkkyT/IIuhI61D+2=Q+Z/n;=e30Q["&fQT87V~=9(6jpg^?4Y`OLx6@/huFq;_XMA!ylvvEE26x{avBCxm=Il"b_&-10Y7Rc^%7>QpYNZ?d?(DFMSJPkY`LT`H/G<y?Ip)yK7WVJftv]bn-61W&nhV;zxFM:MpjQ:"9L><q8TjJ3=A6o#<2,`[b4B{FIRmImwRe)?*hOfwTxSO`+=JSFp"P
MUs^f{FE0EW_56`6U|SsGy:G(wObG{TcAV<E@&VnWT14u:?ekuG[O?22tbK~]|7WPuR:1},wc$0$dSe1s:&h)YC":TEG:#kr-(ZvQp%T2[(&LKC__9*BW#
fwB?STEXWq_)9T>k@D~TPz!H2O,a2TNl9;3C/Bsa$35m@=U`o,bD=nN$:E~IIZ7vpvjPzr.;z?4::Zx8Ysg6{+Prx+O!WM_Y5BoLdE6!.JE6<1}Zv!Y6,QY+yjg"c=cVN"v^(D}naM8gbu
42mes6nn4,FBTOZf9"P+@|_CJCsBQF-zXn&1BHUuwb]o=
H[C]Dy*z-!NNqkNG+iJ"d|re0m^qS<>n]jv
"9--iSQ5;lNNN<jFpREiqu`C<R*p
ONB9L-c0Nmjc,BNbAxddYsoO"A|neE45&M-&[cVfavI9+q~$&Ie!QACRL(|RqDX^qfk/S<ClAp:8P5Q1I_8!U&5Vf:spxGW,/8w-,/l/

Lu$x#kn&#2,8+IC)}L^c<,Jqw`4DsDA<ZiaR>t`$u^&BKj},*l2a<b#tsj;wFk0h6U|;);jg<sE
Bxx@!Wih+/u1,#us5`7"-RKfc06go?MJ__LftKl:zNjw<UL5HN?kn%zMW75&hV`AoWD]Mms%n
:!=PgStlr[*XkW$m|bM3kU}J_Q2Q*J@w??Lcki>l&ZYt3;Jba,8L{x06+rNMx*.I=*(qt
QJ"4hi=.Loi8e/`QwM1@85qus1nh[m78wKz-+OxS_!Q#0Fc0e8RZKS^+MIQEUO(:nX-ZtC[e>b|H>UO*"-,aO+j&GEYav*0`6tYA&)|L[8}T{NEH=C+^88mo"(u@@j-ojOk<GwJNHEF(30|8+c1GQdTvF`:9oc0-7;I-4Rf,<WtmUUIo(7q6D:3b8MG,jCogC>#cL"-MHd2,HJrDvurcO>Ue5Zvp|Z#&U0LkSTWZeO6`uZWf7BR6pkB%tl3aJk*uPaAO%RHN;=SsGl>mx5C6J3znwjb,tM
_4Ul8.PZ)[,EHs<gsxNVaHaWNg6r[=KO=QT_.xjk:<K=:aiw6rq~/m&$0`d#p5%fVl4ItJ=xPSSNx*,ZR`iN<uQhJqUw5Ur#81.<C>n(V+d;_~qrT5i}""0wK9=ZZ3_?.Zis<*[!h5lT/nI6@<sAu4U9B#PS82>ckAJBp<b5H/qTL{VMM8hUx[A{y3UtmfdU$GZ2XHec78%5WK0pKPF1`A:{UFx+qj+99i$/!xc<Jg(f/@Nveny:,MUVodJ,r@?D,L<HP3xzo&f(JWu?T0UlgchN4n)E1V)7rsQ8)rwY,}2y)[9I0~4KxPIe"IQdeJR%ke/$66:<y,vJo9,j/a,.W
e/q^p<8##2]o`UGQr3YoZ"i?7]
X9YnySyOo^?/C#e<[^
&@&rpBo4srTPW/[25p`.9yPb/(doN955NC5LJkO6x[v1feNbiH"n.9
Ao5Kl0F9$1N-MdFa36Da1A}2XmE=g4%fThD5AO/mC<;yUJ`vT[AK*&s)=S,&gZYRaLV<e,cAMQ<T<i[d/
r$oOze%y/2^KC4`N_4$PRp^ri&UIvdQ:{>@,oWrY>S,i$C+TsGDac`g,YrwZ9@?tO/AhXnz+Zz&-F#dn(1if>2oiYP[d.mw[X1:<w+][8/Vf:,^(OnCTteKbXrb5R``J<N9Ee>,Mn#qRKs,mktBN>5,Z<R>O_xUnzn@Y-G!/0CQ/;JTl,
QI0mUV,JzP6r{[Uq1R:$I
5
2<2nya##?SyP(i#SL&A.|xK3S.rTqM}:y`[F{++_/Jt602%1Ifb5y3zc&QD;W$/YnMdb.I~f4S*q{c]8zRBKMOjT=qqvzj/k"9&c?3?OsBYkTl1*w6$@>BzWeMK%UoI;)b)kzo6h_"vBI=aR.^`cp`^DHnn.N_;52A]OJKgL>JTMrR.u8v:Qn)ygdn>]u!/C~sbq*9|[qH*]Xh-_.37yMW"uu64GWn9dR54@&v`W:Ko/>yN3{sxZ<?BK9"Ayc$2b~[Hfz
}y?k<rN"dg.A8vYRB#F5m[YjBg9wA>S>em:$N(I,M5+0]q+N8kq"2VSLTOK<vJq,dc/-AY!>I(%6tH"X_Kz"5c)-Y"S5j2NN^5f3
_IqKKn[nW~%:)cB|v/,R-)S^JuR9Is,2^BRGv5=PUoB|=qN<;]c>)r
1WB
x@i.up8CeA<;4":*:2U!cuWWC7T5y1nb2!+_Xx+K
KI;vradL%v)AZsmc"J7~l`&^Hpwm;pGHkE%x5qL2ra_LYcQ*sx:O$bXa,]<n(DvJ,3MXX.HVFW-L:@8~d;3rwG0f_7W
B|!paAC_>I(^0U%+)F8*2EMAl>j6HU/qm9aWN9&@2Yg0pK;<4{
iSQIxvVp-Q7nseFw7Xt';break;case'da':$mc=')X/ALaMAp,z0
Y+$lT4T_b>R[`WS_uT_u>[WFpxJj6mW!G`lvF$@X8e,BEiejoGynfX=)0zqLGA)p5Cc6QWFf@1ai]hyUP<rO5j<B@"b,qDfT^![">.epK(y5tAj8*|C`1}?2ALySr>jh<9
C-o>g^)]0
DZW@g(^s3maJ3m7
wVh0n>B?=w1nYBSugt6D[J.4V_3m/<ppvuh>DAZ$QsXh`
:<z`piBZx<vl+Hh<.F"Ar^69q;:+tc#0ovprhqN^5HEhnK*sWIf,[faz#uW
ci7jTU8?wX#WY](cz&+a;N6MHru-}^+K(8;?u
Nmjb>x=Y,-HiYk3T[:[L;>9l:186#mZJ73]RWZ&iu;wc+/PuZuLv{kw^WbI3
r|fqLLukYsD@K7VHz&mOwmz$ub@R4laYH*I][G0ANo^)P$<^w|?`1i*U@5=En"]0t!%EfSa|s~-Jk-JsZTy-Dts9bEob,;,E?]YFLiZy;,KJPfJve.L0v]p6DUw-r&aTq+/HkqlnWe?J
*#iB)<xMYvkYr]]DoLB^KLdb858Vz
:Js--2p?=]<ozl_3a`$KO`GN$]<gZZR@ZsRUDT}_#kFx~F
*""4mD5t0u;BrLb5mA6st|BrtpP;iQhOmr5y)}s]KN^p1h.A
1MRGz`qAVctB;vOkQwtduYmhC=0=lIQr]ac>QDz`~HQbdP|!(OBsTZ^bB_pjT+{?LA)/VVwTcyu7}_hC*v73g41@&rbs3%1Pw6%v]q!8=g@,^1bXZg@4#Tc(8BtC$iF#lS
PrZ2g@!{EXId!)877|fkIE_NDJC.yslHQnmns1KEECho.uxUCS+?,RyUr%b;n>7LwXcD9>p$`]&];]V@(fo$slefq&n`Dbgugf?+Wf3C^nLjNgT}]{e->tkt06v4Xq@HIJ)w-Q/LlA@8>0q*`j7DY;TK$,A8ABud+;f3QvVL<~HZcLRHE:R^I)>!x>GXslME$jnlRG@kpS1ljOVGFk
?;BDv$$yP8S!Xe:c{Lm*kH2rg4?7A`Jo4]M_)QieP?~W^G$QW-Ci
Q*?$)"Df`&c=Ui@se(TD1}w~(V,A,~I#EdYr2~EQMdq%IPbaSN@?x:=1L2L]Xw=
sr;t7Bnxr#oRc[r7wt2u/<kl-:f$m:ZQ*Fm</G(<)VAY=wEY+c_JbVL|JnbABt#d^C&^H2D%,xh}=c+sp/pTuVq1c$ak,%Gjh/);TYOec1DW4lQpsIOTFErbs~k!/>@f;4Kl-0J`REoNrZy6>Ob*`nf8YY])>"pw^K@V0`G^kBO;:5H8sV9SMwuk<IvY.wGV8=-Q,dE0ek^@fKx6jVa"xkXwq(GvU+.Se0Z.c*pnIkBk.~YoY%:p%!Gx.i6mJFxR!2yBM[iiZRETx^xmYIg07Hnra2Q604D_E#U2=jS$:|UZ?<i+N07CTo<!bAZZoh,A*4N3]igr,vn)8trZq&cVFEQgYbT1H@
`p
8<F$i=U$Zy4k+wR])xo&*lV@gah(@1oq+x8LR!ht)K^1,{)5hCfr/eaIdyOx*paWD)_ldQy_ai4z/|rGVwc!uI@j
!%4o}+Z2&l%<VYPKac}#OW@;7
OJKOk_L"|FpRi@9gw?`slU*x5snp9)ShAw[p2>O`S/g!cFsS"T+C?`dLAU>N.GOLPP2=2
`A}p+".s;Yk&jxsEj_T>X#Fo7ioG~lPE{k/A,d:gWY[-7$0;^X7.#DbVcDu
GwnhmEr?+](:dfS"F]Q@a:yU8H!;w,dK})_=nbgk=]&w)/$K).2TOy{A~ZrKV:`r|H;t|d(<KqI_x8&5e(~]gb#Ua$o/3kMk%76t
7q.+WGy.0?0^@mEs
U<CS`#XcK_41
O7hbh@>H#*XQdI1GV#`"@&O>-(CSQgFkpEnuAqBQ,)T,!;<)-9f-A.[IC(RyakNJg%"L$X73o=:ju`SLfT<JkLFBoLSu0Mg1Y{ngd.-!/&VJC^C4FYX
`E:{l
KZDt,DY`*Q.n?lP[.0kVC=Jd$&:/#*sdhqsU=QOI$|ww`,*Yjj7pc"gh2N:M(;9K6r5?E*y3K>)FsMPh9#xo@n?Q2M?C3=wJp&>aj3<UpBD!L[QZm%1`>BV&)`+SY$G^nU%g!L2xOYslv)Q;U@"R(/3Og;J*<i_Bcdw;Qh[N=R:&duim-|K{QnBY/=Jk/4z&h+*793_e`z8gQ*UvM"uO^.[go)t#LG#k2=0YY3kMfc4kP7wtl!4b1aif:+<}dZ)+dpaYO!^q`+V0f*af<f;<*1rgu8_hgg<2+Bf<*d%1wzVlYDl=Y@q~=Q70C"e|)?[RDZc#WS:6I1!>nkFS)Xv.k~"Why=T;>D(3cg~wI[OrLbAK]#ccY$B!fN)iK]GvZ7=.#2:"yFi]`?4B6Lun5rgq8x}j2`*,&l&A-clbW&NN;eA(OX.%(PJMG13SO0llW-"U|!WJ.t%V1a7TOn3Z3+g*zwb#/5QV(p+Yt!4-f^v:E;<m]KHAuExxE9c7s"f^W>-6Ni_qX;;gE"#UGxpGlK7f.5pm1v!DK2^ORjp%@d)!RXNL}R+;#.dHV]Q:lGu9RlW(EO7.xa"0KF9%7T-LA.Kc9mGI1X(qG1vK+d<Zk+R*pWC@kCv!f9b#C6pkM&x2EW/--Oi42MwD.O}r7
q2oSCtpSdmqs5@yH<FD(=FWKK<1o}lA($rc02+glGuX=_:xqR%534ypfvoUBu;;PolwxIyQd<7@40_ETW[T4.)~T<!QLPES+$UpkUq{Qpn?Xe"+?y2Z.VaGh:0%#aqXNf,axQ[nCg#SZg8tGT#CK:cYC$6
OOVVDM_yA*>3T)D.FAsk<M7u&k078+_9B:*e&ahD-|VVGa4*8c"z!g)&7uc2=C,5=GX#VV@NMGux4l,gZfB].UY}P}!P9jZW7aqZl7r]!!tB6Mw,2G^KK9KHpAbDC<*hSKuG$)C=`9r^OMk&`7,EK-lR)U-dCugTCUD-3HN)1~#[Xp5<Eq.GdGw^:xKS.B5CbC^^OSssgOh=#l-IeXiinGCk&f$xYQ#q`i,4E[85c{ii`X
L(1j;i:Zm-ECVvivd`)/@]F)UR:O",lwV.o-Qp)"sK}lz<v
Fo?1lpqqY2MQ^`68d3ZNR<VbqpfrVfpX_";j1bz1hNsbP5h_[1]Z8)~SV,4!DTs1uBJ3[)p%NCd>2ZUB<_[fs+%<stzmYG5ouPpH(9zLhJ3m%[yGU_1i
$ZQQ"X$_,,bMFzVxNa,Ee@:/s$jFB2mBd{gJZ_J*B<HC,F`
AOM["Z>wB.KneG5C!F#@nID:foT5(wY)*P%wN1.tS6"ga^P3A^a]^PoLw]*KdX4fN0+je0-A6u&DN>sUB*-GoYG6?:ws7s"q$$kTC}
A6"dD9[x4#S3Bi^t0!4RyPsk-w4
H$Ttk4zM9E(I!;jK"xo3l9wi,adsha3)#3.s4%RuXP@P&GsTJS,i>OJQK*75I@>[kvQ9,;DR24>#:9bck=32Z"5@+s/wG1/Oyy9#OKm7noWT$K?H/GfN<B4Z;87yt5;&M9RO,kXg3QP&OE4+.,<RetJF<kMR5D-JeAA=*.gxt*l$!NhJ:7y%z-$TxO4uak,H
-a>d*eec46JM<_D_#D"IpT7
NExahmGwD3O8gmW1D&5>Ftu*c>"PC12TK*2cEc&z&B=7C6928%YEY}`,_x4!ev=kI7pZ)8HcCg8UOG8@X*j27m=T^
<1xS&:TWlXKG..*n@F!>p?K0fXobc~pCKI6b5YB*nUKH"
]@:<3s&L2*<0#?gZT:t+-!0MYJOc=Z6y<t+&)m<Pnayqc-n`ve(n*!l*/.N$:viuTahSIrwlc76S0SUY&/15j&lws^Z@DEJr+a[mb1Y|2$2WDaJqKfP*&D+uwbV9";xOMeHH@?E^e[9o)5x~b9q^lchO-Yji<o"XeJm`9YM[#k%W,/Y.k0
qx@BGQ#L<
e;8<iTo`d%WD"W6n
?nkuxSI+(hqzJ:GTW
yMTPiRXIwrElNRmX1osj,^+J%GuFr_;iIN!dae&a2FW:>rQ|fj/0D+k+M<dtk2g<CU8YrF<efD5@BcSL*4)Ww=:nejM|bJa][VRxi/O,w5Gd]1qf<!hCvz
dA=Eyy<5V"w!s,E^Si{+4BTZ!^LeVd-NF/4&5I6Kh({0g?(l9kLyE-`p(c?2e^k9QA-%u$TczKTRfO>({?A-PZE(35}aHV:bfz%#]#WP7^.^wa}Y5sz[j4z:s;3Wq3y!:7TCU/k
jXiK"Z)vsGpW<:vDWHm,aalZ%>?F)N6YG1&e]NwtsU$7GKOoBqcMHN}26Gn<mP<MZTKbZ=jYO[vbH!=V|>yK01Zq:fXfn2A/E
vDNDW+[8R4G1usPz&&.';break;case'de':$mc='-]^ALbP.!2M0mN&*Cks=qITg%j;S:[

]_%O6dv98ru!Ei/p>`e0v@GM>FMCu*1o6MaANug0ukr.yJ%-4CCDP3Rhql+l*W}@6qi6aZ*r+kAm]]E[3qL@Lq-ksqDHRn[g_hlArnMJ{pWFH[ZRGH|n|=jP!M&0WAsh@FL>:l5;!peM77Kkr5pZ|^Ds3

Zdw8_EV:
gO^25x-rmxw[d4MGb
zAv(3+1j$(s=1>-.O5ri.*>H?WsG>Y":tMai~/SyB^@sC?>jX_;>zZ,OrrH[bLQL7?DcYK(?OI#z)AdYQ
mcjO*y_yuBm]*K&cI/F)5oS@{m~wjH8O<1Ps{sPTukTytdWd$xI+/<bR4,De~Y(1]=;H}I3c+/CZK;akN_.6RL$0jVCP;iqIvLNWxs,dQCvulMNgnTPr,D5AWSXcG>aKsy8K0`T3DGJ
_`!(Om`Yv2g_3^DTqYrwD6tt<a7dZ4iCv$JB!f6<5fw+suQaeu,,L-GM0yTkA=]&)>t?}]DW*JbO_q/w7at=87`+FHLARDAaj+cJL?NnUUTb(G
[t61!_0<36!h@8:?q:(tY1*^F?
g-{[+L2eE+!:ox(,SD_V0d{7&7g<Wwn4f"52}Lx)tf@F9J>rUbF<3Vv@y<}I.Vb%XK%wRadn5bs2/KT*$!60{:~`W^GmLrgSyE`BgRF9~c,hIW~eNMUV>)[YVIN(au%QTA;HMP(7
%T]2TyBDN<V+KIb)sE=qW8Zg>FPebF!jHN.IHaeHNSXFvCT{PZ4YFX]o44E.RL+CcC<9pfc@hIdd[y.S],4!TQ*!o5fY`n
~_qYZ_Zn1mNh^[P*wKOBEcMSO02bEUi_YXBnjJ?qY5CNyuwM"UNC[(uBuG5;$v~oFXpVlX-xIKN0JKi9cwO(_wzo%
Wd"AO9"Z@Km3a9Wc
A^leQ.UN/{k$[0#EYwDR1EX-]w9
"O1J`BE^6/UjJPTx01R*nxQdW?Cs-+^LM8?%$d-c7%@e9g5,r}ecD53s=3idK-$lg:_Hg5P
d_7S7M]zlj@Ry9UTu6UA9e[Fv5JpR~t{]h.cr2#..zjVi@owA)GwRMP(W)VTv0
=TndjB,)yTyv6(lsE.>v&iORk/G$$q9IKk2/2#~]Jc-;oNsQJ/5dP0kUVSMBe`fQEA9##q
2P%=X3^2YC@[#PbuKZnCiwWBmu%t>Fv-:F,LlL[S0TGccXqOJ+,w$c:;0`;!5g+,c91raqU~Ao%Z<W:9tI
N%(r;`]AF[v5o@nYr%En}K/htTFB^M6
!^~MT=@7WD>5qBl@sPO$j[rhRh<o#nx_$^yq:s_
KA|]c(wXF!HXNvfavWKUM[Pae9fYR,&7,-M]k0.;o-Tiqr&a6Y$Bm1;UXVLxJliUI*oR9_h1K9SIKaLJRbM7wVN!g(:(HsP!?s[8aG&`vB~Iml
Zxs]:#62MtF_&jwOvCXzM*K[
^v:7(?~p2"X3!u87Hgt*m&~nPFA!p(<<d4l1P/YpID`Ps=,aG1M_k?WlnsFgr#Xh"r)jS>UG1`=:
T}8}wS4|I(T*9GA]qJswYL,K73Wck7CLT8R6#jD^Vg;nX)4m/uS0#RB_%IA$!]?V<jj#2H_N6Kw-y]F6A)n36Z0%IeF@;lU#/L&.-:g7X2bZ9|>0QKA`Zh<I3itr$^ZnepRi78g?6;GYYc%"W%n5J+Nt/fNg:C"T
<Nf:PUQF`[u@q,)Q+m<Y(3+V
0=!qKIE}bSQ_;(0$10QSCgJFg<I!jz+}pSpqoQWQOVTtY|iXZ>iw9Q>vP3?Hh"uwc0TNJe[Qmk`H#SN5SYQF"+5kwDdK&n=lN~>1k+jnoV=/t&F6adK!V-.F*?wlTbE4jpYR]"#IZ,nscZXt[t$jp;Hzuw>rFHLK.Lrz:(lrCtaMt"C7ue%IKO>sFA=U.g0G^8)F`aA+y;3(Mwh$egCA0ds(aAv;>INT+"827=l!e|+]5">]81y?EK<p&A$CL_![8
uTii"_a#`i.EM]O[cw-?#1E_eMH*vkd8-0D^[d>}0k#0Yfd+JjM$^>?3mThFMtShsuKPJrB)#;j([$u5)^;#;fvR1sP",*gjk2E3Vec913SfiAl<B7UJH2:8O%?a3jP%_m]0"SAq<)k6)Af>4mKg6}j6#4J6]t$ipN-#HwZGcK1
#"lHt2x?E2dvvEa

#W518DX?K$BH&T,GK3_tEMX:7FR1z-lJh&%)Q!#D#?zSuaswS
2#fG"GS$6U/<87db$TR3*0`d{/T=h,Ka~r=E]y~l:g}m~QYUV0((/XN?WrZ5@4BRd)!Et6bO]0XIx07e.;ahl
B<-G(Bi!BOM8)`_#`!}S-u,f8544z`(]LF"g2rZ
8IM251Q`.37Q)Y[?G(~f*J(p3iJKJ2|=~3Z6mD`4eb`AJGVnAm,i/:zvi_e(<1ViDrM7GsJ.arcY-0#rZ"2;QU5uYK^,E@:.u]G0"Y`Zd`mTrnQC-uNYSj-b.yt-D-x3{h_r4$x%9g07tU<,dx!SQXs^hLr4|
]yA>eSvpJ1J
?XZ?+H.HS6~qeOP#2ft(3,Guuk8Yx]TW~vUGsFE_m0B2Ns&WA]8d3I@[HTTG},g7/WL@-gMrW)V8Ef)^FT2Z-Z<ML+WMFhFg%
md[CGJ>1SBeqh37x$f.sjnHHT!xvpTO6;h{k-Z|xc$coE6?K9q)cl)oPluC,nc";8JqxZS"<m96b5@N@xu3bmy,y%6nh~k}jCNngIV6eL9[1fGG0:i1UC)8hw^|>zr9]YF1mG6Gq-EM,pX<J_pR(fScl6]A4^g_o;O%&GX[Ai*Q-?82%+fj]"`r2AP@%$T,hi"l>/#*WbMf0z)0u;TLw$Le]D]@k#=$X-dR`>o6w[%>3ws4>V&U:H&.0R5<+W7m%rklJPhni.>T+>?n-)pT:<E1LI+qgH(|ml
j&GNa*6P9;;j<.GgnOG<YtH]3PN-wHsqaToU}AxPx))wl;z#JA~+zLhws!xCK.XyE.7nmNDaVN`%K6MX*+--aK(lkbh//q]o$c0.|r|#qxR#6@3pE<"bTt]Xk_1n.Q!mc]:CO*<ih5Zw7xV/=!Rh2.hu.b>*rE~C+1O[^;&@.W.3N_V5J!v;AhYac0
gzhy(i?yRP-#_YC(9JjYD"$J0ogA?=!2H>!)R(KIHYff6^(7+:jlLeMzHHK7*)5e#DcF*-)IOnQFD=`mh5bvm*5#q.=F!<cZKKBBXdR=SxN$yRZae-v^5cIvvHrGsi?
u[F/Hh%hD&xP`@y`Mzm.I6;naMp88!qa@.+Q-ZY{ytWE6bla[7hsG+6##
=#c%O2]2(+Hn?v`Q-mw,""]D8I@u9N-<w[&5?gOj^
:Y
3&M"i<,kCU
RzrkLIkV`yIC1hTH<cHnt8K~VEuMx(ga5:6w5{^]6cIp3"`O!@.*r^a$wCZs**>9KlT&WcBhCW=|mOYl!Qs~1D?#ID)cQs7d,0k
iJxp*GR,+,)[c~C_qs-@^A,a`2vgPrB~aJMUI8u[IyB^Bdxg8[T,&OoJG8l^P>/3c?.dh&XkjP/>-Tw^02@G"f)D4n[D9XniCdO>0T>`uX/T;V956P^&eAheoV5/>p99!a[<)XT7`%1V+;#D_9^6J1hnI|&O#>cD7y/bsowP`_r;RiJ+Vjf.++LGF-wiKc^}yR;]
QCC,]2*Js+jXGe(,Zk*MrlK8A!iuzpQqj+=m-
u2Fg
BM0$)cjr
tOBc
AA!gs[BF"bY>K_W>^VL}V`31ai4v(1P<Vb[lM?EMXEEtI^G+("G{d5R:6{pn?DaBu&bm9hyPiFd8;swd->R*/0YDMdG)pMhugAAEKfY26cTl2W"sV?R_k#"O8My!qIIe2M(A[*2ZcBZ6me&5
<BD4S=uM1u/&NV.(t/yW9)I!@ch,xd^,)iJ@6ge@,o>j6#vxVv(bcwHEiO<dz$GL^I}0LI3l//=!z#4YFHg08+`!pd"6!tqOfyYWrL~fvyCA:Z2x~pZO4R=!Ja~0)3m7T))#-y7a4(t1&LO!
;89$^G&~$_5/EbD{iAEhj-3sv23Hh#_=pRNDE[@J$Y"}_7o0PorJAIt{-BL_20I+H,l^5<foo9j4Iehqp@rpnZ,_^l<0w_+s!Wwao9bRTfKj$*:lR"bhCg)cMK74D@LH1.bNv*"O9?/iF]JQ/1.U8
aEi)9}pxtd?gR)U
n&1>Q?.Fh!d}i(ii
}`]PKmD(eS$_z.}Q<uFg2QMrL&XGZxB7-E,VVPM1uC!tAk>6bFhpk]~E|T4r1
rtOV*u]SYm}O8?W`8CpQLvXTNSEN#7Y
8yCaf$^MWb+!:4nh5*/7qXFn<Q&QSlVFyqi-NY6(Gyp4U[k+$QryJ8c7Ox4]SK{3sTl@/$;Mlgl_j95Sq#ByB=]=yW%j]#DO?Noj2=TU-4(Hp#?<N,l3|T79R_grE+seC7A7AJo2[Di*Fma>`2:xv;Hu&>?VHk,r0>JwunWec,[&OJP&m^r!=d@Ay1#54.t@[AnVAgrD~UV`msWt6MGU3qvn.lAVm%74~]h+5nFdFde!d1IP-0v-0mMlp9+!7Ns5H"(00Y|o0gb+b[<Q%/B]R1J7[,>P~*ZK3yqgT8uN*J)xykgQc!-iV1=rQh+b-E%sOj}/YAD^AP7"_a{5mbMrZlv2YEEU/aVU3q8QP6/??kBny+rw
Tb)vj0VW&&yHeY7pHZ";(^XX@SoaD6%~(r.S?b^)35+|OX](/pXLp-^;mU$(]CvrN%v)d(';break;case'el':$mc='&h_GfcsD),|?z""%8rk;,gX6
L(#;[o%I#Z
^t!u"fNli8s(LNZaGYl"K-mQTPqmspD%.Qo$V.6d%-8XHWjejqn"@uYs.[;i<TCQXq#d:0mfDxUK$M/utEj5uD2qG;@lTh9dC=Ev2
|JqImTd)#U:DXDM2FFt]%JqttQ[uKinl=3|@x,9VlE7P#wJ;ZAw65>jG(=lMwx$pj6wivj+YaYvs4XdynDZ5/nCf|k7^BVJNBjwtsDZX0V-E_b%3l0tJ5$"=*F@C*Je;Lx,#evqZbGb(stV-=febcB$rxP[/l@~Id9hB:(@D8=8n~;Knio@Y*x3ExSdmQ(mE>d/
}(+3_[v
]bYr>WAa9z)wYIdyT2EPrDMQ1;nUJ#3"=y)`wg.2#S2pM_YYjkt6a?noALNhkz(sJVouSA>GDfOgp.uX;tSpFt=;ZgJck)te@a4L|ycX/SFvv?P]^__CSc]dWt
B9S@V<FkKnU^X#i!/N!;D@C[nQ]79HtBuOJX.PmLM[82qsqvv.fcr}%HbCsU^Ej6wL/CLO3f0r#;ePE[<#?[AUNpdL=cQ1B&C&d[`8O_
+;`.
nh>1+#n,5b(-hp6IZgFBa<OQ(<pB@vesV/%81=4Zqh5~e4gqj+K_A1!}2cDMT1Ayl<FDf|Gwg_n!!j2$1YA#0Zo>u0l:Ci>p2W%@f7P
,^hG[D-D6JM_=@_NE.b=LXt,g;SMd6C@[$I&Q6`3Y(24qu9WeeJvdLkKd[n]_D`|1.bfVi
:rH5vi(,S:XW<!5,>Zt%[c
^2Ff!,*i(
/TF]tBMG
)Zp!tl>!o>J=;PukuV/a?wK)Z&;H:BAZI/_S2a[Rj0."g:<?/!=J,nL.A9*on^U:]N%y#=V_>_j!F8V_"u]%19)X
"|IE"e.8f..5&A;:ef-MQ#`}H$U_+=KNKYi{-;<ZPv0/!L)Bx0&3fbuh@[iNt!P5d}`1n"cjkMam77SQ(MtYUQlV[]WE,jLTt1.atd<_u`,KK>9MRuwq*,LbA"qcs8DtblCX.eiF0b^z2u>[%UXJqhBKb
g"DhY2`h@[ezEtu^D1)1/M2zX|3
@NflSkSq<ZS/cH>H42^L)MGzBhoj2yuU3hOZEPh9!TO[!FpvtO!lf*>xwx0k!;sGqVC9?rvOJWy)j7!!Gg8a?PwGG|V#yO&wJ]__g9baruFAiR^5QuM@;ffDKeWg*^u(fP>Z0EOzLDC
a8e<p9A),EmZTxm=8<["q3GGfEConq(y?tpB[5APp[^3T@R6yaG/7wSN!ilelo/MX{:x]G
J]KDj5}*s7wbkmyg$lNlD
g,%G?JU!=g/CXNI_3^(_^p:Z
8Bd3>7D-n)50mCM]M5.<$9!ON|>o-eof9COjNk!$*%`bEiY4[(EcTDdcL>qritnbJaURo,C+$Z.U5=<soxv[eTvuv[%8Q"k,$Gog`_NEHsO!$LVgrVSIQYIx#,V[A#V
Tr3|g!E;04v<K5h%!_v7$/EJ%;P2#9epOp-$Ap&>,0TD:D:MMlk|D.(<vp#RD#rp$xwK$9*KpzfE1#Aj46fzZ]*x2/%..|kO^x&4PROj_n6Ic`3Tf8o~+un|`n1;=JE$1#>EZ_n;TiE1ZOU2qN/WwV&<w)>~cx,LI4i*h9SbQKW"2)Tzj6dtKg/[:wUeku_?)q0e!GS~RO%23gJx2K&Dm$HvU8_H?}.c.][vL!cCk]e;x
kka.M#;1wT.$/i^VamiB*[X@N8btU[g[+,NbwYV?Ure!=S-I=):<
TS>!p*lYYaca$jZw&XQL?V.TLII*0W49(#Dh6iR.8#&kTXWXL
Xxz&F`-`y^xHOT.RahgWdK*;^S!3R,
1&Ms?3n~l3Q]Q};{K24`_|b#MJljt_v[9vsl<a7aS$F@X#e05;@xywo"wX@u/.+e8L)%=WX_K&I->.?Yizlq(Uh3vM!m24T!fT=8Ix%xpS.g1RYJmC?J4NZNf$9"/;)!i+43akw?YJFAZf4%s>@6T-I1UHC&
=o$)~pJ^g(q`Te?6/4v=}obS|Zn=B2z?+()[Oh?<d97q+kS7UUe7+"-^G.lyra@p>5anO;VDRACi7,tr4e>h+u<BT6?A{5k`glcZ>LD"WFYxFs[[wZA6O.#u>OD
-hDMzW2Pz[y90^FTw[=7IW"f>J"o3q/.KI342)dU>S}X*qSkmX~Qlp6%Ak$YOfIV?3IvrD[<y[c>3"V(Z[n4PAhW>TX-B0ON:ZL"->6]prYDk#]k$NYWSVI4X[n+BJKTC3$4[t;5#N?+e25v>Oun%N7
g
f1nc{uu6XcsY9^W5`b`Q`xO>v;{ol6_k:GC[w"[r"4O6a?~*VH^aN[}Hrd[w5Jp^jk2Q{rk
rPl7C>h&pVi^hQ`@HF@[nehphrevf<d`"q/h(I5=
JeMgLxQCWMvN6g@#OBKmf[m7hY[DOw)`)gmgFUPtOu-G-
:*$benb{m|QdO0jD^yd~&I<%D.0G./!EDpVG8o&c4zT0B#FmH?t%<!-33vns1_c"<N!:W"of2B-6SLeUICu5A2*BOP`
U9D|r;]]GAW~jiO.pnirM[usPX["=G&HRB!IHfX:%o)D#-dIYR#fEmIB%3@7ZO3|7;h5VRQsRaSoEv10`KG<Y2ywg9Lp-xPv*$l&$&N1DCoB:-@,D8h5aK8{L
9A`_=IApQ<2CZD<J%<4:>PS$&IY6JcS=oJ<Y89aaY+oHBd(y!aiqRA0W^:;nYIPg)(;7dtFv.lgrB-cO5Wh)J}({lK2<ohIj"&DC%S6&rNaMjR
P8IEQ>"62y2x!&zH+c^;[#8csI!l$@_od5I-Z4zf/nTWtN{$U""gJoOu0JM0(/o2H)CVbEH:1]3%ouzD1bdFq#vC^1t;+0LLnX8[n%7b(ksA9P`"^UB*?_Rp1qrM<+_mVBTC)l%jlHDj"SQ1.^zPOeu
A_wm"4g1OZVI>.Zdogo9:.T"V`FCI<yA?SA/SV&;qaB)~#dglANG>?2>*Rnv1*{n28l#q4q/Fn,,BU+yrxJ<VKQ@sm5W[-}.JvzNC*C;bkZ.SN?Gu-MA|S_7[&TZW1#8CLnG<d]sMBAi3KSae`1N_9%-3_~$<VP&k`^r~6D*_3RvW]7:z.SR@#V-SQKM`/(c+"~"Zu8KXlf$J9r2/1<$cXb/r@~`:VfZrc3j^,4!Fw]/gvQY,s_x45[Q>q0=34r$9![j{=zO0$8<
!]St94k5FsB?N^QX%DQT.M`,,1,)?-/MtXr/7($N6k@olk"I@C)vtKZcaN9wZt=B85^Y=`-#.N[gx~-t.Wp(:`&2QIEs4{EcRqv|HIxOEO$B6CU70WHKo=?"PEbd4}R(nbxN]w9P]VQl`"^rBZkk"whEcnM*"Uiu%J5]u?fGR
MOIz1S%"$jS_4WghD",H@.TL-7h9Mf)n:!8}@5TMJ~hzrIs`48mcV<@e(##&N]R]qq5m8d6Tt7(ts0)o]yXwb0),<q$9I1h
0pq`4uYca)v`$<.LW&Q~piu]5A>rr:R8$1J9XFqW;iQOm<Fh?S[-NlhOf@D=Yh"R?~wDyQI0sWl(7Mt"nnJ&0p:2SksM.*=Z:DV^iiUIISi}72q"GXYs%Rcq?)S_r1A+Vz2uP<!/9*PvI,kR@!FG&g[bKgiXs|U
`*@s7Qtg,DiU3Hd_8e0:m!_zt,3{f)YqHM`7CL%i1l(^Rs,)(`>/y7Dj7]5YElW9G)3w0e
gKz^i(g&F]{.zr[SiL<@~TK(oX
H<B,F9K0+HATf)-9XBX1e-J%,6]uX^JSM$`EB6Wrvc@A^?E"&VHaUi[*8(`exKHt$BmU$A,EiJ**?dJv*Vo4>zZ@L/PoI+
cp1Lp#CPgCl0yGN3#qk({1yL~VxV))Ei.i)^4m6OhE^.%XVM#l"Dc,FKH22RgdWsJSNJt#NKV7W#1)Ot"
9kokD`lSzd&k/o:6JD2<;cpZb.]*X[=^#de-wZ!_c>
;!i_2HZ{jpR3iv=z<}D@,zU&w^97R3QT8@VZx`@|:ReXT_Zx0=dC-ykEXu-l3C*TAuijrV&=cqy(y@f5&_0eKi/&s5bnFPr%mviS7vYOx%?P!~GYHXtwCuW!N=6s>)EvaA+8*s*0J:z)ilB&QChS5>I[[x"Xl=9L]^**X9GG]sT$Uz6~RRRil[Eh(IZETN[Wm*@ZpgI;a-[$.&"6sh5wEzOv#O]P5`K%xNk_:|7>_@do>AP}Oj=-r~52q^@NZ2$@UZ0b>RY0,8^R^C/o-U;bf@l~0x3=tf%Z%L/y>1,BSo_s%fecVZny8s2ZAe6_9J4.b)=gxCRxF18W>s.8Ia=|rFP8g|6}aG_<az.&.HBVkiuG5&Ue0#Qj"Q.w[y9a;iE`_LZ6Soom:o2aW39v:!8NPSGISyKP-:0(*@@1=BACW,#p`rgxVOaAbIOxUi$`=Bys:9BlKW_n"MHS"+xnI$f=8}t~9-rcwsKuP;xJ5uuquDoTfRi,b|Kqs0vX>SH;XzoOEL4svgv$X)?y2LMOJAK2WRsvP%1l5(^[`53u[K$X1nFxCs%)#mbl&P&}Vvi-L-p>,;b-Rl;Rj.)HGj5xuIVS2*8lNkovv*@>f!RmV/4="vke]|*0-2HtMAQIG>"8eW$Cn`>}=m:
dt`F#{M5ScmtJD`Ui2HoXxXu`7MWB0U>MX9da58bHaIK+2;6YQi3K:G0>ZEivaKjGlT_WHG/GgbtJ1xqP&
1giE]Ck"t>)E[pU.U[x6^N]&~#R6RY2R@.vA</3Y}Na/Gw2AYXX1;<*7E2jbE]o
}L7=wJXIe!h6
qD$w])tm>>7YFVeT,RMH[D6/[Xh*%,JrS$`fj>QO<Kd~kTQ.i)qXege=8P.ML}WB!%w}sC*:"N?;nHWNhb
9EY/6]&<BL:&kFJdm>D+zAZtS0=vnAT&;M2cmRQQO+aXsG:-}YE[gc
0BgFBLvO8Lrk<o.MCMN@q|0!KyuwP+Y4@6m}5h)tY=P;OSb14;+YeW_:++@ec.@j-{nty*Ibq#mB/0Pcg(JY"`<~b*L&v]vBf~Tp2#Ul)uT,KK8>k0n5Jcl?&W"I2Ec9
55<"O
!xYH=Q800I&%BG,VHD?HXE{OBF(&-4t_sQx%j36Bb41>c3SCai~]Wc?!lBx`HikV0gdA3V1cU4tDck+&<.1Ti)BK"yh.<sbLXM"m[&VC@%Abrbja]9b*AKEk/N|*&mN;.a$jv<VB/]qv}6,bnnqa.f^m+ypPX9fV^%Hx2GV>PG[g`x;QnB28[itynZ!y|2Af_B;)jyDx|hqt14-11bqG3<YWf@s#7d]2|QDt%Bi`kAvP~K@+Xae@;9v-&czKfVp!v
KXbKr-ww%o=xP47VSIP>P?CQ&a9YA_DYo2D2fGO!59z,FEw3MfM5Z5El:qwb1%}jP_M/}6x&mK+@7:/dG#|0d$^U8MWOP[|K/F?H@GR5M`z.0ffrwPBd7O}89ap"ATpl!1`@shEu%^368bz%UTtarH2c.AIbFE2#DXgA=^kOY>"G;^ZVo846
s0Xz,TCQZTm@o^DT
SqDpk!yR9I+a$Z_^-H=BUh!tqp+sK^GhMBHis/,eF:J-v(*%(k[XA%wHH9/v97bd[ncsAK0L;?fK6TZXsfcwO$&HCNNrHqBD@7|b?)(/v:i48kx1$h{Tr7mUg"+:`gOIZUrIz8x2,u/4%iuAY0w[.+usxVQ8xqz">iApqcRt3p}k+ar+>tCqjQi3yBPO}CScr;na+D?(J&vNvgH,Pl%U*J&o"kI:05ai*#pxp7,/c4Y>eNGsgm[A)q!;DpzA7ucEMsTx^SE';break;case'en':$mc='(X/&Sbsmt,z?z"**C3O.YDV<l)c1vPdgt77mA;(OUHPNCk5U~H*>SSzj9XnuPI"IHfR*[Ps+2Tq)4H7l$[_L.Z7,bCOVxYAVXMkL"H8peaf,OP/2/msrjXyS4?Tj#*5Y[
nec]&gI[h^TDJZqD3Xq90k#bQZmFx
DCQ^ag1
l,)R;Fjm+#>n1A<1m2q2F?D0n`)k#jO9Cn2G<BjMdaXD7kk
|`]hsnGjS[GVSn-a8yDB7x@<VZ}cI<?Qsv}!M>ofVw>b
$G78
gMLn34D/PhotYMA&u]^.pGWf*rDx~9-<dA=^C$7w$Cyn.olK*Cx+AiGQbt!MsAj)L4=T*v6a|e9ALs8@1$SeH^|j*GVCjpTbS^@.Xbk4iMF+K"p3JDXkmjOv>0Ap0l2IpF~[:*$C@QH>[&`X3G|lgT1ko$nkS4^Y`61;am/wSR}_R_cjhp(&yqF97vo!i<}H%LQ/{>=aUE/?g_IGHS>AZ=XA@e9.-_9g.;caZk3+P:dnY?6Xs[]Aq@[TrJQqi:MiUVVLqK7rlb"A&eLi|f*iNn6$os{?ST;Bc<8wZ[&Ed9rdPk8Bjkb?88O#
+d&A&0P7BTW<1|W=H/bVnQ>@1.
<,CY`$#="a$<z`w!sjvVWc>P[2H"m*xn)TR]%rHQywfIoutT?9"jFvy[3-kD95jkyeR4_70Pf`TM|S
C>,Y%zXn9=$jJ`$_iTW|=#j1Y|VTr51f5j>WmqXGF8w;[093u$)/f"*t&byc^6^];_j:b[^?[Y273e&}sa<Ed]<k$`s`/{hmRQCcsD;hV8l/

-eg)VrvFVu^SqZRUj7JNi)dS4%1y&Ecb$?Jh2~wY+:`/X)l=Whx"!9Da8yi>mg$D,[^NrdRV?cDCDWv=!!K
ZsGXIpp4vPHR=T7OTC$/
PV`Yd)z?nkNDsA*oVgG#Pla/T>&JTsU",lv>~Ced4aCFBq+)K5HtZ.e8C4-+NfU/I6.87xC-SX;!%]/3!b!?$bX
sy?bI_bMix@BMXLnH:k]YAW"Ap]b_dAy6L>5+
=LwNce3g;Z)<kqDML
~JABkTN4VL"d{#T6ZkLG)$yI<M*h
7|IQ8v`;d]o:7j?s2J,"YNI._u+ZD21qLvf39byfWg8#y-GPz(A=yEyFHl1yPzQg6N#%_@<Q1G,Z/F_D#
PeC5"qcd`489=RHEQ{Q+@Wt-R:#`r}S(#[icQ&3Io|!N9
4@"!)}x8CXO,)"#>OA0/YrCpTxDTU1DHr_[H"-@xS^.)l:L4fD4}DiTy/ZcIUMO7=^[pk`#,6UiwdJZ7#=7?0VUzJ*Lh@Rhn6]KNf=sA-FM_
J7xrn!2U<KE!X.iel1Feo@ag+/>6o8BNA7StGMcT@8#qAK=ZCxS<PAp<Pn
vO&ud%p/pu.<D2mgRTSff.(yp<Id8oxGf6HSahxXlQAe#-<
I(o+lqID%R.gjJEIDXCtaG5jm6IIh1C*OW1W#Wsy=(YI=?A`;qIe%EYNEFlLM:>-YUw:S$XBT.8{MW-hW]8D11ZA)D%=Me:evb7,6dC6!X(i8p,5izvvLzi.vIKgWL.{_SCi>X4UQ&px4][aD];:PDA@Q[SED&-TPm+:KN2(;COnp|ATb)MPXfe@o9B2s)3&OPk%F/VR]x]s8M)[r*+~`WX2-rDc/y:"Swu(_s.5R&*esf>U^ut1Clt>47jR&O@7:DRoadbEQgIp6mk~"EBsU&>Go5GX;E7>%)!3HR0LYp0nm|/j36-So!CjQMn2pM]{na2a3N_/!&Q"4Ya<ApDpC/W6p8HliH:.4YLV-Pk
WZk~oPFV61vpSi#~p+tt&x+_SEa*0J]<6d4rc[0qK}X!.@q2d^/~?uU49)E05jUq!q`ELf:O2acb9W[ddZKZ9N9_wC2SIT]Pj"k9wF!V$s&_ba80F12Dy3J}Y[#GBH>X,;r9t8q7X^,%E5aDR3>#&;<I)]pJHP##pn^vE-+^n)(`2ppF-6Pk29jN5s4?YS.{)!1A?gfQ:ByBA6W:rUUgV[mML
,E:"a
)pU$pn@)MiA+:psL[9=]:.
$?t4:PAf~p`g6/6B5d_@Loi4F>"OLN!r
IJ1ye19o=IX*af^+txF&(DO0-,Sc)8U5aTw(h{Z
gR&2?_sUQmy%
iUr$BI;b3,D%}XMiUQ?w[0
vjvLsCH&^z#I5bHmA]vHOy*Xwvq=Art="q*FWXys^T>:T{-]ihbtF+UVC.ZUjQ1(/6JeG9;;)3GIK3
D9$;fQ=wlKqp^!nZ@n"H[k,Q4%jR"yE4SDl/g=Ii*/_G+&|"IqP-T#NZ+H,hf1Ke
S2!xMw6>DM+.HW?uQ?XeW>3t+Ic`qLTq!ziXr=g#^B
t?f=mIGdp3!xn1)7)ryKqtkjnqq9xHECyww=.@RC&GXDa=iymbCDJ7p*[]
W]Z}V*LpqxIg@Y#t%<;ULeO{<xhPn46[=i+ox81>*sbaW#:LD/J]#B4*7|,lBY8xAh(3
N0Qq?tm**xs=
eoWR!L5gB3Hdy`l,MYB}$WXv+Z:pN-SI(h#YIGr-bxZ$^u,LT,?,<3`XT#XC"v!`*+annLLq
sa2=
@qLaj:/%,"q*N~Aas6CN0>g-mN%77H:lL2G@6|ZU%x
V]OT>R)W,9igj=IIFZE"3:
qX*V$"bn>l/m87H7_#M$dX4K9DVLtErxwz6l_-DU
8E!46]aP0W
gY-`g:ZJ-)[UU:Pdu+PX8o0#T8@?[9w|uM+gl1=UMYO3+}ooJzNEmGFIX47hHM5dNR0igiK~q1I"a,vJOh2IE;wc7o2u&9`hpQi=E
fdz)Rw4X
$b_oS0bY%R"fu[)+j^*[thK$^C/]Zm-#
5ww$+2`s1*btX>?f2b(l*Gw@/O8{I>91S/j7A/<7[
)2CrrkaIKJ0f,Tx!uRJDff&w47U!g!Obkwi5xDuJ%wL0$ZZCX/X{wnt#3KWdnrVji
@it5uQC,X_X*g
X}EGM?$4qo?-MOPa!SyM]7wmT|P[[rPGIVaOv_!OykviX29"ghV3uLRq4mmWlRIwRY`x1Z)z?3##HJ!v;r5,xs;rA^EBeZH&VHLzqqnEFYANU"J!I;I9VvcnYdcK6njNFO5C,E=oMqGI;X+pOsl@-uKtV1M(6x1?4SO*yr
mxSu=G/GgUna@X+WZZ)$G/N`9B]0k(GXAFq[Z$6w:oNrJ>Pc
2L=vuB7@p&fL?YW>f&)$D.g*J?u:c))n$LrNRi^RBB';break;case'es':$mc='&`G@qbPDI*60
Y,N/p%$ugP:FjVgC@,nVA09Zs}F_q#g+v@v07%/NRgd[eO71_2tD-^(`bbV`wn?Dt.;3`D(4(PsB4SW
b9l=
[=Oq;&qeAHo/{qNrg+"kw?k.q^PaN:Jg90>j1x]wvSj,=_!?En`y1/-P|oR
!7+j8RGjO1
`%0o:?n7JODQJ<GmD,>bkGnY_Xb^?XhA/w^Da3i3ko3+g>Ir]H*DRWv}s
o~()6lVg16a|1XnS$WF;_RW.m8B$@w3rrV5B&S5E&}YtuOj)`Sd!5B4ycr[L4qhnIeWpXke;Zy+Z2?T-b>n-W[[?M9!&wb8SQc6U4R_Wnm
eLxXo5;quun9@Yp0Xkaks&/Y`-<4wGOV{+(c+gFG^hYhYg>H%F"y2m*cpm~(>,X[$s^H%!Or<-abMVjw#KGO~sCAYb{4rh%"5q@xs08]UnNA9s%eo`s#n/@B=X*bE2hMy^h+N:P6*uZ4pa,&A.,2@^[H9%wi*a1`>RSW6&D@L]|0MB*F@894*>d9RG[jNd1glCJud[~rW+L`:kqxoE}ca_hw4
=3c4mXIAJ(Kwk`RJ}uRNpA(yY)E.F3UGW/zG#]3t#pEB$;hHv!hg8C5Zl>=bcxm/`;=EPCTU=m=ADu)3YbF:ADKpdg-:QHS]|ZvRxkFBI"QT{>`yj7h!J<3p20vLw*[(~n9_3"|G,Mc.AXL6.^aN1S2WE0eMmKx@1;`Mau/57748"i:3Xvoo)`%b=?<L9IX"EnXAE=?7FwPUlcj;0WG;!:_O-cO
f]Ht)TC3aPz27lH@,7|lQB=Ealvir;aX*xzk8G==+Sjhm3X6#FAp:V6CT;%FpW6ymFL9>uZP5=8R49Dt+J{;|Rsj;7:Cz:%@#s&RFyj0WX!A#qDvXbybkH1slH_%)n-ko_4LZ_lu"W";C0Beb_/puLR<kLgKV;%:}W/5~
>PIJCjk=C]q0/,DVkL;;{/joi60U6VSPYn]k:voO~>[BuYR4*MLFLH6nN!7DBE11HVCsgk8JsC}t%eyH:y!qwcTV]IVS[H-+vw}EBpTM3pjD7R*?[7qf+BsUiLxgdREEJ1eEOgM^;-~RwWt5nkf`;Y8;=O]wtZ6tpJ
pI3%6j.5b2,t-oqhc6fY=&9TKoes/D
F[$!X1ml{3zhC_d_X;~5ntU?qlL?YDtG>c{0p8>dp?ePlwHgG$@b^c6LK^|-?)&<n,5L+DK$arRo8+?7qpO@9F{Vns^C10Q58&htHkXmD-bu?$!Iu3JQrdfp<i3hv"gde*eNG,FC$vJ[(Qy>CdSAEgDTu;=Idubt>wZ_^5gkc-7;`9We=i%Df73>>F5U_;Q(!.tk.;</}b.y<6eZ<&~[s%p)OH-DHj4PjE#!vDj
73#Pqx@-EY_/7P~LYNg4i%)a1vk"b:9*-RG!1
yUd$i4*95yW:
wpm1UKw;_%]@Nv9rC&[<ZFIF^1pBL=y$Bp<C3H!ByFbtSyqV)Mvum7]/@t]+^W#<C$vFk/MxADo.$YVVhzy|5Iq:[[R!P
$
@f8MRJKo_y8~;LL5LTS1/X_H%
L"wewjThn#MfGzG991`Y*,kIt5WTP~b1E,"^T[
;(X>j,iYLU8>QeZ9Cl&_D9=qB0QsuPerd7zemjq!uX=m{1X+Ax>aH*ot5=w@dJ6"%i@IBNVEeqVs-jpyh6i:7:vsd3.uEYMA<pyDkYR#0eB)""$<{Cj
JOKH@Db%V904
hqP]EK.vVApc.HN(2^[+
D@~3)(<GTPh%4<fbuc>F50qPTSzQW>j8u*>pfg}Lz_/"v_r!KlMe9CW"D#oT0RP
tN,+633Q&6t^b=`54H4Zbac1~<IasZo4ah<@WYl,Zw~9NHee-C:@L5NWQIy1g5|R;f,.&/&b|"t76hcwE+rx8?S9s&c[3E7Ob&S7/!c.GqJn/_h:~t4A?(DC9J~:MkMWeI+:!>K7-.BvQZbW?"{ns*ib>y|W]-_/9+:=6SwwjX9_w8nvF-)U;P
;nZBZ0+44WX#f2N#oQ=jtY/=BQ[}ab?GQNb]GaTa@gMbrthH9(rK0e*2:Im*a~t~RabRf.OKn^%eLIDgT0.1E3&a-T)3]FaC`(;S&>.TBwxQ@}/I"V
[g4@^?%?o0g<c/"=qF)n(h<G8mi%mY%HcE!DKS21sj]$ik~a@7@]@FX^1q@3b&D?^":.$dNf6o*0kefN"cs;0x`g"kx9vk}0.;zIY+h0O=S,I68qJJbZ_#%^lOi1#m.
9I&?t;&aB).]}OEfR2j%mPRm(xWqgF~XDDAs!hF1*1*iFRhe4RDHnT6FDgX,ujUfzvYh/3~g
V4"iLk%2I"oA(.!An*wPvG;V$@^1"e]!^CgF#]U=K#guwnM[BNcZ=z0~soGH,r;Q.O`k>d2qA&[5"`vN3vZoc-A{cAl]AWOvM}]/an21NK6|MaFoufGNPH`/F{en%3m,W5_euw$:9^S56,c{$_B5/SuOcJ]09#J<K(G&8p2fy7DFfSsY/[(@hlVYuUHU_9:jfqq<g,
3%d8<O!L=xvGqUei:K<DvrR@HB3S$L$YU&CC!^"[Fyo9"&eGNPq:t8hh_xyjq#X2E*wT;4er(/We_ho;P]xw{[@MDxk!o/T+.m$,HNxfI,fH%&l#Tv<P7gaWj.B8p5RfrJ<>oTo>yjSM/295[oo2Ga+;H*/ewQlTf3j1,.lhNln2/2@eu$O:3:i4b^|n[:8<&fCV=:
-R>~M@-mg
_D748I&>@e5y&tuDm?4uvI5WK_Z9nM;[0LkxkU#1D[=qg
ibBl]qn<%>=;7XEkOT9m<_]?*O
$4Q)"R6ajO421]vuFLA)!Qi-%/}2EfW1RO[xXX8QY
;;=gYR2<gO}R4l:GqQ--m<Sll#K`@8#=LiTjq?4N9l)ymvVCGYgcNYAAJxr:o.o+v]"&P
#FI/7:@%xQj4817=xfTupT"<?h|0!P&EROM0/sG;7[v`8e(;45Eo@LQmCgQ04QdOJ%VH~SI+u%lL_`.bQb*hUW1AJgz#l-I<8FLst:7.[u?@;>@I3VXe"yv
LX!#Y@&thf[dnW*"?PY
]b`0>^X.J;KXrJPmv:9mwsWoy`u`*H!d5vW(|=AToGis7!lD[eP,Q<O#<q*TD^
Q^Ie0"4yy`mQT%K&.-(~Io,xhO_#A9$b6GK^n>?@S02d@}Bt"/5v.dAc%svbS2ZuAK4#W_1Wd)(k/8e}1~L>SY,>3.]D:GM=c=82OU
O-a<>NfB`9L2+e!
vqX??cOGPDAe
p%eR&n%rn!GuU[UoLSm:Z&I)(wW>rg1JPfk*jd+.^b1X!_bp>GYH#91^E8:I>#kO+bE+h=Wwg*.U9Hp?Plb6lS&"(kGsuhOQ#Q],+ri-K3f$"[ugLi6pY_R(y{o4oNdKIzET0u0$%.b=!&9~O6g[$m@C:lBR?c6sZkJVTpl3(n,~n6>c7:Ne^R/FR>Uo3<hTlRuFb:S}V9AQl=iWQ<fB_I182__}XX
~4alF8{ZQ)7.oa.8bY+
7r&;}ot!3W&ek[D>9mZ0xde@~%/;wF7#:ZJ"{9eS%k7"Opu0W%oME
@7W8d?E3[;afMW0pK!:%2HN8/_aEOPvB,j+>69oaGg=$|j(#9P5.[u)4)3f-e^HshY<B|pC2VRFC[f~5xT.fJ><#CWx38
&-}N4`afV4gR5_cOcV=;?LKNmQ`UxW`gDhm8jS5h[n&Q%Oe1
q:nG!@qV[l){E}wg8XhjtHhJY/<xYYT|CMMSP9=S?JNf%ci|
2@vPdd:>y3?*]rzQtNz>(3|b:39%(i!q+=[Uc$*
WJ|Lb("x9j{5Bg6nQYr[aOz#
"=ZGf}gR
v63jQac#"=%2iD4d-dkUSCS&%PUH
1u
|2i6:Hr%VY=G7w<^gGqH9urwD&0c+,oZTD3Z%j56p7A5PD<:#O9)N.oTtSrFI6{S{$[":[dUM.-4[505UbcksxptTdm2EY+/&c&O]#*2Yd{fLP*:lb3p<G/!91C,+Z7Y)F,U134lJ+!8-i!*?RdOPCL"
$_DViJ-aZ[c50L,25FgTO0"V:#NN=.b@j:)ecaehC}(qR&q4"%=ZLHvN%8cB/zX}<U"!<0<Ry0E>icwvuVH?BTd=X=TQlVvi:UHw.8cL/y[BI{Rq7Cq(L5>sb?6wIa"Mjr=~4C2
Ab1[rpGh%I0h";q06L
%<qILty#2$&I4:t23v;`JcGrhVOT8IhP
^1e^d<xeiX>=.++<$cS)H*t4m?Ar;l`yNzv<yvV5</1Ox9$odsd$JW7QS19,Hr7&[>#ZwaOH]kk[wEf},71_+q3Pg:C_"x7:%3@:T,[%Qc)?>rLt<d.`&Qankm*pNJ1biaanb)w+-#[XK|V#Zp)68wC/t
)kva_k,sw|Mz/JBL3mVoSUnGjq6j!k=ELWb(?~ji@[tz@cR)jm6T(cPM_=o
sC?$g3(&+Y8yk;Es%96A#Zd&_|luU*hQj_gM,7K
OsC&TSb:6!Y=@kp>mRvU#{CkU^lFTPW&x)Sk:`&ZGt8l7Y"SG4FAz$o>9Br2S;*1:H<P0B7Gp)+LpT"X7E0a>qFzngru
mK(TH>CM8+>VpX[9~JRFnkXZ/1xxbHcv$#`m/Q4H[^>B)gI:)#iJI@WckBN6Q;^D$9Z/;J]?IR%d:5?nIY&Iw.$5@EBjP-sG7pq"RR$ajsm
:=hdOz!**';break;case'et':$mc='+R]ATbOWbEg0TSY$t4e1c[8Jp/U&TJe[G;<vg5(B[RN%wlSFTTT_MY2f*>sRaS_v?K5bZDE^=b~t*-mJ7J*",3|)5
IPhy"sYyu[-rN70%[
jr/Luj[LTr@`1BUG^B5*Y,cO?3)p9a3__(Ji,fi?]^fsSTMBu1]`I3?l!F*?vG&>pe^M(44Jdx?;dFHk0GJR?m0PiH>s`D2h#G5
}4=uBiQ%@p-B}63]
Pj^)ok0d?uJn_f_GlvB*yyx-,tc/lk*xyf5J$QAvByrBpdL/7Pcpj&KtY}[<s)nKPPsah(
MnN7s4xx~g$nBy%E~xD+akMhNjBF&6uZ^`AvK^<Q3Vt=dj%8zky*<@m.~jTv8TWuz=BIgEf+SrNc^2Gf8W:JPK.^ah"=.T1
O0bfKaA
goVt3?Vh_AAm3G.OQnD1*Gr=+:X_fv
m>_KuxD$P"4~KP/t`yW4TuWI5fA?IR;L;s,/cJ..X*[4jwD6Job<1J:RU|R
2i:ACW1;*iZ0r4f0U!N}llG!`GYB<QJ|Ri!XxSNyg9mpNfI.vPS)vq]YT)6f*T@(tj-_NP63DCV43T/jks_8+Iyb4B]gJw-Ob8EE
~@)Ri%R6iW
bX/RS=9n>X%
;"pEbh41E%muQOYlof?Es0A5w$OT`z_&_nJcNAjxbWp;jVYesbDR8sVBL~.E<hja_*Tok]OW9h7R>SXdWa8C#l;8Ei^QF|0@ewUS
U%
ft^u0v,0AZg$jLHRn0JC
np#4WR(>Lz!@!IT[Lnm5HCCns3iFaPis3Iuvq_5e_l:P$uvvjdnSRQ8iDF7Z_$o:qpKL>Bd%f8d/{Qq)|B-R.MYNwP#s+8+H2`2w:Y&UbcgIZ2c=1"5lc)F`gvwLKMVAb[()(+g.[cZpz0bGzG*mLgrB+q
@gu<F$ZsUlUk]L^r*mXg?16jh#YApA]``J@*+jTR)M<R^rTbv"c7KM^CtpUEc{r(#w(q*uDrD[4nyZ[{3t_tl~r<Q&v@k>,-[6byCrAZE0<%%6gNEP+Mu.5gAwjFMm,,GKSf
Tf{U<c6WD#nBv>HLfrD!GL9a`GYF=Uc/E0H3jA=o*cAk"ly)&odF&?y>cssbxCmK/A7n0jVH5
ew;[zZo/4@YY~(cx>4+Z->%qhXoq|UN.qS)h"xmehH@Tn0~`xYTmbl
b`%ohgAdex0kS/u>wcshuBiQkp
m6MTX+v,}$g0mJ59=$~<UMXm~emyoM}s<hzGGSF0bL&M;MK<k(I[jSGu+y#Ze7WTYg}piof`::=ZYr[0yWl1P@5pwWd<|NIXob8asUM3_iSl/G9@J#*hY>Gz)JXl6AlVW%RpvJGJnf"ky
H@bude#m6qj*MEIG5w&%{X-tb;o1Z5L.!?Av<1^=_6GF01;C^B:Yibrc&c];z5_N1J6.7`vM^iH_N8G`qj4xo$G*Q0vZ+z#$9Z1:a/Lnyvhe:BwH0BAWljpe{!y1.5j=3W")m;
q
,9v2]Mrg&
_0Z,aCbu%JPXJ{$8)?v-9%F&qivAT3,I@:>gALV8A"2vl`p[TE%-BTS9Gb*KpJWp
hZYHL:14&]=0[TRKOcQF|j@.QY@(-:Jb6#_T2Za5[r)51:eSP>sS~FxxT/SKURMMSV=D5E~O[XM`h[#V&cSYNW{s$^L6%S-fdDQkVJG
xs.D@d@D/w9^Xi2&0rjf(Y*4&"T"&vqJH&}1L1FB;0vf"_:UeCNGZ=NN00cA{`89g3e5iU2*;)`-s5&"eB65Ds<5uEXi<@3CAH]j
gR#$CJ"FH<qqbZ3#o9XG]$5O.#>L#LDF]82)C{#!8KKQ9stlHl!;b!ly/A2k.+xC,VH_HiU;+|c1Bl52itq(voMBp-D#rs)x_8ykL
;i.J@QYt09r(3"x!^-B(p=!R7y%FX2pAkR@r,>.([yNZ%lQJra!j3[TZxj9Eyx7_ygT7cu2&jW(}>ruFUAigp{R6E
JX#5TMAD_YZk=*)XjOFWEF:9Emrt<|&]dun4W>FVViDSV,q&%O$i1Sb<8d,VN965@W>]#s"mGvc1s,N0M<6"w@j)^sKe+zN(ITF_2Og,"64<S12>yTMJ4,41.v8e"W%:l_MC24rF9Gwk&3E#TJ2}$z)I
MU0g@(pGC<1FtH;7C?
Z^a2ZT+6c]h<Q?]]0W9gV`3BQEYpEqn43pbB.qF)_2sS8wumO.[MLy%6^f3UT,S&OQnz?EFx?;S|a2uYntSFLauVlL*3J+BQE55f6~m6D#Yj&$GMNdPaQ$,/HO$Xvm@D.BV.T+ckcIO&eakV3Ps)NlBvnydHY.r
"D!:BRjXq(Q*gY9S87bf_+KgsWnw_0
TS}
22%K4g/5bO8ZY6nxS2j4YFK+Iq`L~B_V]l0>5ImuaQ&3"?V<0;pju#Qy6nmj&Ki*qJ{5WqLkFbvxtvJi3FPdn_Xgsf>CZ&$]EV{DH.l,R,H0p<#-^"UaieF)R[MAtbPQ.Sfl{^1U
&r^<*fiW;D"
<lb9fYmz>-U&_lx66*v=K9_22&?!0?W(rUNfDzw4&Fb6&pfu<-1V
fQd;KGN@G0R!arn,mO]p`7`VCs+G?g:Ur`l=+x,Hxho-F!U1D)(q13}2BM}6/!qTy<CqP&NDYB^6lL$m{p8%KZjC<YEZ+>6$*AY-Tjx==.1p=&B0ah,U"^^n9"7u;Db?*yJL?4USLdGg-vlC_LGd77JTpe84gf,VB_s0(N:1aTs[e/N*Ch
C6/%-#Kl$}3D`?xR2)yE"N2yd_=][`6aoN0.EOL8fB*nN/MLHS<x*,"xWh5?0L:Hth<t1qBbQ46v)c<=V*ED1P0p,
f:CW>yp4Z|(2X,Q`n$E;-8a|!Q]n%DhuL7]w6IZUiCAG9c2ne;JwX@ZuO^o}PfO9AdrL"EX"[rf|>pWh@)R-z"q,UL4$u";b)zFw%kp?gXyPUJ%Ft4E$U,TdIk;)0V6ngE
8Jinyl2xAgc>B90Va=j;t?)#GV@-wKM^|&z-"ngs}9FCM({aR!!H80A[7`&VGGq$id_dh&8a^XI$Z6Pl_*H[U"Qw&Y_0Mns7lswVN&$PPsz-/goCCFM/ce"%""cIW[]Ak,VJ#%y&WRo`pJM6$tW5rnS]<L`FV_HN|C?gY/?u~rx/K22HEn6cQwJQtt?sw:R16&&FP_~YVAfm/<8;7"So1F2;}4HuGJk_Iu+h&mj>E/(#ebUOatV9##[O
>sBMJkCX5qy;;z^6u!P>H`OhaEo7`@9vYO-.lPx:RpivpcesRkDv&jK64aads<lKHT=KQI[W^FX1;C(4n*F?56?kMCY0
6+7oK"_t{K0T$5Nd_WJD7ig_Va{-,eH.9N~LFjJd[xNRqO(HCw4C
<{PD)`,g^2Sb[9?;)X1^M9wA1$+;W1o]#*)k]c9jD|wYuk2i*EL$sgy2FhI~RTrDMqVafYqV]9PT9@Tvr"31.=xqpp2gY"/Ae63lho8JDp%mPy:r%=+:/&`|V4>d:>cldz`9TJ`.tKiNC()p!6c?/80gSVXS;lF+Pa+0ylgI"
h?,#UnA_*}hT(p,Q0d)>]xLy<68wiwRg4e,~?<Tw24fr!/)PrCO(b(*N,*^v1k(-*@<NtI2}EB<"%Bv?XlpZOK@~V!iV&|Kwa5;Vnq:Iwu+m)%uno/.eJ5y4+5;<`ic;F*XxN]3<6~Xx/(b36wQGQ2B[&=q-1<y:=d%gC~vhez@z(dsRlK:sY!fubzCW2gifZWUnwcd-eJ5ja1mb&MnxesB{5~VQ8m5te+ymZ0D9k0%$d5@z?P"%CMy,p+Oy+XtKp1o+8)h/ph<}[3gM4Skm!5J`XGAV%UF0Y[4o2VSK1vU_RUM)">d!
m.unSNQ$dt+V^/)x_cIJu_U2|-o9fCv)hlesUN.;/pxy{a%-ab{shYwZV]sHI"b6/nIj{vN7Pt&7clQ##Y#b#IvMUq{SYp|4tTDD,X~9tG1yTx}Z^"p_e7.yLC~U*/F@ggZk*#C$dRVC(U7oy$&:WOrw7-,4%=C#)i`jtKxswtwu:SJYm5K&Cbvo=BRLXJ9s#hYgybeJ
97[[x.t!&LtC_YT_-ZnW=qMu!;55I34&I:VOq@vA5atfYfcA"g><#$h}Iv?ROf;QNBob.&HA"+h-4@"%%b/0b*C4kfi6R;lM@#l*Y%
4)B[tq=?e4+"DYGFdXBb1!-uK]OxXuGb.RZmQ;9Ws8HgqhN!.(N7MuMPPHmSv:6p9L](+.UE@k0mhqs@$,a)Z]}.!XXMzaRj,`qIIwO"7,n!BQZ>X$UF.COop@P+!OqI{Xc%fs#%7R`D;Eb**9lCJG5a=DL6W%*IRf~[8fY/wcO5d4?XkfO_tOQRsF5+{9{lxJIaR/|
DMR2<u6"|W(s.0E(097y.S#O}LFu|.j*#stRS#!5B6jt7IH?h+{q1L,Zapu6>a_1~(%5Q;xC}<%Bfr@glHDZ$Vn+TlS*
ZA8F*U[I)`KLg6e?L.K,n]aG5Co/<n$kS]WX#W5^b!b>W<:/)&B0x@cI
6yco;h2frf2(Ema%l$tMs)Zt&-E;PSV:FAswfjF8*M|`,r"$7G2hQqK!m:HGB^FA2I$5oy[LOyBEw#UpNlNk$eXd;8O$H.9M{_DtQ<1I[!^p^A$RoZlPcpGyw!Q';break;case'fa':$mc='#c0@j5Hp=B}0mN&$qTA,1
+B4Th&U)Y2hP0K
g&;E?ZV*@Flr3v?i-|:m#LWqc}Nj%}41XGHz3^<5cYK#tdt#7]
m?Db58,3w1Km(@&
oM}I,N#qEbPn5G+oc;A]Ww0WK?R^17Jt7647ex61ddC!+3Yp)?Q7:vI-^
lfLb`xIY|n[Dg>hLDV:o"*s4cDxt,jO%pP.gAM4D>aw<^gVyQS$tqk6GD
sv{,m3(fI?S76-}.
H:5,-/[Y;&AI7
DG.R^Lh5RPb7"{LwO[J/""(r:8B-_Qvd3
C0_%(mk^)FmRRsV9tSiGgP^s0P:u=XWV>LHf>NldL5>u1}f|y]1C@5uJn}(IuzJYvMKL^%c|pDPdLN+xPbpHuWVd4Uie<3xEhlu9xCKD4!w"j6L~MJ:5#/5^sr_XcBO<BaLcUBT:*X$W$YX5k8g=tw$qL-4pwhkJ/?BAIZ/%f-.kF+g-Ck7&(5kT$v4A+oXD]}"dmOKn=IWg69l(@iUYlQnK2
^.Z3XHP4jWob)h7*N&6qs_I
s{-<!oI+6TaW*h(Z4^Y/k-,ErH6o+C0(O?%,ac/o8]X_.<5{8*&)[TA73$JRr^1aVi!ad~<e"7my7PWATb%vB]@bmuh}gUeK%6@X%
qmYLO9:Vo_)1Q?hTRVoo.kMnGR-X_i0Ra9qdd::dBv_W#U6kN>p>@yjOVVNR0P"v>r
_KGN}z$V6lgmoaW$(7dcdbfMr]V#~-mWmaa?&[wXSeYg#u!/e5/O4q1"=-__mbzY0#S4JYt>F,]wo[_8PCW4<Q
9EKB4/c;KGxSPlT>yfk5Gn?nvvVoOL&*^K;s87vDikZbN~;{L$hQ,61Ph>:RmW2WVGXRbg1x<e+m.8taK:g#Nrry+w;[0@4*Mc.3fu`[sO#73s=u`PdmQ*25.wN]?AFp^EkeMkjbm}ZTl{1IK|f;)ARwvCeW3;peydc4CB<x8)Q0W"hyF}e0d~1bq-p_F~?6IEHU-rHsCUQ:+Yl6@JEN%ObD*SDd#S,EI1eK:5T>WGSb*9T~,Rau$F)^SwlcO85v7qU2=GVD6kO?GqMRI$&XFf+yR;0F2G;9w3PA:1VjYG8)`zEvDg-|LbE#x>fcFmCz7":E-xBP!T^E5Bz&9_O+4,?l*(CM%9o3Q:Br:ZG,Sb6(i#$AS[c&XnKAaQ*}+hpkk*fn(.]&*[Q_.1DLMk5N"
`T-)q+],iKN5;_`H(>:u:-Uqc@d~j!D-w7%_A3kY@[n=3J>7SH<@Ut!l#qgKs%Njrb@jP"y1=^-0/1W7]i5Q
WryaW5,b&mX)Hn/E/.?UK/e(Y&,]{m|/{d>#+wRT1>pX2kjw_GRCNS(3!l$fkS7>9rN/mR@p}``((f^xrS]+5F-@C0NiDVG+7C9
T
_.IVuYyYAd}OH`D9<$0lW>W#"SJ#]`$je<JCDmz8Aaxdi"dRGe/*>ny0!i?4IP
u]3W8]s0Si3LaQkM1*T~f>mBfqmZTfDxP`j&6836YWSTXkg5yfx"!_t3VW!(NGsnyE]SH`raNv*&3._.a{OCgM>}q"$P+>m4K$kcp!@Wr7sT%b5lTI$(9$Po_n34f2%cPx[8Ah%dUel5<Lowj!Da/~OFM+n_@5.JMO&/V8YZs~C}by`i=^?^`Y26&YV;PQU|s=/`2MolNn+:4:(3yK8~R[QSu9n=u{_(7xr|h!FOW"7}!ELJw.)nLub<]quWs`nBc@Xrp95B)rwY=yqtu[l{F[B^;VwpC`18,=x*y$=)nhIcetkme&G-PoVwJ|T6G*tYmu=br6HmoFYx=V*h*-qOa
;*9to{l:BT9=<e(J.c-Xe@;!J$uH;QJ!PFkvZD[MomKYP//,@-+b/1Y1=01PjT+W^e%=x+D4iwKg1{P+d.KbNZ&/8=jRfvn5jU`"%MKH9sUSu},_kn3,T2[KCYq=/BuG(3ul!kZe:L#=be>{+[Xu)r
F%~FiNZ*qM4.#Z5;COkDdd/61A_>OEQWj<)
`I^A;@qfAl9$YHjt-xsk_d4Nith&5%p
ANT4S=d186$e[va1":oqW;$Q/!uh(&aDQ^l:ujcs;]=.xU(BdrV]HqmfrfCs0cty_:Z*%JA]P*Q8EJuJ.D-a~y+oQ:F`hU>mu)"%vfT#%hx`mM34!,Ow`y(mZrtxFW#=jk^dw#}JyApRR!
+ApxKl2zx?3AX!KjH]VHZ!OYFqGCFsu8.Y_@OaX)SK,kC1W$G-:c=Td95~pcONZZw2W|fUM"-Vq60|Y71=hAk.ai`Dq{Ap,$"F]}?e%>6u2T==SraJm~?o&Ag{g}QsJVJBF`74-(F?snH/8+s@xx%.lLhZK1?OMs:YG.kEE1x4^TKf>X0Dh}h[ZskuZNAe(#G?X3]?mhL4bQX1)kC&/>G01P3BFsp-Vq(AkJm1n8UM8R]F6_q*X$w~
Yd,&=GWW:(FHmr``Js9F#
%1ziwbMdMF9`_V6uiGU%XbX5LG"=&yAjbm)_IMQc|e6inJPo<M2M@^ky)9LT(n8q0K$9VY&bxSGKY4wnUvb*Kf5><P2O(/>+B6
D4^p+H&|*7.s=>%l9im#iGAK&lxOw(FUL9)OlvtFY3o<D;8MiX?d?qpY4Qftl.noDbDvu]s+3)y#m!T22^oTK+i,lyJIqEXhH)G7w]#jz&`5Sta;3P49B]Y>IR]
o_FFxw1oW6"M1^*Q6=n-b0B7#~Jjc+.tAW.2<wX;_gm42MohpoWKFeEdb8b8wI/5D1AV$4Wl!e,pOR&!"+R`5NbN#Noze$TElW!^5%4IQthY5"aX0b3}f`cmZ_Z3Gd&,YcUYkbg5W]
aWmHXyog@WWJ3D|FKJ"#!h^NN!bSW(oUKJ:lY,qN.se[B1J)7Ri`Sa>Rd[yeMuq!/"t
x%Zf..6,:I)Q1hvc:>#3"3C(|Am9=n!G[Xf:~NZ)7A44/!.#~C"2s@@3?7W/Kl9es)Kd:M=?^.nTYOmW,P{9K0a*Bb[*>
H`xiX*lyoW<?{J@QmK=tTlv-BA%mc7`dS3|A+:$j#KP*5A}A$s<up3@W!bY-UPWgU$ws]"I+/9n-}it5/Sir]&QX.#@-J)4XFF31g>b=%B(fpJvLu4X$.37Al&C;f>@c7ql-SHB@Nee&vvVm<,;)$e4rWA`sog5TI[pOko)-j<?P_d>$>.^;,WP)snR<5rYg%=SoK.-eHvlaw!i3Db
i3pZ(^67SZ#Jkh8,J=9!@S-:=S$?)"`H_4Xh${U:-[,,Td*U3/JhBO!UIZ*Or:!F2T0$8,871M.!?Z)oB*&Ch:7F)k:w9%"/"$gvy-*(s.Apc<ah`]$kFVN1jcL]Zr"R1hwTB?6&7C@~N*IqJcEi9!<JvXTa>J52o#k[L]Y
Xa5kS[az;+-_mp99]0S&!/-?Upl`!O?QH=*QP85[l"+i##:K&`2>9|+5:XPSPI+Yo5p2tI;>FI"guh=&O%H5&e^,-t@Zvgm"!*iq]%K$2)%B
Wuu2X-`@XZ/apSX,2yExx5+hkr*,]^<R@#]mvca:py^R571arXA;f=cE6jUJLvR?GO9vRIf6os7%ECl6f:3L|u!R`81ABgF_6I5eH!wGd6E&kyc3=HF"sHJ@Jye`J-aw?cT[[(<8xSkg$_F7UZ_T`d"2>/!jk:S$d8)Ief;7N"p3MO/rKnk*}O!sZ=wR^CC"85H&}v+
|$]ZA3lVs
]]fG_^H@+))?x;-T~k/b`aB&l8sK<E%]&k_N?c+3![obKVDhV^CY=]Va)Y{sj?Ro+`TC@9q]@KjVGM-51lhj7EiV@)ZP7nGfnw72R;7[nT>J&`;`HYIhG]2I>7{@]^%e1
F$Zd^Q{P.4kL$AXyxUpDe_=`XN/;Y8IAHIf%P;n!NP*%gqPmLdBBhHXM<xYVKLB,S)-V)=vd{"^7[Q[)pGr8cE9"!x6u~q@`(GH]YH,ib->/=Z`><Yt6)EIhnnr@p0k;ztM
feFcxA+QDJw4"Q~g5GaT^$|KwQ5ea&j7auEQgq(IAgRln_FQD6Fm.[3^,[Em(4dWG-!&{FKeqZ"
FEMwr(fkG9:0<E"vcAb[j^<V]8w$E%6TF=B1EMo74veuB-sJO]Xyb0`v&f77-<k5jN!j*$YYJeFD]s:*|^BRB#*1vJ`giis(=8)*/UcHGPByP5!-Z&A#*X:0vl+hOn/=Ja,W#kLJ[rXVUS8tOnSV9tOhVm
+]iM).PRv9!]VY:]DVX(uzN$v%Jd$[&iDCO"9P(Qb6,#Lg.0"/Xrtf1WnsS8FqrZl^z)]"G~s]/J1s@,5,[Mj>IP24y`Onc06Qn)pgg[m*U*^t/UA;O_ba9yvh-lMjM[]0xV_f%>Vy?|X=#0q4dmBYB<K"`?GELn@$4_Mk([n{9/cIYtpA[~]0?tAEpRrhA]dN)R#Zfe`&Bn`,IB@wfSRgx_l_t_%V&]N$%T[+NGad#_&:/*lswVOlr<)wxqf/QUVT1/!tv
mQZ0A^%Ca=VDBk!fCHmjBJG|_QK6<A^*sk]q0N&ZpW>Y2pjJR,_pDWBu5!?|D5x%ZMVqM#kx3aLPBDBZeI
AP]v0Y$IOeCl4MJ[q^9X_kMahpvD
RXw(YOT{LY,b5|>WkqR{!X>J+JSW%Ia%A2WFTjc3?52j-5R!p2C12+,GliOSIO;XMr?W';break;case'fi':$mc='+ZuAM5IAP,{0}N6!c9LFEnt"~KIlrpUFgm0%g*@Dxv!1cWxk_dNSC!qfyy9o%#DDW[&eqgn_mEgTX3"(MDZFe)t<z
Ly^j[J8B)R
iO5Bti&~sf]Hr::*VCjdbWE((mHrB~UfZ$`Myd$TtcKIE{b#aW)[i}VWV&_3WWcNwvR^3?[CLtB]n10y?p@Ll-c[nR[:_0RXrkA/)-iCc,g~sh:IA2naCDsxyFH5oPdWv:jfUS]%z)v>fY1T&#_U`VjTvg!Lg]S1tUw_u9B=Skf$i>faw9L/7PXWe2_h]BxmnUXgho4EctD?a"r%uRN^rggPkUy%W6wH8R&9
%lrWb&q@)kyf
`uK(0goV^jmBJSkO@73y_I;KOd6sym+;$Wj}UjiEq^4nd^m8!OrI@wTLPrTr,8ch0YE+Q>B5^#"HZmca^`kB8N1k]O$Ih}eGO.MqoLf{f*ILnP#zoG$Rs~x
2^@3VTVx-5y6bPKpRhu//<EZ+ziLc&0mhH$br|qF=$4|P,kc7
t-TG1fH/rh?@(~#wtA[0IO`pn3"):F.ic7E`=nw&CaLVG&!;n>E`_}=~v~_sJw9S"~:gmix
H<BH]}[U]0Wr[|t.E8HQqSs&`qJhvfX4^;BE>(xhA:j`44i<1
%7?o.q0_Fsqllk9aoD:BgkWldz..I0k{PDm:na_nb`*)`8+{`[s1UU1c+JB:y?mDkwa"lA`G
-1qY[g.5<Zmp0@?$wx/jh[vb]C33TIAl1)n8ny!=d0BPFsOn&m<o:%p6"E3kup.T#p(vR/Qi"V-Y[j"t?Ndd3pub}S=.`^-*dB;*+Qkut_nm#V.a
%VJQxaLk,iF8v"$nCpWTJI2Htu$q!@tXV?MZChsz_YtZf2t!fKq3m57=."]CoehwC
=nYY^uwo1y<>+OHkI}_2hf""eInF]21xC4Om8uP[";_ccAWy&3"cP"vPRnweNad.@%xz9ZOm<U>M]K5JAQ.269Tm,;Z@XSq!$YPqMJt-2^ty)bnzrNql9I00&Y0msd((EFboU]xfVX@.ly>D1r&{4;Gv1
a?Ti/14&hqEk;_jFZ=6lEP@vAx(,;0^mCC5St@sJE#-(4C*#kA$yU"xY`K)LPBr%ApKL-?W,C/Hy]9$OvJ%@&N.(%Dqsr><;V=3;[6PO0Tu5jPnc>Mak[Ni4M*1{lpC@:f,yl3ZDK@J<4;viDs8|SLai?<%cjd_D^ekp4M;0g>]br"yM,*o>bsPX&qB[;i!$t0X3)~-,l#LD,nnYq3vq3Pt{"F)zDv9O)8E`=T-n!#AK8"5!n*yDO5fU*$QawO>.kV*NFFXW*HB$T,ANeWmt$Z;036kw[ZTHA!!.DoR4vU^DZ5SleQ+n-WF7=o#A@~lK(:h!+q)uWamp(iH_c:(jC`([YK*2f<N{5iAGl)rkc^xYMALOBR$)BcLRv[MW2e){HV>~kU@%J3MQdue,qOf8kf?`^pO0p&>H;&9e<20N8=G^`z0Gh8olPYAxjC7<(utTdePPZm0P:<.NHq*ajku_5nU-g~NjwxE(bH
uU#7"6#>QJ<
NVdM(mS1R$|Q0v$&.wpdO,yg=GencLo(hIjvx#F!@2dD[xw(.WFuTglS_k<D[;CO6g|Ku.`P{R{;<Jh^<+G9Ly#Gwj"=L$:BLG~F%j.9JX&Rg$^5n
dqu
WnL>3Aefsf*a-t_"&u|*LS1e2g83L.-Q3/Og5(*8WyG,6`6CB93;F2kH9Eedub|]cf~iiQzI4QD26eZ?z>@n7-
!!aB!
lS`eUeB!xTyqsy+:53m&6yp5m*[!H<-Upio{y%OEs#x)Rl5R<-LR(fLNj}8
%m]v
ANB6p^rlzr|X(xSum_e0".
mC[RPsvxe0Imhnr~K6[yqOr3;k-a#Xk3!e2:QAlS
"?_a=/=+KD)sFNsMn@V
VAC7a[px<=Fx;3y&i%==9PZ0ho2d5(_^:
x-0Es`
p~6]Zv9<%:^mWMU7#N34.A&"_!cS9B"Mvb`YA-R#H}<o0x*_R.l$.p04>17ErhK9f_pm?0%#HKfV.Vbd.INk2y"i={t:/Tv3xK
1AD_=1iPq((Of"H@gV/r&1z#4Zp)Z/,k,p:#d0bL2ghQ@2@57@2.?vu
UqC-+exC^:w&sZm#*!YtymOjpTc;v@`d38sO[U~bchFc9=1;42*`(/GpOO_-_<L%&7r%7,p!htQ#mP(u2:Gdopq;qRuD<ig"YjS;A^nIiWoD;APRyfx"b5HB:TRY`R@.;F"d:Jr_Ga*&I7F@UOi[),x11LYIuv=&nwlv:m4eL6/uQ9qj:_Q
}[.WBlO+.Fy]e[uS|1+OUqB`WPzU<@Bu.e#`7m=?@-4SAG21hcfPb>]IHO1501t!xVw`UKl=*OLsZj9Fm>p#z+o
jZrq!VL<zrIZ-nJ9ou$u2M0l_2".*UgoSl~g<eZ=3O;]6Xm==
1Fq)yY!^3&}CayJes$uob7Z]%E-V;o5Ph.ze_c-KQ9:DV"Cexsg*NGA[#C#f
-NAAj@a53rqALXbn.a65HIhpcMcTZpDvSm.2C>v%x{lY.qN1d>_LEcguQ.)@g|Gc,}f&K}vS(/-ki4L8"$g]%_+W3m-+khq+iQ;v](^|i@F$dl_kP~h<"&!$Y,.Daymd!flkD~i7])PodY#~dy06Wlh^$Vs^lCY@OPvve`N?!([6<TYAB5#1iKL5X8JoaX?v+D>.<2ACS`?^Zjb~(yR7F96UT=BxlZUtj1h/2[d+v@-me{yT^XA.(30N$)C:<&p~?6B`N>70>s%!A7S]&zNRevNxXf^{R&oaePiyDyx;1vq9dP
R
jf%F?+f/D$|
s#%=hP)g|*<w:45B/cL%m5$IxnIJ#X7/[Ad7KRfM.s9UljBeho.UaG994T-OE,/EXSG1k>hn93L2|$2.zB&0t(lV4EM1}6G-uagbwXCae0ygTY1L(uV7$QPIh>(f,;QbE-Y.Ky),->$NVgqSAAf&j=:$>$ty;n(y|Uu[
(KAo2U*AQbajHJCb5TP;71^&Zd@4%Z3Cne2sB!M{MVu=84w7tK3&JG<i4A]gYb*]F[XEj:pY3Eg[*=g}M9M7Hp
Z?,7"SwDx,XRaG?p]W<`*3x%p-<)gR$79:^"QFCJo*]N{FfWeQ=`4L;]>T%Z;1_k@w|d+,#E#eWdVt2-0rQT;g:s{5ynbcXJ-[3[M>#3/W1D*.`pty?/}5@.XmX)~/EJt?w!ap3grY~Bgr$B1F$<1[!T,=k:9;YB#<"aVlDv:b^bXCcWYlx]CkjF@/5KKhUi|F$BbC8Kpsf6OL,!f)soIs*k~b|1!yC8kUBgHjYCGkMrG=r8N@E1&ZvdJ4->4fHI#CfwmfPp#p$)a(v69^5<2+16:sqv#6@V=]_")k{M+=J
/?_f*QBJeAW1fC&N@_#nfhRPOS#k7
A6)@|P3.LOEP4dSApp<S`hyov,IIlkb(]:Q4l#{Fu2f4Uwd
2WWQaFwrL=imLqV9DP~o58fUeN"jZ%X6Zb&Q.vW>LUJ1Ao+e{0?Vjkkc[+#N/
r
E*EN0MCSB`rqP%ri!Q=9VJ5m;Q)KlB/CF:/kt6ln`*MX
*O!}UF!-`5!0^RRf+z?]_$fOF
*>hzE43;]e0W,sIUu)/^0YD&gb;qn
/n%|EHw@y+en@Dv5q(`hx2=GY?IO*^3O2ZZ=*q,twAc{APc$7x4cIqw`X+#/kQ
HrJ_6kjxvKzaLv!A_P/2"7.^Nol9#("vFbk2Gpc06OCiy<$bDF3OmGNXEQhymVa/4Eg<<Aj5#v`"AO2uRVo`tIMB"qQnglKHYq=.N6krQ9PU$`H.}."c~C+X_-^"g
?1f204X>E7]TWOB3|yH/ysqw:NKf,(JU)gx+)rZcbj#M;H=N]riy=Hvj,-Iu8e;4;H+?b8M5t+}@Oj(rhD/7@!YJmU:H:&N&&A<U`MDKx2uif2ioso?lxy{]ABURjg-610$kSo2P640j
lPs
mA7+@X]BLq>IS2,g

p<K3PnA-Clc`nisefM&@7B2;tEUQ+ns+"S+}(j:n0<PsotGu?|JJTCG]G%T:BA[GY
W!)#DIDp.!tmKhsX8T@qq_/]>uv"DwO`2z6;3&nTl!/HZs/wZ2X7<oR)G
cK]NF[l2wv8idoDJ,+J6^I.6PT6ZGPau7LUWc:k5UA^m8+K42uWrg:_1y3&vtzie2{HC`lt}Gk>(+9oDl=FRwY6W3RG"Cf`7@ee<]8oroxj#N?^a>gI
//5zc`*rY_/e=k7
L*i
:Pc,2I%X/?M}Q<?aQ^)lJ12f4uewit=f.QCgDrMw.&057!ANT4C8<$Kc#tPVZlIva{43IiUmJ0:gtAa765vsYr.NJY4
n-tV$r.ew:S"QQ/F6mC_C+fqH
%3:GE%g=!Ys1NydrtzaF`$:veXHY#ZH%bGM7f~J]UJeF`IX3RDOOtTkLvWI.wK2V
iv3Q>.>(xH($!
}"R>?wY>kQJ
)c3!oW2F4#w7F$;;3U#z%,ujW>MZc7DY=cdh%SSV_D$`_-te/,<8ueO!^*#pJLP6G45g`fRXt4c5`rdu{<XV8+*jG&ZaAAV7$Pe`T_&MDrZZhoQq#C$xvtX';break;case'fr':$mc='%Zu@qbP.!/f0mN($q?k2qTv&[S^DQ3-J.@IDoF/gU5QP_<74(J,D;1^)b&]bKvdm=5xSL7z^{co
]rKhN^vC7dn2iLCK+czxNyT`Zo$b(xbZ~g&GF;J>~f*^{;4?oa:3~H%n^6YNP1"S[[~jBurKE_3=$E^QcfxGNF|`J]U/GUZ.?rn7%>E4"
c)W`B==`@]}ExQV=]QL)~1+3MRoLs3VV!&3>^?@;@hQnZ[UXa+"y
*K]5A~$yv<1IMv(F6ins$KK[`a;jAoI=
qpf@#lWAVpt7[n1aH,;yElsL=kIX_l2q+wgspK%uenj;K0
CQqH6*xyf1<=g=ujgyw
xaucST!7$EF]8MfwsR]vh?F-AqI[3{`*OX9}F>gkc1Ou,Bi(2_SB7c0N[8uI-;iJBz-O(iI+-W.m]1rduxAk(W?Q;;VgGs?`O`f*A8N+;5i&F;arXJ9yCis?[l@G8rw0Oo"PHAu6h0H:%Eh9x#r/<dRyq=c[:ndWV>If3mVPC[?]Q2s9Co*e=&kM;4iA7u[3?Mgk;s6DP<j{P,:iCQ54.x`Q;L&@@0Y<&m*`DClP&ao5TEvg$<o7MWN}[;=skON{2/d8kZ
|($cWw0Ix<#,v>Hb!IdI|$l>+F3/ZB41ajHS%k<uPW^2mD{Y+9{>{S?A2CuT3>@o44pFSnO_cYpg|9b)JeYm0v~_~P$p#1)>@S}Y12kH^y5`?JeTkY|BPvBMjp`hsD%m(>k=vMU?

1]/?5r$mJ9"R7]FeJteTfJ_.%[FP3nRoNIrW2hlleRiq@5G_j?=hGVZPB+pSaf>q5BxxbEU9v<_Q,8B+i$4&o/nq%:D+,PCij&a5o;h!L($c)[,SD^~wP7?GW3ZP:
=,B9N$Cj*@bwx
kr7G|cxXQD8:emTJHaA%Er+tGs^
l91%EXv_td"#wM{jFRbnlE-LUvUlSbbblYM/BvuJfe3K/f^.=.ZY)cBP}/]ty=^RtnCef,ivm4MVvn:=<VOSJraX?8]pKP,i^j-qgE=rN3tMT!YNoD+ioiKy=tPGbH1q1,""iwLhEfk`uu;I(XfM_y8Qo^/yBay)av68:UP^c-KGIk<ZKHy.hvMx
Sp5/gbAa(ZV!p6FOW;I:KU-I#x;910&aLMmHBkZ7ArL3$ju4niKFZ{=.4r[X
!FJWD:5iLR:0G3S8yqu8RENECD5bpmob5
xg#pb_r0Y%&DVf%$+BU!#D{?Qua3m]Ptlwg#AR4<_coE2xx.JG|]:H)-
xli1`?t/%7E=*gpJj*!89OA?h1AlKJ]_&O^dgZyyT2h"6&`pRq^p.&-v4.B7p4,i,j[URh@^pJ.Ju<a{S*#,j3(ZHfB/V+G^ao3DjgB4[y*!AH4IZu4%.DW3u]llTK+inXjh]c0Jra+%W844J.<_JO*6U=)6/;(@?sb>OZe
ITkKFdFqAle-f=7*kEFDq&mR[=8Ydr@fM%PPVT@aIv?@h&J)^Oi8N6"
Jii0hEO]bo%q.3mf8WIYCX+eBWPg@@,Y6Ai!MiERMm`uqM9=8#U*NCVl<X.*[SVOJ++1*qEO2,4FpbRI]v]MGB,Lu>7_7R,2@Zx>x>]H-jw0"b2%U2-:qx[U&bS/Th<M,(P#rJAlR=fRr7;~@a`{c/EIC
3gn{^_]{l22~sKNij)Y/BwNsL(.-j}oZ"T%NT[Fj^@b//;",bA
?b7/dFg&K#h(@ykBfh2[$VjpP_MT4=X)@<7;:hB^n"~6sv~NoILNC^E(wu;?x>|`]>g8-I9*Mm>.L]X7WyqmSOq;|B
uud+*!1AK}%U4[:V:n[DDJuYgIU(hIo_j:poPz.
[`k!$J
ma$VMdz%IY-
)g8Fj%QUGPW.:Ivw5g00sU
rcb5t{p&63<Ek6u#f?4#)s@k>#]":R5Iy-3J.DMq1zh%"*?!]`=!f#[lbzRfiA=g3mcNhYBd!/@VVv3eh2O}-J_d9V9km5m[:78S=<1*C{as:~:j5f/2mxtRHAlaO:1vbx:4x|hL@YITPZcPE_=XPOF`@

2C&Pl#4N&)UrFl5.*JnlX%D-glp+t#1m=ulG=$oP6^b6En,8Pfx#iCpGr%TJS`y2U(exIIcRtp)=Yk#p
fa4}r"?E#!?-+Oo{l/o*?N<)dN2gP-fz]_,fr61&YR@-Z+S38%UW:N=dZJcv,Pjh0}X>MB&DIQ%C7m(aPkH{]L;F#Pb}2
tiA2Tx>a^w&zGwuoAbT4.UJEj7g3.8jmPpJL+*S_
3YOaZT2.CI8(S=gDy,[sN
.r<h^;7_A]`%?e?8IWc-pj>nH:{;NOdtgIAhP:4=laA35IH-B><?r]!7B_Lg/aQPSTGJeU(Hy&sKjE_-&,,@K5UkoXL"uo/8:QTy@[v5;;Wta-CYG]m`Gg?3Q@s0X0I^Z>MVQOR9|[;4]^}kz2w2xqA[%56N`Y..!a+1S/%Y1mt0wuUKC42ROG7;oB)RbpS,;f>OAw2Mz
5`R4>Auxz[2H8!aiRjcx4C3d9eH92:Xq-M^dS[QA20Lo8;suVk83V)x`)xAL_PLl[j{I0Y@,q90Tn`LL>+2m3E^R*%I1yL&/4kh#3(GGETiEx>`=y]UX3+k?:j]qfZWw*f"1Hi^W9,FWh9lMaB-eoF|uODr.cn:Y,1T4LWa]+HC>aq<xz+]`qekpwDy(uKk_a0<UOE?sUh5NJC0B{kM^rr;1@3ZKS/"MyC6SyT%j5^^a7,6k4-30
PTpHm6U2w&VP)24#3R5Jio"B+OqeY,<Ac>"BFP,v+Ok8)(Z,2R<5(nk#i<+U(94Jp00Pw3M|Fa<t]XaK>quS)zRtCr&2mS$zL;ZCpA?2$7ry/tYlCw8A=:x/#E:Z#9BF.3G?J|$8.8Z1[)b9&yrcKnd`w5=_sAGZN6kus."b]+n<L"9:.VY[OW
B0?PGm#vVx%xkbdr=6O;p:GG*@*EWT["?K1SbnbJA^WCRD(5+Yt9I:P?#%4vCl8F(,5N(8++9)Pv^TKUy;Q&r"0XPTw(&dAK1&-2[R;y8H{Q:gMK:a8fElp5J"GN-Wbesc1-iti%E"]WP)(=Q_a@``q=X09y^6PvD1ksLd8@2AB#)$Z2+1%Sk&ELAjS^^GQqb:;I:ib6t71J(6i^`$Sdv)r5ta-q3-i.)AD<jwu<rD43]tl)QB5#lW(aC(RS`G8g*pBHe?9C`gawov@mjHiH4"Iz(w6MU1Joi-H`MHgClS%Il9=0?CMDXdOnm3`D?..[/bpFeGS&7ms#hI"s;ResMD,1n3j<}OoFyJZ%Aki8]Zme7Idl^]7DT^>>/n0Qx$:jsZL/|*HcbIVE)+Wp8m8ML]h"*M:fbU_.G8eR^"*Gw]^1yc$d3$h9!PC6jYT+qXEOW0tOj5,RCrS=}oS((]7A`=}o+Yi7DgVG}63
3
(T5F=Bk?w4d:RFmBa!mDag#()r<>:o7=!#7U
sxl.b>(,T(^@mdP44Z4j_5P,-~r&NhnPE}I"d~
U$hGy=jBJG4=nd13$M46,Q}Ck;oOWf&U=hiJ1>jPT)5/GUf6MGltYjhCiY)+]k9Gb*g?WM!Tyl%/.u.CaZ8R)
f=eC98dI>Yj`xBi
XO-8M$D[j)<N7iMsG!8f}cj1F,<=52.=U29]rGz,&e)69
LWmOU`fJl_"8!0}lde/DF8k&CaE_AS@#2Py7XHcWXccOjgT:}B-WF1}tPZG$7>$aSHOXh7^=U6z^a6YIL&T
z"/eA9QJhPus`P<#1o)R<GQ#C
#,3ptjCUXLdwSg!/}$$cRX.?ym
A^)CVxum/5x*f&B"_,CO2moXGRs?<h?!![#.U#0dQ|/kWpnxs7PhXrV`H-t%_Uf$8G7Za)1BG!gH9?x7J(W?6m%JS91p1vIph.
T.,D2+DA3X7d-Ho2-%RFmJ(K7=37kSLFkEP>2M|:~dSG}&sPUM7(%m_-I5g?#__Uw&~m+PDZP_;FcRtRXYF>g/mU}<@+H-968uo_~jt*;`y;CO$h6owq<LpdU+jWSEPuOdDtAP4CUswM/H:,%
ycvBCOoxAu$@<MgN~yYN>a=g#@bT4cL6!PF4S%J`h58B,R!eR+[up`ah5nrXXP[D^gWbTsx-
_suyC^<?/TF.u,cT8^h)[ch2Xp$z6@dG_5Z!4{-7GUGuE_56$(o[suS}m+48sA<Go-F@uz9ZU"mOGXB2*aOq%ism
PQGrw;o=g(<%_K[CFQWtP/uvkEkrJ"|mL5%WbNK`9h{D$O5P(mU5^tq]Ae1I&W~j,lO#u.|0!VqQr9/WBc96+0_B[Z2J[ao+^0CA?I1H|0u"U!?RMm555K0,L^QE/8PO,q):hdIt_BX-C6&(J
7x.Ilekyw(f0J2pyHp&CXIbK^&qQH78`GYZK8-%#mD7*FXyjuyaQrKJM:)}bN%|G77?8jZ-ac]suBWGsTw
jIV,/KWfTiy?NF(&l7+[C0xq]Y6<&Jt0&*sQkFJW5}8@xG"RgWRJ*
y2lYRQvD,p&/&^?
d-u)pYIo.&4IYXKj;;13=a
{JeKjJOy9k,Xv4cuG`Nr3_>y]h{"AleK9`^0GSzase>`m:2_]bFp7bsac@O;3SB7<DX-PdPFv@q?>PP$5vsLut%x;ABAJ-DK)MlbqK/.2#|Gxiw0+k#Uh=NJRVYm~Y,sq@ygrFFQkUt8{%*QS8uFfCX!clOr:VLqh]kLPqKoQ5Fgm;mc%!{6:];8&NQ8^qCjctF9<$?I,klnM(5//8Q*r7qU<cV_|O6qp&*]@_T&qs2!*W_aLx@_SaL!w%Vs8.3#[:Ds9L
.ef!)DPT)}AvA52taB!>9SM;U9$n1Fi^7_pt8uZj]1D7Rl+`#*ZAI@J_K3z)iO';break;case'gl':$mc='#Zu@qaM+N/e0
Y+"&s+`:tKs1Ns-,<y`|:u;ym$:A7Ar&k0+XW7Mwi&6i*YC(wN*I7AV%pi*5DrcqJiC>5s7:Ks+5_Q1Qy6W6I6i;it7(fVaiu
C2y=Ja)z0VZ%0wQft#e?CB+e[LP/s}G+
nna&XV!cTJQT]/*8KFc1
L4Vkn6`AD|__]*P0
j_N7SJ%n4&a(gBy06<cgx0x
z=~Tk/442WkLM?,(DpDre#)<ywm.vo@/A>"JMA)Q44122X?mMsP3PXR$e.0bYUR7h2?j%b0_npBu:L?M*JwbRp}M/Rac%]`m[y4JEa=h^xMGZodL7#en}SKTW_S(A#D7"hYi<IFLTenFjuDO7I_tp<j^o8yVqDt<Q`C_6LV,Rrq;p8Gf)h-ma;)iXE(bOVi1D?gs-kN"R=,u(JRc-R]wxQHHPZy!<,eVuUl;:v9Vvs"T#@)*DsJ66GaA
3;?pW)T1E^gF]IPhgK@6j<R{6vh86X`[E[4mt@?IF2^8]iYK<*G61#:WAzb|&~OoUXI-AO+GAf
-$3@vDfc@&y>D)-dlw8Cc^?WvD}.ry.@s*ja#3u6B"wA4c4cpTXGfuU-}[I.+&ikT:5uEbd6(0}q=l(dm._WCh}M^),1X!vjs`.[Jl)R]qGc=<f`VjN@t^ew)xRuyDuBj41Cz
Zy4r9fzHdQ]cp0{^"e@LLn0XnUZ7%eYkLVq+
AjH!cxHmX
:Oy6AJWvv?VX^1F;Un^j@6jE:D6a*^ud$Zp+(LB}.k3}OC9L#`Kz!{ibD#jH/[=+WQJdT7h"rpew9e_=!Gg>c~%YRLi"TGZz7E7PPx/hT15
8#5.W}w%<uPk6bq;5:eO-L5SgI1.w;NvpW.4uE1QiLvXa{;ayn[1K#nxV.yF=Q
atPVk"DDSJzQ[HM&HymG,S@71MyUyd<%4+6-&1Yb@Z`epk>e:wwi*!qBu:>yYfR[N"2X.n,jRuJNn06Y$"VGY3~9`dXl/<z%DpjI=#)3m.cFi&,To(PLOcN]81et;;@7^<@^ahs>.<`xC*[7Y;YgOo!tqAv/3mY,Cl@k!+x_u]_0nM]66b@>Xj=n74Erx05;|0q9G%nZa3[=$
D[eN{3>t;Mt/ZBhR{]p$`H-d}F[!]K27!>W0iTCgvkyb(4+OoLTIah*AZ]Gg+e}oX
sOG1ondjqO$&O"=%;F@-f0#+PMG5-V#WXT{-4Y^h1h~y9P42zIjDCHKgj[_`}7Gk/C-9z7n%IrCV{lp)4fSH8V>KW3<m}yMwb!U$5qg7ho2p*xS4HtWXZ1^q4O<kq.TwyXz`v>N&:xw[+N|4BA8t]@vH]c~"b[`V{"qwjv>kO[Jp7VTy,UW[X+rL*?
0A9<9p/VMl
rXS!AVzFZxYVCXRV.D:;u+nisvTdudJ(zp%Y&=A`!g&21nbjC9lGr8^"ro7^2Sl.$Z$%LAlt]0yns,x^~(Due>hJIBuw2pKAX5jkGuSBwxAB5Tu&o3AE#IF1zV/i8mbI$dK=C"#B@@F;AB]%n3slE"NEHP^]Y9/R17"EI-$])a|klgE8e9TB)RPf&G_Gx9{"hf:3a9Qq:U{9rQN5EXjA;2V8sMbP.ptoP!ijL]bn.wQjX)?n%2]DN+rKxTbrSU5I$4U?*]2[[@1FhIa1,$(HGH]X60Hl
/E7c.]=)655lU#Lxn!3^jKwr=#dmHz>o3(_<(mF*+iuNx-].V?%+
f`Ci8*Z
.I`^W7+X
s{.5##$%t}cp9QnkA1s5QOk;JblI680
[.r:o_e0gAoO"*g8Mhr2iQHtSHk[R.Yk"-7KYmH%B}O71/Y_$a7Vw.EGOT%{/Q+`NNky+%=1%nl@;XZs;Mk<b>:c`ydv,U9D&t(%Vrn_pM-t1cql%59sg&E"!DR23LVe/3[Yl_eEDQN}t8N[a#/HkZ$]PX<<Jgwg%cG)Ep_w=O9I4#N+*e)4o$kQV%og"c&~N/
pq?tAW/?%b8HVqwSZuNX!7gE|+j+8Tx^Ju"Wi5Qu;Fep51FB1b+?Z*l)hZ.$<^7#PK1PKhh[=(1/P5s]5X&2!$WE4`*NB`cBR*J,*]=^sgW7L8+]Q@i^
2V@A5i-6@3I34;K5pKCv8=TA8v<IfJJd
|(LL[gZC0NR1oZ4+ACt+mET=~chSuTuE)5EWH"O&6Hgt&=pj*g;fB+h&E<cPOBT"?ak/!ZMTM&ANMxl<22ny`uSS{[gg-cZsQrEQh[bAVm{Y1gyWz;ykc>
OnGN_MC_<g=3x~%]m}mn`<@anj_(m1sILyT*3DoTKm8(<"giWWGfm<!A#
)>/uBVW{Ud/NR8%{2(3nxuV1LR+nEj>5AXrky0b=[U%|bwA1JIIW=^8w4+2V$waH_Q!"S~9mwAR|wK;$UQ!hl+?p-PJ_@n7RBU>Y^j?wKKKCJl/XC~w1ezXoU>GBG$,S?QyZAuT@=6,8_8IMh|wJV24cN>^v_!].H3QM,i]%rLuJvr/SX#XuUy/D&Y83iu]|@+Q}hoOPq2g
gUN;=@B4L-J4_r5sQ
@2yBdDbT7sYAb(_DV%.`mzTR>NxLA}R4/Da1sfbjOD[R>h5&5dWY-jl"&lG*IVB<G|WkJEFR%#>6tb:G^EJiw|w
=])
Jbr+2=F@fT&tP1#pGmT>yV$!oet;rPTKi#=^!ObO62Pts$*8=IX!i5xF<"mY,e;{heFf,]r:M7IH/-0Wh0B=6/-iRL)Ov.q,?bG}P}0T>daBCPH5Qds^n|C&UeBdU6n6x=F~1c$6g)(0*"nPxXE`&I_jfP:]dIaB,jW&DWMwC5g9_.r#W]#7M+QAP(9GYPS2W!U=
aeSeub?C_D0oM7]Z7YY>FWphp5KC~%Ob:G
kqIHD!]7F#Ul!s*:#+-34(C4:T<y$H!F?FmbIE#s^$oq-9m`QX"G017K_Q.$uCw4TLpJ4u$)NX&X&e08BZ7V8W
39nWLd@4-0Q9kgz:rRfOnFrYq3JkSlGVU7M(
2|%p(4R!8`<|xV(V$=b$;;`fV!eY1OqvA[N;toc*uEHJN:jQFKf{w|p5lFiOQ9]5DYX.)wdN-1m5nhH^T:dJu6.hQOM7[G^>+959sih.x%$nF3!t9nIEgEC:yh>(?M86x{>LT^lwFG:qoial07McF
*qMOs4^iB4g&PNg0
,Dc;]F-P_mX[qONf"O87%$a2iq_>bcBnvuxMu/U!7syseT(*&?tTQf{tu#wZFc^"(h+U8j:,0%c0S7R07/42wt_8Eup6=:tJUwR332Gb}uaTz:[PV`emT8kRN>-0-U{xt)4#-`BIQU.ctrmcCC0O/g-Xv5:cD?S:|0tmFme-l!~NOtRRy`2QEG-bb4;ZWs3PG,9^DXb;xt)l*K*5[w|!&`?-bv_on>,Q.w%$<BUmkF4$mGpJNeP+f@Spx;^VlT:AIyhtc=2][G>jpTdt]+}mUv*ITU-O%F
>DA{@"d;I.:t?Ppq+~85W^,8[%?fDhsr10<KLf!8;nISHjdyu:FFRxghmW%q(*
-h-:pW41m6jq:97XU6*!&miM!9+I=!cFUWBRyW,Qw7A.-@%v/kgSl!e)gf)u
1k@@1(x4X+l2c$c%>Gla
`qS+O.^pT"qn,&NyounoK;$jnbh2!uT;]"M<xgKDalT)7OmGQ
#Zi
LSYqO>8"N0DAI?)KVJkQA7}y0KpV
bCL|9
h[AfP#/B*>R-e3u6/R*cz&T+y8">0j1]K#Vvh9!HxyS6w5E2O&OHw]!k%8*_$<N,,Y[Fc<P}S9dBnbwO$:KLGV_z5~90]Sh4u|+F+}N<BR=K8lt1L|D@LvTUXV8;RBvJEnG:tH3C7&#w=JY8a-Z;>6*{WA_zj35X+c>jt
$wrHK?fKu[0/&7e?_|n9nnX0"z>MPjQ_/Ub&2y+x3zp?l0M)bvxP)RgT+P@<KuR~Payj5JAt/YA:n{MVP1lM6$cPFx:
K4vWd{Dy@Q#07xnbQwaX2A!aHt=$
UT,6oq]+&8}bVgA,A)W7FI4V@6[:ld@+d@sx*=q7Y[NrEY=9$S8@Ir;""^uWK`>1~7<d7+EyugnQyxP5Doa!`:vHZj;r2<*8b_p/a+|@l7(k0RFwQY++PDS"X?Z>TC=+y+2?hvJG}gJD1BJL(@r3x1b
DUkN-?@OxL"2JbF)-=g0(h>^y(W;oOUGZ_$^pZ!A<H/^97
t>`l8NFcxi.}g[JBUfQyONaS=lf0d0?kduPWQ0b=a@pfvor_jSURk2ssMbA}pb<`F8RgdlPHsSJZMyNo_3LnvD%FJ;+yAXn^.(&g,pFMr%ig"iH0b55V#ro74]d{PWTt&y=A^-0L1Fo|YVCyJ<S9Z}lb0!Y$r"NDsqx/[@oNJ21*bBpgw&#z2qJ:YI/ZE}T/+NW
a^rv3-H+x6uZ7wJ#Y?iIMKVIWXny&9LFd,BK^7KGX^",8o"ZMt5>n!GA$nHCjgP~,p_?L]wTO`R4HK:qQx6ov+W#N,y/"cWE*BB9dcPi^swM3RgGh(;IV<S*6o$ZFR+je[1l#h5?Zov64
"_HG&ATUyKxOlKfSFdQcgPI,#-c=DsDc;Pfc.YKWlw$SszmWfaVz$Ckj3Ic}ZS>?pfqx#L.u%Kp8hY2>%~^A"1/@r^qd
u[.q#qun<U6fb?iz)YE';break;case'he':$mc='+R]AM5Hp=,{0|84@`Pvmnjh&&r,$z5?5[>"*/V7SY9k5k1_Du&5Y:pOakX6;rsRld9l[)x~B{6:[B=ArUgWd9/_76@!b5c4qfDV&wwz7"WKHOI^tq9tb/5qI4Z7GEB`y5m0^CtQI9;zqJ+{`*mMb2bOB!mO7
HS!ODoI2_Z-~bP:,i6F<F^y_y$VR1mxBfK
36KsPyvAP6TF6F7nAyzm~FJ`[a6`OW&,=Zv1AGKPJsi@:oH:%_rFXtN$bxayzeFxayEnfOtmIy5X&$%%7Y<J_yJ/}Lu/c."cVa<s/z&ycArHOw@x
KlnM_jGIHr7h^K.)MR,wmWslN%L~78,Kh-Cbctna
p$&xv"8BmP]tTjL>TqF4b3C&j=k.2G9qsJ<3ogL.z34H"vpm5t2QZK]VO?vCvQ,2m_Y<XAeH@T3JK^"]Dsk_V(*1{GlG]#TWvlj,mY:?}@Dp>d|81Q.eESo+6>YT[xL["Oy4,U*(U<ice=id5k*pOi$c)R?VqpOJ*r=f{;JRp5K.{[3nX:e8>6>M4CLn$<jQ
7Yk2_zFiIaj3Q;rKdYg-[24tr.CwJsQox7l+=JVBa5gOP?3X;(T;^%*#hk-]Z2L`QBTjx)-omI,RM=I5yE[`8D%Wt0`8Ao(fq};?XEMOTDS.Jsf5[GM;QX19;S#%s4C.`&@xh(FZ-NA.n>S@o0w{_U.d,$ysq{t3D5HAQ8<UmZT5y8xFT>W,N7N
Y_t}P
@kQ9auI_
Jo;w#IY5DI,dp;xoJY^l{/0X_]gS.&x:8dI>:6pm4K)q0/`b@yaId9h2Y&!sr9$
Z6/d]OhN^`;hoR6[MLZ3#9|={@6W`w?*79$hEuXv,ctnMRN6/0;;M;&jL+oO-M2Qd%,<{3Um)U!hdf@_J`1l#5w<X][Py<Hm%c)`tZ$D1%xTv0f>No
If$~`8?r[f[V>1*f*y!BlkQvFI"83QJfO>Q0DrA#:tB[1qe[Y
3spA>[GN7$6JZcU&lDA{Sh:sBW)-"Z>es>A(&lC,b~Tu6W&a-ht59{FqsU`h7g6JUUh~q+iy0DNlXO>gd*DemFTTdiXJ4}_v[3
MqZQ5bo[Zr"/%WC.B2(g+$ywp5bsa>PJGg{KgS(0,m]3GuT5VAA7P8]bRAl1UR&`qQ@d
RAQ4wR1utd5P0"pJq9wBh
6VpKGNL&:
h9l;
uM+lei1gb2Hy.Yd?fL1S$!+r%
O[%[<
?<eTte.uZ2!YYp{%*n,xY`hbxh#)]4]%O[Onk99UW9+Ltv#P~+IA=h}JO&o&qT7)!Iyu,>hW,xYQUw|TU?0W"pi>7TH`Gn^PZ%LTO=RiTojtQu`O/9=$7]CZFJp2?0S6x*O.VkIF,8Qwza)K`0aE,`aysKF]lQs56BWn)O2"q3@WrT"RIYKUB+al10jl)H[NiPV&pIF9zLj"$i%^E4<t<Z2Cl[3vf?8=zETjvVui[_cZg1X-hn,8EQz7S$!4.!,Ycu*d:J|NxdK8~57L#4e[9rY[_iR(A]|3@_m5h;!UsaW$qqP2L9a#0)*ZU&+E1=]wU/YP9](g66.u<GjOtO,vANRCh="B`TZ>*89a*s9u.)e[y09#71Z4]QNY0E^Tfr=U#SO
Z[/nxL,`3j7[4Ox@wYQrKHcWtl-_04qEoWmpGX./_Xe.(s!^)94S1Logz<MF@YqT@Eo:1wH!o5Bl`GLg}.T1ig6d|@FslPLv8b1.?QB@q,Mp0"gYuJ*rt1+7fIedQ41l
CeZ28sDqo
w7Rmsv`<!)R#mEbj3d#Yja,Sj%rHB2fs&62^UKjA2pmPK=b;t~ac/HB.T-$e*%UhhKWl37[WJU)lGI3_Vs_Slv$6DXyx#/g*.h=59cpyaSWAo[dF:z:kq[GqF?:l8t90=V-G(
O:Pl(@;Fq
W5E>_r6n^X9-bI,DQh#6oG-e1bwA#l(,:F#LTFN@UwF?2THI%)<RHOo!,/u#1@"/g(u1;KfHfAX27kwAOIUWh%Fc4{pk!q"So)D}Z7;Hv`/ph8L^o2@m[UpBg9p-DhZ@bjm{XI
mM/Xv(o2#!m)^[p8ZS:$,MD(wrUeKn6OQ`[eqY+:|8qZ18I#7%:*vc7.4_#86r;>v;aWllNBwGNr$4S0Qd><9Bn,74%tE4J1rGa:t)MPVK_7G@I8
0vod)Qm;P)`faDZ`v*lKFh.=PF9gD{V5mL@(,k=MHc(T-D(%yIb8*(Y/p@SED/H.;]C2y-,xvJ]O)<7ou@ow&*REiVv<M/nYglkhCX-HBa%lH5@>eLQjWWC1SQ>5-+5jq>C1,#6Rx&j1<+qgU0L8[xlnL8s5nI%[AE89pmEj>pn7Y[_D:X#c+oY>%Z/EA;kO^(Wv_1D*rG]~mpp&I7o9/x.G`>?j(vSYbBI(3%+)2+juMIyFK[3IIt;XCWq`(EhF,;6&t8mbunhgC0Ek1>WgO5&rA2Z@E]w~/Hfa>$9a@7k7Hus91!uyE
<K`@o<(MuZt+9G=m!,YW6<`DF8Mn)!QuWj4[D(c0??8Yel>xlAlA.=Ll[JHM&OAa:wf{U%r+8Y$BRh!Japg1!}sS5|VA&$+16*hT[URSlLA<vO;8pE89,2(Gk;1"Yg8<>7=>eY"~EOsGV#:B_t>9`-=OaVC&QHDYxO`"R[,xl0ilr+[DEOs~<rA[1r$iH;TJF3u-+~:bjHc6YgJ[T]Fa(e=}bw8J8_<ko*s56d/!Nd:IQbGV$M^,30kN<9N=t->YPm[N?uHu6rI!*"QY2?FiPU3{!V"#*";x:;
rpt;1
>$CpT[VdK6
x+_W4=U"D7C}#F+L2<q*/!aGX/a0KM3Z;|)q`Y+jbr_;4`(0mG25g(YJ04fSOA)wgf<FqJ%+aP*_Gs-N*<*pig*/
b;0"n4&!VjBS:%207?XHz
4(O?r;7ec(BuM=EjBb1^*IGb~t?!fVxnzx4hK^a5JUU/poMjAVwR4V5d[W}@35JQ9j*4B?6(#En&rrD:yf5vb*SO|Q~6kL4#*-}q5XbU!oEhHQHOI.K<Sanf~PLFw6rl&T0#Ux5jef::<GeT]sg[9CM+[O#F(p
]:Oi:kVgL,F19[BN4h0/Z
_k20x*xv>-X)dwB1s(*ktGQ,<<[+S(+O]FEN%=voY,E/DK>?i}En%[nwaEC^_30Av`FIetu(bDpkd((Vs..ckU0AI,/)rJ8([j++DuBf$;dsbd"aCS+Ve/:8K>s53Q;EDkUE6D(CBt>e?0<QS;?Yr}%G/BO;H:oq3xQhu84a.r%vx``;qL&#WJ1qvTV@>Tw_%GWV:BG%.H/8Ip#yhx*Ml)[#mY$Ov_Gd2F-[KMO^Pbfbp_Dg))71p"fh>]W%e~"RS>7[NM<k0Ld0W~q(XQmJ.t
a@:n*XkPOk=Z[;k+=s
_Pf6acYA
IKigkaG6C#@J/%kTT].9o8_E.(.51I,v1?*j_8ZMKh4gIDK`Ge?kqc(@ahZ[]IlrKI*lZU_dDh5!!2b;d`gwV3B68De8K`:58&<2mw#PrM6BG)5!E]Gb.Gk]Ge`%"[r-~3v%XI&%q^maNi#dtXRs:ZCosbm)P3:Bs*oV2Q(-6gwlb3[v"_pU>PHweo>XJtSh{P]G4i|
j6Zx5MJLq#7_lO&g)7,Ir,O*s:6C)HhF8G>b5//x?eAfA$L:FL8>WW[4[HxbIdaKtC{Orv;JYQ&:Q[U(@sHO[C)<#0g$4>l6X]{vc@LO7LGh4n~9:2V6`
5$d=T(9rec1XxjfO2=efDw$9k2Vn}&C9i5^7Wpbd[GoBuhn-8c-t8QU$M,bN2OCTqJ1):ONe;+eg-RAIGB6oCRAp&=H(LbBci-;mh7LCGumRFX*RzR$d((DK>MCi:dJ?4PBtE8!c4R8?zN;9fq+tr?Ys?>`/O;GhSXOl!fR){07w`.%w6z%^59>!|>FnVpO<_i6E!Lj1]?@!{2!!96oH;"Y9&#+RcLm7wH-g;a$M(VKot?=`4^>L^HFs8`.>dRjrdv9y|4BNxr.U;@>.EO7o?o7`Vtpy4f~
`
)c+n.DL*{sWpk.H`%Kh6*RA.Q$LO@=C,{L$N}Jok$hB6|EC9q@uyAI?[8-`Dg<=E`ldNE;GQkZHwXPNr1&(`9/uXe3F=EP<!sO>pK]d75>@fGUE&kL8=6$anx(;s<xh*Kj^#ab<xf(DYN$UmS^;HbY&XsMY_E)h3(gMCFO&LpExH0n3m7A&S)MB*C"#HG#MbZRa?.W#@T;!UB=Ggati#GjOC@4D4J>`KN2iO*-Fp,T>%IR78.Z*3+oyos=/TY+b9Cdy,nFpJaJKe&x&wspXf0WRY_,XxX.s;XW%KW@n8WHgRPqU7kHyA6<w$.B)so98XE3zPB;D@GbaygtX';break;case'hi':$mc='&evQ|]aG"B~?Zo6#jF]RkhK(Xm$8l9C1iRqCxqOtb8wg2an({>H#`Cn?9Z8FFg]?Arq@moFAKG8";8p@0@8k<jp6;WPE^lhk07GW<_^s
bALKa=kDMnXSBY@6k{umlUNB,2Y#tmMu^UuusIdW:aLuMHuYO(V6V`gm2#F5q^lnSex{%o@g3$gc_/k]>A3NLk"{6nFg$Y<:HmdKpbo_Rs]:_Bg4hZx$B~x{TlnIss1Jfr3Vwjd70;*zulu`)*_yxtbdHn5XPe_N!a.r(z.aW;0g(V<$N7>X)E?x2D2KqYMY>Y$ldo)Fo.xE/aQSWmNbZXf,Y688s=(9t9,ro[V>9BYDxfM[J^1<wLQ55HGE@r6dyFsD^Enw/PxQItx1s0Bo]&L>mZS0z)M^Jwd&MjbAL~7GB9c
Xrt>v[vXc;^-yln=IcUgBG7~%fx^u6M4nNQRnI!A_FQENPlo1rDR7zdQ6HB$CI5$IrSvkkhM8`oOIU_GA#lr[|:~?hh+wlZSX"UrN3*HI.mn_zteHp*hH{qs/|9%d{YZg%ihQWokiJWJ8TDmz%DIwd(h"-T[8d<49v@{nhiS$Qi|j
P|4aT>7QyJ)p=B`aS;<FtL&G]->=g]MJ2AtG3O((I?"G?)>GcT?Kc7Z;B!cN&I3N[S+XON
Bb"F>/e>_
/oQGvh4m:"fa[^"oTR$amclsjaCA&r9Pc
ld[^JZHYea9V9D50HH{cF&2l
(pW$Fijq1_bML[JTp^Ee&:lCEpPt?c82"YVSoz1)9)eZK0&2ZR=d@ssCP[M,EhYJCtRtIFhy)fnjK#u_b[RNjlw>PHN?v0E~Dr*~GE0hM4`d*tR7n_oQfl[@kd.n2WL9mi!jp$Ah>N[~5`&o(j8BQI#8w;!!:+"MwP.
WKc3eG!HHikAQ"EZ#9)#6$p`.X9RtB;f&:w%^BFd)_xRc>(.#/D3JJ`x8f6"T_9lsAF{hz$2]Xv_#x2tH8oQkcS95v-*xdd*0(xHrL!/2zj@OaPZZ:(/ri@[N
+_gzj-GYr2m;v6T@3N+ERf".uNHbo7&N+5_4de8PI6yE[&dC[$>5_@N~IvHrL|3cM?swXe:6>A="ig1("
F.(J%v9?l-uK:{F20:p
%4a+h|.1nb3]jGQT:"^}eWf@oZIX<)Io(hG}x$giB5atD/=o`8845,AV,,dee8q~JT.[>v7!I/yI&9EdRfE}xi)q1e*$l!;RZUGCp1!pb&3[B/3*;CS,ot:F)r9*feAf51p3gk*w98E@)eq:lUTqZ3H86JKFX~<;[4e8CoVOhy0u6Ar{!-v/gBIr77j~eP,;@1RbB$(Z.`<ZgGj
h`]E8Fs+I]Bz8nUr3aph!OOe`a7G@Mlqul:5*wZL+fv;p(dNI+FZCwA*`.53V
lLZQm~.IKb;j.6C?h/7D(qfn5]?u`_,dFA;$aeJ@$~@[_Q-c%Vp-i/b.:!^$B`C)Xa(>?,P,IcR[jp>_RK;x01goy=
9P]w9e1RgebhIq[dU[X6aIUIwUqvfOs_<+pVR=}=|PSCKc.ZA<?5k>uk-eeu%e&`wipNlq[*!ryZeD)wOip&KE5m:[MixZ2eFSG*D5AA*f{;NRB!,U@-2K]g|IUACNRg*Wh=%gP@.[R.}agT:Ny-VQ}>V$W1pp}ob-qF|],:te!%vH2C!2^!6>ir10=6SNItE?*?4Z@v9nW>;]4E_/;o&8B1%)D7-W/mF:CT8%P;`D,q][^qF<HiYUQOqVuXIX
:]1s4H,|NMJ.(xyM::7jFtV^N#OZ3t.G1&+IIVFNPus30kKPQq73(92BDFiSC!Ue9}1}&KmP>904!.QCK6T3vkN.E8cnYA-U&F_6$tZ|HC]`XSDLa|/<0
n"NZR~2i3{dn"@V%Q0+[#$I#)";%XI
/U>Mnj31$l$>VFGr<)+Odqwe)E)$D($o?$?mywld;p[Kxs6@vOHZVFSC[F%woSV*ZJC7lHo+%=})y]4GTP0<}3vJ4_OF-e&g7sI_5"#n}1_?-38I`>8^2%?3*l}B7gOTyBuw935p)ZyXC9s4t&)9`:8)$5n"n.}4j)e=ikH&JDX"X*[s{"g^$"JosP0gLO@SV;J(M_9D!C@Gg-~)}mfhD!4,|O!M!;=q9Q:qQo
JQ5xd=YuMSc71{uCVGVZQ@Jf!Td?h8+l9Yg2E+Kc<25,_971cVEgH!M#t)6&,?yeE]Psio/HN2&H/Zg":):T?9Es?*UeRY
Ufn?FOIB3QP4b3>L4$9QFD1Y`oPG:L,v%jn":;Q-4T|;61o[7c^ZJWzxDigaM4I`$i"c5/lI<c=qWt`8&dH4,Tu<
j<c%8ZRE-`7-/2HV!a*X
2*C]x%cU"Z>+TCZjZE+*p4mb4ku):#tmlSxvJ<:,4V;+Hl,<<iAYfc-Guaosf9J1Z1X>;N+$1EiyB%zpndngV5/1k5WY<p!!>RO8-J#E@Q7FhQy"bQkHV)dO*.RW#"vIB@vU},k!nwPMk%l2gpMf1Qca@!]9:f<>+;twI.B*|Lwi]li;nk9;.&D4iq{l=lf6.Y^>v;]MQ7V4MJ.DZF0Lij>2E4f1Y1#m,R$u%ck>/+VF<<TlPho7ak~",.hGa>cd=L:>@4**WfObk"+h!4$P.>`&|(wIqa0@KdR`fe<<=&#4Xd/JL`5WU5Km=7o>q>>5iG*u7:;r!
Zic$qt]H1l0`5Ob1&63v`1:9dD(gMjKea-PH7vH@vOVQ=OmGw>vMh"H0Ld7jOn2_<$zAzXoRTg>2:y4HS
pG#O"bZhwL0oPksBY(JvB(u!ij$Uyxb$Gc6]z]3&040+2j-)|(e
Xywtvf;LAZm<8B>%?ypwnm?-INO:yO{BGH7/6Lh36Z-(G5YPYArHx
vf~_Up~lI%=CR,..$U=]=.M9uk_mi!VMN,5GnW)^w3umzxDZ`]%OlK<s->[Wu%dP/11NOUjX#ZMeZ80BLj,jqX#gj%&w@Wmf{pD,n,l$;mSS4Z;IsDj?]Zy@R,(dj=.YJooT+]V`]*mW9`F8Y(pds="]sI~Of[m&`vfgt;9I+9VvN?ngaN;Tm,)`485*(Vanb;fHn?$:92AvhKA*)7{k{cNxqkvF&%uaGTYZsSD^FU^>j)/",!Jr$_6/h)pe18<54Hy99oz15V"R#7)@`%UB,Q/Wo=O
CSa*~M`
=+&^QCe65VD9KI6p5a}?~T&lAEGcj+GHz$Nk"6+%;ox4F5r#?)8&_5krl)7wjVH?W2v#Uy0kiI1yA"FiunO!hcI$F_.xA8?#R4+.]<VBB8<Mc@QA_$FN{pIGW9lJ<)64G#G$e.?><g/^22FtP"B.T.zj5Dv8,U4t;QQh<Nb]"eYNZbCAl%u01k3GRUfj
$88HejFU:t5:]ub;EJVi[;$K8i=joEO@)81,^aTVj|gX4Z&4s]+I?&$w`@-}^H%.F5-b$A#fi]mk"i[Lff`(BPEnXt4JTeS``J/dM70$me=cN0JohEvyN35#4Rop?yS&0q!,R00l)0M.OCN@W_Y2
0*T4xKm/C8MnM"sh!C;=MQP^t$w#mMcU%8DW`UhIW7%s9JX7uxV89LZn5.[^yqb?`sp6M:8HoN9;XZj8=V4uSd7
c(zW5
kv~%&bn&97#MgFK<{H9v#T[Rs@Dy<O-I??DV*%o#oEWAL"@9F4a(7D$:t*&`=/Hty9eTeE]stPm=%l(T8,xU^E*EV%*VN
+6EUk/ggYnqA~1Z:1XfZ[5MiI*fo
7MJl9jINIH"NFJk]y6V@cp=lJLxY<hoxBa>,7nOlaZ[Uge/3pzKmQ$J_G5;^d-o>GviJ20O^IAAU)AF90DoW.:`oPJfi@c@K<cBO@49HcUH~Rq%`3Hhx>2T:;&l

AGBS}Q|_cPwo,67?`/WGVwLZtbf
L]@5F&J8=!DE5uMfFFIagR4TGnYX1g~/^^AnW@<`uW%^KFM5h=Oi0I@wrG@QB;f?SgHm<
1U!"#U}77[xk`Bok<e8:Hfs>j!$*:
FZp9<b&2;G]-]2iRjOq6+?<BH`A=;:Cn,3hp=[uSv@hG%3R_%6<[fYii:InBTmk"BB/p9A>fckutGDi_Gq$53k^ua%5F}LX9p?uPR^Nx(3!gTD>I90gxDC`RQc.&=Z=Y]ZmH{]waQHZ0mvMev@J)@K)vYFB_CTedNIe
/jz;aV,Z
HsboEnYy:BcL`Q/gOS[
AH5%S386u80,L1VEhj38#J@zS]b^S%7qxDT1f{CreJ-E55^-U2(Ly#8ImiTS)lpWDJ&{
Y]7<"(O,!GSAvtF[f/D=!z$LKSg07_o;(g$EIHP2.P"qE0{S+ah+{1+FOcY(=kd[_L(]kX,N`)3KFk"bx8pxI9[5>j<rWdH&eG<Uclzcm<p5a`DFkQDt?]t/F4?M[H*:r8uC{D%DkC7VSZd]$]qK3UCS.]iH^s+JL!+kK&j1"[/Z[lm0?[.
`q}+Cc8%(b7pA.xm63`R~?/iY;r5>R^."j6QhoM-ZZ<LWq4t_X2Q-v*[)NTSs7l2fbn*rJY#B`=YKDFbFMah}ua[y1$dt"Sr?ITk8f?v:Ox;dYxGJFJLyjL9p$iU9Anxn1w!renL@Cd6ms#u{f(.(%6[fx{m,#@&yX3!6MJ?y"[sC,~&L2?i@qY2Yu7w"h$;GpG7@M_yDOaG~=fo9D/Nz/n%>KTjiHKo(<6%D=f=H"ao_X{f/@NPmV(mXwa[o9|eq"^M1IfmGU!yz`f6$TXw
=nMZWLwp!Mx(y06#r!.JNq6{cx2?k<A4EvKxTq^y"zZpU5*^MY?YcS<U29r<V5B*M2-np(==#2GSAo],yeo*bMQDW4vig0glP!gNqV1)=AD1w=oCqd4L9(j@MM;*?phXs%GF6d4"szJ+k2^b.=g^m/s=DvaQr1#}X}KU=K5Y-WMV/SQccbAj($g,Go%,:ZMoeix9:J.[*(-w9.V*9QN|-bc>7LUSO=wl"kwlsW5WfT#Sa~,Fl>E04!aQGt)3r/?20Djn=/u&GfUzO}`Bv
_D@T^QAYenNyS)-g&d`f&s8{uDIqGM<0<2>_xJJA!Z+Rj6`",;3I/d0-Sxp8pudkCk9cE8^kv[y@!IBgTq/
a"bO!T]&IwU@x6gpngL@rgB0+&!-0uVfkk4L`[KAo(o`yfskE`2Kk,-z[,K}G},
H;Ai-tA>3lmoQVvnyLicRy1S]ve?h|!yPhS&nHY&NF';break;case'hr':$mc='$]^;;6kWR2N@-""*^@Hqb
{FJNsCZmmv,:gI^e{u,ga.1Ezj;YJq9O+iFMKPKY>xl=Qo(Fc@8PIGJZ[DXmkUeFd&(b^?Sa87h/ZrK5p
kO^?xF6na@R^%qZ<yjrHZJ)!|xa50:E7O62P#vcH:OyO#LT8VGVFiX)3JFM!0l4deqF0zoBGdU`0ZQM2hh/?s/r.`6+qlvv:FWPUTOynb@T
}?sLqoh+WF,7ldzf,2_w%eSs)xZYq,mSTp1&}gDhTMUUFm
@/qlvW78<fJFL>7pX{eAspJi4%guFIIqb}k~i1t/H?UpwgrIxLMhxrBY!OQn3Y8wOyS$4.Z^[2m>(Ijf*UQ+_#fKq~a[gJh_X)?P52Qx8X9F3U3oef]p1HVC=irELYTF^HqJ]p7Jwq;S!H9x-(^MwjG/_!;x3M4Wx-]aX%qrLRVrkup27Nm7V{-l,YaDZ>4Rj
evua#n>K8<4%glW;Z7)$0;>I,K*ec2[r"smETSAJ,(3T@X+oJpsSYono>QnI6By%#XkRF9a#R>19*aL#_O`KlSLSK?08xEDQFg_yU1y:E>#*.s6{sl?msdg81U)Y3|(Cl8/FT$?MjqFvPYctfM
}WhU0;;ZgPzBU*WR1,$.,Tb7*Kx7B6T12Mdv_,T.SKUS?uMd
`W2yfmFjk
N1MA
[avVK(Z)ayA3:i"LN6UVH?qeiE>[c,1lh=&uwG`&wT3*zs_#tae3gWb*jWY>GrDUExi*wjx6oyB.Wa*@}AU]%^ixc/r&KS_
#t%=OMiIdIMLyZUK
c"ys<lAS<lhQkpUgS@Wh:Qfs+k5mg?Mr3gI9v+CUe}=Dn@m5;(boUy,:+OEKyVf}UGvPp#UZJ9uGbIp^v&!fgIBj+
j*)tlcd%T
V$c)JXMHB!VN<i+Fj}e,gYgmu."3s$+[o<Yt@-Wzq*!4eqST^n@hiA;k55r6sR:,KpIylFyIppt}VG^)q6&#WNq%bW35i6bRPU4+nQP]T+=N?c12NCW|g^S%o/pxqS^6Xu&/N{j]IlU26MDv?5luJm#N
X(3/tg]GK`wAboiBU5DHocy.ELlExVf2q%bpvfZP>403Iq.kJ:-[7RWmM;D0_N`)Zbn(=VLj%W4N,6yGKc5Ij=5vcg99]vdS<q]7,4@P=njGKSqogVF2p_#-~sc:)`h=`6>N"LQV1v^3TQoEVD%kD=G@Ocm1BB2ch`dq7
q7E8D4DI|@_h+Eg7<S/efK%BB"}#M2z(4!G-,Sis7.$o`BMor$?W~fe[IpQ
7""@b"dgx?C0OVFr:tUu.Z}[lvldtbXcEqVex
v
6l?L)![?>Eaqc!i!8R$#!F-4s*3d$ayjOtYj4<*tG!?<oXUPv6&J48@muS-xn_R=1cKHAAHdQB$6uwi2=pH=irA,gl9ruMWCZ87m@@n=*1ey:Q/V_K|@shkNRVS0Cr|M$SELU1797Qum~
IAim{Nc7hy=bbP1cx)~kCqXX`iP0GTMdh,-&jYz2cZU_}h}4,53fZNo4o&A4[1P=4r]n{#.&=j&XVbMI;%!n-`d.sd]:,6:b;fywSKv
=v!fWL}aen:-[q%<]WL=/MlTrMn/g9L2Q0MD@!{fKc:_R#o8SUjBt=eI3XmR3Vw<uYC
T;%B:I]*z4;u+JFCvU7]asiT}*9*?JR*{5
/1Rr%X``Pg5_hu,%T(N-d)Bn*puI8aE1y54}r7l""JS^]I-:T((
2
TPICV7)BmS,z4X5^
W2%b_CL#c:q8bS~$|x^J-is#fNn2rWb99h;7!@GBuppMk.G[H
{O%S2jI(G8J;SY9e0+WeJM"R^h"GVICkXE
EtNV!US:mXWXJMb8+"xw;CDgCi=YO]B{H}dm*g3DtTZt7AC^r2Gj"vK=b{+a-:]FS[SndMZ?ex-7S0Gh5P,,_)3o8|;`Q*-!cC4In@V0ng%.Z]X3KDd?[3,e`j*Jw"4d57XjER&N5;9Tx{N*#msx?Y6aQo%L)AfSA
[uDHmm,6pW"FX(.2=XLo/?^3$AWYw{G5nt&F@A7!JdU[1R&UAc+.;$0!fBL?[g@9d`N+>>euy[A:%Ch#HBJiPMbox;drY.OzJqMb<-97/um2Vw8h^^4B>_/xRe.n:4w/Agk3krD0eu!2%VO$bYbp
3h<
%94ic^Jl-REyd?uRxy_=S%BI)%2m90SIG66(vA!$Y?~IT/jFph!e/[m1%sFrfR|J;HFb/L"G)4;BQT
n#Ok)i`-_~%p`TALW=_dPbOHH{G7D6?ys0UtTB[=YqH~fkS<rQOQ

xa2F
+2`R4WaQQ/bW+i3w"%?:(0=Z@2fu1oGQP^3Gs_Jb#>bT[uoOnv
!*>Y"cP}Ms7?H(jg`[Rm/.>-"3%6iHn37bsp-?re_;o6v1rVR2`-H-jx&JTLyv,rprL6p2;SJnT]/:T&[]-xDotHe3DKliQZe_el%!L/?b1rKGX}?lppLmi~S#BM.Mru
h)9bN9+/q%/?snm6*y;[
^($^6+5<A1>yKfF|:g+e2M$LCN8If*b9nIsT[v>0ybVH(+vjwCbL&5v/>$
?Q/Y#IkQEwB

gL#SN/Jv-FmY`;[vi.sVc"=SB=Qu9|[]pErR*J0.U;?k,nkKs!DiRbU!RJAzZ^>$Kcck/@3;OwKghMr/mi*nX.*S"Y"@kD)543kM2rKuu<BO,OLJ[UqZZ13@Ib2NY}"44C
FdQJaKwsD#mrL&-g$(U/1HFaDSpCm>_3vZ_jlc7M*om8V"O)4-OC4,$$#qst`fT2HoFT=@#Nc8mUr`_5m;lRz+2"KLT,*HZy&)D#K-J7MwO$`b;.0Q&MN`X
^dCD~.{Tp^^8wBc&2_C.%u
It7|!gN*ecvz$D+P];2e0Tg1B;9LNw2$<rm%c&,W;0_FE#yo1-_
EOW!DVx+oDVcq:!zXr3%#UNAM(TeGB-qU2=y!No~tD]P.ycO)xPwinO|6b?RHK
t#IF=3(8d$zbMJ}Od[ZAVla$2?(G-0l%)km8YQ_tb#Rs3[s#8qN"|Y
jOTz<LrP(A]A_C^`I~4UZY(=(1#;B#!KN!BTw0^[q>hwSH?}95pK[lv@`hl
D|UWbK$s_Di7h5IVF^&U[&PGnOm^qbX*;Y"+dch(0*SpV+.v.qg:i/*^Z<YU]9OJbnJ^sLfZ1DwhZvp6;eG1#2ya=dR_^Rq5;[P+72x(Rn[Whs50!4
b#QpB3RSmfx%*0>cO(IrN$8cLI_6+6!]X1/+@Y/Zqi*Ir"i9cY)V2PXc_2v/pcXQlMO<LW)bDpq!-;vCy?HBuS=5|2zxCZq0pe+1Bod`mvMn|:7[LCG_~V+cNir]B0+bmF8u2lpV2$h_7>:eX/-.IAaqGW`BN=2FHILjKe=@?"uoWTju~G*l]7%_q)3?D%x.}r*?;kl1N6ku
Y+"Q2XCSrCD#-CK(%Vl/&8N`cNK:3k,H,7ZQQl-_3Gb!a`SPS2XLM/Y/TI?T^%Fv$RQ|;5n:byKtPGI5*YfGH^8yU@6G!mNJP}oLJk:<(
`67@(QHN1)HE7oJ`w)`tTVrI$|QTd6V9;=>W%%Bjxu?k;N2snt9@@WgWbT)()0d|Uk@~ZuGH#d;ZvR,_.(tW){WHr.>dgJTG1yYhJ"HjK2VJ@o5Y$VQKw90w=<7L6iVo#EwepA7I>RwMP_]sq2hj4aj7?*bQ_Y;x^w;T8^DJ0M<11Y;UyuyeHC1@njDVkmy#5Lx+=@0B)a.B9XKEv&pbcwVTS]UJd{yb>s2g9
@,dSSRfXc9f|(+SXHbM@9*h,_yJ1O:M.^d[ip(*2&^6R6Y.;dQe;-(qrr?^>@`TC+
o6-1Y:o&/I+9Hk?4QRN;qF+t5G,4B,0=n<*
4qMGqB(/R9O/FF,!
z>qd4WT$oaw2nLgYwU{H0nV5Lf%okhM7B)xK03*r$7Iv1"S-aA"OBm+Rs8AB{P=A[mc
rK>kdk[v."
!CdD[nc%vK!1O<oJPa%2_t##QKtgUIb;h,&@MfAS4x6kunX?]3>J7lO@d-<1xCSH"9R1[jF}^+m)<lcE9.1M^U2V(c#A$gDBhDXgLX%gZuLx$n"Nnlla@!wbh%9XH?ZDbdX#FX#duG#v11/4rj6;AGr60%VO];QIXQ>1Amm%nCqK#-5EQy)xfsu`p0PWo>Vf5.B,xqrw6C&yfR)E&*9:3{Cm#;PZ9Tm,X/tMsFEP?A`SU{.@W+qn"!,K1V[s%:H-?MyLDr0HK)="WN/eWeosC:lNHP3HZ}Vak3TZi~vfkY6t1zQFB{oH9c-Dy#";]1<05!O(;wq8wx#=Q>h0-7Ogw@+b*~G9T+z&umm|TS>avEFd:F/&`#4%G}S,;hT8#:IYLg:mLWYmb5-S/f+U-x:S$tf|%qJw"I1`MQSKI(D`)l:_GlB.CPYPOI*<L8S/8^ren8s<.)Z`&?D#wh<P6I!8tgo0th^W9E<sy}I!Q1iA-qpq45mxZ>n~J2s_$fU2!IUeFQgIS8w?tS1^N5(?YG?(Ub<_GR2;23^8A}.H-BO"h
WaMJ"^ZDBaf0..#Zt!CH.r<Pl8X-D`WIdt[V6X<^(G%T*4!g`Qg]s*AUj5^P&RgVv1"hyMJe

1M)hVyBclj#aU7k1d:mm.WXRka$!q,%&dWoC0[3$Ngu:W,tJY&o-';break;case'hu':$mc=')R];:bpD9,|?`84(`O[=,W
)*O5;3GD-|U!:55Kpv)F[[i3o@2xK)g$pN+k5XLi&vOqpEio#T"cidaFx.kGqf7SNWtaD6yYRa^5?BW`aR#9tu6eeVuDH(c0Y~=yBlqI(y/:A/bFKr/@yR(
tt+`fPjr+*kH2Dlm#GDZ]Lf(xvrcB(L!S~[!@4b-Fd>)fxR;ROJ}45]aj}2ygjB,tH>GuTA^qVG[
lU!f6.K:!4s*sX9jwCOk7
!:Tc
K]%Z;ULXi"u,y{oM:Nn:K<(UA<Bv(?
%#oc#Z>*I2P/.an
I0akgr-+j:(jG>DEUiToelbaPpKv<mjGtw>xbyo7Pf2ZKx-_wxR+PX@yB=9cl`~BA^3dstmm[yq^I5//AbT.KYA(I+tNX^a*SQ~OGiH6^]|;oH#@N!"Jl4#*}OECY9=^":I+#TW4H:>(8N=7Zw(x(:5vW<Or(*#7e/p8h,OJ|xa<NB*]&-TjJ@p^x%QK5k5iY
s0[BoYU3u*a^~gJe8!it)f~jI2eK{&|V*05
5rO!#Dd4[CP;~(&HgsQC9@aw"W"UaV52lGw@>q_`-$2Z5r.qsZ`=L0Zx5rWt3<Y(V_hk)y;7jC-AQ]9c4X-")0<gC7&]xGITfVJgn?r?EOAd[@ZKG7L4Q?%W2%ywr@zY~COD,vAO`G]-yE&b;!Di#sUr!:tn,*^]0GHjFV9Z}`G5f=M+4HJ=3kWaY>L)37O/Zsmx&vg?
1<7~e;<cs;_0F9e[r-7xa(6w_!yl,!Vi<kig(Blb*s4rr)Q@.AlHsLk
@1pj,z]Tp%DB[_eaRqZ"7iq44Z>XeuR@aai?;9tn5d:/Tb=$GaG<r
KVs/T`mZ?G2)`oWaa62r_1*W?chJ@zRZ^D:@8oxVx,[{;X)p<?x3O!^l^{9)IC+3lvC7X#Xj,zU~5uKuum7-g_L/=.k>96VZrrF!)CSls|GIp{ua;87=V~GO7tsof9(7UOcP3]K3tzP7yLP7G/>19vV9KHJ,d?8[)+-NH2e{yO3lLX=V_5FgWgv/*zHLv<VoV&hRHhRsf5DK[O4]U7C~OL*t(^jx3WvBh]3e:j+h9)$L&chFD{,Y`m.3^&Y~
J.-paBJY~3mrH]E@_2t*vgvV2kipPAm=_T_?f4}G%nPjS>PF=333,@%;VV;2!ho%sTSCau
`i4?<"q6Ml@L8k[$?uQvqSQGR)H#pav?AaWGx>[~Wt<+*{[GFITXGJ9R(w@#(kSLb9-EdV6#iPX]wxND&n=ATKY"@Y0Xq"8qUhPOQ&NdlX`o)
8;^}r1+#Vz8>8"D:6qMb4ht<E3FP/"jnm9Jc)dmEvs,1d@o-qN0
hC+#D%Gl^EKw%*AO*=S(XllO@PQyx@TtsT_r73c#*RM<)Oy7(BWWPx!SEKs?*PDm=C2C@FT
QQP5Xw:i>R^hrBNI2R0I(JTzouA5E898,hMH<r4E2_i<>w*thvvSFK6*r6GJ>|$#yC%W87D@W;M_C,aH:GOF4MIO3#X,v8JFnI"kE3z)_[P0;p/.cC,!iy9$^gO"tgj-cA)
N,l#/mRvbt0a%ue[P@)]
_<c8}7I_?E[kflJ$.y<kfWnDUG01p%j,uK
`2I_OSP|cZ#HV|=S<VkT7]I!I;Qa^r^<*<&,2o_[)sL
#|.2>;,~ax6WP;(GUgCRM1W9]Z+PHv8xSeMm9s2($z*nEKsJA7yJVT=Q-,?ZR$Ld;9WR8Pd.RJm
a$!R8y*%bbb-6L;~c#<LsFlO1WdNao"S[nmc4Yqx%O@p/Y%+D>Rndm9f
@9M(-KpVX-{%n&o(4=E>fnLpDdS5co^&_={o>S,]}!*3:)wm?%W)(aRm**pmr)Vwl
fpD.p4/IFmcC%)EszkQq=%lp|p{q_LoS]e
C(k@?mo;*W>W"RP~*n!KQH.JbfWgx}me+}:[S%
=-65oX(w,!Ojf>V+?As4<ri>|t*YSGN&kmt=^m0+:A
s>S:Nc/O8v59WM#jO1m4"O3&"+w<Uy8HO]Fs]s:^h{J>hYZ,hJck"C&b9)I9nXC;Q,F|l.<S>bPq_IUrsCK"=g&k^*&B;n0x0NNAn"EEkP!/s*)>cqd-EU.79-W`vF2"qQpBl}l*#4^j;Wa!tqNpGxq|uLF._n!SA_;bA]:tV+GS-)2z?nql5?pHjplM(M09:uE~#,QSm(G1@Y8&Df
~J*v7PXPFZ2<uKbY]0(+f7n9>adeS`
QWy9;!wYWsc%,b9
jS=E/s;}Mu-)T^>0.~3VV:C|Z;:xriB!r%#@#(2<_j!e=6j>Viu4GU$k,Vm/o@E8:NBKywx93I#OM)=Yna!!PUKY#F4sZ:E0IZORd0hO)oPnn~0pYF*a$i1WVHAQ/MR]Sqvx^aCpMd=9k~t*`Bdj:RtU3Gy.pio:0360(8eh>%IIL}$57NX/jmYgyW;610Tu]>G`(Wb?Ko<U"Ca@I_KC0u8A<C0=Gy$Oxm/j3;2q-&<%#{pLnYa:RIP8.PDLJJZ%[FY!T@Qc_!T
QuBo4a>eX{y!xkx@<+x6ppjS!$/C+W
x(ca3x"Wt,srtEWYaoe>@]|,^`gfz%zM|be[&M*gB!%*;$zjAC01qio$XlxeQg
T/FVPfeY3$Wq#+O9FIc]>m*4c[SqL^#l(a"p[Jl#LpS2(5;ji~
c6!9[(Q;y#s_yw4J8O3hqIF=?3Uy[>WIU2@97UmZ
>{1!twC-XI9=4US~DP.=v[XW4VNH[Ehhbq6&I"ku[;$/KMYP6=_C:6/m)1jGk~f?.<w8u=h_/n@,w,(gn);,]eOo>pb.eh![M4++(t`x)I9rm(vCtyCq^[I9TZ1#TY!4A}9ueztXwxkJKwjCY/S/.WZE+1#-n0;xSktJ
ek%Ia)It^tj)6j2[x1!+W*^Ow":#<?/.8dZS9cnKQ"o@^50uD>CmfX/1QE;9OEU]H2vem8Htn"2N^0XvGF&5{he>e5?]Ux*B+A$j#Y.48UYwt5^D>pGcOWqo}2"bFg&)Aal<5*KN!V[1b#76&*1"mUvZSISOk0htD]HJmX?7sdF]/_j=iulWAc-mp2rrh1=w)#v0X/TtjqZNfVA%w!l*7aT^_(B$E?2.7:c^j/6:T36`0A"r*^6lb<O`uVLs~o[+]1bk&LM
,/ks`.Ek"a{/MiZn.my-v%
b,*#tUPyNUQQ#0WRP|ZMkX0}mTT>#Yly?vyYP5,]fT!u
"JZ[ve^IR?R(1l?YpC@mLGf<$@^SlgDJK6>FS8X"g3`G%nIC~@"*j%KJR1336W/gV;PZQD.[A!yN:4fk$6z`6-I`<s"E*WMbR<{5j,~b,+t23GK*G`!I7p$O!a^s|I*fvYk?s6+I0[oG)TKjP
E^#OBH6E<b-du[DYhaPKGW^8t%@j:nv,*o.7(=c#C4s:bhpjqF<^k9PR#--ATDx0Gm^9:E9j~jnD=n2oW9k@=AH:r_N={IC@x
}dQ2LsV0B9|O<VjMpN4>;a_J]o<+{ql$:L.m.gcGU-K9iem2N[Whk>pb>in?:_M_9osnr(7`.)TaLesJ>T)/=TnkVS
F|Vj/u>Gx{#n:1Ufm79[d,HATC=t>"bB5RG.P))B4&WW</<df<D7F/^^%
d$.g&"^uF;r=C&U_+$qdHGdgsUi)3"$Sk@o>R$;jXb>ypPr!$h9F]<_xuQC3*/O^j6Hlk?fa]#u*%+M|J^U)d1Cm3=p/Z^E5s
q<P!jgOslVnpSajtuY=XYc.mx=JPB$"k#<)X2PY4,(jx_3Xi]GBr_G$4e%2$yCu51XeF*twEqJ"mM"C>wW1Y_;5L)w^ia+P3/oK"YcbV+Xkt0o5$M/
}Qw!}D}%,OGI)rlWI6TfhZ@-Ia~w(9G5G&Nmsw.jsn/Ihr2oIDdM}WHJJ$_aj@O"zv66<y[X|;NNtOf:nGN(6Z
W9CbV65G";%VT*I4Io-lZbM+0k<GE{P:8)H?,_*5IR&jp78"mTw>=~a9X_N,ImxKh*xNaQk[T59J[,6^32g<(K.d:z4,j*(}3`
[G>Hw-=[kY8E2ASt2q-b;uN+m6OnH=A2iK*P4jfk!B]p4[Xe9Cck^Rm_BX{Rr^KZ}t9k4sc$(Lqr9f-Q;e!A#L+
At*oHgw6z@}3Vp&AZ7xj;e>LKW]!3*4B!fne.
s^=(OgivT1]BxmzG>$7y&Xu+f=kR2Xwy4u,wL/l8HApNh0F,X^l,@/51Vw:QMSn2gO0;_is>a`y?oI8R5uD6bRV.^sXWP"89i+Lohj&7wq[f_pWNTG.4(u|t2DR1.&<[r2]Y)ZrGggMf_5,)PPA/(abN=>Xk}gV6vcXqmsS]%+5iF?,t9gy=j!T.a87Iar}@5EBmtp97|RXl+`t"pE^M:@oJp,"k0cJbtGX5yI]SE;yJ>)u1jei"0cRs{v/Swef5RbQ.Qo
^2q/)qoodE+3M9lbR`Mr>2t4+hVgZw!X^}cpdx564~^)h1[K5k8AkKF}!jSbT(j]9:NXZFdy,*w:V)Bh8S4%)TUh7&pTotE(H&qVjmYaWGLIcgPA?8Ivvv#")G]^:$Ab^+APX@T,KXJ58dJJA31nt~rY7WbbgSN.q[t/:,F)nk(an*h%[oI;8K"IE~`6!>!8l#
Te?$(-i=O,T$1edY72LB_xvJVl/iXb8"~Wtd
NQ.fog]Wi9lD1cL5NZV:271j_9IYA85XE&(:BB0xmn91BJBx/d=HmTiq,j4_3)iyGrfhtR06&*7;^3;InB,):>b
R.0&X[-"XQxd';break;case'id':$mc='-UF;riEA`,|?c)T;7PXCd.b^/c@D]sj^A<&6q49gQKU(g=TpR9w;e[AyVK%yJC%pWTs:
^{`+8%bE=Huit/uz3DBIPQ]]H?K8<z`7L,`F`2a9)k]m`T`hX+iI<9qAnqmK1*
w?eg>N;bW#Ajq>q=H)$i3
{[_`fo@LjAWB@n[5vA{uVoBu&_`m0`-Ber|W#F|rkn1</jg52hZeiyRbxBW,9bi;AAm`9=@qv<$n3)~W5y;+3x~mZx
ugXAK%B][cw}c|w>yZL{Rf5bPZkzhrF!ntya+wnX6fxQk2E<H1TrBYK4cpP_A~wd$~>#%s_-aSr;LY_%?/^CL=FW(blSCN^g^4W3B}L@>e`VjgPfm[O>7om^ek=A<H
bmXA5V)LXeunw]X2)&3h>Z+0acBkJI/e;Xs4Mnlk=H1?_UkYiA!@y^Crry0-9t^InCQ^A*#2//2JO0MT^3w0
w)I=E%Ckz$0[d}gDE4`oW>6JRbr>l
?6"Lm0y*pG62VFU/k
EAcjBTK|De0_]?aVEGbF.Zv)M_[,W4dfxd!o*SkUklJ4V&>S8n`i@fnBfhLpTDX0`Fm5udkBI^PqMK[#tBWwwVe@b,^(PZf%fG=xwgQ3h&nA_#2M_5+jD[B~.@c!Hj5}NE0tQL@
.{O5KT3Q7],gF!,L7lf@,kMX3nv/BAe%*Rq_dCe!W>SQr=E-0zgY5}GF+eCFIfNgK,aE1I)dNrg#^rj^GAr@@TyoR
yZhHW4auf+I^a(WDRIslYEi6G;lN(~e1*^r}ouuvq"^`RNG50xe6.5F6Z&fgcFCv+p^%kxLTe`%`h8P9K+2Gu_36FKc_A8$KWu^Q+@uK8;xNr]GxQ_X:-`qP)K&Lf"4yW2=5=d--2#wz9;ZAuEIVmtX<,lI`S`:G(TuunRS!r9o+V;0.V@f#OjVPq0I]@PMa;<ku.iuk<EtyZ^Q{_)[*n
s=Sa(yb<*q6g?~NZ7sK*gz2W)|@6$u
zL"i2/Z7K)o8HH!.G0hNB^omcJmWCh|ZKLy>Z%%-nx>>Pafb-:s1^^7p~Jkbzh=m>4P@a%BM;0Jhb8oJK,7Li[xDIjH8MQ0Q*,.J3-7xM_SW:q5#Gyq;R#="N)Ir1_")Vrk0b7H]3EPKX#n:+M-y5C6+`.Ih
)|SQ:^vSEeAP:JepX0Ze)|Rdo[)q>FG|8Mo11LQ{03U
E-8zDKTP=9^[&gn_;F*|_DAuvvDLn[F{x=t91WY)
Y`1e]6;OmLQEe_MuijkV|*"tM@Ir*CBU6wr*avnwa*o[:?pS>xHSxrv.*&u>ly^BwZS:ZJKbNOM:}P//H,Q:(mvfluzE;:u-VV;xc]SQhwRq$$Xd^Fr)<>+`1&1UFUN,KTRBXG+J!?M+>3z&-g(u%+MOSd)f{>WkiVY;<M:OYh?6$hgs?i,D/>.]%n@-+f#=^,;Co[B`l=z3
#7[AL0bL/r,YM^3c`pAq,[
f73B{o:KH[.N<@I
u2Lhh@NGPgsas8AS>(V92UPR@wh
//(E.S<:-l!iYA?tueK?(TMw/;P%$1N_Bmq]NOloE!U0Q(0?$#ox&i|/1?us0*o
s=.0g$XT8
+N*1Z!pi~"$)cV+FOi2E+P_,x<098)>i@@MX^Yuz%$((tR
K@0GL&02$N$k3"F#YMs"9%$bM"7K;P<2c,IA;P*5%P<
VQf2me?beF<+gpMp2/w%S|IAlC:C%ysvT,$[kMR$oaJ[4~tp2Hnyy~-o8F4_>X2cl4=7/UQS0-e,M=8ZQG%5l3AQk_]Quc
+6M#RB?Km7Y5;pg#N#b,,Z]#U#Pa/euV7NuQMR_I(@<B-QOts4:LRY7>-ktvO9q-Tj;LISy^eB=7EvyOCd*)e67!?Q/+si5VB2mBM;,;SGOVAU:(,"[b$r&%?A-<Yo`=NUfS5Tg1`tc/Y#y3EZXha@)Za>B;X"<eQBZDx!
wH+8&y
?%>*>Wt-:1iOFZ-qApO"ThELhxFdf?DofGVGH[;L4Q7Yjr]9c,6qx@]d+M["b-v>0NEEM^[n1;Mx5l^C*nS<2O3KA(*tRU`-)QLD!A-:SgoNyfnj$ASWms$GOjPQfoj
%KH/hNc$Y/u@vy,HN1hb!2*1KO6y2E0p*A[mq1p7h#DBc:|?aq,-j.9cjsWQR$LH%=_uGfxd(Lmq:R+G1jiranI@zw
ax**$+wSK#*QLX7S#F[<l;s33b9@?6sj]Hr[EeK*n1r{c0"5S.cibKhX[3jyWe_124Vp9i
dTy=c#S8t%Ry
u5gJnb7<WA-d:q<)CoFzjv^AAzinlTT^VA
,N^GGXYvZ/[byGy/z=0SLe02yPgj
DHbt,E(<3fuSsifPr}l3M$1q+x:06obS8LJG2UV/E"yDs0_R(-nWjx<Y=g*bOoKfRx($$V:y8P[,ooa.73<05@un)6vm6Uz#`o9?O!I@nclwC~Ec@*6Ntobh2*8`r*YefGja@q,?6`&OL"f./;y7"*Rj*T(k<Tt}[q%G!5_6"P:q0o
p(yN#w)_82hP5??U#f]MR"
JX-"?fU/uD;m4-h#]lbnqOX38tpI)BsD*5Mf+8Ta)Y@=iW7$c&"/cX=^d:d*]b`3--yWb#&_@=j<`8"K"5NdQ<
xOJESySN+d7"=tNms`r?bM+xACk;C#+Z.#U;,JQNUiT&7h
IB?u&}cg,(!0BqnTI|S9e}@x!H.G@QYBC(>vG*yrRcp(r>Cw91e(C+EJTPVwBE"KWD2X>Cb]ufWeE!I`K|$)&Rtu"n.l0R4XL8&w@?P~N48*sPim*g-(v{n@6xfGN`n(Z;J99UaAqw)7N<-Z3g.J_hV#WdK=4)P0T-#ds|LP1]JsK=E
i;$iSjVO[o!xMasQ[m<07S&RC
jLCddS^QN(i5I!6TfUlYBD3C:bE
[Y3J54<h,GO4S9PVsLyP:>IJ&s.9"p[@%XZO3HYf
/s5`,]G<
DO?%V><NAkFo+,/&_:2CxC^R^:T,;Jp&EB#
G]v".K[30#c-L}g49Hi[J<*9"H>hV?k?Df-*&@H{y$+pU2&+;1m2R8$-#_J$HcviIEs[_N$:VPMIO#`EoD)!s}X_<5-k>ax*u$6-oM(VY4XX^;Il*cY4GXVrC,=EZcrb:~1sxe<1?oLM!KseVYI5VqVhgWIsvgt]17tQ;NA{S3gPX+x-_ViFvR
(+iX`#-XQ=9E}`u-dW+Yat]YlmK`@S-2q)rk$!1-),x({gp=Rr%[wPXBa`[O::>91ft/}y/<wTI[BFpf|Ua_K_yGMc}IuF^ocBhY19$)#J-u,1*gYnUiYD>$?dc;Y_a9?&by~ew>t0|(T2?ea5i/_YAs`QJWh%Ed(.R)0O#$r!q86$bYeram%m%8nNw?gA[nQ-SCNS`pc[;]#S!Go:[cg,xx`KF<#$GZG[rE;C)MyYnqq*"IZF(iKT&Y7r8@UOg2&>n&Ao2u<#I;G$H2lI=*e^6Elo`1U3B;nHgo],tY>K{*{n`Ch2qn"wy"JS>";<rVEEh2yd9DSeeG:_LxN4IP`&bvV;~vaK7Ar-(7:Z$kJ_SF@&2KlUgSC<00$)2S6&qII<6oh+8Twan$^r>5AR!`?)$GX"88#JA4]37
si%Tl6=LThJIJ_nNja_19W}=u=*>O5)w%z"==pm-5W"a/;><Odnp.XWC0#nGz)dk/=SJRH[mc`/HEug%[Cz"=$t>6*[(Ud6DB7U=WT7Jg^;XeKhY@+9,/K5<NpZv?xyrkR:%Qg/82pHqs/HSr2LstwZ;zq:(/#?]uEP?I]g-
6L>@So72J0DC>v"!SSgy3g[(dIK0n%Q4EBd6yFDn;/AR)*#|p`0;U+!?M_%,[E8l,FtLp28:hHj(w#uva?_kRf/2v{B>t#`gCVOi1EAU2DwwAHnFiuq,OX769qL!rKcCd!(i<K#"fmCD+Eqd".U{,>*AV3V#wR8:xDONZ@wpZ8C~74lkz&dN9,xhX4yEW&g,(xXo4`2[gfM,x&dvS6ARdZNq,}"5DN,lkct4l,*K"[tR0A8J</">
B)Qes!htU+o7k&*Ff<|"6(pB@Rj+B3)k^/Uu+[P0Y,v_
0;+UqlHEsE$aCK]K%-Pm:)0&e{0}g,jv9:ingRgh:/uJ@MlCLwWqng3~[t_M?
X:Tr/>kLr^2MiFd(';break;case'it':$mc=')]^<-hAmso!>}3pfH<zKZF2UMV%L&l;(+V}<`nAF6Ms7;#nG$_D<jqLpn;Gy,x>o$(/A<uSNIwbJq*&qmVui{"mNj"CtXJ>wPRsusJ(+C[GsFXRX8`y`Ln[?R=?c$i8jrI=(xZiv*moJ5pyHBX?y07l0eF@3A@8
z]^HJWnj,Zy>_
kBiAT]^J4!Jf-q`%UGm4ghRu4?H$4mMTr`3)by:Z-sd%s3H=-h#G_]:I3*Oj#<JEQU^E,`PfdKa:e:_C>c|c|[bo]Gl[_ssy.a(PMpJ5U*/NXtQ7h51FASR1t7vc<Y&fO=Hhq
_v4Z}20%tS_Izjnk`j3Y:uYJGn$?-E8JE@DaSWi4w@z>Hjg$~Rk]ijn6q4^/8n@elH)D+Fi_Y_
3ryS)nX{TnhCl(Zb%w?@W"!{ml1HWr1eC:m`b[@G<dp}Psl&W[1H$1LT>]&X3|s:#t<U1We{U11)^DQsFx-XgWi^i6h/ow.L?B>;GmndA1JO)oq<Dx*L68
c(.iBRG^9AU&<Gc4Sk>rZ[GjVAAx=fFJ(w;J9JCHo2]O~c)NluW2zRvRm*{3X*G_-XUrtFybI6]tk=p^Q%f)(R7S"Gk1X07A1j8Q@)3n"nXFW$Q^ma+n,u$CWJ)1[E.2!-Ivu?{1{0@GJX3x<*eG{$>H*TSuxe/tJ:h<=XJUXo`1Xs8H345USKUW;qt5!L]WrnVP)UsvU]WpI4s#Ad4[B7OhEDS`6
eC[plE
a)d4@mKcee<=b(n=NkrI!jF}FM]Var3oX~@`im?3n>@*NEs/VWbfd}5h0ylG%"3"wW?TB)^5sx5MF|LJA&Qq/y]^m^I%*SBjuXiX6Tw#2<(#qK*YQ5lTS!?W(|E`ae4o1G&^b8lcGKADf]+BW&VOuB*,pU.m_f,?Deq5Y<VbM"OvRpxiwO0;NLud7qG/?bHrVC]rDrhdDM<I^fDEbO7tml*6;_D)05w+]"g>/GGHWHjGEDIXad^f<V^iV|4[uR3"ZEH+>E,)wvEJ/$wml%sPp7mEU4EnW]W0I.Ut@_)u$Y0bDVgqQBU/vbscD#f@#eq7jAEfjEhv:Hs#2n%Pk#`R;d
3JN,c^7g`*;PxE4T
3wUtVv90K)r@g1`28Nc`9VIpPmA^ix+uIfeX6p8AJe:};]E#6!M+MqQd747<p}+8wqU0wKGsXiYENbgYk*[1/s>Qo/>Uk_kSY<nu:
RZ9VfyrdbgL7@vle5WUlx00~2)g{.<u~<eXSmo9qv7dbx=36?U^bT1++fgFR8J_b,7[PG6^<^_l!=(;97|`%6jvSBWh<VJ@OPR6[y{Q=f24bV22svXpY>>E7r2KM_kA`k!]U
8v(]N!2)TC:JsM5e*^<rjaf4,QMDLttBUu%#IPH6WYxuc]Uj?B7[d+MQKl)Faf:o5]p]RDyog)fM$ZQ(S5^Xm_2f
3:d<d;8^>,=YjKy!32St,icB?Z!]/jtQ6B8g5w!>gDY8JvJ:e8Ch"i]M@#pcIPVY*I%75kxx];,od"GN4crAjh8)A]2rM#ND0qfkD4_5WM61L|1L0^Fh;2)*4-";#klV7~+hf{"+#~`]PFxEh7B@reR21?$h[t6UjVhCAJ$?,--7"He/"rXo8Z;,>nP(.d$r)Ks#0:J]f7M6C!hb9@O-"{^`:+D3Fz.y"IP~*@y?2~RRoAojU-mO%.Dqwn0-*C&A-%JcQaN*=k+y))o$y+S?Z@dB0DCGA*_u/rWu^w@=QlN)d%Q7w?lLpwJ~[0$?8+%Q:ePVAVqn&RTk_hfU=`KUqIUwO`mbr&%*BG7_I8<@O=D^P$o95<;-4KOP9P.)p3C)d)=$
D]`7L#->.p4eXtX%*>r^c&$,uy:Rwus@3e%9[lzCf.PO9861v"5"9+YY;8i24ofWvxM6?E5>ddc1y%+J8g=?tGJ#MY:W&uw)DQ3F1AaA<,o4{l=XH1N;G@BgH)ydlf3Fep
tppP-9:[xF!W"
EKQ@7QKQ.t
5p1$dx>_!rnw=!0E~i9tiUBKKGxW}!3h-&o`_YO[]+%^"xKg8,>/SOAf(G?*MLKHBnwo+9OTp-wobY#y|
)"_pECFH%
rxuT+Ib?`]6kIUs9{T7tp0Ns`<kbYLm$z)TQzY=4Kwh8/XQX?cyeN^CE+`Ho}Hz8`OdR]8wr@W@
#IV8&3S_~4{Cn:eo2-Do?TuvIK=S,&G`~N~4&$iM/pqc::x!SSO=Y,mY{X8z)QiFCWRjOFks`-"O<VIM+:UgB+/%h2f^+[N7zu!xCPaIL?Xi{T-w}G@lUal%CyO)#T@H.M{G
=KWKe*u60-csc:61iLs+KO_
`?AB
s_}:46>+*V&QQq4X."&JnUS]vt6_2%o$oxhwAZOwXT<M^gfb11cn31T)"U))>]HL{[TF~C<H+d3?+#!>",DN!s(F";|H)q--Z77s.vi6cu6dA(Dy)[F,K;xQro~26.N;o
@c|GMljE$6*(4M(.LU;QC%8U_]we%*SRY0&;5A(H{Emu31X
edtvmNCd
(u]o/#6_yW!S@`4j-QLgx>nfVRL8f49=rMa]#AqS!_-6X!^;:JdQ
W_(2|o,ly5o<)f
WCdIvr*@p4^IP-).U::#T;cNYSH
V/tsqZIGA_v,vP!>I)n45@.2:tA)(B8[gz!d.;/nHGYyqw=ATsoh*yU)q7OSv6d@S)?&@&T
.q-<,#1x,5"e,<qK9R=p]$Kimd;ua2(1^FOa
*6la3W]JdJAny(-N<>88pv2IIOir:9H:l@nd=$ye&>xk71Go)j`h52c[>yo#NrT/8akDZV~4VAH9e@4;+[cI%"Bp}"
&<%aM*1#I#_vaePm(^&
:<%C!7rzFFRxie8E8@s#:AOOZKM{%~-%>y(mc:<rHV>^#s&bO0"AOWEK#[PvXtA")"2!RddsBAo#.KHHVQUA?G)nd!bm#W=DRDs?!J-5uz[mGbkq4yt@Js[xjS=
w1HHvavjuatds;r1I*,iGNvjS?eKQ@K1rS-T3&Ufh5d]eZO_B7TN?r*OL&B3yFsXb$!~u=-<0OYkY>%GW;[[`p.m]!Wx]%v%I?PN5uMt=k=adpd46PPck(eYD/6f`h`<=,I,+T(6j>hjSa#eFpY!U5D]&OrAk_><pq<>;I#)NiVp9GWUcZ8w?+_/fpMgDi-`";EuLCI~2"8=%^sk2x<A9./~=gK9!)P]`2igSgpb
xId[NRH37?M+FY+941KTn!h[A3ysI+SH:swW6$PaABhCu"DU,VBY[X%Y`t0GXUJ[1yKKL[A"10
"~h!U83uG]6n[>h!p,2"h*fIGD7SnOrgC+uVgyY:ml(5bX%W(<P{-S
TEgXe-o6dhx2:Sn^c^|
+l^[a.I.2cBo29oli6|#lAkuv*:Nx&G0)fh=~0YZVDm1oTxIc<Z.YQ&6>?NP^C!J384%;L#U]h0^l%2K>YWu[I?F[7e,/YdfRGcm#f8Xs$9JcR55&!hnrJ9qz70^d[q3c4aGN%dT873,~,{CWYV+c4_XmS>*k.<$Og;vcvl"Wj=fioQPnhlLGd<tfG1T!WuY
dZbL+P5;MV)rNFmvrgI#aW91nG3>5;F`ui=[&F"%>p?Cl_W;Qi_
O%k_;%#Lj#7_2Sd)P^fjd)=IcLZ+Z).IAPo!_lrH,}-;IT;mKClw
lb`"3"E
D*c"f1-[ZL4xZ3O[f3y7T-Zwg4*$E#&omk,TPh/0Xv-,F3]d-h~,S4lYdu*BtLd["F]sJ"-WYv9_5qnqd?R2yMIGnv]0L-d
29v_6K/(*Cb[X
A,n6-vQJ9dyd[oD""pGb^^}]rV2AcV;fAgb"<vk*1Y_Swit`0We"WV>jMy_lD0+Ly1z#?NA5JH`#4o(X4(45py
hdFoR~:_$H!8B`1KV@gUo*pck6N+aHu^10KmBwVpLsNTsiFf1ysq%.,2NWODbtSsMIBY&5mi-Jv=P{);B9k)q|jLS2u%
2o=u"3oHN2l?/.]q:iUyS&(>y.NWxqhr{L^AqUxp3@VrigL)En~ikreTY79+0Cj-ZGSJmR$0HquqLXc]O][KmLuQiUdEWTc4muINO!{T4:@OCMCgFs(^qx
F@#<WvL1)uBteAu8NzTr&ol`[Li-".)W#ydsHQtvNvZg-0A"e[QXxQ=#2E0}bTWa_HiXZf#h.Ti9Qf;^1#I:B<]v`BSiI636o]+yK0)hwqZy`{`sUlFL.p)[kQIFDJm]R9g{S9Ga$(CkWBUSr),[&?FiL_C]7r
D$5M._1=iV;s|tJ,)NC!oPoaww(8I8-HMcb;FH<]2_yO4[22RhCZo$_<[k_V_qA?3m
&NsWx*?A01&F/t/2AD"DY|)ruUe-);25eY+U7}PMG%>S5B/,_k-Zblv6cO
0&73U$o_itf[M*>5(y$YGK/nMx#XJ9ZuKd-jT?cy{]Q^d^idjt+_9*/Yy-Y3=+z&b/p2
t]z&$h';break;case'ja':$mc='*X/;rbos&B~?yN&*CW9jmP#>6!vS5C24&mJ!G[j2^9cb/-k,KNIu#%X=i??@5>"T]gC9
^&U$6B9]D}^Gf_RK-Axvyp5vu[D/awETv.bqK$WpJucsx{EJ?Vl/J2yjlkDf!"mJUOq&hdmr]gC>bI6?KWUa7)D[3Mq%R>M-iZE8r0v+?oW?nsn<u4.n7nq:L.]
l%AUn+9@GdE}RQfaUaplw1,z?4mm91hzPC^;^qumf5`(ULH720jtl&.A.i4%hFxGv^Y&h,"G,1B%G^Aft+5TZ?w+.@V?ryh8Du+rXocti%Xis+xHUg9cmy>2C3?K4}+.EZU1l=gQQXFeA&qOTmE_n=21z&,_H=bPo(l!y5HQW^EHxZqhwF>uw*x=_8ct7-MA_`s4a6n}7h]`=CV.[Q?V7~EPtSr"r#YBo[5$gY2Oj^c[KqT]]~?-h.ueX33S`FvK@P_WA;vu/-L}((n|o*@pYWH!ioVTF6H>0j89>ys`@/M.9mIq#.5}jmcE+l,K5StgFY*/b.q&0H<eht#vG?8ALcv2EbilmXR:XA.
tPgVbzRPsCjTnbp%hlM)h1[]Hxx21b[?Oc[3Qepg3o
Yk:aT9xCOrCAOG]J*hf.FX=),F`hYGe<Ho2]b3F3^@y#xSC_J>80g<S/msRfxvTCy@B^bX:#P@hD"E8*r7e&E:n@@]i$kvW%?/<>L.c]9SA7%HbUr@hB]";?/RQ3OiF`
Px_S.a7:UDl+cpq6XP%2]IDhanMB9e=4k8&|+Pkz0P
XNx`<v+K5Iqk`w($co~JJrt9a=o2b1>ti%<0LM,hZY}`J5$.C>%69bP4gz%/^K<K|]nV}n}S,tOX_oDs
6;z#n=vYMv9a/
?o2LBAvvO*mnr#mGKjb6[_@?m!%X/_]$W@;oS<k@j_SJv3mPT*,AkU+KsGO;a1mJ+eq;WU4d91_4re[!=}w8U+3lKKhV!9us91
F",oWv~)9cj3p678~?
l]?Hm$^uDIVw<kz(%R:=7>$&D/Vv_"A620v^m,UD`G!ry0$+]E>pd9wKe+:9_~s"t$,[_uQ.p#LxqH]HansC@!-eQB5HsI9Z^xGbe;-ybGR]C8MAZLbUOwQwcV6wHD:XV!^*H>-ocxBI[v^aXj7g+39lce3he}KKJq5RT=wh-y<{SeCZ%w<Oc_bPq
vD5aWzDwH:09[7xJKrvpgsV7-5KK&qPZ@3-3v7:N(QqVv3O=ss[NKg$^D{cnIdd,Y{yFHN4#Jyqg8phbF@+Pe[FDQ+Vef
G"0nrbR[f:+2=.%}0*5on|TtEEuxdM`b2gA|DvGtuaP]CK#N=h]%rK(nn)6%Py=KM5
Eju5aaC
zi]3LS")`<-3GOll84^_F@E.#.=443Slq)>4kK
lXHKZF7Fk867-OdQ$J=|7yq`F3b380_ukJP^c>[)*4N;y4/Fr*f8CpKBRDtq
sRZU2hjV5t.&`?L(GZ@Ed^0,6p,<}c0p9Y6@[NU&&VQ^uq$qpQjZnbad23/n|"ELSeMeQVB+R^?4nDUrKHOKE6$H_f"X8pTd7GQ
Hgz(`90^u9cGD_zGFehEz,J$zr5tk]vRTNivi>jv4[q=Bc)TG
f9yz);X7
h/yxlrNUJb;ep(BaB$"]I_&jRJ>OSCKq!-r,s%fW:nB+
Tg`n/a|;3,jx%`>S"gj+UiUJYZI"a>eL{SOLSm>Fox$N+YYPob$
aUOZ*O?]pv*"V#`(4jUR1&qVAv|;vols0R4xG[x7{7~oNU4k2i?N8S
A.SZ;D,!2X*3%*u^osjtI
^m/a]3bS@~O76kZ@c*v+%+*0Z20hqeTg<=T6tt1{3LTa]WH)&=3uwnoPwp,gWD<y#*bc9q=ETC6U/suRixnK%0Zd(4s2K2.KnP>
:qAV:6@9*W:M@weDA{m+[eCSu1BRE1@]i08|Cuq7FXCi#VjL6H7BZVn"
//E;[ZBi1.@=l!}b9_s"qQpm3ta3yynWjNn`}>x;$91mMN(lUjR&C$z;UMS-j.S^;B<5kfE9-N3vmnSJ+-)2n[iD;,iAe#{J=%T1a@^8&);W#@.cmB:9-k^v"(B/{7Ua[n"m0*"C|38MP^p6:-g["WWbBhP]T
g8_bseQ>[EXq@n$V[<oGSk]q&sIIehB(mx=(}w=GVUYi|p<O@W3nK%qbe[i9/plT)0M;sHgNp;QI$%_abK5P!s93@-j-35x!Y:.:>lE3
*T6)1>T6aB;t`#mYyJMT6&jMrn?ha*KGp2HX^!(+fa^jEL*#&LsT$kUrkw%yg4Y$:yZEVsn@DrqGw2%cP$U`g";G1-7K@Uei<B2SGR!4*XxHq(qAstENj4h`w9mN^+iC`>bdD+]#enju``*?D28WvBj3p&DiZ3+pru@{O5`f";7UEh.Dnh%iy8g<PY7Kq]&LCx/KNAK,t,u_"]hV8X!04D&6sNP"9!scByKIt4DhGEh"hQegf;O}%@YWA9DK:VyAabW%sgF9qsVzk8dhji.Kd:jFSdTq")&_&9GZ$2K:#ggl%%9Sb5D}]1[Fn.Pv0iV|cRgsq(,v;@64E*DrM3&O*+^e2I=.9hH]a`;~r"B(pwa~+7G-qnqTh3%U&z2uGQsNoE2[ui0pg3Nf`$XvauI"f":V2F(XGZ3[!Hbk=)>7y3/f$I1A2=K#nh>!=i_$"CPqcl?ZV$TjpAgpoy*=#0Q$rPoD%(nQBq^$Ud
+0lSp9%mME[.8A$ab=7^U54"C-]>s>&23m{p~lEv[I.BSqILWVPtm5]i2lU_OhRP0>:W]b%ga![tXZ;Ze&~=N:lk"d.]GS{Nq;ZV7vjRpO!n.Cg8V*ga?Zj6x1*Z-5Z-[Z{cn)c4Obi=H1n"V=l2ek+>>pW65e+h
]28*A`dpc#rFKj1Au*AaW"I>J!Z*ykKy`"]%]#.F6,)bVIL>K#uU9Spf=z"4$_M-O}."-V5&-_S;<XoWxAIij4^>U&q;d7)c5|<h/{bYCt8$HoS{?++D
-6xWADE-Ih)NT9i1-pRHzdA.zbwe;10a;k(WrSM$<A{+R)~@*1"ZEV+`W2td))[MHsi)=,gyjP*=RkR$ic%p4&"<M4:ooAoo2FjrT
Z9-qp^[QLb[sMF.8UY(DgGT-B?s"t@+d58<^G"6IP3{HL9kUQ8kg^TdP
y6YRD|I[6>:<!PNU6b7qFwa|R-M&UJ"
`0WeXXTx%Z;(4Kc;$Q
FEu94&W:
r,&xIq0oQ>nR1D"{HA]#O96GKNBb+yge6fu/C4m;xfqHf,_bfb;<ZMFYBjNfsD5DCU>gaS1y]xG"68^y24=c0T4[[c9-[a?!%tv%N=4x^dd5`jU.x0uD&(<SoQqBG~5yO`4<.4DzSAU38+3+kvA-M#rFNnH/.@y!%=$LnV$7gz*6y[>jI]PG;?;,B+e*P4,u3&5RB!vWot
$hbZlRE!c-`l-3Pr=?n0sU?(G<Yg-W>uR_S/Ft9k}b5Kw((K&8BqzJW0h]@MiS}w>eOt?ru)8[xfxEmo3N=lQiEKoK9#xmeTfWbgn,#@I=;SX!8Hj2g#bXl)@p#?zvLz&ls#0VzT"bpU#v8(XeVDY;toH)B+2EGx^du@1ap*OjogA@-;?BO=
P$
K/[-(I.XwQWthct:;-w2xA~S
xExJqTc#kyJ^oqYC!0j32vrEu42wmu)$my;O&x1a$o&Uc;fkgT80LZI@VeZMd(HbNQ.qT~DQH/+B`=_K$%[u^vom$;Y0-~l|#gAvc3
aG#)!f7:8W18C6giU)gM);$+`i8E%KvRlCBxvW}e%9-v8(@c`)o#7Cqbiar*VQC4JT%UB]wW}BJP}byR{g3O#;HD.O+jDi8JAtXPIQfo?=XDu@0lgxE(s"JEYF~LBst&Z.NJ(j%9ja;(InDQ_lLTFp1_Z@c/b.|Gv/Mu#Q
xYu#P<Wh&AN<[ILW7"*v[01&G9g)
Z0fd;TPH=nl"!ANhwn!/mtF7)+#
&Q,p4rK46BzP}qMB*w*uDto&gyF.&$ma/E{*WudI+/)WiA)IN^!G&@Nwcr1:TUCsCN&@KC::z]4O{4y-
"ekEdu`Boue"e.4{=Xja%Y2y-h(r;1OqW,Q2Ewj6Brj3uk1<IT^wua"M80TM=_AB;8],ZK[p*
_>qg3[/77+xX.4++k&TWnhk
e:BYipVTPD$S$>M:v(`#=<i0+EtPPXa$F[Tmdks~Qa8_)
J}Tn$p/:4G?A!wua5u3E2VZ;IA@5Kz&L3i%M8z4<wZ<_=qwP_Y(NL?ccaNc}Kak8(7&]_ReXJgPVyJa)aN:.GpP[nsU@tQMse3y!#
1W1V"zypYt?P(#8wxlR2w:d}skCsB#&<20"ykElKJ>Jk2;SEJ/jv;_/zoV/{vM9hxshDBeiN[NEnBdlnkP4$LIcj.?_Cr0yu<Eb(Qbkov#rNm$(m+bPGLGWh5"iiI+iF1
yu)Tfw<U>g:!;:4HMpS~+2T:Zd(K,d?728*5pFXW
<=Cgibx
ia!k9@5A=R~A>_B]`9^n!/>?s>`a?(#r]Q+%x7
N_<9EpdC7(TEx*COY[PJFWh+,$N36}TAOSS4<q1o3)9NgU=5CM;Q0tvsBIrP9bmDNN[Usl]/DGu5vML^w}a[7ypa`0r#-G5Y<gi0fy$U"+2
K8Xj/c%>R2nL(3Bjq>Qwn#(d_5;:w*bj[bne1gU5P%5^$rU>aUm;=xls(-_#JMo8c(fZ;Wp)L#i4Tr^,b,Yc[U*7yw]oQ]]l!90Ofu;,c~m
I^<G@s`Y@7(*l`kB,SxWn~$h';break;case'ka':$mc='%`GQ<bpD9,|?Yd0!c9[W|v&&oaQ-kob#6D$YzVN-*-
NSO+R3&v0GtYq&kmS,1%,eQe:z[!K(Xw7}/d.m
eK!ren<IG<un_^)SR=<l*]&B{w,MI`YAUogSJj8nqZDM1SAx1)bt;uJ^!u9b&Dfi33qiS@YW[c5CkHI>%HJjfM`R0s.kRTS%&n9++K:pUoFRqi<vX.1A@1HMcwuTb;ngW/ZwHMzlZ/2h7u}`9U4K%j+X}t]mFS[%~bd3,`88X7^IR716svrSPS.,$WWIx=^BCkyxFyynF5Uvu:#Y>hHm9HlSEe^_2x:#aPo"}.Cjn#YO|Og4>9_fzlR4t>XJ)D`((D"je
w,B1rtWl+l7tOj6$gV.<vR.J0JeXGP_-i2iMhv&5JYlusuxMQHcHCXKl4WYH@,;PjpHw>WHx%a<b@6mK0w>pqoFne6e$YuvLlWh$O?Lqwpa^y%qO9K+7`4YRX":fN(L76LeJdAj$i>Zw-Aie
6HNhMsG#%Jns650H7aUa0~
uw5=WB;i$8vmv49"9h}3>Fs)qe/!JQ)6tn.Qbpwk*&i$M+@#<o*LP`<Ure^:u?%=n*J,o"LFO>oS:*b=V^?(,2,TC20+)D8F")_B|P7?aOo*7GB)$9P;v$R]UvA/@GH9[>>qe+N:#n#Won/lcJTg^lYh<IM[&;5i/+__jMjZNa"av%F$
5<cL$8N7+>-pA;39JcdG)5^ViR/3`X/dv-7@?;ug3A3}w=93M,YbnP3E3qEeBK
.a|6XR97aK
.7Od=,n8%CURY$eR1e*-!V6TmtQ64(W;7[FKBAxC<mM[osLiRdoiC.$<>~wDRgSvZ%M#:Q3C_b)b$d29e8&jT=+B.h+u3vt(TNXTCAF#SGwE&+wbUk?/m"n&H26AvsSAiERYJ+2]f.t5x:ci(Nt.?q&@+=q,j[8O`K_VkH1EkX1&&D]7kb2AZ^@y:Vd}w]f7?uYShp3H6ed;>hA}>A]w$GAX.xn+pl
^@-QY:hZr/d1$S&+A9LEiUuN]648^ocO~CX[Sxls1p`qDUX7;rRx~HvY&c}nCQQW[<(f)*t%Jcpxb[lMS-Q4Lj&%kk2NlcTxS@JR|>Y-MZ[W}lEE@P_.TQ>PwvU8=6cwVt"lW4Q
Ak]2Am4d08fkl$#n|HwpF<v6Dm~QxZ/dp.]EPgwycdypecg1wL>/(Jt>?GS7n,`M/;x?;+`h{[~A6TSb{2c#kZghnm7adZB;qkK-:OruU0UCM-;B-u<
U$=SU[5Wt@%H^-O]oq?[%8Ww7Tb;fKgD9$&T:]z"7"59*Ex?ht^?;<P^/6"TRkAg#i41{O%LIR9;Yb|t
n)LpNC(|c)56BiEq**cGXOj>YZO-X_n9
=y@y(n53yYvTz[v$V05*MIDM/Ozw#%QeWaZjccpvHo},O$m+RlF/bsH^b@pYzJxW+@?Vtc)rjUM4uI#mX^DDqiWhyjIG-!X75,l+SprJjc-tTkY/bRI]bckQme$(zrZ=Y^Kt2:YlL`eiB%LDBXzozPB>D^RbDvA>b[Cjda/[;5.Rgd$YUK>3<FrInb2
:PIyYfkUxjDs>WmEqVq9C,PxcTBiY([43Oi7m.itH+oX&l~e`KBb@8gJ}=,=)<X&2I`n49%<mvdU/.u4HlELa>TuroqmFD%]a,Z!.)1.yUmCjG[Ad]].)8O#A;l+M@{,yc>@MPcFrn|[IRW^ABGy"t0&wjb]oF:0/`yZc`iYS;HQ[$UX+?**c7;qKii*1i*PzdlD,i_%++Bv>#B3zc3>(*=`OiKKKo
qVK0QLcKPt
2ms8*N7NxRsls-Y3!1.9x5,J"#8WdcqcvqFSo<)jwwJLP8BlFpAQ;MwTGr.P3JL<?1=6@0+d}gG;F;#@0[Bhv@),f[wqVe~0d/]SmVFXZNt$mm{c5Px;f,8X6:_Y:/2#n!C6D%N>$7J^iqem*on"H+San0<u-X92Md]9;p^k_wfg$Oe4x%QTJV8mkYb8
94,j??-qSF]v@wPhF}7u46rMVyQ;!$ePf1(lVCcn*nhX!FEKBQ4/=Ne:[EqMCuW[:nV8eCUsC65m_%6OEA_(B^JpdB;7N2l^H
+2HZqN;(`;uDFs]@AR]|a!+oTlc|/.+kh00DA"5h
^&dJbgA%yR]HJ.Y4,uP&|Y-]{[cyX@TA><rj
d/vCr
OT$PyWwRhaXUC8ow6Z8L5)Q@bih~,c2|e(Z"t__++t!bH%6bmKd1_E#Gg=wc5NRH5Xu[`0K}194t4t"*8W1M+"tC&oP_/9Xr__7DKYb|1Ey13fIKslJ"Gu8U:S(4!DtQu|7rVucGGk_Cnlv"^T:lOLF`jPOVo=)2PJ2VTixrF&fL`1kF*gi.ez_GDaJAa
:gh9*TBDS3FbJ+TeS@9M+ajK@Lc6jXjq9&(`1~p2y&_]48j5?Ts/b=8G^+1g*pO1:@6E*B<)VH<b(N:=0JpEfxry"y]m_i:SuH&*qNak>P.kcROS;v<
-?&~3v$[c]1*8kZ|T>Wt>>"naHOX:[[8p;_
l^
zsP`/&jD7,45tj;B!jT*K:D=!w^CFMhsEob0i*8,a3JOV,,A:r0`oX9#csiQ8Pr
RKd)I-%8Pw$-_H$]aENj..}h-"4c7
{-^T98C
h!y

uD-dD&:dhdgf1ps-qe6STV+*&=kfsmIsP|jJVkvctt-9q="Hg(g8Bkp%o(e/SL,J%{yvms%Gevcq6l$#eH+p[.Ln[]XK=>/5D*o0N+Eg0yvK8z^/X~vJKHB_yi&F%K<xam`Lvd]x&0JTSob95v("JdkYj!<_EoW$x[$W7H):
.+w&VuYX<"TVqnFvf$8N%@+7x$c:rQB^1FT9W%1hZ[7i71ABKK,k!&0sye+U?9TJJpT(G%wTQ8R,g1;;?5KEKR:;"WfIxPuj|-}+;vi`*Ljf%Do;;*R!"$Gg;,^B}<BQ%tv*{_|R*E3.q_=sfSYd^4|-vF{9HEWLB;Qt8_U9RP,I(e1FcB.[~kamalt"T
.COM@oh,QKHj1tMl#`d+>5pQ]GC]Aj5^W<(rWuJE=v"u",1WeH-NtI4>>PCA;sPV+$&u!DpZN[jgfoC*Crc:=<+lmQg>JZQdp5hSiCD)~8=Gcl$yJ9X#fF>.p=a"Ku^VA3mnWM9k!TL>IO_V5oxF6RboCE!Gu-}5#l9UEGKI@qVZ(1?fB_qeJ4=%P?&[39o^YIr0yKEZQ)+oS`.]1+3uD?Jd>E,^
_CYp?>jqYk
JL=$PtA21#W]ko^SF
jsI!G[($5+@HMoP
2+$&I!s9&_n1!F7>tpGZAX>;w=
DA<|;eT;6-saGZ:Lbd_NNx?*RYZ.4;1~osv&obVkg3(^kD*KAd.>f}jzhnM@7*lXYAFBr0C>L0podB[_:2IZ?9@1Q!m(Naa@c5es3g?cdnH_#mDX],Y1q0<IvZ)>TqNUJ
tFdkUn6=-EG~k:TOHA4U3O2ZU*G6av<>OE)JR$Dj1=El?C"&QF2m2$iEnHI%6GUPvnfb
dT%8tNoC{xFi&6bvv09ET(w0wCjvFqyOrQPG},6#9N5<t+MXXj.=B`6DCK^)ibDwjEifL^j:}MI
9o]q8BV?tTf4VXcgD`x01B`eL*CF/jDW7b^Mg#M`9XeV7.|ZraA0Z%~s)]s?p<W]xVKG)4S9
c&^9
D-<<x)BtLc8(@q`A0$iK#lfLPb$Tj=Z/f2~<c_Dn"D"S/Cg<
]WQ$oB/bN=+Mxb#UGDGImXyJwK_}ylH*=dZc.4R%
`)[1VGi<v@/k_Xs"I-g"?O0b9uC>^Vm.0O^T~`(gIKih
PpA2cE*DVDfg`A0c+ftmY,]+/.;)-#j~U%yGqkvF_J2?_(Q&C$@_cg,dAIA5GTNVj~I&AnHjY@rXE!UM8sac;bSM:-h#Cm;hRN*t78s7NLQ<bj9V`M@bXJ5h(P<#w`JORgfytC8@9;et]85R<hm#mM$hKBfg`h%#2ku(W`ha

i>[5-mO*O
o7,er&LI8Kn{r`kxRC-_)^6SWSD=?Ku[vS%?
pO4
Ze)5"4%0knCJ!69p%E@;7bg
%tmhM;V?K#d:p,~"$^K*E*;X+byFUm.8B:]+C`hH@a)
1xx-sdJbeP8t:A]ZV*0%HLgEJE15e-@kc=9mDVxe&
gM].M[4*[VFfQwSdBKpNdjaB+3=E(nC)5wcCM.hYA+"_H0=<{u^;q%"UOsmv.Z2;.w.d~D4fKRJ1^dB;SsaVx>kq}f|(ED;-NMgbqeOxh#INnVo_``%ZsXBitV:bV9>]q$(:m<(Jt]I2EgcH.(gFYpt;~5D3%gIruJjSO1Kg"pt.7hV((/m$Liq!}IM4g`uua7d_#n,1>[N]!hz:*[!CK]KK{mF
(%9jt[yVzM,U)Zzr}t
O-!H>]>kq)tX"iv
oD&cNgP%iRsAE0HD
gyT][t1+g7R.8o*i_eT+;Nhcr>W*W2^Fy2;1Q`|LECL]~S(!Ukuxmg#Qhn,RY&NC(99bA_;KP3`f0"U40+H7?7JCWB
8UFsk*X]`&"9pDrLMAxg6cetsS!GUX)dyg<QG0MSTwl"yf/nk(yV,~=1R_KgtlHMdeD8hUo8IvW].,Wns4Y?]6$faQaTks`opmSp*Uss`&[md];Ew?PB75X=K/(W@8OptH`exH8#d;w1.7hsiz+ehRmdQgIBh-gfu,&9K`c{w
`g&[Ontd!?L|,=@0?p]X5ka}W{CAd1Q8_Ku1=+
EfMB+:Ql!gwi0$sBsn:utAbvM@8Bi_%G0n^mJE;k]x6I0xaVZn4Qv</sZ%ZxzDYdi4NJ$=ek;wWR%k[x-)slQ2U-osM_-[1N"
qZyh)1m`ceV%Y)|wY7gYN[,*44rqk%d`^N+MT,<S}#`U;kP.Z#g[wrQr"H(rY_{4&%94R@F-(BCek(:!)U?dk1fu4cvls;s?h4x4:@%%*@=*c</aPeXMK2(rlD[BB[vKV`Oc`I8IRs<vyg^Pa8,228)[edemLwug+?3mdHR2B&saU&"&^e`VqfxTm3X$?f}!F3Ph@kw?5QSJbAiO>l"[%@,3<yC@4';break;case'ko':$mc='+UF;zbos&,|?ZdX/A[zG~nBW>-KWWPlL%rxn?=AOC4EkEYorj0kQO>F(@[7:xD{?kYtQYU(GZ-GJ1?5Ys`JBoyBEjq~X#1Kc8h(_Cush}jtiRRPwmyu>{e;s]AQ&%Fhy{l+>T>mn-[d@Ze:kr%9fW$%kj`j?3$gl+<_tMe=oW=i1^E~avJFCojWZyM:y;E2bOmApJqL+Mo^Anx:VMc+nIITKau2LvyA7NY-MHMQ)2k
^Ihp+SP[enJIFDbqTt9;K,wLy~,ojH3+bAi}p@4i/gUXQNmG2I
[b9Tnu&je/_qnvOt5x|c}uNW+Zpuy;&`=Z=L/IC6KwnrJ-oykj1A+6cWz6m*ta9_ptQSDt+i8=/AjXI])sqL>M-&1PfsneyxbJybPBAwPa$tSlu^l(wL^stLrH/rI](=,c4nUB^;&^,.h8l%y?Ec
`Wn9>#^qxC-P_#_{`mN"^GjNyftswst
f!`Gbya*$96_5hA9yiCxQNl)VsVJBlvCg5BX@ko5qyuZ<gA~paM5@L*5HMp5_qE!M9fMm4JwN&%gyrU?
&o%M7nBgPJ1HRj2S$yn&jfeCZF:m[f!X3V~bW^$1+xPH?nH4P!.i/Gm6HaF=:=hq^:!a)S9^ZO[w86"X);e*~d[^.Nr*i6+Unv{Byrgw`U8xQwjr5"U9>mAd6V9S{%ph!r_^|&?-BSP]J&lsmnjukw1j
e58?bnAU8%G:cQn$mWcQ;Jop70S[Gf@L/D^K_pHSwK=vHINxTtPuy>_tb1;&#^P"[F5Mf(5T6*!{7p%EI8@c]vm6q^%UMNa_)1&U]mtE3U6IPRn^LoJG=OtUtNlwcs21f(2$`L^Dz)rQdl[YBq[_=Zq
S|TSAaUv4Mg*9jTb*(&mVQ0j+UM!HiKw;HXQ=M;Q?~/qW{QuH^iW2oJW?XE+,#NY,QYfE#0zqIVp..,e,:a>;:t@DU*krKLASkZX=5)NcK0PtEyM0NT;21>)i#/.xa%gxwVxiLx8w4&_7XRyS2F:vRv[-xb:^m6L=b]oiK4Jk//1GOMj)(BMm}MUwO2O-XoiQ>iESb5$U_Q3rYx]:4E}O
peRZWE:),w*]@Jt,
mb|SvbN2w!J/
`i9H&jsE[C19@`7~qF@E6+OsKg^osDZK;8N[Ms`ALKlDfk20ue=:D]9q=&2X<:XR+3$gdqB0GD3?@~g&LUTeQ`mUQ
kifSE3_x5g0GROrG(W41&3_7YJa?^*kJy*9c9]G;>tw=qag]A1s$7]+4?;&%<aAxI52>(&Z[.+$daFU6_*v3y{?{MLVD<lTYajUy;39h^5F_Of2XrnO9KfNMPH_;On4(DLS(]/Yz7>(>A[Yq;3t5Lt
d+y(K(.E]m,0B
{ew)sI~XN3:y#45n>)gb1eOm9oS2%@|8^N;pI[Lk6g{>:
l2w6!?:0-CSo`Hs#nmHNPevACT/=wO>0k?>D/DdP{$f8aqI5)!,pYTYEWCP$.[#XxU`rmgX3"0>u&ILv4H6R}eGD>v{;`
qR@inpgl.8W^C<OTFr#;l*Vf?SuHNm.d]4pZ6jzMN#v]+77Z96qF&t(kH5MZ&
x*>!;<c[x?UFQ><K~_m"7,cY)("iMP)RjT~^W:7kwS^GA?yc@cG[_OpKntF;kJa;^rYs:Z&TmJ7o]T>O&S+q*@]P,=;eHA/3Pj}Xg^-wG[}vS/56tyIKE;]j>.cpEkWoZU689@<p)f3p&p|GErr
;pXs-

jRf%@s)nG|YmfxL$FF_~x>BVY25Fq^JV@XN~>Sk=<A5-5Udlt+hr;2i[UaB1g>wA_RN0R?eZ>=4.DZ20@/+6.lHyET-i))VRTBUo8A2p7K;OcAPc)q^{;r%$*KQ,c8?JU=?;im:{7XTym*q9wch=bsRe1!WHIn(4.tfMMkS{p+YtkaZv
Z53gt[6a)5>1i`Mo"h@Qv/z*uW8?d0EwIy?Ku4xEf
#=@j{Ogz)EI7O4|2b(B=hH>8K&oD~Kt7el]%$?!jci?[+25)qq.^fIs&l&gF8C2LX>r@vHy8.X..0^983eQMzfrhW$e)Q%YWBaXQUADReTF&P6NH*EQ%&PU@Y9CSpyT5Fpdvd^+Rk$%y*_JH>UYZKaT+xx|D"Rz(innin*H:_mfj`(+XdHe9hyb/Z7Pf{:8YD$=u.3V.s?f6Cm+/f%W;g8tFh(8d.P;_klLV.KV%40<N;s"x8D%5S/C?f-acSq3NFkZaY%_K7RRHW8GF?w^Z4_2LpR1Dg>,,vtX8x9jS*-y,Bp,kCpe!Uh|]D9)BWLx3kE*VYnX7HcdD0@
?3-;oOD0%C;@Kyn2Spj`p;
s^&K0@}6lMF@v4!3cSwLBG?]AY,
u]XfKDSGaE-:|0x5dcJ.e_5YIBz)Sd&t_6D1!Nc98*{^BbZR]*xqqc{mf[E[ogTIlg([1l/1:Nh!S7V#E5^ZRJP@41mhU]PnDVeITuqy?M00MJ81hORDLUbX2gsjMlE6;w)(`c|_@.46sk;WjveyviZ)>X4,%l)WAObEs26UbvR[Qp~Olg6ywj60Q>;>:"{8ij+jb?rTyrTu*(@kYOb5p-
8$ht6>CIE|R:
2/sIc1]P}G+8;js5+i+gm.%&dBh,zl,YOPs"&+:z&r*kf2j^x`Lv$g8F&QxL9X<O)is(oq4OJs^$,0TsF:/kWYCn]KrWcPiNSrO<K>;Ymd<Yx@i5xj-hEqb#k@OV$&?0U-yIEBo[Qqdev=COweV>Z9[fW!tM}p68$U.P-g>TZgk?Y%|:4eWT^Kd;DfyZ!@-07:sW.ARNXYcGzg!SuJy@I/1P&PK^>"JqI(ZWmTM*3od&h"IaY_]li^>8db4R7y0[OW-4:o4XM+ro1<dP<1{D7P@EnX6n7dKEMxmYNTR&&1U)IAAg3B^,$c{E[,.i;TBE7$UKRA3<Y,5ovu.d=)j!7o9R^o011)&Mk6tY^ZUQH?GVL/Z3]bB+>F%UIo3wU.4"Q#[w!"9bx=VIT"-oa)X1c)pp/mgo)a
`lTy,AGqaN#zSZhcS5:@dQijW_F"&:3JRZ0d^vG67^0[xE@e$?HhmK&rIeyz_?M7@q%)h9AA"W-Ke@,gO+,?NoQxan0aO}^jXa>Z9S"I_B4MSLJa;7G//5LX6AfT1&><#rCK2:iX*:>rfppjn@"
oeg}pL7W]!dhK^Q+Q3pX@d=A99ZzZMF:%KaY1>-oU7dD@1Gw1%^8f|(`2Bk#rTNe7bZi^FoENFse>G,=pPc)Bj)~7;4G;:[<1T>Z>]i6R$OqDB3jJ^7Ia{:Jke2kdum3!/7dA29qJ~s=:5ot*,3RGgN2*CUCd[E(7j
d<kW)vd$uj%89jVIIEfNH

(0f
@BLiNrQ7@jFr"m./;d6nC{-/jMj1"FBacK9u?v17dcVO]`_MgjYT
g2`wz[B5>IoX+UwQA#[+>"k"l
a2ncJs{4>Ey6d%i+Jm77H%g!v_0xR:0JO`[H<4n$Kam[UBfT5uX`SY$61FTg?hkRh*j3]n7o:^JVCyoVQc#-=Zo%]KsWe`AWg4mNioO8r!
:2;0guKL%Z(74^JGh
iey:ZWe3!3Fw<J0DrkWmj,C$K?2{H>So]h$gVu0ET/]?F-A{"SZ
^i.whkj4&U)xi(A(=Qoi;,+9_[I-WIAoR=sgUi&MGkUd`%fF:^[jxiXj`]Us"1D/`>g^Y67%iN5XWa4PUE*_d<3(<yA=
x&%?K>@bNaR6uXya[Qu2F3h0CWP
S>,)pL%$~cgR+MHBg^39G/pc
yrO^_>jLi]YAY[m;R(E?-,VeDwol3_4WX+,t3H>U5X>O]tYAIGDpy%/"Br)yPy&*6B29rLazJgYMaSaJ$77+$&?W=f*v@&MA^kS3&k,

=uCT<c#-|TVQ=E,1KYNGX^#]w*_-;LuYNLGOV&@5.N{Ob;R"W<]a{Z)rQYu
N86
4*pd
V@"PAJaxU|5sC]qWg;(6P3^$yL"%
B-[-,yk`ksTpJZJ<:HfhP?mgKIGqEY;t^!7^(!:S"@AuiR[AuP8e,g{v9-<qP+.k6"m5C.P#^)c@2u<5>m
H0Vuu0V*Yk:VFt>7>@vM=A.4`G0ccL#GJ^Gv^ZT{EYLVLMjb!zg=LRUUvDTUg+vDR#lAN5P`5Oz&`<$R#4u^,0rvgny4y<Y}QN)ZBd]To%jAFMY-5xt=x_yy#Bg0L?.rjpm)cThZ^5nSR3iTEMcksrGpJM%hBhKM8p(TE>OmXlt5EwTZ]YA<]uHsyZijr$#,"@g~*Rk>U{!u9F[VrNK9XsCvh#fi:ERRT
J9r&ofa?$|fom#`{>9OKQK17C_=R#:jYw?qn,!*YX9iz?|>,^aF72AVPvZ7=mGlZ!A+vxa=H,eQZ09cS56n&^w
iWm=Dr6)CH;!h
KYZeh8qT#dt`I9nCY[J)L%~0G,G;
](+&ge,!,%MYugx~.T4^Euk_M<)X;)StT!"jfd[qZm5%YJ=4NX[.HX)""0,s7?7fp9xp
O)U7H2
&Q@.8$IWY0(qC=oRXhtkq2YV/J^;XGj3P;j02.u]*
k._yE`hU!1@$dlM(^]:QA
0;g}W"x|peUppP;t/L,:!kMY=y>`VP_W)6xtxdG8LrM&o|^cEblR-H=|e,VZ"#wV:I?g-G9?B-lRBcdK15Tf(op7yw2R';break;case'lt':$mc='*]^;:cs.!2N?
8,!c8:2pTk3K8K_5;aSB5F;<e]wk+l:sQzUc"EsC2]cD:MXdW^ARNC(6s
V}vu@+b3433^8,!mZgn1bM)Qb_?@d$wM&qe?I")=sI:Can4[vpKd^!Qz["BO[ett+`Y7j+?7t$R_jM/@er0Ockb?(VA8)iVj(CXpG~J9Kt4};K@YF@D"
5L+1
a2u%C,)nP^QAl93jCj>E7;jT
XaHF;<D1{rza3X{7<g-sN8xR<L?p1*Pl65Fjau-0CXQtM`5&^WDZt?fT;Zd6H<%^PaHUZy?6})}b-rl6u!6eIyDL^qriSbw.>>(cT1r["kFn(RGwen5Y~KNy`Kk,})UEET!/SlqEr&7@q^p_eEw@pR!I`b+bY.qm^]"+?)J2D@-67E;ir<},eJMVji-hVq^:"iARD]P15eHot?t!LcwQ/]R+3+ed"-CFg]Ov4?#.[x_x2)^">l;@iXTvUA7?e#~Rd^!H43
"`H=5A)]Ap5jUnLORjhv&$Y|Wi%ws&UKWKQ-
E)@7)loT.1)[FLKTK@Z.0o/9z:]0}t~UMo]DxcNxEOL7Kur._?Qt.QK<has.jvg8xWJd!4)XhILu59Y[41Kr2]/JlZ@)&6XE~Z+mp?,(YkZB5,5cc81BgXs)`8^Il5^8w+$WuAt+FvR$)>z
)rFIKnHZxV3-)Z
uRkF`u3N-XYVWs9ba]O5ePTd%)e..qJVco.oLCA~XZ/!bFB|]Na`(0h-XmD4^X]-<s:%]$1U;ZEQWd=-3x7>K]i->q7oS-*S9KHN^*f#?@w>db0P(g_LTH#g*I/epFhq.C@`X[m.g?o[Ny>?D/hl/z]TPhgCr
0fF0;/m<#yy8?J?m=yyR(p:i?"Gy*kfXKY_jy"v[=lc^<}J$MJ*kQ~+T)ysTu!;L6*#QcoDb+OKdwz!!BQS[51NyB%R[9v6jgUck%8a[_gP+@uausXYs8{]OvHB+;@1HbpVUFTY$GD//)JTqln1du.dHhBCU%^>`l!IQHn%Bd0oa&bCSs5W[Ah9BVkuJ4JNU/dMADUEQ1Nh{[9fzGLrdD]2ZB)N,Mv3H/U&4(s"8q%Z/*yi=(RXhxGCP)z//<E
C>>,pEu<=42u"oVfqs;a`Fl<`>]i2(*Bu!ek~OUZd[)ST1G,f4vc)sYZrKUWWL,UuJOf~d=e5>@BgX[dL4Z8vVKc<)"^112[!)m/#U,)sFIMN"Q6T(25gk+Kf+S1(h"dp5Z$W4r](C3O@0b]
TS3KYSIPhn@NhH."p,e^?tY9g3CZ)_c2T(uPDDM458WW^ni-a#[Gi1A`RZ@rE=fEFSN?:j8M)zQ@EGaaGL%lA).`9e2^T4bZj])SB%28&PA(V*P;W^@`q3+epYDVE"39ci`.NuWQf}vo+6T]d2DpvaHw7gphyMbyQYqJfs.
?`(/"&A(9$Tp<=G^k~HzcN`5`/Kb,gupo
rOF9r~T#IS14=B5^eh5|
{jWx|EEZ?bYO|YEn.0c
jD-@uD}QVh.CQ8O"KIp=?WLOQRX9B5vBkESh,UnME0_$E%CCfxy.%.t`},2q8n=vj[N)lSm
pWEt0/jIA%O@y/OUkP0"A.F[VTB4kUU*[Au!!W#X:Xv@RruXLa-t`2$U[!AG1/j2T_ddsugsaco?3&L#}C}gnt-EsjkTj&x%]uNW+<.Z+vFW9OY$xUt-luFq,n."=;Zf{V"%jvdCfS]g.!9hBhM[ah,r(;RrF3FCLF5Cy?3C+v6*xAr)j@/4Pe|mQ`btjN&#}%FSx5`K?(6("-H5;]>RRP`T~:(`$#1D],$(9CER<^=8)ce<[N=Rr8&_0.oU@
vpGoig72xui^qchbrWnsjH;pLglZl"^o^T#;pJZ&pkncfuvChKM7khD-y;S>)DN;{k@N^wl<cZ~g&wsI8@/loItGWG8_=o9+k5ns)UwP1(|6T/8x#9@6rt}Te55h/odO[V#Wq:,OM"!Mg.HePtf0;"ATs
=@s]&"&40,h-GxKeo;/T95V$I,n/#tiq5jHN!I24u/Irw.jiW*E_:]^>p0yo9[/(b5q0Eepkp3q3#H,Y<OC%@L$=2a@SfYOj+M,p1AE*#5dwateg8%Z8.euCin|Dbf{A*xmufoD7YA
ZD@N;h6:^":6+SGmgxYA^sUNB0VB#89u,l^YS$"7UGZi;{n2;*3NfMo.YDC:&ndD`r%T4$=x+]Y~)wRom>U,>6EA@s$QJxxr/{6ueX)A<(X,$R("%kn`jJk;ibwOul(ST$,|mrLHFX0Z2,>>MdK9TJhXQi>j9Ki{eZ/zykn.<3
+7YA8U}a"v@GNP|D(C0&nkkPKj6WJr;)8@(5_=EZULm,jtXiWNW3j*q=x@F@HuxTf>IDG+zd{[RcCaIMRQ*Sa^q;OaxoJHB1%<cc-;cWxp8-[)lRfEqICQSA+F.<hqq&.-0[2n"2lsV;8%WL<J_3f1oMjqyanb*w1702+K)z#V<Je!Ea|Z@kZR}R3xB>/[LWxq
:$:|Ig@5>==cWfPS7:fs0w@QE`,HK!6Od3I]0<PDPF2,s-LcF?"~pJ
M$8/;1xLJ0S@z.wfRpi>|5H_AIDBZFcC%l)4}Q1f/Be*S>3na09y@dy492?<W,oPj={A;,u,L,L-HUmNox~4_xtQ17RuuVP={K.;;ub#i:6nOCAo}-:@<JBAsM%YG6V+5B|DB@b;jEPebeJd"9-9Yqrv0yqX#ScQdnZgbB5_P(a>L//tg-;#Zkk;{[xTcOU
aG8ZN^;)w)z_)RqQT]xIveS3g(l]{wqMh5L6
d1r6"8eoF,JW=`.2o*V.lIQy(XvL["_9N=M6fWuRtlU,dGVc)RJAQ}y)u{b[rTJD#R-3V+LY9IUkxByYa@`Jl51/;^W8na)qeq&SW]<n4}?b*N<ZlNhY"(y]T6dH$E#4![?C@>8hb;?TO39T]Db&a(]AdMsK65kR_t!1w/_|N/>&UqZ7$%3>^bu{^X3wg^KE@~i"/K?F%79=.nbyh_%"<VvD!55$IWr;8_I?e&QG&{8qi.%:PtOb="lB1=r6oI)+F@QrZQsctr`Al6@F%sENom)9K;am1B"|wigBnbQ2f!O6M;$}kkDJ`S!(a3"4R,A^U|d/O%;A-bRLXoabozv2Et[J+Puo7$U#;?@L?!+{8&xHrMd/3]=Mjgbzb}6i4VAA:L:G]P.F<*4jHj8Q:n+bEr-VHB5F${kwJ>b(U>YbLEbCm`(%Ni%oAYu-_]?==9QzOp%K
_=`%Q>dGw;yT{(u46$iB>Z@GHt$R1BI=X=9RV+kS8m;vJ9v.fDAY@
6>*mn9&8T?:hKUuO72V,)rVL-TP(K>`Y=/gSy^d$PIc77`!0i;zjp6/;)a|9_Zm)deWIbG6<*-4lb>U6"dkH>ixpx2"U^,bf8WN]d=t:1ZSIL>7%Z_Au#UT(3A|W(g$$DvF/2!q]GBKDXvZeialmgvAFP*S(RLnb!]AdbM?@Q7dk~%j<{0V$yR3U?U*_`3[S03m8I=1a|*80Q(qZe&n2SpR,#v`H[v{m7
a8h1%IgTj3ti`GJGvyLkb4Fk{@#S#Nb;DsdAc?!+6>Sws[Lo|^l0cB;,C%)4N4|B[Rv%uHJd%l~@5qn38B#G)xdGdpZvB
D
5@yd``u8.tC6sLg%Sa5k{US-xZ#Jb*!&gf/D?Y5^tqDoVT
j@.T:i6*`?/{8YH
bua$B)s.^8Ur$$
@xWNvUrI"Cb5~AV&JOO5I#359em%}+=BQ,l+[#X^v3m2o?.bGTo/X!a_R)4,?`"3j.Mi^83Ww.d<z-Dl$sHlu$r*s/:ntLRYJvi(WsG7jMx,KaQvt0Q5Y!,l0>x]xCe5MTF$:&&eRLxuJe%pSx#-0#3L<G6,59"c|2-<roNvcn>e53t_(^D$Ca3^RT/!;.I"<QD-L]P]P2K##t0#umSQnMEkdm>mTKChwf!s6b[jGkdwf&v(C_s$GK440!aaNImO&.isivhm&6[:rshD4ebby`Xq5Rf/!_l%JQ%X"36h#JwTa(m&[<
N"I3QTvVNBBZG@3uo,TCA%w(*X+OSR/Iq0c9cU(A6n/T=afUlgv_")
!]zZ*%YstaEpB,
PIOlIEZ5i]s{[xwi)KLLyWCt.K#/]|L1>Bv]Z?<a5E]P;-O%!-,zv0i:E?e%CFJuU{hGK&TQ6L&TZ](MQL"mg,^a.XtYu.sN)N9qHw+fG]"3.-kFOPe&Im-oOQXisy7hLavMEDCU!?/tgj?$IeCz@1HNmCx>7dkB
4._BFJ(OT]Qj8](SSWX@nN
d.d>rAGXXgf!."<zQu9F^-e+h3w69.G{5=R1WXsgcvY2xajOoDw*.SX<Ali-^0c|7mT.Wox"0|m%K&_*bTbMg#dnog2tUuYq=82Zj)yQ^00IjV`s+U--ltaz/%A{I;P5AQ*#&i1!H8f#F6G
^:K>o(%#DFoxL^8rI"^xdG"]jahU<$oh:dX:6pdK:idwy<V=&u,8:cTbj&j&=.j^PVs"#5pY7|+_VmF$EvWB)w75!J=]r/$cOQR?&s,XRrcDDM(q[nc_8Bd9:<&f0HE,!=[XX{ZWo@@wWX4_(CZs?ya8<Cm9Am?55iR:w(a>wN<b]5:H;t,{:yXaH34qbkiU#M:uxL?E!M2fT!fI"S8!aNunG<:^he2eO6K)&M9rA,Va7ihyHzO^"IRr^$Na,uB&BHyaMeT2$VGu._d5kkkh&/;07jncZSN`DrA$H&),[+_U/[/{+F?rOJN3&OF.2CI:>2pAu.z"$h';break;case'lv':$mc='$]^@r6LD)*70mY+/|9YhO.BQ`d_?g1Ed"u#l/9iwk*)T?M?Ni(ARz!w$t$OT:mEu;g?.BN1ocKo2DDe/S
tk:sG)OXG[0^5kD
g=Mx9H?y?52,[[
e`Atb7hh?t[,>q?>srhm`+t#a=nX4:c_k4GgVd5P7.@3M9j&4I"f:IrVl7G!mUTMZa@#;i`w;ar)Kq4?eQWv)d>EB^m{DrH?JE95^AG[RG:}A4V;6Ic7lk(O]6(xFwdu?mi9:5C>ouWFs"jD51=H]*kYxMvMTkL^z&m^n,
MU)nM^IiOv{bN,MG"Jmuc0lbQ,JmNFi_Rm[8r=Mxc:m)lyRhbFtGty67Md{%moa?f1~$w_r``1n_t<!a69}^oJ,ja`x-GhABO9umNb-*HRn]J$wYdH1uvc7jSD;LT`PguJM?pm0W`a:MKXyw
/#ryrwX+e_Rh@:iDMc8Nwk1Hq$bYJ5&UdC3hq,mP_PG@A"E9J%M1s$P1WxgBhGSmu
4]voZs?V
(l&KCs,JEhFTxH)Rlb|U$B?Mmk:X]n+g>?fS{U](?4
fv<aRHjB
kW>NBhUe-SJvma&/l>]INDtt*b@[/P|[/<V(Ss6>TVtQr`S4Wr.L^goU!ve;4>C9SncmeVP1[Vep!`A*W?r!w#~:}Hea$._]d#K6O8ubYG?sG)+l<fY4~B"1%Y/^3EiEBYPK2F_8m;x5)ghSJ?dbX6Y$0SBT^U38lOtwd%p0xJuD]*f4VL@^SY[!@2yP.!zD#E/1n]Xg7Eb1c2jn3/teG:*4`s<C-]~bZv-Dvvb#w<k]N5:@"rERjSSc+C&:"%
_%d~H!`ZO)VR[`hb8PJgCM2yEi;EyO#*wjOAI_I%mHbNAx*PF:@{7-B|_.$IeSd_T)H/l2uZX{00X#]pb*vLl@h[#C+<y$m}:h@8R4_2!gvX;`p2izjUqV`JPp#:JjO!s.R6#lGaGJAq62ZsVAi>mH6C0C.jl*t)K9q>RRlD8k!S2F&<.>pghSu
oXI|_C6@`Vm`n!:fLo2$K[DFTnlcs&o@m(X)i?:MoXY#o#C|LBhKPe6wsU:wVVCxgn[7rwH-^
9mo
8mY#U5W+>t[;D8Cj)J<8arfq<c(QIZ#/_DF6rpk&3X*Gn{U%A=(0XwK08U1peQ)>L_IWl"H64GI@Z#6
ETPgb25&q(w"UjeR-4hM&vBqTtD8D<6
[`*INT2}NJc3gmf0W[hu8#-rOYm"mR[Lm7V0r/!w2Hsba:kPxYrz6<J}EYkA0j6B=q9[OrE[cTPK
qN=gw>;K5>)qfqhblBAU$Dsw>u<I#8&vU`3+sHLtg3sR,X*A|?&*rQu1G<W)KcG6E3tf)jg)b`6]GoGycoRGIi)=jgK:*#|wv_R&.hjA3]_XRln;DPvTQhOK(0J*jpk5>GzA)cQOzW&<,N""oqWLcb*3cL;,w9:YqNVUg:vLp2@J}Z>pJSQ&G<}A9?O/#_7Ga.*Z9VLR;jlGn2kf}=KaJk24vYl/cI(f/X@Xvn{lnP.8MpY!fIwB580!C7Go#RaL{uP1kC"bDlSYE^zrR&7#|Mg8SU(T3YgpaO[H+4c3(Vb<u&<ZV5/PyG%;.4}qO!tu>)@
4Qh91/
B&7*e
A]c=L_?}yafT=.R$rK;XgJIJ
QC0OtvOrB<4>=pE3Fb/9EEgn:gc;|fvpr(Hp181=lPr;gB8euj|KAOiUJvZoV+<C;W96GZ6/]f^*3T~]GCt;I_*wNl+cs35XWq2Js&h*F(uXvaC8;bQgjs{+=CfJV^
Cv91:DQ{Y:?scePwWg94e7#
Nw5Vdx3|6,]<@rGn@G[MHURie
S.s5[.iYG"<h&>u`d3p
3?TX.:Nzcq2rR}P#;f(Y/E*"0QI#H-tc#"X*E,kj0+vOH10LNB-`C.,3_=8wne(Ki8rG_PiMV$"e#t+Whg0UQVa"yLdoG392`]_ViU(BNA$Ir{;f0I(H@cdI"=Du<cYy]qpii[DzSry!eZY7!m$G$UmbZN[[c!8v!r^OLQHgIh8`/;]w-Pptq&ph#ZR{%h!*DZ^=;tRbK/*:v^E_s6qCH^Z(1X.CA/A&,R#aD`dS(nMB3JDQ_.M[JQl{66PEIb13lCp($*DaH@Z_fnAl)@b"(N"CN3c$d,0i?em?R=8:P.DCy9jt(^M}]gSPC0XbF9y$>$3Vhu%xn:HB
iQP_|vn!>5k1EgReeYZ%%?D7irWM>nSBoE-@FcastL8o)C?Xr-bsSudJ"Y-pdA9EkWZ@iF0&lFR=;)qo9B.x}B,-4]!aqOSAm!mPqvDTo2u^@Lb=K-gah>p@NFP`@LD*
V(__sBkrikWe.5_T@`,H0~l"-6I.J5MM#a4]uZvgEYaL0aB#l=P<8m;|/-;UE4qa87aThz;id`Bp+T4=61hgfVct:62rWc3)FmX3iJ/Bm~d;R-$~HS6Scl4^x^YVbxisa[NfaG1^NZqtBuP]IH6Iyi=8GQd0hvGC3<7AkFT#(Z4#Um0Q]U6TUb0J($B(PF3A]se?K1l%O_@8Wi"C$e<"1t/SM&Fm!k8EpzKanrWlO!uTD@(B@&a"7UUP((f_0w3@Kk=P+}(>u).X03K5vzA)%/4<3:U5A5g?"wqP`9*L@ll&;)M[o<Bfov,<(<DpLfm[qoEW^-53t;!-QY;nN,^=ahZmrT3ri]d0I(&<=E/?Iz=XjqBtb>
j,wM=bhp4D~s}DPwfjbaHjREA%1t}4WDD/##O6F>7e8O}-,gtC=-Vdr+9voNRVVupZQ,)dhZpQg*xdXHzY;[W;OeytibwW}rlIA`mQE%Y*CSc(x
$","82!Ir_:vfCB*>&h!]ZKm@?$y()3/
t5QW+/@~^gH`EU-MFH-ae4WQDXY|qW[vq62G+}xj>xip;]>jbGmtE50]@/l72O0"piDrmlS~-%9+pY)7pr$]"9pnbqO^t*F3r&?p
%@8]V$v*a`v1X#|Z}8dWZo383#Ax~QYNfCE3sO/Sm0Mek_b2MbM9UtGblBro2R[o.9yt*TyF>.XhhTp%/<g?_NZE.br*
?$W~lItNb|){K,"vSe[W]!tbR3(mCq8nQ}>kH7d2daSm@x(yk/iq0K&R7`]i<6ut4-wX<{U]Xb2}Y^F5(9l^[fKeM2^qdShYrr#<^wMpVghMgH&31H9SvGaa:3Vvf|xEN[A&DdD_eJZjCKe}lB#1*rbo2=!nm1l$e27SDDAib+9GS{Pb:*IRS3fUWn&,U;VmQTm;QPn0NIi)M0*&qoG.P0NwR@!-5PmR.O"(Im1YP5dbk&=^@)+u_`I~sl(`NljT?e(:E~U%E};JyB
XY#.`_6o>g/tT]Xjb;[o.It7)5n,l@gi^Ib@a+?>OE`Dzj@WY=q:WIGT(6eu7r&5C=#N`#bene[c%MmxE3JU>42;{2z-W
1<RyG(7>G#XIf%4:}Z$J[I3m2H.-y!+J7F,>tSR
E#*7zNwAf+8$ks&MlL1CWY~[DUAa#)Of<
%#l%aj!;xsbw#uGhuZ<qJ_LfkrN.@9N2*ihmc04iK`
@*1M:xs,Eq
Om+U>D6B%p:(f?<Md(OoNgTaSnjQpZ<7/S>tPy=yZy.Lx
Avr_::<$Ew<j+R^Z;w~gHd5?[EK):I1ZZwR7%fqgI&iZhIT`;5HE6=L:kiq9ELLbET3I",_T8G`2BfrFAsKt3@,C(I^VW7Xg)bq2^%C+(D7WC#PK7/IH~Pa!Iu!Cvs:]w(O%/Eesp$YRRiv>DW(6!SL2Jv+0,2y<P?Z=w)bUumowKWaApy
s70Sj+Doi}jzD<w+[_wgd9=|H9vsu{$s_=`w+8"F,[.%@L((!#nn@>=pfYbiIwO!ENk$]~8E?,k([=C!_{kM%0Xjm-3l"SmDp]E@H!op8[pC$s2h/^_aS/fyt.T>D[PdEO+%4<;9#uwSvlgGU9/^gh[E!Y"vVOY
T,@V1fg?X&SB-/E=00.6-$eikDxF[ALbdwRFHTcH[55gO9S3>Gu+M{sW-2DiI*wBkw$:eN:
&J6IqG`hP_sNN6#vv-(!GX(g)gv.RQNB")E^b$DccN>1>//w"#!g-2.mjZaGlXh`/13[;"N^Rk:*LC`n:kTd&vGLXGW{!Q4gueBDx-ClKYS6F9bmh@"4agXOt{cTZ}X}]4X#hnhPM![,Nf9%sz8Wspw0GU`18H?(sX
>M[rQ-)!`4tmw3}63=2L~)8i~Xkguuz5?;?Kec{ZsZZ-{l^cskNd&$H64=bts"aGpGxqw"Y
H5sqm^odtw~U>><If6__7fwgFq_)TvN>#"sY%XPxDX~N&t?y/Ez`-Mnn@NCpGGTdgUqA[2FIO<]"bk]SVlV.S`O`E@=;?Ce,(5ohld-][YGfGb&Fmul
$FF!K9oEgXjnO[,TZ8cVM>Pd]?/<d6S"<o3p,H?$_%ow{`5(dd%Fv,
<<#,0?)03[,y1_s=)0^=Hjf&fe<gme8C*Mvso>].
`fyUZ
{
dFOYK_0t,nsAh!mST_!2RSX6MxP1sShta){Hx$7[]]aYW"cOPf88rwSCft-6l:IizmhVy!xSX!qogEV_e;<z!=o:dG1;P12%}TW3h%RmF@w]Z#42,DO^|.yeUvtx,N!bp.W7E3A#8&Jg]=ff5EgAi,k^Uo-';break;case'ms':$mc='.R]ALh"+>2L0q0^["ATLA&f)xeyG;IDU>g#k*CJ2<.>d9+-4[-J6cUEs_o$tS=CG1WrI@vfOAjGYA7(1#eJqSE9nI_km8.O]`[$r(m{uN.5;YX/FlnYTjJ<Cl)^p_?Ud~j7V!^"keV6)n
%=!`$kqlZWskBLu,J
>?RlR5!b,GyBZcv)rV@s/v8i3Z-d[KcBH]F&k33ImU^Ufkh]cqCLDF3^T;@`WbMJd]V7:qBBe?m
ijyx`5F4o`w6}MdsmLOXwu^KK]~]RA,?>eFM:[:3o]P7S#OwVxHw5mSv}V[Qu]W)jr+<=7Kr>wa_oYlV`6a+x)D<7
WD(VYBUJnf{&jf@oA)T3%]<mBoO0~qJc2FkPyLxRGB{M-jf>}]>TwYBUsvNncnP^nJGX,<%V>]0[AFzK]+".]6.si9Lb[H})BnkOWf*G"k?1;_lNok~?$AgFS):1X6+kxw(.C!,?;,;4Xgl"exm]}
b^|`6]^A&wCg~54:luW_<%xgqaidY?0<Mb?.iR&<ou:7GbB:ycZrj8>hEjLcGgkp/.]_{nLUJP%mN"yvJ0=0_Z3g^?FF&/60k%Pmekh(TU?qu;X>pK:H1lRab/6,"u!@Q)T^smMbg*!_+H^@gIH@n$:b(fv%T^@H=Uh;2
8)M]&AVM:?4X|c@M]rcqZ*Zc
u=sPETdzp@aflrDGrk_Mu,6,#_VaEW^&,.[M<*:~(c1_^9(-*+99xr$Lk#FiK,0xf:p6fqcjVq0zN?6+7")oTqC>mx=5nw3dXsw{Oip/?h>i_D+FSLIsx}(L09],@)CFs/c|v4g5Xabub!VU1|
-SPkPXu5tUjdv]?hQUwl+1SG@@Fi6@/I,[#dwg_dQ)e"%*8-rJ&<[Tmmyq"[zM9;#--VdSq](FyZt<Xnp){7}=WL$*p,3po5QEE5Tuadj6Px,#4a=<tG$dD_h.=0n&0uh",x1QtX)n!EdW]V,Of2A5=;kTm7Mu+Sj/}N/]YxTXxf5Ir^+38d45"Z<2$k$1j3J/eIXQ#!a
icP@kQ[CbPR%WAUZ=2K&g_A[(dg
MESD)D~(O%r&Pgu5=uVf1n-5^Me[0NW.k:.04M;YAT,IZR/WFNw]Cg4^pnF/XhWEiHd=
<?]YYw?tM_u`Wh3nYr![$I(xfIf]SFR|g@o;BrP;<H9,p,-R_s.kc:BgH?4t/.y?3G[`(cq/OrL5lTl].QEp5Y&}9"sTk^-D_op|Q%x4LeVty}F<%(.w*3aDT-da<u=v8vG$Adgl%FBx[66oxyPjKv+yXlR=XL"t<q.!/RgmDkd>)!wI5Ga9uM7,:kT[_GFm#5o[<yBd[Ry1Boq.7w,S/):KuSL|7"<?YH2`s1klx8%k_N/DH(;;_]W>;ym.HqhB;g-fVn?O6zO$0.g
%@p]?,Acbm.26%&v_/YdDMj*iI<=,><=3dp0e
I5gRZ6ABQ^?blQZYWkMW8jMxq
PkU
/q.8*c8XbBUnP^"/Ak3P
JLy%*-WhtBC3.o|Gz]I+7aGE)w|)$/Lu!PVSJ_F1hSE@K(|"%azES#P0usSv%f6Kw
boy#QR4F@/iRBe,b~/lN^"YCE-]]xUe",)|cS%E8qp~B8qjTSA2`kY:jZ="Isih"3gA.MpV3fh;k=.KD$G1cui#)`v<w6"Tdhj7J+I$kpO2$:2+mEv"@;S>+AU?90AxoCNvpa0C`qREY2@)d,vwr}#~<|7Q9!pc9tp$uj&+PkHI_FXKYrPCWO)Nv0Ql^D-l$;Q;68/#NM<(
I:[bfCH2Oa8g~8_[ni2Uql<3W=#Q##+332,f73e"{9C`md-(76tU$`|:mc*BZZ!,cQqAJ5j`#t
d,f1_hjJ#b=xj@#;Z^U?ATU[uZP0EHpk-2>P1V@LYey;a^.7^`w)uz?`[b`R<@T?C(3.`(.^Pw4$BAiM.O5w>wjKM6c[XbRpw-%orZbniX*:dYGDT<L:73EKk}$(^hS^(@inU?57eNYGpQ*ZB6N4%("jUu?kuv/QU;]h>
1C*Kw/Tp2WR
uPnQVcB"Z<:f]X6B5tvPEv"{C{@0RZ9qv
4g-X]I=xGYfLrFmPn
%BO/O-dU5aw]8G:`4S>syL48AI4W/D^{hf./<ewnDDu@8C>{)/ZN*Y
5Z<=8*K-]G6#1j;i4gswNbBA!;-hD2+Ac:@z&soc<e))v=FK89Q:SK`uC$?jJSaJ[l(GZB^*.Zy=Ro#u5#u
l#{>C1dK/
"T/!S$0pN]FC_#pClH!j"3EY
/njY
#QZ!98Eh{^[X[#T>JENKT2%;C789bUT?/:oCx=0AZJkIKH83}&92M2-baX!b1(;An6k0GvA7O0c1mjYj)Y*-#mAQlQL,];B;]
t43(`k}<eV(pKZ~QRNxC>g<J@p~tmE_,kWyPm_6i$$Tk2eq8znUSh,=g;xAT,2Z_+ygp%(46EL:x/S8C37mkrE&47459N><l,l[Opd{X6q8)KyFuq&t-{T;uo;]d9tsJ[ZJY*U,:*#Zlc:n73N&@cA)i`!e#?tI8j(T*anpNu]s]eSBuee%(s1LWY.Q:/"M"b0$1cv9F;su3@xMXg9ZGZf(&EZ<0uV-V_dnZ/0zQ1()NA@ClZ-km?8cu+P#9=4elU[<Y[E{NU"RC<d57RhD=N#HSo&rM6HeF?51."3mR=VUWBVAUV.NwNb&
]g4TxpH]^!)Cu-QUMZNx4,)O0*fv>+?YI@e5_>F&
&`.sJ}_^C<_atHY,7orwv?LNER0]1-p0_:r2$vQ"wq2M:?Z@>T]vAOR6Svi^YGrh&v2&8a[^="hRkd6I.-d^0l2t8GURt[**m_.712M~e@%w3cNoNw]?[7A?6`huhtqSDZ)OXO>/]Z*lN`AmMnFPmLk&wA`c#NWu)7Nv4H:s
:PyG)UVPm4@]hT"`KaB`3@J7Wc1IpaR&]DurLQuGUP"Xlo~fNi[5J8a-8SaZd%2kMf(rzoc`988K1t$7i3<t7hj)os<:+m0[zA}QpO30#<ANF>w50oeS`a_(Z4W^7=%ZmoB%~T`>w
mb=
7P[8WNasMZPB:3f?;wj!0:>F@dT;3:2c:0_$o.0)xxzD{
}NPqCNE1-2_`+:
G2&W?cXxd?bMM2v6<FZDxK.bq0I]#x1QvZS(BRZ]l;5E$H[^nfc_RSTHMhx%y#)O^cy().D!EnrV4X5bQJ%dQQf]I-!Bwa,N7yx50%c}8k5{Q/&hJO:mhlrTH{!AqZsAbRgf$5"$w~H]Hy3Ww$P^Blb$]9[W2J5AjnQ!MS>*MnD>ggOq8WUgk-gwR
2?[,eSAO"V!Wvb,woX9`F*kO4;lZ+:DVhQ]TsCS#-:tXds_x56ct@,D[*/Ki6Eaj<5WD_nc`qS/?om%8
P1hH
!**@VY0PE1-k!bGA+{Q$KAxh!VP2=[y,*.,.WSkBPc6iS@`0]sy~F(JL#9C]6Tt!l`-@nyoTV@*VG*4v%EjEx:fA.jG)c&0)`8jl7bwAFI"AeaP*(X`=d(CA!FA`gRdR[Y5CuB!SPmf-WKP,4?Npj["H2#L#orTGAvJtt[1`@/+_hex|!=MXum@Ie&`MB05Q#Z$_*DE^@Rc=5=3yI(^@PzT,>o3x.U*exMu5fhh`w_W^_)y?9?(dG1%fYaZj?,M_W,-r_y[6kp&y4&(-0YCqM4#P.;p:hdrv#w@0gwUOZ+5>o.Hk)JIz$:"7p(=mg%K=[w@F9x)Qd>Tq;-+Ey=T<l7SkacKQW",dme&$c"LY81xBu#u^)8JY`lEop`$.nly^W0f_c{3!ok7Tohol-AeY(0t|%PF:F7J
_,W]j^5rxUz((U,-j:)K5n;..r3L!x*Y)BZ3>6nV19d8U0P^Y[-(K9/E.5k[+XaCJR:YJ%:dBzPZBI(S;IO:eU+h]|:p!X_#6yVZ"yTmp|K@ZHo|>>k7,-3oS5uH&v1-#M07SL)wL#b:`aX<]+/)s!4"S7r~uGy)jE.5QlP9ALgFr}2SeIT4oarUqJds(&fci5<WP2yC9ldH<O[|!_+meZXhm5UZi`ip!QAU3u`9),uQ@ldQty;h;o"-!LpU[0*20=hTyy@:L@#FCr^U&nB<P<b}y%-$VO!)+CVPq4gwlBbm
QkP*v2FRk+=GEgc6auB@55_Bh!()2KW8AYFy~oR[,&N:j2|)DdX#f6urteKsas>/r*{o`$+P_7ETHop92l^I,bR%KA46q=ymG$R"N!veY&$z)wHN&';break;case'nl':$mc='+ZuAM6LD),{0}N:!d9R8Xpgf
#5^t;7n61d`r]MPp^u5S9D6U;+iiAm`453YnbrSS_#To
#<b3g!USY#ktBb9?Cul=Bn_piWHE
p5?FH&
j?~JM^P;Ga)kK^4UO^Ol|MR-vBT=>A21Z]hVliqho[2kLj`mWWgr}D]rI<UkHm9ZY4_J8G_7,;zXY*e]~uOqFy-g__?Dv?MG`tFXy*&?m]X?R54>eIjc2scW/G)dyNwJLjd`Zk+!*%r>aJ=gkq-^P]L(hFR7M%|iU^uEX>S]itUx#mIc;PhxKt+w*u:,qb~[#^L[clzN%yY8?ED,MMLnao|S+c|s3rQ8[1I(dTU95&V_%BDk4Aim4JCw|B%b6/3`3k/0S*`B?H]x;wDcP7EJyqi(i3~1z+{3qGmDD3Yq/dY^+XXr%^q!I[!ZCGQl]^0GjTa]~Jm0UL,ExlF<nji?C5hg|*"H*ZW;xA{j:e|Pyt/#Z+:egoC]^L4:)J<@~UwQx3eHi,9LT"}*S?pFIO<JI2`BZ/RJl0e_&CDr0m),9&h+w+N?/3Hih=LW01i`7_GEx^:-ngtN#/JM{TS9hPiHrc]89vRkS=
N@r&GX4&?Jv=K[l{9l,L^)lz&O@Y_L&#?H?F+#Z(J{w,=}p>4qBtkqaFB;!BkDt:0i*"x/#:=6Jd^PUQ=xo!5[w8!,-~#f`*:/I]s"sh<[J0V=L=Wd]n8ca5E
bY^px(PLjtt)WUk~bFE`s?s4rQ_QLG(7VA`@)dx[c*#4UW2`t92.u[,]6vX}Q=78$qc27n3nQ4^#Rh>?
}nSmQc5jcX#M5bcw=-oq/]o47:Qc7MXyong,ZhRqNJA]QAS;*FWs,DVvU)X]6=RXvS.9AehR*@yE:w!qLB4F]]5k4S>+rMJ`+G_7w[_o&rkiR80H4;h@Bgm3B7L[M;3IRy4]d9d8Z8;GC^+e`$D9?A
im
o82;x_FD6<h;)?X^
sadR
zni,YSG;G5>a#?+R`D+^@pq`!%Hs
+`@mWi>b^NePc5782sMmgfWPrR;xYcv)GIXOK.-9H0#~gwmBg;hT`H*>c1PM)dN~l<RobY"Pvha5-@Jr24g`I`YEqA"bx7t+
[m;)|bP+!_[,"1p((>y9%Ze(N)q9AGB;SEp<[amCzVA[{wmoI2sT=(,dKS+(+&07[9|S7)^-L3fF#wk!AGz20KUvaY&5b8V)<D|BcpcnDDe7NuRBFhPDSQNW#BXFID@Pe1@p+s}iLmQI#*1FRO|mh^3n=+uQRc5lRxYFw-V20`vWPIO(u53u^07uMq]W5F~SY++TsT^IeB]JQQ4.g3nS($!/;Q:[Z^1]QBO=wign;l{KI"{Yq`gfy#ED8h$JeA2[Aeci0*nSEV0:
ycF0^<][er$"laE(`r/Eq2rVTMmEA,bt>#<|;{u7kzq)b9a$qOS,2#IVLo*Dqz7gAQ8R?o>AZT*eB9B;)6K`s/ez5+pI^)?]rmAQ)G>l#Xhl/HZOY_Q;&4g<fTF&<^GZZ#mky&=)g8j:lU?i(&q<k
A<Hp%M`GH&+>>ho{Ppv]#lvRDlZH*OGtKOrMSGW8UynX-}J;,fatWFNTpsr*4B1Iu.XR5!g8Keaj6/6i[#BgRWx@JU?-;{>m[Ck9fMy|/Om=Q`u;LPI(J4`yu>[|pVcZLx%-)Gjt#%9)FvHyIXY96_-Iu~?Y-@r2La39sf.Ft.;@DqKh]:qSc651=3SVY!+`0YXk9ON0s*FFu1A/l6qJWi6Ki;iIf#C7SYWVqi1=;+#WtA6`;De?0dR{bU>^InjM_!2&bv:|8e<:83d@UT%4/$&2I]3KAQH0k<V~IE6kIw[-pv8aX^eY]GcTiU[^axa&Qe=bEuG42ax{9C5B?f@l7km~JT?X"2>OM[<6-p9+V"Z-X$)?.6#FSX@}>}(Qjq:?t`q_XFp"6dN3#!::`:p]YYA_ZmhaAOoKQCPo#o%BM^BV,m8"*D8"Fx"U6lK>:.*KCZi2XF_!"z=A*KO#j-G(K%keCm!!pk(POOJ|edR!Vrx1w-9>"tixB"6,1ms#0DhZDaR,9hH#IKEkS@dmuMYu*@hu,".Ta#l]$;o7P$7[W|KGX!I-B>lIh$oenH.N),UCtb9,</ZaK#VqeRZ5s)Of6ahj7VQIk+luat$Y>!S{YoGq2Y1gX_y~v{[)`;su.$4.x8;jd65*&w$)!d#Ai6pK;Dl[P>>y:*M,=NNWVw
1BV!Wb[
(nx#G]m)4ZsRpyUr1%a[C2r=EM?%li1^2>3]CIV?;h71>gEFru,<h:UU@1R<%d.eHR2Z&/)4n=31~p:HC7f+7L`w0Ra
pt3P~tgfU;17HgU>j[I`_54h@]thLE.4hjfH3XN$c]d#Fe:!,j&#5h/`9?oqlMq^FBa&T8bxZcrBQi:@:/zC~x,iORngi#t^CvYIVT07<H$ACm>!RSgP1vAQ}qiO6T
)#q7?3d8%3(XKL;+t^ob/^OYIwcIc,K],1(7X{L2?;MqK,KtojtkMsdch,_$q"!XH<t+=]_na$R3t~x@lg&G6>y4&#Cg&^QJ9rP(MAj^o.8#kg"u[l=0Z>/KFv
aU_qnTucUq!c<l#NODF>ZL/3!BgQRva[YVAgL0x+#;2,`U9xu$(-0eu1V0S$XR9n^5(m.FFj#Ao$3iX&X#JT-"4SK#)qOVz9
.RB#!;eDP}YynJ+*Vqyk:5Z("6pNf<6k"zn&$d;7fzH";Uhuq,ijTGp6DpftmoJ,u|cD9D>x$;iZPKVPN++!m{$[_>l:P~4.S)4"8dx!5%N:tnY}j5@/"#5L,?=qpXJ;g-7#7XS%ya.)Yu"gX^giFR"gG1.LI=..t$I-%,lq*;$GH2W{76N]gQI^NRowVCU7H6L6Frqr*n!YmtYk6w;:/He[1t0Ha@r2.T)=`q]w84lbiqKh<Yk09w!.rE2*PJkg!_I/SyowST^J@hQ5x,5_5z)5@N`|Xypp]kNL!
7U4z(Y3?GmMX@~4F+e*k%`"<"RSg%gY1M_jO$`7[f7.[!x8JSUSRcPG{m"!(FGu{x-8;vp8uf8u%D")cB&#%S
_(fOR`8hC.hqwt"fw}+Ye
_}vUSbd^s,B+HVCaS]8!g7*=<-Ld;o.!v),"+KBf@:J#Eoa7e!l19b*?FK7![s8((n(,k]]>*J/6IR/Y8{Z_2rdC"_?TlF6(4j_gh]VF#VNW1RSYAC%jRAE;fjEk>qVGV^(%KW)DA*Th(X&9Hj$Pi#GqSL(ltaLx^G"mYnj,JQ#(s]1pY=suRb=WK]D40.u,/(U[T.T5)/_}u:n81"x%tD?9
<oCcr"B@fO7>U_sLT7tdRlu9DYy!BEpk49pgaaUjJKG
=ezd*-H;6wL%5DV?v"]=;?.jrgu2Zr7#zF:n@1VN9+)rfU9:kg<ON*tLn;}v%<>
|.iB+.o.Qd.KGAi[^#BvtM80E8.63d&=QC(!?E+x]qACT+1"X!{=7!KNC:&JiOi.G">XPTpe!Dzj"ah2a<205:`od&/2i!<$Q!G_EBA
.J[^G%WQ^
A]*cA`!y&UZw(ko(lYT8.P-fk=UH6r;05!@r8Ab]RM?8u(kiI6-PP?yoE`T*6SL@v.)DD!NM;PNd3[pnzX/#+-a1271*|v~Ecyid59Wqi_;$eJ0wVpH-kR|2#Y?WtMJ!a1GlG&mcghHx
:;0gSv)([m7!
|1UU(_6Z`h|/MSor~P~JaTy&JK"P5NW*INjB&*T=%d[o*-NRct.$x&tin&3aY^SMTHz[^HnOMA<r>^tfl$h.;qCV%`K5ndJm,eC-iv_U:WaoJ)u<p_MYSHW3436$CjiRB.#QLqY?NIGJ6:jb8-cKA8&Xd,
]YV^JS
H*Zmpd`?Y=4iC6e.r@cqOrH!7&:/WC/Po6a7c7+Dd5~dQ*[o_wI!rCHR$mt
b($(Nf%Ek]B8@YM.*x-=7&>Q@W0P
q3<`
#bK_qDgAk5JD"^&g2g8uT^mq4CWQl;DO]7rN{p?F+d0CwB7J
,k!di!mG$rrPE,/pCJNL4f9~ls!fg~2TYO$d?&4v#j"K`0nBa8&H4{6Q"m6j=|,~n
e~ya*&aw="C`k`FmTr[cFO2b#<I>65%)Clt!(/WSx";2#F_/e=q9S_GZAG@0`}L?&G%~Q?l!y~1{5;!M;iApOu3h8!.Qu5](0/P;R9n{]@@5-Ef3]!,;57f9V[v0Oppvl;3[N4^7tT8$BHb_6:dq%5Wc9B6L(qEKucGm2rIW[k[]4t)_>vKes7jemM$~/6g#ZG=|A3Fu9+,z6q")^,sQQtHt^}T6eHUZh?04`xy{r1w9#(u+n`m0/bIOlDi$/VE-5he*g<l%%B<ggak[k,b8Nwz&&UHk[5aFC#Xw,9P0h:qC#;QVly4!.6hr2{im6aBRr>4&qyvAKhM[N-ni?s=O,b^G5CcwCT#1b7lPo"9f_6g6=v5_,lbQEvBb`ZV(s
fB=FAi)/4*n!79=-Xrc
bpRks[eosdtKa`C-y@7FBr/aR>_/AO+,8m"3B!,o;TR6cDsg@s.HEV:kHcTs!2k,tS3R"I^]3dR|9WS
sVd2chr88?#<LF18"UbqywC%';break;case'no':$mc='+]^ALbPDI,z0
Y+$lT5OPX;4_E.BM4tPYJ?WFpxK-6Ilh;PUc"YGM2bpSyO!8+
P!MHU85UW}lb5<4]p$jtavb7JE
I?VSQI=:Su7LL`#ntFhZ8n<`I0AT&;XEEv
yN:b0$ianY:B[b
}6<[K4._9<x/^5ic.JPZ[JHxbI@
f?l9ZA2H!EA<b3!ST77?#xz9aJjpbW6KKyTm}vM.?>2y`O<O
qb1ZD!k:F/fGlbgkK!A~g-pfx"J(`}pUw55zDJ4o]^7xl9r-@"59y.Wti@w:uwwdQ9W;v7p:mBXP<
ygBXMSygy^9!2QpJ(Ck?t~C9aJ_JaC36W_"US-<V7in3b4VJO)^nB6gm9MdR:eFh6Sv4Z1gXd-9})vA+m<*XfbL0moq%vAh*t3LMg#&Q`2`s5BF8#O0*)|vPi1g[4_eE?4!5[;2#96`j<X^
7T"L%S2OR"vVm3V%%sjlDR.!Z5A}dPnty1SnvPm]DMcx-iDN@.-OYvVB/dTUu&3YFft],(]V_b?W.5JV](P3:J]r+l?*CkE:
eURS]5H#7P%(E9;9qDw^JF!DJfDH]:|(#wWvLwx4Mg<Ra0u0l@d>jr&J1]h13+9BrPX@?-ffzl.A8NugJA1o0@`:tpba`-=6kkM^?mBXojcb0s$nQ>sG$LY0{1q4,wxmX$=6YW6&6TSkLf2p$t]Xh+kpe&_QN*KLw^
V(jiF05C/ogeH]60q)T`87c1v~"I8ZHhm5V)/9TuJK`:JPWXOh#Q?at{A7_XR-89H[pugojQ2+["UL-pKotUrK0?E?p1I@aQoh65V0y(0]WW#/E>+$&V!<chmhm:5%V5yyfes;5w8"uORYQ?@ALQLO5:O~I&ha.;=U/=7P%yaW7l4NNhmH:5!MlzfC)EmGCr%9"gDUE//|?UvV]p`Er.NJO
#0a53~^.3n)WeQFJkp[AwPc=I3*k?wmQX,c!7ab@iMt.pk/>R,TYMU[XFpRR
fiDh4Da2SR#vgS`"HbmcCEv([g,x5G%hdK%2i-GAc5+?J8/0H[:JvZ^*cfV?T&ztaMxu}@a$auN,UK|5bv|reou#_JE4Eyi8
c$K(OWqHG,gj,[n#SD?A`SV~GLf^0Cb6A-;|_>Dv;bg?G8s/cB5MFwbv_O$n8zMBG7l`j15IC~8_yCwHk8m^Wf]ZHp!Ld<kUuFSe/D1wh-7sb
fQZWl,9"M72~fPUyU[*2B;[&b<A%jIihq9Yu`jkpF#gq8Ux<sc;mQ{d5jE_=iU1^@bmL=dKA@mw{:o`~U/t,<]_AFmk1T$I-_VWfQeD*lluJ:|?)h]_St7y>@;m=ET$weeeHuXfET4E6Z[6?X<)PkJP7JIv4-rn,B!
,(QnnpKL/XOV69(
`W-B9^8P/GNg1wCV,MWw#/FLoQx>oxN]|ClZsZqKQRzjq1Ig>J(Og!=?1$Us|I]_].]Z^eYFV($pDv-?{/8NJG4nE#J={8lMd,WoF1P=}[c(;eJWSkvC6eI&&?6DXG;;c%"GvB6Hep/xGW>=:ta/TQ.eeDYxC&5=$J::)ebgWHZ>s4K5hgB.+XgTiP{QB$m"/_J`dY").vbr[)AG+nv`=+ClBQ3&BVQ#0Sx?NptP~"u]kQ1I|n+-A>"Qn9"36Fw(#-
"w.~#QxZYi`^&1Nrt6KX(4<O5<91EtKx0XYo.^3#W1vL_LkLvpU7
0u@E&&]dn:tGYl?gypS?M$V#eqzXpu3O;nGFiJ!<sXrHYRjnr^+"})[.j0S?nT%xQk7)c$ttI>keU:kQUJesL;!R9S(@;12P7sTh~_gt
Es/rM/[^P4rUhkP#rVfP*RQR+G)"%Z<N&y$Mwya/;Z&~I<w:u!F{=mo;Lw[=3c:,Nz<mnN6B.+Cf`%7HmjW6T#ITJ:Qw=g9#"fP4]g8p:0aF?Qa=_N"S[amK<0/aL5$Ab64J%|t~EP)*&5*Ve6$0H/sNABRFE(s]?o]N)pb9U%p7$N&50Q9y[/!8JE]"e3j!2cO<@fR{P
Stxqi>7Afx&^f*Y{$|9Jrs-R#ZWi0(fJ5(`Veo:kHxGj>4CD-}@{fDi6M>/y(,aPxe<P+X*WAZy35qSY7sG)T;EY*%8jHcQd?FWO^x=TE%1MKH$ql>C=J}W7586gX1YzQo%)m>?liv/9=BZ(>*Oq0b?5E{;Xc{2=W@B,bOp8BP^Z?g6C>31cu:A8]ig192`uNh[lV7w`xH%@x[Z*ZmQj`wDz=Mn-8HN,5~Bm?w$T=Ze:?=BT[p9-;rJ}^5
Y#!./wrwY/Vljhu2Pv]L/vkjWj)5ELoGDv,_zICqpy+7LZ]@sr9xdv56_MRl;oa6L-Wa@U>V?$
Es>$OW6C=N]c:==UoJ:.ZUF&$}=TgW<@#n@BZ|i;ApH{lJZ{ZZ;:B#B[Z+MKOrQuv{[WFk&<Z7;wBA=&QCDY23FuG4c}99A%Fm&6,?CcS?$L#>e(7k7c.Z/NPlRA1,[9o%"@.GVB=t<A4%hym`2P)*g:;3.h?4${YYP@bP#ciHVIyI9asYGAd(R+@B1Z[BO])?$<Jjbv^vC#imrD]=TDK>uF!YrFAhy).-V%!_9n(X^mXD`$+pFPl?s^)HDTxZ>d?%c5SR&1?$;t*q0l_8u7AHC8LGTB>N])OaW}2
T9SwP8bwdu70eTf&<-ywW>2211m]W"M(-4+m4h]B32Hi7od-GhdERu*/%*[[pQLAeX7%c/9J1+oR";SWOgT6
e!YgFO31T*j%b%xmf@Jx/>TR^eLOwvd0j"_E@]vLJ@"l46cSdgax)0D8RN
w+-<h@#h<|qO9N`SA&
f3UU}<88]l!&
151`F-xp?:w4CxaQjCF9.@gJLf!*GMz%=a^i4Ou~#jc7!`H!-Qs=I^J;/~Hdi7$CS-O2:_W=8BBiu=14=g#ZbXkKj=qP1HF7.qxRHu*Gd-nqV<+j.1f#^3*/w}PZ2,=0IRfUaXuqJkY<&bJ>6VGVgG>Y5vQfX&3SIe8@<U,EqiAzf<#|A<iXGbJ[9dIUCWpD!E-0*;h&kuovm=Ym@:H)jM%/Y{D`^{pTa?=gOZkB#Jpp2Twj(ms@EHA3=_
-Uf)I<81%SH*ypimPrAv@FU_hTv+oS-V#cdrk0O#u.sPbq{wB+pBpi%wue=QXcN8zd]O;r"B?y:-l(=kg3m5wWcabyzZDmlt540gsfD7O9W^tR;A"ZqjFfz6%(}/Rum&itNQ~a?ZpivJ0y;W/@eoU1u_^=r6L]eQp1i4|Labb4x#Ta#c8(i<T,,tf+&-@SVp&a6Bf[xU!Or7i_V12Dfi(,T,yNB:rxiqW>.aD+5#u_E+jU{x3$G)Wxm)e@>P<aGYAt_XriNc
nDN#Tv/ocUV~y74zoM5QP+QIx=_[O-U]FBQ"8EY)H^d0Q|u}!NL3!4AE>9:;dNm^pU<&kTP|H]`|&/[71v9Qsji,b.V$;,LtEqq;s{0Ch"3pL]0qG2m^x7PjB"n*_F$K)>_fkMSVL9Kxm9;h59LdW4j*,)Ydiz$zy
!JL"T88D`L])t48J4YAx*Ktt65&W9Hd34?nA^hp!E(.@s/[ee%,"-0?Fjt5CSk-B_Mm_iz$UkC/=.A,+1%
9gPrx7MNze"R2
Z;%#$2Xo{!M[9?Nu"<N<*rXY3^.]nmsjD*Ifp_d[=eG9A7Z"gJ|x".BV"h!/Jp?o%FdqQHbacekHz#=YXmJpuCSYU
P;Su{6+lql.*1xQvdlF>VDA%j@oJ5-kju("gP0HaepB-s[t.&vz5@-b2Z!].~_aHanmh}n"&_ukM*!:h%H6!>8pqC/](.>##[x!g0OEq.E?Sg7WJ}N-q|7($[Nl$%Kf%Z%ZPJe6yz?on@$LA$2#K8!B=A*LR}wjCrpF%
G&u5=b0R
u%
Fy<u<N`0oWQ{qcPh82SBMW&i)J*oG+"n<DmE
OjcqOLWr0_Y>U<($yA>n9G&*0=^lqqsHop*L&v5)6>{NPF{OF?^(~7L_q^}!#
kCW8-1#qts6)xj
v?%Re,/4)$"|>UZD3g[fcLalv)^fZzsdKbHoLXBLRq?%W8Bk9j.rc3ZMM7Jja-Zbjx
w8$`_FJ&_6J`3y{Hi]w@{Q=)l/9>Nj!GOn<(u5vjNS(rO4cb8]gS|Lvh[vlZ@A??Hfl1Oew+(=Jp&,Iev6DO5XZ^OFvaQ3<BS!Y_8n=T3sTZD^k.AHQ[pph8/BFS#GN,e<@o0tKmj(mm3@~"v/s(WwRHts[?ee*x,f}6?GY-kxO0o]FLGa&1-5U^u8I.wa"d+l<SwKfjw?sHGJ0Z6xeM1D8L1Uz*jm,x(*flm>bT[G[hyt`y0d<,BvU/0hH%R%seE5+[j!MLAm=e|stF_Sdw#++_i9>)_TdyFwt"DtCMmIdT6:c<Ts}9NmJyw""';break;case'pl':$mc=',]^AM6LDQ,z0
SU,;@I`J0FR-e7RVVigw6xJHAG8(EhS_
E>O]?8nu;!RK:C1-8x%icwt=*L3E&ytAiHzHH][GJn:vI3;>X39fK
#gpS=ddK
WeR+PP8Rv{/0z&j>w8t%h.^>M:J}q+L~7D"N(zxkJ}fKqaeJ3j-*`W2gD@_W@O?>:!:"m>S>fB,[6{,;p!GbjukS;lOq8J9F&$Yp
i:*p++SBLg_Qz^@sfEALKHAqGWKKQP`>j7}
,8s*)`C6Nk51FyuC/KiJQ@*W3v
ks^ERSw4LuJmM0o(K
L`mJgOi:`mm[xyDw4>d[kim-Tem[wf0eF8<35/=VMNHPP
5(pJ[
dQaqoEje_^2bqG]#mPP_3D%%VI:ju`:xpcD-EP/3bL]qr:*ORIfitDG"!y(s@79GjI+A?Ky4&n4*Xvl%HtY.j7A,83JZWxw%u@BTQhW`.J;fPu)~q9(hy=_1ij3[=.u%9*]qf.[Rv,jG=o$$7CX|l]Q/3xnfvbq.9s3F$rW~PWVr$;%xK+s;&<YS$<Il(2M5[Sw[=QrLi$D+?Wo>drs-^t?ev8VCBG+{xM8sGF1+^)S6+_>|j9H,UYy]Of.s]`3BsEGR"ep9ozDridstr=6nZsG}9x%C
J6-nVb3clvp+oa,;p7(."/XG0cN6zY(rGJ^S@K7GAags)Ao*lT)1vcO;
z!Za_4F&6J5KG<Lm!^G!3}%<$FmY0=Csb"w_/Mlwy-FErK3HF1Wq8@`Z7PkPe0;W$RW1ngIY_9fff{c&0URw,C+L(I!v!_3ntj)=_l])#r?}gYPoA`;Y8F6;py-jV`?BlfCh#robgf,}Z]K-H8m0p^"nJ^roTB(2333*aEbuq-[{=^lZd:RpV<JY)?-hWBw;I:TRpm=peIiTvb.NP[xbw%6aT=]-x@C/intHo-8ew8=UiFB|c{*$ylSg9?Rm3S+i@4!JBLYNx]0ZT4o~AvVx?k7WASh|xv#>gfF?"Q53
I5p$[FxK[>AjQ;8Tz@LRTn1*$;9&1:9*CIBg6<"W4%ArjgsMIn:IfYd3XKVP2P;dOuyM@2xJk6h@TDaVF(2(6eL2FwXI3WZ+4AYXAJ*q5RZ_UBbR5HbmWAgYOWzO?Gf8gu"52D$A@t_K7s?Hr@oUKK!2>4Oe+A|O@Gp"BOKAw40UeE*B*gs4AWQKKX>QkF((PfFSdFSEiRyw!5E(h0BK`)2>}L,H5e0
ZdiW}dkN0D&Q[t"pA)
#}**FMR7f#%z0!ATYC,,pr82x_-$17K0?>,6"4dP6:vkA7d3flKF.0!g!p7^:U/=&W^;^/M{V|x=qu)n]Z68gz$OTbQ$lG2h8&_ndCxC3<0
;Ck5kgXR#&%[w5ebJ(TXbh`/W0u|bq%(1
q^YMbjXN[Zv:gj5<juQGE+h;%&xD$IyQ
v_;@Lk]eYFw-Q1A?IGnss$^t}8Zv5d].WTU+/!t=f%+R$bI(eW*Q3@eJe?>->QE]Z4]dM<qLy2s9[i1B44or/l<F)Rhgw*#x/K78l?u>|&aRBkm[&l^*Dt(yD7k/n>2L2)@
Am6`N>BQ%3GCc%x$!^HX92UvuL>]^;4(FQ7
XpdcVh$.oQQj~[)Cl4c-;0Q3L)]/hm)C8)^oq,KR}T#:RR^FUyU$~StntHXg@Guq+ua8
[[WKB#TGOj=)UOO8=}#<7zK@jXFR7Q?Zy0/{E(*[%s%n,{-1f5uWG(S|U]k%MG`j__-GV39>]~GQ"3z&OB`*ej<
yQ!}"LDtH*?Tow>{SN*U>@$tTU:6ndHVxpW#;QT0N?4;dd-<gEH.5N:Elh+fu*WC2a9gNyb{QiWAF0Y|TSKUP#8pM
_@0s9.+9p#Hx4>B?]?mJ-=TD(#!B`0>zkgl"n;9%WDkefcJwtxPa&R=qXE[)wK"]d/GgR!#f
ZkV$2bSxro/Hs:%Hye8vFJ}%|VHfpJGuCg[C29pa95XnWM^%i&YjdJx0TZ%B#tWOvcJc`)zuDn]fLuJ])NK(tp@%LVm)/Ez_Epu=%qCQelmh*;h->>a>~qXg9l&fSUmME@P]k2FM3y<N
L$gtUeb@P?>nkO];>W(neI*LT3X*%(?A9WQ"?RI56kN;PWZ9X>(34&%dLa<$1MetUr-9k1PE0g+71!>3o{oi9t]d4(O+ax3E*0ug!7c8h~Mu-"^,T_qgg7MBLVofR,i0O"abvz5Caq_1Xp40<KY|kp).Ecl915mO;f`+#Ug]`$BNZjDW1@N0sha/1f&gR&/u4%p9QMB04J!r*kO1Ad"LT-)~b:GE3TfQ7C.(Cl)Z!z(SH((_QbU&*9yFs_Mvy=agVNq"4/YlY;+6iCUJ=n02f-N+Dx%{#i/"Vb;143/1;wZdlYoS%5iy"s8~::w$pla3k%*;dH`ma4of"w/.LU
$pn;#^,N`:UBd+XhipkeomN2;r?esPU4n!AYD^6y}%QTNoU=$tl#:
;CeQRg3Ffb9UifXZ:nK
d(-YlSg^NnS.YR%;W4-Ny>yS+T=#l;WNCtt@uY!uGIRU~nxa"H|H0%"^D%/YQ$F.^*:YXWF%X.tbiqNHRppMl@O!5^)3u3qIPqJ>dP(fu-J^TgApTmkPWm2^Aqiz$?z_o?#fRes#O)6C*]VRUq#Vlu#4;Gvh%S.Y!?h&B[PC%PQKz54
1jI/h$IFn)(w51Ei]m[rX2OT,-EN;Q*Umy7+mIH+zdjOoQP`xg%8bUZ6cZ}>kUBL}eK1VcKOw)[$L]sldory)BDwvRYy)2%oI=lyq+{2Fa)8zdGqIR{h=a+70t/(t&6N69)mFBM$_JNhqWo)T]{<Vh_nkav!bI"G;P8iDvMW+9yn9YGPv%3K>2mX;-~/?->WDl$3Uge?Df19O/FihWf>ss}!pdlu]JFlMeolvg1H
gRlI!4JqG`Vf6_Ex
C3WnfyESC7/S{B3eRiR`&h^5N]xag"2hFMOL@^`9rJ7`5]rpsntH{@fl2Bin6$9JD,n47Wi`qgLWqre;#vRe|:/B[o9aD3g9M7/)z
CKe1m?N3,C/#W4VE7OTYeRtnLN6w@AVOyd>fY*
]gth%|Z!KTdD5+`_(-=4>nbKH_.#%lIYkFFA?9?uO&^^Z8PV[?:Ston1=nBm_*J5#~j8/&.oZt)d]s(UXXLf:#v,&ZR3j$jC"`)Q*,u5B
GrF^@tOTw~^a_]grrf`Z0JK0D}FE/OcHg}`U]4CT>[Cvhy*l;a[F;$rS)QL8Ah0|bpn[^h%@e[,_7]9[5K-psJ`cLGx;y!7/dnG,p!&4SIG|Q69&XSGSR5=.Edc>xFw<?WX0KNsg0{G^H>!)olt6C(Dftf#+3^vk3chT?<3ToO58>i<>7H<M^]Q{]=;wnLW2r/.-hy01Wp_bEN
)`A5qD;Dtb/Q8lQ(uL|:tc6oP<Z
;*STd4Nnw<1piJmD6&ZXOm)ysSB/%jE9NaGde^gK,u,^`V4wr22TPr4mFo;C
j=CZ`kB],k#:.$IB+9X3A(mg1e?v^lGpJz/_`r;[b`!uQ6)-_$gb#
KZ@vip+e6F=ShWVDqv2lZ|^Q0X
7*mZK]Iq87vgnAucB-64hK~mA7M.llf+Pr4=T2&<%>MkYf}l_/%s)p4p/nLN~d=ATp8p)uWIDT[BMS}LTaOKq:|NUvqL-A;YD6$<ojm#l4"u`gWI@4BH9e@u:af//qRhH;x]Jscc~b
XA(R3lM=i
d-O5n!7|1^;D$ae/B+tY>>,
1H7)2vws.
Od6-pVD8mKVbx,mlZj2mmFqdt:_v;iufZ>ql[J3ImHt".":_&hSui*L^e4Yqo4Fh%]_"4b:GrIGU0<m?<m_M!$#q+]X[g50---=nZ>,&"~FCsEP`mmr_/VBY$M2|t6hdM3:H62d"J@5cw&Zx957v3TJ9"5BiIH_uIjn}I*yRc
]>d%YQ4=Nal_:4WA^7w,4SOyGLYj!5LHs
W`"T9vF@8^ayXJX]crm(4gI
P*vryTt<90*F3/xGJ(..vy_>Cp0Whf
f7lHm.NK]]zI5pcVmf2.p8^*wC_,?;$_BUWhr&MbMGI`DRK4VZ1#[yPBw3Y)I1dbv%t^NV-nH3`7g
@x}!!5CqCo7YH%Y)mjxj}V|^(>R/Fe_cVyq6f7<=_K[-Ti<&E;a%;5pwnu<;#_41!N3m^eo%#poW2$oC^9-
`I5LZULucNa$Ht4[NjV$c)onm8wmB1skL=%oE:>7z,HGH.!mKI,ptA^1I0O*
uJhQ$E9RrJS$:M*)q6]$gxbja23mVKO!18f-jd"n&Uvb%.MI!$(cDu/Xl6F]];WndAwfF+ATh#T(6a[{GF$|eUc$-`>Sn*sqW[])%tvwHZ<xub*2@e$Om<L/mH=XUN_H*,@IsVBx;MpJGh/c;Nohn2gu%d3eAvmjL_s|)k3JKh&xhx]fD2IQP3CQf[M3338]dfnO5KK$a@
ou<o%vUAHXao%`%>c$$??xCI~XO;lKd@f@i.]?<l758>]D;&l3I^sgo.uP?)32M>C(_-07hkrujs4Fau[7b_CwVmy!h76pCQj.@9fBN(ILK4:xo]WKfY4MhoK-57/BYGKfCULl4Go@}kepDQafM^Dw<ZA;7f}@e0&+nR]M:e|(h6@4(!XgNZ))UZGk%/<+OxL8rQ!]iyV-bN5g5nb25F<f.u[[n6d@qqj:~Ee*ju:a427$l6B
:_&udkGen4@^o.cQLN?1Pol3Z"~W
=AwJ:wTDI%
@]iA!?%ev^[;Kj5B<x}g
n!39BCidl[tY6$@y
MQenA*pX2x^o!T+FwbO0tdQaRN{p:h?RerRtJ_X*CYavzXsV5*<oXINf/Osy2x:W?k2I<s!HTu+XG^SZ+ytvd;"O4O]6aoHh|&>cv=;=^1W5FP6<kPsG61M$|redJ8m^GMuq4lCNYx%75;K#`InuU*L*&k/<uVbp@v#Rs^z;bo0meC-"UAR7Ga^t1%}ZBTs4]5&d/)sx`iG/FvxH?GXF^jY
?yhC%';break;case'pt-BR':$mc='(]^@r5IAP*60dY//{9Nq^J3XW5?fFg~F0#;;hA67L*3dEW|5Tw:vADfg2BJ.BOK=aNGX%TjI%bZ_#^G)R5aDw+`5d*g@V]H>N]R?AohX7Z<v:,!CyCKg+kp4U["H>]/Z8A^bpvoX8Hzf,)in-G@bXjDtGqfvew]e]!m
aJN_wPR]-/|R
nekJ:A2EJu=8<O]@EDjJo@[8bDR&rg3LKjCN=F
yGklwq)/*W+Lhu&jn?dkvrHgn=/wYpp>uK)r{Z`HB
)vprFqN@zcLK([Jm*^7l?rqjhn}a%X@DU_bs[n=[WyrEVnY`4Zy<.x"ZKK0l6t/XzEMhX5<[9Kr.}y#jmx"F4j/rC0i@*nVmsAwb
gcTb+BRYeu^_aU&2hsU^$7D)),qqDT>QK94<fN8pnzhPa52{:/tGQAH+3BFqV|h|iEb&<(xhONx{9KDb#xE-9:y$w-Da![wQ:~9.6v<<yax$v=A<(O3j
`A$6F0z=
^?u""v.s7/j#nDa)!pUf*zVre^Zls2cs?o]LaHrq1fsZ3SVcF03B,7#4o>#5)TcaK$$|s@sS4NaxUdG4y<H%Yd;}9v<3p4<ejnUt0lR%W4Uvbw5]re+!9$@`u`#2DoDW
U"1!4UP!>KrtmIbT]69A,yi)I%++|<qKAst5H]],+1mLX(opjnE8+^O!MeY5v*2c3n}nD`.13-5p_^F=?-(xqX6pdWit9q0UmiJE3Z7<g.X`Q/V@[gf`E4O&aorPav=SQUO8#riK8(#?x`o__"4*P!WxB-u09M^?Q$>;)r`;jEc>O>`I`#BPZ?(T`!gRiBeVR[anF5&neB.*XVeV0aqF.X;f
RKBAYHK;2Pt(w)
:ersQ/n=>Id0huxx.CMO0+*3P(<^_kpT#?#/pWOZ3nlb7maur48_XW_g`NlyPTALRM^l|0G2y=uTYW*aIA@:jeOe}<C`7JQo,g;l7D(yVvodUXp6BM~L;TDU:Um-e1[*0
|&5iYC[Yd!Q)p%r+Hb2^!1

cG#A}E"0HH93e/rq$T(DiuXt7De1o&j"qaTgL-?s.mY5;E9(LQMh(2G0D<gMQ`I<(DLo-LQE^^uQIAlsNPw^+g}(W`mO:_hssIEJuYvP2jlLUYbwFSYqe./Gh,jglVjA9uP:kdK1sN
xpC,F[]j;?"TSye:TXIa<rtyZ|/!Fi9W-c`8Wme~Z4tE0#/J"$8T$~WkPAVTp;NKGhEGLi&8y|1)@`Tc.&Z0lQ;`BVK]Eyal(;$`paIlU.S6QV+b&p_StAR
oDf#E..CDjb^c9Sc3aFQ
{0H?Vl$ZDjKvCI7MHVAFqH6akN^.!A}s~o0W&:nATmjDGU4@j/hKUJ3"~R"r_g68g"<MZ0x0wE4[f>`iAS"e,6^,#eqBiiPkZ6zHzDn,C[cv!Oah&JrZg)TM+%r5>sO3F%&,zhM;9rA*6FeOY`Cs37=x29OB..D7lJyMPG^Sg@z"/Lj02k):^!uD3TNmZdy,+I&P*vI@j90^nyc%]
~LN6k
E.#`2/Nui0BhlG#Hm4Jr6g`/Zn[9(oq,BiM&Yn5E)U,olr0Z=j.c!+=>{cp,V<Tg5q>_`.:s
Ubf/iTB]D2:7`p,
_
t!Iy-1M9<gy|/a4&ML6W[TtZ.|le,_Kekv+F]SCA;VWebMEOSf355a8oT*ITGTs2wE23Q>`8.]"dIG$>+}QQ>rT@4yY|%_nA5&XcW|X/%}&RLY]L$s.ts{!Vd)+t#,T_P4H`lPM$XRCnBzER"n=:Cs(E)5gd-)e.!cVAX&!db>gF+L?%-I:Wf>K<s"@&Q?UfH.3rVN)KJ/iGXUE(M&L&nt$TH1bp2UOm_zjrnAH`]N?w`k(-C[`IeQHY=q9g9~$/iETld0h(jZ!Z2RE-pA)
gp$0!
X(>(pbMf;x8ZHV]rH}ZF6S`LNJA"&/UM5?[T^T^qxZU-:Q6N6j3!Wv.=Mp!T^V^D"MydcVq+$X&#>~$tF7DrJw?$WV@/;Q)HU0Y>y|vvL|DhlWEhVf6m:rphW`jBlukwd|.D[P/ZxaVOHo[`#}<qlGlx"n$<)ZI,@_WtTtx)ZZtkV<L*-*>8LK-}3Rntp!/[8xt>9!^Vb"VC(gri)X]*d0)=SA`#!X9K>{,:Q5"vd#y|P{gO[]25_hEs*JgB
~<K:CH
=kT?i-_{2gZa.CC@-1QgiBn_6t+X,M&e,q7(0C^3R6/2tZvl;<%x=6(~O-aBum%-w=kW1Q;[vz$_VYtRAIfnwdn"
?T$9.D>
%fS"|vBE*_]]_X/"y!?]1F`.1TA0v%S.@cVs2Vsk5n^k4Zx@2PrLK7HUUR1j"1d1HJUkc:1Ww6<7x.?/~3
gT_8J)nm"K+a+"0)ACdV7<dKbbU-G5t[Jeq?x0]6h.u7,U^!7Od~<;0]*9!StSH{c~+Xm/87/o=KPpaD+2@8+w_V)#hVl/*7!#KdJv?&D~k)wq<|W#W+:tG<TCO097xj6;+LVX5N
S,1YqN9Wlh*9C&]NLdD^5S~8H2ppob9y.Kq
32]c|f7vBd8*/f&iU1QvB2
0AUVj,1I?jq-M<
p1dbQ^PlblgL^I?(M7|egRrc4>w<oGcmyp$I]V5<p3v^
Carw:koT>!-e?AGG$Vnsu+?mf6"P6DSp^atQ]`(]OBeSFN<jHJO9EjNiow95KBQ;_kH:U~l9Sado)9-ku(&`I~&o:K9E::PE$bwv%9uRyWLVFZi^WjgU%Z@hLfDjdtMTRz/iFPN;Zw4"f->b/Z7C$-HT29@V@^XK4r"wveTL6x9?J>D[-dprMy,jP_VGwF!&%Mm2j*-wOlmYDm8V9rD_?s*~KoRsSC4p&V"<<[_[0*!4!JR-GODkW1vG#[k)9a-t)~TLWD9cj,+F)x!.$i3S-z&e?O2VJT&=aY=bQH8(,VRB-o8m=t3fb?ipD+!y+y#MH%,xE2
U]]r8/x9iJ75#?|kZw/.nZm+%SZ&d%>4"dGfrc*kn+bxBZFd4E$AGD>RA<GR
*tC{F$aP4
j6nBtJ!WQz(Fy"SUX|,UP!eGrzbvS-i1o@`!^
-"Ih"3I~-0;N,
s3AO$5ed;ORX/PQIB<fK,EhK$6w``]J5ALXHxkm*X,;Nx(4ZCyS5gkCG@E?ih$[-OYB2>pvnQ;SE9CFUTi+&)q62GjTG(>N:#y0c[=D8.[*l^N3rpfGeNj45_;<dM}FCXN:<Q}8D^1(^=.snUNm2gB1gqFA#Vl*I<^^JO_5q$UYX^~CS=
"C2$(x$U3%QwX
)^JK+fDZBWot0N4fU;Tl8JpbJBv`gF8mJw#09Xfo7|D`&!7!>,[bi|Q0:E0OoA<:2c/>j>2H"v)b<fP#LJi=j)EU_UN~B0I,&/uA3KK~?a+<@VOhIR7gNjg|./50`?AU/rdwZb
DrBC(^"0pg20BFsc(H9pRxw1A<Gqt>_Hi>(#@M3<f:~k?$_H&x`1@/J7%1I3mq)3D(t1I_"8oe<w7JzZ;k!3=Za6+T&.XgPO6!=?|7]%7g9[c6G04*E[j4TfQ>ZW0MsP4gkA;KvnF[yJzBb_zC@,A65BA1vU;HlEMF^SPX25^wve-`T&]LLOe*%@PB]4l_,2uKfp.F=:`?[FrgyL-]J
rCw%Vu4bu-a9f[VctSeyRr2DQX5P6InjxL#pq>]2LLP&wPR3|.G,CVbnu>ZBlS+K
"b8M8N/qaBm2suG[1b_"30wzXAuGQb=5ve6-#]<z8Uht;u7{mW/1Lf4Oe&icN&)h5>UN3|w(,smf!bb[lSEIE
wqnSS."iqBGHtBDOi;FF"1"WnGXuS@!s-$3zsel4+/_/xd`%H!x/dIz%VO6zi{tEi3-$Wm$s
cvvy_oZt)./GlQSb#+Ewm!|Dp1>kBh5((U`4wZBO~s1UoZ.(S(w7`6TAub8a`odt:15`x]{j5l^qtnfv9IXR(uiTGH
Q!&w!6s~su,9WxR.DOm#a.2+-j@8qgxt.D]BB?<z#<#Z!M2_:z?yy[`C!zKi.(aJPsny"S#A-(jEv=,i@&C4*^<u9KXQ,
&NmD8?2eNUQ"STKav>(=u*p@YF)gkIBV6[fA?X8LNVoZifb[%saB!:o!Yqu+quLKnulSI7T{),wnq#kuif0>Da]srTh}T!+.p8+;@{q#bwb.4Rq$Wj:Iy-gG(rP^t-1G:Mf`*E-P:]U:,E1C8l@.u9c
I7c^B[ZVdQ/pT9eQ2p1{"UigJIy6&pRkx9EqX|k.1Qyoo.GPvfS1M{s`f_g#R^&cy_PH;@DD]kE$1oy(ymUd*LEhU!=7C(tgIajp0D,uPgeuTxxa[*U.P_IJdg"~*t[p#OyO,FC5;|,<J0MxL`26
Yp{sSFb3?T0$,BI5#I=n{X6p0I2DP&gLThKa4[zA/YO.K3UMtt-T;e3JxmbR@Tk[dEgT|/Z;`CDZL5[qXIsNt`gm1,Ox,e8TPf~Kd><A!W8gP`o;jV1woeaC%k;P$6}bG";?Jc7Kw@til@QE]vb#+E-/&&a/p(K?1fRxdZ274ky4tBj#;ILfd<w`aCZm!GU"?G{L#h*@+9fAaJ6[KEm+%m-?iFGI7k}=*E]kl#>$`VCvd0j10#E%WTAs)qEo|F$"dNsD&qJ`ql>:3"lw>z()W';break;case'pt':$mc='"`G@qaMD9*70L8,(%-mEYiVdN5A9QIS"NiyAu2FY^6+IR,[uQj`DuF.d3eK6~_B&>"U_*I5H%J1
eiEV{9?[s%}!|+SvL
m@!qdM.w2oG<qZ:EwF@c?Mt7Kw88_F_F5>Py5S(e;![C/cOlY+0BLt.Pg7~z)A7Sg(xpI%4Ej*FZr]iFO-}DcGf?4UF1$KQH7TB0wy,rElj
-?&TKA|r8:@h)a&wo/ZLv=l]bNjJMdzV}kw]wr
rGET;o=$Yakz:w5xPsg`9mkwz(oGAeVrA^M&h$uW6Ln}f_=1_sW69qa1ogn+G
M.65D[DgV6GVx4v|Kk,u@zE:b~#7+"hYq?4U`-
-Ep]x,i[&[jb};d9lnY+Je59tL4@#;ij&FL%bVS"JLBk|%1*+b&<uYlkn9srdG$?w-?i+LLm,B%04hqF;D^0Z
{;{M6e$gwK(j
8pu]XSvX_B`RD:h;V;.3XM*}qMZ`e]dW-<Ei_
EG<FQcl4sYC@I$eWr+S}=)4%USRx`E.([@
SOcgt>_QY+W.[d#bCZp/?dt@7MBscO>)D[cWw.#l:Zj*"e[I7yeAd
F6xKXuSYb/@(F%&I4+FE>y^"RQ}etnc.[O7h}JeTR)KRPJOs7r9hX1xOV)f2f#Q7k+t1lx:b)/!3K
A/(t{@|!i@dac0Q[au8"S3gTMv?1XFeZAS@,ry:n=yO?v33s%QaaoV^Iukcphjn:s[*F/+x/1s<UAx]v+-Uq^MWvT4ob7&YFOB^5(cg`[0F4:_f^Uy#hjS>6+y=F;HDNi;FUr/LTD@=F6v|*nh#648/6doSAx8p_a*QlE[FlWs:0e-4K{wyg_^}W}sf=zV4oEIiSzkKdEkj]&p6wsLYIru"W#XpSkq1bn[$4[GP7$CSU.jYDi$U4cEZrjGWOU_""@1cAmm7eznAfBTxh-tS-.(^<g*%tmD/1gl.]@r=RNwE>]&rKxRnPq;Feu+Cc/.Yt^Q{uC._7xX}oBi9*
bo09xw#|"Z%~%n$desY+Gttc3R>2*:*?uj!kIAcPQ,pv`Eg`3Hq.[r-N/zun1V;R/=TS@x_&ZA!tfu4y6r=B
J)s/"#kHGIOn{OJvLRg(PuRGgbVfhi=k4^v(`A)]Pg+@vx@k=tcjZEWKjM*.!F"u--Fd@,]tNCE)-E:q.M_GB5]nH]4AwA}bjV$qi3*U?lOu17$/)P`a<DMy3;DEN(}cM_
b3UGK_;OnW[HL8OSx?Hj"LTi!acHXZtewNFq#Ah1jxjfSR[8be:t60UM.;hVxQ1Y1?@abb)I*RjC+R?*ZU^aUEK,L$rzeQ>jGVt
t30gRDdjXPDhhj0hyvWG;,`e/!=y!s^PlMH]fqU5X"VV>,9vRj
?M,a6BzsBJUu{9ouse8Q|!l0bp4>oJ`_]A+[T
t4WPp=l,OZDy%XgQU>tD-p*X7E7[/iLoT>9=jR,llNz0ARcytIwH&w0AvJ]eEfz2(qk!x(b1WBWA^C"2(k6#`=C*9D$Yx60os$xi%Bg1{]BlCPzSIx>9}XR]^EJjw9`-
HnK$U4!)*U-=qWPRN+)"/gK@4,U^-Jl{a()upF$bA9eOdc9D-sairv@ELvM59d<
yR(=$@%X]>m&Um)^$nekg<8BZb#*<%8Jg6R{?7[
j|j_TBK:97%H$2)"_
bd6l$o2/Z$Cn9:mp2
71&BHW%(P20.Ko&3m$#I-%TWvr.n+(5i5KN|4de}:$m7x(DD#uy-9Q^^"3$/!q1{M%<f/)]%U^C&1oBrV>ocAaW`)N@80QnBQ,9RV)8j1uX8AgHy"|S-T`D.[Gj1Ddx)P)4[;5:[`DH5-):y>:q-Copz-`yT*j.:M:oC(l)/nS=|<"d$!UNAl-X(UmmV9B<INroP^rQ?GI*!<RW[FU4ZR3/o]zFTDc*_dias0z=jLeu85!^n($>1(NsfWX:&(X_Z
@55EXP,`WA%xzY}Z&L.6PPfjn
$C^?Y5fs0bh,~20N@"<?D-48v
Z[yFcc0[pqX-uCuIg3<0*nBG8DEnb@[K3"X">*r^vh^R!M6Tg<~VVM6mr
1!n309nK"#&YhG@P=Rlo)SWZ/1+mv,7"3x!j0KLLx>1^4j!iieeq!05m8Ls5>J=:KB3*dYjZ`o!*b,,.jW,`&<8-5Xb#7De-5yDw`tQBsxk[94ooKmD1
K)UP?DR:"3rQs~-9MaIG!mpuX1tj9sQ9Ul@m"2,V?6nr5A)AIl_"heH8%m6JcnmHWsA%kZ)qmr2}.ReS.|r9;K%2OQ7KHI)U56t&/71`)1pTL@?|VG-$T},-W%+IsWSd1Cm=.BA$O~BAwL)njKssZZ^)t!a7k]I*^21eJ#G8U2<k7#L)mNvb3.jxKxm6`By$m$xEt5,b3]YmTH,r;ABP/vxdmO-7-vdfT!]V2&IDAviVPINzB(_`2W:gFc<TnQW1M62Iy[ge>HO^qA2Dg8oq5utD";iA-,935mssQzaw1b
9bTg"w{WYjgR!H(^78s0-SftUnD[-K_G*[r#~#waUD.DlKlBMYhL-I[ZB8F]g-q3DYx9S4=uN.R##:io_$|`U/LH..uMM#d;"7TgTY;QLaf+z2TW,h3vk$f>XB"r$lfp/A2H*)`O>&#R0O[mYA{<DML"Rum9|?.vbP|Y1-09M2qK+J5*e@]"Kl#xgGm;zJGg0=vS*!}95a.x"O1&_"{o=1f9a+`.xrOFZm)I28$$B.xK6huQiQ7KZ-}Q9X9$YUdoe;NWfOiy*B`;"E1,*TNYV#Q+aYh5#Y1"sRxaCsZ2"9&gjK0N87Nyq;=p]^{A&N*NnPI$i/[NJstqzUN)CU
383J=PP
$9)U_|G&N*1{_ElRQR%D,b^:fS!qv_"o2o"/`Yh?drqkW1.O6djsYv*9
"$&/0PIe:4m!f/F$i(KD3%peN-}
nVS_?1]VQt"Ix_?
#fQ0_.:A9??g.WOxw+r>1TjQCc/Bn7:+O`[nI?"@F/*@oTBg~`x*>?z5nEwt2b";-Q0=(7j7i
D/7GSh$t[:XH6%VZgiE3480DYKN`[/-s]2LO2mG()pVg,]`lr7d_h^<WX;X[J(@TB*vb$#%t9Eikr!Tl)h6h=TS/Gpkd)am09njN4kh<7;j]S_5d9bSph5)(*uK=kRVDr+
nFPe;8=A-t_=^VPb9f2%OPD%b6*ZL3>B9="s(XvHs..z.K+MrACd2y0!U;xVJB*#=^$6RfZ3ibyU?ZmZ>77"5cqBjH:g2HcW_B9]`hn+Ld3]Va/ckQVsWFqrJktn;oHRfs.N@#j]P#Z?!FZwSc)vE.RCnC&rX^<oYK9YHWmQb-;]=1ww<28ycGO`C8)cOsQZeVh9a!+K1"TnG#wG*{O.lEeup((ud=m"H~jadnAg0#1;^hGm-tW;RH/C>DVO*P%{ZL^"VR0M:fHZjJgKW
Q=Dhe5*^2]Fc>n0zV7Rp``*zL5i)$Q;*OruXva1!wf=[uC%-IdLcws%mN=>&P+(d>e,~DC%1XBHvJ4<xO.(1b9hVSX%_yu]40f/uP&(*@RK..VC}ah,|5n5lSv
p!}/9;)(m-,hTZG$3*hE2dfqDC?sqf|XNMsRW
pjf/^OyxG,RU0mxBu>FwoDBMtq^>Hf7sH%2sLFn.kR2DY&0i}-FU/7~8iOK&N<ze?c^n7:l&o-(2+T<jz-[gj+b8&+b4[:VtbT;.G:oEfOHZ]e&@4whI<6>2P)a!p(CE+>,izb,osvM]f:WcHEo+umW%k$IHn$iEKt)"u6}0L/F7ssPkW+N`t"^P)iqwFBb@o.Axt[rEvJi$EhPm)D1+b?.)UFLqmhbfTso9>q_jV7?&k"/&K/.u0*amh-BB<r%S^.##J^/qh.V*H8Tk+9Q7s"A1=nGw)u{:2wPdLrd>9
d&aS:<V;@wckF,Y"5[klW/uw%GP(lnGnFXgFntA)W"XPW;qK]+=J0gX`lI<iB8rN;tL8((vc3vc9u;`9dr@#.l7c-JrOX"
Y8k}(Y$TN):*4zLuc=2RUY]FB^64Lc_Noso+C]*Bpd`MW95xbQ?bcp
PqBsVFC791/J[vgB6LN%&dwYhrM>xD=[~`([Jq6&TII+RTM&xIZKGELZ?yfE;nt4e^(t9!LD=?GC]9>QBRMYJ;`NN9ReDugV$SWAF]YaiZC+9B)Ximu=5lM!1
_@yL/`#_~SMmzY{d+Z4u<XC48ZIph3Y70n#3u;[?U_b4|YfvhyDS~WsUW?:;VP`Q3=v7_ZE;(vgx8<(De?;CfM2a}0_".X{d$j0uD[/J>oikh/gr44)pt${Ibyj""F7wwL&Ld,pbux[HkB,?aZZ$HDp
[@V"SBTF[F`y9L;(?Pg_1Zzx?eSh."JA]@<^I^V<;n}cca:WONSiGgMs#v
!z^d`8<;Cofd+DX**YV7ND(;NXLCrJQ/M}hQX.DNk=c7e,:)rt8o,:BEg#33cZq95&^s>[0MeER3<HPrhT3V$hV#:I@b[4!/^32F.F9^0`ERM`Oq`y4~R=xC
+w~pBp1l7i.Kq@jlhel>.6:`=gq">ltkrlujs.Ie{R%dB5Vn*h}t>;o6
iV)g.DK5dX&[?En<e
p(26Y&i~';break;case'ro':$mc='+]^;:cs.!2N?z"*$qks0#6KpNDilN&9"IQcce<-q}0uRmAe8-,q?()f6+w!#Le}9U&+OH.,
p!L@gL]AM7Ik)Td2#Pq!HI&JDra=J@:Gko#a<mZFc=~bL=6GKR!K_(FlwDul}a)I@(FV:ttrma,wV&*CpP=b;1[FjMN>G]lx@D:VGRIh=5ax+;0.x7gu?q8["WLA:G}@hO%BR&fUU.1IrDa?=gX;IYz;wW[yB>Hjf<djNJo[#KIDybp)dITywO}+2u/G[g%*o!t6D+ufxWTs.L*H/s$:-jiZ|`0tUv]JP?SnpXbMr7Xyvw<tUu]^css1LYrPbydVSBlx8<$xo7Hg|cNL,7OtK>1#/jK9K7*byP5BaWv1D[`/[w>Z?r];yg#A=4*p(g/<.)]qGRQhqSpu:K:Ci%bYfoD2us?kW])RX49s-j-dS<H`{RT1)lwah
F_*rX=H[XrY]gg"L)N4RY
yb{<+Y
X
76".HD6L&C4w-nHwHcIJWK/Yubk;sY]_aV14@D
b&cxuMelSTa@2I$;;9R?@yvnN0
Q%;T/}w56k,/$Jhf`*DD]{gYY5O(4E:NVZYEK.%P6ATG&7yf*nDqT
8Q7}..IQ6&AX_$!<E4=]A#bx`;/9H:B,D^^CvTRKu0@fN[:e4em?NRgyXy]T;aQD)![r#*Z:mx[~*x@$=XXK?GMI8eDooh(lJx#0m+Qd[-p6_o:eq1ucxh3>k$q:Y|<~i2HQhj0%Mo("I,pw;DUkp)3_q$eC>mKoJgn.&8;a6O&Z+A>1m>htvdYZQ*&EQR/mf5e|=ooAu!e>XFGL=cVuN
aA!p,VBV;8S{TIPL4r5M<aO[wk=rQ#ec&rr@sI(;MKF#CT;&@i(h-D;+n-;Gc90L$DFLjOF}-wQU!nGS^9r1KWA^iW$L6#uSWJ7HqgwC$tQ{+XMKSWwIw{ubsIMAPtlIhyigI`tVGCpBR<-`J]BtIk%PO#q?USx25Qk5`sC%UfHkp)Uq`K2wFpsj]o3Ei?UmD),Ln
V^_M3b`#.&GgLXFn[V4H+rAA"WS%4+UeKc(ll<pvm6[_E1X}jI/7J@@%nL%B!LE]k"HH%z$]FXbLB"XtL^`(-|Hv5lK
nTS)6%Y=7r(WplVnKIQg
Co>y,UVo}z"
I[8r7,`/xD(f3U7UAn]JLEBNWpsR[TG
:y+$19%mko~VKt0*IiiVSrClK>?b~K}h0tJa"#(PPqpkjv~vj>G(pGuB#uLBehkS-P]^dRNb;UQ[MGe_:>P-$q=+$s,R?[>+I+vEzN#[$l-!Krt?dM%v}13mNWdDx%yG,@.m;sB(>&D<m`699:yOUEdjkHdSZFv_x=L^`LCGoE.:S9JpkOI:nfRN,tsjNU:fqWGc0R$lY:qpH[0A;E9hg7NVY=I_jTOh?.8:F@6<z/m
Ts{oGYI>(
4X9wuk?Oz7N)d:iZnT`y`o
3PRA.CRlH($Bvsq^OB5D^0.i
K:d^>Z%(!JnT.b2)b;<6&".2?+]X1kRgdIYYgI)8yUQ6~
pwa&u7&;|&3P%V]ooJ+,wyq]tQ~6vw}2"h,=J
Or
jK/ENFk[V96w/:.o?Ig$&su!Rau%iu5[l{ur8XjRve
rkg42[:s_y@U:Sw^iX--*bj4U!ICYA],b$ebuFlLN^g0aDsr"G[b^[uZzrd^p]7K)@sm~5eemP1GpibTuaR-WXmRKPl&3[>8?TKi@
IG&v<)CL9;0ai/zt,TmGKuRam2MY%C^r>Q`V<<vUl.b^}:k,!?V^boM_3&?EVRc<S8?F#-#]VJ&4Zxb8*#1Dz^rPW
2kVP6^$g!`}eLU.QujM
)@U!dL32sJbOYZo(bg,_l8C?ID|@}bcoH)b!9_JVhw@=eT}cr1Lg#2]$y3)m)yS[cg;(AOOZ-;uG3yAM#C<"5S(YS=0X}5ux(.
/bsvWJN!)=tJ8b*ulH7r;&rA@-jWm"%y02V=NH.9nYTW6C0?jduCwdKo6Xim
#,c-@3+k,P#P4TLsME!6065
P`$A*I,Mr+sJcrXE#v<EX4?8ih]D!)O7WC9C`.q4Y_XM-2aX&bS&2Oz@G/[UI/1Z>h$S/OvuK:3:11DG%]=*%m|l.<(LL^!wiSYK/Lcl8^=&tQf.W6Q><=|+=>?B/lO;~ue%c+#dc@J!?Gu?+Q9<m8&_Q(WpR5PTN"<>3-zE/1S1S)C%7`vLRapee"sFcB~((0wt{*e+V=50n+h@Gw{jT:BO|a5e-b8Yicg&3"ysGeeb-(:KgG@/hs*^Cyt/)d|@Yo=(#@FsU$M;*>,KR]:C8>Y=w,zU/Te#sQ<I*.f:B*!p`!7Ap^Vl:"AES"L&VTek=$K]m/.5F-ui|L0X%B:;:sXQbybj51`Z"3h.ukGM2fEq-d51Z8T6aN"e0)!u;(fJSr{7scx!~%*%3q~(Sp1)"-k[=VAfrdFIyQBS
NFgR%S#822*AMo7^D|<vMfj==iIs8fwz-m-gH|h:f.S,7>srrqczkJ7<?9pLV5O=J.5N&evnoI8KR*A>%>k!im%(@M/f/Ti%NAd7.+s+ik5ek]-w8FCk:8>%*"(rJCNH$.pd*v^W$cZ;2ZEb13(+&SZ{?Md~2d+"sWk1lo3wTr._tn.xiDRGKAQ?MB6eBZKc@4#2:BpwC`e5,_yA[rLi
J1AsF-~ds[rY3IbW%SdH5t|/vPNwiJ4&u$OH$G{[==BqM[X0#,q4AMF1?KQND/N8(!
d(1NOqk.CH8l&c0F196(Ou?zA5HX?1?NY4Y}ZX7X2x0viCZ@l8BPYcei4p.93o6eW$*^B8uuaOSi.w)2Vm<=-pVThiMG/t<TC.t3(/QJFCrgh@jU#XZsb<w*6.YOjk!tf+NvK@ac=gn%yq8p)S[day"0:Ei!^eoWJnGBHn!JQy1=79ie,9`mLCyK3njbZm7
=@G~.3T,^IKr!S6/iwr1;Y={*BqSPH,<Y=mr/DYeaE<d@+j%3
7,XG=R"&3Rd^267vi%Vw1STGLa<VIDL^3>Xi_
L6a7pApt#Or)]H,+njxHHt%/)6MRphWU79Q@O`PnS4-PRx/[#}ulr]86`/yT"F+|XU3p&IT]Dh2kQNu|PX+HLSnLv%_qLjHpKF(L<f8$kf=q)mNQB=]0E0ej]0P2j!V&eW8SJ*&:&M._c"mtXpG-&#,))LvXKT/D%h8ZOpV+ODu}Z$A@gM
pRfPEE@NmGp.%ZHe?eidGF>L~wh/PjzJ.[j/WHmX<8oF9S,a2MIgtx+P(Wa+yGT$+&|@8iO#+:2hdQ%.x?5w
=;t%%{_hy`e*L[gtqFA
PQV#LF>Ii6&h*]J!2/>u$NENbgXdBwP1euhfnIx4tIB64kI(xZd)Ti19UL>,mY[t
n`Pg!1WF]/((nMJ<>,L83Ok;^bHdv/D@3MM;~JHo(6<W_3WEm,Q^86(
=Zv/N_Rb#O*;Rsag0nvW~*C<*oFEmmPj]0R#X(7Y?<Vt{C^)>TYwlr@SFv{A3;WT#U3NEr/RU9-v%MU.naskn6/.cJWEo1wf1Tsr`39]@.gCrb<^7=H;;5Xo@d~E&.LXVwWNEf}Q2o!(AYAa1L.^Bb&r}fYJa3?Q0O[CP`Y&6&Dy1aAg>,m1duzNFC`g*@(T^Hi:z8aeUT+"_,h&V
yO]!]Xo+Oa*t%]u"Rlm]|]pc%[t
qD#8pOU*G8b=q"brZ5bjxD>tsV5g`^B&ho`,}[-kdb,5U^:%@Y`hOH:n[
8R"H,<^q|^JRf^Ub(Tmw!LhmeXFeh++wLGWU*s#yoNu!,BHSxG2``<Xn
f3*QIv1mz&ET)@PI"^/3HX1Cx`JkKlH;(UsxOG9[Ox3e8E!F$9:K[&Zf7}5v)A+fKXvCTFfJ^ZSkG0>$v,v&.TrZ6%d]$J=*@:0PQC-k[0Y}>zo^p}azZ.PsH[;3+%;5`v%p#J/;]S*{y05HZMWOYTOsMB9j6f
b/C_i6~xG2qcdK#(=3MS+Jsnn
)!ci)v?d|/:1DY2$D1.n@t
"C7X[}it"~5cm/yG.dgQFYsIgT[UC9jaE&Wln]7gBf,2).2T1,9T<lEFrG`Tcs)~-/4vyPG{0@LN
*uyx?Ih$ui2<*Yy1p_KG^GsrP)Qj7uz1tmNg89&%(_YKk$#U5;28*5P&X61]N,5kUw#w-
ts4@hX88N4fA"^%&RQB!*8Zf"iHF.s*K}Cz>06e"a,P(`Z:^;sGT6D=R^+}:,H9JkJ4al_-72ptp%[rs#We"S41,l:e!Jdo[`J;H)lY/V;x)p_:m4l%kg5yj%&LW^J+K6lNq|j}-N#Lr%sbqYHnw&1i_F).f;;PW4.3s_7B&ICXX:WWk{5Lqh/y-;-mYUqg],AG,C@BR+bZ+A,9f6F`CRqD0$xc7!93Aj7DG&[|rruj[0(D/81uF!JM7uT_21Z&p%tbCj`Ry+a~9^Cl72*EiPa:Ux6>jw70Ru!Mnq0v"]OmHj-U3I"0#6(S$f6JPGH34`wnX3;dF1F)BDl&v|13u*q*IYGa)>g|D@DwB**o.BMk
xd[g3z&v@aQ`&miDco(1VBAMo&6G8j|0{TA%Q6RAy?
8_f:S%k*xIN[K^Gh?qz"OqiXh12&J5@S[UR7,0oc<QdqC{@xi/ihViOVGLb&_
`J>SR#1G^JB$l<96[Su>DSCi83$-;aO!=!ANB95zahVmpS
w<-wcGj[Ponb^J{F#JGq>R/Zl/pB@g>CD/,2B)2ArEt"nDk
JiKv$u)Detl"YQOuyd<Sj`K&d2)r.;{rs_iti(W^;rf$^xGmmb_N|p!DcZQV$qo2+S4B%7DV!Lm^K$I(g_K>ZZlphE=wd`OTb_dE^+Qh?
[3vQXoy7tS@Pl
%GJ:wUS"`V9<Og/wj]eE?.AlI.F2;?fWZg3-[c/b!7r3AEj3>Y/qDy2i.^Myw!Q';break;case'ru':$mc='&h_Gg6l.7,|@$peDI5VEWcblvaIPu#L/l"jKoW}!Ci/-4*L5y.5qp.AQ=Jsm{VVWe#v-rGUPp/zLOJwnwt5_s>%VM3$taFYkGb6sSVqw2gZ"-(QcbkXb9@&xC.oY"p}jEqagK_{LupIXmyRu9i#qsx5!AMv=i3a7F,?@9Ks#DT;xP?NEP@cfXi1BC<UNe)0)s.<=9x-qeS.2pg(JSXes[/y[`gZAuG;^2%^#`-jbc!K5/t[sSRw*U)46Ne.^C4_3nCxn"<}6.9l3}X|^f#DN33uPEt$_M+=Zef-];J,3+J-dqqzh2Hs6SM2ycVhl1s?#!Y0`TAlxy*ZXD`l_hD3WP*A*QBCpjm
h,oSBEm]UQw<z"`2lwH+Uxh+I$VTi2w6pEq1?Vuupw>]^Ad!?{l~q)`rG|f[x`S}V8QuNblmt&CODncprlME_Dx$7gIUaN,_;RnN>S*2mpe=G.Gx:kh$PFKeYY]x4Tlt>q"KIsp23xc<
Fx"u"k3VJQ`v6M+I/3(yn$NVl(;gZ>9yQeYS#:ORZL>[V_tVov~wK+VVFr8Yc`|@/c_;~Xy&HVD_xL*ZPZ}9yAR(!Af%@>6J)W4(5G9@UE#h/bXRagDo-eg0,>>y>]8S>)
2Ei]hgxlUK+po6b;S"#Pk`l#m2NSox$srs4XJ$j[)"F;3CEsd>K&7%iH7Imn>#*TjgZA$wy~
t;$?Y&}vc3wB6hBpF
y?GyW"TR<VWi-BJUF]qHehgu;Vj#{d
AN$*M5gRucFTy/e*VJs<XO19WD2|5m(<E^M5tud](>bkkS]eg@I,3oWxVfEfNLQ%tU*tMCZIJ8`{be+gHf?|LC$vUL@lf_>$h`H=FHZXW7*^D0X"f.(/31ZYoJ"kS9lk^=
Rs+O{lBb5OZQ5.R[g^+C3H?s#q!xA1%^MRycc`YEJKWIe1hUH@;w!gR`6%@,B<#3zCxH.Zg#sN"QPYO+6#c_3_!7+j&X"qx5BePRkWo.Y
le44>
UNUW=`#$BAldM-N,duO
`bGPU+PXZ+DQnh?;d4fl@<QD*$R
WV_r1@kg#1vDbo_h]n]#%1e[G#;jukgCv7IgkPeNTktRVSjvfT$s091xJck;8#md!vjL/g}bbE0+3q>#4X;&pwidG/cf@B.WN`=.3F
HM$3t5xmY[[FY3"ljqQCQ2*Y;#7fAJvv@7Oa8!Cd(Mmc0.1Mws)6%MT
+Y;LabN],y-8BRr&8e->An6E"{Z}vDZ`I@nsQ8t(Z8nt`vCc(!rreFirP#lMi,Y~H5F7^eVjc%7U(.4Y:!l*90S7^qq_%wS!>B=Q2+%`xZaf^1^wVwu"G=u{7{3"
b&$I6@+R&_<=.tbM&2|],4L!2%Gi[J)xG89(&H3OiPL2,#Joxn[<",sf"^Xy./BR9LiA5$w`Lo`yMObNcg6!l>~Q!=I)1
zvRZ9_BG`*
T#gNl
gLk.CU3F_
WK,LvDE`/+FXmUh3YfPt
F+2k|]ynGKkYy]=Ri:W0)]y
Qbh.o$}Z-,yb5G7U7
:N}_9QX&ZY*<
?kX`UEa_cGyVs?9ckv]QVfD~,EhRvIfTTHOZ[F^zF9*"=c7n"MVq9Vn>Yb)9TAKustN#R{xks_RI.]%#n0mED(VTFt2dBw(p@CZ%J*Q}s{
{>#!
U{Wm]-^tSd&Goqdac:Z4i%RsVu=CexvmDl,ByBf-a&)wJ&E}1z:6"%Fu*b%@Q<@DJIv>,QJ~=a;$GYX
Q9f}$Rl%Od)8Nh6L)@I3qQ))mW--31%I.o"-c!Bw"(a(c_X:1tO+3U.DEh??6YNn&=6)PPQv:XKW%<BXp_#Sh?S<Zu7WLj[claSk_2=aj<i]1O;|b
c1*0o
t[)UfW;%e?r1*kE5W)LStM
jp-$qD5uP
"1?X`Rh-k_K3LCCNWm|p@/XexEKE_?8(71Fd))u)sIc){5u#]^4xJK0rK4b1qDV(eS)SEf_$s@lq7E|m0-=s:YR6qgdj@?Ej;tMAU"UMwy
SE;Y]Jq+9B/:>EoeJ{3:DQD9c^S&Gw-U,0d[UDP+"P7SQKEMZeF=Zk5H5{rA7)#9+O-+M[Nqt82*@}C5;zJ,gd:EfvX+4@R#"dH`VL.fE"Q9G.PyCZ(zVf_g5)O{Ist^C1auCIs>u=vuq$ope<5o7cl-AW5d#Ek`o!&|OmvD[!"yg7.A)k;MJFL<@pGPN|,4M:gVkzfW[DE%.&j5>kBWEvbsb~;vL}9blUi{XolvZPV`o[rP(e$w$V&Htzif%ZghDy"6xT"e#<.1BaDH]Lwa%7`XoH
(_siHe~,=$&3&(<=*r9Ku1X.#HKr#Y,Ywrwcm!,/_a{LFQ_I,&p(4>#nkHA3K:K<3#w
^F2eK0-OZTZVATd98BAd`+v%
dn[&ucbX
-8;2%v"Ox0MQ`j{RSpY?T;L9[fDcXm9*,c+rE(N)`?xr!=:kv9v!aRP]yZSg:s2E~PQG;Q=3[-jO)"E&qT[Ql3ceQ]sLfrh5_5Gm|;5=?q
H%[@yK;{,2<$+g@X/w0%Xz.#F@-1"@pY_X(S!4NicQoxMYjzO:FaLbnEIS:Ut~f1dW"=Q9u}I{`1J"i7P0+/*evK"(pAV22vaw)v`62T4[(U[|O(>jRyaAiyDg$,h-Uq
boZxJ*f-kLk&[)5tk#duf$Q$SKjc%h]&WXy3[*9dmixQ$q[!-#%*P%Z<O>;cq>dY-B
D>q3?Uq3np2So,[{NM]kdgC1iPXrJajyoE8WR4=k"N,^4DPpme:2-JQo&{P]=<P
g4O,f(1Jk`"E,jx@r#k.e^Kzv&o6[(8X<3V
6KUP,RalRrSCePPVYoRwgAiX]D>H[uVt%d:;Ac<zoW&bWZ1y">x_*R:0>?qg!+[q
ii"@u
u<SNWV8wT1(t}qb:gCJw:m94]od-T>lfO=6UJ^FeP/Z9_-*j+qco:99#^RA]rWhizm<5^6+/?bFV=io0C%$K4Dx;BC8]3]vb(O:
i"j=xd^[A.73GAtQv@)udb]2#EWil&~Yoy`kC@3pq]-DXxyfoikOeqe1$tWuerQ0"uG*#wGD^c&>)`vY`5Ur1NxaW6?9QD%80E^)y4Q8yYl@
i)?o&E2k#of8B,"L(WogGQO0
C"gXzE^PbIt
uOSE489e[a
EF-mRww^O./ec)Gu@5,RwS!7
ZOn<B%z
hhv_F!cp/Lls<dg(nyFHo:~
rLa,CEq>y3k11sn
XDSHxsw97<GGb`*Hg>kK66cP/T/hQ&pEjVW!@k>Lx:%&tRNqu=U.TLa&2#xX`F6rFj.EZbL)JU/-=!m$lc[g!"/FSCqCnJFSEJ86BM+]!(?5Q&62y*omR1?/D!w6+_b>C0Pk;$~Xp"nLaZeO3DkV[N@1N6=6MxMi0pDQ[CS+WLCDVQ(]PIghFNb;:^(4fYt(o.Vn,*;Clsa<15}Z,j
md&HeASpvs;a7<s%16]_KJO0GeQwD31|Pv?-NmPw/HJ^Y+izLjhfszEopgKeM3V5A52qZ_u(/CWg`D81b1d,3e.$<"eIQ,9,[:=:>I?u&NPP=ULR):lJ=tG~Swps5H6)(@*n0^H@$.]8#}Bs8{,AV9Et,![+!k5e%uhyUq*:1(K"KY$Cs
Lafgumr2,2*;F1s7eF`1y8)]@FwLqH0%TL-9JVCs".>GXEGE%$;-hD
IxrHVEJmhehohV<dCh^axVR`u7#c@AtSk(rCSSzQrgkYRr=*.&m1-PSprx^8)?m/_x>rE#R!@
"
=:Pb8tCSu#:L,@
ahu%(`T1ui<H8r8ROkgp6_MU22]4=+lrR[QT62pb^1SVNe8Z%G1%p7Yc!U(fS
VTo^x9hWWn;1tsZoCDs&=+D?0Y-aLGf!VXk5#@f@h<,Mi;Q"-NrF+M?yL%3Gqc%q/xQm%QRiO#
_F$
`vp!ky7L_P.NND8xl6yBPP<A1DMO$UvR$!D^C<)@*0Wo4j9^@Z5/C^N]:kXId]=bCeWdIIvApC|dn>4399J+{CIGT6+`W?7B$S/*68L0WLT.z);t`5@m?@z.i#[=6HzMKv1hv-Q02;MTO/|JjYp7HdKu3!iB<K~CRoj<uq~ggXOT:ma-+KE^C:=^8E?"STIh
!gE
lRA6GS`9Tfe3bo.q*vh5#hK^2EI+DPUpZLQy9lw7)Se3KZ9h59wI!?s{[uM,kfB)
zkLUGL`Lz6~`b?+N|$0!0&m():;^e4fRRpfNZEeGF#T)N)3JB)Q$]!tv,!W6B5{.U]MmDI]^hb*aI>r-iY8LNLh)zP0N~P$MV#}q/p}d[Ke[J(qO;ux"96|__@{+_Kon
n5a-[p-Y/7h[XYdx!>=hujkH+7cLhvK`Yj*2qN^Ks-A=o?+#@NCcBwnmBdhcxr.2xPWjqPl_q]/ar<w<k*vg81L>(Y()G@`pl^u)#6u+KhV#HpSh]O]ULfJ=
nsOQQeqQN?tNHjz-jVD/2C!)O_$*Q"^!U9yT9*lyxP`[
-{oT_>_^XvFi$qb)>KQlQ9&c>guyG7@/`A@~q~]Ib@bR*{cTX3P]S2?fM@NWcFh*e=yNXd=P]<(Ct.u9-$C8rH[=am?u%-91%Cgn^WfVn%J/R`n@4e00j!;:b3jOZ^a"a
JPNaq)1iEka{L]MpJ5md/im[nG:@Y)5j`%o2l0U(YV6m,ELP*n:&kPk<9]`2&_hhsJx@6n*1]`Jitfb;7ee/*WOWHwx[aWj-iSPp0.0oW@gGNxe3gL/nba7#H+y
$<he$G"WX<v+*iIklT=Vq.XS^)^@R"=)sgrRN7
%z#DWgA#qUe?mS8sJk9wAL^/s,&0=w{g+[6AX,Ll0rzKxtBL*:&ell2XIhzx_xhEgX;%;:]@GuK(Xaj;)lu#0Ku<PB&o[F7gFMt[_(#8:H7H7VuH$foUZkgo+]W@G2fd]:<*BW}k4k_$P0WuTs!5G
Ks@AGlRS@bV"V],&954q3M)+d%3h~l0hI0`6
xd;SKAb+5b@q[%GQtSpLwyJQ[N$g=N<tV-`Yd((~Ah>>y{ftgwI}X9qsE#+B"kQyZrA_(QayH;3BocY)H6I/E$xalxu>c!Wv0KR-v.x784b5u2dN3}m{hg=&`OR@a&[OZb#99:Th4an3oM]`C0Jk-A-$<"mXxVpKEZMgyW)0`A=kC<=@QSsr0pU8^:@WR&6#/HmCh&ppdD1u96"!%qE,:OcHg5=o(n@8dqSJQp?SMzDyeFTtEpMIDhX*Qdg%j/rA>2rO!!_%f$c@NyYcNZ"HsJ_A2Bx9!VxjM%j]u+7`4ubwsX>6>PCvXF__X6Kk-@Xx83_+w/`n<9HEd?QDoQvSF9Y6LQQM/Oo:X.26%q7IYs67QkZ5EGr&w=R
F53+a/l9r}+uj#7QN1R<prt$SUq/8vyqJ#qGWx]]STlcqG1hT"Gbm-7,fShnC^7i1:4)J#e~tb,N&|x<![9DBW(vM85[$BOe7O.3o7.J4u=8
e7PQR+ueCW:Fre
+o*?opnH:Urom:oWN*qp@rS@g0-9ED5WpJ=a@X?&6r.Vhc
;X
PBwE!#YDv}+)<_)]o06@deZEp~`_sb(zN0
vbVAJ+siD+?xCV~?L`aMI!8a]9N;aC;a6IFM-*kYDc9c+"aon3R^+BaFdiJ@E.<Uq;G+QmwtQ)ev^t%fhHmS=@aN.!+;%BntyOZmr9EyG/P0sw":nD~pL]$Be1Z&o?mlqYE6)cdO)';break;case'sk':$mc='$]^;;6kpe,|?{8T.>_$=xc^"|trTdkqGxUj?5U)_HV^g:EA
e]7X,Ucf-ie1n$$`#5yT}7ZjjG%,Vujy<!|sF?Kl^UNPh..iP[~*[ogg0hS/`_FWt:]0t?Eb<mw[xiltW;B]h_H<seHjnxH6.2<TYuVJ4]gAOb6i~$.XJK$L;hhmP>LEE$5IcA*Z,k71El1U?.swqr}6s1j?L010UULlwIL6/Z&fUJfT6EUS)=G.}fJQ%q_7,2a1<_lqv?Mb;nSQeSvAPBHh]_TM,PiA>G!
C5.d!l)D{6y^LsOL-7(n}iJp]6y&7_"sfs-v<UOiM:&lFXm+hMM9=2MvRk<r-q;;>IE8Yj.VX6__QfH[%2MXu)ZU3`iUu_+B5wXF2;E.BEWtow!:
V.w@:V/U8vhwEQl"ypUH`duXGZ?1hI:^c+[OMj-(MVJ?;p4_anJ7<H/gTm_Qj>fhnmOlZ`W54/r6k?_GD5feFKkHLfbIS.v+ihT.HCPId$W?H#$J&J%*+).qAr&!Z/TF0qvDE.2}
-ptJh4a9{jz3,Nk)
9VZi$zc;DbbFKhB4<tj;S=J}4|TgtOj_JPhs9|D:A{?>0V
S`U1h]J3v@#xdJ89a2S_`<24VG>xvd>$E!y7!NmENykpF)5.|^HH=4
QrDcPD[%#u4DW;vC6?8vC;DK9B.9
Kazl>S*_9D(;un2@w9IfjZ8@rrz9+qxfB%Ypj!<.k1|_S;AussRTIx}wyg`uZCG3</!F~t@d"b?@+?um~q5:4xmZE$oF55v!sMn8U14avU+-N<TZ{kI^NR{[79%#8kv]L/XC[]Mc5E!Z4*!K,0u(0b;7eb%@EQ:6.42It-<w2r?@(U
_G=o_oL^8swglkk
<~Zpl[3<(qi|4<eOy#-i1iMdxc!Ap^;>Q?^Aw8H%VxT=rJ+:owg)Tc!Eu7%e]$L9C"mAW_`8aeMw=#a06uw>E8N|veZAWOa;IXN]=>p`ao$]_=9lwY3=Cam92`6jN$LkmxB@<R`CPq>bpB!?mz^detxhNG4>/1iNW%31Nls5ND&{AwC#wT!55250es2uu"
gYP2beF@4^iVyFg(})c!I3$*//PNn8mnJCd.oTyW,s8gS*h*69w2y_w^,Tie^7Vfk6_
DODW!(aH9Q*:,ny/+MbPaI:?q^hpJ/RUJI53Pi~0bBjta*,HyY
Y<Zn)[spvi;`$+Aasz1,RA4uq#
3ItrU0dJyG_0X//]fI?olOY9U(RucK;k/N;pqp7>L/1gBbG@sh2eTwaQRdBi?Tu6Hl+MR6(G)nYi/X"O2Qub`UtU{eQcNgM<;5>"[u@SsiL/"a2eg.J2:O8>(*6!+*`)9P~u-Q9r[F3lIJ}^|x|D(=qZ.8TtB`GhchREIRG1Z5X.aWyDi<x?5vVA
yxU&gfy@P$vOY>rza.RD-f;ek07&Ow
y1BIt:VN%p9.vQn
##nEWr0hX3hlR#};5Y;l`Rd,@W6gpVuVxhAY_6|vea4gr5Q9t[_g!d:557vJTYXVITc1&]4l3r;Pr;(V&5VUg9W/ahmQlr7`7v6P=Bl&YY}UmP7r)5JAL;xX.<C7*quL:w=#EI?cKg-xZWz,23jMmxXb}xhHRiC:O/.>A
SI`IcFv#:l4d@pl*w)tL&=C_r?
SU[WavIjFVA!1SQ]d{*fQm_D.JV9]>ZIj%ryRJ`3?rk9)IXBPyV
BH-S`$](0+DMGDJp*MD?io=_>6!(t."z?X@Yh-x|&vjH[[Wz4IsRxR(?-|;/=g=cX,IrhVtBh/xo2WHXvX&P&}r&5WQaAoVON,$#4D4&7^95ny-uFflxlvG~>Ash#}Op16RtcZ9KneJ94v"H2,Q9<x!v0o8}>q5]-ifS`ap92!h`N%>P2Kv%j#k}3FTLDRD&f&p`J<(A)TX-%o4x)0)bq~dJ0##%>8>C&=xyj`?*2w`IH2kQ%/Zr:~m_RwoRVM<(MEk!$Rx{+T;X-*DtWH$jU!S?>~K*d636issCy:dtHSiQ@?+`b=b-NQFSuQFk%~[(qnET=7eU9-2,K+VGJqk<[I4UZDns:R=&]><K;,/cr)2<(0imHINoZf?RYNnqnw]GxjI=</)S`*8i&f[9_ih]>K6J.v
w&$]DY2AM8{[I.DXNagaWOqcF#F[gPnB5uD;Y$vq[Wh0{;bn7#j/;X,G_Sag!qT7z&GsPs4yJSs/fOi.>m.R<(tjE4Q1zuJ
aOS3jEX.iOTQ%/qFqIo4mB<B!0Eq*>MQ3#)p$-zIVT)^,Vx]Feg&(2SK""D$|sgTL7Kx{?4RQ#Tyt([<$9g(,
)"W7)VUxofmP|EFy[8Mw".<.x?-/I>h8/&/I},JOdCq;n$/1Edi1F#Dipg`h:s(S>s3xc%m-MSR>+iht@VDA>Hj)5;:@S3gAM-PVST$_z6Sw^H*xM$</@=544E]XW#=q:cU2p-7a@o|jyo[+cn^xBYZxw%oVp]
(Jql;QcVf
7hT{?bv>0|a<_/xGc1]ed2h+[4)n
T4Z^?"p];Pv?$tmh-&4N@j}
5^z:4+f<?HFZMn^[a/b$v1sCKwZ)ULRcw:xV})}y3dR<lSOK2^fI:I
(RXSyj
Tj#2CMEtvSgO8",p3yY?{l^*E+3=UR|!t655|?.gh*8%&%dB)rVLUo>Fec3&8RsVOPy%Q#qDXNV]gf43Y)mT;<92|TOsfQ.TAR{q_!!;hH$3^e@175ffo[gUinyw1<F-re_xM6?!NnB#,!D"%w"><EmB@J;uEFj@"OP`&!dFfON4LVpV<]&]@+w7NC;,)41bGb5Imr.6L4e1d0$+.7w33fbDc67pmID//&r`^ho/EY{1QNrek%asYmU"1hMPXo@E@@yMR6(g"jP=:Wa4E[n,{]Ff~v;Tv604b2?`7ODP9[!DAS}0D!F`>rY=1DWieZ$(4Wy,KN0hrJ~L>UbXn/H2`Iw3Xj}UEjK^j05,.p47Ex,FwTg6k@AdW/nE+$]Btl4MrD{[R*7=G"xK66^wfKK"eEw08Kn7wZ(C}VHv$jS+~7_U{1;/TeMuKkFh/b76Bei#7peq$_,Pc^6?]Q[Y)N@JWZ2BI%]F9=8*5)x35:[]j;WT__~!Q2X"6<.NPv|S#5}[me#W558gcARc_$/).:r9*@/.IR3/Pm3mqG4!-e{]ay`T)^V0!3e9Nh:3;Fs$`4]p<(mM<7]:bC|ZE"(u#
wa(0E<l*
`$v.!$^!!SS,W`:{J3tuk
(k)Rbjb(ck]ky:vc"=Ff7n^4JvX$d!0kKRZ8nx`yc~WWgzW"g^q%ALZnZWY1oFxOV6.2PpVU"IGYop1RCBhW&-rRHkbn(-]Gk[H(fGbGm0aG,3&/m+P
"h1<NGb&CY%WkI$
TGHykoP12
I0"j6#mHBoY5O8!BD+[{T0sk^jX1fCE)>&/%Z_Dr2s)k+jC/3OU9<3!(r(OM<b;FU|Go!-^pC13h
9;T4ZX+hN
kX-XXi!IqYh.p,x$1"pcqum&!Qp!r%tMaF%h~Odhq8zvrl}@F)#o%89a*kH;#S:-@02o;cWt}Jzt8/Kk{OG_1_gMO&qwumen;Z,mGHDac$P4!=Y8*"q:*VYl@Wvy9nV/c;Q.l)c(7-K&59BB"_%VO4yjz[%W_cJO]0X/^Q7
r#16qh08ajO=)O>1-I=Sq^i7(r];z5<aO*~?7,NG*]wv#*(_V/!`1lsd>Sb(wsU:JJp4-f~-:5%qj]*QA6)S$q7rN2]5Nd7jvZ^9{A:!AiK/1*)scw--4a<7}7GB=MX/RwjBow]+]wOs1rlDkoc0[>L9JZCM/=Er(Rcf+]{?Kj1]q5/ad&nNY6YxvfhE#Wg6i(b-S;ivX3^HK"em/"!RXlX8[":HRQ>";!U[3LAp%96D$F71#;`H&TOcE[?5<0SQGKA"Q./B5T{`dmYw#$7)jqf"irNsWq%&v1tUjXJ2R*Chnrv4r#vyTVM1oZM4GaY$9".^R`D1&8)"lQQ>^pC!-#^b/EV@CsB;GM_/eSTS)COmm*tI2XC0YQG)KYIs,OR<,0)Ev81F*"@v}P)Q_Nj==P:ZkT&h0<]f/D9,+)Xfswo6:ll2Ub!9AYYXGQtk-*|#Kb[,v43%s9v"Bx"pw7I?3s"KPDbw8$?FzW8*[JmkcEk,5*?)i.wa]6#V_OEa
:j;nec(>6GTf7^PNUlBN+dSFjm$UpJ5qe>0)a=aeZe77,N&1S<M@FElP!?@_W-$p.]J4S%SYWWyX(<"MvDcpdY8*:/dYLlPlq.N|JUOB:Mi:dSa,
H`H&bs&R+3m$GnEZ,K%"66|w4b]6ad8xwR`#a:_EVt_e7JHaq"%"V(1t,xWY8S]B4o6.K-7[CAfN:(q.JU^iMaG*@6H@0B<OM$d0v59[
hpQctD]"!+]eDs]R8.9]#.Pg)24B-37&:Z2%$`^^xS;Jhz4I%iVCW{yyIwZwahk{T}f+&:bg[b3^xmx+Hw#YOv(Q-~JYe}i.@sU&QLC;j"iQsH^2k{h/lU
e=M5K1Mcoy>Fmx};J@b`1>dXiCK-,`Xwqx8+g3VygEz-(#6ey1tdHF][K[Mv<hkD(:nIIj(K7*no$6`*=(J("?y
>dd*w"@bsrf4abTg645^R@<5iYR;jY"w)g2EXrE-L!@MN&b&jI8i
Vm<YQ7R"Dy6nXTl@j4SIJnG,`R?$U0Jp+}+zk!l/3e],c1BkN(ig,%-dx87>lpF$,Vl"!RXtE>
.]v;}T]N-
YGh@CCdS>@)Y8OyI2%@iJ$wNS(
cyn2%,f?XuVIrRt?HdW]P2!k2jL~EqHr+"*S#w?hvUs=CTx?p{dRcE4!5evwPKb/&z+m;quux2&?"Q1&I.?+i<q@3-mRpv,x=;MEUe8YDunoW($`N{.LLh2P:y-<<1m`U?OFe^s4XmNB<7I$"o!b(fqK1QVzM6A97D-%9bTyNO
v<0i_:iJn92=|R62rr=lXNSb2a~Tf4SiIC$oE';break;case'sl':$mc='+ZuB?h"A@*80o*k[@<sck6<B{p43Cs%X[_5
*g0U!Y65=5:Rz.3g1K[o]ss8BCd>;uT2<c|MdCfO-vof;*9HT)Wt4?^tUdG[<Tr5a<yrF^0lj?CVTFA`Fw!maH-gEICe/tQ`9M-1Vgrgda<-~t)k@t$a*>J*]>e_"M::C?p1@Q6oLA)E)kNZo=}h2H1]{_e[;FJgYnzPJFKYU0J;;1je+GX;ZQdGOyyy4J!L2
*?uiSStF
]Y3xz#rPljXZxaKdl1vZI3M"i=V{<|77D^s2KZsc
avy3L.`rq4@MYye7kh3f!FK[iP&u2F!`EuJ;)rc>9s
3bOVr?NnJnk;LbL%n]0~<QZ`k
5([eJ3P%u0
i?FG!:WeR,.x*C231M
BTsNBRQ+5-g%m-Z6hUiN`amI<eF7]ipk;tL-mB>ckjfy]LUxIgEBrRA1$=>yKM2(H=Q;KhW7sMa%T1AAAI_PV2qE$=G6]IVRPC?_l+
Yhp_T<JmV[+sV(w@qw,=Uw@3FY9w/t.[61}]`&MVNwQgf`V:pvH9iWxCIb;sg&J^YLZTDqDW{=cRS/nWD*Nx>*_b9[N4;$q
HPwmFtP]9,0]UsZ))sF09u|lW_HG/FdT
gI+xpVg{4YN.)0T|I$nn#<s=A{C}snYK]YpHw]XA1l>n:</[a6/AIH*1s<>EZt;*smi,r3ieSF#<myCw;C.iw$S6nrBGDWZ-(aMx4h*dSu+hEih"hI1&VVeU5Q+BmMl4
I@h"-),#3&M-)wD6Ti/77y6IIGunSL)-*7`ijw41^:?XVq^LOF!-xeg_5BAE(Q~tK7wVZ/)Pbrx]*V0ssq6rayj%HV{1fs77^2MY$7~=.>iv@=?97`iw=irXt(&s[L_lf*hF"H&e}y9JrNpuVjQb>Uu]r=jLLC{dzP)q!Kl0(F&b_45:#Ki@D<fUR;=ZKJRHbXUY@vqHWx-DP_f6nk{Y@2|X_(jve>)ahm9)hx[jg4qQ}_5f*5]Z2HuV*Kob+Kq&mJK#w8L5DBh%Uc
V-w;$#Jg@S]d]X6AE7S[9k!5+Hhg,MveA$GVSC"?]nM^Zae)[<AUA|4A
Y
m.aJIQG+~RuFEN~CU*}n0*}>@3W>eCJ)DI-=*Z0]XRCR_WFVI-t)sgsFUoMphb
-R)3f`)Mp_oS]Dqf*`214Nmy/
QBRUH*5&b7nC)`M5Q~:U*--AI%ycoVS`v<&tL9s_bT!Sv-bTKb0"WtAQ.vmi]>Sb9gRl80h#$Cf;k1l6]_
mHZWDD:=hbJOXwAOr+r>?v52<$Ljpay(X?!Q>]"^xB1sva#TJJ>#f0vX>IaIPy.+1P")04obZ)j)0/8,Qh<^x46@,Za4BT!b~IcFZDgf,R~^hRmbk,Gad4[4b
vw_ZP9$_@mNx*V;Na/;QaA3]&
kOVAHjoNPjcB`fEt>ozhl3z%[i"b?L+Q]MUI3*s(|C"f;qFUx?^t$T1+nf8"(N!F[!Q/6b<YqT,WDF,f<23m5n(3V/L]:ip)1JiojvG%DNL<4T)xG:@n$<6V&n1ET$i6bDnlW["WpGQlDCz2G?S+QVa!$re3(V3SEa@^h&#9Q4s=d_TYQxPsB#8)`v4eg5-@*A@B63j6YlA`MesRbp5pgQMyoK
d]#QK"Fi#`.(Cf%:Vr5=*V%6nTCx1M*=GkjM2~5LeqA@&s$1ts80!W6ci7sD#S3v/a9dg=j
DQI<xx(Q/U2_PdD3Y7&>T<&#s.m_cEe;O3<U/<3!nF*un,VA&jt+oG+r5_x6L8t%j{-!][U5N*DG-+>rb{+`<5A?SvjW]a=ax`B}WFH5rlqLmBkjy|UoQQ&F!q`D_|n#;5"%f
MTb.(>[O:b!jOP7igYOn"Hho;8S-.^#-.Ob]xG1dp`<P:eqZ12pz6$NGeD1h"|"K[vgl[$;(]CU$*bGc!:c%j>H{2_"2n-%(:0U[3mN{.tD)S,+ivd[DDnO{#_[8VVq82T6`tgh-l`^D1)$"+ifqn%G%TYg(!*Wnm)GaIs<I@7*:"@qW!{;58.mxA8Ir<:?E*L:o&X>W%3V85}7O(P"4)3rYa%t*m)]3@pK5M/Ss20YSF&Nj4>x{dM4^1Pf2Y&nqv/y|`[/vMR*Lj0$gt:b6HYLOq}W~@<erk5D/@nCfS=TGG{v.kjbK<#K>g19}#Sa-GR-F"E#}E0K`j2C688[rWMUMU6aBdLY2/%F4=k-n"G-K,z^b^z9JP(I
703@DTT38{&f-~Q(6V7%qcg:)O24m3dX+@L0FXxd"tpn%+Uo8oe$;"r3uoyTOCGR@FH;:O2~Od/Dv&i{?UyDkTr{`;?h+lp>/s8%/gO7_ji5xr,W5~cPWO3tMaGv&swa1*K&C7*U;kXZ90uj&"DY>&%9I
iP2E6&)6.6STo_jd<1<xM&LwhoP0)dMr9.U+e$T8/ZPP=6]?o[C@Dq,n9`2v2c0}.H%@^~pNcL0|qPbKdImqHQdd>`uDFfa)K%HO+{UGN53
Mu_u_rRcG"Mr2YFZbyHKpNNxYsA7th7EX(kLqSOqVaikULO^)!>Am-nl7r^{bL6f86-8..A8AYCJxK8Y-0vt!X#Rd[M;[|Z^(*bhxy1/ZBDCdu3LkwV";u,j)5ZPrF<>B`A{g.7_#S9$k@!NRJKhH~^p4Km.%QCH+BmE;Ev(!Rhz3mr7ZSN9(tDs_;et9k###~N0&p$$.,bf41>Gm:/9!]J)S)uDcbjb25k=
x%A%<4yDanZ-NR|6>Gy%v,pRdo=UpOn[om@0m.K8$lZ5p=`qotw.e3gNHBK[!E.
p_=txQ?&F6Q,8pfS[gWs[+pT!Zh<RS,,EYOs
%yEZ9hHNSyR?O@C!,}q{v+LX:;Bnk%FZJIf)T)&-ruENmMGu@^:E=VO_VO?$jsZPc?Uj/uKwaN#+/ML@@.oz<|$7c#_^5L!"f6+=#0?a-oE/Nj#R>,l1t]ou`:&RNP-XllS]e3lfG5%-`T^L%6du3j<{[*(Po%je"GmKv;"!XS:A8x7uJiQ;YpASFmb]cMg@Y-yDmwmdx>?>$HeUVyDPT":d39/W_FlHcBumKeFZUmP#h`2uKJLz33^&f_ap&.v=%K+NP;Oe*kdVAz2mS#%b/.[_LFSmn$2CX_43,setG400rwu4&>gcn2kR6>&PA?:$caX,EKe8S^XKk9RS#,DgEcm}u4qLaOAr5oTGE*6RNWj?CWX>(]Q.N53fcl-K&sn2^4@n6c[u+,5PX-WM/E:.EQLB@9np<R%(K"VS?(+oR]qCh:v8_Q4Ef#,Eih"{X/]s.NL{5j[]F.RSC8SLIa"[j$c4][<w"n&paaRrmk6LX:X]StE("E!jw62IQ#R|[ORdj*$O?0)kl%7q
gl^"Nbvp[=;GNj,*Ey0)g;?y{:=yZSyxIWwcm%K5+7:Ls:(`}Q
feJGU
4%m0OM`=cj7mnH)agC=[P1y{+kLM-0s.!~>@80??(96_v7&MN`29H-m}5-s
n1s}:O:PJU&^aZ<5"}9x7sg3KR..@;1C2SM|Npu
ew
(E+2NiSXoG@X]9kz(rj5Mr>q"k=M+f2tCKe>oqx7F*x/fsS=[gHb(J9jR#nf`s~c=o^"!sMkSn~$hY99OXk30DSLbR%[6in>?YsCi`*>@;IWOtU*Y`"pB2e,A]gXmRVY]Q~q;(Sbe0WSm${NK:9c}?}.6.d#xfm&UuT$~QSd-G%w0"io82]N5a@Gcb-PUi1t~[%H1$sA8O]P]+$Tm5SnOwYCJX%ts(QqWMF:+9h"~<<O5(YY05{6qac>G_.G$+j4i:utYD5#oC">l-zjDi1"7fM8Rl$BTDX*Iqve?L$OFCCi(k0!.Y19;Ji_;+auH2$f,(E-V8&>^I6"PsVnXd$1_%!dXr][qD&8R3A:!nMT:l`)PhdKa1z4+.E1vg^65"],`$)9A!,dQ%ktcmcjhAd23lVJgU2R5[Xu3aN41S5uN"dKrXv@jSR)NgB^}cLv/EYP5YCF)!ba`<<E]El*31<=&MQiV-KpXnUt9fSgq!X[|qxEG2RJ>Y6ZFx!20Esbnt?xx-1z"s2tuiZu,SmI+PLD02^)7moF!A{ldS&ir1g8r!XDY;!w(@29JB<IaJt<"Q2"OoV@n9%%QF`2Q`@<tPX8v@5r$;P;9k]J9Qn73#gBV
ke"^]*v*=Wr,>jL1Vg"S7d0sS9OVLa2YeFJim@n(Nt
[$Q[?B,Q=Bk-[=uD53J>t&oCc[E*e&&9&Ls,a}gfVTyQHX_y&|SWuZk3aha(LJ&kbQXK)8.2u{pRZ$.8,p[P.g[y5M71#dvAPT!]8,yz]<t~glJ)G#u)RSA}hro/4[jUxTqMx.IF)jYvGlZIp
$f=_f>2/$Nw,s42u(3*JN:0*KRuYxJeg#FA|;#aQ3"5SmvK.OGK&w"C5o7F,:$b
]|k!d`H=Zy=7xQRTw},qE:0Ki54c!q+HS|**(/n!nD<E^C7sfJbC?D$=lgQ>gO;EQT-zGr4x*daH2w=WR2$!rr&;eFse3##dM#SsTpNaky4TG%&3.rY+5=(C]wJ|qg:YLoy-8ZwY%]AHO+5wS-fbD/UjpQXot%S[1bPUoNlmT<-+Io
XH@QNwi7|.JJ7NmE"@fjx)kj3WQ`ZL&/%]8a=f%Y`V~gZimfrBobXTQoVWX^D7FY(K5TI_)h8?G=;4V[7[?hhcpMH=*y7mL!L^wA^vQtX';break;case'sr':$mc='%c0F|6lCvB~@!DqDIp%MTUx>hi
ec^v*</
#d36Qh^v%f5^^:$JVe]5bCxVTc+H!YSeV]EcfYIbJhx|b
yQ7*ujk;^+n,]B&o!D,&
9mZ3sczBwj+lG*7f#*t
/l>LrI4S283q"T#wotRgf>Erg:buXaVYmGV7/bW`WUQX,n`S#OcW!50]6dU#V"gwtnVq<(7-%LEO|KiL,rZ
z7!oc#vetEdGgDZgfY#]&jd)rJBBZP.6:XLLilwX&Mkm|!YvjropmMjO&91D}n8<2J2V_1-xaKZxAM`LN4BB0*}AXK$1%bs7*tf=sYaf%WHGb`9=<r/c3#
s-Ib=ry%,O,CAUqjDf8#X=gmspMb7PF$A=csmp_?hsx^w|OfAz+SyFHrM0TYwRGJK/jCUfuAGtySOB+vM_7OsJ*&cb&^pGwx.l
Bpf,s83`TM=F`8XaxeXX$E[iQ:DB`<dfyj991Dm3R5l;rv1@]>a=rh8A+DjwJ^.-VrA`lOLV]_Iw-r7i$QB,C;+N(oh`F<|##LDSOI8P+_T[0BzL}Q/O5c/oQP:dF@2GiQ
.{[S@Vt"JM;8J)Uf;()=j@?"fT9wk5N:Kq%&Vy3bRNFRf7M:U
wvyDq%k=(H>p/lU
j1D(e+2"L.M/sNCE!CUJdT3hR4?KKCr{hO?MHG@sFv/ouGtN!iT&7gtGhO^>B
@e]TiM"#QGNkiK>r-=.[`HkVNlJw.qa{`OYrqz
I+x&(c|dAM;B%`+Wz;2Vl-"0!*Pma9(-;gv#<K?B.-n6ZRwUZ.!,kq4Z;&p`W<J`-1oye@SX[vCDEM@K<jFGLa:k|+
Mj4%d!X{F$io0}n^f8gjKSp[fx
<5-9!K]usJ_<a/lvUg9-T(dFKud?@[%9nc%2IugN*&ItdZE^pp!e>G6;Arw5"
YF!QAt_yP13oH5w
fpy2dsnxOI#v7$+/!.mMFr6:
2,:8iUQ[r_bK;AQu"GCai(p[jvVFY"yn;Z*K
r6jY;Av$xAYq)Iy6!JY$TU6;=xk:!&X4aw1rA0hjvtIPDywk
FJHDec
v6c>j.Q0!B3%OG.iX$0BN&sD-]2*,hNpZfE6pP(#UiOHXbhZ6v>g&V7&jAqHxQtYu]KtO];^p=Y_u>vgRtr&i<nfkTP$-RhC?Ec_,N&CBW]pI#pVb)%-~Y)C=7DXTlf]Z7yar?]9iR5u]d]XNMzJ8pG&o(Hqf832{B)uZ7<DW^gDEXpe+3*dFg34,qZ(*6_^H_qopWRI,!IxR#aO,dCu^B6mGVQD9+[mFWNtHUp;;5Dd&W~%XX,j-tvg#)T=Hq.0$$s(%(](wn4)2CgKAHQ`x3&FI_TC=xW9.I|%SAZ//dfvBLpgV-?JGt/?:EM=*O9hS"tC!nUYT&|-&UdP=A4soF@yF3J8*^Y)v`+ZKhO%yyuXG<5cr%;WDV-c
u"VX-Z0c39M6p&_!;"@y1:+^Q~7.J`7JDZtT-Dn(a<<}@_=B;s/p
bU?/pX+Wop/^~OEtVeFea#ID@u-E/.(43FW,tyGUG%61?%B5TbjY/NTQH^6@X"K58QB"~k!QG<s<Q:N9/C1VQT(8IlO3Gq-.ijSi2vMpZh7c&Ys>i]?GMF)+I;]mQQLWa:v-lCvfAii[YaLS%V8.8qyDQRoqVlFHcWuk_Zp
w^.[F70b=j1D%M@L}/tjH(U4!!sI+!tDV`deqqa>BL+dD5Ww
=gmzdhM_kl_yE4]Xg=@T=kvn5Vkh[!)y:a"
x<3yFH]t;v1$.&1VtG)WdCMG"X`T^w_l7-"zrj7j2haWML
&+},j"49X3.H]eOKA5ow{W0oja0Vx.RKiXxPS5/q9Uf?/JtP1("9:D~tfO3u>f9s8bD>f2VhQ2l${3*;|m7PDA?L*LMc];riPC7Mfk,q4I[ok`A(w>u)-2aCiVr+Yin:"`.]:(KVkXMad;}Hiwv);Drr#F!p>?j0OBb9LSu1&H$)Zp}Us9h!]+s:g%N7?)^*D@%B44U:.6zm3S5%Ei#YPd**!$1uq".c/EA*4y%"3LB]qmK#Z6C?:[40W(,7l$}CFj))?+(D<Bb5OfRu"1cTj)UcvxrN)I(DK.cgT&)DXM/LOX[f#i/5W#Y(1J8Rm:N3qbSexb~FlN})?cmpBjPI_n0F;GyoSGX^o5KBv3B*r(q$"GeqaRb,$p0hesJx?J!a>>`437rJ"UF[vXI6j9Omj#/bHk!3Al!o3Z"KJVtG68/k*`!d(A!9rd{hfx!>ojC2GWt8N*BwAmo!ml[s+Re5,mz0vHDCohoM4l
Hh?i4%v%&#RBm|X{b#<+o4$j)+6G`(OE(*gSTUpjLP85$o#q@zN?)F-$DxN_F>&Lfb7Gq9;PZS1Z)3&HX/WY@HM`JjXXMqn.XB35i4<E#/H)W3!>]Kvojfd4ghZ5(XFj?3r@WtOkSnwS
B0H[bLzs%]Cb?EXFd6v+%1wf0J>f=V}%/$Uu_/_HW*T$vJm2ErM8!"yn6l[@D*/V0Wrm5
3u7$3<4ec1N<i?i9zmXHKO{JJxu;T?@?Aobiit%U@
b"6krs+L(9,t#NP#J)9_,<OND_.(Wu%l+VrO%51+3X"ELP:gDSn(9N>h|MdlEas[AQ`GB.8O_7xlJ6Cp:IW(BWq[.KHdz_
1fhGvItP:C?18|(1"}dT1s0}GQM^E_Xh:5xi!eakG#9)ECF`"QQCgT(hdrq>Ay&[M/jhm^a&*9sN5B"aAj;$/LWV
dvMuy1a]$CxITZ
Xs[gaiIf7
fKONbnZ=Bf<km_Jays/}jzci>7z$^5xb,&7*#!C.waRM*,R!JUlKJ|Iu6he6,BEY0E@@J"p(W@:M[Ol5e1PuPrue42N61B+$lN[f(mnh=;tpOsdu%kZ4QI1yGO6w"Q;TJfAc3?5
&FZp!Z?s9]&"6v29oki>cV"][@*[`3Xkn#<#aIE.`9=B"T_G[/pB#4d.;-bmI23+F7"E)qf|%x:P=e[JQyIe!:+SF,amB)Y/Ff@ib!7]exR1xxC=eyu~DweNfR_3)F28yFpR09bwftn
TP8P6H_5Y(g>KjfNVM"MD,qo&z1TH67PYMA>R~k*@")z-H2k7<ZNw"6|>*)Ugfeyr=Jl@y3.J<1]]1YH#?qlbFZjYHCVwzq:YVQ=(RIbwIm`Y{L%rR.i/p=|<^@*DD)BguM"0e^feN+PSX1~5IOY2EQN^K6oaKIp#o5qF{;pCW7:h3tI+%jk
mM
h8!~qtB6u{%}C/0NofflV~S@tH3)fNnij=hHi(QD/,]UeD(wTYr&!M!*3%56yTQLxjhrX
%XK?iG/Y*
"|4S09mTj$.EjK`E5PH4-J!pfW]9J(n]i-8+p^_Q&,a]w>QK;|YMcI:OVVY:fMS5WU>M9Ym|!.-Xe~G},#M?G~>m,f7GsSc9/r<0H*hH(N-73/o7wY$pSGa-3Hb`ORaI%J%DHG;W0gb}Y[wasx5m7JL~R0.-^$O[Ao4
ZqB;v:]l0}Q>D#Id<6W!1[-B.(n_/v5^`}YB?&.GgF*378cREFWv]R/:K<cH7U%hnzbug-7W[8=RV*[*ydv6m=fojh8Gaf])jwsg_>C/[m7>xLei7gM+/>my6QK$X6$s<(ZNM_P:.TXd`piyDPbLt`+!44u;SPArCRP|*(Fl4+X8[v1}mkhko9FT"8;{@_m"3~N,SLK!I&WA&$QJwm.i!0c3!1$DK>v6/n7`nC>{K<^zbg"qXI70#><^`
4<[~4!el;AUx-7v|y<:u_m)LhMiAe-loJD8WLW"RE%O(>YOxYL[[j[3JL"kq^oaBX$*7wHNjWh
G=<[<8??ia7Cb0XmS2:Vq(X?H,@lIh=PIczOn5wUB4cqxmQn^E36~[i*=RE.
sa58@QZayMZh`xM@1iUHtq_XC&Qp1=)0dGa8w5X
GQ3tO5//w|-9;p8+
]Thc3y@qD6
-iu8CJ&lwVFcKsM}8J,!VB#XN87}ZdtECg9hhTL<KtFXHln/9-M[B)2)o$eIp@8v@V(MA+,I#E,^3026p0wg`#nz>+u9Uz3.TB%?;7s0NhRFuh;`hB:[UcJbWg40VXPM
~pc.{)
/E_dbpB}d}Lcf4fN3cbQmsL.S+r^33k9CbV/uu3&x|64[Q8Ce?D5^ED{=:#^q?eEgC=C!sH.#;.)qdEM*)CKC$L=J#KSu69voUxMyy;#<is,q[r%5,/QAGZrUhU5_NE,(by2:e8}kj:`Em]d.aJdC"l5_ob3K6gc_J$FfW[wK-K9CY#e&av3+OH$e",[V,dk(2`?8X/!xGUGt;-m$stKT2:TyLYwud0Pi|;t#O[>F}WdONaIj8V2da:=f]U_LqLrvfL|fmB+!0G#ku.qg).tc$%A(#Mt;Y$`$uBZIZN|K(VdK/^%QF<=*y^Wq+6II|gMs_K"A>BRBJ`6](_>qbx5t~9D5[!>F];c=&o8.b!}cL5S4W5iFGOQumj#:Y[K6u/?0h#wx=]{ti&Yr)ETF`?SOOv3la&KuY(&!q9oJi@t>RRa[:ambvCN8&L}]`teWeQ$96n;:rctc:I^Tb6Ohi3yqdnPL6hN3fx/l:Jq2y7!8Q=NEOsPhCQTlk&s%@,.Fv1>:(SUJxI+Sh`m=.o;0|_.8C6KB+d*y:gpO$Sjf%f{leJ@t:=.X]OZGodG8|:Jg"8Z7J-uhqfN]}=.ill~o$x<l69U"a&8]^eOCI$eaH[E$YiD(^n4g3BO50s*_eN$N0;"!]cFH*"mi|mTbT3H!A+4_X`hPGtOjWGN*w7w:)Tn)fSk9#gr6RG]4h`"GSMS2g(PJupZgdm$?T.G(+bxcc;~l$e?BJU4U+-B@`]RGmJ$c(<_*Ia:E&YFklv
2neL69Z>`2wTeYWmV=HZn|8q:^P0#T<p$i
|M=1"K1y?*#8
6x[-YNLRttx9mmP`^cO<K)%8Y*tkvJ="ahiI2qaGrjIQqsYyV6/Ru,X*(?Zzn`j+@.)N5x3u2{hk
H2>F{@ARv38gfaz?llOpzY.]!Q?%by`a.G;H
OHvGm^1Ry.,_3R6eKSP3`Da`;*[[g5c
LC%O(:PXOvqVw1*Jk,b3wQM~kVcq7m.-^KGt>Sw!)Y?^q#*w_yIT,*U#R{!qf<Ana}R`FB<[aJRz(!E8lDHez%C=P3<qDk,~CgyW$3n=k*nA;;"SoQM[X-iIhr_a>6Of#(Dcp);VsJRf=5U6$0e[6+jj00I&jN^I5%bh;%+~Oi^]%_JuM;g&_"xkSnKDz)!I';break;case'sv':$mc='(Zu<%bPDI+YU+o-$lT5OMqNp2CTpjVfmNh%?0l%`=vRh7j&QR!*(oeR8OfIB&#ce"iWb{Qcy1
FlRaX-c,MQLo#[ll+
ej)z!EyXWuo.``Iw,r{]]3#m0>IF*ZrU(p:u87(:d0@E>c,>%)m`i;hK/a{,*H!2Hb2ncjWA:bYrEb;9guRrO<jgH/nQD^FM5jVmJ
Vau@G<K18Zv_nT"^L=-hc3tX)49RnD1e85&0aat3L4}wpvIvBsb]}l-[Qf;><D10WOkFcdRc/t.a(__w{y5R_`YM^qil~cT7Pz%u%KzVi>]vPG!y4URHO*tma<ABix$i4vXMww:W+(`B"=1F4).#rt}h[+*/OSELtr{K%2Vqu?x=k)iC=5*AEyi`B9|c/L5Vjrlk:
?LuodYm^Qvkm0Qo.mmRM^d|C}Icysc+Z2_cXp*kI@g[PH[4hNl^u=fAXyIFs4LXd/Xxd:Nf&L.2n9a>.{BRR7*x%0gLL8^/MP..g/(.B[yWbY8%?jZdP:"8AR>,sg2la;erLn@r%Y1$F<k(!zxT;zUo]v5XkE0NF}!o7YaIMN`5/5LBR;+$CA&;.-$91^k/0ac)%!Hi-T?00s.ln(fTJ?8qDX?z]hZ4g>84t0>lduxk>zD57l1gyQ;ue,s`aG
.3$B=.Q.pv7Buv]^9MqEdsS?twkC<!166e]_&!&Y<VCk:R$8rd%9E/0T,tGpcPeP4V.h&`u;]nB$|ka%*%3K+Y/_uDec.2y4[Jf5gW75rr1?B@{@yE_b/x],65N1I96gGMflNtA,J2hqU/8vf_C$^FUi"4>2Qybm5>{Lh_k[K4dw~JH]xQDGp.m!!Vjd[#XpFeWfUEWo_5)@+MSH<];0KkyNDsAV}JIJg7uqxd/?9-A=95(FVUC8BhrX!+HMT]iO].o$B*#qnQKK>bjHr#wM.+P=1y&RJ9mL$-n6{6!Wo7!]?vw2+4TCR5]4.B4[u3fo~wI#7M9k?1.*%*tgt3B]q<<=asE+[-d9wd$u]e=[THC0AT9b{5W
HbVSjR~U"/UR?#aWeWGGIaeO9q[kC=hH;K8<tv`.EhXi":vLdh1wwm%@+nR2bZv`wVx7syrJJb(QJir:Qd^;TNCkLl*?}+%a3HOgP!-<glu1xuP&M*;BGW;J|@wdr7quG-=O&r0mjB6W^rHM=axy<VwbiW.JTI3Iqf@MY,fZImjfv)FL/X*K|J(Km:k<<&4L}Kla%qKa[4(M,:nX.mRB.u7EZmad$#oeT)K)q^pERJc>YI`e2K,k_I.G2v4n{6I7<;"Gm[/kv?*5H9dp%Z)4%0%VzRBh=4rxuk?RC4DtL9I,xeAIO9rgiK-35^2SHoS_h?OXm>@5nN{9e7L"8K`<iMX24p(c,l7tMobce
_=NXV2MSOrnWxq
noFLwrIQ1wP(]X?pMB0jfy0PwJ5EXYmj:/"wNq+Y05E&0.T)+6U!_CkrMTJ,$
;O*(lK2}lJ*}"Mt,h%1=@Q#b7JjPZ-%)D2^/yTHTle=-D8QZICKb(%gt%vyFb:r^-JZ!H~UUH=%SZ%oR,i%Vu:!1Xzl2O(p<=f!:SS3#U)Y~(Y6q*u-~XO=fEMw.G5DX2yv-:>Z9?B(w<J,f*mg:<L:F1
0@GK?p@M(eYNteaPKS/T?o>7V=b1v#w*(;XWR.$C5gHmCppb$VC!L?5?8$7G%Vkp]g=omE=/3_v2y+hZQ[ic)smc
(&t"7i_bU!b6"Eo4obBmtNwW+/L#d.WH}]Yu0e]DebQw?i4Ud;@:H+.L=Cfm4d)$fd2klMNX&52[d[v^Sl`O@=RwWe8QMo<+]A2;XWM%yI-U7DHK}PD9_OU
xHU7C%R4jII!/KD6m.2i:R5-Pc^%0`C)qg{^?
FcXp_&0vlgA>GE+,X(zSU"RxpsmUa#>W[g!,S3@8@bu+p)"J",G.mGYej/iai+P6|!;N[R-Mq:}.oU[JqT%eVVtxV:^KbFr%FQ=gD?SBgVhyP%T/y8Q>6A+uB"F#2<z"jNj!r8.MVH^D9L:6fUWFyrd-sR2z$U+/Cpk9c.dfSGlFWdYnam/[@G=uC;v0{#LNqr$nJ3TD8>Eym!T^kwr7bD&-EK7hN0TmoTIEKUAs>DEP2U*%:%
9l3i"YC_x(F(CSY(oY+sI.?IkG@o-uI|PFRQFY2u]w#,H94OiOt2wj,+88sN?qo}J7C4OCn|f=Urk1"CZ3*;[/O&5+^6Q3Y:Dg[O";b$/o[%r&[Ry1RNC|!.VxR+8-MKXs+DMY+O6^Z3!hTK_SwX]xQJN7;r]&?:x:gfyDF=o
?2+s2aU/W2gUAeJ!Rn,YQL%}X[SGeF2x`ar2<%@xVN
spy@H]gx<;a3vwJ;Oa@+%3J%z"$im.|F
1o!TU%K!005hC)_Z&>6[c}V2U-,ndgU%y|b5B=WR;0_cGwQgeO:d<h2dKU)D,wx]JB=5
QFD.j),>p
Kbv=V"/`sw]0L5^.jy#.Ccm
zN]QTCk04uI["65@&:Pt["l+bw>.~oL-Lw+^iok;]57,C,?K4>"a$b,2h&N4/
wu<R/GT-Jg|[GqMTzK9$g)*y7*:"OQNeSJv/Ewh5X+6#,U:ik&RBG"lbvkv9znt%0.HOK.TNv.qPXvb#xkRShf44@%U.3-7N_FPAC(i-bq}9u]3jkS&d5YQ.`uljCvt.lQ,7URt4B(;wj73xyt2/MF^RLGv#b$
33Gyv#D3nGVs7mIHn0p)pg9^abOW_##1u(z"=g3rKjHZ>j7ZGx/{^yLLfpIA`lR$n4e
I9Vb),&fd(^!F{936pfB!8fn46YiFTOpRrlS"ASw**U"1j^t4~cIHc;@`N
RX+*5($9s/iG_i(GLvxT3m7/{x#P&7V?UH#U^
X(paID
U2Hm*7dj/lq"y4WfCaNQqH,]=8=vDH]I_"uExU+d<]7+kg85WA2o7=x3O$G(2f>MD(lR,ptOL0-
9#IhjJ(^st%sKYy1PL5Er+4xM1O;//>0Iy>1+.&ziNL{Wy,vG}#fW-BdTLS")4nO=42`e;_L95We-)lu&ZM<&z3l,TF!N]t)e+>{LS3Jf}_-D`="W0XG@v0Jh-nNFRVGCrb#CPfBg^#3AS1Ppu+?wV6iAQSp%::-Yr93q@[G`dE~M$%l<Y2[^XL)=vjz7P9v-n8!uu/%(V-?+pS1e8^j2GD$"S;O+9HXK4x$A}WA$a5N9Sm!Ga!3r/f}:jmDFJhh<D?>ho3>HFQ}I:sc,;C&G;tj*TKQDRq1fH6!Y@7z->Xd:z1:0VFapUhZcP1^6)UdpD`#QgDLdz$Z9LgY3]J]m|StjZsMb%m?&.=_,w`*U_(,PW^lR4&,_c!BhdN@Q1<qf>[S2mv%+9BI<@x"/HBDOmNrLoES#H!
#z7^H?ErB/s)J.fLsnv$MKoR!&uglJl-JLSZLLnt0*yi$ty]a,of9Jv>h?T#_&^Y+TJeV=$N.?_
fj-4C{wZRl2su),]C>83
-o}20$XRtvF/%2!-Nd8O4eX9UnlIk8!m[xqqoG`V
dK%g)hY,mUw!oE3*?UQ@YChJq7Y9g2n9VB/WF]hAacii?Mv5)A?P,r)Xnp";Ix&=LM&.N7/]pV"K&P(`B%rvh4wK`(eEH%s&pfv
H[.HQ59A^X06At)ceJU&gIr"gBp@On.jvb+,"jHl0,m;3IN^=kqBo:F*YkV?jhp,4e+ir}If0G"%iI_q8uu
HN.vF&Is%Sx!pS+`5>Sb"[sf)fq<ZjC))jS:e9dA`}Xw*FwZ"|,
pEVrM*RL-HJi+bO,d1+j!VHE]Av2OI^eJ/M382jtR5838_/=WTli_^c>#}wSue@/*{n>CS)TM"Y3%*BBe0HiA$a)vhdU2@`X/9&GU#j]hR>pG2":w#Bz5MjD5fPn"hnwq&,EfZG<j^3K<6wP+{gtH%>T:}L5
%B{wA3R3WLE!Ii}t@YYNt+}v0#7@x)PuYCpRH_H=QkCxmFTQ:o8VEEMBiEUWW`f4-^hN>I4e+G{^iuE]bl,pih=xw
k?biEtoPB)U]Px8Q;xu"=wi28jNr@7A_gScg!MA$QiuD|%"91x[k`sE0-A;7E>G.HO#r$s3N4Gi,<Ee-$Z03+7QNP1zPI"9:FqN)iKA2uHLO?sUm-V@AA=;T:QmPLx!GrGiL3.@rJLw1m/*#O;M)K,r=xt!6&ZhSQ-baD<WZ2aM@6]aFey)/lE,C$!#I1SL"j!|wg
lM7H-M[Q7&:[AD-gG6XOb.x0*Mqp8-l46K=nRYBGNrLc>HPf&0a39xBmV=Ue!R9UjN*[+Zo;/^WSdnCTYYu<.&Fg3%QV5L;<XWjbLnE"I(zV>xq.eTXO
Cek}*FbKqjxcer$R
Q@4^i-/;s(dR}h4qu(PZ6uP[@HL2)s]i&hrW`9/T4,-QJ!ZJU4.h+Zv5Sw<>-z"4X';break;case'ta':$mc='&n1Q<bpD9,|?Yd0)-$pKr]fbR<?&VT3/)^x^z5C<3
iQSIC"1CSpqDO"JNB;AU.8+"q$(7qd;g1Kx%};:TJMtBFML.^^3M=[dJ3sDBE8Yexe~,#]*JD^*v}K(vVp7l%xV`Xs%6M22pY2Bs$ZI27oG,KdYuY!AuZ&<jw!#t&un7Tq7nL8bb%TiKD6[Xcd9mFN!tVmfZyM)PdIn*m/Anfx%QWMjydUV#nMp!pg<MRq"KVw`l7ofGRV;SQ,^J4w[*Mu*XOwAbudyu6dQv$5yLsB|wLEGshfyNcKANH5m#,P%suwms3ga"e"KmbPSqnN4>PK-t?-8O=*Jhvb4SeER[z*e[2cX0UeDKDt=o)NL;Ilrsf-3P.V7U%JeH@b#RL#@GA>qSS"GfuCm:UEltS,qe|i$Rj`rB}[F0$uvE^Ycry5^K0wgoRBMyFjILZd~^m^=S@lvl,7TA(fIVYn:^k
7X/nEDk6U(71%eFA!j:oXA|!D/uFpvSP]-@/b4u*xV,.%X=h0/%SA-ai1@7;xk|PfU0pju"9aB<yqgY<^<O;$Fb$@kZ#YbH@1O$uMDev/LCYJvXD,Y96}%dccR{cn$ty6jJHp1*0tf`%BpGR~,UA)lLHhBw.UFA)$;
HuhpObn^fpZE-e8Pg1SwnJ=Pn&Cer
6V6VNI
.hPoWPaT)q*.,9u]v_eJCmlx=ssQkmN77efKJ];0sZ%EGYc5[AkQH78PqgVT#[ygeBsyG9oyrrd&s`@_+LJ/fL:PO)jYUD!]5dGHA]vegVR<E_)3tU8>3b(p.fH9}dsD#3+U>B)V)[^gU?OoLiNj0.^WJPZVC/8cgJe,]_1G`tB559qqj]p<eYH
QCdu3_<r::eOJ8#>XmB#Cd
d:e
TiklELnfE3fgwd/]VJATLO^$2{,0*=-NQ8Sx0}M|VnuLp:O)60ZPI(*N"/QA?/%A:n]uh_G&e>rW)UFC=z$|L
<J9GOz^bm!9uvIf^b|rQA+0{b=efM+Z"2nTDMz7h)R@uT;d@bCq&HKb3R2w1P[-I11rz<~!Z.E"}h/-z=/MOKt#<q!p4lkIyAXGFO_[xp2^6=BX}3k+leEVTTV^aaa5.4(hF-t;I#B@CCCC]2g
lN6qtt:G&"O0bHHx5x8C[YTnhU-(e]x!^tcPQ&;+m$?efbws>
|)<NksdQw:):=]>722g,q3yn4E7UGdu${#uz!oK@W)s#zi*-NN$tQwPK",^p]4b7*5ZN+AT;X[0Zu!=QE"sWjPY+f$Lg~;>C0Sj(}Z5J`M<Lmu(q>wPU&ejAR@A[=abZAg04
A2
WOPGdEhOcnPevVM<3Z=1NG8<JcqB>u,$Q;>s>a=>4c%buo.qD>-9;A9W}m6pOG>qI*8%U`g/E7wK$x(Gs:V"TsbQ)*%1
+#jTB_DI.6
CFW[GDzWHxDH@A*xdXl`T9FiSOFC*)rlf>(5Y>+jAQdps?
7S,7

2q>h;;8hf8^^b7H>mDt@Le:Q/tU6U_,)f|k:`KHj_5R"d"38b,Wuh<],/ay/#P>~2Hh4&FHGJM4$iKq!uegRWJ1YxG9gqO_3of;r`B1dxmO/=k+$s]x&*El/<.94pm`2iOE<,
OoM,WBG+P>$(wk#ZYnZs)@AxmpS/js`VM!n
`d<::$j`5`cqTthG1@8_xGk:"%c(A<F[-x0c+s@E)!U(rYfEiK]Z)RXmbJ>sp[e&_0c8des_]7.Cqyy{d0+K[q(!.Hk-`]R/,E*2i2;gJ_(R_AE34uR4aX;>F+R7D,Z::Q=-3&$ABX7zZQ*?Q<bt@egs6bbq8W^4U}U[Oh;M=+PmoG#1DXwBN6Rz^P*(6(YD4)-/3iE%A561=#:.g
3d"*M(7SgwgzMiR^%lY-_?F-KmGC?^+xVLTw3"%z_>&{>eF#&H_.wdRs9=l,9buJcc[eyBvRHMm`Pp<?t#^QTSBbN{tf^oiqxCQ!.*8[>8@Z9}LY`)i-q}m=;L&cDy`p
G-e+/:JWG+wBd?/>L<U+]q8Wx$zOc8GagW*!*91QK@5F{b{pD-TO-9HT&Fh*RB!d=YCm%PF"
Cji:_sH`v?F-r*0clf+/N)Ndb|==fJsz*r
f=pBBkstK
Lf=HcGl=;?S]!_QSwF5s>K|NG@i%~`Zh"U(<FLDgUndI
j`CTwr@j$
xh<e(JFQxFQk,Vb.CH0=%*=!pH0@gy&hY*rSxgh{_A+h!ad}+&A1M~i;.m[QqEQLJ::hJDXVE=p)t]T>F0N*r<m:F2t-@}N{O7M:u;#`>6SjL,J4*2h[W@]og(F/kCM*hYusf==*O%`D9g3;-_)mDFDb0*aiqu5hj.^|_c"c9%[;JDTh+KT/5eQ!UwPQu(UQaID2fAIoUdX,i1aSq8MoDL0BHSEhV:tog]T
_E,2Z-+6
r-"7sfO
.GfPiKiG_eP0FQtLTT>a(uHEITY*/Qo;6i9B{(zCh6w./[IAPW::rATUZj6pA9{<0@);2;uw(G5P*J1u:?FN^[D!:0gP0M@BWRY>eg)VfSa/Pa8.&p]1M=Pt1Bj;Y3.q]G:2"^AsNCPL!DLMfK^3-XGKwj2k:/W5*TIb8ASHxMDS?b>1Yx7K%@da97Re&FwYGoLh]@QRdZ:@Gu<jF%nKbA]_ThA.Tp=PA_P$Be}d>M:3dey;0*W1lON2>"0AAnC
FR,?@+7//f_p$NpGcn7P&J{d=12VIG+<=RWo#I
wethX$:Qg|cfN"7EHaF}Za$9PyIga,g~$dadaL*ShWOL*#5
v&/pQ/Fhx$QaR$0E(xs2$LTPqYnVDDKefJ4/2R;4;ar|sXn%1Ld#(~n#I+k9?p9<ZQ-"T:fY
1OW%,)R=L$r/T8:iKDApi8++7t0"S=c`>YtkM^RnyJV])Y#L87m`HI<H%(`DpfKwks++/dF3M*kF>H{[{eY3V_f?w#8`$Nl
Aa0P[3%Bo3A8),#it?]F?.:kV&3B~#LbUvU>y.[uG(A_ZS&.WNL<61P:TAFy0%>P<n<L
e3.{q?RMp-]BI[<*l(Yk9S62nLqB1duPOprBe*CALDZ$_
04d5Ikgun9p%^kBlq{D5l0ij[AmuYKg{"bx{#Q^>Cz(nvgUSi^C!3yAwhfsrb]K:rxoE*,X2IZFkr*pAe+0$;zZzHc`+-Pu_g)#N-8$sK{5Jw]$Xo&,./EvK3#ZrQGYH`8oP[o]?_Z)"J$?$t)q%;Dkh[t.jkeuB)0;)_Au8QP3eBISs)E1
$#suYgdo=D:6X@^-M1]Wl+qE>T18p?"7a7
*%NMXO_4~vPR!C7!mX%)rW?Jtu|C`qxGtsBb>wh/bwpDRt0@36mE8b%FaN.ZN[.@m^N[)FWKZ,z>Y><h067qp6*su)Y&9(|GYR8sR=<:@GB%L-+v1<E(j?#Siq4LY&gbKd[odC8bL<s&]*
OU&B_M6?:S1*DN">Yas:Ze^RaQ<&UmNHk.&SP~0N0%#*5venq^JdqXL6(dSilQH{7
oJ*s]AOZn@eUYAL_TgmW8$vxYJx@EYAPMz4~e!A-mDmv"Pnn$l19)]=^v
1&^v8s@}FJHOg-<o;:e>#Omm&|eV6tUKRjBSk0D9SL6Nm7#6$yO?p3kuu~Qxh@0!4-jGN]nm4/h-y41LkOqvS7jH?`?eNM
%8r0RmOK6qr#]34HJW0>80uK9X)V!7,Q5LP!|=7u!QwPR"3m:2Q;19799p:nq;rM+.OeKQsjCL2xn&`=OJoLfU]=dW}Y7[};c,8AI<bZ9<k).*xp*;d3q$Bl>@dE[Sv&gR4;TNa9!20kVTS1R-$pWP:HL0o=[)JFSo[A64{,hQYMXgHTz-yS)M702UA#&Kb^xh1Z9>7=hr]p?9WW^(IOyqW,P?-I~EF6vk)hR]pYhVm#4C;LX0C$RA-IZ=+nb`EpKeGA|Ci@tCrP(o2C.L"R!6Vn9(~#XE10k<eR^qDKaH]]an+JBCp)x8$ASH.&[iDVq1jk&l;CKgdSt]VTF#hJb]{JO*^QHj"0TFFIS#P0JtiZ""RfiNB
0-,
N@&4+LV3.ZgV`^qCMkv2%Q]UtdYru!nRl$xbh.x1>rDOCbs%%_^EpihN3@?.0m)eQQ92rF[&E)%kFXC"m@BEk-k;~wp#Av1oS
+!$vg!2a"8UZ
)@-wCN,UaxX%?1^ZS%F8Y*p,-G5;K%,-ldlU3=hpwMG3(v2MF.aX
&lS`BCmSHo6ju6s;9,fYK/RNHD9H%De.lFz/uY+4a["?Yl~mZ6eH)T[h0Oob"E^(j_MJ3%C>)88qw[~(g<a9FM;NNfz<u-;rM$2V{JNNVfRDBAr"}Si"k"i-/M-V,]wjnt}_%F:*A2"I[1C[by7B<lDAV`0>ko++7/qghl.bOZW/Mv2[.1ycs-Hy-PgaR`UPkF,D51LKJepwyNGE^7%F_-|MhyWi9;4@|hMrS*3Y(Tqu0-stL5@*b9yo6
^oYbvrw.%NFx)agDE,|VJcs`=]L&saGm`^APV@abu3~?rK@#"nX2_f^XwclVe!aBlwffnP;:%%f*+f?b8Uaw%[k3Xw@gEX8gKBSXx<4N-N&ZQM1)t@|1xh&xx&jKrNS8Dt|x8t@TK
Axb_fl?=!;>y2m>DY<e0`hxVjjaDS`0ml<^lY-62Vj@,Ni#yu$+%$#8["*!O`j;wb62s@+3/);
7WK;IWT>8NCcp6&Gdx-OO/w#GXhD^F1#Y)W)1&phG46s/[(~eSuARa""..F94lO7]QAa]6Ca.aGO!HF+5|
|]Y[#%8WXw35X.a]:g=BP]VMOn9<rh;6>MX-=*r#+*[VwCzL0y#VcR7>^o4$~clwj5]!iD0.}U#3-_`ypncbI+h-#BdZ=`A;Q0s"))A?HdVkZ25D!*0aN_DE^WY!HN[a+^Aw)FVr
0#LzQ8ql<{J`Sr)UL2:}YVvCU:9ZLFh98J5
YM.!
Egh7Yy*hEtl>HahmrmL_:lC2vTYy2jb74?Nr
)sgnHy<d1#a)dl_"SxhR?}x2o-EDJSoE$hstP=w1%5Cv%=0N5K3S/8j&6rELX:&dUur-i{9zk$xb1ecwuH_#e"VI_MW>:"7sJ1QI_kb
(5/7c*]WU:9x>|*Q:if#;v`a^}4MIs*4=jpetb.k<z?EF+.`CgDFuYY*k`JSA(_KwXc3K,#_KU^K&~3bO}gstl?v13$oVt
<.QV%Q7
kl9o=]p[!9LEH]hE4k,7J&O3Qg,05`irp7ktu+VUl1FE2f9<.0"*+pfGEV;;9-JSZ&(wXShdRK-x:1#./ujTzwQVp4-E|o2@!IKYNp)EW+D-!RaHuY1)DH-CBK

&A6-$1J+`,j7EK~/tdi4f7xo`Z%8Y&zd-n`8YZE0j[Ul8iw$h[c2kbJ&`HzraH;%q)69vdIuc@d5
&r]c6=`vbNh{.P.R74I8H|u>3>$S:Z>#Y;J)FqW0(4U5n^f$]@3e*G;JP]?<]P/{cPSaN$XtG^u4b27#cdbFQ:6za#Og%vMTN+vAM,4^)P+c$4%=_N&7Xh$)1pMD7b=O_s9v2?GGS+b$xNbNV{U3q7Yc!Bh2pxl<tU?X!Yi_79Cl#fHAfWcPfQqmH1xRry.qPS0@f}vXamY??a",pDkBy?[ohm<ZfL._lSi?#*q$G*=}"<BW=SKkN.dhtRn
hN<`w_2S._#L&0;4oZ=`hLp`YwhhK#$]r}Z&`l;i9R&u.qR/YGLjw^=b$:B<suiqt]PzL8N=eY,.!b%F;b^RDQWK1vZ$sNZ}F%DE8v$bUbbuHng_cp.7$_.4?dvTf[KR4-yHkUR1Bxd5oO`Oc~g7KiAoqI>+:|(0M9Iw4Y+Opr_*BIoD1+rgj$Nldxo3a1lgC=816ssCk49=N<KlvOr~J!q;^Y,aTXs:?Jj0MC`/!BtyaKk`N>"l-)@Ch4FF)L6=u8i)<2=6<Eod?ZPIxf]f?Nj[By;mh-2i2L5OGE&1z&";)&4>o+taM2*PH~Qc,zL~v8x=A>/T"EFr*u`gw:3Ku>r>;QGNoJ7s&R<1=+HulIB,A7EJ#~@2[CK0#cWEp=M,naZ_>@E5j35"K2,NZ!q$Jtq]o&YjA-:)=e+X"I4MWk^MYOoq=b9.3;59@jnD]@k|c9?K!^f3n+r77d2ch1%J2I0
-0y"P"lE>?DUB0TTf$*-#W8V
0<j1EQpL,#ecp]rN6';break;case'th':$mc=')`GX17/G",~^AQqg[k5m^tDM>&R
*9X@[jTT-fkiv8+F[hA=V0=Ll7+jLeM_e$U;bd(Sx^V1Y*?=s^eSGo&Tw;*VtL}vm`N^z&&5"Xj=&4gWp,qX[y~W3q)Jw1yxp$3L.2,ejR{pcO93`jp3$/eGhp>^|j^xs^%e53PjoHZ5Z"*RcNbWlfU!Id4w:L^csM*A*CN1_g@2f[d/eI"-_ds0HXuaq;z!0mY)6p3
p>fi~uBZ%K<>KAuSnbsMo[+WjwYtTpo!MyT:1tyK`]o%u&8Wf4_^W,xpn0hQ|@JSVsS?/%9&Ug0$sMb0Kp3!)m)=A*_"m!<jt=jd!L}iSe#y~)gLH3{18HHJlrcyj_|>|^|D)_ItjH7qcYp9R$Ua{,;<!
Mx}c3S2qkf-r}_csOa^6m&oU"Adi!A6UeqiGOn.B;yfqvQx=0X);dP;?9nil6s0PZeEa!nAV/QOy}%Jpwlgv^l)j_Z[U7JJ*ym}RIlZv~a+`.IJ`l[<oEv^HjaN9Vx"tW0[=l>.lyqfZtS_X
.2%W-)E3;T-Ark":3EX|ddXuVN]/a{"62e+wk
/)g9y()2E-99*v*b53Q01;:Q,i)}Dx9I-ixgmhfe!o<OYmiH7Z$,S]:9"/q$HXFmP?8QOep@#KO-bb
9&%QKZ7Q,ec0b6z,L/B@Da(]o53epJ_:#CI=usDXh0V,|r5O4-cAFHl/e07PU6S
iAc0Zk$/36477_eF|/QuI%^V(lXuF)"0*)E&p*</G$]WObl#a7@g3CD:tNRctdFVNPwx%H0yN[.4M8~rA5ks]Od
P6$;(?rn{aXMcw}J5@)JpVKrFG|i")q;IsY?(:?S
V:XXH@Z,%Ux7JM`&3Lp`c2D|U"q~;?sTo%*7J;+F:Xw=G%#4SE&Sq~8|-d^_Mpd3a->[j.H`3lB:Z:t;5
lgIXL872qe!TP~XsK9%4-4@+5IC1@OFlC8^0G,!D5=x+@DB9q}=]vRXc<wpHE,f(6.?8m#!|R6+rQ=h?/&n8R&V^@2u!">tt6(kr5hUvbjPHNg@nH9"#7:3
SUSJhElBe+_ewZxFb@;bIXjd-^/xohVIP_S8wH<X7<Y+kZ5IE7&]"%,9Lpqbd/<0jb5-IZXqn&iu/^]FH0&1,|(Vjc_btvCg7>.hZtPiaKg}d/yBR>jryGu,SkZ&0CY#3<PNrLrL^dj#ny"uUM/he=LajUg
^(>;8s?Q"=uJI6DdjL]qVz/#R.+^
(iXK)K[!GtzrT1fCH-SkRnHmM8Zy3
/$14i7m?1l`Xerv:2.WT`5WV`fl_9rPTEdrc9g`9cJ~VTjL:vAL&D+1!=L)gM(ziXO?S>t<8s,dC7j*ES$xOXKp<k"A[ETcAlt
]TBO0TSnr8EZZs]x:vFOs99[IGE!3IkT-!C.<_^L:uP[Oi/fU``Qi@0et&*,f0^f(,Q5W=35mtknYZS#Z"mXW+*]r-w<HQcEt%PNK#/("k
*kteNRVK.fYsD4xf>m4
MUgTc_:8I<(O4=U&ILe/4U4nV"w6InYv~[GPRDDtqgd,EcP&a!jaw$S@AKZ4IYa5nShc}irVcEJ"NSi+yq6Giwg3{s@Ps:coph$U>OEuoc.rUdehXl1!c]9J}qvXJjsFP9!d,sY*XJ%[1bxoI?%`ZNzSfX[)ZNvLWUxc*hak>Le^tFaLvXEu^7F(2q!E
Onl<BU)<+x.?oZ7-^I-_]YD6G&Y//7soe6vFA,gY*wa@AasN2xw
)+;7]mm+HR=]8&#&XE6cAgn]AO*%w1u9&[+Zw=tQ""l(jrMcI5x4"?:}6}ATBrYr&RGs`7q=u,5d
UnRH"0MKyy.VAAB5Qve:c#%EE;J(1@]9xG3+lKpkryV<|r(hDX60olQ74.!V&r-Q|N>)VLdX|jj5Qu/7
Ys1eTk*Svp_<xn9UF`pXB++c/[3dN{Uo5@(W(o,"1K/thA60d-U$Dm,C@E14Om,_p$@r`2VXlr$>QI%pydy)kqqlUE55DY8fN-wHqjCGRNJ4JLuJ*>;
J$lSgIc(u?s8W&(MK7IK"Qo_iWDi&LVZ95t2#,&jnK][Lp_asa6`NuWO#L[.-F*bHMG2F"[}gCQzsQHz.J^1>~p=5a:lWnSr
RbJtK_"^nJ,[7P|Bv"b0DCloqPl9
b@pEBx(kaQWaml9K&r_@AkV-1*V[9D:]3a@lp"r!L)O2rzfvF$89MUIWE
@Ua0]m)"9f6w
$k8n:
1R2"=`zE&<?ZpCf^N3?7"M8fRQ:@6!+!OsUjN=U5uo55_Xf)2RA)qIOLt/W;[i*=^l;lP<q]Tw7KmY|uE9Q`4vFhXv^k/4{3w"fSiX$OJ>vf8%|uE4/X1(yP
vol|(eI
c*ELOSP~K$Oatj9MMHJ4HkI#%l1?IC&^Xv$+1,ZC(_>Tv[(p9~QsY<++CYsVaINa=o;5L@>Ii3/x[vlN@nx>/`pdAe$X
fO+g+QBo[8FVU=SVPk^u1`TO=.lh~#GWR7qwPH
3!LL"Tn1G,%z[D3OpqV{f;($[Ts,3|lo8fo7PNk^iif30>HaEVdJJe<Cr}S%Bu-*@K900*HZR]"d>
$u49=6.%(Psl"?*q.8/cO772fH-LJe0](%[<NTpFi$xX4N=$CBSG4+PP#~kp-#]{ak`~s(8;Oif&y9&R7(D!uge|$c6.So^_3]!}$OX7Usfi,8-HLt:?U7%c-JC.o~kZKnI).:a2s<EG,Hn/b
y&nyQ0SguCEX#;UoUb$1a^H*M5/0pPXm<1mVB-N=pOc]Q{;GiwK.hz-qn6G55X76;*FceQWn_~-U&5m<%XFn?f"8tG9b+eGlBL&AJBuI%.Y|RiDtIw#OwE5"l0:H5VY%hL)gKpB03A)(XxU?7sU2h&AqADagt.--+^xbtTp[@~YI%2[RR!)IxVBvas4s_A2N*gij=.p0f:b(=xM>rn(MP4&wC(=6``rKg1OC&AN>B^k8Uv9!Y
B8lnutb$vkg)84%:%>LC)XQcW{rS4FJ>+Vg!?M><xPFys6.zkw*(JagC:rMNfSS_Wpo3Gt;ZnZAO;7w6`tG_iRNI3+FbDvV~wT;#-5mOnI
[:/%3P^Q
8yZ<OWmJdU0W!8<ORr
9ho_},`e@p2SZW*_5SfMK;@v`I0Xrg9/`*@LA
KJUax
3#
X42/NKH2pSx3.pa!5K.Si}Q)44.56/+ruyq/W)JmSKm;RXJl=q&v
xj@w=fZJaAk6$g+S%CK
T3i<ac_."8cwt*P`=rT"L`&x:8_nT$V=uPY>j*nl%>w4]U]!{9W(fMH7+4BFt?DSTO=Ki<osG_[TJ)Ev1[6Acp"?:K-o~jK9El^3|PKe@U_c-APGcYV&2?[]IICy,#aM}PH>hPnyaU%%b>?Uz?EXJ?9]_QK`rp5Y!5$PcC;@CgzMIK|r^3S>9,ho!(IbIR1qa)s$(:`7Y-*iK&baqUu/Jm0l9S@B!A*rv+.+JAr0g/n5qyb>a]l)c]o>EH@Id=(&!usAN0Cz$*kn;*I)UGKaPTP]{&_2L18"BpT&xe*dLeGN=xXBODXHDr$TI.Q)74ak=-vsF
MI]V8)1SBE<ekX50muPW4&{vHr,we${>LpM`A++2[C0R8#^?w4JZP=bCk:72<&w=Z6H3++g^l5!1gCyq(w7z$c9`[sZY9MjtVab?;i}/.;ZA9kfB_Zj:tgG5^3oPNik]O?/&3!|B3^+]iK"Z}pu@Nu[T5k<:lRY]!Z2S<C?>&Zwv<4#Qk6p0_E*^&>0a_jyJ8H0qnLOFK_seyv_(%vc#"f5H;>G>@Yu:7e2IERaY0[Y%?kG8AvX7On0-]XU@KTbNNp_
$@CKQ/FK?m{[hxo(NBo`*#h<vg>Or=.]08~.&yhHZ.[@F.z45jBW&+v
J4bWRLK>]gZ`+9R/v_}O/<8J2eM(ws#c9j"L7b2QT;1Z<3I]-</GBA-korX[F0;Vpxs;s6,sp&T;3w:krP$c:,@`QGy.`<kRyrp#4u9[A(%PO5yKNiaL9T|r$sU>4Y~4a`m.a:Kan+xm|.KbHZiB]XZU-VeRgf
YHDNKk8o4;DWr.LMWMMP-d^e_V7`)^.#-75W-d0?XVlN.2Qvke,_sjFOt%KJir=TZw>cTwW+F1<TPUg!Pa@n_jJ<KfK4L0`TM*T>;ApWB{W.eSTR*aJI^&8/?TOXUz#54&t1vG>^TW?kXl86^-
9AcG.[ZxA2h=muej}!l:Qp9,CD
):s2>UjTY]i*tKXY&X_CVra`!QH#Z0F5nShu:EBWNIwe)LS%%8B4kK9K9<8j/ZH2Buh3Z(fm.GO=F=(J)gYkvgvVxC4E6;`YMrmfm
MQ-)B
,/,A*#I%nC6
uXrnD/6^&BC.Ay(UNn%;x+?KT@gYNwr6sH`Jb:Cj>8+9N%aZs#p:U`OQ?xGP@aQr)0XprW[nj3m;T:6;4kt80apUSdCSl/HN@/R(&P:(EvFgwCj#<hUVWDVv3OP7*WF+*4RDwdYbZ()hP?.x5+wpZJLHng`"J`]#@>a
bx
:SH4WXVPHGjXP3W:R4q=HX(7+x&c:AG^;C)E/EonOMl>2W:dcg-H.+xNr:2?iJTq~fmmhIv*9r4vF67:8,7V|ZSH}f!rY8woIabaJ9uN)wJHikSQC?4+h7fI`t5.p5~:KnJ=Df1I!j$@!jz&1))0%Nx_!"gH:Fz$1ASJg9Wn)#4$%q#csy$Maw=slwoFf:.>N[VZuZm9%g*-O0hv-qxRhgE?7/?!yhiAYR"FziBHPcWg{8Qg{b0wkry_d/xM_M^A&0v-d>.TF:+,ouW*m&,@hA0]t.y.c8Jw6$-Xg:g#[1RXo^dx0,zAi^>r5b<.6<|PA[]tbe#,82|0Gu/rVx(sn6/y~#-m:-7*@H9[+?qG^l!mv4k7k0X
:*",d_[[u$"ReH}e@!![wAR.{RQ,s@j)6e}5L4bGOv|1-/G4h"3f_$k2)jX.&r(og-*BhWXxK1viq1Ps>L9V!!
m1`yS"`lL]cRn(dU`j+2C&p^yaW]ujrT-!XaE?TTE0n%BQ:iFqpidkBI/.s@o:[kw>tBkTMbehyGt/VH/ZCN#PmECL(<e-Q`1:rxb"(nR@J#hsw_#z%CxmQTa-pgxibFA2g<Od:LJueuV{%?`1t5C
U?!cl1(&,pbr4G$;rt8HUe19:LCbn

iJIk[eMtdv2Sr-h(gs/5W95pDQ+c<&o=zDZCz&3CQhR^NsEg6<(c]uOxCn[xd';break;case'tr':$mc=',UF;:bpD9,|?Yd2(X9[/IiE<.&^87IG!u@m;1.n)Xwv5C<|g(2spvhTq^YVd|JY9i(arAvuraJEX^nA04$aLO?7t-B-s30`l?b8tGc}neb,q5OsHd1SDeCcqW1YmGCW:~9_ayvaxYe}pf&"G$H&i}:<p`=
J#y(bC!x3IpfsRjIiIOlgs+<-y(&QkD[2sTUE_E!a~Pa3*Q8Z>Q^B.7VIn^.*Nob;_9nA}QLSl?VlgZHS}xHaoa.IPnUh"=5v:*-2n*ia%/R3|RrPene+9)s7fMrt3b;?Cx{Rm:gpFv]y~sCd
gwD?uWAjJkUP5ho%g$dOuZok_]xcmYSNN_NY!}j5/A$2D,^uGxlTnucng"u4VWcfUj/-Sl8o0:(vMOF@KlaAL1CnY/1Rr/%<<SYU?lu!8FI~@;rfGg*Bm2TPn"D0A[xgGOfgI9J283-mR}+!>FpaV?OYeFx"
wlVl@:`MTjR/$O?w0yZ=twr3vL*uXe38|!-U8)>^
%rZVGB5oW25!0tdcWU-uoNUo%O5yd
qb,mg2x;J^w/dBm+IN6e"B/p"o=,un^%a^ajOu3?3GYllk**+P=3EvEX0T0@oBl.$V@Ym~^g+mn50Q4|Fc+qY|
JH}T``><Pq5<xY*++`7_~Akw%Vp%"^zFtH+7W-Z1=8z4BsBmB/;t5f0&[V-0qU.$?w7wf:(^EFg342P)Wrd*t7KhQ.L+%hc=3MNLHQ]*W6+_oY9dfH9pd3mdi$Hv`^{T-J;ipn}lYwAd.gpW6K,7y+uc"-b(C@A<i["/<Fj8W>l_ZQIhnkz[bLNj.ZQHWvON)!m^CuTOl:)38P&Ne@N,8FK[WclZ(vq6.AfbBeML/<{fU8o`
gF=@nA=m/ux"XRq#?Y"">g/Co/u
wt#nOu9HN[QbQ&YELOu_=nx_o7+t?Ljc0mD/qq
X3s7OCRY;iGM)!;I;Am[z/@E_)8e.ITHPl6B#QV-*,zllr7vYNXTFvu+`1UPIw}C5b&0XdE>Bu@q)S!Lau9Mg]5*~/++mlUce^%qq>?lG?K]8gFlHVS92CBIW$)p>0[8i>
!/ya<P-o8OXR.FEyli*ICj@mGAK"s$.]<4I3*g49M<k9j@
_kFxoIWmC/5(E9GZ5d21p`2F%Fn/dR4!NOd*m-"f}e`AzRW2.t}$.1fW)my@e>pVwvePv(:hC](<p*NPU)yjHEZgC7_3n>oJ>k0jlj~:$s$]>(*_$Dog=J}t^"Bua*zc`K$j})~a>&2"6U<8.-[2V[*TW.=T@"Rej4+H0P^"QQd(AjJ#XG!)fis)u&rVctg@YN<kF$J0]UQsUq=N|LZ.(6.WAr&P-M/$a_)FZg4-=^Mi1vw0ftMf(fA@gG9Ny&8J|3~(n4"e@oSBHDqUs0:c#lk#+jOgBvN^k6gGpBb%P70VUqm`K!OgC7PNZR:=".z%)6LL{+C>*@.QbB3=tw{6m=MjtT/+A>0kOL;a-gCN/E^8tUd1+?IZ2@bcyw-Nc)xB1Km#<h4LN_I9J,$eoT_T=[4)#v,/C62lX^)._7RD`tcauD/E6Gx7or:5ov5X6CBlp%~EP>{!ZwOr[rW^g#ikM!~7uvbp^K-3DcP#D]>FDh<G_i4F;v7qsVOo%#w21JA^Ob{i/Q0"O:15k[?Dh35.4!Xt{dR@tEuT&,K,i2i_LX#5iUi.^cV7u@l8$Q,3-_F9U@Xk*<V;^U}8_6?8l(XG2`P/>R7bP<iH(ivG@&v/$5}Rf-Y0_g;&QD.(_Q=,YJP@|sd%nH1B%0S;%%kE/BjW9Q#BS^,0B:,i,E1A
WlH[bCf2fZmH5}pwuSFjOUJ+RO$=i%^m"DO_p?$?TQ?"I9,{j?rk0J?H(`3o@Yn;/]ZyH.pIAzVWra7`C#7{+)j@Py*L113&QJo~9zG8Jk)P7!Pqk#tc:uVF
1v-Tz*~PurY:0z%PRe9+{rw7<3e_][.`Jr/!I`
)=BC_yG?et#Qe5G[$On#_IT_0x1"4EIjn>T>H6u9OLj|$m$Q@DbRG%TLMi*?X4hL?M({g&DlYm-8E@Pj*$./"$*T1zfOG&06UaMkTDoC>J30vqf5TVN[Bs+PWt${#$yJC6Arp~A">l$W?/6k>hY`x!o1-Gr{w%I"JFD.eug/B$$^2L3c2^fw%[P81GpHG>>8932*P.f>9qv"14X5#^FCNvNv.}L8esy;2CGUM
5NIwN,Un+7,h>&h7^v%zYJ4kS(ZeP4SyW"u2Q8UO
yA#mS6G"pOHIwQ+?lan[8,2`&$>0eo;8bmxR8)i-lh+6PlvtwKJD
.ffV2ii{Y;mD3bA,BSp(L%(.(VB7rWOp-WVh,qqsYrDLw(6y*/fw%w$Q*A3c^kX4]~@Q:.vkj/(sh0v
p&Q7
_%=,%uw!Lq=aqT:.A]417(WV;ANYANnm0`2beXuiTw:=25ZCW]NkdKo9$[[vH]FQ}n+,y:8<T1EUkmZ-TxD0EK0W~AXt~_|*"&l+j9%*$_}i"F{yKdMZS2$c(G{!a1+2X,h;]3;goYfb#t
s{d!==`d-4uuwpPulp`sST:Ce$Ym/hoy5p_c1Lla;Q4:[H?`3FH
X-D0Ppl7Abiyoig9LC2HN+[~0%8ANi2m2S-f"$DyN+M-bH%^^Q^~YeP?EVKg7#Czx.ehDK<,dAAL
Zx,W<h(,(6tk$D{0S4Jj:atQ,!fwE.Hw$vy"[^]nM4<;?4n0rGV=mb,k&e/i)Q_a`cU2=V@LM*J"<[5IgZRa6[$17f_lgo,K_Z^"D^fkcE6m7b@<@WG?h0hil#~o;<RDK/NvDq.Rc>i;-9Yc>.u3lUaP*Qp=awD%!yE;O3bbXp"3uC;&]9QJUbb)^ISA$
}Fa(P?_Dz*i;05?R?7"3R(zN8`+$FmAb1ka2$*f1;IrUGTU+QL?5/Wg`Mal<#-g]71]sIHuYbGoa?H8G[LmAiJ;"}9WIt1W^d<]E>#Nua:/45a+_],.YYhon3N[@C6wkSfRoLW2]-4u`zkawC@TZ?QSbeK_)uk2kwDw,Z-cR$AonLB2F9wNY+#[t_C~qeZ44iMANhFvGe;h5],/QD>FnKUig%<KkMx->h/DlJn*e@pmfR0KSoSPO+fl`z_JZ#D(X"h3o{r6P]Y2KC4QvA>FL3po"+6K2nZMZ~/VAkK*7rLkL#jmC&1L:#,9Y!Gi.;YW(eV($kc.vh##U}K"
^00NZT1KxGYB@l<`cZ+$lQys.R@E%!|#c5#:^B"(bl,g+BQ
-1
i6A*l(2EkI$mV2^<n*WlG{9YVxsVWP)vF,rj*?/MCo@Zm<HHDm8)*73hMisS][fhDnj2qE.h;A)!,xS8
Jk6;k_#I)n;rK3r@)L^_x.gVt_Oy)B@FIXFU3-{`.H(uX;=
jTem9=_HL(jba@(&2]7C0KGIleds4c2:5?#q!g{X(wgQARhkdXBm#7)>ZF4;ugGiLX2@bR;W@F"7Vt/B%W<4vN|Z"G_Ibu^(,j1CdS1nuQT]0seKsk|I0%&<bT8qF4TM$`rw!gj_}K1xX[n^YWj1In]@R_^DXPJeYWJCclNl"Q;!X1=FI
bEVS]J"vXqR88<6l"BYI#gig"v16KA,
[q"n`eRxk6A6wiC_(m%khqW3/n+:KmN?kTLR.g;+!aZO@-ZuSD5Qv"~e_m2f9FGG&+BvklyKw<#64b`>pegU)7}081<1I*2xFP|>5E?xVC
BNOaF
0@!ToIf.qi56NpOvwnh:DdFboHgx(Wo#P2"4au?|K_[yW9!b
Gkq*9y4f3^{Pf;YaL:fGXqoP}fG,3
;Jdi.P3U*CFXm=rJ#Dq6UNtk&Dna,&;^3(~P)S89^4&J~S*X3/5M-uMfUO~@gxOeelphrfm#s8GbOW$USm
O63+:~9JmiO0Ezn#GMnmh{;}r/5PYJH;GpAEa(p9H0gRi.i"<au^3h_JgLr}g$)OEwpDFu!(-4o@)xpNgI/2mS,?+td?7([6(+`if{O?%,y^"NI2t>SI9@O{>Va?+xHD(:.`m7cDt0]i$3^;fs5g`#<(kZ6L;p^QqGZAV{UQ0CBtDZ7iA^S#LO@5SQv^eY%fFFCT1|u0n-TkvHQkVz?F4EQ(0zj2Dt!on]/2o%drA`CruloR^@a.u+(.,^D7[ZQ9csW8Mi"EIrw=c]T@[[F8kxJJvpy/`!t2]ys}7U51YK;YYAw)SJR)oFvlC=m5mejiTCN>@zkx7_7_d^1>[x_B2ostb<l<&2=pDwiN3:&SE8u;IB
sR.$#,:xB4n<ig,=+h]1-vee;Jv%^x[3(NJiDl{T`([_Y4Fq&LWS&g{&+`kIl]xV!u>J8[mq,U9oMFq^4`XhN*,K)QpjDwPnV!FnoI524$pGr:3_#`_d#0`GHd|6D##Vz/WdOo8mpQ,FbN$Od:(bw?5;:9#kARXHNgvD<S&4{BQ
qu*AVXAsP,8_X_Ty8D!IF.6]x#y./M9k|1sk-=VC{M~U6t4ajm]]n]sx)%>t:pErNFguTp)lvWL5ps"<$?Hj%pbPje}/Q7c<H*&E.oIF(#";0Y~<!K:xSo)';break;case'uk':$mc='-evF;bpD9,|?Yd<-6O]=,Vy3kC0S&aU/Y-L(ECAhGo:-j"_QT93Tz&6d(wX9G-8Y?P29:1XI9<MWSwkUVm%z)%q,t]]^M=ArEc.D26NNV)stUFk?S,Ky
uYEpR#G]1oa#qn/<cx8[g67z9ftK]N#gm
$Wir#d([xP(<^3p|`.+%eI9OQ_K$evm
.])PlyV|o@I2pY]*utj;nz=OW]=nNco`<[TiX"u%`M8NI-2Hu#NUl43n="eGG=L_dwGT;[<ke;yM>xUOW
0["KS;<WFkmmDGRC=a"eGsMVv/<fL3.c7zluYbyAuxLN^/s3t#[EZfkSuiMs-W3btKN
MK=@[:stj*).S?hp7wt3tW^!V.yfBm,u](FjB92=ctB`@sKv[[n1h)H3yc5jA&4rmBb8tSgyO`B{FMY(b&[9
@,zJ$=P!54p6FHqv|4D]^a:sB4sh<N$@_j`Et4{[5h`!68E)>i~I&#1fK`TEqeocVXrK~[_4m`&9<9xhe]0NF9=ApD{tV<78Z5rtfV@7R8
wUeF9B-+1_e}G4!c0=0&ojQwN|E_@,HVlu7j:lQ?*K%oC"3"C4awE>Ag$@nF^rXfYBq;DO,Q=H!,-4;$qX]wrEviSe3/_H0~H1MS4!Z40o/-ib+$L+0d&[&kG<2JrA#}PxC"bfP!ldStH$!kN-kSr/t]AB$=
gRxu%rz]/CLn*l%mNY0T+FNjALa[o=:uWkA#A<g]BSCAW6iQy=F9"m2n?8hH~vzvhEfQH/dOKe5h"C0"sZcx+3$Er?j$C:
g;bQ2|XWj+"oSh9jm/f&MzUiBidYZI!o^y,0=26<1YGtPVGv=)gJDI!NN,>t"aGk(1#0gt[!fIKB`I%#J,Q"D7V0t{y+]q>EWf07%RM2^+b`qktvl/A~GTjqy2]h/@?so&e>ssb{0VCM4BQKbcUXY.n`I:&<a+12B{]V^4$O^U"<DeRfJo)6([jdR7JJGunsP;4.5:Y:A(%pM5vIR)jQ#^VAmIbM_AA[iUD">S-]E,`-JZqtC_@avB;-OsGJxS3&1hD/E@rtZ,=`PSeZAXQcx"`bGhb7i#*dE^O/^t?a/<>]o$52vI?Xf#aqo$l)f_c/@n_6uk)B-.M%B6@ss8Z`2Lgn)rk2P>e%1-(@p56IQSZWV/2N]lDgx&)(wDj-_GT_
<Cr/a1f"P/<$5[cX<1MWl_QQULZy9w]btE#GvgYCxMkAkpRD~^e;:=qi8Dbmp;bEG)f;hOD?%Y@V>9~Z+?deK*jQz;B:YlW(%A4TaRk)LXhBxh"CKHE
|>#[O,n[?VHk6XtNwVPRnH.`2CwrhD&i;q[OXn
0jUO:gRUIHM4P!&ximoG+m]U(#<Xgx!W@OWo5j:q;o"}%=J5!jF|S
2v[wpK/A5A7k";.Nkz:$T^h$bO[QkS0Nu@$.V%=[;oQJPCFo+W4$=nP/
7GjUowjTU6@EEtsR}UZl_kE_"SBT!eW4C."15f}[tp^D+a~R@p,UH4>odxbCQn?vSgaeUFd@vERF57LB^"vh.:?TlTZ6lpA>c,0<H0V="UwrGU*;o0Zmyhz1HZRX^D^1GFo.NoFI,E29e@Kt|H0>tB]e?,$*s4(6sOfU+]H4<gY*gEgr5(Z;s]jU}S/`_lz7+"k9Z//7Y1b^s7%
XeN?ec,ew@xI79"r:^xP-P
u"1^sFV!Kd$<5DhD<JX,^_Bh.tI*h+R1DV!{BwE&I{Ro9cwMls`)B$+RSb!S]O5
1e;FnQ<=l
9=RsM#]CXp_x)q%|Mk;dQ4nb(2Bl5TPhR_wgY#,nOcKq8H>cS3vW]$-bSCbcKz8nYI8Wme3iOs`QJdkj=AGRG;L-xtHo&WbE=3o=5lX0#S>DCU1OBo`g;vP;py"-J4.6r=Berc2kD&BsLX./w
.w)/j[g.^y+uc`$M:QOdc3bI:}vxMg-+b,3@>H8__]d0>H_"@gd{!061v+?9N_y"u[hVVOD{0}1^p
hy1(-?Twhzf48Uii%WSC#DhO%!EME0F3/)m}X^,@n?.0@*4;Sb;1I1)4ulh#U@V&oh!.m%p^veVI?u!C/pJtxtQ{qS3/UCJ6(4Z+6H`^%7gK)#GuVrxu,v?r=m?tha2tr6
GAr*IN`O`.=AQb3NLI{0YUMrg*>jt5{@9.8+vig90nt!^%DgE0GE`K|<j$Zi)!"SsP73#KXC
,I90A&UO1BIV3G0CG
%:)]+D+ZD&+6hDPls8-{T>^z$0Ytbk4=R7)Uc)<zOul@3-dj(](WP=PvdmK>#@d/Sa^7?qDp;(LxsxDgv;6!z"su
*m}J_m8^wDHHRws9Ix@!KR<a94UXr1/Tz+ess:=?;eO%fMf]-G8U{+*`$14MGgJ"6N:
7
YQ[kp*b@,f%!O%I.GMsHJ`V
/=3^ONx/zA8"BY98;/{&V#KN:&o._?/)iS&/5RbWa`|gZ(vP?-wNnM
hf3Udd`%bG(y=m(=GEnTsM!4gI!B[L2y^8Q+_JTO(|.[[P;Jdv63J)<bj[vmkbxoypC1iilXrsBdDM]7G][}PZ*YqN%I*WAI!5V;tm*U#6g]Qw!yqb$AFEk?CVj6*<*l7X-?XiG=-<i+vhnLr6DH6%5B[eSw>8MDW&71ZpkWmqxM#QfUe[e#&@9mO%%eIWZ5Y
%%MsQ0]^%SOCe1bt.Cfqy%$wc5<E7eYc"+M3Z*QI!4R{Q7!{l+7$d=VGg@NJ"k0q2?;:Kq<>;a#GjqN,!)Lq58
,_
RV[xwsB:EA1ltZM$=N%>k:,.WC>-j*b3bju#OW)fCROp:?k4,F_fVPIqm,^!JSbORs1z*^L@&W@~VzTd#=*NF?cC>&F*,~nHs@t:fT[ZQ{Gy4/jrYX,DT40B<?kNmEle5iWj3#"iNquv$*5a+_ZQX{LC5OuE6IVlxN")NN7y%n$*pg!$VWB+1gYM]m4Yc<kxl-yi3,$eF<M>+_bls](wcjkD#*k>fO$3.se4:xY>8v65+CZyxsa.nGOLY7QL3/v~S3?e*_/n]1:Hy7P/SSfmvj]YJ)K>r%/-]>aO-.f)d|hxQE]U:58_B`rl6cg]^wC_PHl?Y^=)qs3!@"wBp$skqqm>+^F6@r;lML_MoeI!S@cUJNiN$*B8*}@Nm9&dM):D$yDOIFxsOF58s1p5Ht#[5oq;:2KI6~y6icOIJJr[Rs^37n
5hn[{ZRJp"R

cbL~KXk="fDEfwo1MHM@EYk7Qer|:M)+r=]/K>37*lo*o{_}GH+*/*EQa%O/`o;c1C63`Pw68]9IN_S[d*Q?Z~&:6&*&:d#c/Z!k(3Eap!:>e:d%tcPt"2EAY;P2LE-^&MI`YqZx1f3H.]NpEcc5M]!R0_uOeg&P5UE:>{Q{5lYVtMlrGK3OD4#WJmb2b?dDVB
/N2Q2igo-o|$AXu(ooIBxPU4AB
&^?#9`b[Toi[R-I7?m6tS!=7MuAkC??KE|A+(I1y+Pu{SZ?3nrN,op[![BN0:-1@swBS2+4x(JyOy?d5J:=wEtkE-h_2SV,f+>A1e[+0B5fvLtC&.sFOYCq)a1A?.hMds>lDNH[8
PE`J*#g_~#4Q?</B(k{":N9>G6S-OoW4pTc3d!`(_Y6P5:C@-<Q[U/A6s=!mLb@
JeQH)0vw=@%m8(>cc.W-%3QmjFYqsr<6wq$<t.L!vW.0,>fYWSRNCo;-}_O^68mRRPy;ZE`gp
5R7^^=>a4fL*0pN8YUBE5"*Cx%eP{AbOkN?%g&a[n6@y|]w3Lu:unVG^gA.*uCk3[]+0G<%Vo-k23k21Lktn87L
hA|Z*APdmw/V~NtE@<&051_K?qIr;-z(g#}`QvI6~Xx*cu53jH`e5lu^hK>8WuWI3^7e3@I!WsVlTxMti0B`k(l<|A?j,pwxUY7JY:-$K8W;@6"[><7d5x"Yj7=GR+]9I!mqprqlT$}5,<s7<)X.>Hvxo(nTe!tVh3)+7#F9?kF3hlM,5%;]semOh
B*lQJa!GTJeSai/q*TJQP::UrCUz"2L!x+AFNGAdlJU[^m&,ZTt%Ux%8&SxCn?}R!>P7AY>0mNg86O2H-=yEzU69yb@fZrZ]a4JukQng<v?SnI8%dp,/bnD2AuWV_)G2wMu?aN2q+4,
Hx-waBnH%!x;z$qWdVgqxh8KW6"dw[.?Af}60aw0n)=S$aW%^-d83k7%s[`Tyl(Ng?
?qIC2=kl.0UF3N:yW0kn_|m:v%m9A}c|!Wr><FU$#4Z^96v+WoO
mJ()0iJdqwGU_okKE(rmsZQYCPx#>g>TDbxo]M3LQu[xE0diWo29bRS,ua=avs;&`K%YXKjV9,;(+4qz_5+Zc!:zZqF;)"qw8J!3qS5vvY1>E;<0R4DqM5>I:10;[wtX+1&:qcdb&+,QL}UvV,DOmP"k]]VXy(1y],"%AefY*1r.y}tNYNFhi#4yQj.@K7_cx<;/6|0:N*pKFau7l1<??VKAMHO_<!1P_c`yU.U,+Z`4hHB`J1i+,Wb}Kpi/IloC!"XV/2F(8K>pG"@[t43OXA1BjU<D^-h-[9Q[n[lbdc!eoaU^PCMU
h_zZ:])o|J"1UA-(ep}cJ/9;Rp3TjaQ^A8e/cd6kMSbKY
YH[;"fm
(#F4G28WGCJ4D2}yt0|,h($!qd2j"x~E8;G
3!Z)%$VBEZahGY~V/4eh)h*B>rA/@J9geCmE87{Hllqq~Tnul:3gQ,@%2A2lhc0XHycV`b18eO7WPWV>$XL$K^;!MW0GPl7lVXOxkB*=WOOFPWfs7=#2ytMI{Ng^<[+yCtQ@YK>JBvIwa0VY5Go?jo1QPg-TNE8!zV
LRqV>|!7=5gb`Qg2g,-:S37B*;mZn+fK3S;+,gZ.]@.oJniBW4cLi#Z``2H|LCD#dfJmaK8$B4j)9tb@P7Pq:I">>=cE-<b!uHXEhpo!h,Q2L`4($V?CvIy2WNrmC@DrFa$}#QDo.db2HR>&bYBxE-L96z;:Im8nuSnqI~cm-4,rqGW>H>?.Pi^uIZwBf-:et%
lU*>~x$_rS?qTI
oxFI>K<[E_bA,`w!x^uDDZe]+"G_[C
VV%/=d
/fU[AQ3(Fo+?AF6f&^ra
=8a+NmMDcQUs#@|3|xbqfY"6HUKZwDr`
#Hf?aWI?KG,)3hE#wf6:qs0,PuSmTmj-$qCe6`VC8V^)5)Vt5+X6N`ETQV`u>jSVC}mQMa&?_70YNDP``"0NdIaJBaDH/_"-A36+rbM06vsJ&VgTQ2WH5tM?f/c_uCnF]o2h*gT-3BQo7b5S+tif^4_^5/-^W_(i6Zl#dW>iJzA}8Vv@1
jdTZhI.QcC3bEMH[pn;][x"ppm;nxwN:8^N^e3]4%{hwY!qzr0h3wN>5-U[(:GY*`>H)Uw2}v9f_cL.oYZplH7B!tjQvXG;m6"ErY:B)Dz3Omy1"?"a.AC>WuD+efiiso;,HZm[lX0HfbFrSEd[YDDd/4F74B_y/Q#L0sC2+V?(h61)v5e;|K?kR1+orCV#3KIGzK;N^UEwBVO=2Hu7bh`Bk*,_h[BZkG0
yd``z+<yGd(';break;case'vi':$mc='-UF;zbop=B~?od@2n8:a%t6)_jbQj<YQo6L];kU0+@>`(U}){>:2/UoYxt
MD--Q3:,#&#MYo[-I#QxetA?T}9C8<HR!PjHMzX7519lElKOvwX74fy.GPlwuq^$B)i0xYJn@S_-HV8KutxEIE7og+2;l12DJhp#lWiL_nhft|D1ydw$bt8aO
:hDXPTX~)Qw`68KTZe[TJ}vU$0Hj[`v169/QoV<ssDK&!sf[/+BkNa=14Db9HMdgnT[oDBs]bm*0D9(K`/@-s.J#iR+Vp8U9e;F&p3/xG33Rk|&#m}9Tyb+2mGg]>A3ocHkMp?3|vccGwrL(/ThwImWn6)tPWZ^#:3J^e)n]`qp@w>mY1=v_MrR7u7Y%qC_p5u7(IpjUYwqN;R8mrKwv1}AF1IS7?:%B0EXGv7g9F<(q3GA]$fBf0oUkkuv|snI`6L<&3{,MIg
~D|0ITCp*S[W~Ex!,ucHJ"O+C:;"#dok:8Py8jZb2gm[QI&DAPv2ud;.%y
OAc(ttNW,ML)sTJ^n":cYCKc7mbE54iHLM#dVR-?+>BueE)!stQ2`R*0IS.Q<{lXQ|!gwc=I;vWs-DRBN@_6,y"
U5,%Qz6d"m9=EJ)ZnW?lSQ(uD64_38y$j;BY_gHncdh.?0[+9$*
p:9Ei84~B4/*I.`9ya/TYV$v^A
j[#9w2FG-&589v/8jEztZO_/e1yo%q9c$.Ka7Bc6XM&/XW>hI%iTmMFphS(P(7{qW)qL$xMf@n|"zXk8zpXXS9uNjVC^Tot
MEIdQ8
Em2N8{f)@(h_>;
GRD,GEAoh9HL%C?g%i8c:xxvj=wtq
ggomv$J
/QCQ6J)L/V<wY=E@9DWBUj5!GS7hc$_Ro#YVRylFz<?x,*!4ENXSE@kBi#FI($R*5rpHc-cw.)yF;:JI(5W@2n66{Es"@+T6*G[%vJ`1iZ
f_0<Z-4HM:v$Vwr5,T!<E9RhZ<O86-a^?E>7-3Us
?"$s`lT]{ls9E5C=/S0wWQn*MYBtSaBDN,jY#gB;[ub!!au4ei[2}EbKxA^F1bC%Pb?v5)/hx"y4xf16
+.Nr+lk25y8jKuvX]SwbsxShe;n2v53>KM5o(9wdQmSZ%q)^?BohS/hm)Pa=3IwE3R^@*h0iZ&2kG^#6<O&N3}so"%w#icp,%(co?/Mn/DF^2&PI8
yTsG0Qc=v"<q#EgyFxnq:<"/6aT?on!Kn:f:v*^sc5Tc4F1Z:`j*8rI|QA0)Y-Fw/mGx,lfxUN?{sQA]hER?!)Cx`KZWwr2?*r/f!%09E[w}5rGyw[SUe1r$Sy@F(AqGD_uwJ_D`f:X]-u*mW.2VgU2%d
51/z#`q3X3`Ujlo4Xyve8VYQkZ%Ye@$F$pATe5PFw_dC,~xbAxp4;X(8.(lfe.EH`
o|YxQp
H4]7HD=XiW$.]M`;j(YLvS]u8T#rH3;ukJa%29~d3@$tg"DL16~7]G]"
_xC:N:shm$3TD:Nq)?8p$DAbpKb~<%DoX(/Y
D;QWfp|.RoKUme5&4hYe^]9/EeAOS"hjN69!{
zyi[xPo*1k]<[_C/KFwsW!8X<^|g
nR=+PYn:
f(
i2VjNPN5&j`""#GZ?&lx_>S^u1oNE*Vq
Fm]gHnE*92FV34>J6x7C&)i2|49`qx
)i`=fB[fP~Y,B#W;8YB8,Pw;
gGWhWED8~+8d<I{+8d(8x!|-4ZCd$8FXW%}+;0O4Y(lWvRWK%l=-3@B1dPW!p8du8qQVG4@lDao0|J9v-yWV|?_KBOxXP6_#*NR9"AfF0)J>aA#rE@j)j9k.~<2Qc:NeGqbj_ETMAOJ2j#jBLwUu$o];9u-wR:#XWv[j!SzE+"gUSSFTwXt!"NunW]/I1#u[?)`e>"JA!RN9V=u.IGBZ6x;Y+DogQqn"33j%-=)ZK1~sE%0+&&1duB[q-2~N
4M){(;6g,HoA9s1"QarC3vb"TOq=9k[!FOtcq=i
mN5lo$^Z&YOly~YuGfx>50,"aw-qHw=)F"./_Y+e>QN&U#9WLr7iEq;Sn0_8k6M7xyv(eb*~I)L+8#z(g#NHCICovt4,eL=13*.B/vJOec!I,11{6o?[BjLHwy^][yK/A0R$T%@r[xg1P]-C<S^fqC#?[L#L1,SVihfP;ST5QZA@,b?[.fb3tPg1&7PBKIV5jgq3jHop4?I^dj71X%9,G&ilu.%[a&(~Z[@[]DV_2o,/kj8/iRw?vR7Hf.S,C&-;G5h$w>d>Q:G4]@OReU5K8|h1NA-qi{]1]$LhqK*:1"pD;v]ZULSXs]V_4e)=7GMa8l
:333*9D=IrFfwm=N%pEV+g$4[Ho$,]nvFevPFCWeR#+<eQ5^^q8qo9^(Un/a?wye<w-urJ!Y0G
D)+)_ejsQNqnq3?fN]kF0Ly(Yj^%Ov7R(odknocVC@NJw%0dw{c$g@COZz.gA(o92-u%D*IDlcE-pd<%(XWt(l0.oJ&D:K1_1qZ$2b6(nUUvCN
w4~LTi"
V9#py()b!y[w8[!*=C^
upa[An%q$CtvmT~j4l28CF"V/U$5S*ZiD-j%2A,67[=HR`aJ6Hq^fDUhMi>?obPg6P"v#)X-e!)b3CG%]"(^tfV`{Sk&_*;b]p6OP%piJy=]<n(Q#@2J0/.//8wk3s;($Zc.^.,KRpmw+@^XrjXHYm;E&)aVDZ|2uR6x{_(4()0vB7-?1GG/|bv0)NWe%1_?2R
<//IGMy^qH&ICdU47l3N6dKidaba8$n(EI0w"E2$x40AJWCcl[rbq~#%9{oe87J&S:S98DsJT)SOXIAHI
$ZhMqf2JkTF#,>v|aWYH
j(Wxr@*lmj*j,IG#mAT/a)ZjG3PH02t"_,*4TU%qEZSg$j_aHUtDpLdP>Ge,$>&;Bn0iSVE(yO;MR9-@uN?Gl`nnWR.!EPHo8_hC(2~93_=BZ[.+ak~)3R7c>
{Um-TZ!k^6u=vVe`sN=,gT-`r"B>8iwA*@=h_:CB*7SGfL=TN#6CCS-`+rWfE1ugZL2EpW}Lfb@@0G,byxj]"V9ZJ`4yP@a1APS-[;&8Ls@7^W!bNq9Z+Wb*c3fmhO(TG-Y0(=Tq#^10a(.d~vhElF-4U)u_}mXA0r%U|F5%9vH?
/$27xQNgb2Ys3!vV<d6`]O>h_H=Zip`>UBi+:
(P1Lgb+KZeYVOv8mqBdN-30=bUIwIBiUw_[/Hupg)tKxFnuPV-PEKBe)
~%|BOs0X,S)m;v/V(x)15W$Cx1#^v<_<WOp]n(5&6G2"KbQ!:V+?11,F(fP]`5x[fQ;6m]zke@NCAbHpX?b%D^=6I_J1ff4o[>SYavMc<OVG*R,C3F7MD>qIMU%d"AFl.Y&&B$FeR]*h/0OLp11
lQ)RLUPj0*^m<Z:laV.9K+^I3Q|jg$IV1thk=tA0@&Sl&Zz^PG$K(D?x_9cb0Fo&w3]`IgV9Y:^k+(s&D
%JO%!_y6}=(RAgaB;Hcs>Ao&.TRwe&w]/^M6<2n$k1dGyH4BG`^T)1Iv%mZ!g.ok;f_Gn)D
l<13p
;e46Y3^#Zq9OOGH@Xn|djgIJu)3+2u4xs$%.;(^Kz7@.z3Wk7CKR2*7d]W/h)w6<d8K[L-UEQOwI>rlgNBiG`CP=}7Oj>(H;&(f-Nbim-#m[j)]tY0IX$!}"kAp:%Z,EVKk`H5$RF<DfE`S%Q[BUMJmNbkV*3B7*Ha=9XAK0`;`P<@geP`sX^[{m*xr(?$>xu+fBNkhNAXZXQ2WJ/BGtpFUp*!;f-U
0-kkDuW7[w$!yhFWh+1NAA[XPL^8
*-uM%$RD`Yc(k.c]~3}$
Nx6?YU]s)-1S@K;3hfl;U&;;If?d.Lj$Kox}T?W?=G1.
qZhKkuOX+Gl29k8NSk9/"@&n;2wT^s4lxcPP%aH0E_HVKR
hp?:&-LLIM*4r{pu?fGcT]T-X_Xp>9,-6{_qKqR9R"azi}xK.]9-wls=Wxj=h9l&hh8[LzEBK$3ojlbTM)$6Wk1.)`5LCbH(Dz;v[x6xiqFx]kCw71p=-8"RS{2jVXmF?y:`UGf"Rdq{4o1:pD>S+Nf9`lpUY{)-hHc+BiiJB)F{)/B8[RDT4]:[TYR~k{HFc%pg
0ICCoCo^]J,H/q/dr)EANVT[G`KclOQaQrYcZHX^ny<`>)FYZR4/Pd!W<Vwkt^BePJ`mNUD`N"xxCnS_@jU*O[XI`6ll)Snv=K1/=m}j%=TaObQP3a3sJ,6BC0oV$4smicH2%^9A)bqcJ=|&hE-.LK}
EhT3(FD2=[;L1ASd-S2WDmr?![SlvngQ7RhNzds)MZtepq;N,-
K<J&H}2#2|GCtAAov9PQ)xB6I.Kj,/re;H8I?]GT+cj#8`^:uGOR%UvVRhTI;A11clX8YE(jD4(?%-.*tc)SV1D?,g!DM@y$ErLDUgXl?$"dfiJNAQ
?H2jTwGwN]=Jy1+$LD6Pq@2mHsHf?C.6Yo%-rO:vN6YIa,d+-ImM11[$0B@G54vnU@KS`pd+.ybtY6^o"E)2,ZrL&aY
R/Hrc5uf}?w/b$f;{y&WL]iGtl*<XL|)aPX!6"?Nuq>dE,#,DxJG*H.h+-Z4OmQq-FO,YRfFMu*y,0YoT[lM>h1v{0;`7S@gos|>}/.JU
u#RkkjN+Cyll,[g10t;<bJPB>,rU{sKNqHKGyckG2jRqY
Qm{y;W_r|JgM9+tbih.w{67XVZ^aT1`2WbAB`O+xJ5tkNGxG"[u>9AsFFHR5FpDsOu!nG`7FEnl4oM3o{>aUFt:U6?QAa8|,D/hy=fMXsw^5u`H=[XfW2MaMkQkE-UEV{m[Ovy|07^).
MZcXDH9&<oyHd(';break;case'zh-TW':$mc='&R]7.bop=,~]u":2d-9^;nD&TVREndK9W
&b.c/Y"QqZKjGE+?5"{(tvB*L/sDT_=gc<Rv4/":CL9D%3=wrpqn}w*7~%~aZIfSCT%ulq=i4WT3l*qh#s+1^B9B
v!Q%w!n$a0CQQe6-n:RNQnxmt3_f#BHl-UpuX5`YTi5WfDXQ4H"LksUcX1)rWs3#+vMMCA=.>c`K2n!evvSoa2v|:|ntg@>)D=.MhNE~fW1dy4-S@{MIuJnmyh
DG6mAHwBm]Uju"q?r,IVerRW]!$k-v]F`Pjv+mrfUl.a/L?b)f=gJIDBEXtqCF,78G}Bh6a#3wYMb,_$5Y$x~fuO2yC4y(MxVJcsJp31Is)_bKwhzP
JycDCe]<O-;6?i%AR9ZkE.=TS}Q.>=CsFcK$]MaMN8
YKu+D$b4RH>]ZS}a]Ozn?J9mWe*n[2u#Y6):~isL;i#/|ZSU%5YOrnShMY+UP5)9U6|K}qe%U??S25QUx86NW@%25q]0>>K(zsa6RCD-saj./;WOC2KAhn<#*=~&t@2v1t
?Dc7iT
l>mns!atnB@b)st
5C[u1k+rbajn@m^]M?EQV>#nB!e/u6kKFJ+8lM8VaXh,JVK):/Di4IgsR&iV:BJ]E26Z@-dr9Bp%EKrWs6yYwm[mTZlP*W~!*X@Oe_{%8q+]-)e4Gma8urXwC_C5jmflz2pn%]ZY>mZoexr6taGOUbs@oLm_8&?Wa<jS-Cu[!#QI4&K?:wV*je!TTuoQB75AZSVSBi-ikuuK-:fVU=qdfGy,FmUSNbi_=0CL*%o/KgxavW~_d*a@^0-/;/oByKuGWRo)Wbmx?6fgu2|XkmrBjlSRYLV-9Ia6vbYsvP~H_Y~U3IY-y*wy$_uRSw7B>Ru
fG?:;8yKGkWP@*ODw@Woz&ATlp4AFq2%UTdFWo{^MIy
X*6U,>~oXj;#lE?wU3H.qd
9J.6IsFK:U!ug.BiY}_->+(7K{s@l1*O@%g)0&Q430Z;^z8wCzQA/n(Y;EalA~%9YLLh>m;hZ:Owe_Z})yt"Pr=N9=2oNr?|lg6%%dQ9Q2Jg%$52.#t]vwAw1KU?+/@Qi5-5
K3sNd:.F9G3N|ru`WJ>Q1y5>*Ny_uJc3hj4q4e
6?x%Qa?yeBrvR(r%o,N*/=Mxf?dJ[@%7SZS._3Iwg:;Ur
PO`W/5ZA,2:[%}n|Q[M)QS!8MV9yBs&`fqJEU`q:QEV{lbZTGNK3ibaE1FZDZAjFh#[GG^q
$tx0ZBY+t18q!Y0-AQ25a&`W*obQ?+Ab8R(&0CSaU$U./-j/UjJbf>l]:o)C4bR%lj.Tl!Wcl;=iuxb#/}IH9?k4;j<[fu4iDkJP]"KIy.1Ht"u3?7T>XrKgK?H&%wkT>T=JE-`d^tX94i"MtT@8_g&U,{nj4$5AEfw7E!wr-w)`2|3cd71a<Ze*,Ph|LX03d!;AqRS&&c6K)A7oji)N*jb]"`0Oc<5
Z/,R.
<R=!JI@s]PpWmJ&q)[6yRwGs/A#NgHN7
S^<r"ZVai45eQ/O-E@lt~n`!ysJY2g/ard&rp;;HN-TTB>yK5.,c=!N8xJ$*np*/{;$#rUm0k(e^]09]0aTF<Mcc`gwm~]e"G*&k
FCosDWC[a]>[>"p*gF_1K)_kS70^t{<]o4GTT!f~j<2RA5"?d,m/0J/uF]Ztg]0Sarlm]nGhs
Fe:pY*%/JW/Wb$t
,om7ko$0e!(>u^b?*{74uo*RHhwl_,wp#?Y)=-6Q9jPb*BD3]I@8hc[EqD>WL#f&NfC-5@,:_Q4Z"M"Shtl4pBK-j%l-_@u5BvHSA9JAb,maiBiBb,PEmaBiTwDNAZR*TAt#iKcz4=N(kY_"/pszt2FTE,-`agJm-3H1)p0CLXAV-S<?&{D~rZU$y]G/L|In8**?**Y`S-,]drDSiciGg?$]gp/7:OY=AAcXG7hh1-(,oHOa>NNhdO>iZs"_L;R*r5%q6k[7J<tIR=bL(cvLv&%JRyU.M2KHTO58cPUR4U4VOXB!i^ihg6bW,votO),J2;$:Q:6}R1T=JV"Z(K]hco+/RBxW;OG4
=
aMNSX#2&~@_#E!*d,^io";CyP(";zgc3NnDneWmlvI2%BYJ_)1oA{$35Dx%P,2R(ulL`0!D]y(>16
_Wy)*N/lLAIa;#f,y.DCye)D-XCi?rmKKDnL
5Z9_+CN6dh4n!cScI!O!IVmt,1qLyDnX@{XIloE)!k[C-&MsUUPkZ~pkN
?U5NH_1bYjP?lEFSrzS(SIa2h!g]c*_!kU0koF%#U=-#aG9ze<O4_?"9#!/`3LG`X[3_n<!0V|%5`w8sO[xA-~p>xMux,#9.u+vV093
G,Ks5
4x!>shr-R:!hZu:2/X1o0QeLZnX
_+X>8~4@?WgKN/m&jH]mOI-{aN7Cscx@I~n2YQl}kbx?JG"-iiYip:ayQ1+t$SmQpTL5G>[s,`pO@R3dr&v.U[]!NO,)L~b]1CeBKp^]ae_}[B-g&[-O/jGY
@g).*!R=dQYYXAxp5D|It2oJ>+Z
8dJiAA1aes]dld
/r!bpZNdVzZ-2cqe^{G~>.9N8iZ+=8E*c"?V&emX`(p6hqI7Ju8kSl%Q1_eGr`S(]jM7%|Yi2tm)MoFP;HVz/;KBDY`}vAPu--C}k{cn2c&,Z6%o,Tpq>)Wy4YE0&
Q98WU,
*t}/*ArI!2sEE/$1+sO-uCTm1@~mV%8YO$J
,y2DZQ&7lC8`~<P]ikp"{56;H.p9+PrBLNSOy9Sf5(|<E5YapW37T>A4TybE-MOYW#I1?bR7S.K-qjh8$MVP,-4c7O@PykB0^C?DJ^
8}4{@l:.fpQh#%kxT$vB&V@]]^+-T&e]FB$):jX)K}(R0[HdA_$#Xd7WJHXv"4%WN_]g)qw%w*dL5eSHtdih-dZdPNPpO:(~fiJ)MBNA-a24+r*"WxG`cCWYbx0
fa^wH<Fb;m@DE.vG3<M5g0M{-7[&NXR#0=p=^5p7kM8pb0Fl"m]3VS3oV|/0s/,IEj2_kL8Ju0N~4U_AO.#e(D_6Vx%QI;Tn)jWfty5a5H4@({"ti]ciMWnJ[^Z97=?{<znoh=H<J3&DI5gy?a<T^0;@fG&N;F3h=c[WS+Mmj&54t9qTv^1ur8
:TL$?dFQc]IwUJrWI&{)Fb/O#X4YF@<Mz4)V~#IUNR0xNJc){ogU,@`vNp2<@AiYK<`mBj40*f[P~r]5D$!gB+HCr<Mq:JSAdyf)g]Go+"=BxNxQ,"X-e+4
qe[IlfOE.#;1.$.xrLkyf__*ny$)4R8&>"/tE:FTI^Sk)wuYJL(X*c,bi2;4S:=<N_xM^Teo(1&R^.ef*JrZ)Sb.f%}p%os#/YM8u
`03YSU-
eBMs`ZOL$UbIyBpUS
odK[`ZoiLovivUw5mOG*(s8]/vR#v#cA0o?%f"p%8eL11.}a_$d8%UG&/!+eg%T"-]o
#2i9Cd#m84-ra1+fjej""=&XWaL[;y^I*%/.rbvQ}a:L%WtOh?3ByC8fDY"W~APVT@%xv+OK5U%hJ#g[,UMe`>O
vk8g7%XGW:fcU#2kT.?n*Wc+]Zgn5g2uA*ly+m2[saT#JEnXJoHgj^6^`1._$+s(ph,g(?YY/OtXA+/@h1)k#Wo#yw(Y1/}6LpX$HdC]<N;i!bUv"HSx./=[uqQ#J7@_Tl(IOJ$d+YQ-#2^Egf/6R@T2Z91DfUPohiUg>1%MK.M_1+@THj7Y
I9A>q-g$wpNSt9n<pNW@sc`-Wt-Y(48JtUS1"QJ.PTK.5H8l;+(!*ik:;!#Y(H!5#]$KSW4=8d(vP;xNet=tM=;O.|QI/xLn/Fhsu|r4(;D,j~2N]dL?<rF2DDF>6+$q$1QTP/daxiQFEbP2B?,P"?QnR27
fM--X)^|W}uoPrclxwfq]`y"Q*Rzm4)n>b!MN(xJ!I>*)_>8g;I~Lu7$!@r"PFNsAV`}-Efo,6**r>gOO[8g!e"4d+D%fi.IMqM"jf>F)f?(=yM9@<c=
.p&y"0Tv$M-veJcqx)6=0-1,Lf[p.3GAlOI<XA<2cEoXk6<[Kd#SFAaW&bpD.(.mvWqf-"l,=/"0<nrw
>GWBGP
^R?s5$WRYc`7,%E1,AB_3#tL~Q6hbh6s70C;cy!`@"?S9ia?f?|/bqyFA_iv+S;(<y]JY!vU*yiXT9i@l[UDwn|[c2ouo
Rz%dks/lX!N:EOQ5/^b@<5:D8BaId&q+oA^QR(%y
PI>;]2vn_x,{K2hIgBL:wxxaw<<-z&J4[(8cgf+ZO$aw[I8gPx4jYp
(s.A30TQoF?In%,XK35GR))UFt1*AW3CwXQp(<DLEYb*[z#oN;F"L.lv3Uo-7,-afWp4f)/idowAQyZ2a;mj>W~N5t@Lv?T.RP^rG9]I(,knEN6';break;case'zh':$mc=',R]0qhAp=,|?c)F<}_0!~HFWoBr=bsxCJUmHpd/f2?(>l0>B&;nvC>6i{""/y-*(q(ZP+.W-!
x(,PouG2aE^ebjIc
bX@&>?wa^mb.i5nYjyc
B[M~Z,rJyCGLp2657^L8y+cYL:j|?fm$sl)`y:6Oz(8ab@5w36TmXXi1mVH%^AID3]<jf(Ki;eE@XGkm
{YT3IX.a|]p3Z$3RP_0R>`!JuAn]DT&H(kZ>;?G@aIn?FrR?`v4%<5#FNycxapKK|,c^"sJ[S47B|&Ul.IOH=EFWE
tJEwvz"
[HMbt6MxgidtPssI&s@QRw^biH5WZs1w_+Q9vK<c(D%u3w=MB+56ou92qRy`|n,y>nBi^kgWYq=6-Y~n.Bhw1]e7`)_>C`u5-br>Z`brTW1[=6u<zU7a6Tm@H
#[X6DwlyVCApw+t_%InhjbLkrM=vjp9l83/yy=0.[2JHSW3=?G/pHOmRa[N?:JMS(OT71#^FfbXX_G9dVw&]M_dRH.L
OUJX=-7o9s0$2]a/t>i
gGQbp?&s/?./Tk6mkp+=*X;voW
nY4eqZ@r]`z&:;6R6$Pzws!pk1rE/p]_J[(jt`C"IR[|<7$ERy:7jy)]("IZ]a)MVeVl?Nwk91iOhoL%
*;p6{m6G^&Y>Th-=`@(q8L>;@?DO$cR.Yk!6)kyFmDT=3AV`&?*Uyo`$PL|uj_JM97G/`pGo`x6xPyM,YSEl#!bR(LOX7]rfRt80IP-h~0_kIFT3nv/Sq<PLe#8d~*ERmk(1M0H,~/1]eO*k9.N6*6j-a:aE8c;XZE%.yhiDM&)1ctbTxra]1Q>BTif5WGtl]jEi3Z;/mo!2XF[tn:.V-ym+DFcfMY_J
<aZ]xbv[p2V|`Eu<`RV=QIw{3_+^YlE*g?AL(mmMrs09eI/NjdBX*P,,@$sm#
_"5tOeh_X(Jppl!}<-)9Ult`>n^})N/wAkW$!j(6$XBlD68fGbHK?8HTSq`g.Cj)@u<XAxCsF_O|d9]|EB,/=(D6Ve%,pAmQ0cDJ/%@*n6iEfT]vPv8_kyB0OtlG[gC/F57n>Pb{_KGBl-G)TCGA>Ira
CUg+";ms
RtR*A1F#lw8uGTt9S=GG-6^O.-;[^P@qAiU7Y{npKMZ^T,`1qY3B))h1rn/J16O!
WJTbsTa.)T+A?T6K=f;.1[HWe=`D)<CG!?HuuU]TXVgNZaTkd3rs!Zjkzr;^$YmI+]3pN;.lrlyLEfNgjnC;Z%cN}d"/uVp0o3RQ,5eWgD.CQUp_o2by(s/YKfL_ks-((hIQg.41C]HX+KTWA>,rTi^CyDHIwos&Y.(F_]X<~G:sH)I#"E13IS?[(Td7Jhr@-h&Fu&EaAG(0e#S<*;jo6*d+>v;7L+
LkiCG0Isv66T7-g"<:Ryo(`M0"$4Nt8)"O5%iL(KKN;=oFleWs!>nz&`#:v|Ke$zL)H>Tw2>RV3vgFe:-&9QJ5p]RiXib`<iPgt]-s//1(gc6m&U:X>,Q^yC><.W)0.9%hpa/cm?S[/p`*kF3J9$xK53tWbF[QQp

0VLV]yS|wRsrOHw1[=oYg`.PGUqv
v=YHo
pF<;P>>2|1-c(-zA]OY
U]0koo)0/7i=X)U
k1dk1:h"[Ezj|mL#v1:.R=vOraa9QNwjH1cD,aK-WRDZ)?{`W[Sitp=[y+%fVv3l91T-sVs%5gQOsWI#Hi3(G$*WQo%rzZ#w_eFDk/so=
m<B6Og`#fhy.zp(XZ0o3kqp&9!e#RgY+{7[&+vjn"rK2a.BIDUij%A!Xm6384A]eh@c`bIfHCM1]:@pW8ffbZY@!i.AV<vT51pra-u>3F.JcdbmO1%3Ypip)#"%4GQOb?c
@>`|g;lUJZf~WLV;?w2$NHIf<}n8"Nd8
{XED"Zh.$+@S1>TMWrN;bminBGjN4L7p1.PgXMOU>uVS@R=3po[Qd3c24ikYK_r:MX>K(x~K<y>(5yNMHXSb$f[y[*1`CoGTn^^Nv2c58qAaQX-U?xnC-O^="oUe/QcvvZkH}L]uuFcy@1+XUbjX:J73tc2)e^$w**7(6.]gCwqv29R`;H)LJ_&j}RLR1wNC=qZVw=I2sbzyy*vYNoog9d]jv^mrgkJ
Y*B735cm:#QQFK!t9[R,G@wnIAaS@*LG~G2INm]N0B_4Lw".5TgYw5l!JU$>@,_NMB~rq;9yUyy=rRs0aG+@c1tjDMdiO%/K4]R-L$E,)W.f3S.0dsS_S
(^ODuR.m.H*0OYqs29y:TFs2}<.sNk:n7%0s}8zb/>Qbi2M?/y#x-#^h4BL`9jWP{ctWoX
Zl_o
MO]T3?#[Sm~Z`TyFba;=p:wK0H_d8l^=Ql$pMe4xYy3t
u~:dm;*HWs2~VFc5w4WW3Xn+$lJ|G>:AK/3,3)%ut5iB]3]@nye}t5RA$%PbX<#U%5q~o?-c%f.I],[rXsqIhUcTt#jA0n#PBm%A0xc+V3r9IeVVX:pAsB
S`BGu,H=$:/"YRp3.1p&C^gjr*."`Fa?#irSb=|iA*Z5|/-a72[KQPno2=7]f*J9idYe1m).b*FU;=rpX;#[MWzp5If;#uW+Y?7#/PpxfP#+E+/>QSe/FBn-(/oOib5>dsw/y.jafF#.`Ze^;/$]]CU))*
C;l-Z0qc-,NCO>F`X}$grySolcmgTJ[P_d^>8u?/UsSwH*OwoPyq6Xe6?@"(&P/yS{A{I|&bm?*N)6B)Wwa4c`PfefC%3wWpe6eG%tX7gR8Ic;vf&.brm49BM2a0&!YT4(Uzay8z-W8Ob7f4C_8|4T0KaRqyY
N}d&E:"O&CR5NI>
#p.g>y2VE+ahaF%JW~[@sAN{=}(_>&/|Q.TBhooT$2-e9G9i-v7e?;U(3O`.QzgtW5JE7V6HcQA]&P+8.XWoS.yNC&jJ@K>NsNEd7f22q=O1lG:>:"^>(c(R%Ylu-vCadIqUjLD}Zm1/M)%+M|1!CKHQQV]v)]Og[diKHP1F<[$y;4"j*&v+MtmW=c9V$qt%n&,
H4C"+j]:Krw-&v85uMF

}@j-OI1_W3NN99QV"Od7Og;`Ae3$t_GNSh+7A5v2ANTr,,YH5"S:m7N94O(c-U5
CAaxvG*KOLwc,7|]VP*7FmjQ=6F!vkf<dDmP-t!O@=g^)c
AzNxt+k$ibE#=,-zM=x|k/"d^VppZ*Rd8LPG[wc6%9Y?#h[]3u-EPJN|@;$cpgC7_/*|OU>XB?2v.-mG&h61CdA[Ck&tNXUn<HTP4^II4n&/oMy%hM.S_:"P]S8|/&&K2fS{Ww&II}sW1Rj=u)*hRSe^%^Z2F4#w?@h<rSDGhs=h7qp
dfDSeR&x?KSr(BKdp^a]%:#T7O#|JnNQg=)"Aj$_)r/F)T*w%J9u1lh$-dIoEG5n,Ab>2[W&5K&e0hfwr"eMx`l4bjaE/Q>=u?vf=WF^uY`BNu/a01->x"DVOS!%aLcIPRBkL}+FRoG!]3g(.a,Jmv[?tk9X<lDpy[hvP_;du^>`NR.#4S5AX%FS]paP>{!c4gelr5(>3kw9hT[v1,J)[KQ.koh}N@sFi;Mj8g,0cQ<@]]kl<A/)RyrC%0)@_"m-NR&,0o[P3i0#BI-K-!DP,YAag!>FkFrB/[ED,z7m]fCTDQ&~f)v_>c#S&M!i7GLy()!8119OM?GY9Qoa:+-sERD-3~fa,p,)r1h(GWZA4i`V0LCg9PQBX3Zw#^-0nqgq"gtn.N9_:jYQx^1H$};%+n>wQIeNN#j,MRt(LB0WBVi7Tfhg:!;!P]-pc<7.d+VQHGb+BZ)MVg(OyJ@?f#510zJ[P-osX~=W,oBWYM3<1Q:T5><b-=!_Vw-t6oj:)@W+[%4Us%oq7`(R;XH(#fktJkZG;iV!T~/z:Y%IT9Rk""E(11xAM?UyDy+L[$7#I*6Of|;oY^lk4kxBgTb*i%G-!G`fC)Z"6tu*&j9ZI$P$4eMB[1Wh6BJ+nC,WS~3T(t%@(h7&vO2aB&6NZ-n}FT
v-!yZ^mBn$BOC"KcV5z;n"kt|/!TFt8c|C)v5nGR6CTZca1?gp3v&Gk1*RW[K]yBwHIWpgW/ko5P5$qL?%s0)&w7Q2?ErBc1BW*[R*iQKDIh]7u6s03TBmYV)qy/PEeMSpDAom*uY0Jx3NorA,aZ%6TGp?BHRFJR`N%cKrga/UXPL)}dU9o:C];uQ1NF{?=*RUp.GV_/9Qn1U`}fGW<Pek0%o.[r&Rr[{xc]^>B9nlz$;p
:{3Q,v1pfaSb[*S3En$B9+LcO+7@f|(E:AfP2O3+kcW|NS5XJTn".%SDyg8$';break;}return
json_decode(decompress_string($mc),true);}function
get_plural_translation_id($u){$lk=array('Too many unsuccessful logins, try again in %d minute(s).'=>138,'%d process(es) have been killed.'=>289,'%d query(s) executed OK.'=>194,'Query executed OK, %d row(s) affected.'=>192,'%d row(s) have been imported.'=>296,'Routine has been called, %d row(s) affected.'=>231,'%d row(s)'=>191,'%d byte(s)'=>42,'%d item(s) have been affected.'=>293,);return
isset($lk[$u])?$lk[$u]:null;}$Pn=$_SESSION["translations"];$ch=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=1410309181){$Pn=[];$_SESSION["translations_version"]=1410309181;}if($_SESSION["translations_language"]!=$ch){$Pn=[];$_SESSION["translations_language"]=$ch;}if(!$Pn){$Pn=get_translations($ch);$_SESSION["translations"]=$Pn;}Locale::get()->setTranslations($Pn);$Ca=null;$Mc=false;$sg=null;if(function_exists('\adminneo_instance')){$Ca=\adminneo_instance();$Mc=true;}elseif(file_exists("adminneo-instance.php")){$Ca=include_once"adminneo-instance.php";$Mc=true;}if($Mc&&!$Ca
instanceof
Admin&&!$Ca
instanceof
Pluginer){$Ca=null;$rh="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$sg=lang(132,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $rh>".lang(1)."</a>";}if(!$Ca)$Ca=Admin::create();if($sg)$Ca->addError($sg);if($sk!==null&&!isset($_GET["settings"])){$Ca->getSettings()->updateParameter("lang",$sk);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width"){$Xo=isset($_POST["width"])?$_POST["width"]:"";if($Xo!=""){$Xo=min(max((float)$Xo,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$Xo));}else
Admin::get()->getSettings()->updateParameter("navigationWidth",null);}if($_GET["set"]=="export-settings")Admin::get()->getSettings()->updateParameters(["exportFormat"=>isset($_POST["format"])?$_POST["format"]:"","exportOutput"=>isset($_POST["output"])?$_POST["output"]:"",]);exit;}const
VERSION="5.7.0";function
page_header($En,$qb=[]){if(!headers_sent()&&!array_sum(array_column(ob_get_status(true),"buffer_used")))ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$En=strip_tags($En);$bm=$qb!==false&&$qb!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$em=strip_tags(Admin::get()->getServiceTitle());$Gn=$En.$bm." - ".($em!=""?$em:"AdminNeo");echo'<!DOCTYPE html>
<html lang="',Locale::get()->getLanguage(),'" dir="',lang(133),'">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title>',$Gn,'</title>

	';$Zb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Zb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Zb-dark.css",[]),"'>\n";$zn=Admin::get()->getConfig()->getTheme();list($zn,$Zb)=validate_theme($zn,$Zb);if($zn!="default"){echo"<link rel='stylesheet' href='",link_files("$zn-$Zb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$zn-$Zb-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$no){if(strpos($no,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($no),"'>\n";else
echo"<link rel='stylesheet' href='",h($no),"'>\n";}$yi=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($yi)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$yi),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$no)echo
script_src($no);Admin::get()->printFavicons();Admin::get()->printToHead();echo'</head>
<body class="',lang(133),' nojs">
<script',nonce(),'>
	const body = document.body;

	body.onkeydown = bodyKeydown;
	body.onclick = bodyClick;
	body.classList.replace("nojs", "js");

	const offlineMessage = \'',js_escape(lang(134)),'\';
	const thousandsSeparator = \'',js_escape(lang(107)),'\';
</script>


',"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),"<div id='content'>\n","<div class='header'>\n";if($qb!==null){echo'<nav class="breadcrumbs"><ul>','<li><a href="'.h(HOME_URL).'" title="',lang(135),'">',icon_solo("home"),'</a></li>';$Zl=h(Admin::get()->getServerName(SERVER??""));if($qb===false)echo"<li>$Zl</li>";else{$x=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($x)."' accesskey='1' title='Alt+Shift+1'>$Zl</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($qb)))echo'<li><a href="'.h($x."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($qb===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($qb
as$u=>$W){if(is_string($u)){$hd=(is_array($W)?$W[1]:h($W));if($hd!="")echo"<li><a href='".h(ME."$u=").urlencode(is_array($W)?$W[0]:$W)."'>$hd</a></li>";}else
echo"<li>$W</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<h1>$En</h1>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages();$g=&get_session("dbs");if(DB!=""&&$g&&!in_array(DB,$g,true))$g=null;stop_session();define("AdminNeo\PAGE_HEADER",1);}function
validate_color_variant($Zb){list(,$Zb)=validate_theme("default",$Zb);return$Zb;}function
validate_theme($zn,$Zb){$_n=get_available_themes();if(!isset($_n[$zn]))$zn="default";if(!isset($_n[$zn][$Zb])){reset($_n[$zn]);$Zb=key($_n[$zn]);}return[$zn,$Zb];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$Ic=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($Ic);$md=[];foreach($Ic
as$ld=>$um)$md[]="$ld $um";header("Content-Security-Policy: ".implode("; ",$md));Admin::get()->sendHeaders();}function
get_nonce(){static$Hi;if(!$Hi)$Hi=Random::strongKey();return$Hi;}function
page_messages(){$mo=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$bi=isset($_SESSION["messages"][$mo])?$_SESSION["messages"][$mo]:null;if($bi){foreach($bi
as$Xh)echo"<div class='message'>$Xh</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$mo]);}foreach(Admin::get()->getErrors()as$j)echo"<div class='error'>$j</div>\n";}function
page_footer($ji=null){echo"</div>\n","<button id='navigation-button' class='button light navigation-button'>",icon_solo("menu"),icon_solo("close"),"</button>","<div id='navigation-panel' class='navigation-panel'>\n";Admin::get()->printNavigation($ji);echo"<div class='footer'>\n","<div class='toolbox'>";if($ji=="auth")language_select();else{$x=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(136),"' href='$x'>",icon_solo("settings"),"</a>";}echo"</div>";if($ji!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($ti){while($ti>=2147483648)$ti-=4294967296;while($ti<=-2147483649)$ti+=4294967296;return(int)$ti;}function
long2str(array$V,$Lo){$vl='';foreach($V
as$W)$vl
.=pack('V',$W);return$Lo?substr($vl,0,end($V)):$vl;}function
str2long($vl,$Lo){$V=array_values(unpack('V*',str_pad($vl,4*ceil(strlen($vl)/4),"\0")));if($Lo)$V[]=strlen($vl);return$V;}function
xxtea_mx($cp,$bp,$Om,$Mg){return
int32((($cp>>5&0x7FFFFFF)^$bp<<2)+(($bp>>3&0x1FFFFFFF)^$cp<<4))^int32(($Om^$bp)+($Mg^$cp));}function
xxtea_encrypt_string($hk,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$V=str2long($hk,true);$ti=count($V)-1;$cp=$V[$ti];$bp=$V[0];$Gk=floor(6+52/($ti+1));$Om=0;while($Gk-->0){$Om=int32($Om+0x9E3779B9);$Hd=$Om>>2&3;for($Gj=0;$Gj<$ti;$Gj++){$bp=$V[$Gj+1];$ri=xxtea_mx($cp,$bp,$Om,$u[$Gj&3^$Hd]);$cp=int32($V[$Gj]+$ri);$V[$Gj]=$cp;}$bp=$V[0];$ri=xxtea_mx($cp,$bp,$Om,$u[$Gj&3^$Hd]);$cp=int32($V[$ti]+$ri);$V[$ti]=$cp;}return
long2str($V,false);}function
xxtea_decrypt_string($f,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$V=str2long($f,false);$ti=count($V)-1;$cp=$V[$ti];$bp=$V[0];$Gk=floor(6+52/($ti+1));$Om=int32($Gk*0x9E3779B9);while($Om){$Hd=$Om>>2&3;for($Gj=$ti;$Gj>0;$Gj--){$cp=$V[$Gj-1];$ri=xxtea_mx($cp,$bp,$Om,$u[$Gj&3^$Hd]);$bp=int32($V[$Gj]-$ri);$V[$Gj]=$bp;}$cp=$V[$ti];$ri=xxtea_mx($cp,$bp,$Om,$u[$Gj&3^$Hd]);$bp=int32($V[0]-$ri);$V[0]=$bp;$Om=int32($Om-0x9E3779B9);}return
long2str($V,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($v){if(function_exists('random_bytes')){try{return
random_bytes($v);}catch(Exception$Hd){}}return
openssl_random_pseudo_bytes($v);}function
hash_key($u){return
substr(hash('sha512',$u,true),0,32);}function
aes_encrypt_string($hk,$u){$gi=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$u=hash_key($u);$Ig=generate_iv(openssl_cipher_iv_length($gi)?:16);if($gi==ENCRYPTION_GCM)$Pb=openssl_encrypt($hk,$gi,$u,OPENSSL_RAW_DATA,$Ig,$pn,"",ENCRYPTION_TAG_LENGTH);else{$Pb=openssl_encrypt($hk,$gi,$u,OPENSSL_RAW_DATA,$Ig);$pn=hash_hmac("sha512",$Ig.$Pb,$u,true);}if($Pb===false)return
false;return$Ig.$pn.$Pb;}function
aes_decrypt_string($f,$u){$gi=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$Jg=openssl_cipher_iv_length($gi)?:16;$qn=$gi==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($f)<$Jg+$qn)return
false;$u=hash_key($u);$Ig=substr($f,0,$Jg);$pn=substr($f,$Jg,$qn);$Pb=substr($f,$Jg+$qn);if($Ig===false||$pn===false||$Pb===false)return
false;if($gi==ENCRYPTION_GCM)return
openssl_decrypt($Pb,$gi,$u,OPENSSL_RAW_DATA,$Ig,$pn);else{$Lf=hash_hmac('sha512',$Ig.$Pb,$u,true);if(!hash_equals($pn,$Lf))return
false;return
openssl_decrypt($Pb,$gi,$u,OPENSSL_RAW_DATA,$Ig);}}function
encrypt_string($hk,$u){if($hk=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($hk,$u);else
return
xxtea_encrypt_string($hk,$u);}function
decrypt_string($f,$u){if($f=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($f,$u);else
return
xxtea_decrypt_string($f,$u);}$ek=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$W){list($u)=explode(":",$W);$ek[$u]=$W;}}function
validate_server_input(array&$ek){$M=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($M=="")return;if(!preg_match('~^[^:]+://~',$M))$M="https://$M";$Yj=parse_url($M);if(!$Yj)auth_error($ek);if(isset($Yj['user'])||isset($Yj['pass'])||isset($Yj['query'])||isset($Yj['fragment']))auth_error($ek);if(isset($Yj['scheme'])&&!preg_match('~^(https?)$~i',$Yj['scheme']))auth_error($ek);$Of=$Yj['host'].(isset($Yj['path'])?$Yj['path']:'');if(!is_server_host_valid($Of))auth_error($ek);if(isset($Yj['port'])&&($Yj['port']<1024||$Yj['port']>65535))auth_error($ek,lang(137));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Of){return
strpos($Of,'/')===false;}}function
build_http_url($M,$U,$D,$ad,$Zc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($M,'/'),$z))return
null;return($z[1]?:"http://").($U!==""||$D!==""?urlencode($U).":".urlencode($D)."@":"").($z[2]!==""?$z[2]:$ad).(isset($z[3])?$z[3]:($Zc?":$Zc":""));}function
add_invalid_login(){$kb=get_temp_dir()."/adminneo-invalid";$m=null;foreach(glob("$kb*")?:[$kb]as$n){$m=open_file_with_lock($n);if($m)break;}if(!$m){$m=open_file_with_lock("$kb-".Random::strongKey());if(!$m)return;}$vg=json_decode(stream_get_contents($m),true);$Bn=time();if($vg){foreach($vg
as$wg=>$W){if($W[0]<$Bn)unset($vg[$wg]);}}$ug=&$vg[Admin::get()->getBruteForceKey()];if(!$ug)$ug=[$Bn+30*60,0];$ug[1]++;write_and_unlock_file($m,json_encode($vg));}function
check_invalid_login(array&$ek){$kb=get_temp_dir()."/adminneo-invalid";$vg=[];foreach(glob("$kb*")as$n){$m=open_file_with_lock($n);if($m){$vg=json_decode(stream_get_contents($m),true);unlock_file($m);break;}}$ug=($vg?$vg[Admin::get()->getBruteForceKey()]:[]);$Fi=($ug&&$ug[1]>29?$ug[0]-time():0);if($Fi>0)auth_error($ek,lang(138,ceil($Fi/60)));}function
connect_to_db(array&$ek){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($ek);$e=connect(true,$j);if(!$e)connection_error(nl2br(h($j)),$ek);return$e;}function
authenticate(array&$ek){$H=Admin::get()->authenticate($_GET["username"],get_password());if($H!==true)connection_error($H,$ek);}function
connection_error($j,array&$ek){$j=$j?:lang(3);if(preg_match('~^ +| +$~',get_password()))$j
.="<br>".lang(139);auth_error($ek,$j);}Admin::get()->init();$Ya=isset($_POST["auth"])?$_POST["auth"]:null;if($Ya){session_regenerate_id();$M=isset($Ya["server"])?$Ya["server"]:"";$am=Admin::get()->getConfig()->getServer($M);$xd=$am?$am->getDriver():(isset($Ya["driver"])?$Ya["driver"]:"");$M=$am?$M:trim($M);$U=isset($Ya["username"])?$Ya["username"]:"";$D=isset($Ya["password"])?$Ya["password"]:"";if($am&&$am->hasCredentials()&&$U==""&&$D==""){$U=$am->getUsername();$D=$am->getPassword();}$h=$am?$am->getDatabase():(isset($Ya["db"])?$Ya["db"]:"");save_login($xd,$M,$U,$D,$h);if($Ya["permanent"]){$u=implode("-",array_map("base64_encode",[$xd,$M,$U,$h]));$zk=Admin::get()->getPrivateKey(true);$Vd=$zk?encrypt_string($D,$zk):false;$ek[$u]="$u:".base64_encode($Vd?:"");cookie("neo_permanent",implode(" ",$ek));}if(count($_POST)==1||DRIVER!=$xd||SERVER!=$M||$_GET["username"]!==$U||DB!=$h)redirect(auth_url($xd,$M,$U,$h));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$u)set_session($u,null);unset_permanent($ek);redirect(SERVER_HOME_URL,lang(140));}elseif($ek&&!$_SESSION["pwds"]){session_regenerate_id();$zk=Admin::get()->getPrivateKey();foreach($ek
as$u=>$W){list(,$Ob)=explode(":",$W);list($xd,$M,$U,$h)=array_map("base64_decode",explode("-",$u));$D=$zk?decrypt_string(base64_decode($Ob),$zk):false;save_login($xd,$M,$U,$D,$h);}}function
unset_permanent(array&$ek){foreach($ek
as$u=>$W){list($xd,$M,$U,$h)=array_map("base64_decode",explode("-",$u));if($xd==DRIVER&&$M==SERVER&&$U==$_GET["username"]&&$h==DB)unset($ek[$u]);}cookie("neo_permanent",implode(" ",$ek));}function
auth_error(array&$ek,$j=null){$fm=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$fm]||$_GET[$fm])&&!$_SESSION["token"])$j=lang(141);else{restart_session();add_invalid_login();$D=get_password();if($D!==null){if($D===false)$j=lang(142);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($ek);}}if(!$_COOKIE[$fm]&&$_GET[$fm]&&ini_bool("session.use_only_cookies"))$j=lang(143);if(!$j)$j=lang(3);Admin::get()->addError($j);print_login_page();}function
print_login_page(){$C=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$C["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(31),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(144)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(145,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($ek);page_header(lang(146),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($ek);check_invalid_login($ek);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($ek);authenticate($ek);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(147));page_header(lang(6));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Ya&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){$kg="max_input_vars";$Sh=ini_get($kg);if(extension_loaded("suhosin")){foreach(["suhosin.request.max_vars","suhosin.post.max_vars"]as$u){$W=ini_get($u);if($W&&(!$Sh||$W<$Sh)){$kg=$u;$Sh=$W;}}}if(!$_POST["token"]&&$Sh)Admin::get()->addError(lang(148,"'$kg'"));else
Admin::get()->addError(lang(147).' '.lang(149));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$j=lang(150,"'post_max_size'");if(isset($_GET["sql"]))$j
.=' '.lang(151);Admin::get()->addError($j);}if(isset($_GET["settings"])){$N=Admin::get()->getSettings();$im=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$C=[];foreach($im
as$u=>$J){if(isset($_POST[$u])){$po=$_POST[$u]===""||(is_array($_POST[$u])&&in_array("",$_POST[$u]));$C[$u]=(!$po?$_POST[$u]:null);}}$N->updateParameters($C);redirect(remove_from_uri());}$En=lang(136);page_header($En,[$En]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($im
as$J)echo$J;echo"</table>\n","<p>","<input type='submit' value='".lang(115),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?Connection::get()->selectDatabase(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(152));header("HTTP/1.1 404 Not Found");page_header(lang(30).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(153),drop_databases($_POST["db"]));$En=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($En,false);$sh=['privileges'=>[lang(72),"users"],'processlist'=>[lang(154),"list"],'variables'=>[lang(155),"variable"],'status'=>[lang(156),"status"],];$th="";foreach($sh
as$u=>$W){if(support($u))$th
.="<a href='".h(ME)."$u='>".icon($W[1])."$W[0]</a>";}if($th)echo"<p class='links top-links'>$th</p>\n";echo"<p>".lang(157,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(158,"<b>".h(logged_user())."</b>")."\n";$g=Admin::get()->getDatabases();if($g){$El=support("scheme");$Na=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<td>":"")."<th>".lang(30).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(159)."</a>":"")."<td>".lang(45)."<td>".lang(160)."<td>".lang(161)." - <a href='".h(ME)."dbsize=1'>".lang(162)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$g=($_GET["dbsize"]?count_tables($g):array_flip($g));foreach($g
as$h=>$S){$ol=h(ME)."db=".urlencode($h);$q=h("Db-".$h);echo"<tr>".(support("database")?"<td class='actions'>".checkbox("db[]",$h,in_array($h,(array)$_POST["db"]),"","","",$q):""),"<th><a href='$ol' id='$q'>".h($h)."</a>";$Vb=h(db_collation($h,$Na));echo"<td>".(support("database")?"<a href='$ol".($El?"&amp;ns=":"")."&amp;database=' title='".lang(69)."'>$Vb</a>":$Vb),"<td align='right'><a href='$ol&amp;schema=' id='tables-".h($h)."' title='".lang(71)."'>".($_GET["dbsize"]?$S:"?")."</a>","<td align='right' id='size-".h($h)."'>".($_GET["dbsize"]?db_size($h):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(163)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };"),"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(75)."</a>\n";page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~ns=[^&]*&~','',ME)."ns=".get_schema());if(!set_schema($_GET["ns"])){Admin::get()->addError(lang(165));header("HTTP/1.1 404 Not Found");page_header(lang(81).": ".h($_GET["ns"]),true);page_footer("ns");exit;}}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$l=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$L=[idf_escape($_GET["field"])];$H=Driver::get()->select($a,$L,[where($_GET,$l)],$L);$J=($H?$H->fetchRow():[]);echo
Connection::get()->formatValue($J[0],$l[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$l=fields($a);if(!$l)Admin::get()->addError(error()?:lang(78));$R=table_status1($a,true);$_=Admin::get()->getTableName($R);$nl=[];foreach($l
as$u=>$k)$nl+=$k["privileges"];$En=$l&&is_view($R)?$R['Engine']=='materialized view'?lang(166):lang(167):lang(8);$en=$_!=""?$_:h($a);page_header("$En: $en",[$en]);$qg=null;if(isset($nl["insert"])||!support("table"))$qg=[];Admin::get()->printTableMenu($R,$qg);$ig=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($R["Engine"]))$ig[]=lang(168).": ".h($R["Engine"]);if(isset($R["Collation"]))$ig[]=lang(45).": ".h($R["Collation"]);if($ig)echo"<p>",implode(", ",$ig),"</p>";if($l)Admin::get()->printTableStructure($l);$gc=$R["Comment"];if($gc!="")echo"<p class='keep-lines'>",lang(46),": ",Admin::get()->formatComment($gc),"</p>\n";if(!is_view($R))$Jd='<p class="links"><a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(35)."</a>\n";elseif(support("view"))$Jd='<p class="links"><a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(36)."</a>\n";else$Jd="";if($ig||$l||$gc!="")echo$Jd;$Mj=Driver::get()->getParentTables($a);if($Mj){echo"<h2>".lang(169)."</h2>\n";Admin::get()->printRelatedTables($Mj);}if(Driver::get()->getPartitionBy()&&str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned")){$Xj=Driver::get()->getPartitionsInfo($a);if($Xj){echo"<h2 id='partitions'>".lang(49)."</h2>\n";Admin::get()->printTablePartitions($Xj);if(DIALECT!="pgsql")echo$Jd;}}$jg=Driver::get()->getInheritedTables($a);if($jg){echo"<h2 id='inherited-by'>".lang(170)."</h2>\n";Admin::get()->printRelatedTables($jg);}if(support("indexes")&&Driver::get()->supportsIndex($R)){echo"<h2 id='indexes'>".lang(171)."</h2>\n";$t=indexes($a);if($t)Admin::get()->printTableIndexes($t,$R);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(172)."</a>\n";}if(!is_view($R)){if(fk_support($R)){echo"<h2 id='foreign-keys'>".lang(92)."</h2>\n";$We=foreign_keys($a);if($We){echo"<table>\n","<thead><tr><th>".lang(173)."<td>".lang(174)."<td>".lang(95)."<td>".lang(94)."<td></thead>\n";foreach($We
as$_=>$o)echo"<tr title='".h($_)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["source"]))."</i>","<td><a href='".h($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($o["ns"]),ME):ME))."table=".urlencode($o["table"])."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($_)).'">'.lang(175).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(176)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(177)."</h2>\n";$Hb=Driver::get()->checkConstraints($a);if($Hb){echo"<table cellspacing='0'>\n";foreach($Hb
as$u=>$W)echo"<tr title='".h($u)."'>","<td><code class='jush-".DIALECT."'>".h($W),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($u))."'>".lang(175)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(178)."</a>\n";}}if(support(is_view($R)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(179)."</h2>\n";$Vn=triggers($a);if($Vn){echo"<table>\n";foreach($Vn
as$u=>$W)echo"<tr><td>".h($W[0])."<td>".h($W[1])."<th>".h($u)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($u))."'>".lang(175)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(180)."</a>\n";}}elseif(isset($_GET["schema"])){$Fn=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(71).$Fn,[lang(71)]);$hn=[];$in=[];$Ce=[];$qa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$qa,$z,PREG_SET_ORDER);foreach($z
as$p=>$y){$hn[$y[1]]=[(float)$y[2],(float)$y[3]];$in[]="\n\t'".js_escape($y[1])."': [ $y[2], $y[3] ]";}$Kn=0;$jb=-1;$Cl=[];$Vk=[];$ih=[];$Oa=Driver::get()->getAllFields();foreach(table_status('',true)as$Q=>$R){if(is_view($R))continue;$nk=0;$Cl[$Q]["fields"]=[];foreach(isset($Oa[$Q])?$Oa[$Q]:[]as$k){$nk+=1.25;$Ce[$Q][$k["field"]]=$nk;$Cl[$Q]["fields"][$k["field"]]=$k;}$Cl[$Q]["pos"]=(isset($hn[$Q])?$hn[$Q]:[$Kn,0]);foreach(Admin::get()->getForeignKeys($Q)as$W){if(!$W["db"]){$gh=$jb;if((isset($hn[$Q][1])?$hn[$Q][1]:0)||(isset($hn[$W["table"]][1])?$hn[$W["table"]][1]:0))$gh=min(floatval(isset($hn[$Q][1])?$hn[$Q][1]:0),floatval(isset($hn[$W["table"]][1])?$hn[$W["table"]][1]:0))-1;else$jb-=.1;while($ih[(string)$gh])$gh-=.0001;$Cl[$Q]["references"][$W["table"]][(string)$gh]=[$W["source"],$W["target"]];$Vk[$W["table"]][$Q][(string)$gh]=$W["target"];$ih[(string)$gh]=true;}}$Kn=max($Kn,$Cl[$Q]["pos"][0]+2.5+$nk);}echo"<div id='schema' style='height: {$Kn}em;'>\n","<script",nonce(),">\n","gid('schema').onselectstart = () => false;\n","const tablePos = {",implode(",",$in),"\n};\n","const em = gid('schema').offsetHeight / $Kn;\n","document.onmousemove = schemaMousemove;\n","document.onmouseup = partialArg(schemaMouseup, '",js_escape(DB),"');\n","</script>\n";foreach($Cl
as$_=>$Q){echo"<div class='table' style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($_).'"><b>'.h($_)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($Q["fields"]as$k){$W='<span '.type_class($k["type"]).' title="'.h($k["type"].($k["length"]?"($k[length])":"").($k["null"]?" NULL":'')).'">'.h($k["field"]).'</span>';echo"<br>".($k["primary"]?"<i>$W</i>":$W);}foreach((array)$Q["references"]as$sn=>$Xk){foreach($Xk
as$gh=>$Rk){$hh=$gh-(isset($hn[$_][1])?$hn[$_][1]:0);$p=0;foreach($Rk[0]as$tm){echo"\n<div class='references' title='",h($sn),"' id='refs$gh-$p' style='left: {$hh}em; top: ",$Ce[$_][$tm],"em; padding-top: .5em;'>","<div style='border-top: 1px solid Gray; width: ".(-$hh)."em;'></div>","</div>";$p++;}}}foreach((array)$Vk[$_]as$sn=>$Xk){foreach($Xk
as$gh=>$d){$hh=$gh-(isset($hn[$_][1])?$hn[$_][1]:0);$p=0;foreach($d
as$rn){echo"\n<div class='references' title='",h($sn),"' id='refd$gh-$p' style='left: {$hh}em; top: ".$Ce[$_][$rn]."em; height: 1.25em;'>","<svg style='width: 1em; height: 1em; float: right;' viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='height: .5em; border-bottom: 1px solid Gray; width: ".(-$hh)."em;'></div>","</div>";$p++;}}}echo"\n</div>\n";}foreach($Cl
as$_=>$Q){foreach((array)$Q["references"]as$sn=>$Xk){if($Cl[$sn]){foreach($Xk
as$gh=>$Rk){$ii=$Kn;$Ph=-10;foreach($Rk[0]as$u=>$tm){$ok=$Q["pos"][0]+$Ce[$_][$tm];$pk=$Cl[$sn]["pos"][0]+$Ce[$sn][$Rk[1][$u]];$ii=min($ii,$ok,$pk);$Ph=max($Ph,$ok,$pk);}echo"<div class='references' id='refl$gh' style='left: $gh"."em; top: $ii"."em; padding: .5em 0;'><div style='border-right: 1px solid Gray; margin-top: 1px; height: ".($Ph-$ii)."em;'></div></div>\n";}}}}echo"</div>\n","<p class='links'>","<a href='",(ME."schema=".urlencode($qa)),"' id='schema-link'>",lang(181),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$N=Admin::get()->getSettings();if($_POST){$N->updateParameters(["dumpFormat"=>$_POST["format"],"dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),"dumpDataStyle"=>$_POST["data_style"],"dumpOutput"=>$_POST["output"],]);if(DB!="")$g=[DB];else{$g=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($g))$g=explode("\n",rtrim(str_replace("\r","",$g),"\n"));}$Dl=isset($_POST["schemas"])?$_POST["schemas"]:[];$S=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($S)==1)$Sf=key($S);elseif(count($Dl)==1)$Sf=$Dl[0];elseif(count($g)==1)$Sf=$g[0];else$Sf=Admin::get()->getServerName(SERVER,true,"server");$qe=dump_headers($Sf,DB==""||$_GET["ns"]===""||count($S)>1);$Eg=preg_match('~sql~',$_POST["format"]);$Pc=$Eg&&$_POST["data_style"]&&!$_POST["table_style"]&&DIALECT!="sql";if($Eg){echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$Km=$_POST["db_style"];foreach($g
as$h){Admin::get()->dumpDatabase($h);if(Connection::get()->selectDatabase($h)){if($Eg){if($Km)echo
create_database_sql($h,$Km),use_sql($h,$Km)."\n";$Cj="";if($_POST["types"]){foreach(types()as$q=>$T){$Zd=type_values($q);if($Zd)$Cj
.=($Km!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($T).";;\n":"")."CREATE TYPE ".idf_escape($T)." AS ENUM ($Zd);\n\n";else$Cj
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$J){$_=$J["ROUTINE_NAME"];$pl=$J["ROUTINE_TYPE"];$Dc=create_routine($pl,["name"=>$_]+routine($J["SPECIFIC_NAME"],$pl));set_utf8mb4($Dc);$Cj
.=($Km!='DROP+CREATE'?"DROP $pl IF EXISTS ".idf_escape($_).";;\n":"")."$Dc;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$J){$Dc=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($J["Name"]),3));set_utf8mb4($Dc);$Cj
.=($Km!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($J["Name"]).";;\n":"")."$Dc;;\n\n";}}echo($Cj&&DIALECT=='sql'?"DELIMITER ;;\n\n$Cj"."DELIMITER ;\n\n":$Cj);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$Cl){if($Cl!="")set_schema($Cl);$nn=table_status('',true);$fn=array_keys($nn);$nd=false;if($Pc&&$fn){$Wk=[];foreach($fn
as$_){if(!is_view($nn[$_])&&(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["data"]))){foreach(foreign_keys($_)as$o)$Wk[$_][]=$o["table"];}}$sj=dump_table_order($fn,$Wk);if($sj)$fn=$sj;else$nd=function_exists('AdminNeo\foreign_key_checks_sql');}if($nd)echo
foreign_key_checks_sql(false)."\n";$Go=[];foreach($fn
as$_){$R=$nn[$_];$Q=(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["tables"]));$f=(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["data"]));if($Q||$f){$Hn=null;if($qe=="tar"){$Hn=new
TmpFile();ob_start([$Hn,'write'],1e5);}$Ec=($Q?$_POST["table_style"]:"");Admin::get()->dumpTable($_,$Ec,(is_view($R)?2:0));if(is_view($R)&&$qe!="tar")$Go[]=$_;elseif($f){$l=fields($_);Admin::get()->dumpData($_,$_POST["data_style"],"SELECT *".convert_fields($l,$l)." FROM ".table($_));if($Eg&&!$Ec&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($_);}if($Eg&&$_POST["triggers"]&&$Q&&($Vn=trigger_sql($_)))echo"\nDELIMITER ;;\n$Vn\nDELIMITER ;\n";if($qe=="tar"){ob_end_flush();tar_file((DB!=""?"":"$h/")."$_.csv",$Hn);}elseif($Eg)echo"\n";}}if($nd)echo
foreign_key_checks_sql(true)."\n";if($_POST["table_style"]&&function_exists('AdminNeo\foreign_keys_sql')){foreach($nn
as$_=>$R){$Q=(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["tables"]));if($Q&&!is_view($R))echo
foreign_keys_sql($_);}}foreach($Go
as$Eo)Admin::get()->dumpTable($Eo,$_POST["table_style"],1);if($qe=="tar")echo
pack("x512");}}}}if($Eg)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$_=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(74).": $_",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(74)]));echo"<form action='' method='post'>\n","<table class='box'>\n";$Vc=['','USE','DROP+CREATE','CREATE'];$kn=['','DROP+CREATE','CREATE'];$Qc=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$Qc[]='INSERT+UPDATE';echo"<tr><th>",lang(182),"</th><td>",html_radios("format",Admin::get()->getDumpFormats(),$N->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(30),"</th>","<td>",html_select('db_style',$Vc,$N->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("type"))echo
checkbox("types",1,$N->getParameter("dumpTypes"),lang(109));if(support("routine"))echo
checkbox("routines",1,$N->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(183));if(support("event"))echo
checkbox("events",1,$N->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(184));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(160),"</th><td>",html_select('table_style',$kn,$N->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$N->getParameter("dumpAutoIncrement"),lang(47));if(support("trigger"))echo
checkbox("triggers",1,$N->getParameter("dumpTriggers","1"),lang(179));echo"</span></td></tr>","<tr><th id='label-data'>",lang(185),"</th><td>",html_select("data_style",$Qc,$N->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(186),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$N->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n","<p>","<input type='submit' class='button default' value='",lang(74),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$uk=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly'>".lang(81)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$Cl)echo"<tr><td>".checkbox("schemas[]",$Cl,true,$Cl,"","block")."\n";}elseif(DB!=""){$Kb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$Kb class='jsonly'>".lang(8)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(185)."<input type='checkbox' id='check-data'$Kb class='jsonly'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Go="";$mn=tables_list();foreach($mn
as$_=>$T){$tk=preg_replace('~_.*~','',$_);$Kb=($a==""||$a==(substr($a,-1)=="%"?"$tk%":$_));$yk="<tr><td>".checkbox("tables[]",$_,$Kb,$_,"","block");if($T!==null&&!preg_match('~table~i',$T))$Go
.="$yk\n";else
echo"$yk<td class='right'><label class='block'><span id='Rows-".h($_)."'></span>".checkbox("data[]",$_,$Kb)."</label>\n";$uk[$tk]++;}echo$Go;if($mn)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$g=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($g?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(30)."</label>","</thead>\n";if($g){foreach($g
as$h){if(!information_schema($h)){$tk=preg_replace('~_.*~','',$h);echo"<tr><td>".checkbox("databases[]",$h,$a==""||$a=="$tk%",$h,"","block")."\n";$uk[$tk]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$sh=[];foreach($uk
as$u=>$W){if($u!=""&&$W>1)$sh[]="<a href='".h(ME)."dump=".urlencode("$u%")."'>".icon("check").h($u)."*</a>";}if($sh)echo"<p class='links'>",implode("",$sh),"</p>\n";}elseif(isset($_GET["privileges"])){$Fn=DB!=""?h(": ".DB):"";page_header(lang(72).$Fn,[lang(72)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(187),"</a></p>\n";$H=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$of=$H;if(!$H)$H=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$of)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(28)."<th>".lang(5)."<th></thead>\n";while($J=$H->fetchAssoc())echo'<tr><td>'.h($J["User"])."<td>".h($J["Host"]).'<td><a href="'.h(ME.'user='.urlencode($J["User"]).'&host='.urlencode($J["Host"])).'">'.lang(38)."</a>\n";if(!$of||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'><td><input class='input' name='host' value='localhost' autocapitalize='off'><td><input type='submit' class='button' value='".lang(38)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){$N=Admin::get()->getSettings();if($_POST["export"]){$N->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Jf=&get_session("queries");$If=&$Jf[DB];if($_POST["clear"]){$If=[];redirect(remove_from_uri("history"));}stop_session();$En=isset($_GET["import"])?lang(73):lang(40);page_header($En,[$En]);$ph="--".(DIALECT=="sql"?" ":"");if($_POST){$df=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$Xf=Admin::get()->getImportFilePath();if($Xf){if(file_exists($Xf))$df=fopen($Xf,"rb");elseif(file_exists("$Xf.gz"))$df=fopen("compress.zlib://$Xf.gz","rb");}$F=$df?fread($df,1e6):false;}else$F=get_file("sql_file",true,";");if(is_string($F)){if(($Vh=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Vh,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$Gk=$F.(preg_match("~;[ \t\r\n]*\$~",$F)?"":";");if(!$If||first(end($If))!=$Gk){restart_session();$If[]=[$Gk,time()];set_session("queries",$Jf);stop_session();}}$vm="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$ph)[^\n]*\n?|--\r?\n)";$fd=";";$gd=1;$A=0;$Qd=true;$sc=connect();if($sc&&DB!=""){$sc->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$sc);}$fc=0;$be=[];$Nj='[\'"'.(DIALECT=="sql"?'`#':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$ph.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Ln=microtime(true);$Gd=Admin::get()->getDumpFormats();unset($Gd["sql"]);while($F!=""){if(!$A&&preg_match("~^$vm*+DELIMITER\\s+(\\S+)~i",$F,$y)){$fd=preg_quote($y[1]);$gd=strlen($y[1]);$Ze=Admin::get()->formatSqlCommandQuery(trim($y[0]));if($Ze!="")echo"<pre><code class='jush-".DIALECT."'>$Ze</code></pre>\n";$F=substr($F,strlen($y[0]));}elseif(!$A&&DIALECT=="pgsql"&&preg_match("~^($vm*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$y)){$fd="\n\\\\\\.\r?\n";$gd=3;$A=strlen($y[0]);}else{preg_match("($fd\\s*|$Nj)",$F,$y,PREG_OFFSET_CAPTURE,$A);list($bf,$nk)=$y[0];if(!$bf&&$df&&!feof($df))$F
.=fread($df,1e5);else{if(!$bf&&rtrim($F)=="")break;$A=$nk+strlen($bf);if($bf&&!preg_match("(^$fd)",$bf)){$yb=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($nk>0&&strtolower($F[$nk-1])=="e"));$ck='(';if($bf=='/*')$ck
.='\*/';elseif($bf=='[')$ck
.=']';elseif(preg_match("~^$ph|^#~",$bf))$ck
.="\n";else$ck
.=preg_quote($bf).($yb?"|\\\\.":"");$ck
.='|$)s';while(preg_match($ck,$F,$y,PREG_OFFSET_CAPTURE,$A)){$vl=$y[0][0];if(!$vl&&$df&&!feof($df))$F
.=fread($df,1e5);else{$A=$y[0][1]+strlen($vl);if(!isset($vl[0])||$vl[0]!="\\")break;}}}else{$Qd=false;$Gk=substr($F,0,$nk+$gd);$fc++;$yk="<pre id='sql-$fc'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($Gk))."</code></pre>\n";if(DIALECT=="sqlite"&&preg_match("~^$vm*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Gk,$y)!==0){echo$yk,"<p class='error'>".lang(188,preg_match('~ATTACH~i',$y[1])?'ATTACH':'VACUUM INTO')."\n";$be[]=" <a href='#sql-$fc'>$fc</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$yk;ob_flush();flush();}$Dm=microtime(true);if(Connection::get()->multiQuery($Gk)&&is_object($sc)&&preg_match("~^$vm*+USE\\b~i",$Gk))$sc->query($Gk);do{$H=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$yk:""),"<p class='error'>",lang(189),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$be[]=" <a href='#sql-$fc'>$fc</a>";if($_POST["error_stops"])break
2;}else{$Bn=" <span class='time'>(".format_time($Dm).")</span>";$Kd=(strlen($Gk)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Gk))."'>".icon("edit").lang(38)."</a>":"");$Lk=Connection::get()->getQueryInfo();$Ea=Connection::get()->getAffectedRows();$Mo=($_POST["only_errors"]?null:Driver::get()->warnings());$Oo="warnings-$fc";$Po=$Mo?"<a href='#$Oo' class='toggle'>".lang(39).icon_chevron_down()."</a>":null;$le=$vj=null;$me="explain-$fc";$ne=false;$oe="export-$fc";$w=0;if(is_object($H)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$w=(int)$_POST["limit"];$vj=print_select_result($H,$sc,[],$w);if(!$_POST["only_errors"]){echo"<p class='links'>";$Li=$H->getRowsCount();echo($Li?($w&&$Li>$w?lang(190,$w):"").lang(191,$Li):""),$Bn,$Kd,$Po;if($sc&&preg_match("~^($vm|\\()*+SELECT\\b~i",$Gk)&&($le=explain($sc,$Gk)))echo"<a href='#$me' class='toggle'>Explain".icon_chevron_down()."</a>";$ne=true;echo"<a href='#$oe' class='toggle'>".lang(74).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$vm*+(CREATE|DROP|ALTER)$vm++(DATABASE|SCHEMA)\\b~i",$Gk)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($Lk)."'>",lang(192,$Ea),"$Bn $Kd";if($Po)echo", $Po";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($Mo)echo"<div id='$Oo' class='hidden'>\n$Mo</div>\n";if($le){echo"<div id='$me' class='hidden explain'>\n";print_select_result($le,$sc,$vj);echo"</div>\n";}if($ne){echo"<form id='$oe' action='' method='post' class='hidden'><p>\n",html_select("format",$Gd,$N->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$N->getParameter("exportOutput"))." ",input_hidden("query",$Gk),input_token()," <input type='submit' class='button' name='export' value='".lang(74)."'>";if(!$w)echo
script("qsl('input').onclick = partial(sqlExport, '".js_escape(ME)."set=export-settings');","");echo"</p></form>\n";}if(is_object($H)&&!$_POST["only_errors"])echo"</div>\n";}$Dm=microtime(true);}while(Connection::get()->nextResult());}$F=substr($F,$A);$A=0;}}}}if($Qd)echo"<p class='message'>".lang(193)."\n";elseif($_POST["only_errors"]){$Ti=$fc-count($be);echo"<p class='".($Ti?"message":"error")."'>".lang(194,$fc-count($be))," <span class='time'>(".format_time($Ln).")</span>\n";}elseif($be&&$fc>1)echo"<p class='error'>".lang(189).": ".implode("",$be)."\n";}else
echo"<p class='error'>".upload_error($F)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$Gk=$_GET["sql"];if($_POST)$Gk=$_POST["query"];elseif($_GET["history"]=="all")$Gk=$If;elseif($_GET["history"]!="")$Gk=$If[$_GET["history"]][0];echo"<p>";textarea("query",$Gk,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(195)."' title='Ctrl+Enter'>",lang(196).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(197)."</legend><div class='fieldset-content'>";$wf=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$wf (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(195)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(198);echo"</div></fieldset>\n";$Xf=Admin::get()->getImportFilePath();if($Xf)echo"<fieldset><legend>".lang(199)."</legend><div class='fieldset-content'>",lang(200,"<code>".h($Xf)."$wf</code>"),' <input type="submit" class="button default" name="webfile" value="'.lang(201).'">',"</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(202)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(203)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$If){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(204),"history",$_GET["history"]!="");for($W=end($If);$W;$W=prev($If)){$u=key($If);list($Gk,$Bn,$Od)=$W;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$ph).*~m",'',$Gk))))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$u")."'>".icon("edit").lang(38)."</a>"," <span class='time' title='".@date('Y-m-d',$Bn)."'>".@date("H:i:s",$Bn).($Od?" ($Od)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(205)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(206),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$l=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$l):""):where($_GET,$l));$lo=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($l
as$_=>$k){if((!$lo&&!isset($k["privileges"]["insert"]))||Admin::get()->getFieldName($k)=="")unset($l[$_]);}if($_POST&&!isset($_GET["select"])){$Ah=$_POST["referer"];if($_POST["insert"])$Ah=($lo?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Ah))$Ah=ME."select=".urlencode($a);$t=indexes($a);$fo=unique_array(isset($_GET["where"])?$_GET["where"]:[],$t);$Mk="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Ah,lang(207),(bool)Driver::get()->delete($a,$Mk,$fo?0:1));else{$gm=[];foreach($l
as$_=>$k){$W=process_input($k);if($W!==false&&$W!==null)$gm[idf_escape($_)]=$W;}if($lo){if(!$gm)redirect($Ah);queries_redirect($Ah,lang(208),(bool)Driver::get()->update($a,$gm,$Mk,$fo?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$H=Driver::get()->insert($a,$gm);$eh=($H?last_id($H):0);queries_redirect($Ah,lang(209,($eh?" $eh":"")),(bool)$H);}}}$J=null;if($Z){$L=[];foreach($l
as$_=>$k){if(isset($k["privileges"]["select"])){$Va=($_POST["clone"]&&$k["auto_increment"]?"''":convert_field($k));$L[]=($Va?"$Va AS ":"").idf_escape($_);}}$J=[];if(!support("table"))$L=["*"];if($L){$H=Driver::get()->select($a,$L,[$Z],$L,[],(isset($_GET["select"])?2:1));if(!$H)Admin::get()->addError(error());else{$J=$H->fetchAssoc();if(!$J)$J=false;}if(isset($_GET["select"])&&(!$J||$H->fetchAssoc()))$J=null;}}if(!support("table")&&!$l){if(!$Z){$H=Driver::get()->select($a,["*"],[],["*"]);$J=($H?$H->fetchAssoc():false);if(!$J)$J=[Driver::get()->primary=>""];}if($J){foreach($J
as$u=>$W){if(!$Z)$J[$u]=null;$l[$u]=["field"=>$u,"null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false){$qk=[];foreach((isset($_POST["fields"])?$_POST["fields"]:[])as$u=>$W)$qk[bracket_escape($u,true)]=$W;$J=$qk+($J?:[]);}if($_POST["edit"]){$Md=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});}else$Md=$l;edit_form($a,$Md,$J,$lo);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Tj=Driver::get()->getPartitionBy();$Xj=$Tj?Driver::get()->getPartitionsInfo($a):[];$Tk=referencable_primary($a);$We=[];foreach($Tk
as$en=>$k)$We[str_replace("`","``",$en)."`".str_replace("`","``",$k["field"])]=$en;$yj=[];$R=[];if($a!=""){$yj=fields($a);$R=table_status1($a);if(count($R)<2)Admin::get()->addError(lang(78));}$J=$_POST;$J["fields"]=(array)$J["fields"];if($J["auto_increment_col"])$J["fields"][$J["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($J["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(210),drop_tables([$a]));else{$l=[];$Oa=[];$qo=false;$Ue=[];$xj=reset($yj);$Ha=" FIRST";foreach($J["fields"]as$u=>$k){$o=$We[$k["type"]];$Zn=($o!==null?$Tk[$o]:$k);if($k["field"]!=""){if(!$k["generated"])$k["default"]=null;$Dk=process_field($k,$Zn);$Oa[]=[$k["orig"],$Dk,$Ha];if(!$xj||$Dk!==process_field($xj,$xj)){$l[]=[$k["orig"],$Dk,$Ha];if($k["orig"]!=""||$Ha)$qo=true;}if($o!==null)$Ue[idf_escape($k["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$We[$k["type"]],'source'=>[$k["field"]],'target'=>[$Zn["field"]],'on_delete'=>$k["on_delete"],]);$Ha=" AFTER ".idf_escape($k["field"]);}elseif($k["orig"]!=""){$qo=true;$l[]=[$k["orig"]];}if($k["orig"]!=""){$xj=next($yj);if(!$xj)$Ha="";}}$Vj=[];if(in_array($J["partition_by"],$Tj)){foreach($J
as$u=>$W){if(preg_match('~^partition~',$u))$Vj[$u]=$W;}foreach($Vj["partition_names"]as$u=>$_){if($_===""){unset($Vj["partition_names"][$u]);unset($Vj["partition_values"][$u]);}}$Vj["partition_names"]=array_values($Vj["partition_names"]);$Vj["partition_values"]=array_values($Vj["partition_values"]);if($Vj==$Xj)$Vj=[];}elseif(str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned"))$Vj=null;$Xh=lang(211);if($a==""){cookie("neo_engine",isset($J["Engine"])?$J["Engine"]:"");$Xh=lang(212);}$_=trim($J["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($_),$Xh,alter_table($a,$_,(DIALECT=="sqlite"&&($qo||$Ue)?$Oa:$l),$Ue,($J["Comment"]!=$R["Comment"]?$J["Comment"]:null),($J["Engine"]&&$J["Engine"]!=$R["Engine"]?$J["Engine"]:""),($J["Collation"]&&$J["Collation"]!=$R["Collation"]?$J["Collation"]:""),($J["Auto_increment"]!=""?number($J["Auto_increment"]):""),$Vj));}}if($a!="")page_header(lang(35).": ".h($a),["table"=>$a,lang(35)]);else
page_header(lang(77),[lang(77)]);if(!$_POST){$bo=Driver::get()->getTypes();$J=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($bo["int"])?"int":(isset($bo["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$J=$R;$J["name"]=$a;$J["fields"]=[];if(!$_GET["auto_increment"])$J["Auto_increment"]="";foreach($yj
as$k){$k["generated"]=$k["generated"]?:(isset($k["default"])?"DEFAULT":"");$J["fields"][]=$k;}if($Tj){$J+=$Xj;$J["partition_names"][]="";$J["partition_values"][]="";}}}$Og=[];if($J["Collation"])$Og[$J["Collation"]]=true;foreach($J["fields"]as$k){if($k["collation"])$Og[$k["collation"]]=true;}$Wb=Admin::get()->getCollations(array_keys($Og));$Xd=Driver::get()->engines();foreach($Xd
as$Wd){if(!strcasecmp($Wd,$J["Engine"])){$J["Engine"]=$Wd;break;}}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(213),": ","<input class='input' name='name' data-maxlength='64' value='",h($J["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($Xd)echo" ",html_select("Engine",[""=>"(".lang(214).")"]+$Xd,$J["Engine"]),help_script_command("value",true);if($Wb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(93).")"]+$Wb,$J["Collation"]);echo" <input type='submit' class='button default' value='",lang(115),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($J["fields"],$Wb,"TABLE",$We);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(47),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($J["Auto_increment"]),"'>";$kc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$hc=$kc?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$kc,lang(46),"editingCommentsClick(this, ".(support("move_col")?7:6).");","jsonly")," ";if(preg_match('~\n~',$J["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($hc?" class='$hc'":""),">",h($J["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($J["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $hc'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(lang(215,$a)),"</p>\n";if($Tj&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$Uj=preg_match('~RANGE|LIST~',$J["partition_by"]);print_fieldset_start("partition",lang(216),"split",(bool)$J["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$Tj),$J["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($J["partition"]),"'>) ",lang(49),": ","<input type='number' name='partitions' class='input size ",($Uj||!$J["partition_by"]?"hidden":""),"' value='",h($J["partitions"]),"'>","</p>\n","<table id='partition-table'",($Uj?"":" class='hidden'"),">\n","<thead><tr><th>",lang(217),"</th><th>",lang(51),"</th></tr></thead>\n";foreach($J["partition_names"]as$u=>$W){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($W),"' autocapitalize='off'>";if($u==count($J["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($J["partition_values"][$u])?$J["partition_values"][$u]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$eg=["PRIMARY","UNIQUE","INDEX"];$R=table_status1($a,true);$bg=Driver::get()->getIndexAlgorithms($R);$e=Connection::get();$Gh=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($Gh?"10.0.5":"5.6")?'|InnoDB':'').'~i',$R["Engine"]))$eg[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($Gh?"10.2.2":"5.7")?'|InnoDB':'').'~i',$R["Engine"]))$eg[]="SPATIAL";if($Gh&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$eg[]="VECTOR";$t=indexes($a);$l=fields($a);$E=[];if(DIALECT=="mongo"){$E=$t["_id_"];unset($eg[0]);unset($t["_id_"]);}$J=$_POST;if($J){$N=Admin::get()->getSettings();if($N->getParameter("indexOptions")!==null)$N->updateParameter("indexOptions",null);}if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$b=[];foreach($J["indexes"]as$s){$_=$s["name"];if(in_array($s["type"],$eg)){$d=[];$mh=[];$jd=[];$hj=[];$ag=$bg?(in_array($s["algorithm"],$bg)?$s["algorithm"]:first($bg)):"";$cg=(support("partial_indexes")?$s["partial"]:"");$gm=[];ksort($s["columns"]);foreach($s["columns"]as$u=>$c){if($c!=""){$v=isset($s["lengths"][$u])?$s["lengths"][$u]:null;$hd=isset($s["descs"][$u])?$s["descs"][$u]:null;$gj=isset($s["opclasses"][$u])?$s["opclasses"][$u]:null;$gm[]=($l[$c]?idf_escape($c):$c).($v?"(".(+$v).")":"").($gj!=""?" ".idf_escape($gj):"").($hd?" DESC":"");$d[]=$c;$mh[]=($v?:null);$jd[]=$hd;$hj[]="$gj";}}$je=$t[$_];if($je){ksort($je["columns"]);ksort($je["lengths"]);ksort($je["descs"]);if($s["type"]==$je["type"]&&array_values($je["columns"])===$d&&(!$je["lengths"]||array_values($je["lengths"])===$mh)&&array_values($je["descs"])===$jd&&(!$je["opclasses"]||array_values($je["opclasses"])===$hj)&&(!$bg||$je["algorithm"]===$ag)&&$je["partial"]==$cg){unset($t[$_]);continue;}}if($d)$b[]=[$s["type"],$_,$gm,$ag,$cg];}}foreach($t
as$_=>$je)$b[]=[$je["type"],$_,"DROP"];if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(218),alter_indexes($a,$b));}page_header(lang(172),["table"=>$a,lang(172)],h($a));$Ee=array_keys($l);if($_POST["add"]){foreach($J["indexes"]as$u=>$s){if($s["columns"][count($s["columns"])]!="")$J["indexes"][$u]["columns"][]="";}$s=end($J["indexes"]);if($s["type"]||array_filter($s["columns"],'strlen'))$J["indexes"][]=["columns"=>[1=>""]];}if(!$J){foreach($t
as$u=>$s){$t[$u]["name"]=$u;$t[$u]["columns"][]="";}$t[]=["columns"=>[1=>""]];$J["indexes"]=$t;}$mh=(DIALECT=="sql"||DIALECT=="mssql");$hj=Driver::get()->getIndexOpclasses();if($_POST)$km=$_POST["options"];else{$km=false;foreach($t
as$s){if(array_filter(isset($s["lengths"])?$s["lengths"]:[])||array_filter(isset($s["descs"])?$s["descs"]:[])||array_filter(isset($s["opclasses"])?$s["opclasses"]:[])||(isset($s["partial"])?$s["partial"]:"")!=""){$km=true;break;}}}echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(219),"</th>";$nj="class='idxopts".($km?"":" hidden")."'";if(count($bg)>1)echo"<th id='label-method' $nj>",lang(220),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types','pgsql'=>'indexes-types.html',]),"</th>";echo"<th><input type='submit' hidden>",lang(52).($mh?"<span $nj> (".lang(53).")</span>":"");if($mh||support("descidx"))echo
checkbox("options",1,$km,lang(99),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(221),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $nj>",lang(54),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(100),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($E){echo"<tr><td>PRIMARY<td>";foreach($E["columns"]as$c)echo
select_input(" disabled",$Ee,$c),"<label><input type='checkbox' disabled>".lang(62)."</label> ";echo"<td><td>\n";}$Kg=1;foreach($J["indexes"]as$s){if(!$_POST["drop_col"]||$Kg!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$Kg][type]",[-1=>""]+$eg,$s["type"],($Kg==count($J["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($bg)>1)echo"<td $nj>",html_select("indexes[$Kg][algorithm]",array_merge([""],$bg),$s['algorithm'],"label-method"),"</td>";echo"<td>";ksort($s["columns"]);$p=1;foreach($s["columns"]as$u=>$c){echo"<span>".select_input(" name='indexes[$Kg][columns][$p]' title='".lang(43)."'",($l&&($c==""||$l[$c])?array_combine($Ee,$Ee):[]),$c,"partial(".($p==count($s["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $nj>";if($mh)echo"<input type='number' name='indexes[$Kg][lengths][$p]' class='input size' value='".(h(isset($s["lengths"][$u])?$s["lengths"][$u]:"")),"' title='".lang(98),"'>";if($hj){$gj=isset($s["opclasses"][$u])?$s["opclasses"][$u]:"";echo
html_select("indexes[$Kg][opclasses][$p]",[""=>"(".lang(222).")"]+array_combine($hj,$hj)+($gj!=""?[$gj=>$gj]:[]),$gj),doc_link(['pgsql'=>'indexes-opclass.html']);}if(support("descidx"))echo
checkbox("indexes[$Kg][descs][$p]",1,isset($s["descs"][$u])?$s["descs"][$u]:false,lang(62));echo"<br></span></span>";$p++;}echo"</td>","<td><input name='indexes[$Kg][name]' value='",h($s["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $nj><input name='indexes[$Kg][partial]' value='".h($s["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$Kg]' value='1' title='",h(lang(58)),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$Kg++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$J=$_POST;if($_POST&&!isset($_POST["add_x"])){$_=trim($J["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(223),drop_databases([DB]));}elseif(DB!==$_){if(DB!=""){$_GET["db"]=$_;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($_),lang(224),rename_database($_,$J["collation"]));}else{$g=explode("\n",str_replace("\r","",$_));$Mm=true;$dh="";foreach($g
as$h){if(count($g)==1||$h!=""){if(!create_database($h,$J["collation"]))$Mm=false;$dh=$h;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($dh),lang(225),$Mm);}}else{if(!$J["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($_).(preg_match('~^[a-z0-9_]+$~i',$J["collation"])?" COLLATE $J[collation]":""),substr(ME,0,-1),lang(226));}}if(DB!="")page_header(lang(69).": ".h(DB),[lang(69)]);else
page_header(lang(75),[lang(75)]);$_=DB;if($_POST)$_=$J["name"];elseif(DB!="")$J["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$of){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$of,$y)&&$y[1]){$_=stripcslashes(idf_unescape("`$y[2]`"));break;}}}$Wb=Admin::get()->getCollations($J["collation"]?[$J["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($_,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($_),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($_),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Wb)echo
html_select("collation",[""=>"(".lang(93).")"]+$Wb,$J["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",]),"\n";echo"<input type='submit' class='button default' value='",lang(115),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(164)."'>".confirm(lang(215,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",h(lang(100)),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["scheme"])){$J=$_POST;if($_POST){$x=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$x,lang(227));else{$_=trim($J["name"]);$x
.=urlencode($_);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($_),$x,lang(228));elseif($_GET["ns"]!=$_)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($_),$x,lang(229));else
redirect($x);}}if($_GET["ns"]!="")page_header(lang(70).": ".h($_GET["ns"]),[lang(70)]);else
page_header(lang(76),[lang(76)]);if(!$J)$J["name"]=$_GET["ns"];echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' id='name' value='",h($J["name"]),"' autocapitalize='off' autofocus>","<input type='submit' class='button default' value='",lang(115),"'>";if($_GET["ns"]!="")echo"<input type='submit' class='button' name='drop' value='".lang(164)."'>".confirm(lang(215,$_GET["ns"]))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$pa=$_GET["name"]?:$_GET["call"];page_header(lang(230).": ".h($pa),[lang(230)]);$pl=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Yf=[];$Cj=[];foreach($pl["fields"]as$p=>$k){if(substr($k["inout"],-3)=="OUT"&&DIALECT=='sql')$Cj[$p]="@".idf_escape($k["field"])." AS ".idf_escape($k["field"]);if(!$k["inout"]||substr($k["inout"],0,2)=="IN")$Yf[]=$p;}if($_POST){$_b=[];foreach($pl["fields"]as$u=>$k){$W="";if(in_array($u,$Yf)){$W=process_input($k);if($W===false)$W="''";if(isset($Cj[$u]))Connection::get()->query("SET @".idf_escape($k["field"])." = $W");}if(isset($Cj[$u]))$_b[]="@".idf_escape($k["field"]);elseif(in_array($u,$Yf))$_b[]=$W;}$F=(isset($_GET["callf"])?"SELECT ":"CALL ").($pl["returns"]&&$pl["returns"]["type"]=="record"?"* FROM ":"").table($pa)."(".implode(", ",$_b).")";$Dm=microtime(true);$H=Connection::get()->multiQuery($F);$Ea=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($F,$Dm,!$H);if(!$H)echo"<p class='error'>".error()."\n";else{$sc=connect();if($sc)$sc->selectDatabase(DB);do{$H=Connection::get()->storeResult();if(is_object($H))print_select_result($H,$sc);else
echo"<p class='message'>".lang(231,$Ea)." <span class='time'>".@date("H:i:s")."</span>\n";}while(Connection::get()->nextResult());if($Cj)print_select_result(Connection::get()->query("SELECT ".implode(", ",$Cj)));}}echo"<form action='' method='post'>\n";if($Yf){echo"<table class='box'>\n";foreach($Yf
as$u){$k=$pl["fields"][$u];$_=$k["field"];echo"<tr><th>".Admin::get()->getFieldName($k);$X=isset($_POST["fields"][$_])?$_POST["fields"][$_]:"";if($X!=""){if($k["type"]=="set")$X=implode(",",$X);}input($k,$X,(string)(isset($_POST["function"][$_])?$_POST["function"][$_]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(230),"'>\n",input_token(),"</p>\n","</form>\n";$gc=$pl["comment"];if($gc!==null&&$gc!==""){$gc=h(trim($pl["comment"],"\n"));if(preg_match('~^ +~',$gc,$z)){preg_match_all("~^($z[0]|$)~m",$gc,$qh);if(count($qh[0])==substr_count($gc,"\n"))$gc=preg_replace("~^($z[0])~m","",$gc);}$gc=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$gc);echo"<pre class='comment'>$gc</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$_=$_GET["name"];$J=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$J["source"]=array_filter($J["source"],'strlen');ksort($J["source"]);$rn=[];foreach($J["source"]as$u=>$W)$rn[$u]=$J["target"][$u];$J["target"]=$rn;}if(DIALECT=="sqlite")$H=recreate_table($a,$a,[],[],[" $_"=>($J["drop"]?"":" ".format_foreign_key($J))]);else{$b="ALTER TABLE ".table($a);$H=($_==""||queries("$b DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($_)));if(!$J["drop"])$H=queries("$b ADD".format_foreign_key($J));}queries_redirect(ME."table=".urlencode($a),($J["drop"]?lang(232):($_!=""?lang(233):lang(234))),(bool)$H);if(!$J["drop"])Admin::get()->addError(lang(235));}page_header(lang(236).": ".h($a),["table"=>$a,lang(236)]);if($_POST){ksort($J["source"]);if($_POST["change"]||$_POST["change-js"])$J["target"]=[];else$J["source"][]="";}elseif($_!=""){$We=foreign_keys($a);$J=$We[$_];$J["source"][]="";}else{$J["table"]=$a;$J["source"]=[""];}echo"<form action='' method='post'>\n";$tm=array_keys(fields($a));if($J["db"]!="")Connection::get()->selectDatabase($J["db"]);if($J["ns"]!=""){$zj=get_schema();set_schema($J["ns"]);}$Sk=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$rn=array_keys(fields(in_array($J["table"],$Sk)?$J["table"]:reset($Sk)));$cj="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(237),":</span> ",html_select("table",$Sk,$J["table"],$cj,"label-table");if(support("scheme")){$Dl=array_filter(Admin::get()->getSchemas(),function($Cl){return!preg_match('~^information_schema$~i',$Cl);});echo"<span id='label-schema'>",lang(81),":</span> ",html_select("ns",$Dl,$J["ns"]!=""?$J["ns"]:$_GET["ns"],$cj,"label-schema");if($J["ns"]!="")set_schema($zj);}elseif(DIALECT!="sqlite"){$Wc=[];foreach(Admin::get()->getDatabases()as$h){if(!information_schema($h))$Wc[]=$h;}echo"<span id='label-db'>",lang(238),":</span> ",html_select("db",$Wc,$J["db"]!=""?$J["db"]:$_GET["db"],$cj,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(239),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(173),"<th id='label-target'>",lang(174),"</thead>\n";$Kg=0;foreach($J["source"]as$u=>$W){echo"<tr>","<td>".html_select("source[".(+$u)."]",[-1=>""]+$tm,$W,($Kg==count($J["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$u)."]",$rn,isset($J["target"][$u])?$J["target"][$u]:null,"","label-target");$Kg++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(240),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(95),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$J["on_delete"],"","label-delete"),"<span id='label-update'>".lang(94),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$J["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$J["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($_!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(lang(215,$_));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$J=$_POST;$_j="VIEW";if(DIALECT=="pgsql"&&$a!=""){$O=table_status1($a);$_j=strtoupper($O["Engine"]);}if($_POST){$_=trim($J["name"]);$Va=" AS\n$J[select]";$Ah=ME."table=".urlencode($_);$Xh=lang(241);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$_&&DIALECT!="sqlite"&&$T=="VIEW"&&$_j=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($_).$Va,$Ah,$Xh);else{$tn=$_."_adminneo_".uniqid();drop_create("DROP $_j ".table($a),"CREATE $T ".table($_).$Va,"DROP $T ".table($_),"CREATE $T ".table($tn).$Va,"DROP $T ".table($tn),($_POST["drop"]?substr(ME,0,-1):$Ah),lang(242),$Xh,lang(243),$a,$_);}}if(!$_POST&&$a!=""){$J=view($a);$J["name"]=$a;$J["materialized"]=($_j!="VIEW");if($j=error())Admin::get()->addError($j);}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(244),[lang(244)]);echo"<form action='' method='post'>\n","<p>",lang(221),":","<input class='input' name='name' value='",h($J["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$J["materialized"],lang(166));echo"</p>\n<p>";textarea("select",$J["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>\n",confirm(lang(215,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$tg=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Gm=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$J=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(245));elseif(in_array($J["INTERVAL_FIELD"],$tg)&&isset($Gm[$J["STATUS"]])){$Bl="\nON SCHEDULE ".($J["INTERVAL_VALUE"]?"EVERY ".q($J["INTERVAL_VALUE"])." $J[INTERVAL_FIELD]".($J["STARTS"]?" STARTS ".q($J["STARTS"]):"").($J["ENDS"]?" ENDS ".q($J["ENDS"]):""):"AT ".q($J["STARTS"]))." ON COMPLETION".($J["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(246):lang(247)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Bl.($ea!=$J["EVENT_NAME"]?"\nRENAME TO ".idf_escape($J["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($J["EVENT_NAME"]).$Bl)."\n".$Gm[$J["STATUS"]]." COMMENT ".q($J["EVENT_COMMENT"]).rtrim(" DO\n$J[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(248).": ".h($ea),[lang(248)]);else
page_header(lang(249),[lang(249)]);if(!$J&&$ea!=""){$K=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$J=reset($K);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(221),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($J["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(250),"</th><td>","<input class='input' name='STARTS' value='",h("$J[EXECUTE_AT]$J[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(251),"</th><td>","<input class='input' name='ENDS' value='",h($J["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(252),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($J["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$tg,$J["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(156),"</th><td>",html_select("STATUS",$Gm,$J["STATUS"]),"</td></tr>\n","<tr><th>",lang(46),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($J["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$J["ON_COMPLETION"]=="PRESERVE",lang(253)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$J["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(lang(215,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$pa=($_GET["name"]?:$_GET["procedure"]);$pl=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$J=$_POST;$J["fields"]=(array)$J["fields"];if($_POST&&!process_fields($J["fields"])){foreach($J["fields"]as$u=>$k){if($k["field"]=="")unset($J["fields"][$u]);}$Xi=routine_id($pa,routine($_GET["procedure"],$pl));$Bi=routine_id($J["name"],$J);$Dc=create_routine($pl,$J);$Ah=substr(ME,0,-1);$Xh=lang(254);if(!$_POST["drop"]&&$Xi==$Bi&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($Dc,' OR REPLACE',6,0),$Ah,$Xh);else{$tn="$J[name]_adminer_".uniqid();drop_create("DROP $pl $Xi",$Dc,"DROP $pl $Bi",create_routine($pl,["name"=>$tn]+$J),"DROP $pl ".routine_id($tn,$J),$Ah,lang(255),$Xh,lang(256),$pa,$J["name"]);}}if($pa!=""){$En=isset($_GET["function"])?lang(257):lang(258);page_header($En.": ".h($pa),[$En]);}else{$En=isset($_GET["function"])?lang(259):lang(260);page_header($En,[$En]);}if(!$_POST){if($pa=="")$J["language"]="sql";else{$J=routine($_GET["procedure"],$pl);$J["name"]=$pa;}}$Fb=get_vals("SHOW CHARACTER SET");sort($Fb);$ql=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(221),": ","<input class='input' name='name' value='",h($J["name"]),"' data-maxlength='64' autocapitalize='off'>";if($ql)echo"<span id='label-language'>",lang(9),":</span> ",html_select("language",$ql,$J["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(115),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($J["fields"],$Fb,$pl);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(261),"</th>";edit_type("returns",(array)$J["returns"],$Fb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$J["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($pa!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(lang(215,$pa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["sequence"])){$ra=$_GET["sequence"];$J=$_POST;if($_POST){$x=substr(ME,0,-1);$_=trim($J["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($ra),$x,lang(262));elseif($ra=="")query_redirect("CREATE SEQUENCE ".idf_escape($_),$x,lang(263));elseif($ra!=$_)query_redirect("ALTER SEQUENCE ".idf_escape($ra)." RENAME TO ".idf_escape($_),$x,lang(264));else
redirect($x);}if($ra!="")page_header(lang(265).": ".h($ra),[h($ra)]);else
page_header(lang(266),[lang(267)]);if(!$J)$J["name"]=$ra;echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' value='",h($J["name"]),"' autocapitalize='off'>","<input type='submit' class='button default' value='",lang(115),"'>";if($ra!="")echo"<input type='submit' class='button' name='drop' value='".lang(164)."'>".confirm(lang(215,$ra))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["type"])){$sa=$_GET["type"];$J=$_POST;if($_POST){$x=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($sa),$x,lang(268));else
query_redirect("CREATE TYPE ".idf_escape(trim($J["name"]))." $J[as]",$x,lang(269));}if($sa!="")page_header(lang(270).": ".h($sa),[h($sa)]);else
page_header(lang(267),[lang(267)]);if(!$J)$J["as"]="AS ";echo"<form action='' method='post'>\n";if($sa!=""){$bo=Driver::get()->getTypes();$Zd=type_values($bo[$sa]);if($Zd)echo"<p><code class='jush-".DIALECT."'>ENUM (".h($Zd).")</code><p>\n";echo"<p>","<input type='submit' class='button' name='drop' value='".lang(164)."'>".confirm(lang(215,$sa)),"</p>\n";}else{echo"<p>",lang(221).": <input class='input' name='name' value='".h($J['name'])."' autocapitalize='off'>\n",doc_link(['pgsql'=>"datatype-enum.html",],"?"),"</p>\n<p>";textarea("as",$J["as"]);echo"</p>\n","<p><input type='submit' class='button default' value='".lang(115)."'></p>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$_=$_GET["name"];$J=$_POST;if($J){if(DIALECT=="sqlite")$Mm=recreate_table($a,$a,[],[],[],"",[],"$_",($J["drop"]?"":$J["clause"]));else{$Mm=($_==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($_)));if(!$J["drop"])$Mm=(bool)queries("ALTER TABLE ".table($a)." ADD".($J["name"]!=""?" CONSTRAINT ".idf_escape($J["name"]):"")." CHECK ($J[clause])");}queries_redirect(ME."table=".urlencode($a),($J["drop"]?lang(271):($_!=""?lang(272):lang(273))),$Mm);}page_header(($_!=""?lang(274).": ".h($_):lang(178)),["table"=>$a]);if(!$J){$Lb=Driver::get()->checkConstraints($a);$J=["name"=>$_,"clause"=>$Lb[$_]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(221).': <input name="name" value="'.h($J["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",],"?"),"</p>\n<p>";textarea("clause",$J["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($_!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(lang(215,$_));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$_=isset($_GET["name"])?$_GET["name"]:"";$Un=trigger_options();$J=trigger($_,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$Un["Timing"])&&in_array($_POST["Event"],$Un["Event"])&&in_array($_POST["Type"],$Un["Type"])){$aj=" ON ".table($a);$_d="DROP TRIGGER ".idf_escape($_).(DIALECT=="pgsql"?$aj:"");$Ah=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($_d,$Ah,lang(275));else{if($_!="")queries($_d);queries_redirect($Ah,($_!=""?lang(276):lang(277)),(bool)queries(create_trigger($aj,$_POST)));if($_!="")queries(create_trigger($aj,$J+["Type"=>reset($Un["Type"])]));}}$J=$_POST;}if($_!="")page_header(lang(278).": ".h($_),["table"=>$a,h($_)]);else
page_header(lang(279),["table"=>$a,lang(279)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(280),"</th><td>",html_select("Timing",$Un["Timing"],$J["Timing"],"triggerChange(/^".js_escape_re($a)."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(281),"</th><td>",html_select("Event",$Un["Event"],$J["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$Un["Event"]))echo" <input name='Of' value='".h($J["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(44),"</th><td>",html_select("Type",$Un["Type"],$J["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(221),"<input class='input' name='Trigger' value='",h($J["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$J["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($_!="")echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>",confirm(lang(215,$_));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$ta=$_GET["user"];$Ak=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$J){foreach(explode(",",($J["Privilege"]=="Grant option"?"":$J["Context"]))as$zc)$Ak[$zc=="File access on server"?"Server Admin":$zc][$J["Privilege"]]=$J["Comment"];}unset($Ak["Server Admin"]["Usage"]);foreach($Ak["Tables"]as$u=>$W)unset($Ak["Databases"][$u]);$Ai=[];if($_POST){foreach($_POST["objects"]as$u=>$W)$Ai[$W]=(array)$Ai[$W]+(array)$_POST["grants"][$u];}$qf=[];if(isset($_GET["host"])&&($H=Connection::get()->query("SHOW GRANTS FOR ".q($ta)."@".q($_GET["host"])))){while($J=$H->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$J[0],$y)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$y[1],$z,PREG_SET_ORDER)){foreach($z
as$W){if($W[1]!="USAGE")$qf["$y[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$J[0]))$qf["$y[2]$W[2]"]["GRANT OPTION"]=true;}}}}$gk=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$Zi=(isset($_GET["host"])?q($ta)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Zi",ME."privileges=",lang(282));else{$Di=q($_POST["user"])."@".q($_POST["host"]);$Zj=$_POST["pass"];$Gc=false;$H=true;if($Zi!=$Di){$Gc=(bool)queries("CREATE USER $Di IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($Zj));$H=$Gc;}elseif($Zj!="")$H=(bool)queries("SET PASSWORD FOR $Di = ".($gk||$_POST["hashed"]?q($Zj):"PASSWORD(".q($Zj).")"));if($H){$ml=[];foreach($Ai
as$Oi=>$of){if(isset($_GET["grant"]))$of=array_filter($of);$of=array_keys($of);if(isset($_GET["grant"]))$ml=array_diff(array_keys(array_filter($Ai[$Oi],'strlen')),$of);elseif($Zi==$Di){$Wi=array_keys((array)$qf[$Oi]);$ml=array_diff($Wi,$of);$of=array_diff($of,$Wi);unset($qf[$Oi]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Oi,$y)&&(!grant(false,$ml,$y[2],$y[1],$Di)||!grant(true,$of,$y[2],$y[1],$Di))){$H=false;break;}}}if($H&&isset($_GET["host"])){if($Zi!=$Di)queries("DROP USER $Zi");elseif(!isset($_GET["grant"])){foreach($qf
as$Oi=>$ml){if(preg_match('~^(.+)(\(.*\))?$~U',$Oi,$y))grant(false,array_keys($ml),$y[2],$y[1],$Di);}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(283):lang(284)),$H);if($Gc)Connection::get()->query("DROP USER $Di");}}$En=isset($_GET["host"])?lang(28).": ".h("$ta@$_GET[host]"):lang(187);$Fn=isset($_GET["host"])?h($ta):lang(187);page_header($En,["privileges"=>['',lang(72)],$Fn]);if($_POST){$J=$_POST;$qf=$Ai;}else{$J=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($qf)$qf[".*"]=[];elseif(DB!="")$qf[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$qf["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($J["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(28),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($J["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(29),"</th>","<td><input class='input' name='pass' id='pass' value='",h($J["pass"]),"' autocomplete='new-password'>";if(!$gk)echo
checkbox("hashed",1,$J["hashed"],lang(285),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$J["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(72).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$p=0;foreach($qf
as$Oi=>$of){echo"<th>";if($Oi=="*.*")echo"*.*",input_hidden("objects[$p]","*.*");else
echo"<input class='input' name='objects[$p]' value='".h(trim($Oi))."' size='10' autocapitalize='off'>";echo"</th>";$p++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(30),"Tables"=>lang(8),"Procedures"=>lang(286),]as$zc=>$hd){foreach((array)$Ak[$zc]as$_k=>$gc){echo"<tr>";if($hd)echo"<td>$hd</td>";echo"<td".(!$hd?" colspan='2'":"").' lang="en" title="'.h($gc).'">'.h($_k)."</td>";$p=0;foreach($qf
as$Oi=>$of){$_="'grants[$p][".h(strtoupper($_k))."]'";$X=$of[strtoupper($_k)];$Fk=strpos($Oi,"@")!==false;$_i=$Oi==".*";$Ma=$_k=="All privileges";$pf=$_k=="Grant option";if($Oi=="*.*"&&$_k=="Proxy")echo"<td></td>";elseif($Fk&&$_k!="Proxy"&&!$pf)echo"<td></td>";elseif($zc=="Server Admin"&&$Oi!=(isset($qf["*.*"])?"*.*":".*")&&!(($Fk||$_i)&&$_k=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$_>"."<option></option>"."<option value='1'".($X?" selected":"").">".lang(287)."</option>"."<option value='0'".($X=="0"?" selected":"").">".lang(288)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$_ value='1'".($X?" checked":"").($Ma?" id='grants-$p-all'":(!$pf?" class='grants-$p'":"")).">";if($Ma)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$p'); };");elseif(!$pf)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$p-all'); };");echo"</label>";}$p++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(164),"'>\n",confirm(lang(215,"$ta@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Vg=0;foreach((array)$_POST["kill"]as$W){if(kill_process($W))$Vg++;}queries_redirect(ME."processlist=",lang(289,$Vg),$Vg||!$_POST["kill"]);}}page_header(lang(154),[lang(154)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$p=-1;foreach(process_list()as$p=>$J){if(!$p){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($J
as$u=>$W)echo"<th>$u".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($u),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$J[DIALECT=="sql"?"Id":"pid"],0):"");foreach($J
as$u=>$W)echo"<td>".($W!=""&&((DIALECT=="sql"&&$u=="Info"&&preg_match("~Query|Killed~",$J["Command"]))||(DIALECT=="pgsql"&&$u=="query")||(DIALECT=="oracle"&&$u=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($W,100).'</code> <a href="'.h(ME.($J["db"]!=""?"db=".urlencode($J["db"])."&":"")."sql=".urlencode($W)).'">'.icon("edit").lang(290).'</a>':h($W));echo"\n";}if($p>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($p+1)."/".lang(291,max_connections()),"<p><input type='submit' class='button' value='".lang(292)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$R=table_status1($a);$t=indexes($a);$l=fields($a);$We=column_foreign_keys($a);$Ri=$R["Oid"];$nl=[];$d=[];$Hl=[];$qj=[];$yn=null;foreach($l
as$u=>$k){$_=Admin::get()->getFieldName($k);$vi=html_entity_decode(strip_tags($_),ENT_QUOTES);if(isset($k["privileges"]["select"])&&$_!=""){$d[$u]=$vi;if(is_shortable($k))$yn=Admin::get()->processSelectionLength();}if(isset($k["privileges"]["where"])&&$_!="")$Hl[$u]=$vi;if(isset($k["privileges"]["order"])&&$_!="")$qj[$u]=$vi;$nl+=$k["privileges"];}list($L,$rf)=Admin::get()->processSelectionColumns($d,$t);$L=array_unique($L);$rf=array_unique($rf);$Cg=count($rf)<count($L);$Z=Admin::get()->processSelectionSearch($l,$t);$pj=Admin::get()->processSelectionOrder($l,$t);$w=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$go=>$J){$Va=convert_field($l[key($J)]);$L=[$Va?:idf_escape(key($J))];$Z[]=where_check($go,$l);$I=Driver::get()->select($a,$L,$Z,$L);if($I)echo
first($I->fetchRow());}exit;}$E=$jo=[];foreach($t
as$s){if($s["type"]=="PRIMARY"){$E=array_flip($s["columns"]);$jo=($L?$E:[]);foreach($jo
as$u=>$W){if(in_array(idf_escape($u),$L))unset($jo[$u]);}break;}}if($Ri&&!$E){$E=$jo=[$Ri=>0];$t[]=["type"=>"PRIMARY","columns"=>[$Ri]];}$N=Admin::get()->getSettings();if($_POST){$Uo=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Lb=[];foreach($_POST["check"]as$Gb)$Lb[]=where_check($Gb,$l);$Uo[]="((".implode(") OR (",$Lb)."))";}$Uo=($Uo?"\nWHERE ".implode(" AND ",$Uo):"");if($_POST["export"]){$N->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$ff=($L?implode(", ",$L):"*").convert_fields($d,$l,$L)."\nFROM ".table($a);$uf=($rf&&$Cg?"\nGROUP BY ".implode(", ",$rf):"").($pj?"\nORDER BY ".implode(", ",$pj):"");if(!is_array($_POST["check"])||$E)$F="SELECT $ff$Uo$uf";else{$do=[];foreach($_POST["check"]as$W)$do[]="(SELECT".limit($ff,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$l).$uf,1).")";$F=implode(" UNION ALL ",$do);}Admin::get()->dumpData($a,"table",$F);exit;}if($_POST["save"]||$_POST["delete"]){$H=true;$Ea=0;$gm=[];if(!$_POST["delete"]){$Ql=array_keys($_POST["fields"]+$_POST["function"]);foreach($Ql
as$_){$W=process_input($l[$_]);if($W!==null&&($_POST["clone"]||$W!==false))$gm[idf_escape($_)]=($W!==false?$W:idf_escape($_));}}if($_POST["delete"]||$gm){if($_POST["clone"])$F="INTO ".table($a)." (".implode(", ",array_keys($gm)).")\nSELECT ".implode(", ",$gm)."\nFROM ".table($a);if($_POST["all"]||($E&&is_array($_POST["check"]))||$Cg){$H=($_POST["delete"]?Driver::get()->delete($a,$Uo):($_POST["clone"]?queries("INSERT $F$Uo".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$gm,$Uo)));$Ea=Connection::get()->getAffectedRows();if(is_object($H))$Ea+=$H->getRowsCount();}else{foreach((array)$_POST["check"]as$W){$Qo="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$l);$H=($_POST["delete"]?Driver::get()->delete($a,$Qo,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$Qo)):Driver::get()->update($a,$gm,$Qo,1)));if(!$H)break;$Ea+=Connection::get()->getAffectedRows();}}}$Xh=lang(293,$Ea);if($_POST["clone"]&&$H&&$Ea==1){$eh=last_id($H);if($eh)$Xh=lang(209," $eh");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Xh,(bool)$H);if(!$_POST["delete"]){$Md=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});edit_form($a,$Md,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(294));else{$Mm=true;$Ea=0;foreach($_POST["val"]as$go=>$J){$gm=[];foreach($J
as$u=>$W){$u=bracket_escape($u,true);$gm[idf_escape($u)]=(preg_match('~char|text~',$l[$u]["type"])||$W!=""?Admin::get()->processFieldInput($l[$u],$W):"NULL");}$Mm=(bool)Driver::get()->update($a,$gm," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($go,$l),($Cg||$E?0:1)," ");if(!$Mm)break;$Ea+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(293,$Ea),$Mm);}}elseif(!is_string($m=get_file("csv_file",true)))Admin::get()->addError(upload_error($m));elseif(!preg_match('~~u',$m))Admin::get()->addError(lang(295));else{$N->updateParameter("exportFormat",$_POST["import_format"]);$ac=array_keys($l);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$m,$z);$Ea=count($z[0]);Driver::get()->begin();$Rl=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$K=[];foreach($z[0]as$u=>$W){preg_match_all("~((?>\"[^\"]*\")+|[^$Rl]*)$Rl~",$W.$Rl,$Kh);if(!$u&&!array_diff($Kh[1],$ac)){$ac=$Kh[1];$Ea--;}else{$gm=[];foreach($Kh[1]as$p=>$Tb)$gm[idf_escape($ac[$p])]=($Tb==""&&$l[$ac[$p]]["null"]?"NULL":q(preg_match('~^".*"$~s',$Tb)?str_replace('""','"',substr($Tb,1,-1)):$Tb));$K[]=$gm;}}$Mm=!$K||Driver::get()->insertUpdate($a,$K,$E);if($Mm)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(296,$Ea),$Mm);Driver::get()->rollback();}}$en=Admin::get()->getTableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(55).": $en",[$en]);$qg=null;if(isset($nl["insert"])||!support("table")){$qg=[];foreach((array)$_GET["where"]as$W){if(isset($We[$W["col"]])&&count($We[$W["col"]])==1&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$W["val"])))))$qg["preset"."[".bracket_escape($W["col"])."]"]=$W["val"];}}Admin::get()->printTableMenu($R,$qg);if(!$d&&support("table"))echo"<p class='error'>".lang(297).($l?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div hidden>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),'<input type="submit" class="button" value="'.h(lang(55)).'">',"</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($L,$d);Admin::get()->printSelectionSearch($Z,$Hl,$t);Admin::get()->printSelectionOrder($pj,$qj,$t);Admin::get()->printSelectionLimit($w);Admin::get()->printSelectionLength($yn);Admin::get()->printSelectionAction($t);echo"</div>\n</form>\n";$Hj=isset($_GET["page"])?$_GET["page"]:null;if($Hj=="last"){$cf=Connection::get()->getValue(count_rows($a,$Z,$Cg,$rf));$Hj=(int)floor(max(0,intval($cf)-1)/$w);}else{$cf=false;$Hj=(int)$Hj;}$Jl=$L;$sf=$rf;if(!$Jl){$Jl[]="*";$_c=convert_fields($d,$l,$L);if($_c)$Jl[]=substr($_c,2);}foreach($L
as$u=>$W){$k=$l[idf_unescape($W)];if($k&&($Va=convert_field($k)))$Jl[$u]="$Va AS $W";}if(DIALECT=="pgsql"||DIALECT=="mssql"){foreach((array)$_GET["columns"]as$u=>$W){if(isset($Jl[$u])&&$W["fun"])$Jl[$u].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if(!$Cg&&$jo){foreach($jo
as$u=>$W){$Jl[]=idf_escape($u);if($sf)$sf[]=idf_escape($u);}}$H=Driver::get()->select($a,$Jl,$Z,$sf,$pj,$w,$Hj,true);if(!$H)echo"<p class='error'>".error()."\n";else{if(DIALECT=="mssql"&&$Hj)$H->seek($w*$Hj);echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";$K=[];while($J=$H->fetchAssoc()){if($Hj&&DIALECT=="oracle")unset($J["RNUM"]);$K[]=$J;}if($_GET["page"]!="last"&&$w&&$rf&&$Cg&&DIALECT=="sql")$cf=Connection::get()->getValue(" SELECT FOUND_ROWS()");$Nd=false;if(!$K)echo"<p class='message'>".lang(91)."\n";else{$ib=Admin::get()->getBackwardKeys($a,$en);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($rf||!$L){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly'>".script("gid('all-page').onclick = partial(formCheck, /check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(298),"'>",icon_solo("edit-all"),"</a>";}$wi=[];$jf=[];reset($L);$Ok=1;foreach($K[0]as$u=>$W){if(!isset($jo[$u])){$Ll=key($L);$W=isset($_GET["columns"][$Ll])?$_GET["columns"][$Ll]:[];$k=$l[$L?($W?$W["col"]:current($L)):$u];$_=($k?Admin::get()->getFieldName($k,$Ok):(isset($W["fun"])?"*":h($u)));if($_!=""){$Ok++;$wi[$u]=$_;$c=idf_escape($u);$Pf=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($u);$hd="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($u))."]'>";$hf=apply_sql_function(isset($W["fun"])?$W["fun"]:null,$_);$sm=isset($k["privileges"]["order"])||(isset($W["fun"])?$W["fun"]:null);if($sm)echo'<a href="',h($Pf.($pj[0]==$c||$pj[0]==$u?$hd:'')),'">',"$hf</a>";else
echo$hf;echo"<span class='column'>";if($sm)echo"<a href='".h($Pf.$hd)."' title='".lang(62)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($W["fun"])&&isset($k["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.lang(59).'" class="button light jsonly">',icon_solo("search"),'</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($u)."');");echo"</span>";}$jf[$u]=isset($W["fun"])?$W["fun"]:null;next($L);}}$mh=[];if($_GET["modify"]){foreach($K
as$J){foreach($J
as$u=>$W)$mh[$u]=max($mh[$u],min(40,strlen(utf8_decode($W))));}}if($ib)echo"<th>".lang(17)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($K,$We)as$ti=>$J){$fo=unique_array($K[$ti],$t);if(!$fo){$fo=[];reset($L);foreach($K[$ti]as$u=>$W){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($L)))$fo[$u]=$W;next($L);}}$go="";foreach($fo
as$u=>$W){$k=isset($l[$u])?$l[$u]:null;if((DIALECT=="sql"||DIALECT=="pgsql")&&$k&&preg_match('~char|text|enum|set~',$k["type"])&&strlen($W)>64){$u=(strpos($u,'(')?$u:idf_escape($u));$u="MD5(".(DIALECT!='sql'||preg_match("~^utf8~",isset($k["collation"])?$k["collation"]:"")?$u:"CONVERT($u USING ".charset(Connection::get()).")").")";$W=md5($W);}$go
.="&".($W!==null?urlencode("where[".bracket_escape($u)."]")."=".urlencode($W===false?"f":$W):"null%5B%5D=".urlencode($u));}echo"<tr>";if($rf||!$L){echo"<td class='actions'>",checkbox("check[]",substr($go,1),in_array(substr($go,1),(array)$_POST["check"]));if(!$Cg&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$go),"' class='edit' title='",lang(38),"'>",icon_solo("edit"),"</a>";}reset($L);foreach($J
as$u=>$W){if(isset($wi[$u])){$c=current($L);$k=isset($l[$u])?$l[$u]:null;$x="";if($k&&is_blob($k)&&$W!="")$x=ME.'download='.urlencode($a).'&field='.urlencode($u).$go;if(!$x&&$W!==null){foreach((array)$We[$u]as$o){if(count($We[$u])==1||end($o["source"])==$u){$x="";foreach($o["source"]as$p=>$tm)$x
.=where_link($p,$o["target"][$p],$K[$ti][$tm]);$x=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($o["db"]),ME):ME).'select='.urlencode($o["table"]).$x;if($o["ns"])$x=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($o["ns"]),$x);if(count($o["source"])==1)break;}}}if($c=="COUNT(*)"){$x=ME."select=".urlencode($a);$p=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$fo))$x
.=where_link($p++,$V["col"],$V["val"],$V["op"]);}foreach($fo
as$Mg=>$V)$x
.=where_link($p++,$Mg,$V);}$Ji=$W===null;$Qf=select_value($W,$x,$k,$yn);$de=bracket_escape($u);$q=h("val[$go][$de]");$rk=isset($_POST["val"][$go][$de])?$_POST["val"][$go][$de]:null;$lo=isset($k["privileges"]["update"])?$k["privileges"]["update"]:false;$Ld=!is_array($J[$u])&&is_utf8($Qf)&&$K[$ti][$u]==$J[$u]&&!$jf[$u]&&!(isset($k["generated"])?$k["generated"]:false);$T=($c&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$c,$z)?$l[idf_unescape($z[2])]["type"]:(isset($k["type"])?$k["type"]:null));$ni=$T=="money"||($c&&preg_match('~^SUM\((.+)\)~',$c,$z)&&$l[idf_unescape($z[1])]["type"])=="money";$vn=$T&&preg_match('~text|json|lob~',$T);$Ni=($T&&preg_match(number_type(),$T))||($c&&preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',$c));$Qb=$Ni&&($Ji||is_numeric(strip_tags($Qf))||$ni)?"class='number'":"";echo"<td id='$q' $Qb";if(($_GET["modify"]&&$Ld&&!$Ji)||$rk!==null){$Nd=true;$xf=h($rk!==null?$rk:$J[$u]);echo" data-editing='true'>".($vn?"<textarea name='$q' cols='30' rows='".(substr_count($J[$u],"\n")+1)."'>$xf</textarea>":"<input class='input' name='$q' value='$xf' size='$mh[$u]'>");}else{$Ch=strpos($Qf,"<i>…</i>");if($lo)echo" data-text='".($Ch?2:($vn?1:0))."'".($Ld?"":" data-warning='".h(lang(299))."'");echo">$Qf";}}next($L);}if($ib){echo"<td>";Admin::get()->printBackwardKeys($ib,$K[$ti]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: partialArg(tableClick, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: partialArg(tableClick, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($K||$Hj){$ge=true;if($_GET["page"]!="last"){if(!$w||(count($K)<$w&&($K||!$Hj)))$cf=($Hj?$Hj*$w:0)+count($K);elseif(DIALECT!="sql"||!$Cg){$cf=($Cg?false:found_rows($R,$Z));if($cf<max(1e4,2*($Hj+1)*$w))$cf=first(slow_query(count_rows($a,$Z,$Cg,$rf)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$ge=false;}}$Jj=($w!==null&&($cf===false||$cf>$w||$Hj));if($Jj){if(($cf===false?count($K)+1:$cf-$Hj*$w)>$w)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($Hj+1)),'" class="loadmore">',icon("expand"),lang(300),'</a>',script("qsl('a').onclick = partial(loadNextPage, $w, '".lang(301)."…');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($Jj){$Oh=($cf===false?$Hj+(count($K)>=$w?2:1):(int)floor(($cf-1)/$w));$wd="<li>…</li>";echo"<fieldset>";if(DIALECT!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".lang(302)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(302)."', '".($Hj+1)."')); return false; };"),"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$Hj);if($Hj>5)echo$wd;for($p=max(1,$Hj-4);$p<min($Oh,$Hj+5);$p++)echo
pagination($p,$Hj);if($Oh>0){if($Hj+5<$Oh)echo$wd;echo($ge&&$cf!==false?pagination($Oh,$Hj):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Oh'>".lang(303)."</a>");}echo"</ul></div>";}else{echo"<legend>".lang(302)."</legend>","<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$Hj);if($Hj>1)echo$wd;if($Hj)echo
pagination($Hj,$Hj);if($Oh>$Hj){echo
pagination($Hj+1,$Hj);if($Oh>$Hj+1)echo$wd;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(304)."</legend><div class='fieldset-content'>";$pd=($ge?"":"~ ").$cf;echo
checkbox("all",1,0,($cf!==false?($ge?"":"~ ").lang(191,$cf):""),"const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$pd' : checked); selectCount('selected2', this.checked || !checked ? '$pd' : checked);")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(298),"</legend>";$xl=($_GET["modify"]?"":" data-inline-edit='1'".($Nd?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(294)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(115),"'",$xl,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(163)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(38),"'> ","<input type='submit' class='button' name='clone' value='",lang(290),"'> ","<input type='submit' class='button' name='delete' value='",lang(119),"'>",confirm(),"</div>","</fieldset>\n";}$Ye=Admin::get()->getDumpFormats();foreach((array)$_GET["columns"]as$c){if($c["fun"]){unset($Ye['sql']);break;}}if($Ye){print_fieldset_start("export",lang(74)." <span id='selected2'></span>","export");echo
html_select("format",$Ye,$N->getParameter("exportFormat"));$Dj=Admin::get()->getDumpOutputs();echo($Dj?" ".html_select("output",$Dj,$N->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(74)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(73),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$N->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(73)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(198);echo"</p>";}echo
input_token(),"</form>\n",(!$rf&&$L?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$O=isset($_GET["status"]);$En=$O?lang(156):lang(155);page_header($En,[$En]);$_o=($O?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$_o)echo"<p class='message'>",lang(91),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($_o
as$J){echo"<tr>";$u=array_shift($J);echo"<th><code class='jush-".DIALECT.($O?"status":"set")."'>".h($u)."</code></th>";foreach($J
as$W)echo"<td>",nl2br(h($W)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Pm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$f=[];$Uc=null;foreach(table_status()as$_=>$R){$f["Comment-$_"]=h($R["Comment"]);if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){$f["Engine-$_"]=h($R["Engine"]);$Vb=isset($R["Collation"])?$R["Collation"]:"";if($Vb==""){if($Uc===null)$Uc=db_collation(DB,collations())??"";$Vb=$Uc;}$f["Collation-$_"]=h($Vb);foreach($Pm+["Auto_increment"=>0,"Rows"=>0]as$u=>$W){if($R[$u]!=""){$W=format_number($R[$u]);if($W>=0)$f["$u-$_"]=($u=="Rows"?format_rows($R):$W);if(isset($Pm[$u]))$Pm[$u]+=($R["Engine"]!="InnoDB"||$u!="Data_free"?$R[$u]:0);}elseif(array_key_exists($u,$R))$f["$u-$_"]="?";}}}if(function_exists('AdminNeo\db_status'))$Pm=db_status();foreach($Pm
as$u=>$W)$f["sum-$u"]=format_number($W);echo
json_encode($f,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$f=[];foreach(count_tables(Admin::get()->getDatabases())as$h=>$W){$f["tables-$h"]=$W;$f["size-$h"]=db_size($h);}echo
json_encode($f,JSON_UNESCAPED_UNICODE);}exit;}else{$on=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($on&&!$_POST["search"]){$H=true;$Xh="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]||$_POST["truncate_cascade"]){if($_POST["tables"])$H=truncate_tables($_POST["tables"],(bool)$_POST["truncate_cascade"]);$Xh=lang(305);}elseif($_POST["move"]){$H=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Xh=lang(306);}elseif($_POST["copy"]){$H=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Xh=lang(307);}elseif($_POST["drop"]){if($_POST["views"])$H=drop_views($_POST["views"]);if($H&&$_POST["tables"])$H=drop_tables($_POST["tables"]);$Xh=lang(308);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$J)$Xh
.="<b>".h($Q)."</b>: ".h($J["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$H=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),$_POST["tables"]));$Xh=lang(309);}elseif(!$_POST["tables"])$Xh=lang(78);elseif($H=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($J=$H->fetchAssoc())$Xh
.="<b>".h($J["Table"])."</b>: ".h($J["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$Xh,(bool)$H);}if($_GET["ns"]=="")page_header(lang(30).": ".h(DB),true);else
page_header(lang(81).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(310)."</h2>\n";$Dl=Admin::get()->getSchemas();if(!$Dl)echo"<p class='message'>".lang(311)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(81),"</th></tr></thead>";foreach($Dl
as$_)echo"<tr><th><a href='",h(ME),"ns=".urlencode($_),"' title='",lang(312),"'>".h($_)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(76)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(313)."</h2>\n";$jn=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$Uc=db_collation(DB,collations());$d=["Engine"=>["label"=>lang(168),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($Uc!="")$d["Collation"]=["label"=>lang(45),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$d+=["Data_length"=>["label"=>lang(314),"doc"=>doc_link($jn+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(35),],"Index_length"=>["label"=>lang(315),"doc"=>doc_link($jn+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(172),],"Data_free"=>["label"=>lang(316),"doc"=>doc_link($jn),"link"=>"edit","title"=>lang(7),],"Auto_increment"=>["label"=>lang(47),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(35),],"Rows"=>["label"=>lang(317),"doc"=>doc_link($jn+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(33),],];if(support("comment"))$d["Comment"]=["label"=>lang(46),"doc"=>doc_link($jn+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$pj=(is_string($_GET["order"])?$_GET["order"]:"");$id=null;if(preg_match('~^(.+)-(asc|desc)$~',$pj,$y)){$pj=$y[1];$id=($y[2]=="desc");}if($pj!="__table"&&!isset($d[$pj]))$pj="";if($id===null)$id=isset($d[$pj]["link"]);$Yo=($pj!=""&&$pj!="__table")||support("fast_status");$mn=($Yo?table_status():tables_list());if(!$mn)echo"<p class='message'>".lang(78)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(318)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' class='button' name='search' value='".lang(59)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<td class="actions"><input id="check-all" type="checkbox" class="input jsonly">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$ui=($pj==""||$pj=="__table");$dn=($ui&&!$id?ME."order=__table-desc":substr(ME,0,-1));echo'<th><a href="'.h($dn).'">'.lang(8).'</a>';foreach($d
as$u=>$c){$kd=($u===$pj?!$id:isset($c["link"]));echo'<td><a href="'.h(ME)."order=$u-".($kd?"desc":"asc").'">'.$c["label"].'</a>'.$c["doc"];}echo"</thead>\n","<tbody>\n";if($pj=="__table"){if($id)$mn=array_reverse($mn,true);}elseif($pj){uasort($mn,function($va,$fb)use($pj,$id){$Zo=isset($va[$pj])?$va[$pj]:null;$bp=isset($fb[$pj])?$fb[$pj]:null;$H=($Zo<$bp?-1:($Zo>$bp?1:0));return($id?-$H:$H);});}$Pm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$S=0;foreach($mn
as$_=>$O){$Eo=($Yo?is_view($O):$O!==null&&!preg_match('~table|sequence~i',$O));$Wd=($Yo?(isset($O["Engine"])?$O["Engine"]:""):$O);$q=h("Table-".$_);echo'<tr><td class="actions">'.checkbox(($Eo?"views[]":"tables[]"),$_,in_array("$_",$on,true),"","","",$q);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$xa="table";else$xa="select";echo"<th><a href='",h(ME),"$xa=",urlencode($_),"' id='$q'>",h($_),"</a></th>";if($Eo&&!preg_match('~materialized~i',$Wd)){$En=lang(167);$bc=count($d)-(support("comment")?2:1);echo'<td colspan="'.$bc.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($_)."' title='".lang(36)."'>$En</a>":$En),'<td align="right"><a href="'.h(ME)."select=".urlencode($_).'" title="'.lang(33).'">?</a>';}else{foreach($d
as$u=>$c){if($u=="Comment")continue;$q=" id='$u-".h($_)."'";$x=isset($c["link"])?$c["link"]:"";if(!$x){$W="";if($Yo){$W=isset($O[$u])?$O[$u]:"";if($u=="Collation"&&$W=="")$W=$Uc;}echo"<td$q>".h($W);continue;}$W="?";if($Yo){$Mi=isset($O[$u])?$O[$u]:"";if(is_numeric($Mi)&&$Mi>=0){$W=($u=="Rows"?format_rows($O):format_number($Mi));if(isset($Pm[$u])&&($Wd!="InnoDB"||$u!="Data_free"))$Pm[$u]+=$Mi;}}echo"<td align='right'>".(support("table")||$u=="Rows"||(support("indexes")&&$u!="Data_length")?"<a href='".h(ME."$x=").urlencode($_)."'$q title='".$c["title"]."'>".h($W)."</a>":"<span$q>".h($W)."</span>");}$S++;}echo(support("comment")?"<td id='Comment-".h($_)."'>".($Yo?h(isset($O["Comment"])?$O["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<tfoot><tr>","<td><th>".lang(291,count($mn)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($Uc!=""?"<td>".h($Uc):"");if($Yo&&function_exists('AdminNeo\db_status'))$Pm=db_status();foreach($Pm
as$u=>$Om)echo"<td align='right' id='sum-$u'>".($Yo?format_number($Om):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($Yo?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$xo="<input type='submit' class='button' value='".lang(319)."'> ".help_script("VACUUM");$kj="<input type='submit' class='button' name='optimize' value='".lang(320)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(163)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$xo."<input type='submit' class='button' name='check' value='".lang(321)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$xo.$kj:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(322)."'> ".help_script("ANALYZE TABLE").$kj."<input type='submit' class='button' name='check' value='".lang(321)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(323)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(324)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm().(DIALECT=="pgsql"?"<input type='submit' class='button' name='truncate_cascade' value='".lang(325)."'> ".help_script("TRUNCATE CASCADE").confirm():"")."<input type='submit' class='button' name='drop' value='".lang(164)."'>".help_script("DROP TABLE").confirm()."\n";$g=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";$Fl="";if(count($g)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(326)." <span id='selected3'></span></legend><div>";$h=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($g?html_select("target",$g,$h,"","label-move"):'<input class="input" name="target" value="'.h($h).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(327)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(328)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(329)):""),"</div></fieldset>\n";$Fl=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo
input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $S);":"")."$Fl }"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(77),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(244),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(183)."</h2>\n";$rl=routines();if($rl){$jc=$rl[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(221),'</th><td>',lang(44),'</td><td>',lang(261),"</td>";if($jc)echo"<td>",lang(46),"</td>";echo"<td></td>","</tr></thead>\n";foreach($rl
as$J){$_=($J["SPECIFIC_NAME"]==$J["ROUTINE_NAME"]?"":"&name=".urlencode($J["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($J["SPECIFIC_NAME"]).$_),'">',h($J["ROUTINE_NAME"]),'</a></th>','<td>',h($J["ROUTINE_TYPE"]),'</td>','<td>',h($J["DTD_IDENTIFIER"]),'</td>';if($jc)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($J["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($J["SPECIFIC_NAME"]).$_).'">'.lang(175)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(260),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(259),"</a>\n","</p>\n";}if(support("sequence")){echo"<h2 id='sequences'>".lang(330)."</h2>\n";$Ul=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Ul){echo"<table>\n","<thead><tr><th>",lang(221),"</th><td></td></tr></thead>\n";foreach($Ul
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"sequence=",urlencode($W),"'>",lang(175),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"sequence='>",icon("add"),lang(266),"</a></p>\n";}if(support("type")){echo"<h2 id='user-types'>".lang(109)."</h2>\n";$uo=types();if($uo){echo"<table>\n","<thead><tr><th>",lang(221),"</th><td></td></tr></thead>\n";foreach($uo
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"type=",urlencode($W),"'>",lang(175),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"type='>",icon("add"),lang(267),"</a></p>\n";}if(support("event")){echo"<h2 id='events'>".lang(184)."</h2>\n";$K=get_rows("SHOW EVENTS");if($K){echo"<table>\n","<thead><tr><th>".lang(221)."<td>".lang(331)."<td>".lang(250)."<td>".lang(251)."<td></thead>\n";foreach($K
as$J)echo"<tr>","<th>".h($J["Name"]),"<td>".($J["Execute at"]?lang(332)."<td>".h($J["Execute at"]):lang(252)." ".h($J["Interval value"])." ".h($J["Interval field"])."<td>".h($J["Starts"])),"<td>".h($J["Ends"]),'<td><a href="'.h(ME).'event='.urlencode($J["Name"]).'">'.lang(175).'</a>';echo"</table>\n";$ee=Connection::get()->getValue("SELECT @@event_scheduler");if($ee&&$ee!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($ee)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(249),"</a></p>\n";}}}page_footer();