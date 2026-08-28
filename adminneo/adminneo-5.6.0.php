<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.6.0
 *
 * Compiled with
 * drivers:   all
 * languages: all
 * themes:    all
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
inject($Ba,Config$mc,Settings$Vl,Locale$sh){$this->admin=$Ba;$this->config=$mc;$this->settings=$Vl;$this->locale=$sh;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$mc=[],array$Xj=[]){if(self::$instance)die("Admin instance already exists.\n");$Ba=new
static();if(!$mc&&file_exists("adminneo-config.php")){$mc=include_once("adminneo-config.php");if(!is_array($mc)){$mc=[];$kh="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$Ba->addError(lang(0,"<b>adminneo-config.php</b>")." <a $kh>".lang(1)."</a>");}}$mc=new
Config($mc);$Vl=new
Settings($mc);if(!$Xj&&file_exists("adminneo-plugins.php")){$Xj=include_once("adminneo-plugins.php");if(!is_array($Xj)){$Xj=[];$kh="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$Ba->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $kh>".lang(1)."</a>");}}self::$instance=$Xj?new
Pluginer($Ba,$Xj):$Ba;$Ba->inject(self::$instance,$mc,$Vl,Locale::get());foreach($Xj
as$Wj)$Wj->inject(self::$instance,$mc,$Vl,Locale::get());return
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
getCredentials(){$N=$this->config->getServer(SERVER);return[$N?$N->getServer():SERVER,$_GET["username"],get_password()];}function
verifyDefaultPassword($E){$yf=$this->config->getDefaultPasswordHash();if($yf===null||$yf==="")return
lang(2);elseif(!password_verify($E,$yf))return
lang(3);return
true;}function
authenticate($U,$E){if($E==""){$yf=$this->config->getDefaultPasswordHash();if($yf===null)return
lang(4,target_blank());else
return$yf==="";}return
true;}function
getPrivateKey($Ac=false){return
get_private_key($Ac);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($N,$Sk=true,$se=null){if($N==""){if(!$Sk)return"";$N=Connection::exists()?Connection::get()->getDefaultServerName():"";if($N=="")return$se!==null?$se:lang(5);$Kl=null;}else$Kl=$this->config->getServer($N);return$Kl?$Kl->getName():preg_replace('~^https?://~',"",$N);}abstract
function
getDatabase();function
getDatabases($Oe=true){return$this->filterListWithWildcards(get_databases($Oe),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());}function
getSchemas($_i=false){$Bf=$this->config->getHiddenSchemas();if($_i&&!in_array("__system",$Bf))$Bf[]="__system";return$this->filterListWithWildcards(schemas(),$Bf,false,Driver::get()->getSystemSchemas());}function
getCollations(array$Gg=[]){$wo=$this->config->getVisibleCollations();$Fe=$wo?array_merge($wo,$Gg):[];return$this->filterListWithWildcards(collations(),$Fe,true);}private
function
filterListWithWildcards(array$Y,array$Fe,$Ig,array$Im=[]){if(!$Y||!$Fe)return$Y;$s=array_search("__system",$Fe);if($s!==false){unset($Fe[$s]);$Fe=array_merge($Fe,$Im);}array_walk($Fe,function(&$X){$X=str_replace('\\*',".*",preg_quote($X,"~"));});$Qj='~^('.implode("|",$Fe).')$~';return$this->filterListWithPattern($Y,$Qj,$Ig);}private
function
filterListWithPattern(array$Y,$Qj,$Ig){$I=[];foreach($Y
as$u=>$X){if(is_array($X)){if($zm=$this->filterListWithPattern($X,$Qj,$Ig))$I[$u]=$zm;}elseif(($Ig&&preg_match($Qj,$X))||(!$Ig&&!preg_match($Qj,$X)))$I[$u]=$X;}return$I;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$Fc){}function
printFavicons(){$Vb=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Vb.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Vb.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Vb.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$bo=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$n){if(file_exists($n))$bo[]="$n?v=".filemtime($n);}return$bo;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($Lc){$ei=$Lc?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$fi=$Lc?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$Be=file_exists("adminneo-$ei.css");$Ce=file_exists("adminneo-$fi.css");if($Be&&!$Ce)return
true;return$this->settings->getColorScheme()==$ei&&!($Be
xor$Ce);}function
getJsUrls(){$bo=$this->config->getJsUrls();$n="adminneo.js";if(file_exists($n))$bo[]="$n?v=".filemtime($n);return$bo;}abstract
function
printLoginForm();function
getLoginFormRow($we,$Rg,$k){if($Rg)return"<tr><th>$Rg</th><td>$k</td></tr>\n";else
return"$k\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n",h($_GET["username"]),"<input type='submit' class='button' name='logout' value='",lang(6),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$Nm){return
h($Nm["Name"]);}abstract
function
getFieldName(array$k,$fj=0);function
formatComment($dc){return
h($dc);}abstract
function
printTableMenu(array$Nm,$Tl="");function
getForeignKeys($Q){return
foreign_keys($Q);}function
getBackwardKeys($Q,$Lm){if(!$this->settings->isRelationLinks())return[];$L=backward_keys($Q);$Lg=[];foreach($L
as$K){$q=$K["table_schema"].".".$K["table_name"];$Lg[$q]["schema"]=$K["table_schema"];$Lg[$q]["table"]=$K["table_name"];$Lg[$q]["constraints"][$K["constraint_name"]][$K["column_name"]]=$K["referenced_column_name"];}foreach($Lg
as$q=>$u){$_=$this->admin->getTableName(table_status1($u["table"],true));if($_!=""){$ul=preg_quote($Lm);$El="(:|\\s*-)?\\s+";$Lg[$q]["name"]=(preg_match("(^$ul$El(.+)|^(.+?)$El$ul\$)iu",$_,$y)?$y[2].$y[3]:$_);}else
unset($Lg[$q]);}return$Lg;}function
printBackwardKeys(array$fb,array$K){foreach($fb
as$u){foreach($u["constraints"]as$tc){$Nh=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($u["schema"])."&",ME);$x=$Nh.'select='.urlencode($u["table"]);$p=0;foreach($tc
as$c=>$W){if(!isset($K[$W]))continue
2;$x
.=where_link($p++,$c,$K[$W]);}$_=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$u["name"]);$sn=implode(", ",array_keys($tc));echo"<a href='".h($x)."' title='".h($sn)."'>".h($_)."</a>";$x=$Nh.'edit='.urlencode($u["table"]);foreach($tc
as$c=>$W)$x
.="&set".urlencode("[".bracket_escape($c)."]")."=".urlencode($K[$W]);echo"<a href='".h($x)."' title='".lang(7)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($G,$rm,$re=false);abstract
function
formatMessageQuery($G,$pn,$re=false);abstract
function
formatSqlCommandQuery($G);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($Q);abstract
function
fillForeignDescriptions(array$L,array$Re);function
getFieldValueLink($W,$k){if(is_mail($W))return"mailto:$W";if(is_web_url($W))return$W;return
null;}abstract
function
formatSelectionValue($W,$x,$k,$rj);abstract
function
formatFieldValue($X,array$k);abstract
function
printTableStructure(array$l);abstract
function
printTablePartitions(array$Gj);abstract
function
printRelatedTables(array$S);abstract
function
printTableIndexes(array$t,array$Nm);abstract
function
printSelectionColumns(array$M,array$d);abstract
function
printSelectionSearch(array$Z,array$d,array$t);abstract
function
printSelectionOrder(array$fj,array$d,array$t);abstract
function
printSelectionLimit($w);abstract
function
printSelectionLength($jn);abstract
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
getFieldInput($Q,array$k,$Va,$X,$df);function
getFieldInputHint($Q,array$k,$X){return
support("comment")?$this->admin->formatComment($k["comment"]):"";}abstract
function
processFieldInput(array$k,$X,$df="");function
detectJson($xe,&$X,$ik=null){if(is_array($X)){$Me=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$X=json_encode($X,$Me);return
true;}$Me=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($ik?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$xe)){if($X!=null&&$ik!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode(json_decode($X),$Me);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($X)&&$X!=""&&preg_match('~varchar|text|character varying|String|keyword~',$xe)&&($X[0]=="{"||$X[0]=="[")&&($Eg=json_decode($X))){if($ik!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode($Eg,$Me);return
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
sendDumpHeaders($Nf,$ii=false);function
dumpDatabase($Oc){}abstract
function
dumpTable($Q,$ym,$to=0);abstract
function
dumpData($Q,$ym,$G);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();function
printNavigation($ci){$Yg=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n",$this->admin->getServiceTitle()."\n";if($ci!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if($this->config->isVersionVerificationEnabled()&&$Yg&&version_compare(VERSION,$Yg)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($Yg)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if($this->config->isVersionVerificationEnabled()&&!$Yg)echo
script("verifyVersion('".js_escape(ME)."', '".get_token()."');");}echo"</div>\n";}abstract
function
printDatabaseSwitcher($ci);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(8)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase()).");")."</div>\n";}abstract
function
printTableList(array$S);function
getSettingsRows($of){$Vl=[];if($of==1){$B=get_language_options();if($B)$Vl["lang"]="<tr><th id='label-language'>".lang(9)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$B=[""=>lang(10),Settings::$ColorSchemeLight=>lang(11),Settings::$ColorSchemeDark=>lang(12)];$Vl["colorScheme"]="<tr><th>".lang(13)."</th>"."<td>".html_radios("colorScheme",$B,($ta=$this->settings->getParameter("colorScheme"))!==null?$ta:"")."</td></tr>\n";}elseif($of==2){$B=[""=>lang(14),true=>lang(15),false=>lang(16),];$i=$B[$this->config->isRelationLinks()];$B[""].=" ($i)";$Vl["relationLinks"]="<tr><th>".lang(17)."</th>"."<td>".html_radios("relationLinks",$B,($ta=$this->settings->getParameter("relationLinks"))!==null?$ta:"")."<span class='input-hint'>".lang(18)."</span>"."</td></tr>\n";$i=$this->config->getRecordsPerPage();$B=[""=>lang(14)." ($i)","20","30","50","70","100",];$Vl["recordsPerPage"]="<tr><th id='label-records'>".lang(19)."</th>"."<td>".html_select("recordsPerPage",$B,($ta=$this->settings->getParameter("recordsPerPage"))!==null?$ta:"","","label-records")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$i=($ta=$this->config->getEnumAsSelectThreshold())!==null?$ta:lang(21);$B=[""=>lang(14)." ($i)",-1=>lang(21),0=>lang(22),3=>lang(23,3),5=>lang(23,5),10=>lang(23,10),20=>lang(23,20),];$Vl["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(24)."</th>"."<td>".html_select("enumAsSelectThreshold",$B,($ta=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ta:"","","label-enum",true)."<span class='input-hint'>".lang(25)."</span>"."</td></tr>\n";}return$Vl;}abstract
function
getForeignColumnInfo(array$Re,$c);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$Ba,array$Xj){$this->plugins=$Xj;foreach(get_class_methods('\AdminNeo\Origin')as$Zh){$this->hooks[$Zh]=[];if(!(isset(self::$InternalMethods[$Zh])?self::$InternalMethods[$Zh]:false)){foreach($Xj
as$Wj){if(method_exists($Wj,$Zh))$this->hooks[$Zh][]=$Wj;}}if(isset(self::$AppendMethods[$Zh])?self::$AppendMethods[$Zh]:false)array_unshift($this->hooks[$Zh],$Ba);else$this->hooks[$Zh][]=$Ba;}}function
getPlugins(){return$this->plugins;}function
__call($_,array$D){$Qa=isset(self::$AppendMethods[$_])?self::$AppendMethods[$_]:false;$I=$Qa?[]:null;assert(isset($this->hooks[$_]),"Calling unknown plugin method: $_");foreach($this->hooks[$_]as$Wj){$X=call_user_func_array([$Wj,$_],$D);if($X!==null){if($Qa)$I+=$X;else
return$X;}}return$I;}function
updateCspHeader(array&$Fc){$this->__call(__FUNCTION__,[&$Fc]);}function
detectJson($xe,&$X,$ik=null){return$this->__call(__FUNCTION__,[$xe,&$X,$ik]);}}class
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
printLoginForm(){$wd=Drivers::getList();$Ll=$this->config->getServerPairs($wd);$N=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($Ll)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($Ll,$N,true)."</select>");else{$ud=DRIVER?:$this->config->getDefaultDriver($wd);if(count($wd)>1)echo$this->admin->getLoginFormRow('driver',lang(26),html_select("auth[driver]",$wd,$ud).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$ud));echo$this->admin->getLoginFormRow('server',lang(5),'<input class="input" name="auth[server]" value="'.h($N).'" title="'.lang(27).'" placeholder="localhost" autocapitalize="off">');}echo$this->admin->getLoginFormRow('username',lang(28),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(29),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$Ll){$Oc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(30),'<input class="input" name="auth[db]" value="'.h($Oc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(31)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(32)),"</p>\n";}function
getFieldName(array$k,$fj=0){$T=$k["full_type"].($k["null"]?" NULL":"");$dc=$k["comment"];$El=$T&&$dc!=""?": ":"";return'<span title="'.h($T.$El.$dc).'">'.h($k["field"]).'</span>';}function
printTableMenu(array$Nm,$Tl=""){echo'<p class="links top-tabs">';$lh=[];$Al=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($Al)$lh["select"]=[lang(33),"data"];if(support("table")||support("indexes"))$lh["table"]=[lang(34),"structure"];if(!$Al)$lh["select"]=[lang(33),"data"];$Q=$Nm["Name"];$_g=false;if(support("table")){$_g=is_view($Nm);if(!$_g){if($Q!="")$lh["create"]=[lang(35),"edit"];}elseif(support("view"))$lh["view"]=[lang(36),"edit"];}if($Tl!==null)$lh["edit"]=[lang(7),"item-add"];foreach($lh
as$u=>$W)echo" <a href='",h(ME),"$u=",urlencode($Q),($u=="edit"?$Tl:""),"'",bold(isset($_GET[$u])),">",icon($W[1]),"$W[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($Q,$_g)],icon("help").lang(37)),"\n";}function
formatSelectQuery($G,$rm,$re=false){$Dm=support("sql");$_o=!$re?Driver::get()->warnings():null;if($Dm)$G
.=";";$Gm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J="<pre><code class='jush-$Gm'>".h(str_replace("\n"," ",$G))."</code></pre>\n";$J
.="<p class='links'>";if($Dm)$J
.="<a href='".h(ME)."sql=".urlencode($G)."'>".icon("edit").lang(38)."</a>";if($_o)$J
.="<a href='#warnings' class='toggle'>".lang(39).icon_chevron_down()."</a>";$J
.=" <span class='time'>(".format_time($rm).")</span>";$J
.="</p>\n";if($_o){$J
.=script("initToggles(qsl('p'));");$J
.="<div id='warnings' class='warnings hidden'>\n$_o\n</div>\n";}return$J;}function
formatMessageQuery($G,$pn,$re=false){restart_session();$Df=&get_session("queries");if(!isset($Df[$_GET["db"]]))$Df[$_GET["db"]]=[];if(strlen($G)>1e6)$G=preg_replace('~[\x80-\xFF]+$~','',substr($G,0,1e6))."\n…";$Df[$_GET["db"]][]=[$G,time(),$pn];$Dm=support("sql");$_o=!$re?Driver::get()->warnings():null;$lm="sql-".count($Df[$_GET["db"]]);$Ao="warnings-".count($Df[$_GET["db"]]);$J=" ";if($_o)$J
.="<a href='#$Ao' class='toggle'>".lang(39).icon_chevron_down()."</a>, ";$wk=support("sql")?lang(40):lang(41);$J
.="<a href='#$lm' class='toggle'>$wk".icon_chevron_down()."</a>";$J
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($_o)$J
.="<div id='$Ao' class='warnings hidden'>\n$_o</div>\n";$J
.="<div id='$lm' class='hidden'>\n";$Gm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J
.="<pre><code class='jush-$Gm'>".truncate_utf8($G,1000)."</code></pre>\n";$J
.="<p class='links'>";if($Dm)$J
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Df[$_GET["db"]])-1))."'>".icon("edit").lang(38)."</a>";if($pn)$J
.=" <span class='time'>($pn)</span>";$J
.="</p>\n";$J
.="</div>\n";return$J;}function
formatSqlCommandQuery($G){if(preg_match('~^DELIMITER\s~i',$G))return"";return
truncate_utf8($G,1000);}function
getTableDescriptionFieldName($Q){return"";}function
fillForeignDescriptions(array$L,array$Re){return$L;}function
formatSelectionValue($W,$x,$k,$rj){if($W===null)$in="<i>NULL</i>";elseif(!$k)$in=$W;elseif(preg_match("~char|binary|boolean~",$k["type"])&&!preg_match("~var~",$k["type"]))$in="<code>$W</code>";elseif(is_blob($k)&&!is_utf8($W))$in="<i>".lang(42,strlen($rj))."</i>";elseif($this->admin->detectJson($k["full_type"],$rj))$in="<code class='jush-json'>$W</code>";else$in=$W;if($x)$in="<a href='".h($x)."'".(is_web_url($x)?target_blank():"").">$in</a>";return$in;}function
formatFieldValue($X,array$k){return$X;}function
printTableStructure(array$l){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(43),"</th>","<td>",lang(44),"</td>","<td>",lang(45),"</td>";if(support("comment"))echo"<td>",lang(46),"</td>";echo"</tr></thead>\n";$ho=Driver::get()->getUserTypes();foreach($l
as$k){echo"<tr>","<th>",h($k["field"]),"</th>","<td>";$T=h($k["full_type"]);if(in_array($T,$ho))echo"<a href='".h(ME.'type='.urlencode($T))."'>$T</a>";else
echo$T;if($k["null"])echo" <i>NULL</i>";if($k["auto_increment"])echo" <i>".lang(47)."</i>";$i=h($k["default"]);if(isset($k["default"]))echo" <span title='".lang(48)."'>[<b>",$k["generated"]?"<code class='jush-".DIALECT."'>$i</code>":$i,"</b>]</span>";echo"</td>","<td>",h($k["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($k["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$Gj){$Xl=isset($Gj["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$Gj["partition_by"]} ({$Gj["partition"]})</code>";if(!$Xl&&isset($Gj["partitions"]))echo" ".lang(49).": ".h($Gj["partitions"]);echo"</p>";if($Xl){echo"<table>\n","<thead><tr><th>".lang(50)."</th><td>".lang(51)."</td></tr></thead>\n";foreach($Gj["partition_names"]as$u=>$_){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($_)),"'>";echo
h($_);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($Gj["partition_values"][$u])."\n";}echo"</table>\n";}}function
printRelatedTables(array$S){echo"<ul class='links'>\n";foreach($S
as$K){$x=preg_replace('~ns=[^&]*~',"ns=".urlencode($K["ns"]),ME);echo"<li><a href='",h($x."table=".urlencode($K["table"])),"'>",icon("structure");if($K["ns"]!=$_GET["ns"])echo"<b>".h($K["ns"])."</b>.";echo
h($K["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$t,array$Nm){$Vc=first(Driver::get()->getIndexAlgorithms($Nm));$Dj=false;foreach($t
as$s){if(isset($s["partial"])?$s["partial"]:false){$Dj=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(52)," (",lang(53),")</td>";if($Dj)echo"<td>",lang(54),"</td>";echo"</tr></thead>\n";foreach($t
as$_=>$s){ksort($s["columns"]);$lk=[];foreach($s["columns"]as$u=>$W)$lk[]="<i>".h($W)."</i>".($s["lengths"][$u]?"(".$s["lengths"][$u].")":"").($s["descs"][$u]?" DESC":"");echo"<tr title='",h($_),"'>","<th>",$s["type"];if(isset($s['algorithm'])&&$s['algorithm']!=$Vc)echo" ({$s["algorithm"]})";echo"</th>","<td>",implode(", ",$lk),"</td>";if($Dj){echo"<td>";if($s['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($s['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$M,array$d){print_fieldset_start("select",lang(55),"columns",(bool)$M,true);$M[""]=[];$p=0;foreach($M
as$u=>$W){$W=isset($_GET["columns"][$u])?$_GET["columns"][$u]:[];$c=select_input("name='columns[$p][col]'",$d,isset($W["col"])?$W["col"]:null,$u!==""?"selectFieldChange":"selectAddRow");echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$p][fun]",[-1=>""]+array_filter([lang(56)=>Driver::get()->getFunctions(),lang(57)=>Driver::get()->getGrouping()]),isset($W["fun"])?$W["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($u!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($c)";else
echo$c;echo" <button class='button light remove jsonly' title='",h(lang(58)),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$p++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$d,array$t){print_fieldset_start("search",lang(59),"search",(bool)$Z);foreach($t
as$p=>$s){if($s["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$s["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$p]' value='".h(isset($_GET["fulltext"][$p])?$_GET["fulltext"][$p]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$p]",1,isset($_GET["boolean"][$p]),"BOOL");echo"</div>\n";}}$_b="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$p=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$p][col]'",$d,$W["col"],($W?"selectFieldChange":"selectAddRow"),"(".lang(60).")"),html_select("where[$p][op]",$this->getOperators(),$W["op"],$_b),"<input type='text' class='input' name='where[$p][val]' value='".h($W["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $_b }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".h(lang(58))."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$fj,array$d,array$t){print_fieldset_start("sort",lang(61),"sort",(bool)$fj,true);$_GET["order"][""]="";$p=0;foreach((array)$_GET["order"]as$u=>$W){if($u!=""&&$W=="")continue;echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$p]'",$d,$W,$u!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$p]",1,isset($_GET["desc"][$u]),lang(62))," <button class='button light remove jsonly' title='",h(lang(58)),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$p++;}print_fieldset_end("sort",true);}function
printSelectionLimit($w){echo"<fieldset><legend>".lang(63)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='$w'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($jn){if($jn!==null)echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($jn)."'>","</div></fieldset>\n";}function
printSelectionAction(array$t){echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(55)."'>"," <span id='noindex' title='".lang(66)."'></span>","<script".nonce().">\n";$d=new
stdClass();foreach($t
as$s){$Hc=reset($s["columns"]);if($s["type"]!="FULLTEXT"&&$Hc)$d->$Hc=null;}echo"const indexColumns = ".json_encode($d,JSON_UNESCAPED_UNICODE).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$d,array$t){$M=[];$mf=[];foreach((array)$_GET["columns"]as$u=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],Driver::get()->getFunctions())||in_array($W["fun"],Driver::get()->getGrouping())))){$M[$u]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],Driver::get()->getGrouping()))$mf[]=$M[$u];}}return[$M,$mf];}function
processSelectionSearch(array$l,array$t){$J=[];foreach($t
as$p=>$s){if($s["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$p]!="")$J[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$s["columns"])).") AGAINST (".q($_GET["fulltext"][$p]).(isset($_GET["boolean"][$p])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$Qb=$Z["col"];$Wi=$Z["op"];$W=$Z["val"];if("$Qb$W"!=""&&in_array($Wi,$this->getOperators())){$lc=[];foreach(($Qb!=""?[$Qb=>$l[$Qb]]:$l)as$_=>$k){$gk="";$kc=" $Wi";$Ki=DIALECT=="pgsql"&&$Wi=="="&&$k["type"]=="oid";if($Ki)$kc
.=" ".$this->admin->processFieldInput($k,$W)."::regproc";elseif(preg_match('~IN$~',$Wi)){$Tf=process_length($W);$kc
.=" ".($Tf!=""?$Tf:"(NULL)");}elseif($Wi=="SQL")$kc=" $W";elseif(preg_match('~^(I?LIKE) %%$~',$Wi,$y))$kc=" $y[1] ".$this->admin->processFieldInput($k,"%$W%");elseif($Wi=="FIND_IN_SET"){$gk="$Wi(".q($W).", ";$kc=")";}elseif(!preg_match('~NULL$~',$Wi))$kc
.=" ".$this->admin->processFieldInput($k,$W);if($Qb!=""||(isset($k["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$Wi)?',':'').']+$~',$W)||!preg_match('~'.number_type().'|bit~',$k["type"]))&&(!preg_match("~[\x80-\xFF]~",$W)||preg_match('~char|text|enum|set~',$k["type"]))&&(!preg_match('~date|timestamp~',$k["type"])||preg_match('~^\d+-\d+-\d+~',$W))&&(!preg_match('~^elastic~',DRIVER)||$k["type"]!="boolean"||preg_match('~true|false~',$W))&&(!preg_match('~^elastic~',DRIVER)||strpos($Wi,"regexp")===false||preg_match('~text|keyword~',$k["type"])))){if($Ki)$lc[]=$gk.idf_escape($_).$kc;else$lc[]=$gk.Driver::get()->convertSearch(idf_escape($_),$Z,$k).$kc;}}if(count($lc)==1)$J[]=$lc[0];elseif($lc)$J[]="(".implode(" OR ",$lc).")";else$J[]="1 = 0";}}return$J;}function
processSelectionOrder(array$l,array$t){$J=[];foreach((array)$_GET["order"]as$u=>$W){if($W!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$u])?" DESC":"");}return$J;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$k){$J=($k["null"]?"NULL/":"");$Yn=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$u=>$ef){if(!$u||(!isset($_GET["call"])&&$Yn)){foreach($ef
as$Qj=>$W){if(!$Qj||preg_match("~$Qj~",$k["type"]))$J
.="/$W";}}if($u&&$ef&&!preg_match('~enum|set|bool~',$k["type"])&&!is_blob($k))$J
.="/SQL";}if($k["auto_increment"]&&!$Yn)$J=lang(47);return
explode("/",$J);}function
getFieldInput($Q,array$k,$Va,$X,$df){return"";}function
processFieldInput(array$k,$X,$df=""){if($df=="SQL")return$X;if(isset($k["full_type"]))$this->admin->detectJson($k["full_type"],$X,false);$_=$k["field"];$J=q($X);if(preg_match('~^(now|getdate|uuid)$~',$df))$J="$df()";elseif(preg_match('~^current_(date|timestamp)$~',$df))$J=$df;elseif(preg_match('~^([+-]|\|\|)$~',$df))$J=idf_escape($_)." $df $J";elseif(preg_match('~^[+-] interval$~',$df))$J=idf_escape($_)." $df ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&DIALECT!="pgsql"?$X:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$df))$J="$df(".idf_escape($_).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$df))$J="$df($J)";elseif($k["type"]=="boolean"&&DIALECT=="elastic")$J=$J=="0"?"false":"true";return
unconvert_field($k,$J);}function
getDumpOutputs(){$vj=['file'=>lang(67),'text'=>lang(68),];if(function_exists('gzencode'))$vj['gz']='gzip';return$vj;}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
sendDumpHeaders($Nf,$ii=false){$uj=$_POST["output"];$ne=(str_contains($_POST["format"],"sql")?"sql":($ii?"tar":"csv"));if($uj=="gz"){header("Content-Type: application/x-gzip");ob_start(function($P){return
gzencode($P);},1e6);}elseif($ne=="tar")header("Content-Type: application/x-tar");elseif($ne=="sql"||$uj=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$ne;}function
dumpTable($Q,$ym,$to=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($ym)dump_csv(array_keys(fields($Q)));}else{if($to==2){$l=[];foreach(fields($Q)as$_=>$k)$l[]=idf_escape($_)." $k[full_type]";$Ac="CREATE TABLE ".table($Q)." (".implode(", ",$l).")";}else$Ac=create_sql($Q,$_POST["auto_increment"],$ym);set_utf8mb4($Ac);if($ym&&$Ac){if($ym=="DROP+CREATE"||$to==1)echo"DROP ".($to==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($to==1)$Ac=remove_definer($Ac);echo"$Ac;\n\n";}}}function
dumpData($Q,$ym,$G){if($ym){$Gh=(DIALECT=="sqlite"?0:1048576);$l=[];$Pf=false;if($_POST["format"]=="sql"){if($ym=="TRUNCATE+INSERT")echo
truncate_sql($Q).";\n";$l=fields($Q);if(DIALECT=="mssql"){foreach($l
as$k){if($k["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Pf=true;break;}}}}$I=Connection::get()->query($G,1);if($I){$ig="";$pb="";$Lg=[];$gf=[];$Am="";$zc=0;while($K=($Q!=''?$I->fetchAssoc():$I->fetchRow())){if(!$Lg){$Y=[];foreach($K
as$W){$k=$I->fetchField();if(!empty($l[$k->name]['generated'])){$gf[$k->name]=true;continue;}$Lg[]=$k->name;$u=idf_escape($k->name);$Y[]="$u = VALUES($u)";}$Am=($ym=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($ym=="table"){dump_csv($Lg);$ym="INSERT";}dump_csv($K);}else{if(!$ig)$ig="INSERT INTO ".table($Q)." (".implode(", ",array_map('AdminNeo\idf_escape',$Lg)).") VALUES";foreach($K
as$u=>$W){if(isset($gf[$u])){unset($K[$u]);continue;}$k=$l[$u];$K[$u]=($W===null?"NULL":($W===false?0:unconvert_field($k,preg_match(number_type(),$k["type"])&&!preg_match('~\[~',$k["full_type"])&&is_numeric($W)?$W:(!is_blob($k)||is_utf8($W)?q($W):Driver::get()->quoteBinary($W)))));}$il=($Gh?"\n":" ")."(".implode(",\t",$K).")";if(!$pb)$pb=$ig.$il;elseif(DIALECT=="mssql"?$zc%1000!=0:strlen($pb)+4+strlen($il)+strlen($Am)<$Gh)$pb
.=",$il";else{echo$pb.$Am;$pb=$ig.$il;}}$zc++;}if($pb)echo$pb.$Am;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Pf)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$Bi=isset($_GET["ns"])?$_GET["ns"]:null;if($Bi==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(69),"</a>\n";if($Bi!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(70),"</a>\n";if($Bi!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(71),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(72),"</a>\n";echo"</p>\n";}function
printNavigation($ci){parent::printNavigation($ci);if($ci=="auth"){$uj="";foreach((array)$_SESSION["pwds"]as$po=>$Pl){foreach($Pl
as$N=>$io){foreach($io
as$U=>$E){if($E!==null){$Tc=$_SESSION["db"][$po][$N][$U];foreach(($Tc?array_keys($Tc):[""])as$h){$Ml=$this->admin->getServerName($N,false);$sn=h(get_driver_name($po,$N)).($U!=""||$Ml!=""?" - ":"").h($U).($U!=""&&$Ml!=""?"@":"").h($Ml).($h!=""?h(" - $h"):"");$uj
.="<li><a href='".h(auth_url($po,$N,$U,$h))."' class='primary' title='$sn'>$sn</a></li>\n";}}}}}if($uj)echo"<nav id='logins'><menu>\n$uj</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($ci);$xa=[];if(DB==""||!$ci){if(support("sql")){$xa[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(40)."</a>";$xa[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(73)."</a>";}$xa[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(74)."</a>";}if(DB=="")$xa[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(75)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$ci)$xa[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$ci)$xa[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(77)."</a>\n";if($xa)echo"<p class='links'>".implode("\n",$xa)."</p>";$S=[];if($_GET["ns"]!==""&&!$ci&&DB!=""){Connection::get()->selectDatabase(DB);$S=table_status('',true);}if($_GET["ns"]!==""&&!$ci&&DB!=""){if($S){$this->admin->printTablesFilter();$this->admin->printTableList($S);}else
echo"<p class='message'>".lang(78)."</p>\n";}if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$S){$lh=[];foreach($S
as$Q=>$T)$lh[]=preg_quote($Q,'/');$Mm=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$Mm.'=$&'),': /\b('.implode('|',$lh).')\b/g';if(support('routine')){foreach(routines()as$K)echo",\n",js_escape_key(ME.'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&'),': /\b'.preg_quote($K["ROUTINE_NAME"],'/').'(?=["`]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$W)echo"jushLinks.$W = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$Ym=array_fill_keys(array_keys($S),[]);foreach(Driver::get()->getAllFields()as$Q=>$l){foreach($l
as$k)$Ym[$Q][]=$k["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($Ym)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($ci){$g=$this->admin->getDatabases();if(!$g&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($g)echo"<select id='database-select' name='db' title='",lang(30),"'>".optionlist([""=>"(".lang(79).")"]+$g,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(30),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(80)."' class='button ".($g?"hidden":"")."'>\n","</div>";if(support("scheme")&&$ci!="db"&&DB!=""&&Connection::get()->selectDatabase(DB)){echo"<div>","<select id='scheme-select' name='ns' title='",lang(81),"'>".optionlist([""=>"(".lang(82).")"]+$this->admin->getSchemas(),$_GET["ns"])."</select>".script("mixin(gid('scheme-select'), {onmousedown: dbMouseDown, onchange: dbChange});"),"</div>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}foreach(["import","sql","schema","dump","privileges"]as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</form></div>\n";}function
printTableList(array$S){$Ph=($this->settings->isNavigationDual()?"class='dual'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><menu $Ph>";foreach($S
as$Q=>$O){$Q="$Q";$_=$this->admin->getTableName($O);if($_==""||(isset($O["Partition"])?$O["Partition"]:false))continue;echo"<li>";$ya=in_array($Q,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$Nb="primary".(is_view($O)?" view":"");$Em=support("table")||support("indexes");$yl=h(ME)."select=".urlencode($Q);$Om=h(ME)."table=".urlencode($Q);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$Em)echo" <a href='$Om' title='",lang(34),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$yl'",bold($ya,$Nb)," data-primary='true' title='$_'>$_</a>";if($this->settings->isNavigationDual()&&$Em)echo" <a href='$Om' title='",lang(34),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$yl' title='",lang(33),"' class='secondary'>",icon("data"),"</a>";if($Em)echo"<a href='$Om'",bold($ya,$Nb)," data-primary='true' title='$_'>$_</a>";else
echo"<span data-primary='true'",bold($ya,$Nb),">$_</span>";if($this->settings->isNavigationDual())echo" <a href='$yl' title='",lang(33),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase()).");");}function
getSettingsRows($of){$Vl=parent::getSettingsRows($of);if($of==1){$B=[""=>lang(14),Config::$NavigationSimple=>lang(83),Config::$NavigationDual=>lang(84),Config::$NavigationReversed=>lang(85)];$i=$B[$this->config->getNavigationMode()];$B[""].=" ($i)";$Vl["navigationMode"]="<tr><th>".lang(86)."</th>"."<td>".html_radios("navigationMode",$B,($ta=$this->settings->getParameter("navigationMode"))!==null?$ta:"")."<span class='input-hint'>".lang(87)."</span>"."</td></tr>\n";$B=[""=>lang(14),0=>lang(34),1=>lang(33),];$i=$B[$this->config->isSelectionPreferred()?1:0];$B[""].=" ($i)";$Vl["preferSelection"]="<tr><th id='label-links'>".lang(88)."</th>"."<td>".html_select("preferSelection",$B,($ta=$this->settings->getParameter("preferSelection"))!==null?$ta:"","","label-links",true)."<span class='input-hint'>".lang(89)."</span>"."</td></tr>\n";}return$Vl;}function
getForeignColumnInfo(array$Re,$c){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($vc){if(!$this->handler)return;$this->size+=strlen($vc);fwrite($this->handler,$vc);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$I,$e=null,array$lj=[],$w=0){$lh=[];$t=[];$d=[];$lb=[];$On=[];$J=[];for($p=0;(!$w||$p<$w)&&($K=$I->fetchRow());$p++){if(!$p){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($Dg=0;$Dg<count($K);$Dg++){$k=$I->fetchField();if(!$k){echo"<th></th>";continue;}$_=$k->name;$kj=isset($k->orgtable)?$k->orgtable:"";$jj=isset($k->orgname)?$k->orgname:$_;if(isset($k->table))$J[$k->table]=$kj;if($lj&&DIALECT=="sql")$lh[$Dg]=($_=="table"?"table=":($_=="possible_keys"?"indexes=":null));elseif($kj!=""){if(!isset($t[$kj])){$t[$kj]=[];foreach(indexes($kj,$e)as$s){if($s["type"]=="PRIMARY"){$t[$kj]=array_flip($s["columns"]);break;}}$d[$kj]=$t[$kj];}if(isset($d[$kj][$jj])){unset($d[$kj][$jj]);$t[$kj][$jj]=$Dg;$lh[$Dg]=$kj;}}if($k->charsetnr==63)$lb[$Dg]=true;$On[$Dg]=$k->type;echo"<th".($kj!=""||$k->name!=$jj?" title='".h(($kj!=""?"$kj.":"").$jj)."'":"").">".h($_).($lj?doc_link(['sql'=>"explain-output.html#explain_".strtolower($_),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$u=>$W){$x="";if(isset($lh[$u])&&!$d[$lh[$u]]){if($lj&&DIALECT=="sql"){$Q=$K[array_search("table=",$lh)];$x=ME.$lh[$u].urlencode($lj[$Q]!=""?$lj[$Q]:$Q);}else{$x=ME."edit=".urlencode($lh[$u]);foreach($t[$lh[$u]]as$Qb=>$Dg)$x
.="&where".urlencode("[".bracket_escape($Qb)."]")."=".urlencode($K[$Dg]);}}$T=($lb[$u]?'blob':($On[$u]==254?'char':''));$k=['full_type'=>$T,'type'=>$T,];$W=select_value($W,$x,$k,null);$Nb=$On[$u]<=9||$On[$u]==246?"class='number'":"";echo"<td $Nb>$W</td>";}}if($p)echo"</table>\n</div>";else
echo"<p class='message'>".lang(90);echo"\n";return$J;}function
referencable_primary($Cl){$J=[];foreach(table_status('',true)as$Rm=>$Q){if($Rm!=$Cl&&fk_support($Q)){foreach(fields($Rm)as$k){if($k["primary"]){if($J[$Rm]){unset($J[$Rm]);break;}$J[$Rm]=$k;}}}}return$J;}function
textarea($_,$X,$L=10,$Xb=80){echo"<textarea name='".h($_)."' rows='$L' cols='$Xb' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($Va,$B,$X="",$Ui="",$Tj=""){$cn=($B?"select":"input");return"<$cn $Va".($B?"><option value=''>$Tj".optionlist($B,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$Tj'>").($Ui?script("qsl('$cn').onchange = $Ui;",""):"");}function
json_row($u,$W=null){static$He=true;if($He)echo"{";if($u!=""){echo($He?"":",")."\n\t\"".addcslashes($u,"\r\n\t\"\\/").'": '.($W!==null?'"'.addcslashes($W,"\r\n\t\"\\/").'"':'null');$He=false;}else{echo"\n}\n";$He=true;}}function
edit_type($u,$k,$Tb,$Se=[],$qe=[]){$T=isset($k["type"])?$k["type"]:null;echo'<td><select name="',h($u),'[type]" class="type" aria-labelledby="label-type">';$vd=Driver::get()->getTypes();if($T&&!isset($vd[$T])&&!isset($Se[$T])&&!in_array($T,$qe))$qe[]=$T;$xm=Driver::get()->getStructuredTypes();if($Se)$xm[lang(91)]=$Se;echo
optionlist(array_merge($qe,$xm),$T),'</select><td><input name="',h($u),'[length]" value="',h(isset($k["length"])?$k["length"]:null),'" size="3"',(!(isset($k["length"])?$k["length"]:null)&&preg_match('~var(char|binary)$~',$T)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Tb?"<select name='".h($u)."[collation]'".(preg_match('~(char|text|enum|set)$~',$T)?"":" class='hidden'").'><option value="">('.lang(92).')'.optionlist($Tb,isset($k["collation"])?$k["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($u)."[unsigned]'".(!$T||preg_match(number_type(),$T)?"":" class='hidden'").'><option>'.optionlist(Driver::get()->getUnsigned(),isset($k["unsigned"])?$k["unsigned"]:null).'</select>':''),(isset($k['on_update'])?"<select name='".h($u)."[on_update]'".(preg_match('~timestamp|datetime~',$T)?"":" class='hidden'").'>'.optionlist([""=>"(".lang(93).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"CURRENT_TIMESTAMP":$k["on_update"])).'</select>':''),($Se?"<select name='".h($u)."[on_delete]'".(preg_match("~`~",$T)?"":" class='hidden'")."><option value=''>(".lang(94).")".optionlist(Driver::get()->getOnActions(),isset($k["on_delete"])?$k["on_delete"]:null)."</select> ":" ");}function
process_length($v){$Ud=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$Ud(?:\\s*,\\s*$Ud)*+\\s*\\)?\\s*\$~",$v)&&preg_match_all("~$Ud~",$v,$z)?"(".implode(",",$z[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$v)));}function
process_type($k,$Rb="COLLATE"){return" $k[type]".process_length($k["length"]).(preg_match(number_type(),$k["type"])&&in_array($k["unsigned"],Driver::get()->getUnsigned())?" $k[unsigned]":"").(preg_match('~char|text|enum|set~',$k["type"])&&$k["collation"]?" $Rb ".(DIALECT=="mssql"?$k["collation"]:q($k["collation"])):"");}function
process_field($k,$Mn){if($k["on_update"])$k["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$k["on_update"]);return[idf_escape(trim($k["field"])),process_type($Mn),($k["null"]?" NULL":" NOT NULL"),default_value($k),(preg_match('~timestamp|datetime~',$k["type"])&&$k["on_update"]?" ON UPDATE ".$k["on_update"]:""),(support("comment")&&$k["comment"]!=""?" COMMENT ".q($k["comment"]):""),($k["auto_increment"]?auto_increment():null),];}function
default_value($k){if($k["default"]===null)return"";$i=str_replace("\r","",$k["default"]);$ff=$k["generated"];if(in_array($ff,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($i)".($ff=="VIRTUAL"?"":" $ff");else
return" GENERATED ALWAYS AS ($i) $ff";}if(stripos($i,"GENERATED ")===0)return" $i";if(preg_match('~char|binary|text|json|enum|set~',$k["type"])||preg_match('~^(?![a-z])~i',$i)){if(DIALECT=="sql"&&preg_match('~text|json~',$k["type"]))return" DEFAULT (".q($i).")";else
return" DEFAULT ".q($i);}else{$i=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$i);return" DEFAULT ".(DIALECT=="sqlite"?"($i)":$i);}}function
type_class($T){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$Nb=>$Qj){if(preg_match("~$Nb|$Qj~",$T))return"class='$Nb'";}return"";}function
edit_fields(array$l,array$Tb,$T="TABLE",$Se=[]){$l=array_values($l);$hc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$ec=$hc?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<td class='jsonly'></td>";if($T=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($T=="TABLE"?lang(95):lang(96)),"</th>\n","<td id='label-type'>",lang(44),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(97),"</td>\n","<td>",lang(98),"</td>\n";if($T=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(47),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",]),"</td>\n","<td id='label-default'>",lang(48),"</td>\n",support("comment")?"<td id='label-comment' $ec>".lang(46)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($l)),"]' value='1' title='",h(lang(99)),"' class='button light'>",icon_solo("add"),"</button>",script("row_count = ".count($l).";"),"</td>\n","</tr></thead>\n";$Nb=support("move_col")?"class='sortable'":"";echo"<tbody $Nb>\n";foreach($l
as$p=>$k){$p++;$mj=$k[($_POST?"orig":"field")];$ld=(isset($_POST["add"][$p-1])||(isset($k["field"])&&!(isset($_POST["drop_col"][$p])?$_POST["drop_col"][$p]:null)))&&(support("drop_col")||$mj=="");$ym=$ld?"":"style='display: none;'";echo"<tr $ym>\n";if(support("move_col"))echo"<td class='handle jsonly'>",icon_solo("handle"),"</td>";if($T=="PROCEDURE")echo"<td>",html_select("fields[$p][inout]",Driver::get()->getInOut(),$k["inout"]),"</td>\n";echo"<th>";if($ld)echo"<input class='input' name='fields[$p][field]' value='",h($k["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$p-1])?"autofocus":"").">";echo
input_hidden("fields[$p][orig]",$mj);edit_type("fields[$p]",$k,$Tb,$Se);echo"</th>\n";if($T=="TABLE"){echo"<td>",checkbox("fields[$p][null]",1,$k["null"],"","","block","label-null"),"</td>\n";$Hb=$k["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$p' $Hb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$p][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$k["generated"]);else
echo
checkbox("fields[$p][generated]",1,$k["generated"],"","","","label-default");$Va="name='fields[$p][default]' aria-labelledby='label-default'";$X=h($k["default"]);if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Va rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Va value='$X'>";echo"</td>\n";if(support("comment")){$Fh=Connection::get()->isMinVersion("5.5")?1024:255;echo"<td $ec>","<input class='input' name='fields[$p][comment]' value='",h($k["comment"]),"' data-maxlength='$Fh' aria-labelledby='label-comment'>","</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$p]' value='1' title='".h(lang(99))."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$p]' value='1' title='".h(lang(100))."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$p]' value='1' title='".h(lang(101))."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($mj==""||support("drop_col"))echo"<button name='drop_col[$p]' value='1' title='".h(lang(58))."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$l){$A=0;if($_POST["up"]){$Wg=0;foreach($l
as$u=>$k){if(key($_POST["up"])==$u){unset($l[$u]);array_splice($l,$Wg,0,[$k]);break;}if(isset($k["field"]))$Wg=$A;$A++;}}elseif($_POST["down"]){$Xe=false;foreach($l
as$u=>$k){if(isset($k["field"])&&$Xe){unset($l[key($_POST["down"])]);array_splice($l,$A,0,[$Xe]);break;}if(key($_POST["down"])==$u)$Xe=$k;$A++;}}elseif($_POST["add"]){$l=array_values($l);array_splice($l,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($y){$W=$y[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($W[0].$W[0],$W[0],substr($W,1,-1))),'\\'))."'";}function
grant($jf,array$ok,$d,$Si,$go){if(!$ok)return
true;if($ok==["ALL PRIVILEGES","GRANT OPTION"]){if($jf)return(bool)queries("GRANT ALL PRIVILEGES ON $Si TO $go WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $Si FROM $go")&&queries("REVOKE GRANT OPTION ON $Si FROM $go");}if($ok==["GRANT OPTION","PROXY"]){if($jf)return(bool)queries("GRANT PROXY ON $Si TO $go WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $Si FROM $go");}return(bool)queries(($jf?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$d, ",$ok).$d)." ON $Si ".($jf?"TO ":"FROM ").$go);}function
drop_create($xd,$Ac,$zd,$hn,$Ad,$uh,$Th,$Rh,$Sh,$Qi,$wi){if($_POST["drop"])query_redirect($xd,$uh,$Th);elseif($Qi=="")query_redirect($Ac,$uh,$Sh);elseif($Qi!=$wi){$Dc=queries($Ac);queries_redirect($uh,$Rh,$Dc&&queries($xd));if($Dc)queries($zd);}else
queries_redirect($uh,$Rh,queries($hn)&&queries($Ad)&&queries($xd)&&queries($Ac));}function
create_trigger($Si,array$Fn){$rn=" $Fn[Timing] $Fn[Event]".(preg_match('~ OF~',$Fn["Event"])?" $Fn[Of]":"");return"CREATE TRIGGER ".idf_escape($Fn["Trigger"]).(DIALECT=="mssql"?$Si.$rn:$rn.$Si).rtrim(" $Fn[Type]\n$Fn[Statement]",";").";";}function
create_routine($cl,$K){$Tl=[];$l=(array)$K["fields"];ksort($l);$Uf=implode("|",Driver::get()->getInOut());foreach($l
as$k){if($k["field"]!="")$Tl[]=(preg_match("~^($Uf)\$~",$k["inout"])?"$k[inout] ":"").idf_escape($k["field"]).process_type($k,"CHARACTER SET");}$Zc=rtrim($K["definition"],";");return"CREATE $cl ".idf_escape(trim($K["name"]))." (".implode(", ",$Tl).")".($cl=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(DIALECT=="pgsql"?" AS ".q($Zc):"\n$Zc;");}function
remove_definer($G){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$G);}function
format_foreign_key($o){$Ti=implode("|",Driver::get()->getOnActions());$h=$o["db"];$Bi=$o["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$o["source"])).") REFERENCES ".($h!=""&&$h!=$_GET["db"]?idf_escape($h).".":"").($Bi!=""&&$Bi!=$_GET["ns"]?idf_escape($Bi).".":"").idf_escape($o["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$o["target"])).")".(preg_match("~^($Ti)\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^($Ti)\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").(isset($o["deferrable"])?" $o[deferrable]":"");}function
tar_file($n,TmpFile$vn){$_f=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($vn->getSize()),decoct(time()));$Jb=8*32;for($p=0;$p<strlen($_f);$p++)$Jb+=ord($_f[$p]);$_f
.=sprintf("%06o",$Jb)."\0 ";echo$_f,str_repeat("\0",512-strlen($_f));$vn->send();echo
str_repeat("\0",511-($vn->getSize()+511)%512);}function
doc_link(array$Pj,$in="<sup>?</sup>"){if(!(isset($Pj[DIALECT])?$Pj[DIALECT]:null))return"";$qo=doc_version();$bo=['sql'=>"https://dev.mysql.com/doc/refman/$qo/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$qo)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$qo)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$qo/",];if(Connection::get()->isMariaDB()){$bo['sql']="https://mariadb.com/docs/server/";$Pj['sql']=isset($Pj['mariadb'])?$Pj['mariadb']:str_replace(".html","",$Pj['sql']);}return"<a href='".h($bo[DIALECT].$Pj[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$qo":""))."'".target_blank().">$in</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($h){if(!Connection::get()->selectDatabase($h))return"?";$J=0;foreach(table_status()as$R)$J+=$R["Data_length"]+$R["Index_length"];return
format_number($J);}function
set_utf8mb4($Ac){static$Tl=false;if(!$Tl&&preg_match('~\butf8mb4~i',$Ac)){$Tl=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($Wd,$j){return(bool)preg_match('~^Undefined (array key|offset|index)~',$j);},E_WARNING|E_NOTICE);;$Ee=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Ee||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$W){$Vn=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($Vn)$$W=$Vn;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$D,$u=null){$this->params=$D;$this->key=$u;}function
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
getConfigParams(){$D=isset($this->params["config"])?$this->params["config"]:[];$if=["servers"];foreach($if
as$zj){if(isset($D[$zj]))unset($D[$zj]);}return$D;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$D){$this->params=$D;if(isset($this->params["servers"])){foreach($this->params["servers"]as$u=>$N){$Kl=new
Server($N,is_string($u)?$u:null);$this->params["servers"][$u]=$Kl;$this->servers[$Kl->getKey()]=$Kl;}}}function
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
getDefaultDriver(array$wd){$ud=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$ud&&isset($wd[$ud])?$ud:key($wd);}function
getDefaultServer(){$N=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($N===null)return
null;$Kl=isset($this->params["servers"][$N])?$this->params["servers"][$N]:null;if($Kl)return$Kl->getKey();return$N;}function
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
getServerPairs(array$wd){$am=null;foreach($this->servers
as$N){if(!isset($wd[$N->getDriver()]))continue;if(!$am)$am=$N->getDriver();elseif($N->getDriver()!=$am){$am=null;break;}}$Ll=[];foreach($this->servers
as$u=>$N){if(!isset($wd[$N->getDriver()]))continue;$Jl=$N->getName();if($am&&$Jl)$Ll[$u]=$Jl;else$Ll[$u]=$wd[$N->getDriver()].($Jl!=""?" - $Jl":"");}return$Ll;}function
getServer($Il){return
isset($this->servers[$Il])?$this->servers[$Il]:null;}function
applyServer($N){$N=$this->getServer($N);if(!$N)return;$this->params=array_merge($this->params,$N->getConfigParams());}private
function
parseList($nh){if(is_array($nh))return$nh;return
preg_split('~\s*,\s*~',(string)$nh);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$mc){$this->config=$mc;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($u){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$D);return
isset($D[$u])?$D[$u]:null;}function
getParameter($u,$i=null){return
isset($this->params[$u])?$this->params[$u]:$i;}function
updateParameter($u,$X){$this->updateParameters([$u=>$X]);}function
updateParameters(array$D){$this->params=array_filter(array_merge($this->params,$D),function($X){return$X!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ta=$this->getParameter("navigationMode"))!==null?$ta:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$Ko=$this->getParameter("navigationWidth");if($Ko===null)return
null;return
min(max((float)$Ko,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ta=$this->getParameter("preferSelection"))!==null?$ta:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ta=$this->getParameter("recordsPerPage"))!==null?$ta:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$X=$this->getParameter("enumAsSelectThreshold");if($X<0)return
null;return$X!==null?(int)$X:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($v,$u,$dg="",$jl=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$u,$v,$dg,$jl);if($jl=="")$jl=str_repeat("\0",20);$pk=self::hmacSha1($u,$jl);$Mi="";for($Jg="",$mb=1;!isset($Mi[$v-1]);$mb++){$Jg=self::hmacSha1($Jg.$dg.chr($mb),$pk);$Mi
.=$Jg;}return
substr($Mi,0,$v);}static
function
hmacSha1($f,$u){if(!extension_loaded("hash"))return
hash_hmac("sha1",$f,$u,true);if(strlen($u)>64)$u=sha1($u,true);$u=str_pad($u,64,"\0");$qg=($u^str_repeat("\x36",64));$Xi=($u^str_repeat("\x5C",64));return
sha1($Xi.sha1($qg.$f,true),true);}}class
Random{static
function
strongKey(){return
strtr(rtrim(base64_encode(Random::bytes(32)),"="),"+/","-_");}static
function
bytes($v){if(PHP_VERSION_ID>=70000)return
random_bytes($v);$I=self::tryAlternatives($v);if($I!==false)return$I;$I=self::lastResortRandom($v);if($I!==false)return$I;throw
new
Exception("Error generating random bytes");}private
static
function
tryAlternatives($v){if(extension_loaded("libsodium"))return
\Sodium\randombytes_buf($v);$Un=DIRECTORY_SEPARATOR==="/";if($Un){$I=self::readDevUrandom($v);if($I!==false)return$I;}$qb=$Un&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$qb){$I=mcrypt_create_iv($v,MCRYPT_DEV_URANDOM);if($I!==false)return$I;}$rb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$rb){$I=openssl_random_pseudo_bytes($v,$wm);if($wm)return$I;}return
false;}private
static
function
readDevUrandom($v){static$m=null;if($m===null)$m=@fopen("/dev/urandom","rb");if(!$m)return
false;$Nk=$v;$I="";do{$f=fread($m,$Nk);if($f===false)return
false;$Nk-=strlen($f);$I
.=$f;}while($Nk>0);return$I;}private
static
function
readCapicom($v){$ac=new
\COM("CAPICOM.Utilities.1");$Nk=$v;$I="";do{$f=base64_decode((string)$ac->GetRandom($v,0));$Nk-=strlen($f);$I
.=$f;}while($Nk>0);return$I;}private
static
function
lastResortRandom($v){static$u=null;static$jl=null;if($u===null){$f=$_SERVER;$f[]=uniqid("",true);shuffle($f);$u=sha1(serialize($f),true);if(extension_loaded("openssl"))$jl=openssl_random_pseudo_bytes(20);else{$jl="";for($p=0;$p<20;$p++)$jl
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($u)%2===0)===(ord($jl)%2===0))$u=Hash::hmacSha1($u,$jl);else$jl=Hash::hmacSha1($jl,$u);}return
Hash::hkdf($v,$u,"$v",$jl);}}if(!function_exists("str_starts_with")){function
str_starts_with($zf,$si){return
strpos($zf,$si)===0;}}if(!function_exists("str_contains")){function
str_contains($zf,$si){return
strpos($zf,$si)!==false;}}if(!function_exists("password_verify")){function
password_verify($E,$yf){return
false;}}if(!function_exists("ini_set")){function
ini_set($bj,$X){return
false;}}function
version(){return
VERSION;}function
idf_unescape($r){if(!preg_match('~^[`\'"[]~',$r))return$r;$Wg=substr($r,-1);return
str_replace($Wg.$Wg,$Wg,substr($r,1,-1));}function
q($P){return
Connection::get()->quote($P);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$Y,$Ee=false){$J=[];foreach($Y
as$u=>$W)$J[stripslashes($u)]=(is_array($W)?remove_slashes($W,$Ee):($Ee?$W:stripslashes($W)));return$J;}function
bracket_escape($r,$eb=false){static$An=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($r,($eb?array_flip($An):$An));}function
min_version($qo,$Ah=null,$e=null){if(!$e)$e=Connection::get();if($Ah&&$e->isMariaDB())$qo=$Ah;return$qo&&$e->isMinVersion($qo);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($_,array$De){switch($_){case'favicon-blue.ico':$n='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__6bb95962.ico';break;case'favicon-green.ico':$n='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__6bb95962.ico';break;case'favicon-orange.ico':$n='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__6bb95962.ico';break;case'favicon-purple.ico':$n='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__6bb95962.ico';break;case'favicon-red.ico':$n='favicon-red-c2ebb34a8df5aba28e15d87728a151df__6bb95962.ico';break;case'favicon-blue.svg':$n='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__6bb95962.svg';break;case'favicon-green.svg':$n='favicon-green-bb254c95a033f67e3d433a3df63e160d__6bb95962.svg';break;case'favicon-orange.svg':$n='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__6bb95962.svg';break;case'favicon-purple.svg':$n='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__6bb95962.svg';break;case'favicon-red.svg':$n='favicon-red-a006e401273230fd6be80568c8361b57__6bb95962.svg';break;case'apple-touch-icon-blue.png':$n='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__6bb95962.png';break;case'apple-touch-icon-green.png':$n='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__6bb95962.png';break;case'apple-touch-icon-orange.png':$n='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__6bb95962.png';break;case'apple-touch-icon-purple.png':$n='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__6bb95962.png';break;case'apple-touch-icon-red.png':$n='apple-touch-icon-red-507228751d2170d047e72142d2c02390__6bb95962.png';break;case'logo.svg':$n='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$n='jush-b3a93b18444da26820ff61746521dede__6f96e697.css';break;case'jush-dark.css':$n='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$n='jush-31ccd1ce96536a294822e952872683d1__fc7b22bf.js';break;case'icons.svg':$n='icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg';break;case'default-blue.css':$n='default-blue-564b3ff62703b0741b8754503c621af3__006fca63.css';break;case'default-green.css':$n='default-green-8facfae54345a3eb358848ed4141060f__006fca63.css';break;case'default-orange.css':$n='default-orange-4fd2276ffa8eaad143aec2dba3782911__b085cace.css';break;case'default-purple.css':$n='default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__b085cace.css';break;case'default-red.css':$n='default-red-9c7de6d1d78ea798bfef943c92b6b611__006fca63.css';break;case'default-blue-dark.css':$n='default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css';break;case'default-green-dark.css':$n='default-green-dark-d7e561f7fc07f913992951110461fd8c__7a7f64b1.css';break;case'default-orange-dark.css':$n='default-orange-dark-e6668a1545546a87b40acb95390b5283__1e3abf59.css';break;case'default-purple-dark.css':$n='default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__1e3abf59.css';break;case'default-red-dark.css':$n='default-red-dark-aa471f32fb495651c17bba291cd8b147__7a7f64b1.css';break;case'main.js':$n='main-eaf2ce2c3d91edbef355936903e47e59__d91c4c0d.js';break;default:$n=null;break;}if(!$n)return
null;return
BASE_URL."?file=".urldecode($n);}function
ini_bool($bj){$W=ini_get($bj);return
preg_match('~^(on|true|yes)$~i',$W)||(int)$W;}function
ini_bytes($fg){$W=ini_get($fg);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
sid(){static$J;if($J===null)$J=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
save_driver_name($ud,$N,$_){restart_session();$_SESSION["drivers"][$ud][$N]=$_;stop_session();}function
get_driver_name($ud,$N=null){return
isset($_SESSION["drivers"][$ud][$N])?$_SESSION["drivers"][$ud][$N]:Drivers::get($ud);}function
save_login($ud,$N,$U,$E,$h=""){$u=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$ud][$N][$U]=$u?[encrypt_string($E,$u)]:$E;$_SESSION["db"][$ud][$N][$U][$h]=true;}function
delete_login($ud,$N,$U){unset($_SESSION["pwds"][$ud][$N][$U]);unset($_SESSION["db"][$ud][$N][$U]);}function
get_password(){$E=get_session("pwds");if(is_array($E))return$_COOKIE["neo_key"]?decrypt_string($E[0],$_COOKIE["neo_key"]):false;return$E;}function
get_vals($G,$c=0){$J=[];$I=Connection::get()->query($G);if(is_object($I)){while($K=$I->fetchRow())$J[]=$K[$c];}return$J;}function
get_key_vals($G,$e=null,$Ul=true){if(!$e)$e=Connection::get();$J=[];$I=$e->query($G);if(is_object($I)){while($K=$I->fetchRow()){if($Ul)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($G,$e=null,$j="<p class='error'>"){if(!$e)$e=Connection::get();$J=[];$I=$e->query($G);if(is_object($I)){while($K=$I->fetchAssoc())$J[]=$K;}elseif(!$I&&!is_object($e)&&$j&&(defined("AdminNeo\PAGE_HEADER")||$j=="-- "))echo$j.error()."\n";return$J;}function
unique_array(array$K,array$t){foreach($t
as$s){if(!preg_match("~PRIMARY|UNIQUE~",$s["type"])&&!$s["partial"])continue;$Rn=[];foreach($s["columns"]as$u){if(!isset($K[$u]))continue
2;$Rn[$u]=$K[$u];}return$Rn;}return
null;}function
escape_key($u){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$u,$y))return$y[1].idf_escape(idf_unescape($y[2])).$y[3];return
idf_escape($u);}function
where($Z,$l=[]){$lc=[];foreach((array)$Z["where"]as$u=>$W){$u=bracket_escape($u,true);$c=escape_key($u);$_e=isset($l[$u]["type"])?$l[$u]["type"]:null;$bf=isset($l[$u]["full_type"])?$l[$u]["full_type"]:null;if(DIALECT=="sql"&&$_e=="json")$lc[]="$c = CAST(".q($W)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$bf))$lc[]="$c::jsonb = ".q($W)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($W)&&strpos($W,".")!==false)$lc[]="$c LIKE ".q($W);elseif(DIALECT=="mssql"&&strpos($_e,"datetime")===false)$lc[]="$c LIKE ".q(preg_replace('~[_%[]~','[\0]',$W));else$lc[]="$c = ".(isset($l[$u])?unconvert_field($l[$u],q($W)):q($W));if(DIALECT=="sql"&&preg_match('~char|text~',$_e)&&preg_match("~[^ -@]~",$W))$lc[]="$c = ".q($W)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$u)$lc[]=escape_key($u)." IS NULL";return
implode(" AND ",$lc);}function
where_check($W,$l=[]){parse_str($W,$Db);remove_slashes([&$Db]);return
where($Db,$l);}function
where_link($p,$c,$X,$Yi="="){return"&where%5B$p%5D%5Bcol%5D=".urlencode($c)."&where%5B$p%5D%5Bop%5D=".urlencode(($X!==null?$Yi:"IS NULL"))."&where%5B$p%5D%5Bval%5D=".urlencode($X);}function
convert_fields(array$d,array$l,array$M=[]){$I="";foreach($d
as$u=>$W){if($M&&!in_array(idf_escape($u),$M))continue;$Ta=convert_field($l[$u]);if($Ta)$I
.=", $Ta AS ".idf_escape($u);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($_,$X,$gh=2592000){header("Set-Cookie: $_=".rawurlencode($X).($gh?"; expires=".gmdate("D, d M Y H:i:s",time()+$gh)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($ao,$wc){$J=@file_get_contents($ao,false,$wc);if(function_exists('http_get_last_response_headers'))$http_response_header=($ta=http_get_last_response_headers())!==null?$ta:[];return[$J,isset($http_response_header)?$http_response_header:[]];}function
get_settings($yc="neo_settings"){parse_str(isset($_COOKIE[$yc])?$_COOKIE[$yc]:"",$Vl);return$Vl;}function
get_setting($u,$yc="neo_settings"){$Vl=get_settings($yc);return
isset($Vl[$u])?$Vl[$u]:null;}function
save_settings(array$Vl,$yc="neo_settings"){cookie($yc,http_build_query($Vl+get_settings($yc)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($Pe=false){$eo=ini_bool("session.use_cookies");if(!$eo||$Pe){session_write_close();if($eo&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($u){return$_SESSION[$u][DRIVER][SERVER][$_GET["username"]];}function
set_session($u,$W){$_SESSION[$u][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($po,$N,$U,$h=null){$Zn=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($h!==null?"db|":"").($po=='mssql'||$po=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$Zn,$y);return"$y[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").urlencode($po)."=".urlencode($N)."&".($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($U).($h!=""?"&db=".urlencode($h):"").($y[2]?"&$y[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($uh,$Qh=null){if($Qh!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($uh!==null?$uh:$_SERVER["REQUEST_URI"]))][]=$Qh;}if($uh!==null){if($uh=="")$uh=".";header("Location: $uh");exit;}}function
query_redirect($G,$uh,$Qh,$Dk=true,$ee=true,$re=false,$pn=""){if($ee){$rm=microtime(true);$re=!Connection::get()->query($G);$pn=format_time($rm);}$km=$G?Admin::get()->formatMessageQuery($G,$pn,$re):"";if($re){Admin::get()->addError(error().$km.script("initToggles();"));return
false;}if($Dk)redirect($uh,$Qh.$km);return
true;}function
queries_redirect($uh,$Qh,$Dk){$vk=implode("\n",Queries::$queries);$pn=format_time(Queries::$start);return
query_redirect($vk,$uh,$Qh,$Dk,false,!$Dk,$pn);}class
Queries{static$queries=[];static$start=0.0;}function
queries($G){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql")){Queries::$queries[]=(preg_match('~;$~',$G)?"DELIMITER ;;\n$G;\nDELIMITER ":$G).";";return
Connection::get()->query($G);}else{Queries::$queries[]=$G;return[];}}function
apply_queries($G,array$S,$Yd='AdminNeo\table'){foreach($S
as$Q){if(!queries("$G ".$Yd($Q)))return
false;}return
true;}function
format_time($rm){return
lang(102,max(0,microtime(true)-$rm));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($zj=""){return
substr(preg_replace("~(?<=[?&])($zj".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($u,$Uc=false,$cd=""){$m=$_FILES[$u];if(!$m)return
null;foreach($m
as$u=>$W)$m[$u]=(array)$W;$J='';foreach($m["error"]as$u=>$j){if($j)return$j;$_=$m["name"][$u];$wn=$m["tmp_name"][$u];$uc=file_get_contents($Uc&&preg_match('~\.gz$~',$_)?"compress.zlib://$wn":$wn);if($Uc){$rm=substr($uc,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$rm))$uc=iconv("utf-16","utf-8",$uc);elseif($rm=="\xEF\xBB\xBF")$uc=substr($uc,3);}if($cd){if(!preg_match("~$cd\\s*\$~",$uc))$uc
.=";";$uc
.="\n\n";}$J
.=$uc;}return$J;}function
upload_error($j){$Jh=($j==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($j?lang(103).($Jh?" ".lang(104,$Jh):""):lang(105));}function
repeat_pattern($Qj,$v){return
str_repeat("$Qj{0,65535}",$v/65535)."$Qj{0,".($v%65535)."}";}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
format_number($W){return
strtr(number_format($W,0,".",lang(106)),preg_split('~~u',lang(107),-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($Q,$te=false){$J=table_status($Q,$te);return($J?reset($J):["Name"=>$Q]);}function
column_foreign_keys($Q){$J=[];foreach(Admin::get()->getForeignKeys($Q)as$o){foreach($o["source"]as$W)$J[$W][]=$o;}return$J;}function
fields_from_edit(){$J=[];foreach((array)$_POST["field_keys"]as$u=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$u];$_POST["fields"][$W]=$_POST["field_vals"][$u];}}foreach((array)$_POST["fields"]as$u=>$W){$_=bracket_escape($u,true);$J[$_]=["field"=>$_,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($u==Driver::get()->primary),];}return$J;}function
dump_headers($Nf,$ji=false){$Nf=friendly_url($Nf).date("-Ymd-His");$ne=Admin::get()->sendDumpHeaders($Nf,$ji);$uj=$_POST["output"];if($uj!="text")header("Content-Disposition: attachment; filename=$Nf.$ne".($uj!="file"&&preg_match('~^[0-9a-z]+$~',$uj)?".$uj":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$ne;}function
dump_table_order(array$pi,array$Jk){$Pg=array_flip($pi);$hj=[];$yo=[];$Kc=false;$xo=function($_)use(&$xo,&$hj,&$yo,&$Kc,$Pg,$Jk){if(isset($hj[$_]))return;if(isset($yo[$_])){$Kc=true;return;}$yo[$_]=true;foreach(isset($Jk[$_])?$Jk[$_]:[]as$Hk){if(isset($Pg[$Hk]))$xo($Hk);}unset($yo[$_]);$hj[$_]=true;};foreach($pi
as$_)$xo($_);return($Kc?null:array_keys($hj));}function
dump_csv($K){$Ln=$_POST["format"]=="tsv";foreach($K
as$u=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Ln?'\t':'[,;]|^$').'~',$W))$K[$u]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($Ln?"\t":";")),$K)."\r\n";}function
apply_sql_function($df,$c){return($df?($df=="unixepoch"?"DATETIME($c, '$df')":($df=="count distinct"?"COUNT(DISTINCT ":strtoupper("$df("))."$c)"):$c);}function
get_temp_dir(){$Oj=ini_get("upload_tmp_dir");if(!$Oj)$Oj=sys_get_temp_dir();return$Oj;}function
open_file_with_lock($n){if(is_link($n))return
null;$m=@fopen($n,"c+");if(!$m)return
null;@chmod($n,0660);if(!flock($m,LOCK_EX)){fclose($m);return
null;}return$m;}function
write_and_unlock_file($m,$f){rewind($m);fwrite($m,$f);ftruncate($m,strlen($f));unlock_file($m);}function
unlock_file($m){flock($m,LOCK_UN);fclose($m);}function
first(array$Sa){return
reset($Sa);}function
get_private_key($Ac){$n=get_temp_dir()."/adminneo.key";if(!$Ac&&!file_exists($n))return
false;$m=open_file_with_lock($n);if(!$m)return
false;$u=stream_get_contents($m);if(!$u){$u=Random::strongKey();write_and_unlock_file($m,$u);}else
unlock_file($m);return$u;}function
get_random_string(){return
Random::strongKey();}function
select_value($W,$x,$k,$ln){if(is_array($W)){$J="";if(array_filter($W,'is_array')==array_values($W)){$Lg=[];foreach($W
as$V)$Lg+=array_fill_keys(array_keys($V),null);foreach(array_keys($Lg)as$Fg)$J
.="<th>".h($Fg);foreach($W
as$V){$J
.="<tr>";foreach(array_merge($Lg,$V)as$jo)$J
.="<td>".select_value($jo,$x,$k,$ln);}}else{foreach($W
as$Fg=>$V)$J
.="<tr>".($W!=array_values($W)?"<th>".h($Fg):"")."<td>".select_value($V,$x,$k,$ln);}return"<table>$J</table>";}$ol="";if($k&&$W!==null&&($ln===null||strlen($W)<=$ln)&&($Y=Driver::get()->explodeArrayValue($W,$k["full_type"],$ol))){$nl=$k;$nl["type"]=$nl["full_type"]=$ol;$J=select_array_value($Y,$W,$x,$nl,$ln);return
Driver::get()->implodeArrayValues($J,$k["full_type"]);}if(!$x)$x=Admin::get()->getFieldValueLink($W,$k);if($k)$W=Connection::get()->formatValue($W,$k);$J=$k?Admin::get()->formatFieldValue($W,$k):$W;if($J!==null){if(!is_utf8($J))$J="\0";elseif($ln!=""&&is_shortable($k))$J=truncate_utf8($J,max(0,+$ln));else$J=h($J);}return
Admin::get()->formatSelectionValue($J,$x,$k,$W);}function
select_array_value(array$Y,$W,$x,array$k,$ln){$I=[];foreach($Y
as$X){if(is_array($X))$I[]=select_array_value($X,$W,$x,$k,$ln);else{$Qg=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($W),"~")."~",'${1}'.urlencode($X),$x);$I[]=select_value($X,$Qg,$k,$ln);}}return$I;}function
is_blob(array$k){$On=Driver::get()->getStructuredTypes();$T=lang(108);return
preg_match('~blob|bytea|raw|file~',$k["type"])&&!in_array($k["type"],isset($On[$T])?$On[$T]:[]);}function
is_mail($X){return
is_string($X)&&filter_var($X,FILTER_VALIDATE_EMAIL);}function
is_web_url($X){if(!is_string($X)||!preg_match('~^(https?:)?//~i',$X))return
false;$ic=parse_url($X);if(!$ic)return
false;$ao=$X;if(isset($ic['path'])){$Pd=array_map('urlencode',explode('/',$ic['path']));$ao=str_replace($ic['path'],implode('/',$Pd),$ao);}if(isset($ic['query'])){parse_str($ic['query'],$D);$ao=str_replace($ic['query'],http_build_query($D),$ao);}if(!isset($ic['scheme']))$ao="https:$ao";return(bool)filter_var($ao,FILTER_VALIDATE_URL);}function
is_shortable($k){return$k&&!preg_match('~'.number_type().'|date|time|year~',$k["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$y)?[(isset($y[4])?$y[4]:"").(isset($y[5])?$y[5]:""),$y[2].(isset($y[8])?$y[8]:"")]:[$N,'']);}function
count_rows($Q,$Z,$wg,$mf){$G=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($wg&&(DIALECT=="sql"||count($mf)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$mf).")$G":"SELECT COUNT(*)".($wg?" FROM (SELECT 1$G GROUP BY ".implode(", ",$mf).") x":$G));}function
slow_query($G){$h=Admin::get()->getDatabase();$qn=Admin::get()->getQueryTimeout();$dm=Driver::get()->slowQuery($G,$qn);$e=null;if(!$dm&&support("kill")){$e=connect();if($e&&($h==""||$e->selectDatabase($h))){$Ng=$e->getValue(connection_id());echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Ng,'&token=',get_token(),'\');
	}, ',1000*$qn,');
</script>
';}}ob_flush();flush();$J=@get_key_vals(($dm?:$G),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$Ak=rand(1,1e6);return($Ak^$_SESSION["token"]).":$Ak";}function
verify_token(){list($xn,$Ak)=explode(":",$_POST["token"]);return($Ak^$_SESSION["token"])==$xn&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($gm,$_n="\n"){return"<script".nonce().">$gm</script>$_n";}function
script_src($ao,$Yc=false){return"<script src='".h($ao)."'".nonce().($Yc?" defer":"")."></script>\n";}function
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
icon($q,$Nb=null){$q=h($q);return"<svg class='icon ic-$q $Nb'><use href='".link_files("icons.svg",[])."#$q'/></svg>";}function
checkbox($_,$X,$Hb,$Rg="",$Vi="",$Nb="",$Tg=""){$J="<input type='checkbox' name='$_' value='".h($X)."'".($Hb?" checked":"").($Tg?" aria-labelledby='$Tg'":"").">".($Vi?script("qsl('input').onclick = function () { $Vi };",""):"");return($Rg!=""||$Nb?"<label".($Nb?" class='$Nb'":"").">$J".h($Rg)."</label>":$J);}function
optionlist($B,$_l=null,$fo=false){$J="";foreach($B
as$Fg=>$V){$ej=[$Fg=>$V];if(is_array($V)){$J
.='<optgroup label="'.h($Fg).'">';$ej=$V;}foreach($ej
as$u=>$W)$J
.='<option'.($fo||is_string($u)?' value="'.h($u).'"':'').($_l!==null&&($fo||is_string($u)?(string)$u:$W)===$_l?' selected':'').'>'.h($W);if(is_array($V))$J
.='</optgroup>';}return$J;}function
html_select($_,$B,$X="",$Ui="",$Tg="",$fo=false){static$Rg=0;$Sg="";if(!$Tg&&substr(isset($B[""])?$B[""]:"",0,1)=="("){$Rg++;$Tg="label-$Rg";$Sg="<option value='' id='$Tg'>".h($B[""]);unset($B[""]);}return"<select name='".h($_)."'".($Tg?" aria-labelledby='$Tg'":"").">".$Sg.optionlist($B,$X,$fo)."</select>".($Ui?script("qsl('select').onchange = function () { $Ui };",""):"");}function
html_radios($_,$B,$X=""){$I="<span class='labels'>";foreach($B
as$u=>$W)$I
.="<label><input type='radio' name='".h($_)."' value='".h($u)."'".($u==$X?" checked":"").">".h($W)."</label>";$I
.="</span>";return$I;}function
confirm($Qh="",$Bl="qsl('input')"){return
script("$Bl.onclick = () => confirm('".($Qh?js_escape($Qh):lang(109))."');","");}function
print_fieldset_start($q,$ch,$Mf,$vo=false,$fm=false){echo"<fieldset id='fieldset-$q' class='closable ".(!$vo?" closed":"")."'>","<legend><a href='#'>$ch</a></legend>",icon($Mf,"fieldset-icon jsonly"),"<div class='fieldset-content".($fm?" sortable":"")."'>";}function
print_fieldset_end($q,$fm=false){echo"</div>",script("initFieldset('$q');","");if($fm)echo
script("initSortable('#fieldset-$q .fieldset-content');","");echo"</fieldset>\n";}function
bold($nb,$Nb=""){return($nb?" class='$Nb active'":($Nb?" class='$Nb'":""));}function
js_escape($P){return
addcslashes($P,"\r\n'\\/");}function
js_escape_key($P){return'"'.addcslashes($P,"\r\n\t\"\\/").'"';}function
pagination($C,$Gc){return"<li>".($C==$Gc?"<strong>".($C+1)."</strong>":'<a href="'.h(remove_from_uri("page").($C?"&page=$C".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($C+1)."</a>")."</li>";}function
print_hidden_fields(array$qk,array$Rf=[],$gk=""){$I=false;foreach($qk
as$u=>$W){if(!in_array($u,$Rf)){if(is_array($W))print_hidden_fields($W,[],$u);else{$I=true;echo
input_hidden($gk?$gk."[$u]":$u,$W);}}}return$I;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Va,array$k,$X,$Md=null,$Gb=false){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$z);$Y=$z[1];$on=Admin::get()->getSettings()->getEnumAsSelectThreshold();$M=!$Gb&&$on!==null&&count($Y)>$on;$T=$Gb?"checkbox":"radio";$za=$M?"selected":"checked";$I=$M?"<select $Va>":"<span class='labels'>";if($M&&$k["null"]&&$Md!==""){$Hb=$X===null?$za:"";$I
.="<option value='__adminneo_empty__' disabled $Hb></option>";}if($Md!==null){$Hb=(is_array($X)?in_array($Md,$X):$X===$Md)?$za:"";if($M)$I
.="<option value='$Md' $Hb>".lang(110)."</option>";else$I
.="<label><input type='$T' $Va value='$Md' $Hb><i>".lang(110)."</i></label>";}foreach($Y
as$W){if($Md===""&&$W==="")continue;$W=stripcslashes(str_replace("''","'",$W));$Hb=is_array($X)?in_array($W,$X):$X===$W;$Hb=$Hb?$za:"";$We=$W===""?("<i>".lang(110)."</i>"):h(Admin::get()->formatFieldValue($W,$k));if($M)$I
.="<option value='".h($W)."' $Hb>$We</option>";else$I
.=" <label><input type='$T' $Va value='".h($W)."' $Hb>$We</label>";}$I
.=$M?"</select>":"</span>";return$I;}function
input($k,$X,$df,$bb=false){$_=h(bracket_escape($k["field"]));$On=Driver::get()->getTypes();$xg=isset($k["full_type"])&&Admin::get()->detectJson($k["full_type"],$X,true);$Rk=(DIALECT=="mssql"&&$k["auto_increment"]&&!$_POST["clone"]);if($Rk&&!$_POST["save"])$df=null;if(in_array($k["type"],Driver::get()->getUserTypes())){$Vd=type_values($On[$k["type"]]);if($Vd){$k["type"]="enum";$k["length"]=$Vd;}}$Va=" name='fields[$_]' ".($bb?" autofocus":"");$ef=(isset($_GET["select"])||$Rk?["orig"=>lang(111)]:[])+Admin::get()->getFieldFunctions($k);$wf=(in_array($df,$ef)||isset($ef[$df]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($k)." ";if(count($ef)>1){$_l=$df===null||$wf?$df:"";echo"<select name='function[$_]'>".optionlist($ef,$_l)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($ef));echo"</td><td>";$gg=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$k,$Va,$X,$df);if($gg!="")echo$gg;elseif(preg_match('~bool~',$k["type"]))echo"<input type='hidden'$Va value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked='checked'":"")."$Va value='1'>";elseif($k["type"]=="enum")echo
enum_input($Va,$k,$X);elseif($k["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$z);echo"<span class='labels'>";foreach($z[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$Hb=$X!==null&&in_array($W,explode(",",$X),true);$Hb=$Hb?"checked":"";$We=$W===""?("<i>".lang(110)."</i>"):h(Admin::get()->formatFieldValue($W,$k));echo" <label><input type='checkbox' name='fields[$_][]' value='".h($W)."' $Hb>$We</label>";}echo"</span>";}elseif(is_blob($k)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$_'>";elseif($xg)echo"<textarea $Va cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($in=preg_match('~text|lob|memo|json~i',$k["type"]))||preg_match("~\n~",$X)){if($in&&DIALECT!="sqlite")$Va
.=" cols='50' rows='12'";else{$L=min(12,substr_count($X,"\n")+1);$Va
.=" cols='30' rows='$L'";}echo"<textarea $Va>".h($X).'</textarea>';}else{$Mh=!preg_match('~int~',$k["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$k["length"],$y)?((preg_match("~binary~",$k["type"])?2:1)*$y[1]+($y[3]?1:0)+($y[2]&&!$k["unsigned"]?1:0)):($On&&$On[$k["type"]]?$On[$k["type"]]+($k["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$k["type"]))$Mh+=7;echo"<input class='input'".((!$wf||$df==="")&&preg_match('~(?<!o)int(?!er)~',$k["type"])&&!preg_match('~\[\]~',$k["full_type"])?" type='number'":"").($df!="now"?" value='".h($X)."'":" data-last-value='".h($X)."'").($Mh?" data-maxlength='$Mh'":"").(preg_match('~char|binary~',$k["type"])&&$Mh>20?" size='44'":"")."$Va>";}$Cf=Admin::get()->getFieldInputHint($_GET["edit"],$k,$X);if($Cf!="")echo" <span class='input-hint'>$Cf</span>";$Ie=0;foreach($ef
as$u=>$W){if($u===""||!$W)break;$Ie++;}if(count($ef)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Ie);");}function
process_input($k){$r=bracket_escape($k["field"]);$df=isset($_POST["function"][$r])?$_POST["function"][$r]:"";if($df=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?idf_escape($k["field"]):false);if($df=="NULL")return
Driver::get()->getNull();if(is_blob($k)&&ini_bool("file_uploads")){$m=get_file("fields-$r");if(!is_string($m))return
false;return
Driver::get()->quoteBinary($m);}$X=isset($_POST["fields"][$r])?$_POST["fields"][$r]:(isset($_FILES["fields"]["name"][$r])?$_FILES["fields"]["name"][$r]:null);if($X===null)return
false;if($k["auto_increment"]&&$X=="")return
null;if($k["type"]=="set")$X=implode(",",(array)$X);if($df=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}return
Admin::get()->processFieldInput($k,$X,$df);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Xk=$Xd=[];foreach(table_status("",true)as$Q=>$R){$Rm=Admin::get()->getTableName($R);if(!isset($R["Engine"])||$Rm==""||($_POST["tables"]&&!in_array($Q,$_POST["tables"])))continue;$I=Connection::get()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($Q),[])),1));if($I&&!$I->fetchRow())continue;$x=h(ME."select=".urlencode($Q)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($I)$Xk[]="<li><a href='$x'>".icon("search")."$Rm</a></li>";else$Xd[]="<div class='error'><a href='$x'>$Rm</a>: ".error()."</div>";}if($Xk)echo"<ul class='links'>\n",implode("\n",$Xk),"</ul>\n";if($Xd)echo
implode("\n",$Xd),"\n";if(!$Xk&&!$Xd)echo"<p class='message'>".lang(78)."</p>\n";}function
help_script($in,$Zl=false){return
script("initHelpFor(qsl('select, input'), '".h($in)."', $Zl);","");}function
help_script_command($bc,$Zl=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $bc; }, $Zl);","");}function
edit_form($Q,$l,$K,$Yn){$Rm=Admin::get()->getTableName(table_status1($Q,true));$sn=$Yn?lang(38):lang(112);page_header("$sn: $Rm",["select"=>[$Q,$Rm],$sn]);if($K===false){echo"<p class='error'>".lang(90)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Hd=false;if(!$l)echo"<p class='error'>".lang(113)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$bb=!$_POST;foreach($l
as$_=>$k){echo"<tr><th>".Admin::get()->getFieldName($k);$u=bracket_escape($_);$i=isset($_GET["set"][$u])?$_GET["set"][$u]:null;if($i===null){$i=$k["default"];if($k["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$i,$Lk))$i=$Lk[1];if(DIALECT=="sql"&&preg_match('~binary~',$k["type"]))$i=bin2hex($i);}$X=($K!==null?($K[$_]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$k["type"])&&is_array($K[$_])?implode(",",$K[$_]):(is_bool($K[$_])?+$K[$_]:$K[$_])):(!$Yn&&$k["auto_increment"]?"":(isset($_GET["select"])?false:$i)));if(!$_POST["save"]&&is_string($X))$X=Admin::get()->formatFieldValue($X,$k);if(($Yn&&!isset($k["privileges"]["update"]))||$k["generated"]){echo"<td class='function'></td><td>";if($Yn||!$k["generated"])echo
select_value($X,'',$k,null);else
echo"<code class='jush-".DIALECT."'>",h($X),"</code>";echo"</td>";}else{$Hd=true;$df=($_POST["save"]?isset($_POST["function"][$_])?$_POST["function"][$_]:"":($Yn&&preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$Yn&&$X==$k["default"]&&preg_match('~^[\w.]+\(~',$X))$df="SQL";if(preg_match("~time~",$k["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$df="now";}if($k["type"]=="uuid"&&$X=="uuid()"){$X="";$df="uuid";}if($bb!==false)$bb=($k["auto_increment"]||$df=="now"||$df=="uuid"?null:true);input($k,$X,$df,(bool)$bb);if($bb)$bb=false;}echo"\n";}if(!support("table")&&!fields($Q))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");}echo"<p>";if($Hd){echo"<input type='submit' class='button default' value='".lang(114)."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' class='button' name='insert' value='".($Yn?lang(115):lang(116))."' title='Ctrl+Shift+Enter'>\n",($Yn?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(117)."…', this); };"):"");}echo($Yn?"<input type='submit' class='button' name='delete' value='".lang(118)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($Te,$hg){$Eh=ini_get("max_file_uploads");$Jh=ini_get("upload_max_filesize");$Kh=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($Te)."', '".js_escape($hg)."', "."$Eh, '".lang(119,$Eh,"\'max_file_uploads\'")."', "."$Kh, '".lang(120,$Jh,"\'upload_max_filesize\'")."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($P){$Oa=array_flip(str_split(compress_alphabet()));$v=strlen($P);$mo=($v?13*($v-1)/2-$Oa[$P[0]]:0);$jb="";$Vk=0;$Wk=0;for($p=1;$p<$v;$p+=2){$Vk=($Vk<<13)+$Oa[$P[$p]]*93+$Oa[$P[$p+1]];$Wk+=13;while($Wk>=8&&$mo>=8){$Wk-=8;$mo-=8;$jb
.=chr($Vk>>$Wk);$Vk&=(1<<$Wk)-1;}}if($jb=="")return"";return
function_exists('gzinflate')?gzinflate($jb):inflate($jb);}function
inflate($jb){$dh=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$eh=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$nd=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$pd=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$J="";$bk=0;do{$Ge=inflate_bits($jb,$bk,1);$T=inflate_bits($jb,$bk,2);if(!$T){$bk=($bk+7)&~7;$v=inflate_bits($jb,$bk,16);$bk+=16;$J
.=substr($jb,$bk>>3,$v);$bk+=$v<<3;}else{if($T==1){$ph=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$qd=array_fill(0,30,5);}else{$oh=inflate_bits($jb,$bk,5)+257;$od=inflate_bits($jb,$bk,5)+1;$fj=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$Xh=array_fill(0,19,0);$Wh=inflate_bits($jb,$bk,4)+4;for($p=0;$p<$Wh;$p++)$Xh[$fj[$p]]=inflate_bits($jb,$bk,3);$Yh=inflate_table($Xh);$fh=[];while(count($fh)<$oh+$od){$Fm=inflate_symbol($jb,$bk,$Yh);if($Fm==16)$fh=array_merge($fh,array_fill(0,inflate_bits($jb,$bk,2)+3,end($fh)));elseif($Fm==17)$fh=array_merge($fh,array_fill(0,inflate_bits($jb,$bk,3)+3,0));elseif($Fm==18)$fh=array_merge($fh,array_fill(0,inflate_bits($jb,$bk,7)+11,0));else$fh[]=$Fm;}$ph=array_slice($fh,0,$oh);$qd=array_slice($fh,$oh);}$qh=inflate_table($ph);$sd=inflate_table($qd);while(($Fm=inflate_symbol($jb,$bk,$qh))!=256){if($Fm<256)$J
.=chr($Fm);else{$v=$dh[$Fm-257]+inflate_bits($jb,$bk,$eh[$Fm-257]);$rd=inflate_symbol($jb,$bk,$sd);$A=strlen($J)-$nd[$rd]-inflate_bits($jb,$bk,$pd[$rd]);for($p=0;$p<$v;$p++)$J
.=$J[$A+$p];}}}}while(!$Ge);return$J;}function
inflate_bits($jb,&$bk,$zc){$J=0;for($p=0;$p<$zc;$p++){$J+=((ord($jb[$bk>>3])>>($bk&7))&1)<<$p;$bk++;}return$J;}function
inflate_table(array$fh){$Q=[];$Pb=0;for($kb=1;$kb<=max($fh);$kb++){foreach($fh
as$Fm=>$v){if($v==$kb){$Q[$kb][$Pb]=$Fm;$Pb++;}}$Pb<<=1;}return$Q;}function
inflate_symbol($jb,&$bk,array$Q){$Pb=0;$kb=0;do{$Pb=($Pb<<1)+inflate_bits($jb,$bk,1);$kb++;}while(!isset($Q[$kb][$Pb]));return$Q[$kb][$Pb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($n){if($n==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$ne=pathinfo($n,PATHINFO_EXTENSION);switch($ne){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($n){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__6bb95962.svg':$f='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-bb254c95a033f67e3d433a3df63e160d__6bb95962.svg':$f='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__6bb95962.svg':$f='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-4cfd57d31ab991e8071fe34060cd3123__6bb95962.svg':$f='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-a006e401273230fd6be80568c8361b57__6bb95962.svg':$f='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-507228751d2170d047e72142d2c02390__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg':$f='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
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
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__6f96e697.css':$f='+UEmPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M)rQq_opI7=E.gy$2_wn3[@yoG6r~P5/:mrvY<e>#2+8qezLLv^&nr;/Kkr(>?R(rf#PZ<Kx
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
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-31ccd1ce96536a294822e952872683d1__fc7b22bf.js':$f='&hk^;xqDE.!v]yOYu=(i
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
d%=.2Qw3Bb';break;case'default-blue-564b3ff62703b0741b8754503c621af3__006fca63.css':$f=')erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(&7`$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]vPG8`;E;R30aa7<b@aIv3LTDEEJnPV!Xp)qJdvq|mKA)^=i31Y<^kzJqpBDM5iar@mXU=_S+?GH1hlN6kHbf8JTe?V!)yZXS7xqwx==<=Lr(U3yo+Ix:ZVP9Tr.9B,W4[D.PeiNDFiT@=_LoZ=;`@rR(p"SE$Ea$b1]!%aMxw_8{w(5gFU`#=!Itw6T{N/7L3N8%&m6|O}.PrGhy1P01gH:+`iM&(fFGM/@xso?,CwRq/xsML~H!]~UV;XG}hByeM^w;jxMbiAtQtu_rsw:IyRIivrSRsPpbMxpdn-q+L921ELz&H27|l5qjXaklS8m6ycz(T/s043@3tQu2Ktw6K6yFf]fkz#h/MAo%=FKr78u)16!L6evk!N_vMjToz!eGL-u{M|yUY"w_7(v^`sw;5Jyff%wqv|y-2Mnye/MvxjMwuzK{MBa=eIyfcTuz(o!OBaS8qJc$q]n7X%v|My5RJ4rl,W?Kd#^l=mJprF-vsPsA?8$hn-<Z.I:%mIP]5qo#.B)KiJ[D&}?SoTxDG&E9!wZ:x3MTj3wkkvpEkuY?D6
Nhq.3YR*Er{s@tZ"(o|NVN0N~/k$+iSK*C2OX9P#XVL3y5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,X$f$kG1d>1b.qd2=$fHyP)/,C<tPFoTtlVOo_h_G?$dzV$1m20bW(hy}Cu8n9vFB]g8Be
a3PbL31Vb;"l(Kepm
#qF3qWK.X7e}Dw*:@N"1DIVD$+7}FnFq;nN-/1pM`^-D#0TZN9#{VTE+"RcL8Tkx,uXl`#aOWK4P*LW"V:EUn)%2!do%5ddp@(]#lBcyX25U,^>/Y7^[VG,aP#">e^i=y,@pww<_`?h&!gm?W>N+E4b;daa;/t8.!s^Guod29YEb=C1P)(:_NwTQs8-~GjAYZl)aj`l:ELo5qz^wwIE1q/ollg
9o;>*?KRYx
F%J,
%qyLWvZi[cDUHlfqq!pFGF$?5cjuD!&Xc[&`6YpLnTQ/3w7RDhU$
:@dU1NT1l+T5.,Yj[K#h:!hF9!a3O1!b<eT9U$qHG@G(rEg`<p#~bjRW4n4yjX>?@"DV%aJcXxWiF|_c!t*Z)9d*N_fH=a-fxBJvPa234c1[7L,w_Wg
#a#eu
+SBm%.l`eCpl3%?(D)<
_s5,;F9q1b%sJrCq:-!Wp5!7GHt
[%FH](_bKiStnLSpV2"hb/U-/
p~1m>g>,%A><>hlVwW1Y$+`:
IhxZR-uVn#&8-0/*F+8b$tM)6lV/YA<[=xJZ^sMCx2Z`ISXQX@E7Dbi#v2*tw:xUTNlc-NiGlqaBD4^Vc1R)z0g(L@]H$kW!hYEGA32?E_btML:M6/cX@"|j9`d5Nlt&s>axb`b_NwfN-q50kn7;KyzQjN;2yVtoR
QnJfynP2FPCs0P=k-e+R!b(ooeISOim27RBm^^.]lPB/x@<&0u2eUxCw8Oev9jkH
Wi0#7IALkc$a3.u63zU_8"&wYc)SuEg"`s,$"WZG+}jZ*pWoi/3-Z@vAm9`~U45NTB=&x..)`U;70Z1spbN2br&l#rXG:yqLEg^IH_bnX(0.Jt(:4Amh4Z3nHW,IO194svlTo@bbffU,xlt0I"BekXlyw8F)8g4*CK&?9K%$%Y8?tdgq3=fsYE_dwE[*`$"3XF"0FO8|lhVnGk?3!92I%h#2e<Z!!.OI6Z38^N0edM?D
%JxG:8rfh(%n<OW%/<VG95=5;;N.E?V
9)O7Z[qycV}"Wo^RNq?v%US$?dac<UkWK9`>H*)k3hW%0q5(+,ht
)6>3Q_ge4;CMp_Tpexk_
K*d;c8@ka/u_nptDIxd1hgmvHBWu]I~d+
#Y@L)d.=8Py$#*^KSe+gQ[Os:B@$P,
gi<p>X->>BOJV|2iW:bEN*+RMQsGYLOG(WU,s+&oc))mW#:jZC
H)W,ix5sl.vh5->T`dx#t;I!Kq5`#E=T*pK"UT+IAVpB!T7y`(+V`&F6fNx#r3I,knE9ZPr5L=/l*/%qVd#nvs7]W$]43BfuN12ikx>R)(lZXqpDxLaV6/I1C9yL6x1-D&Fc~)c533p
BW9U?5
9W8~.psPiX"#Jz;<Es
Ig69c4v"N
CXPm">ScL*Eo=y7<|BDw=3oujZVrtw=.0lJtn
:Hju;+VY{s:w3nJv,$,fDj_GLnTpH.$e,kP1IPCfCx6w[P2f(P48jNX7<mu"rSdUBF_e?AhcfkEQ6K#L(*4?4Ji^GBM&
8jLsp
8]?cNz?K7>OlhGc2(;A@-pdD*[1q>AaA(Q_dI{]K]c<_:34E4sM[3uSUO5xuG<W|;<s&LoVTh9)"sx5tT?D(=>*`)?uMN
OP0hhD1tcw?|VBY^sOwd$pb56x7(;RFf%Q9JZojIOBjzPZn*ll+]C+"T8}T%MIwmK%j6JYLMVkk-1&l!x0Tv*k7x@sv,;*nDwCHYb/Kr%Y>.#W&G_i63&]N0>0&OG.&+(6&xJ&bdOkdaG!TeBs$DOO<&9p9l44E!dqYl)oVC2f`!**q:l|A:Rwd1j$EILZe~vfT&49Q8Fc?tReP0CT-$
,-M3ER;0:n/g:H},i,!3+SKZie{4Ju#
Qr?<0G"q[
b#m3=6K;5JIdMsy3&R-R1u!C857:!ihWt8O=]cwH+:*G*EF)ccDBZ$|Mx,ULjQElwv]#<exh0mqU*aVB-O(yrH28"v$N`siMUk5(|:z.~XPA_bQ&#mC`SSp4{9MALPTuF8cI{PPhOP
lyq%VpD!z#SToZKy/.@u/EPVVgU^<HC{Z1q^W2Pt<]EC.j&30/Vb6yF-(=61oJ0oS7lyYft#Dj("?1UUTamlPB_%:-yJ?mQPoNDXWr@|szC~GZeY)^$SjNS"d@h4<&BIuc(VW%,^8;oe(;ci,J,YNuDIQI8Ydk)68HBAN*B~HDqIGz,lR"gBZd-%(qa%$;oW&IP$tpx*,DLJk/*c_ieW6m"C&V-/qx&xcT?V0v+6oBbN80Q5*9K2d9M;-XM[d+"1J}?RK]>{Lik9PoKx0&<
5WTJqW6@nL3V&HDsIZ)2D3E/w33Jjlv,+bJQ>;rzcd03ATehiyBa3[AF
"Mpv[xu5#?%6
5=f2NpA>;f),k"xZqA&s%-Mvu+&V7Lj7s:ySr9Za^&k@z#Em)yD82u&LrBj<CesMfp*44cl1iBvYF
HHGdjHLnVS
`W#<m`q<jsugJT:"9b^9Po:]dwGI}V]tx&>k7?kC5VEvA*!qoe~!&d.&`B<1u$vNaj*rVvA,[U_fp!m_<l@xPD+t3$k<<8"*I^B#(-uJk^_8h;+p6Hpw#Olg1uUEvc=m?9YvgfW.-_%yp9!C~B,C_J?Z:7.(-/GN&a~Y//K=vWup-=s/;YzFBo4O0IK"R,B#dcsLnuNJ7l5<[*M]F_iwH7yLP*@_Cwro$f%0f(tRB6LX
PAo>1Q<H!6PRg,w~K##/Vzar>nW|Dyu"W>/,OKL}CNJrd2!4UGZyX+
rvcIR6cr5cQny?MP/j<G6u+d~UTiQC{[^QLT{NK%_[)z"q;>#$JA]6(<zBE=RV*G:pks/.jx4F:C&h72k"rAY#u9:-5>d:~s^K)#^nejbPF&}Ew5?)Ka/G"rF#0B/*
e&rfp_%.;4;V>fc}.NsdjKy+<<G>m&gw@R@0IL6mP+4s>NO:<`(weG!*jPhs93I(wQV{nWs*ZDu9pfls.f-yuO09cRON["%RZ&SA)b9j"KuQqZ%A<XiLdmx{5a<$K?Y1<?75<w5l@[DkpBZ6L)wyMvbCZ&d[>,jQ>L>-R5,`cB"|`
YETNd3J&W5#v>`"j570("i$XUrM)rU#M_OGp#IYkc:,saUf&${T.$k(UO;n1"e(lOR3KDJ%"-jt0yLAM02)C;_BR/BlW_-C08Rsu_WMeu~jVo{d`v;#u&4H_f_D%4>)S!QhU;`+Su%ad)<N7"sSWf|e%I;l"^:!ihb=4e$JvC6/kA_$/2Hsz$-Y!sj?y4%khaR^y
5K.3BP#%)e}%8dTe@];J34sBRQsn!R<l<[Uhi$>G-mJcvwPAA)or"bw
5sS8/Zq+1l=2;Yd3a;bCo*gbD
($n=Q]zOkLS
jg}01*dEnvp+DV^(
t0hz>AT}sA9sk5nV@duI4uk~=8Q}"AI@_f^@`}@jYp=p9my7?:a>YMfE;`.iV.UZeAsJ!A*)NMFA.wHGV8.lI!+Zy>J(0iJ2sW?GM,e8@(OEL+li]<q7eWT^++G0g-U6J^[FSLD_?X:"?%%}4fX@*$a
h}B}pflz0+obTIuFuKHC2&0sM-UPjLj~NM?_b~&Z^3CNvSJ.2;$w$[AZu5cN:^<Y)cx7_Qv]3P!/.|U``:Uzgn"?tQN@ZqirT("HvaP"_EID_Q1?Kp5(mt?2khbE,{jtFy]6:d^TO{u[_=Sn8WFWLon~S1W:14uWT+C4"?J!6Mp34{H
;Ig&M_5_1QV+4x$6ENAW${3~C)di4a7q_UPy$iJT)Jay>&KQtd@OvF<iU<3/
kHtFg0_UV/w4iH.JHQU)4C4Zejj;xY7&HY~E%5/Xq>/Ak0<2RO%9OlN,ZBK$2MTKxC[lAPTg[@Ox=d0&Dk:g:k^L#]<kuAH4.7y_T(]Ou*nk".5XZ*ma9$xQk[%wM_q`]M7)9IrDpq8QAj}>i-1<~Q$+.8PRGsLbf>zgR@vOOV/
~!)a!-R.YrvpfRTeLR&9/J^2TWCc#Ey9`cR)PWm?je4eji=`>8KA94R2`h%e!!wTF&A$5!W5RxiR>5&m<]`.J0*@U,1FD4ClC`dMU%P!&24.
E>L@w8/@jnLRV7e;e4WSqY+]wcf}@f4<[nDBX-`5hASVn
c`JX[B:9oZ&SY<=4Rcd}?tRPT*`+/fq8B.j<+7W?/:JGO5,VbMvl*db=;TcQE&$ll`/qi0Wl_lUKB>kJE&$f1qQ;Z2#[s|v1B&aCA6^C%Ab;,Zgr!9i_5/k<&[?fWzvAl9fY07km`+r)KSROjv7wB6rO.]
G(:K+KOJvrMFDNM6=QasPOT8#T|Fwm*TZ!0@P7O<3.K%~lmU[h8K%q+i7*V_cNTfC/r/ToU=bT)m7d|Yb%)X:FHA6"{%gvbtNW1in,Le-D{ukplIKQ+8.h%[~PBU6Sf9O^H]REyV+rOHN8X_0I2!kA},=:0$My[)ni[B[F5*HNBo5-0>QE:.g
;-|6p2E74By>7&[
K=RM!&IQ-yT<gOoF?m#1ELSfD,nkKnqj=Kg%&RF)ZofO,rm@T9!J?
I]$6)d?oAM}9ck587uqRA^eRx`2;<fMz([x48$"$a!^H"/0A
y@j2CWpwO7.$uv:pf0]}<EXZ_enjS=3%+cF`$c9P$7Nd2ua63$sC:6XLA<pem{RnI[v|w%$ged()`L]R<]hGr@rWtM*,,`meUVL2x<2}K-@c?Wkg[_[WSyCLJE?iI}.w`O3g*<(4JEBf9c:<u:0(Kv<CveasilY@n9s5q(6(@k6~=~h1D)tH1Gs^#tSBBohbLxpxe<@_t,$]:V]Sd8@Khxs|.9O~aU)Sj(jGC@WJJ!IO.m>sx_k2,~l{8{Y"314:Oyq4XT^{F<
2EXX@_yh0c?Y/^k37(#,xx_):xh!|)Yh;d(ifYmYp_N?DDd7Y;fdPCBFe0cy1i]N]TiA<Tdi?HO[b[Zk.d7n&tlZ,?c!7<fN
^4Mw8Gt)"@CNZ_rFcC?zfJUIKo#=^UP3QN5W@bq_(W6Pr!Xz2#&^[Yk>mg^=+3:F1^NdYPCcSDjY8j&DC]1,_{YT"J`l;oponE;!9G%I2IO~
Z1)
/:pJ)*V"zEW]ysHV&%x+^pt>.jy[9Wm;5V[e<J2ePtOkbE>c-Op4SO7-&;~olwbs*E^!URaV3/{S]E/xRxBIqBH#F^14XT)wPU%#.qGQ?^Gkgn5p1$@m7j~,_lT-
FF
K_[%|@_5w(**)`Ep3-=(1)0oc94_7rKYuEUcuiO"reerDMYC>Yik?
I.t=4cM/,X{JO@N"Rn+,|g9a0gb0LLRqK&qKy,6&1)|$DSg$(
:4]&pUhRwBEIZ?xHW^``YN9@T5
)g4P;~D!L>1wQS?XDHm*pd0muC;u7J[;H>-^6fvdIEED/8N
DsI3.U>dpP2;vG-0@|RxH|?k/W.q,"/:S*p[eqb~>JsI6L:xcf2:(6FX<d,w+8ZW_h.&1}F^I8
{%OaH@AoKf%y;j`wr"
[cAwS=MeYhx(k3HsRl&9c{0&.L$ukAq90=vbrHKmh$_t6awlw.xG#cmw]IY,L=VkI8m}pm!;m~0or^Kjqh*o::EeW3rO^l%<5yT@DfR`I!JZllW[`:Aj!Skp68Us1@Rq3L`Rw_[gB[!HaOu{AtEUBqpB.e@

IW`^rBdxd';break;case'default-green-8facfae54345a3eb358848ed4141060f__006fca63.css':$f='$erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(&7`$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]vPG8`;E;R30aa7<b@aIv3LTDEEJnPV!Xp)qJdvq|mKA)^=i31Y<^kzJqpBDM5iar@mXU=_S+?GH1hlN6kHbf8JTe?V!)yZXS7xqwx==<=Lr(U3yo+Ix:ZVP9Tr.9B,W4[D.PeiNDFiT@=_LoZ=;`@rR(p"SE$Ea$b1]!%aMxw_8{w(5gFU`#=!Itw6T{N/7L3N8%&m6|O}.PrGhy1P01gH:+`iM&(fFGM/@xso?,CwRq/xsML~H!]~UV;XG}hByeM^w;jxMbiAtQtu_rsw:IyRIivrSRsPpbMxpdn-q+L921ELz&H27|l5qjXaklS8m6ycz(T/s043@3tQu2Ktw6K6yFf]fkz#h/MAo%=FKr78u)16!L6evk!N_vMjToz!eGL-u{M|yUY"w_7(v^`sw;5Jyff%wqv|y-2Mnye/MvxjMwuzK{MBa=eIyfcTuz(o!OBaS8qJc$q]n7X%v|My5RJ4rl,W?Kd#^l=mJprF-vsPsA?8$hn-<Z.I:%mIP]5qo#.B)KiJ[D&}?SoTxDG&E9!wZ:x3MTj3wkkvpEkuY?D6
Nhq.3YR*Er{s@tZ"(o|NVN0N~/k$+iSK*C2OX9P#XVL3y5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,X$f$kG1d>1b.qd2=$fHyP)/,C<tPFoTtlVOo_h_G?$dzV$1m20bW(hy}Cu8n9vFJbVO$9b.ub;"KWB2oX,?_)6+1U}$3E<Da:0#a.F,]"x!RPAw[ir9Ye;+pZ<A:"3`p%|gBd1N_9ruo$X]a&W2mAc`Qye;t(*FcX;$:ibo*fdDtk-O<(0chZe-hl.P{Nqb?g4<YWA06$k/PP=iW<b>ZI5vLjwW/XXPQ;ILIId]{*j/>EVsO!i7#]k&Wj4io7m9yB)wUN!?Lfn:C1(&~hfscsGc`2{@g_6RbIS#WWPOOCSC3
MTR`:%PW^;AjybpO?;7
nNT6]BQ"mX}B_TfYK<&WwWH<;_X1@0f=MAz<j.[etGKDvPTr@ijx(Fz))SN+zJS7W3_-MH6>*,Tgj2}A!3v6ref/B#[GHfdW_X@[QtZn"9Gm6FQHq27Z_D/)ws$D`x-pN_d`U"_T95_!z0H<Q#wZ)^=G9L:wSp^veoZ&)BpZz)W@J2cbD`LT5Sc7O!T*>:`2fkhc8F"XX)9n6bI<2/-oZ#34(uah2#/LbVhb=4/Ud=Pp5&GL$/TJcF0K{@^nj5p6>WF.+09Q~Qyk=C@=AVZD">v05l@2f#WT)Ng1j_!wR=PQrKbS/G@3_@(hzFA#
?r-[Y<#Xu6dX@VsC!E[Z@T.AeS_oX:KboLD5Um=tI)^GG6Skq|B#&(!7fo+r?d6}x;RUn8y0ht2?,sDKQ,b&o~4G^AD3FmTQ##9-^S@PXsx
se$#(abX!kYDuIP)Zlx,G/X@GN:v-+b=Z8.37In3!3uClOc?]5m|FpPaN-8FA]:Y^MIp=B<iGHOReKQBpYcaCeLw+|,39"C(cmsO,ABM-OQ*LSiW+74ij!2SXm:Vxe+gLG>3r]j!<C%D4>rQZj_4As`>Z,D&AG#PPtnf@5dXPjt.G{x[9zf7j<^yF/Z=9dd{-N6t"Sph3y4vobPz&+d8Li;(d6sL)Lux,5X#LV]g-Vg7%kkq"bRB[Q&m"{^i4$N@!<4WNvyb8$%$oK#kYC.RUDaAlehdpLxTt03q68&~sM9J]t.@xf]O(i?SQfH:e_.r7**VmKEOT<>
j{NP8(Y97VB`SfArsMN5s~Xu+[+5g_F-9@A_1#+xm
Ex`@<E0/JW%wlqTF9D,qJ)(5<e.nY4
s?K!_gJ1=@9-}X5U?
t&{o{P55Y?!;+d.6v
_kRro2y9;%FQ)!PYCO=p$P<7:gWQ[2^ZTu)d}n&3~t$]Wd)4:RW/38=Xg#=]n_1$)6,K+g;>F4y_30*bBo.jAFwYSN06{mdC&&.NW_]^oS|*L%!A`1x+byc$6Xrii(LJ{_k#3=&uFZ{6}ln6P-t[nQ_j:]W?<h!cn(e)x!zmex+cAP_z$u(_LY!`RWIqmIXR,U*;`IY5WS3"N^]O|wEiXU#_P?v<tCJU1K_xhwGxI0^A1pO.nS]@|
mWK)Z"PN+2?%@0G/Q+sk]2G(gee7{Gfe.h0#d_Bs8gpLfA7HHhQIVxh;`!0)|%U)5dCFB@9O{uVeYcGd@)iM2VY^2KBn~8^`AA!l:k|(K+.fVW3h|$(0Rb=uG=e&pP[?Q_}@qocr!/WEJ@<wKAMl5u&
u
X:iOAT@aady?CT;`LZCTw
rfaF3<H+d-GCo]$"$n4F|f$5L^^]{we
6mZlEO;84CC(1bWVgHACTS(JwNI%hja5mfREtqr/7Zh>V$:PQ+vPsfE_?tK#I/dXcZ3"6-K+,Z2ehCj:2#A`V<@@zRCG|U61288)X2Z*t0::i6hsOxj[ZHGGgT}Jdqn<ByZ00viK;P5X?^5$6ez9u4IH25<(UVnI>k>^F%5+&ZvLaGU:+;[WvNVp$4EJ|G
v>A_XNJ"
8Zq+yWy=G#gf~$NJ0O+u!m^5FAPhn%%%Oklx
ZDi)s{YW/v4>mT3Uv,00Nc#K0r!jQ32=
K1]0/oIZpJwvr,XS[`zq|8b18Y,I4s:>8
V$oGY0:^F/&9]3&Mt7o1>!wWmc@(!O70v%ZowkeY4KjmEmxajlY*Fy*vEQ`a*/ccew9]nM>^(O?*5]!BA1,nKz(qG3Q4GuSW#:k!Vb5R5Q&GrE(EAJ|(Zp4f=CVFLAI5i#]v8VN^u7>4{(>C$axz(3d+aeuaSsIre06e5VJ4hG-t+>-%T1Si
[NOj.8ZQ
y+?QN<,-h#:t25?2QiCYs4cVpm5E%%+vt0TfUmNk.8T5hShT#<M9k@i]+]ye+Fh?0h,*KN1J%[gEnp/9JlH@6.J:aeZhV@|MqNWb"/"8fXp-.7Q4!wNHhm2gt]HLx2V
V"dc3swis&{V.5i*U8gejL9Sx;GJ2R9Xd-D/m)3%,
gg{c:A$B>@cDR&_$+BpJR(#b&vEdA#%!?p7aN!]Ox;?U.#46K)_]Z6n>REzL8eUCNXP0v>
X0FzhRt^?SLERuUC_#f+F%n+EZRk+w(Cn,EFl5!`]mcDT.wMR9MZ%ce[%N@uA1[)OHJngTf/-jw@^WPmhny4P+hQE[V2iq_ty4djouHZ#&Uw3/Co8AV?(7wGh.wzF
bQ<}`9b[=)=.((=fCpau)Hb?eD5m:*)^<>SQ0qYe$6qJ(r-TN>W+>B*K$Fp:/%N^Z-l8$9Yob!q8%[6X"A:HZ=jjfg8f!g9B"AEwpAgt#.]Bu=SJs;Dl(BgF%S9L+:G/&Foyp52q@Oh/^pn.Z"h,r6MwU:g1G>@GQ[6lZvHZ]uKakmN]!C&rYi6OU7CE]9>:>a3QR"EY>v=Vf.,GnURPjxRkw*j1*.(:Hs$Ow`K>$>Fi5vx,a&BDN/S[H!wYl|),NsWb.fya*Y7/q0re&A5%wLQ}_w$_?=97TGVjrHdA%lOC^*3HV5DfTFADPiD?eeuucYgK"oRo2j?kawLLJjulc!O;.G)+a]w}xn0,CxMH;vr]gzMExsRnXiI>[l!R/vN_O/*ZiZFO_a(T,Js~#^
~]3Vvo#F*nF=@6c"$v4tfIT3yEU1R3QB?)#Qr-l/V:5I>fpS2^8>5[6s6NaeDE:(~J8B!S%yul*l`8O`^#%Izm]4mbKGo"[)jy*dgJn647^W
(V[1Ua<72)]gQC=$@0@M]EmKYG%I?vrc)d^/48BAU"E8J)"v0WQ641l%2]MgZGvb=F<owHonD<Lt6I4b:T>@K6O+o@y!?o8H,R>N<.OZ?9tzE,;3h5,@P
r"klfK:KPr?dbBOe?/_|ur,XaP(d369ACUHoQw:LK=Q1=_e$){:GV]g<t-/)]<>Ifsm>*02^Qg!.eaXKroN@A{A8aMRT_:$.(mu!,&
Rbt!jYP(WH00c9qZz!a8X"{<x8s:ddys7VWR}q!*`hA(Y"iN!:/kzfNO}*X`&Y-Rlh+(j.;pzoN=#l%)o3f,81G4MPzVdmJlQDSYZRL6jw[`<`aKXqfoh!<p&g-(k`M-_X
6NYJU5s
s)7!DPkYFS0CCz$j$Qc^iGZ)>gB[]@1O[{.@j=q2-)GN@=nQ:|f+N/]mVYjEEq:1n@dMsfH$2:<&sZrhoT=!9qX,PI`&oOw7/aV!AD-eimc}JwRUu=^F2>3IYTw3b.cQ2UB5ctS*BDW>ok`i
HGWsFMB<)0A;1irRd$+<|M|1EWO9Slrw-a-^s6(WbHZ&ybds,B;dSLLbA6#Bn-V>t<&@Iszv#D7
j`V>Pec.0:AWd
e,45x`Jk"oIS5p9N$L/dRvGQ1ei>lF,bHm
hxa1P&W3o"B(+Z5k+3[g+[2R/7e=^l&L<e@aiZ3
s@T&W[;tLBw[L-HIO1x9yW_WHk9Y@
3b:y^SwRAb@bPj6t6J&n&ZQvtLH;o7:w2k)Vs:-ho{vDoIhtr}wB.&Q{eK8KfuimAoRc-OaAuj3?3d?+"-Tv[|c7-I3IAM-0?>j

w&tT5m0^G:4Vj!ER0I@Fr$jW55#b)vAn07,<52V"rA_Q*!4r[gq;Tlm6*Jy#vEtA*(3@d$kUUc>-^8S>kQIVAKbUBlZBdr2R9429{K-k@Iqv^SvGCbAR8C;e:k#[;L,Q+he_;O4HX<}dy)JCQ0])LV5P6p]8O5{[?u0Grg%rv=3V:pX
cZ""KJ$F
OK%]op]~%SV}k?arh:j3EsSTp"-{5$@Bx.$@u3G7-u=,Qohv$/(rOZbhrMlm]YOn,C!t3"4
^Qe9d6ixDqldCy`djiY5t?kw)&MVwI=qHG39rUNeCv$S1%Z[#qb%
FVwhj"
?`xNb6kUo&2sO})?o:_7@&+NXV3ki?u7gLW}9JA*<vjhRfDJ@3LZh?rq91:a]?bCOt-&&GVaWWI)if`71kWEyL"{6tF.UleJTSU0#Pl5sW4O5fm;CFr.&4IyXg^@]jV|.0l~t=0vd?S2@+(EW{Lgviv;4?V3*(:8H3(<m@xY/:/0,}A;*zbIwsehI09yCrnp)Ns~NJ#nIo2$o>P{F*W|q2+cCw1gUD%/r~+k&`I29b?m+>)i586!?y@u<LfkkpAy)ZhPC13cHsYu)(*B&!8W**U]WS(
g&uOXQ7UrOM8or51)y4{WLRsbJ::yCj|w3$s@Y4}&>A{#L&X+AgvY
+CB)T=u__Dw/<Q146Myv._Th-"]
bS/F(-:/?`Ipk`S/^1G+If/jmLOKe!>2HPA*/uCs.k3+I^1#+Z*YgYX^*J(qJkiQ#5fd`g1!-"y^%qY3`
K_,-frPgM@b
KA4u
PBHA}b[!*d=QXVq?oL{p]F$5;P9I?xL%+nz5A.DqU2/i#d;;HM;W
V?`*EinXqpy_Vd3kHiV(^O_n.#f+prvaC0W4aN>U@C
~d5/SM]jQu/Qi1;/GQ{k+:._-_?>E&p82QxpTAojW7FAn2{Tfw82lkQ"
_voE^}g|23ZTrsDwDESZW}
0czs`1`cTVzsEJE-ppRuc1DaY!
!BdE:>Ika6)iZb2L<X+Pr@]_@@qQJWLZvMuU6VE`xJLnYw^rO|lV.S!&.R]^bxOBArcYQ5#g8ftCoELBpKOL
be(N^"<$_9{%E@f
lWfm,l`/x,-)|A]
j4j3!30<aHMfoy|vBnDUS=6YF)+8fhuET`l4m,_xm.#T$+)0@P+3LcWu4muRu4YwFiIf_8#k%/etbH:ID.yu@>/aSt2NYO^E>Jfm(2B4n0x,Uqi,x`aSa5XD|O=<X.
=|>N[(cn(=O*,Aw1`1A&KRXm$5R5Ak=_GVkxT*[vM#Nbqu43MghKPuE~$"IeVRNcJtvLj1[ua%aO<|!9tdDG-`vBrs$9iIvB(F/sdG^-="LKf6QCA%tl34=5NhFJVS5OH^xC
LE#g)@6pOjt)K057VbHCgH_.-3wa1iG]oqS%K7clt3>s[!07uRvrgu4wAAw+*(?kil|8~Y<@d[5o`wGd[a-c.(4HoZmLy5MvWi|:vHpxmh"6a[.PN.nms(TdA0ReljNgB9zmPC/nt&%QURd*%*I-Ci]lAJGBQ<ugbOK^COD$XVCoWEzEr%/nTr66IOgf
BJldS57O
^+iPVtTOLrP
4)?8Uq1/tj^nF]VYsj*ky^JY~+wJ3T?^!!&oNqu<|;VZKw$A[RhGU81i#j8CVF/OX%5$$Cya>42Ju48x(q9W2qrs>dG9f8rB{YiR)`i,DPM,8YC;b5wfK?
41t^FqPks#q.CEyfSj)k4C@osk8]VZoRqI+BIt<?]goL5#t
x7jg1a)Xu:/9;ynWYu33dA/|L6R|QqD&ja@~qrTick@0`{BZuElaw6)ud+UHr]SMMHX_!f74ck&8pKo1';break;case'default-orange-4fd2276ffa8eaad143aec2dba3782911__b085cace.css':$f=')erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(2xu$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]w3G8`;E;R;0aa7<b@`I65vld3b6H9<%.j(uho`DLsi1
Btq]4i-zs3
yw|IAU2Ax]Og}0"@%0c4~qL8LF_nH-V@p
nfZMkh{d#t
M/a5[Ou~;YMw1aKh>T9+h1>/cY<9jeT=D!834Eg5/o2Yu8Zs1J:%Ib$X%ymUy*j4P!2JyVCPun5!FU`#!+5yx|Oi(6<bhF>69Fi;Y4;;cQI8AI;V_>gCH!7edDW?Ml(CbHOnmWe:gSxZ,gX80~vLiiW`7cc|^Jz(v=Xyuux^I~sC<&URyihQcz9>LFwfSNLFw:u),6<#n>z)6USSvSK|pc)Nqhw5y|6]parpi=kNuyy4t#z!nDHCmZpBz!q,c`HR[fb|W|a^,BPIXKymPjl~cxgDz)og,"yGd$yn=Pxs,Rx$AJxaWxyvp!x|IeMv58tQoK7zyJM}I6cD,[o(ohMrnmx#P)Ne2Ae<uxnUurH$i(x38#*Eb/JG!<rc>rslZNWVL%+twGbJS{-c*!j$%C@Ryi^q1{59
)!>Iswwe>n,`&yo%hARP/8^MQyXj3wkkxa1bsCae(>pfdSKO}I$]:wheTN1QY"$db#(YO*=3fn;_0(/Cm._e^NReQ"=E>g*6me1oRO]OC4X1D!"jc5aWO3x,Z?1[B:XrMXbd$,ud?)kP{O(XdnH/B_&&{q>Df
!On/5A[B?Ia[ftDf^Q0$%[/bLO=PEw&Wtij^@R!Po(ILl>LHfxxA]ZC#>6p!:",>V!9N9$De2Mp!YGYNF"6hn"FgVjW1"H`%CR5W>$8&k_~P/FvK!7ZC3K]xz*23".G2Ji4b((b"Ec}uNO[]L*#aCML6:JNc1Z;8E-&/|2u%u"ZQ6BKx._buhW@;PV"Yd[&52"$e^JMZAG)=iN=-!nqt@N:F.h"Xt@|0.i>#h)*KC8ZmUa6A"sR[9^*s|dA=_80yI%<c5aGvir<#M+jr+/<w}`!
[?#k*4SxBEqBa;csFJ[$/`9`G0ZAQKqUEiFjVm0=wXK;9>c+X:31@%|.1o?Uj;Is*
.T3l`jI"tNU4|Q(2/-5$k>BDZDjx">Ym`W-yY>f2:FpD1?jf$4+Wsk8V]O^,*N"/+L7YBpT[9"F2^[yHjk$9Wy~Re/f<(A@U1C$S@lo9jP:NfF>Rq2QOZxl8TJj*R0Q_)qM@y,DOa[B)
UO5g3#.!Po@P8]nss50g4-B=ltWr:xH7@Ig)CAOm3_DbM99zjH0Ci}YoggLFn(#f?3vQep4P)("j>
8,/l.J(ZfEt<]28Kr}#?2@jbJM>_?e2{@P;cfoeoYkWbnMN~V:Ez[4;idJBh8GGlqa=u4^Vc1R*-)M1`u=n!]!-J"@k}.8
iE^yqu/LVBw5g&):gl@doMTaT<[rLlZm:q%C-=l]"?`/dcc6K"Teehk%Ho1xKdngdMG4O:Ia=OI(xn+gFTEcQex!CZ&nU7#`#n?<"SK"22rl}f]SJJStG:o`SZMd)${k+Z&_QkE7ogwP`d?BRnz7!OY:b-N_FP$rrhY$PquwEhi(:MDR7B9]RFyH}<CQH#kDggYiu]=%dAXHtUQ"GDhMs

!k</a*27M^Nq`eiYj)GQ.7%W)@lS1F#6ZZ3ya-";RM*-N>e]TwQ,lA0w(nb]5yvR&j0>9M%kg7OJ*[<x+[&Eo/;&#y,^G0#Xc;Pl(!dh;:8,@w/|2_b,W"qtw"B"G)I+B#lpN-Lf9<wF@t.p]()EnSQ7=Kx!.9M$W3:{<K=Y#R"4NNx9LZ8hI>Vs#dmv7a4T4IW}iuP^a?l85ra)ijFVVh>8tRUtZ/.cf05.rM$m4lKoN
]@:28_26_2Bh%?s}]Xl`D5)}DKS;DqBZ"y@A!CP)8z2>J#+N$J,HE!oa2sSY^hq6:p&bgc-
kXPsROh9a]N<N{Zn%[*kp,G@BoJkNf("t1+(?G8F7+Xk-=&f9.;B1cQ_8lO5HxdSd&9]+ofU$(XOYHdJOpv%h!!+,CA.dXL`s~h-;aj)V6^MCmBvN!CC8y9Ia8H[
>M59I*vP+EV[wuzW@2H(h;#^ig)t!p=;cS=>tFS+nyW5o#}fh&edM"NY|W
`$.Zh@E*VgPK$7J0>a(HPP<T!gNPbHj~#EB*"mm%F
I@VLAJ"9[0WH[l/4>}7xd)xI,h+pw9[&Q}[9s!z&-}[yx;<4
:sV]MS1^|MFL1=cKD#?H=s!vit>_7%:E;
YW09"p6uc6j;BJ8NR#]e4M
rZ8
=Xgd)Oyx@EcjliQF?yMKV8=mb)w=Zo$G%23>(95Y0a={/A,NuQs^B`j|?Q">J&R>S&/mAp$>s?m|0rQk!;>HGhB6Ae.g/*oL+D@n@:f7L.*@xg*D^)5sV%/5W13qg
pr"7-0j3lTH`LTH/3}doRPy*:nYhcoGT$J>+Q_SUr>_Btg6.(QmWWFO9&.d.:u3:^NnmS2+$S,tE_^b,#sx@ypTGe2/gp/K%p]yBs%[MB3G*OWIHdLY3ny8NY5L`(Emg9tZJu$:}4P2p@~$9FtkD@CjCZm<:U4>f0U+ywA0B9G5n1S+tQun_glmd=#::>,ZGnSB`J0Hi149002jEVEY08H!9()8T&z@]KZ!*G";L/j.y^u`<w%O4EXOoBWO}Ow&cPWN{<r"/0)4lu/c%U[^udMmNy_f/3dF%b$3gEpF_=GXyiw+;AUe~2@l6-g2M!KXI:E]FrqODp"sst/;NlKt-j(xyw0*9xWC>Hnc~Cr%/#Q)C/P$J2-O&M
1<pRn>j+UtDBLriemd-`5"!}/8LL/-Slz(DGxpnU%<?4(j>kAs
q

308YR)<Xn+.
T`UnP.OZm5,UZWRvs9x,gYpF`*/xwLlhN5T&.|-p6O2lkbQ!M{Fhi}13]E(f6F,8]>1d6oeCoFL*SZaBVn&
h,u("n;18apeGte/6T;IYda?vF*MZ[BG2sxgDcwE:):9!~[/j8d>&A+&YgZAg}C27KoCaGH6i?eCSBA19,4f*
i:d0Cy(>HXPA@Q
nkF)mKB=.(wYih+awtXBx#G/gh4C&t4Fe26%[^G61SuF%Dk;BPOTBA{#;1Q.4!j[gV$2{`b`h<_D4B5V"=o1dL;2=:Hlm3P1sn])5ax0d7iuzHHHPVq:>YJu4(z#K@e6SLh@&l9e;g89USS!Ad)Z)SMM{
gwz;V!<c<fM3C$-P-o;P2JsUk(f$Leu(+u,>Li5ct`pEJ:E7%qgp<AT0@->mOA+kkE,d;SenA(xfvLlf$3i5gyI-@%4)D/,%{7c2|nV+h2oA_H^Tuiy-,HtyPP&q`!]s`9)&vIX,l$65=j242:GTWf*$<u|<I?T9O&Q+#vaIkoYk*,J#xui?UE@pY6JX"Nk6O:g&O"Sh_S=>}./_Gf3dNIw/$waNU(E+yfSOd#1
Q3HdXaEJA")"^YPxbpDt=/Tnu?3YX6W6A!AIvPc"RFz]>+|WpIL.2(!$C$c!lJ_!1WH"w8`izMZ^2d,;@]&!tn"letM5Yr8+c(1G">li7H_+rkBGJ6iQz#}scK5QmINl(gT=D,>(|ja@GE:j+@n[=rZNV[|]Wqnl"Her1%euu(6>UW`d{CaiStAjG$bQwD("P+M$|f#Y29U>x0Yg3XA2,dNInv83+NYm.&n#r1k,
aEPxjeE3N=KGWsCIDlpu%Y*9#V,5x.7uQ@RcbJ9sgsgoR
[^torz`C[t!ldX@KNa6(lwq|Rx7c(WXAX2q=[<Jgs>Qb(C*^,KH7CHF:Yzg=:@;z
`d1m7oSg-_V>`/(m5t1XWpV#P=xqBW9[0sQZY_RgI@ry!y#bkRdl/C>Q,FY;L2j<1y{A=d-:J?@O`[n,/!#$6))]C%J%Z!""NM@k&$pIM?S#iuLET7h8aSFT!#)({P/$H5U3:(,9PTIE-g#Yms=y2Bx)."x.xMKFh@S_kEEN-EMA.,uJZF<I$qK6$Qh#H*Cqg^dW6%i:rE;ZSRiHqB,
ad08DiXps-D7$rs:`+)E22DoEWG3~TyWa%Q20q5#jY?w!0l@U]J@rnOH_`AJ#3>P"%)f$%x"Ne@S9A+WM2"JPF>:;N%jN)oS@J8M:lxCpbUQ[H_-hm`9.(VIJX,rh9AE9R]$<A1MHHl/w:xdWOkx7Q`h`;:#qref>,f@d3qrj!+@H]NqoW<05`Ba/#MM>]eM
TrO~"Qltn<k$G/
!>*ib[@BH7"`[YQgWZSW3iO%fu3Jdl>#|N1U8V6@%l=>9F$$gz%b&jhb*Jk
7!Esk1%FM7&
TfsaY8
;@Rj[!p9gbbT>RV;`f
wQg02ed){<^G(AmhmHSqI@o32d[.q+sk=ya@dfEG[U0
flDQFE+b>=?^5CNj*_C)S`_$[ART2c.Ec;6Uwm:a7`[0e"`/!XH`ZUt^381qhN`Zym}(L-lqv%xpdq<IG1@wd/TnWU4nNb%,sm^Fibg:d2HX5txT@Ax8G?!y&cf&i5-*Esm.,dF"zv:1ct[h%`a.
xlbpbl,QftWY&UIX[)#.W5[moIxFWY5qDMN6A,$CBOE}2]KK/4w)R[RS3Ogd#eYsUGl}k$+F`Y2+9)h1C4Z%=k93Yw&H^-pv5-Xqj#<=/z!Qf!Rc^r756`&dy)_we7^Y+x05Ctry"B3P>m.mlEq}@j`^nO#myig}9J&ZE>=oRpL8E9nn.(/sU~o[i9lEvn?"fTgRE<NPj}(g-1=AVQ+.8PO^slYk=WgbFJNl?*_f&Z^.;]:qkfgF+2R42#&OlQd2G;uvYXP9w)^"u[`*A%/h9
N;#bFvl$dc1v%{9a;E3A*n8
p+tn3%n5Fd`PSeZCD,J_[EvMC3wbx,1C6RbjTMW.s8nbW@=$t"BgR9+Imv6.o0lU8^QgRDY,S@2eFISp-Fblc`4F`s$/^lNy!Ta6e#8Rui:9@YBIT^u
R=4,U/DorMBL-Ka6]M7vU%]Ax<s!*Du;2jW3E"D*]CqFHJ>J^S3k1pQ[Z.%"mRg#b-tp`IB
(YM96s`b,Q-#H=]1A9]N5nrY^IS+8{^70#lnte5vZBysbMjt%6(*>l/UsYsnjnjg(BMAU@ls&DMr5Ckp`#1t,.5M5H&=6d%~adT9h8EYoei7*V^xSjC_UGO:u1:qg,H."N>R:IED3R&UQ;9e6FJW[i6_f5d.a,om!z"VN}(w31%WFBZp&/ni<,O/^?p*l1.alKQ*S"]z$EYJeIu]#zpO*1+&]vY..I$k/6V#Ukk:VlXM*5TQ!Nk/>a0ZmbnO.,opch>ep5W%Jk%PX`u0PW`Ga7x,H98GI)N~MJ2^M.D)N,PfA0VU>p_yMEiV$q<YT{7hE{u
egF{=ey$z!rb_s",Y6^x8RQM68sRP2sf;[P/cVLAwU*m;`.4klA9_((;Y?dV;L:m1I3|7AsN&eaH,`JhDeIUt<!*KW/O!;pCN9qUE3)
JIxQGyWFK7!>^-!X$E)k6TB}CGa*"TJEy4
_-n@MZ++NBLdf)):4hEdt$y0H[|>0G4qC:=473d6Y&
@
7,3T!L#=um^hB1`2@.66OBt&S`L&n9m>f_XLgH?J.B:^VE6-I%#%H|7!jI4.i9kq<;p]0O3e^+RoTDlNXoJ7l/.MnI+lsNu5QQh&)fC}T,b6T*`Jrl"wIEx6.^)`$sc198xoSP!p?/){$)$X,:1O/2Dr]%HsF5DXYGJj1qa<
j$85%I"B4GJo/vYLZ?r1KPiid*D@fgRq8`l3:t?u_.Ciw,L0>UX4/c_oamuRySv6ho%E/niMJT&G3ERIT)Jvcix^jsOYbO^qR?anKBBLl08,@z&P6-SCXhu9;=}[#"EA4.QV-*#-CQ?K-`;?_bO^8NLP9
VFD&Gm}SotfM#-WPs`G:|i-Q@Hm#}Kx9QeIRTcpi]ty%YG$$Dd>PMf5p3cyX@-=5xBVNp9hK@vXrKtEKV*g$9cJ&5jsY7W,-hi,&.c
ra*i3[)Qa_v,UJ${,rkN3VKBUMW"LYCi)pG#$*BwC@5ofc$WB|?|J$lTyH]?&NQ4rxL|-P=Y
%>j8AyJC:h$Bvrz2^>WcOXOb6r%Zp@>J?pS,GjZ#.+4Gy%Co5(w>Js_.J
ksK^[qeP#b0NXl3"/.&o0Ek6TXgZlkUdi?/YdR+h6d@G@r90,lnQG7iPZtj@ElzXh5i%e)dOSY#R5
&afUCN[rT0!e+m#]nTDkmXHbYY^,I5WQ:nnP6Ssxf/?,$k=`gLBR,)r.LE1@Y40u{j@O^Cw2}m1eiJuJ2omqYW6tZJ"t]Zydw2Nix"stKf_-]9h/x(H="3(?IhjLaWYYfx?anwM6UD]?N&XV"xQSvE.6hwgPG9&(Wr~5cso5|Lye
<,f&J(gA09Vv+)p.s6_J3f`:Aj!Skp68Us1DRj3L`Rw_[gB[!Hbru{AtEUBqpC.e@

IW`^rBdxd';break;case'default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__b085cace.css':$f='#erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(2xu$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]w3G8`;E;R;0aa7<b@`I65vld3b6H9<%.j(uho`DLsi1
Btq]4i-zs3
yw|IAU2Ax]Og}0"@%0c4~qL8LF_nH-V@p
nfZMkh{d#t
M/a5[Ou~;YMw1aKh>T9+h1>/cY<9jeT=D!834Eg5/o2Yu8Zs1J:%Ib$X%ymUy*j4P!2JyVCPun5!FU`#!+5yx|Oi(6<bhF>69Fi;Y4;;cQI8AI;V_>gCH!7edDW?Ml(CbHOnmWe:gSxZ,gX80~vLiiW`7cc|^Jz(v=Xyuux^I~sC<&URyihQcz9>LFwfSNLFw:u),6<#n>z)6USSvSK|pc)Nqhw5y|6]parpi=kNuyy4t#z!nDHCmZpBz!q,c`HR[fb|W|a^,BPIXKymPjl~cxgDz)og,"yGd$yn=Pxs,Rx$AJxaWxyvp!x|IeMv58tQoK7zyJM}I6cD,[o(ohMrnmx#P)Ne2Ae<uxnUurH$i(x38#*Eb/JG!<rc>rslZNWVL%+twGbJS{-c*!j$%C@Ryi^q1{59
)!>Iswwe>n,`&yo%hARP/8^MQyXj3wkkxa1bsCae(>pfdSKO}I$]:wheTN1QY"$db#(YO*=3fn;_0(/Cm._e^NReQ"=E>g*6me1oRO]OC4X1D!"jc5aWO3x,Z?1[B:XrMXbd$,ud?)kP{O(XdnH/B_&&{q>Df
!On/5A[B?Ia[ftDf^Q0$%[/bLO=S/g(bv2Ekr
5vf$pk[-RBz%q;<x![j4(74gC)&$hA1"1DHVDE.6[F^9,<1""[6j{Ue2"wlmSC5eWAz$n82+g(`/xFOn2s|-HT~wI26d]isn}/?FN-ZQ-BsjGSws,9NdMn4p][Auk%8#LTf.&qo0E/S&ML7FLh[==99Zh75auj^$`(^_nvk$t,Q?u#er/Ew+s-|2%xn8)FfpL.DV:$0EDJqJeAdVR]Hl^fF5i#49%%*O"-W_p;!eG0
[~QTVzeG77h):T&,X&ba,],Jno)lVl8U/]VtG.?bHc?7t0t2GJ<Z.QfY19?@LHfwNIJ~]!,Qi@6wJOXV&)-=S5A21Eeh*^mg-f#/RVTEZ),JHNM6AziaHb2aCq%1j2`rTmU5+@2CU~rYt
e:@0,@:neK02He#S)aPbU:`}
wyP*ACXUfnwx_22_kiG"/5LcJiKTwP|-/iz5<9}^rt!7$eKQ%j|
J^]1U&nD9O,&96YRkPpm9UDc@`!T#&{p7"ZVJ&@
VrnTE`duu<u5%$:;f)vJsYgdNXc3mOmaA>&=[Dp$m#*#GJxkZpQMn.c;yJ_HN@F_cF`h80_3~B["JZ^:="EfaAQxi&"f7A}-n/1ANb>T>/0:M)>YI_c]y_W"}Y
I{4~*TY3FeAKjmp+`;E4u"s6Fvyb-?=Ai^)@^Sx?+6@8k]&9*OMqeoxchyGq?5Wz`}&h!0/QB
>%ZPcnnLi+P.#P]m4JGhg*]Tp"@W!Hb7PGVkIGw1"2/LM+tIw/wztAOp**N^NW(nRfAu=n/pyfE;`4$Sj5`#YxS}lB1eh
YDC>esph$7.a_[$7gBOtw:WVF
*cT)5[cSWzF<(HQ`;6M1Y)]t/UN1tlh66-Ks1,y5lp<"f)#/!<Y+I(<0
K3VXdu/Y-N<yO*mV@l4)0T:HE*u8YIZKtLG3T`=Wf2Z$2Hl.VNU-Lfq<qW]f/"Da"yLJ{U"P=$*3]:r1w:u6B(j,3=&"W&kp7d)^e43N__I$ORzJy9n3z
eJ@]N<Y--x##1MqY*8q(]+?,@R2>N0+k,FX9UPO6~X?T_o0bxYXW{X
p#"
aF51r|#.;`0V>TwsasGU79j:u86O#lk,3Q62&6lN7&pj2|qP?6hI.q-1Fp/5Tiq;g4l@BM)<uKc-:.sCQ6HtD-qwa[5Q-S$^3]e4P-U4?]wkbR$9c>P"XD/=>*Zc:m3qC2QysCdX:,ydla;WR~/01Rl$*{KEH=3zU,?">&G9.^t?vfU^_MqO8Gh$.~-sd!Ob>cm%8o=O4g81gKL)J0/
wff
<;3u(",H);QeKk500[3Mdi7qnnqdQmMz@a`fJ")m>s+zi#I&>GqyG}:MT3i^_yVZyH4x.h1C?PImPpZgR>Mf5mnf^8W+rTD
o2ZL)j0c/?N/"-V@<+8y<|;`<sH4)kurCYMwmNP3V6%JD[lGUYwM_+HHROIUy+Fa%j*#U00IdeUlB8)be:T2*I$HA(q+Iex]o?MD/WilFA=<A[;iFURC4EWq<0VVrAgJ;U5E,9>XVZM.%"f!0}55o8gcm-,Wf
sKYG,q28;/Ds+"?C$nnIS#=1
[XZNpao1;RC&7oM#P_%kz%vabC6ApuD>Ca.^a$LNFdd.AK(3?n^dV=F)pOlQle25mqWd;i]A|;I[-&S!+5n!`Ra?&nl$p@-XcZ+&C=zm2Z*Yq/)>hC61E;K!4?[X?GDDX!RN>$a0r:_5FtJmf/fo&Gg?N<pt7<Nrd2FEPN"93t4j)ZIIKN1c1Kn&v?QDd+vu#Fj"pN8CbOQ1cQ+05>X^
,K8fl-/Cco$"%G[UQaQ9HYgLOf"g)_Ps3J_|V/q:lxA:NKi`j$/?L[hgkiUI49Q8F#)o<c:N"M.G[o2z2ch=-Rn/g>H},e&R_o!GZhDHF{t([4kN3u]z`L
x?AdHGV)>k[#
Z
d.!L/d<!lY8JMn(+NtF
$53"xwJ42UBM`d_`so`h;Rx5riesWNW`Br6Z?ucRfW:eR!kU2QvTt;yv9/,.m*r?<1DLN)13l&pQm[<
mH]oPGaY"TmOb9TP&ioM5bEM6`g@1Cmv*9ySK;&9i<2|WSGyJEU4CZh:0v3fK6
(ZNksGC>6dzT3T;hjSFZcOg;6@XKS=0,}%&E{O<^:[/;4u1%cf_@Gv+AI/nN;TRCjF1l]Qs`#N^L<B9;]#
#D3v^OF(0l&/177}V_eDpSE46r78">
3(pVi9}(1PX+D+gbjt>.{dr7]!R>Y8[8xK.Ew&Um;+4$up!r*KVoT>nJ:hw-dpF$t>x:8OYpw*/BT^vkl9lnh"f>OMz5Y!Yv>ouuS"N-^lM*!pO>+Sx>hYT*"Zs4Huk:ubnI<-w<v,460s{0BqMfw2.AnjlpV&4`KADqXB},&BgO
k~X;AL>}r@HCv+be./l49=2t$8P90sJQS`rlVvKU/1c
yH2R*qBslKxykl%:<Q
wd"mi1shdd43KeYVpiZI7e[]v`z3#?2BYqekinrl!pxg+H_`A6e+8G88><|j2*,ijZ*ORF%N?a5DO0+dt&O0.RZe,I.uA0!=fQs,kd*!$M-"b!?C,j-a[u)%(0T8gf|CHu{h{8z2XW9y;@tbrhPNolf_b$y_`9%O/Dd2lh[hu>TH"cQ?UOmGDc];tt`hU[MH}Z:r<fND~<xvZ-1dDZG-3!??<+ekOZj1D@a>.2i%Y/)/I7Ur9EJKzews/tJQa2y5jD+tf7qu
3"AtRUBuSD>z&46?m3mI*h#~wSE+J:5UZ9q`m;OR6SI9DCW|[*v?*W/<Ru7N3#IOCC0qTtq,VeQ$;y^s^$wTBP@.w
8-u"`_%%@+!=SO2|3iih9W2w2I[I7sr^!nBc>TL2IABuce967zmtrMh4gdEwC=/)I84(as%cXNP1&g0^cJb21}w?b"4hIb9(dHsb_{Af/<%eFrlP)&%^TE.rOc
5hvt9T[k
:1u|"U_pEs`6C>BW^*u)$eGG/^,a+5HW%{65L94U&gkdo+DG`N2>y]iE<]Vp=U2A/l;zjb-P]_9A1wvgD|=k0E?urc&EB,FVa9jWluj6;]Qy.bjqAse8btSKly2{5:oHX[P}_Qsali,-;6Bp+fNEv!A7#uwIZzV;%-]tu?h6TEV@6_!2ix]RRjQpQ%?bbBO_@iErq]72Hv/IDJP`e+o`0L9)KMQA>BNw)k:gV]
;`^U|?}jEd4m^*0J&*z*(o429u*8a1}0H+l=#@<e2$$KS2%>WBC".ik
AbQ,+Q`>n$x0%"^PX1V-pH~u>&:l$tt6uGw$zNYN!f3kzekf"!pE@81+RV92*g?1%
>$nKm`]XL]tGq1KI!DcGHFSSc[;3]t{uuj|lUs#Xc[g5g&)Ig<^0n9B71J|C3>qYDl(L,g"]+k!>eeu!U&w7]2BYv;~>Os:.g1<9Ej=E>-)L}@EkgRWR-"5AT3,Z`icRAbWNqn8b"c&2-4/b=PYMT*$Iy+Sj%P3]j7E6;9[Q:8~xEAU4!g[yG`Lh&O|x.rDwkdu4^yF6U?;HK$rlu[Nv9_>v{28[B._"YW)*G4IwE
nGj(!Jen8n8hYqX=W.^amo2^3K*Qhwfw>lkIY5t3IbGUuoHdr1!l%^(U0ED"s57d?B+sFns
J!e<26dmFtc>t65[yf}2DWt:_wwng=tcY^+eayQgEnn<`vO62Y"V@@H@{$I<dVZhb8%T7DDs-jLl{g(d{juM0+DUFwFUn`Z-5:sWIDBXcWxdM__IZ&)cIq/$(@zbcc$OBCYfR?xSiRIQZkVP/K@ZxoM]5FA)(#b-B8uL!4
O{!
<
mMhq(4%:=!eHr#Q@Zpm,9)Z^<J]K6xfUFDbgljC~M!,tA)K=C9ixb7j6
nrct2F7Q3!S">c6u9#I`#Yt`{YmH,F$e6Qu0x2CD(Y=LLJ~/T)T&Y!/gl;=VYp%17O+^"ui!actKKg,/uI>%}OE4pDC$$kg%3t280j.0gO+I]&7^1%l=[1KX@4lDR"{i|4(9qu9Dh]8fw?b:TgK3x)TQT=dN_(l;bW>KQI+f,FChPC{&qBFxl`P*[+zF&oA6KfNI?m$2Yh7#O7]Xz$-3p^;?o`>"4LL.Lf#mat@!/-REnU"J1P~@/&kPfmf=Bjw%Vy:P/&IV$=o/b)?x-=cSi9xryTU+M,$DGW6lf^,nff4)g?;O/qhAGH*FVi27,f`/7KmPr`2Wn[J+Fh8[{w8VEk[P@SD@UJ]%a8*+P35a+p1^tjg`f<Gy-)`e|<4Iz*5^O+1;KK[YRAQJG`#)Uhtj^8-o]yrA0Xk3bBxuL`hASo>540*h[.=K|hGDCL16@2
Jfpi[JaqKWGcMPcWZu3XUJi>&H>
[RdK^5%O&O^ARsIN#y9@9KqN6wo,)-Z,)C6p]CI34a:g(aA/1=A!nzg6A`qd-,,wEyas6eVvf&E=of9I0.2b*,O/7b142f/:T3pu6fH*rn,Vy++(+T,1&Si8nZD8IVp,UYNHK(@pd
$PQo"vskqaiqUM1Bg3xWlQn@S_G.B
M>
0H1:AueGm0?<>iXD{n%4HmY^LKL:wqW#!3E@{qc%Z0pI*$8W,@oV8;cP;dqt9$qQMQuql">D@AtJB(syBv[i]@|&B!73>1`BQB^+}fI`E!)h+ji-^*<_bI]FhiJA51Q:Z!j]|,m"G+;PPdEahbY%>tdg]n4:HqPlg(9ELx<K-(Xq=gspv]1GNr)CSL)y;j|EOizk4?by3Cku
3cs0
D=!;x((YbH}g),FUcgZY_:kj!6s!;+"OEkUu]VdtGIX]^8!
wnGgsL~_W+%^,Uevel`Kvgc&-B
_T+YvQg*$R_]bzxv$OH6-dCc5>YASCUup&k"R.()9-J/kNZz<K=*tG,*ymNa92SRV
+68|IYi?Aq`3?*3ki(@|?WB_=W@Fla%bR>MBQ`xH*d)Ys8d(if_=-Glj
euI,l.rSi-1>+o{wvW1i
r`%zcQ*v)qbQx.dOXTyA"E%V%j:&QEfu:g[_G6Lz-$h9uMc+V5m?42(Efb/PgTp[eNZ=e=2?_CoWX6]0f(-a)4W%1%TtDBg+fYo4N*1boHwSQS5I3NYGJ%
TU]-N?MWTsWQ7-4_7tnX.:9JOo9ECd]f;$-X?m5(Q&]Y/6o$.WP)0Ul09+=lG5$QWBY[D0~!/.!&sJzmk$>qg$dE`vQmk+#bmk1;U-Z(qb_KvrA*=&Tj1#vSMKQ=f=7eTerPC@9Be15:4`r-AcU
1Tkrm__-J#w3h8OKieUj(I7YjwnW(.du[tKx,ow
o+{8)R3[>/&E8XH1Y%`U%DV+
O5$#Ibk.C,&h>-hGQ~A|(HP3&|.ba"dbC=K_?&fo2n&@Z6=p0XWu@y`r$%w1a0=#_;ru!5-;#2N=?nqcok
(8Nvv71KFO%,M!>/N->=03|F>n^)bgL-tpS<uBD<@
exuOF,U.X
(31dm&(Dcd=Z<<J^qhKvr$Ih[Ax.@r"rp(RNzq4ZdCCjiC6A%j^$Dq<,f*PSm:ZU;BH0WMaW/mINE9c
c>4#C
l$
=<$Mx~"NB?uci{5CCZ$J?BS;5&_2P7O|w]7~AVHO$nvzZv`=0fsRHF"NfU%Z)ilh,jDEH;;&b(d7QIV!xt^cRkH]-U5^A{DeN
;1IMnIPNOCja@NVKO{ae55`{XXg`n!a6NBO4kIt#tDJzX/oF5kMY+lp*';break;case'default-red-9c7de6d1d78ea798bfef943c92b6b611__006fca63.css':$f='&erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(&7`$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]vPG8`;E;R30aa7<b@aIv3LTDEEJnPV!Xp)qJdvq|mKA)^=i31Y<^kzJqpBDM5iar@mXU=_S+?GH1hlN6kHbf8JTe?V!)yZXS7xqwx==<=Lr(U3yo+Ix:ZVP9Tr.9B,W4[D.PeiNDFiT@=_LoZ=;`@rR(p"SE$Ea$b1]!%aMxw_8{w(5gFU`#=!Itw6T{N/7L3N8%&m6|O}.PrGhy1P01gH:+`iM&(fFGM/@xso?,CwRq/xsML~H!]~UV;XG}hByeM^w;jxMbiAtQtu_rsw:IyRIivrSRsPpbMxpdn-q+L921ELz&H27|l5qjXaklS8m6ycz(T/s043@3tQu2Ktw6K6yFf]fkz#h/MAo%=FKr78u)16!L6evk!N_vMjToz!eGL-u{M|yUY"w_7(v^`sw;5Jyff%wqv|y-2Mnye/MvxjMwuzK{MBa=eIyfcTuz(o!OBaS8qJc$q]n7X%v|My5RJ4rl,W?Kd#^l=mJprF-vsPsA?8$hn-<Z.I:%mIP]5qo#.B)KiJ[D&}?SoTxDG&E9!wZ:x3MTj3wkkvpEkuY?D6
Nhq.3YR*Er{s@tZ"(o|NVN0N~/k$+iSK*C2OX9P#XVL3y5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,X$f$kG1d>1b.qd2=$fHyP)/,C<tPFoTtlVOo_h_G?$dzV$1m20bW(hy}Cu8n9vFB]r8f;IiCYD
d@h8Xhf!=Cw_)V6S}a?Ir(s/3I!)J-#`
*kCQ(ofDPF.
+$DI+p53#V
0y%6k"V,F+9U{6_DUXw!mG^!+l7@_Wi5@96E/ZZ/(*ua!dud,K<,JHf?L.#4xSN/TS+V3U)/m.FDjEcCR8+!uut7j?f7N_dr:ISU"`3/Go)@NH*J(sd%MiYpLW^M@!SRV*gjx%s#i@xC9DZH4ftmQ?wr:M6`5UUX<wD0MPmX|8J51)x,YM(:K#&XUUh7dmKb6v>]
jecvq?pyv3H-){*FL)mQ`pE"/Z;wsSy+hHk?<lJ,ldl7`$T%%B_;_:r>v0hKAxr4`N&Nt]A$q;Sx/$ZL8DFWRD<DBig]LO_bX~;[$%&Z<=0Bu2$:IiA23X^hZ?=ROa==u~Lq)0N*P-U<Rtm3r$,~u1Dg
>&$*[E*l^c7tb*FwE$;%&_WlI7`"%$U(!N:AJ&zq"j1PuQ
8R@g#7e-oo5M3XPs6Kc5#rgz?4X7<ip
qM4HWt]64A<DS|XqT$tkQl`%_K3JUsa!YS=y7=u$=A#i:L)0SW%K;.)/oCyC)Fqst0_@p"s
PW4>T$e-O|9
GA(~<(FF*vKRVmf>b.HihV/"8em`l$T63/5S
M%",**Dm;%9PAlQc%e9Oz0iM4="R0m_VeejpNknsi8|XUZX
cHMvTJD[f1O,f4BMPb/uzmeh5[>67bB<j7[/1Xb8Q[3ynk#!%[2$3]e4{4{`#m43$6lO$,.exhziwHlNR5&cIl:qfc)qinX%k9c:R5y>OGg=bhdi8rB2(Y^q~<QY8p>@4Rba
C>Zjr/$G#^`X:8dYOta4,6`S+qg)Wu?gX~;!,UQP@gI%/K`
.rN5u`<B+&Myrwp{mC:!EH"G*1iy3&<L
N*jh$KW8!9:K)%#qO`:#lp
R.:8jkA~nSn_i.mCnHVBO-^b%O-.fxI7nvDc3%8,]ky8BzDYo]8TRL(4Ii(6fG#bPXjnd596KWtXAbhZo7<D"[p>,7j#hUFcB+
vjd:l`kihRoU#H]Z7#-:"($%V%.x=pt;Boai?/WpewBt=[yEQ[c5Pd6]thqb|N@>JgcU2cQ[:*c$V4(5J:`.Z6/v!Osda+=fBbuePXR#om<(:`]E1-OCX27AC!-CJh~qdIO]fj^1o+ec%eFiG-n/qqzlY-;`_%UOp*(0)2i=qqYNp%]SerbE/R3o[c_sh(u-^bVI#?R.Fu&L|O33#&K/?_{%U#hRSea"9N"Q<5`S#$hby8gN+;
o8V$755A`9TZwBWtXvU$O(2Co!e[c.",ddOSPqG#oH>Rx(Px3m%it/=xq^4VBq[U!~C,_.l_:
UH,P[ik(5>n*JB%lPh&edM"NY|W
`6;2&::)g!E"dSeuUUOb-[%_Nhj_l$f>"B&)%SL(JCfw0`o`u<DiU:qHrbtVwABb[jNhH3)nu.j-7=Mn).YJB[k]UH?I55%$i9q[/`*($XHZ[KcW0xccLCo;vc%`Vrk"cK^LTqkC/,i]Np11TuXXT{OiAt5e!0
h<o2+<9b9#C9^jNh(Mrgk=`u]3qVb8ud}?;O&T4sZ1yXAr,0g$j&vr*Q,-sB:=my8AQ.rdUOF;O<erHh0d<.^&0EW
~A;m<MnQk6n`h
D=A,9hN3W"q*S6rf0ZK_=5]t%O7&iUr+e4?cqk~$p:d*rj768%s^`uG)>1ALcd}OpJu/B-!#%2SuEhEr`*5E"$1x[x?GFFcNacX7;ZUr+Qnx5_nI_y`vV>f7:FgOWIGdLY3iJ8NY5L`(MlM%ffwrdS[<$E!4%*{glU0F-$nv#2}jC<u00u^o+<b-8ec0<Z~@sa%B[o{k+1/!&BhufvaX:ORC[&w;sY}nCWc-DK]&Xo*3!Pyg:Ju<9<)8A;*5xaN`@hyO~*+B-!:%W%v!(9qT}"
>0F7DAK~1}-ZQZmnywCEV}q3H4W{WbW9B?T&W/%uh*r(Eh6$lIOHg#ZGk&`{atwL`cH?[=Luy/
oBDy~;|u:a;5<@9_wU/"&;WO.O[H1OdKF]Nep]Clw+%3OGH@2A`6Z3{.rbqQhhyU:U}N%moyHR#pr1(r4q6?*?/*FZ4abc
GKQW8yFse08,.o,)(2n.KO@tXEF9)%)`y*woWN!SI+Zck/=OG3_*p"*7+3pUQo0pU3Qi:`6@g>&nHjaVH&Y51okj#0uU5-d;#c-AuC[Np>,3.d=qmcLDR;j:]U@Tv_Ytmi.%.=Xjjh0+C/$QRRk==nq#2XXiH`Adw1fh2p3m?mtu8W$L8
K~$m@^ES/k?XL5g@=m=KkXCe#P6uL!XJQL8U_ueK@DK3+"m
$<Fr:>q7ATj#>CCao;*9>/)5>-$tjO<!*NmVAC.]u9]fV"=o1dL:2=:Hlm3P1sn])5ax0d7iuzHHHPVq:>YJu4(z#K>PK!wQ^*^I[RTF$u,~,aN1:(BCyv20EP5_7/`s+
R**N!Td]*FJsUk(V$Lf8(+u9>LSDBumM3d_hXV3iKU1IU9)$G_b.kkE,d;SeX?o-i?M0Rj3i6vMd2`O]T>O*P$B{*NH<$-VL]{2`TuSw2Zu|yXOsqd"?n
$,+mpN7["]p?$TmK+)E*u3<YC(VpR(RB%Q0TvaIgo`k*,J#wug?V:Tu=T.=2;.7r:e;O#!mB?:>s1RNC;"dNIw/$waNU"s+yfY%WN<U?VgqgA`5MN!NET,nAJVw#TmtO
U@TW}c6Pd5zeDN>4V@4SDh{c/!U&=OD4.fu22Vm(@8C3{4`o"pB^YDeq(*}n"lEtM5Ur8+c(0G">lfNH_*OkBGJ7,*00}m?H5)Uq^
aT~XF+I1@[;^ji6p0_`<1pa,A/w]WEjl"C6
/%euu(6>UW`mvCQiStAj?$bQwD(&|*j%%N~i
doU<T;IAZw%rH[hRc
8+ie;uE#-@c9NqS>ZMA,K
(6,b[QW*@}4b9:P!e/fcMTSR"bEWt2,M403L]&
LAdvTA1k"R?dD$*o7^4_FL#IlSM9XydR=]bmSvbfGVcNX^IZ3GcEo]H[{6@.]Hh@AKB<"`c@G_F:AcF2.V{7KYo)9?e5p

67gixIXWOlEe!Jh0VY1]q0W2Uobtu<ZV+
9|s7>|.jtjv^9<E%d1Nx>(8[oQoC-(PbbS3_*N?O_&5O1P/fr)T04]o5W2tp"2!4kn>x*]eO;?_]$qbD7cfm
[8O3JYyQmNlt4aoYgK~JHP&x`)+7Wp.)xfbrWN*3eAfg{q$2Wth"ogkVX`m?R-71?[mQ_UkMDj,/D)E-CkfHc=%mWoiQ/(QN
5d_!Eq7"#rQb#ys{GiWRB;1&QA<5^^vc^aElE.c38XgGjY#p/D&:)vvC9tPza*qj7<SMb5*NfVq!
9^"oiBg5U^,q~<Yo
D"Qq?r]V#ne338u)bw.EWK)bCO0aI[*1S`&I"u$^kk3!Lp.3D-JnfL"s=FI-i2"q]>=Uy9<kG]?p0]J=;mx)4U9$&cy(?_b,Vs1FN;cH[Y
?oDK=bKVcRaWpld3FoHtUb6<^s)PY+3Hq<{C|D[uuA8<d@#9K#X5VuFt&p`+S.khcmOb6Kbd|DST&Dfp#JJq=h#ymjSj"qOhFtNAROH;5[XbO?-]e`t.GbH$"ovDvxE<eR*YVgI@CW|XukZ;tdi<FqIc;f#wsf{t}v4.{v4i%rRH;h5k"M_ihNW/]+b4JG#HFNRSN*aB`RFi5:$*YX_mMnB0Sj)`9SF#W9cgQ+GL^L?#[sNfz2Pr#V~@>tLrMH45R+Y8mEcR*pEYd&BFE",K#+(d2Vr%{1q&$0CNYDP@J&cidUSjKZ:[]p4X{9T<=%=k!WV%ug;REn.I-WPjO(z5PrrM!m6dk9iN2kKcj>-U:H%%WF6N6#Gq4ff=miuQ%A]Q(bC_Q&E?e?qBdf/8Pvvq7=)1),,!/gj<$_0e}@_<5DaEG-DMatuT.=kZXqT$`Gafd&!]GT[xG9-R3Uo*MV^IFJ2VXQPDL^CcA-s/3QxpP3LReEyeb8IKb,k/Iid?u(~YJ#7.vUF)Qu#IKf,G&RFC{!4MCxs`I(65uT)j*JuRrp]_{C4VD$|7:7m&8EbBLhaFR"Fvv:wQza4nV,49%i]/w
?!ua%+X!FaEXc[g).yJP/RMPP>R/Z)_xMCV-KQzhR=GsuDYSSUYluJPb&Mdqt-C0{6kR4rXeWvOkYKcH:L9E=SghLt~s.DKoG`WBx^g?RmM,>D-@D$C#-:0hPB5W~PIEP;tm*_!&8Dk-#csci
i/ap&ZwFWkSdF@75F9W"I[DwnFdGMiL7TgXlq=*P@Bf_r44i,qn3B^?rVn!bu_@>tTnqXe4`]KswKV*h:BA6z3^<J+t)&LXT0H`@x2]X=1A&z.-J{q>":q"?:?[
8fuBeDXLqUtZLepk&v+TeiqKOlIqe/P`7P
aWC(jwXtON.<Y/oyE8Q7R8Z^n6

;<U!r|INHMuhiXf+a/l`e)tu
n5@1mo;`l-qg|5S3ut;C%;_mIKB&]BdQKQ;W^m6_L$uB1nU6C-2nnl>Crbg3^QiNb_rXj^qJnv}BT+ViWeWY=MlfpXNm;eN941Wi"rH@|7qjyj~/-m$Xwu{I(y5)I^_yetVvF*w"&=ZlRF<u968sRS2gqUp<7J<w$jB4~)=?f]Q`MD!.U8UO%(bS[ASEqLau4&ebS,`J`DfIUt<&zmpo
/curYNhxhE1TUJxQGyWJK7!>^-!X$F)lbXX~dYAPObb7t,?@`G@MZ++RAuO500RGVqU3!s(e@[-wx+UlVjpDh0^J7:E+`<iTcq!5fx8}M@e$BJru!8Q;:WEnMRF]MQI3[ZE-R+!=Y.0[JF$(oyL/[K01XI][^mp]?oEKB-AUF@de60spnbZhvwrXc$*co])<qU1N#F0uH}>eGMqC/8rN>v9w]eipK[f)wR*6YXKM!]7A!"LbJ@9y
u;spJ8nnI8dgwc/cRE?3vLsO$tK$7;|B9c*F"h|M`#UOl(Kj)-p_:`ZuwZ+BhPlIXM!^?[*uR&TOfIo(!pux*y$u0ob*%RDVI[Y
oI%P{OpE<%hZi*X_?oVG3u|0uv-yWgU2^3ng9sI?BG19]_B)|nKuCj>"/u*6sR`>ak=x/3I*Nqr9>JF4D+|8,FJ!>:;fasWD$-d?1sX]#gV^UXX28,-<65PWdHt+8ce+V=NZ/WQDUKS[uU+:P[`M}unTJ5?RprF$9i7D^=v2@fv$!cW=pMv2Wf(P@.eMKS|gzf!e{(?gn)PoZY:Kri:-Fj+r.e,>9M:u$RlFr@;P3d*P0l-T6Igk=)l?BdLIv&f:Pmr_m
(^_PI/xq<9>=%Vf"]U}sD+FdA`(@S-!p|^`&W>,/s(JGG6:*mA1ywX$D}su+%d`%ANcg
Qah.ui#O62x(EA6B8?,r:58ZB(Hc=tnCfD(jHzg?Lb^[+NpqxG:L.u=8=sG+#O5+eANIK"VS"GR`t@MH44uO[YYJFVK<%.:|Tb8q[LwCh[GD*]%9IQZ<%v*<4Q;%2?,E7^`q3.Q(kC_uOT9E))7~`3ucpZjf,7ahi[2ZF#[[@eS+$U_q$COzo%o"BvGl5?xAZVUA2LGBIi&jl)#Skywl,jDAEPgJQ]PRk![Zs%H]N`vP..xpIh_k$AZMkStt5O,VVxe_e7hIrCcZa;vD>tq|c=$L+;
wIVLC^Zrq$Qh7vNpK**';break;case'default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css':$f='+O{Rg7nV?&=MEN7&/;#P]lROOX$][e
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
,hpS(,|+kl?TuvT:Ynf_0+Jxd';break;case'default-orange-dark-e6668a1545546a87b40acb95390b5283__1e3abf59.css':$f='#O{Rg7nV?$iyIN7&/Uh!6LdYH
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
Men9`Au!Ql7V?0?Bxco";t^$hB`pKy[|0XSM8"EE]ZXpPh-:EfnMG-jx';break;case'default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__1e3abf59.css':$f='(O{SRcRV?$uMU`I>H]#8<+F;W]X`%)h+r4#?fl%d$HA!etXhsF@cAx%aW"zYl?eSD,[s{x_``KU;Topg=>BJjPllS*Sb}UI
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
1?u_ebu:Ik+(l`wi77+KB?*(yG8$';break;case'main-eaf2ce2c3d91edbef355936903e47e59__d91c4c0d.js':$f='*hc]`iDZS1ptWOqUr:J%A8nFO6Tw)VHt3GZyv+4lMgxb*J#_=8&VVTX#)#{`@N%tJ?>o+>h<HaV4~1c9A@&gj@gBn[#^P@.]JluX
W,vUewi(My*v</Oy.yY|ra^3DmKTR.)p3jb;X{.t4a/.GH
ChQ&:n-v
;#21=@s.Qu^a1Ymb?hgfb+&KkEXoR4Mxm.h!0b^!kxrnA;hlFk>u:lrij-QjHh%OxW^e%^VCyXZ#]Rl*wP!%F5c}Abyf4]YuLR
lkh5.d?arB#H{)US$Dk6
3fFd[PfFiP&R>LFbd}%xTQrUy5"wAVe63d3Gpwl*rx)A)EWHb
In2N6]Gfj}]O"{^$S)dotfnQ$Wh1PW2PRU1SJE-v
I;}LM1[0}sD2O5o5gyo1De!VrQIRY()whAYG2K#Io_"ZE.vQD*SZ#eunb
dv!/6s`186(u.j[(S?N$>]_0#hFZQrm/[6gblGV"4KNk*IUWO7`Y/xb2j7>:4s$9Y7]yO8*?&v!dXCmk*ay2Vnr[!vL^|?UN
d9Fmn5FA1&L^,RjP%F=}pqcw_!d?Bl;mNT^?dqXStlL|@o9eV-yEB]l9olk-?qQ5FHCPth783LE!6s[fvPZ42YBC%+=RT3tELJt*uPfjGwu`.567LvV<"zV:E4J$reOf1E98]:9n6:MKASP._.BB%.^C:)j_QT&!?G=O-7hAN:ypI~x#M"<H!}
z;bYyK`sV79xzB:XOY+j$H>Q,i>xwedRw*VMByU
N=@oh!"vmW4ukPkl1D6YxaVF1]r08aHrJ$qte+N2k/$v
`/cFgxX$&r+F(zi8U3/54
_OV}JQ0DPNtB]+xcf_MY
=WxN17?1m:iZc3$u_V5@i
#1-evIC`C]
;!;9kh!LkH0i&Wo>MA^H/~d.ujUa*
HjIf51QyR6?@@hb;W4X&%U7;eJ3k[<gukMt;b5VKK-7;#n-oMvw{M2l7i6:ee4#<o)_7ucD{g!_6c^1[*/O{j:JYC21Z=ad(0IZDwo_T"EDA^LWVHgE#QC8jbZ>:N*[[f;?]b1^0AEBV(`Gg1sJ6H+V$*Js+K4v|&CJ4.QYKMh57"Cu|v#Xqw=a]G<)x<kG/SF=C*lLPa
FXf
:,`m.m7F63t?Cg=QZbynD~1s06-sJP+CYvtP)cxB#V%;d"wi*%LV"JwsP?e*"IGSkaHT>)7Py)Hvn*/f`]o3/+L#x9ABB:S:;[>V%=3(PFgL=PPJ"{$lRf9G>Cod2yKjAThhv/,_qm
F
Vc2@1eq?Fs^NOp~<*lyNgx;
2UaSMZ`j=,0:h,$
t?HU[&xpJRg84UrUMpCvu35b:&IeQ!i%![&QP?sJ"OY@7[qkS3K4
;xg)lW$uv+We<w9R7YyD9!82?*(c%:c<?}wgDr
MWa/AXA
]PsWv;IY74$yhGPI4fvv2a=88v=kQn>&H9S#8<Ye{-N)8:_!qPu/|fb$l,nC=(>&e;iNLbi8gBC0}5%px+&IB^;C4@_+F0AE(mx,p8(2rWLUP<i_OP+;k4|1cff;sHs[h.:9}
r8dCwC%^t%7s:?]iZph#g:c(0?6yf^(HSKP!;phRN9U)H@"=9SqDgG;MTRHfUMVSeve"#HiA1a6*li%J+PE;HD~42woX"h`z#G|Tt95frE+]8wNPKnQ!9gxks11hx
q=s;TR2W!FsdlW/9-i~9/]=dp%AQEvng^RtOI;rVT8GCZsq!ESl$&/~fbhg;c9a3K
0PlrF,d.#r
1WOZub[&#b_>X`-}gPEl[A=}m~:HxwwF2b?1D20hK^ix8lh`74
*pW*Qsr`R+_e5a06|a%l8Z3ok]N2M7i`aEOy$=6u1GKC{VAxg18+Ed|.po
5I<YDkua1xNKWmWzP@;Ou#g_PM)$6$^+vi2N`zN@:$HJa*Dt!AYP<7GZ*N$wUJj>^+2f5Gp),75o&tjq[3-1TQ/9b|GYtHxUSO"Y:3DT7W*@09kp:JD3bk]}`0?/Zb4NWO]I42r1$s8CP*%v+_lx>ZXv$60c`s
8f,sHA2w*BzFsj7d=M0q"1@!9MK`C2a7hEb`BHrHCXw:YYd5hWcEvD0!
%6T#N}K&QJXYq$eHL?oV5wRsNHCOkkay[{`Wd}?<-c5rS>8?tu[sL,,/bHKoy:C%KuhYe$3BiX"FagGpPQte1{PF])@03mQJ+E?p^}N"L-ndqk
$f3@I2c)(yMIw%k0lpjKGNHCZ.gYc&_qf5W4#3p?Pu%y9Kb`u?
)^+AFZ5Q9_vLIgumI@vbb7WbM`QZSz;]P-H1I>eRsF60"w]bPDYO/|qoe5t-d75yoBn#vK3Q&gtrn1EvodnWM/
5wLV</<vF*eo/,[.Kae>dCM6,@qdskCZ>cDC2l-4D*,54#t%WVXF^emnJ@gnjv2Fd4lo>HT2;9=gC1IO|THFXK
g-4zID@b)D7K>Y$/N5cz%Zp2l~`M)`K@--02$}KE>h3g=+8>?#SA`,ed^
R_nwG1#$jedhKL&{%Pav;Kp!YMX`[[q1#_s0Jy+=i"%?m?4hiW<3I%F&":EANZNEXMGl^{VxC}S_=Q).!3(A#&lN%b^ru*rdp(BZLtP{%y2
?<3HG0?:ng/5
/XC,gG*$|AS_Riog|;>o3^BMn
z2wuwx%48YFtg1M68M#C1Mbizd6CXu"OSlT;Qb$!8y<hFD{KRue>NeRY*1~$
j[AfLXr0+|]Hk+vknb6Cj$Ruo%-c8[?x1)#D:-dXinc<KH.OpPd~/M9u
P$6;4HUFRV,32Xu$:PceHA*,@K0@v".MrN%g=lUdRIZ3p6/Q=H;B?j?OTFL#-qEWQA=i
b5&(2N^f2a
F&)k>Jg"wau+m>w[w.!3<[{o_/+"F57$@V&,WN?,fc/3wGd!:W}3$oCV.?I55yYh#*/"xFJ%L[ALK/{m:Qo>.vn6R9e4S]MWe:Uof.4[{!o8?$)uZ>G#/=S.<GlTo"r0-E4n*Bpq$7HF<H6mk[;;Re"kv=YG^b&^<v%l567io7c1}-yyCWk4I7pFeE{`jVgm}n!.0U^QR?AKLKqu9uf(#^TJqb#:).5E2xUx,L+gj[6ITy?mwuRd1=L-9Bb%%o6_c3Lf(_ojsfRBQi8xKBVjYkB0aj(rfM-kA?Hw0#-&l"8dDw}7Xl5l
i]?zo!N--d!MB3OFI5+eNS]QCFUv6T[c5H7`G=GP?7k>&BZ]5`
zo^!ia*0UFVc_>Etv%{nBE1%rqms#OdH/hhAGc-`otgbc/]S=_4U)qOQ|QTn>!hCqTPZUiElNs&&%XPFvUmXf=*$UKI2nn
cDaHBYb|Yuu:[OohC(tVIrFK7<nEE!?:1gj=:,dl+l
/j~y}"svairFA@-bC!h?p+F8nssDe<;CCEm;?smjFq&^jdlG5PZQITs6ix,GOa2iNKpFi6Raq1
v}HOm^pYUG.QexvG>#X|
d/rem21&!s+*_v/mejc]3d3(BeBOvIsl#]D&:x{`}L=qa2h%j)2W4&6yrN0crq7f;,*c5xHsUs-/
,jOuY]Kfg3KldZ2^$i=G"jaIsX:ZDA7hENx"XK^eRhj9xpXQ_!`h*A3Kt.<?ccScLmSd7QW]v!D|w!!`f8$&SX%%["r"8Q[UpGw$uj!}5V$lp>;=ttD,/k9A_]a0aiH(99dSV0
Se,/kpXx%G)T0[{imtm(f2S/-:0P.
`SJxELW>y.`mn9s0:Drk.EAr6e`Z*$f]>n7U%i4.Z@t=X02ctZoxg/c5?yt;YC7_4Izm@kI_@6-,C[eLE[aBl`WJ2RY+XqJS
*5/R-c.h8&^?d56/Jy)Ix!:hrx%>h0]#S?!U"3E*E%?rp%)Yiw7:n1@S=}Y(A%@?7OZ%Zh8tc1aAvGFnw((E#z4$$zCB"[m+%5)PN,^O@Go.[~%toF?>$MIrT$AC`c.?*1
NT)jL9OL@`gcTdPN]/K-y*sv4L)k~GZH&;yXs@G3~whX8"A!Qras`ZQ;)uy"f#~1VF9VGfCR&;~
q9~//1akAUHeq3OA5p>9DaksXFT7Ht/=8WmSinHQO%xn{Gt]3+2f_hFO,)@H?*)BO&Nybh},Jx`>f<]^GlN9oPs3"aG2WQL:ADebe
S?+?*u
${]YXC-V;Z"t96UzT]]gJh.dBc,p*^tLP@X0m"B8J0wX&-=Lx#6!o<S=7>9C:mG~LKP1AZk,gD:TkOGoMR6UA#%C]g`J&f8r)LV>OrJL5AY{9~S,Nx6lSg#=C.8D]XQG+evhd+B*OZ?}c-ah"X>}7x=xF8QS=Gv_"l#DKekmexyYpw+1``CA=VdjQs(s+Jo|9?])!v-UHW;X)Pjj!mGMNwEMsr<k4n1?_(WCiC!ydj9:yaG<<T,i*W
Td4gr^0#"%/D,NMJSg{lTRV.gPAQTg0<%]:gSc~4CY)shl"kG6Cy0;jSTdK[^Pe</xDF#a~8nQ1tmVg;&y!>Ug|Qa:gl<lx(-jpfd,f?6HnK!yb4mhXk?Wi_7&r+Hikbjr?L%dj.y9<P:g@S1G7ae/y3)-O1k5VM:Aa:n[a+s+_a,d;YIs1ee6BR~xr-bxUS(MTim4C";bmZJm7Aeu`gv8RksDv].ftGJ/suGM]f=-;z%<LaEj{Ytnac`]x/}OY]yWb"_4Z..[}Jx-(P"52I)>s<l;L6[T-.`,hL-ac5+n2vuVL/Hfw"}uH!f.U2a#I
sQ5J%k%O?"nW!S1=d8wOaa$b*_dg2)PKeI,%F#.A9gN<PKz:Ykiwx9T)]
ymf#<^V&w"TJJC|YfcBds?Qd@(NDU^1p"IT&~8kH]3ZY#F.7QF5Kgj>7w#TtnQSyot7,Y!ITmY=bhjD7bbmHe@5pDP00Xg1M,;+:4OQfPZVk?6;TOm21Y.d3<xC[R=X;7C34)(9cYcvVj0;]SUtNN5e,8g.5H@nH,Rs,jkIH[EE&B+Ei/hv9ojB";;%10
"<Q2hCvpTS$vr<6C!=x(=.aQdjCMkkNWt!X6rbZ/hY=l/aeA
L0e6>,>+^@=SmK!tw-<YrUUAt-Sq$nit3-&zG1ggi)1Y5]>^6P&SXChgB-SOL:wqSb8$MzF<d@!QSOi|W0D.Zly*ZNRvXYkKoV.|PH*/&+]e(LtM$sjyM=Z+Qn,ChaQF!c!)n-]t2j5qx=D*n$hnF([52[V|"PQ~z(/J@tF#%A;T[er:&Da(+$UH;2q-fz^}cn7,!]7ao;#ur}`l4)--c<hUntl~ShC>um>IW`pO>(ZFnc0Tdle(Fi7MVZS}-)J{m|.{VF5!/LnE<wZ[dj
O`|t,-=0gVYff@<K0qu(o-sZ_w8SA&yM"6QP{R6A(P}5A%,yX]9!N)l&`(>mOd6"KPvbZ(x9@B;]kJs,k%AZHC~>]107PKZ,AYeDm<%y`d}
yEB6L!(M2umlbTdQ`Ol2aDf7w&<YK&X^+QTD2g^A|6EGMsD3}QAw?#1G9@<PG)Angxf,:8af%S6F|&o5Y:@,`"+y=TnOt+L>tq!!c92SL(.In)JP*IjRs77df1}k+fsT2_@4$yfi@]/w5<Elm7&o_#m9bY4MYc
m?lU;e>MVy0GBnyP9%s
>"v[p3BnvTs|naY?;{uxS2O@Anf[^$XCqZXqaX8$"K]3!GKQr6UQWB=(JQZs1=RX"v+RC)%pVO0+J2lRxT.;9Iu0iSD0n<n9pMmF3-OS9!s"+Pc.5W`y4f2";VUETIRQ<aB^f,:f,nk-H7>QmMUGr,o,=Ho9eo&a.bui[13Ws~,S:x,,V`!:J&yHDu`uK>^
tnk.+CyxiU!$dw"}&N68>whGS6hPTUUIo>o+yoX<-}KC$e;jQzl~P-.UI*)ykcwe*;i
&a]u#cM5rJPCe-E~g%v7h8F70Z(v>"5zHKGpeFY,p
*Xb&*n^H8XL4G+_>-32KlM
Y..oUwA0Xp]8Ffb;c(Qoo">M7XM2
8l*SMl<h>=V+.=&aq}yeq?^8#b[!Z%;5.>dT?Gg(j4#q?MoJ0rx!95W>
^%OMsl=DC](v&#|<zZoy2-T`QvM@:T~Qlc~vye0MLwoUatkLl
pnct0]<L9qT"GSxqX0Ek1X;+&AS={,%<$!P147]VN-aFS4|Beg@B`wPE/xDF`,2b=dBZ(*u@V!h
K`I_hU_kvlu%!ui?+pB2#"OIyZTAE&63E9[ZU@WLb4y5kEnL.V9J02uhKuG/2`xX8a4/DMF"JXE<y:jGVj!eX`#%:PY
ao]n~T"@RK.,>Fn>IZc;(6HRMS<W?n,:qL?DzvX%kPCF/XRVkkd/&n?7N2Ph<h$wi%-_u7
ZC#T;oT%0p(-*g>)fY@-R?1(_.
tU:=^+J$B`fZ{,Mm~/9mwJu,](:h_lRvE4^pZ2pI-h,Qxd$XAV!=E9QEsQ?M*@t2?=R*&-%Q.1R>$
k-f,hX]%IsE!aNB!A3v)i&txR,.%^CZ4_MV<)t>/`bYpGf|m=xs.e*{gMZJqH/[9<D#9p+x"g+92#iV
``#-^UkL)l5.#s~nXsU1hR[H
x4EXl~Ui`:WF[Lf]rW+ZIbJIB`]3B7j;Cqb{QTu.nj0"EQ,s*kD./K6|Ew<9hcpIFkjZ.qD-#%.#F*ufKvO@Ep-h/OCVI}
W4&B{Pq_rPPGz+..;KsYQFz8w@=j52bVq,K>SO:*![pE/y!?CnII-FTJVwmd1<dA5Ve&?(g#k1kYRDkD5-oX_3`,oj^mzSST}gpl&jnx^<3#fbEa_TC@0sR;^9)J7U&3jD
E~FSnl-G5t*,P56FbjLWalP`0s3<1*(}wDGm9#?#U+Z$>l>^u9x6fK@HC"A@:ayo"&L:fDyok0*Ovy
e8AY3+|j5.e[GKG6H`4eWS<`z1LV9>?DoIK?qc>+"f@b9gm!!S{dk[M9A5}TM74Ot,{/Q;0jReIFcj=3)qd-W<7@n6,lWt43WwN>V[,D}?wde@mi[jB>sDpDae48Uw:<b"
p|jI>XAHO0jdb6!_y5]:FU,BDEGwe53N21>en{xZ=d?Ge<gW_Q8qtCYq1zv?fT!5bSO!2zo;_LKNi;K""G*Sq3H_>A7-B8kEuEMZ_D0u>GOiI~RakX0=267ql>9,q40j:56h>="uhqa[1e`Mg%$o=0?~KhcG+fn(tNefZ$&#d])DV:rpL~iy[1M/_U`:wQGK48x]S-gXf2WL)Mii]0
U!nkh67>B(V%p"ZNjdL6m*)^Ay|Fyk
KuPw"&7H83DXMTeg8sc3sr5ZbnMAclX{OK!i&[22+o4ByZ5S/6>eeD0-<`o!@,`:0~x02WO*Df!)k.e7eQKp[m<<nx?_ctxk^GmYsn&LxlD(1ltA&YG8XTHx525BkAp(9V3f[<"A=S(y)%5Q$7p8SI<>&of0T:=2SF=3dA?Dix@b$fF[)%@tVW*NZ4?^-F`1WfW}/Bw*%TG|I4
Ng3*C>u@y
#kk,;G*Y]@Zk#@J0f=S8,f*`283W)VMO9pWQJ4N)$7=%
B0>D92HN0Mf&.1CL!0l;FkPa%<7s<EYW%#jVbtx@*&IIY,Pk+2J&^"Yl0v8cREKhfyQ7PHZ//f6n3FjM_oP}k"_-UtNfA#e=iO(;G*#b
8(ducdK.gp6J].;T30a,q[9;|NgOKqA7|?t92Ma$fwUJX_V+D<Dma63q0`l;fRj58?vo!$G_~V~gK$$iGp:HME<w1b=bc2p"%Xa#io_qY@Ut`dl"&E&y/u>Uzr{K}(T9?Pl13x(;t+Ru%
;*t+uggV6[vWfFnIv@t035^,t->$?:|M+A)5+O`KJ%.itK=swNyG;,nio9Xj]T|VmIO.}wFs(fa
p`EHsF^".JYOK.$lG;%$q,=O6AZ:~VkY[:ti~&(u)QoE
4^tZEjDp9X=?P?OV$iMz&3BD<h[JZ&Lud}Ip%(inOL,~FtD8qf&xm@tTHS%+z!-(ElwU,{OE5B8D?Ru:!St,&+:&N0L~V!+E
#`RuFOJg}+w;8>VdO<G!uSauXIp-&QmkGi#h1a|!n>&bO.&oI%8]#KvXlrQF8py-FN2c`#g:A8lMb--
kD+^S4gr7t+pTM?_>TCbZ3]Vvy%M6bIcJ7K<$A8RxTWUAN=0&^*L3^go%R?y%n4)]y,0:Uu5RvMPsgl-,9h;TOi[8"GUv,46bH+pRPEj"ms-0PGyF0;sV/%BKP0D6-F>}+]?w:w43e
UqX~HpYWTz>NGROAQVoAVL[U9Ne.Ik[(8F^&(z,2etDDov5=U~ZvOZN.A$E"<f-hexgVQ&[tHR:/kQs^po&XUTZIx&;D[%E2**QY?...Rbbd22kw85+2tkWVm4.^4.3RJWqAVTy)&WGrbp.=7kVz@1JIV*U$)lJEhoWcf8smdrX(&*@`:`2=tU&wssNQcMh2=uwj<KjaI-!oNuNVfB"%/bb"(*im&dS)SxoS,bT99aIGaHvwe3V!_k$lYm@h7&kxge!+3X9@M$5H96T@St3u3#aCN"v*^n;%mS+}tw+}Rs+3l#kA%O/==Ga08]qdFxVk,ymE&=wd%
&th`]yMt#<P1&+jc/9561|Zs:TxIumwajSObx^v!s#qR:F(tox^!>Ai*H+O
E}_!RJ"1Q|nce^<u)F2+N|C%LO,IQrZP6L/m+=-J9<j*fnU}ATXSL<T-Q^JvjyF83+4Kd|_WCNm=]d/00^ry5uu<CR1|MZ_/3hn".XbZ`f#2I=qQ,>"9wR"VD!Pu"#k,C"ka6<qm[xf_r=Dc3(%43M()/wMP,eglmMwl
C%6IB5v%<X5@kCP_bIElB5J-PC#1{?I!QQAUi+E+vh*H_qd(5^8Q)-VllW!U@j:N<WISd:~ljliL]$,$ULGr`>l-`]?A"5dir(0qAXjj.1|p$,jdO-+imX5l*H]I?/;:"Ud
2*AZ3<`=1RD"aO1HonSi{E1<dvCSoe@R8=%;UayAq[OVF?Pxv3-Q^;-UPVrC)4$);k.=4k!;*79F?Gpp<QULhR_T}r:`cjx!7hb5%YZ"
RK-"0)%+<Bi/tJDai}SU9p9~H&_mY"As.(;$jH7IKlVLvHoQ#qI)r~]=){O;1(pg>.D%bS?D,sO6fm9l8GMZ8&y+:o!}n-PD],Av_FSPB_-;3IGxw>7
.o^$vUn3RW0WUH;jeMF}A<pXD>xm
?J*_QOBtVt.R/(5<X)1!Lf!iIJ0U&a
o#W|bsk
yqNfKo37AWk_#W:j_5H%I]HU8+>*B4$zr]Z@?]TA#&=3[kO3^gK1tXZBpwa.22TgriV4$f=,E3a:wi-[(ffaP{jPCli
V#1Eo]Fyf[Q`HQ!n9[s`4m?kp.6*6SxV@Tb(2I*LhI7Q2Q8I[Dk+LseU1{h+*mR$=zL$DUMrRDv;GRpuybV~-Ri^MJv:^lKg>%9%2)WfCO+Vsh7UY(u
uTEOM@NY:#6lZ>24%mvjc7Q)1bX/:e9A?40
klka_B<dn1-ZbFRh"|
:v~VwE[I3&)ZGTD6(L;8Be/8$oA(Pvr.~Hgq)CWA3X
n+-E(SDDny
h&h*kgE-lV|=]
MB*!^sVGi]</Z7%8MPuE9fW+;]
vpOZ*s
kZli4&=3d2]M(-7EVCLeP#L@YhP%x9Z(u;1)bYXB?aa
U>h.x]rLX50]E+,*PK%m-)$Pfy1[n*-]2/o(DN9x(<^<]yA_[0QGV`Ns:B5Q,9Y)x[#u=@kpXK@"x[f8o4a4;S
N&2W.`FPDMeRPgAGXI<X!<]!qKMoGv3GWaU=Xm8Y#A#9w1tsZL:ARn/%6r)F+jAWByYsH~b8^wKv-<EdM`1xaimF]Ad%LMlO2-w@Z^d9/W-q9ykW8A@77[Cq,+]%n>c%Xm8A[3
*
o=oLBFk0sCW(OTl
FPY>QlRn15:KYS$MWkNe~Z`Xr
M-~F!H1Kw4&GB[CeE_j8/pTl|n*IES@a#_G?rg#jb]5i!&a_<I%2z6kV^6^7;./-dr0Vk#8i019v&ds^6b{EQ;(K0^8lG[2F*HTr,N
:M4!hWydZr()*zRRv:S?B1jm1hp~%EJ<_,I,Fk>{FxB*"?Vd#pkgGn
$_[4%LvT+jD#&]yyLO"p8;<7{3he
)@S@l7w.w}n}i6Xsz%6"TUG3hTo"(uW]iBv=@|/vK6It^UN(7eu:tp`;3*TNEoX&1yB)+Vj*2%ggwD8l"TN&:0Y,%a4Ow5D=yU5xEJyd91u;3=4|4osw;=KQK=Mv74<N@rkL9)ZZA<"/F~TSG;q10Un&Q
HaO;pN%{.S)<ZBssxhM"CK(Ec!Kur5RFEyKL;]$$4!KMoK]5"nqDl%=clP)e`N4&mO8ppD1Ygf0>FWo$4KWJP"N_WHs^2s2G"l97Ht`9TngO<nu8"=ftduj0u?ey4/1@t[Ki$Td`N7%kWn?~oFp(vyQ2)NqW=TDC[NHK1Q0kla>dXKd(Qq8d%">HA[]]iHVz@B5-s6y=3`akYdrC!|QZ!yqzP;T=#2sE&:w]93VfQ8_F2JYq!y!nJ3NsUuEHQMh5[~J5;89S_{j]_/i7-lF-6<VuD0BF_fcp!ya:*m+l?}$>_M)*;PIKrwg1,LU9gF"D>,DL_}fP0!4JESC@x:C*D=,IEm`
Oh14ON$3P
"%P9.l"
/whD_DT-I;b?_]8S<quu<v8FvPBN+HbwPahA<
@7O*/pli0>:)L:[@`tG=c]qo5{QUp)DW<#Krd^@C4"JyNQBQD90F!1?a7[V>U*-DuJN/;:Q4tg0)Y1!&XE%:K5vSZ+C-rBec]/s>oe_Ym;!T!2=?
QrISqKz(e`ck&L+L&:
s;E
B/;#"qX6u:hJJp0,P:bpxHk8^HK}qQiu9R[aXr-H3h0FSER>mC/pc~/Ry7Qf4a74T"nE=I$9Fk:.@7_c8R
3rtfQ1epzVKcLyU2v]If#I$Xr,#"NHs?.T.a?xJSGv;pBS7kTJ@`e%.L%ae5}@CYYZ{c8M[Id>7m44.J}Yd(|,@Ca[DA]jJO<K(0sEv+xEV<V;+4QjH2a0?13PVFF16eG/z4<128b_%uzeZtdljQ!QFsYv.ol,K
{now;KT(j2YOXx,`kkEOwY8Z3f&4!"UlhcXtM+-37&Ok<A($43BjlAcPNo,Q(M,:TDdQE6"Ej3I<0riSJ+ZVw)EWM*O>3t;$)-4
SG|6@n?,95q;6?w7[7Pt&J1Wd=A0hO4$/*cd~weO$CP6sg;0e9e-dH/U#ANAm<4e-)B1Ue,uv0n(1
G;%aFeVmG7w^@2SZ]/vbkx
#>:E98fKg"h8aW4$UFP[t$?[vq78n|*#YI6^-zS;R.b}R7
mk}0xX?S?Q&usXUD-K-4N8j"ycY;GPJBR>)fzLz]bR~ZQhZZ[@EM>uQ"^D0a0<`Oztg7.fC:+e8eo=BG^^Xa^I7q,18K*HkQDO<+@J
L6KOV
g|<6M&N2fLlxySI.0|
8Kmefu|;znZvuWWQb/*t?g]T>uu+{qmc8nU/TVVhRH#X~jse`&B+}"o0{hh,@6F7,I{hTbm>/^>G7C~v`o@ct:c7ZXgIDa+sSSO5Bgto17Jo`-Q+ce+o
dLY#*}lCZ/
Pc0*=TT[;:yW`<0YOU"jm0De_PGcxxQU/X8=SAecGwD#C%&r%g`=&p@OhI[G?#Qjs2|aY!0+l3F:LC^!)/cCzH7i%E!p?MRH0E:28h0KDv_jZ?aJ!iO`OQy>R
cq*c^
U[$Jfp48_$1f@#,GeIY^/=obP:Ae=5il^K+AJ]nNTJDs5B{LI_I7i^{Hy?vgmTcy7gt$~d2ubhMA9@Z.5gFJbTRRDpz7W*wlSX(C=)Q!2PB2sR!=`*UBC<v9]5T0iI:p)RGjFDKn!OHC!voW^la)X)F)T_R1&P%hG!kNdP&xO+|p+(@t?xoSHJh7Cm/N!lIt1=,G5&<j@>c`L3!jg2,&CW*QU_tZnX0al-Q"Cjkuk&2>rTsCYuk&uZ4GG"+w_k]3f(hdc^Fay3nkj#|6|@Yj?6ds$j|D*Ks8Eu95B-C5=0%:f6!E9k/d~2R9r"<E}=,$ATnasjPQ3aGeUOIGn8}dVVrm%.*RQ`fR(w<nJ,hN1ybss[S9jh-gQa|18Hbx?WpH~r0BOg{KxZ;gyId0LMnLdd/eJqUErD2!V4y&;S?A^>%=Fl9pKw5+8[MH+XKq;l;K1d:u^^Mda5@fY2!mV)MLXtbWd:,;c#A3}Tu91Dj=(/vRk%-3v80mrSoCM><7Bpf6aOjToyT]>&8sBim#<8XJc,RC27Awu+KZ8R_LHYT3P*EvCQqs(Z+EBeyNxnz2*ZW>ZmUi3)Sg%(F!,6>DNYi.4W{/o"TFnj?jqN3i3nG4v#IY,GYwW!,GK;}inUy=,
`v=8[<(T/
Q&Qp)38vuY9<>dwn8g@vJx}v)u3qXso4U]fP:M5d,(fmA`C!b6RulqRKfD;J3@T]o!cs2]G2OJ!)nb4&LI<v-UAJb^i3gGm^[ichkHvqjn5XzS*j$<=3.BjskPX_7)NJ@_tKQ/#ol>FXk)Cn84.sRjSLKpAv:lF/^&{YGj9,w7xyn^OqIan<7^1IU)FlSZjE^kVKybV+bm<GP&%$3`@F0qBC?,%Inq}wX@rmR*6JY!cN"/<YhevDN=i;^ZmY_5*H~0^P)fB>1[UVxCF1%63Ld9/]3Yi?U_%jDT51T(M.pj?&gDijrg"]"O%ys:[5o%_
=[!hW#p)TB)(4yy?6)O,(&-!
L%adw61#1jSo*pE^1H6Eo5X|9Xwdxuc8JPt2tcsd$)="GDpC5Ha;rWT,E{DGS>g3s"QyB8z$v((ulsKG@::wrK^{FIoj
+.I,4b9y>rK?
xSd|Mbl|*/Mg$`IT.N4PRU@vRGn<E&3L]@ORdt
?)A"]7KT=`c$cI2)1E4MWFetRFb>jwD"fbQ^*F+Af<njUB
ibQL1-OIb_hu"=5SP;>V*93eqx,8:
Tkb5L,pS<"Aj`($h4u-,:6yCH1X4("UY9E,W2h:=CD&Mn:1<##C}^*Nq7"m`OydJ!{c4cT2Kt?Y}$=tof,IeN9H6n0Z%v8kHH?0Tk*yz-SMom1kk!Xsl=kweHgrLO2WFtH6P$^?#n(oB")wZqivSec56B+p-C,QhRMOCW?iti3gh.56mW$jRMK)!Z)<f).$e^1t19^@,3X*eicR
aGg=broF]+_eFgwEWe-Jd]YZA(t)dycL:UR/g=B6[Gdvv[?UQ97!A`g4g]j.]1@HgAjg3KB04U+Qr/_PcmqEI(c(h*;ngzTy#b]_*=vSBRmt]A,X*x$g&|)PPLwE<pe)
~%oY`rC?Xa#o[wtfSMTX>K*qw9[Q5&UPf>,6Jt$m}Tw^0BO+BFnF2T~9{_rogy/E7xj,!/Lm[?AQkrIv#yhuPo)&o
s[UimM1>2_!F5g_haD<
83z<J=mMpqgSNx%bNCzB-j<f3",5tMJf^p-0,KgKZQ:Jo`r#}n#[A[0:(fs8xFzt}VNUi4ia2u@&%3Rj:BIA>Kpt81tV#VqX2cmStKjd#wA"P:Qih6%?_b#RK`0,Fp8;sbaLO6v)J7|iFsf4dHM5b<*/|L"bAP&bh6Y1o.#bnBwJvLM@Z1oae@(rjcvup,)*Q8d`>_)qeF
9=pcva21;l<13,H3QCsDu,]i1Wls.:WGeZ!@B+e3>h0{MyMf_*<b
T]R_!a-x<lu+ypr`YLHE]HK=VN2<AR%q9JZ"TO>&8:ad^dcwS?<m2qBrO_DqrniSO)G<*oJFs;;!Z?"s8F>%qp8W&w^7~)ufHoQQ4eV,hbV:U!"I7>`LZ>7nLnnvv8GYXg]7i[)_s&*/)Ez/X8zZVN6X~&M`gdi*khWTAN^lV3fcB<4/dbYdM[K`M`}qxY+J}3{v<f9ka)lQ2)A7O%UU~mw:H
Drso7NRLvrl)@P$H`S2-ZQV%j[%?&XK.Abq9Hn?8X0;imN-)f)?9P;;eunfAw/h(b(|Ete6Ix2`N~FfLQ(;"vmNkzk4C~+rL
RJy;2m!M84]souPR
(A"V[ufvhnxu}-&G.=_D7^v?`NN!
Ff![^}l%6FN:^w?z9SV
V_?KSl9yajJ%mXw&amG]
3_3<E"pTN8~yg*EX4Nzy(v@PCF"^
z)/;rGT"yI2%:<Tx]oH9rE#Rs{mAVO<AN@q_-[E3*##)"n!Fgh.@`9#W)Vh$2/vAN7-m
$8`6/0;=blHua>kGE@<"03-3CBrpQvEys`<YFZ?7$VAbpTKLy%TwZ0=a@m?=U"z]1#]0<48lWY0Fu]&1VaBB!BV_(@+lr
WH;>:jdlcU!5/B8/s<o(aVC]KyF2Bl5p-Nut0v9:p73+{MQG*8~ZB<Pk@AfmZ6H(F*@ZAKMp6,7nMf.A53cxj=4R9]A":[b.uo&Q|B1X
_()9XS^{(HqSYmDz&X[@vnS47-,uPU%KMGc0@#RKnA!tjky4(U+zAKKTBQ5)WZ%p@+1`OO1%;KH0gb*yK
K0CVH5m<r9ZV$$dz4Z([$?9aFSk[Kiib%"vS!N4kGn7%O1O#lCgBH(yLtNs:mUl/i^8#3[@Ip{(kOb
X3Y?dsd$=Nu:t0y%/R49.rN(Fq871$c,zYrAQr#]IdTO`pN>W!H[SKRXJ(%Y(s1Z]3Xi#HbaGRC^?2vJHuY3w8!?%J5Mh!+`O`>7DvpkrA<cNDFIe&cm-GorHU1&|V&`NIbGe(T
rgAk1FsUCGeEgG%hh+%Aps}8NG9FD,;P%+Lf]v(UnKo3da`I[][F|cqHbCC_WlM!$Hd0NxI,$O=Kje6jE7yK=';break;default:$f=null;break;}if(!$f){http_response_code(404);exit;}if(in_array($ne,["png","ico"]))$f=base64_decode($f);else$f=decompress_string($f);echo$f;exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid","0");if(!defined("SID")){session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Ee);$_POST=remove_slashes($_POST,$Ee);$_COOKIE=remove_slashes($_COOKIE,$Ee);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','ar'=>'العربية','bg'=>'Български','bn'=>'বাংলা','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','el'=>'Ελληνικά','es'=>'Español','et'=>'Eesti','fa'=>'فارسی','fi'=>'Suomi','fr'=>'Français','gl'=>'Galego','he'=>'עברית','hi'=>'हिन्दी','hr'=>'Hrvatski','hu'=>'Magyar','id'=>'Bahasa Indonesia','it'=>'Italiano','ja'=>'日本語','ka'=>'ქართული','ko'=>'한국어','lv'=>'Latviešu','lt'=>'Lietuvių','ms'=>'Bahasa Melayu','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','ro'=>'Limba Română','ru'=>'Русский','sk'=>'Slovenčina','sl'=>'Slovenski','sr'=>'Српски','sv'=>'Svenska','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','tr'=>'Türkçe','uk'=>'Українська','vi'=>'Tiếng Việt','zh'=>'简体中文','zh-TW'=>'繁體中文',];private$language;private$translations;private
static$instance=null;static
function
create($Vg){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($Vg);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($Vg){$this->language=$Vg;}function
getLanguage(){return$this->language;}function
setTranslations(array$Cn){$this->translations=$Cn;}function
getTranslations(){return$this->translations;}function
translate($u,$Fi=null){$u=$this->convertTranslationKey($u);$Bn=isset($this->translations[$u])?$this->translations[$u]:$u;$Vg=$this->language;if(is_array($Bn)){$bk=($Fi==1?0:($Vg=='cs'||$Vg=='sk'?($Fi&&$Fi<5?1:2):($Vg=='fr'?(!$Fi?0:1):($Vg=='pl'?($Fi%10>1&&$Fi%10<5&&$Fi/10%10!=1?1:2):($Vg=='sl'?($Fi%100==1?0:($Fi%100==2?1:($Fi%100==3||$Fi%100==4?2:3))):($Vg=='lt'?($Fi%10==1&&$Fi%100!=11?0:($Fi%10>1&&$Fi/10%10!=1?1:2)):($Vg=='lv'?($Fi%10==1&&$Fi%100!=11?0:($Fi?1:2)):($Vg=='ro'?(!$Fi||($Fi%100>0&&$Fi%100<20)?1:2):($Vg=='bs'||$Vg=='hr'||$Vg=='ru'||$Vg=='sr'||$Vg=='uk'?($Fi%10==1&&$Fi%100!=11?0:($Fi%10>1&&$Fi%10<5&&$Fi/10%10!=1?1:2)):1)))))))));$Bn=$Bn[$bk];}$Bn=str_replace("'",'’',$Bn);$Ra=func_get_args();array_shift($Ra);$Ue=str_replace("%d","%s",$Bn);if($Ue!=$Bn)$Ra[0]=format_number($Fi);return
vsprintf($Ue,$Ra);}function
convertTranslationKey($u){static$Nd=null;if(is_string($u)){if(!$Nd)$Nd=get_translations("en");if(($s=array_search($u,$Nd))!==false)$u=$s;elseif(($s=get_plural_translation_id($u))!==null)$u=$s;}return$u;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($u,$Fi=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$cb=get_available_languages();if(count($cb)==1)return[];$B=[];foreach(Locale::$Languages
as$Vg=>$sn){if(isset($cb[$Vg]))$B[$Vg]=$sn;}return$B;}function
language_select(){$B=get_language_options();if(!$B)return;echo"<form action='' method='post'>\n",html_select("lang",$B,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(80),"' class='button hidden'>\n",input_token(),"</form>\n";}$cb=get_available_languages();$Vg=array_keys($cb)[0];$fk=null;if(isset($_POST["lang"])&&isset($cb[$_POST["lang"]])&&verify_token()){$fk=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$ll=($ta=Settings::readParameter("lang"))!==null?$ta:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($ll!==null&&isset($cb[$ll]))$Vg=$ll;elseif(isset($_SESSION["lang"])&&isset($cb[$_SESSION["lang"]]))$Vg=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$va=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$z,PREG_SET_ORDER);foreach($z
as$y)$va[$y[1]]=(isset($y[3])?$y[3]:1);arsort($va);foreach($va
as$u=>$uk){if(isset($cb[$u])){$Vg=$u;break;}$u=preg_replace('~-.*~','',$u);if(!isset($va[$u])&&isset($cb[$u])){$Vg=$u;break;}}}Locale::create($Vg);abstract
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
openPasswordless($N,$U,$E,$vm=true){$uf=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($E!=""&&($vm||$uf)&&$this->open($N,$U,"")){$I=Admin::get()->verifyDefaultPassword($E);if($I!==true){$this->error=$I;return
false;}return
true;}return$this->open($N,$U,$E);}abstract
function
open($N,$U,$E);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($qo){return
version_compare($this->version,$qo)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($Ea){$this->affectedRows=$Ea;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($j){$this->error=$j;}abstract
function
selectDatabase($_);abstract
function
quote($P);function
formatValue($X,array$k){return$X;}abstract
function
query($G,$Pn=false);function
getQueryInfo(){return
null;}function
getResult($G,$k=0){return$this->getValue($G,$k);}function
getValue($G,$ve=0){$I=$this->query($G);if(!is_object($I))return
false;$K=$I->fetchRow();return$K?$K[$ve]:false;}function
multiQuery($G){$this->multiResult=$this->query($G);return(bool)($this->multiResult);}function
storeResult($I=null){return$this->multiResult;}function
nextResult(){return
false;}}abstract
class
Result{protected$rowsCount;function
__construct($hl){$this->rowsCount=$hl;}function
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
dsn($Bd,$U,$E,array$B=[]){$B[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new
PDO($Bd,$U,$E,$B);}catch(Exception$de){$this->error=$de->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($P){return$this->pdo->quote($P);}function
query($G,$Pn=false){$sm=$this->pdo->query($G);$this->error="";if(!$sm){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(121);return
false;}$I=new
PdoResult($sm);$this->storeResult($I);return$I;}function
storeResult($I=null){if(!$I){$I=$this->multiResult;if(!$I)return
false;}if($I->getColumnsCount())return$I;$this->affectedRows=$I->getAffectedRowsCount();return
true;}function
nextResult(){return$this->multiResult&&$this->multiResult->nextRowset();}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$sm){parent::__construct(max($sm->columnCount()?$sm->rowCount():0,0));$this->statement=$sm;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($di){$I=$this->statement->fetch($di);return$I?array_map([$this,'unresource'],$I):$I;}private
function
unresource($X){return
is_resource($X)?stream_get_contents($X):$X;}function
fetchField(){$K=$this->statement->getColumnMeta($this->offset++);if($K===false)return
false;$T=$K["pdo_type"];$K["type"]=($T==PDO::PARAM_INT?0:15);$K["charsetnr"]=($T==\PDO::PARAM_LOB||(isset($K["flags"])&&in_array("blob",(array)$K["flags"]))?63:0);return(object)$K;}function
seek($A){for($p=0;$p<$A;$p++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($q,$_,array$oe){self::$drivers[$q]=$_;self::$extensions[$q]=$oe;}static
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
create(Connection$e,$Ba){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($e,$Ba);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct(Connection$e,$Ba){$this->connection=$e;$this->admin=$Ba;}function
getTypes(){return
call_user_func_array("array_merge",array_values($this->types));}function
getStructuredTypes(){return
array_map("array_keys",$this->types);}function
setUserTypes(array$On){$this->types[lang(108)]=array_flip($On);}function
getUserTypes(){$u=lang(108);return
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
select($Q,array$M,array$Z,array$mf,array$fj=[],$w=1,$C=0,$lk=false){$wg=(count($mf)<count($M));$G="SELECT".limit(($_GET["page"]!="last"&&$w&&$mf&&$wg&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($mf&&$wg?"\nGROUP BY ".implode(", ",$mf):"").($fj?"\nORDER BY ".implode(", ",$fj):""),$w,($C?$w*$C:0),"\n");$rm=microtime(true);$J=$this->connection->query($G);if($lk)echo
Admin::get()->formatSelectQuery($G,$rm,!$J);return$J;}function
delete($Q,$yk,$w=0){$G="FROM ".table($Q);return
queries("DELETE".($w?limit1($Q,$G,$yk):" $G$yk"));}function
update($Q,array$H,$yk,$w=0,$El="\n"){$Y=[];foreach($H
as$u=>$W)$Y[]="$u = $W";$G=table($Q)." SET$El".implode(",$El",$Y);return
queries("UPDATE".($w?limit1($Q,$G,$yk,$El):" $G$yk"));}function
insert($Q,array$H){return
queries("INSERT INTO ".table($Q).($H?" (".implode(", ",array_keys($H)).")\nVALUES (".implode(", ",$H).")":" DEFAULT VALUES").$this->getInsertReturningSql($Q));}function
getInsertReturningSql($Q){return"";}function
insertUpdate($Q,array$Ck,array$F){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($G,$qn){return
null;}function
convertSearch($r,array$Z,array$k){return$r;}function
getNull(){return"NULL";}function
quoteBinary($P){return
q($P);}function
warnings(){return
null;}function
tableHelp($_,$vg=false){return
null;}function
supportsIndex(array$Nm){return!is_view($Nm);}function
getIndexAlgorithms(array$Nm){return[];}function
getInheritedTables($Q){return[];}function
getParentTables($Q){return[];}function
isPartition($Q){return
false;}function
getPartitionsInfo($Q){return[];}function
hasCStyleEscapes(){return
false;}function
engines(){return[];}function
explodeArrayValue($X,$T,&$ml){return[];}function
implodeArrayValues(array$Y,$T){return"";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Ka=[];$L=get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(DIALECT=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->connection);foreach($L
as$K){$K["null"]=($K["nullable"]=="YES");$Ka[$K["tab"]][]=$K;}return$Ka;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($N,$U,$E){mysqli_report(MYSQLI_REPORT_OFF);list($If,$ak)=host_port($N);$u=Admin::get()->getConfig()->getSslKey();$zb=Admin::get()->getConfig()->getSslCertificate();$xb=Admin::get()->getConfig()->getSslCaCertificate();$pm=$u||$zb||$xb;if($pm){$this->mysqli->ssl_set($u,$zb,$xb,null,null);$Me=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$Me=0;$oc=@$this->mysqli->real_connect(($N!=""?$If:ini_get("mysqli.default_host")),($N.$U!=""?$U:ini_get("mysqli.default_user")),($N.$U.$E!=""?$E:ini_get("mysqli.default_pw")),null,(is_numeric($ak)?(int)$ak:ini_get("mysqli.default_port")),(!is_numeric($ak)?$ak:null),$Me);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($oc){$dg=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$dg);$this->flavor=str_contains($dg,"MariaDB")?"mariadb":null;}return$oc;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($_){return$this->mysqli->select_db($_);}function
setCharset($Bb){if($this->mysqli->set_charset($Bb))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $Bb");}function
quote($P){return"'".$this->mysqli->escape_string($P)."'";}function
query($G,$Pn=false){$I=$this->mysqli->query($G);return
is_object($I)?new
MySqlResult($I):$I;}function
getQueryInfo(){return$this->mysqli->info;}function
multiQuery($G){return$this->mysqli->multi_query($G);}function
storeResult($I=null){$I=$this->mysqli->store_result();if(!$I)return
false;return
new
MySqlResult($I);}function
nextResult(){return$this->mysqli->more_results()&&$this->mysqli->next_result();}}class
MySqlResult
extends
Result{private$resource;function
__construct(mysqli_result$Tk){parent::__construct($Tk->num_rows);$this->resource=$Tk;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($A){return$this->resource->data_seek($A);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($N,$U,$E){list($If,$ak)=host_port($N);$Bd="mysql:charset=utf8".($If!=""?";host=$If":"").($ak?(is_numeric($ak)?";port=":";unix_socket=").$ak:"");$B=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];$u=Admin::get()->getConfig()->getSslKey();if($u)$B[PDO::MYSQL_ATTR_SSL_KEY]=$u;$zb=Admin::get()->getConfig()->getSslCertificate();if($zb)$B[PDO::MYSQL_ATTR_SSL_CERT]=$zb;$xb=Admin::get()->getConfig()->getSslCaCertificate();if($xb)$B[PDO::MYSQL_ATTR_SSL_CA]=$xb;$Kn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Kn!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$B[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$Kn;if(!$this->dsn($Bd,$U,$E,$B))return
false;$ro=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($ro,"MariaDB")?"mariadb":null;return
true;}function
setCharset($Bb){return(bool)$this->query("SET NAMES $Bb");}function
selectDatabase($_){return(bool)$this->query("USE ".idf_escape($_));}function
query($G,$Pn=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Pn);return
parent::query($G,$Pn);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(122)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(123)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(124)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(125)=>["enum"=>65535,"set"=>64,],lang(126)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(127)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$_h=$e->isMariaDB();if($e->isMinVersion($_h?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($_h?"10.2":"5.7.8"))$this->types[lang(124)]["json"]=4294967295;if($_h&&$e->isMinVersion("10.7")){$this->types[lang(124)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($_h&&$e->isMinVersion("10.5")){$this->types[lang(128)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(128)]["inet4"]=15;}if($e->isMinVersion($_h?"11.7":"9"))$this->types[lang(122)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($Q,array$H){return($H?parent::insert($Q,$H):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
getUnconvertFunction(array$k){if(preg_match("~binary~",$k["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($k["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($k["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
quoteBinary($P){return"X".q(bin2hex($P));}function
insertUpdate($Q,array$Ck,array$F){$d=array_keys(reset($Ck));$gk="INSERT INTO ".table($Q)." (".implode(", ",$d).") VALUES\n";$Y=[];foreach($d
as$u)$Y[$u]="$u = VALUES($u)";$Am="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=[];$v=0;foreach($Ck
as$H){$X="(".implode(", ",$H).")";if($Y&&(strlen($gk)+$v+strlen($X)+strlen($Am)>1e6)){if(!queries($gk.implode(",\n",$Y).$Am))return
false;$Y=[];$v=0;}$Y[]=$X;$v+=strlen($X)+2;}return
queries($gk.implode(",\n",$Y).$Am);}function
slowQuery($G,$qn){$_h=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($_h?"10.1.2":"5.7.8"))return
null;if($_h)return"SET STATEMENT max_statement_time=$qn FOR $G";elseif(preg_match('~^(SELECT\b)(.+)~is',$G,$y))return"$y[1] /*+ MAX_EXECUTION_TIME(".($qn*1000).") */ $y[2]";else
return
null;}function
convertSearch($r,array$Z,array$k){return(preg_match('~char|text|enum|set~',$k["type"])&&!preg_match("~^utf8~",$k["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($r USING ".charset($this->connection).")":$r);}function
warnings(){$I=$this->connection->query("SHOW WARNINGS");if($I&&$I->getRowsCount()){ob_start();print_select_result($I);return
ob_get_clean();}return
null;}function
tableHelp($_,$vg=false){$_h=$this->connection->isMariaDB();if(DB=="information_schema"){$_=strtolower($_);return$_h?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($_,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$_-table":"information-schema-".str_replace("_","-",$_)."-table.html";}if(DB=="performance_schema")return$_h?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$_-table":"performance-schema-".str_replace("_","-",$_)."-table.html";if(DB=="mysql")return$_h?"reference/system-tables/the-mysql-database-tables/mysql-$_".str_starts_with($_,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($Q){$af="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$I=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $af ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$I)return[];$dg=["partition_by"=>$I[0],"partition"=>$I[1],"partitions"=>$I[2],];$Kj=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $af AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$dg["partition_names"]=array_keys($Kj);$dg["partition_values"]=array_values($Kj);return$dg;}function
getIndexAlgorithms(array$Nm){return
preg_match('~^(MEMORY|NDB)$~',$Nm["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$vb;if($vb===null){$mm=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$vb=(strpos($mm,'NO_BACKSLASH_ESCAPES')===false);}return$vb;}function
engines(){$Td=[];foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$Td[]=$K["Engine"];}return$Td;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($r){return"`".str_replace("`","``",$r)."`";}function
table($r){return
idf_escape($r);}function
connect($F=false,&$j=null){$e=$F?MySqlConnection::create():MySqlConnection::createSecondary();list($N,$U,$E)=Admin::get()->getCredentials();if(!$e->openPasswordless($N,$U,$E,false)){$j=$e->getError();if(function_exists('iconv')&&!is_utf8($j)&&strlen($il=iconv("windows-1250","utf-8",$j))>strlen($j))$j=$il;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($F&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$N,"MariaDB");}return$e;}function
get_databases($Oe){$g=get_session("dbs");if($g===null){$G="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$g=($Oe?slow_query($G):get_vals($G));restart_session();set_session("dbs",$g);stop_session();}return$g;}function
limit($G,$Z,$w,$A=0,$El=" "){return" $G$Z".($w?$El."LIMIT $w".($A?" OFFSET $A":""):"");}function
limit1($Q,$G,$Z,$El="\n"){return
limit($G,$Z,1,0,$El);}function
db_collation($h,$Tb){$J=null;$Ac=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$Ac,$y))$J=$y[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$Ac,$y))$J=$Tb[$y[1]][-1];return$J;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables($g){$J=[];foreach($g
as$h)$J[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$J;}function
table_status($_="",$te=false){if($te)$G="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options, TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($_!=""?"AND TABLE_NAME = ".q($_):"ORDER BY Name");else$G="SHOW TABLE STATUS".($_!=""?" LIKE ".q(addcslashes($_,"%_\\")):"");$S=[];foreach(get_rows($G)as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($_!="")$K["Name"]=$_;$S[$K["Name"]]=$K;}return$S;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support($R){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$R["Engine"]);}function
fields($Q){$_h=Connection::get()->isMariaDB();$J=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$K){$k=$K["COLUMN_NAME"];$T=preg_replace('~\s?/\*.+\*/~U',"",$K["COLUMN_TYPE"]);$pe=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$pe,$ff);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$T,$Nn);$i=$_h&&$K["COLUMN_DEFAULT"]=="NULL"?null:$K["COLUMN_DEFAULT"];if($i!==null){$zg=preg_match('~(text|json)~',$Nn[1]);if(!$_h&&$zg)$i=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($i));if($_h||$zg){$i=preg_replace_callback("~^'(.*)'$~",function($z){return
stripslashes(str_replace("''","'",$z[1]));},$i);}if(!$_h&&preg_match('~binary~',$Nn[1])&&preg_match('~^0x(\w*)$~',$i,$z))$i=pack("H*",$z[1]);}$hf=$K["GENERATION_EXPRESSION"];if(!$_h)$hf=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($hf));$J[$k]=["field"=>$k,"full_type"=>$T,"type"=>$Nn[1],"length"=>$Nn[2],"unsigned"=>ltrim($Nn[3].$Nn[4]),"default"=>($ff?$hf:$i),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($pe=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$pe,$Nn)?$Nn[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$K["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($ff[1]=="PERSISTENT"?"STORED":$ff[1]),];}return$J;}function
indexes($Q,$e=null){$J=[];foreach(get_rows("SHOW INDEX FROM ".table($Q),$e)as$K){$_=$K["Key_name"];$J[$_]["type"]=($_=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$K["Index_type"])?$K["Index_type"]:"INDEX"):"UNIQUE")));$J[$_]["columns"][]=$K["Column_name"];$J[$_]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$_]["descs"][]=null;$J[$_]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($Q){static$Qj='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=[];$Cc=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if($Cc){$Ti=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($Qj) FOREIGN KEY ?\\(((?:$Qj,? ?)+)\\) REFERENCES ($Qj)(?:\\.($Qj))? \\(((?:$Qj,? ?)+)\\)(?: ON DELETE ($Ti))?(?: ON UPDATE ($Ti))?~",$Cc,$z,PREG_SET_ORDER);foreach($z
as$y){preg_match_all("~$Qj~",$y[2],$gm);preg_match_all("~$Qj~",$y[5],$en);$J[idf_unescape($y[1])]=["db"=>idf_unescape($y[4]!=""?$y[3]:$y[4]),"table"=>idf_unescape($y[4]!=""?$y[4]:$y[3]),"source"=>array_map('AdminNeo\idf_unescape',$gm[0]),"target"=>array_map('AdminNeo\idf_unescape',$en[0]),"on_delete"=>($y[6]?:"RESTRICT"),"on_update"=>($y[7]?:"RESTRICT"),];}}return$J;}function
backward_keys($Q){$G="SELECT constraint_name, table_schema, table_name, column_name, referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = ".q(DB)."
AND referenced_table_schema = ".q(DB)."
AND referenced_table_name = ".q($Q)."
ORDER BY ordinal_position";return
get_rows($G,null,"");}function
view($_){$M=Connection::get()->getValue("SHOW CREATE VIEW ".table($_),1);$rh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$M=preg_replace("~^$rh\\s+AS\\s+~isU","",$M);return["select"=>format_sql($M)];}function
collations(){$J=[];$G=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($G)as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$u=>$W)sort($J[$u]);return$J;}function
information_schema($h){return($h=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$h=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($h,$Sb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Sb?" COLLATE ".q($Sb):""));}function
drop_databases($g){$J=apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($_,$Sb){$J=false;if(create_database($_,$Sb)){$S=[];$uo=[];foreach(tables_list()as$Q=>$T){if($T=='VIEW')$uo[]=$Q;else$S[]=$Q;}$J=(!$S&&!$uo)||move_tables($S,$uo,$_);drop_databases($J?[DB]:[]);}return$J;}function
auto_increment(){$ab=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$s){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$s["columns"],true)){$ab="";break;}if($s["type"]=="PRIMARY")$ab=" UNIQUE";}}return" AUTO_INCREMENT$ab";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$b=[];foreach($l
as$k){if($k[1]){$i=$k[1][3];if(str_contains($i," GENERATED")){$k[1][3]=Connection::get()->isMariaDB()?"":$k[1][2];$k[1][2]=$i;}$b[]=($Q!=""?($k[0]!=""?"CHANGE ".idf_escape($k[0]):"ADD"):" ")." ".implode($k[1]).($Q!=""?$k[2]:"");}else$b[]="DROP ".idf_escape($k[0]);}$b=array_merge($b,$Qe);$O=($dc!==null?" COMMENT=".q($dc):"").($Sd?" ENGINE=".q($Sd):"").($Sb?" COLLATE ".q($Sb):"").($Za!=""?" AUTO_INCREMENT=$Za":"");if($Jj){$Kj=[];if($Jj["partition_by"]=='RANGE'||$Jj["partition_by"]=='LIST'){foreach($Jj["partition_names"]as$u=>$W){$X=$Jj["partition_values"][$u];$Kj[]="\n  PARTITION ".idf_escape($W)." VALUES ".($Jj["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$O
.="\nPARTITION BY {$Jj["partition_by"]}({$Jj["partition"]})";if($Kj)$O
.=" (".implode(",",$Kj)."\n)";elseif($Jj["partitions"])$O
.=" PARTITIONS ".(int)$Jj["partitions"];}elseif($Jj===null)$O
.="\nREMOVE PARTITIONING";if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)$O");if($Q!=$_)$b[]="RENAME TO ".table($_);if($O)$b[]=ltrim($O);return!$b||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b));}function
alter_indexes($Q,$b){$Ab=[];foreach($b
as$u=>$W)$Ab[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return(bool)queries("ALTER TABLE ".table($Q).implode(",",$Ab));}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($uo){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$uo)));}function
drop_tables($S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables($S,$uo,$en){$Qk=[];foreach($S
as$Q)$Qk[]=table($Q)." TO ".idf_escape($en).".".table($Q);if(!$Qk||queries("RENAME TABLE ".implode(", ",$Qk))){$ad=[];foreach($uo
as$Q)$ad[table($Q)]=view($Q);Connection::get()->selectDatabase($en);$h=idf_escape(DB);foreach($ad
as$_=>$so){if(!queries("CREATE VIEW $_ AS ".str_replace(" $h."," ",$so["select"]))||!queries("DROP VIEW $h.$_"))return
false;}return
true;}return
false;}function
copy_tables($S,$uo,$en){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$_=($en==DB?table("copy_$Q"):idf_escape($en).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $_"))||!queries("CREATE TABLE $_ LIKE ".table($Q))||!queries("INSERT INTO $_ SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$K){$Fn=$K["Trigger"];if(!queries("CREATE TRIGGER ".($en==DB?idf_escape("copy_$Fn"):idf_escape($en).".".idf_escape($Fn))." $K[Timing] $K[Event] ON $_ FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($uo
as$Q){$_=($en==DB?table("copy_$Q"):idf_escape($en).".".table($Q));$so=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $_"))||!queries("CREATE VIEW $_ AS $so[select]"))return
false;}return
true;}function
trigger($_,$Q){if($_=="")return[];$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($_));return
reset($L);}function
triggers($Q){$J=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$K)$J[$K["Trigger"]]=[$K["Timing"],$K["Event"]];return$J;}function
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
ORDER BY ORDINAL_POSITION");$J=Connection::get()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($_))->fetchAssoc();if($l&&$l[0]['field']=='')$J['returns']=array_shift($l);$J['fields']=$l;return$J;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return[];}function
routine_id($_,$K){return
idf_escape($_);}function
last_id($I){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$G){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$G);}function
found_rows(array$R,array$Z){return$R["Engine"]=="InnoDB"&&!$Z?(int)$R["Rows"]:null;}function
format_sql($G){$rh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Mg='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$G=preg_replace("~($rh)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$G);$G=preg_replace("~($rh)\\s+($Mg)~isU","$1\n$2",$G);$G=preg_replace("~($rh),~isU","$1,\n  ",$G);return$G;}function
create_sql($Q,$Za,$ym){$G=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if(!$Za)$G=preg_replace('~ AUTO_INCREMENT=\d+~','',$G);return!str_contains($G,"\n")?format_sql($G):$G;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
create_database_sql($Oc,$ym=""){$_=idf_escape($Oc);$bc="";if(str_contains($ym,"CREATE")&&($Ac=Connection::get()->getValue("SHOW CREATE DATABASE $_",1))){set_utf8mb4($Ac);if($ym=="DROP+CREATE")$bc="DROP DATABASE IF EXISTS $_;\n";$bc
.="$Ac;\n";}return$bc;}function
use_sql($Oc,$ym=""){return"USE ".idf_escape($Oc).";\n";}function
trigger_sql($Q){$km="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$K)$km
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$km;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$k){if(preg_match("~binary~",$k["type"]))return"HEX(".idf_escape($k["field"]).")";if($k["type"]=="bit")return"BIN(".idf_escape($k["field"])." + 0)";if($k["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($k["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($k["field"]).")";return
null;}function
unconvert_field(array$k,$J){if(preg_match("~binary~",$k["type"]))$J="UNHEX($J)";if($k["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if($k["type"]=="vector")$J=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($J)";if(preg_match("~geometry|point|linestring|polygon~",$k["type"])){$gk=(Connection::get()->isMinVersion("8")?"ST_":"");$J=$gk."GeomFromText($J, $gk"."SRID($k[field]))";}return$J;}function
support($ue){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion("8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').')$~',$ue);}function
kill_process($W){return
queries("KILL ".number($W));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}Drivers::add("pgsql","PostgreSQL",["PgSQL","PDO_PgSQL"]);if(isset($_GET["pgsql"])){define("AdminNeo\DRIVER","pgsql");define("AdminNeo\DIALECT","pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","PgSQL");class
PgSqlConnectionBase
extends
Connection{var$timeout=0;private$connection;private$connectionString;private$hasDefaultDatabase;function
open($N,$U,$E){$h=Admin::get()->getDatabase();set_error_handler(function($Wd,$j){if(ini_bool("html_errors"))$j=html_entity_decode(strip_tags($j));$j=preg_replace('~^[^:]*: ~','',$j);$this->error=$j;});list($If,$ak)=host_port($N);$this->connectionString=($If!=""?"host=$If ":"").($ak?"port=$ak ":"")."user='".addcslashes($U,"'\\")."' password='".addcslashes($E,"'\\")."'";$qm=Admin::get()->getConfig()->getSslMode();if($qm)$this->connectionString
.=" sslmode='$qm'";$this->connection=@pg_connect("$this->connectionString dbname='".($h!=""?addcslashes($h,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->connection&&$h!=""){$this->hasDefaultDatabase=false;$this->connection=@pg_connect("$this->connectionString dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}else$this->hasDefaultDatabase=true;restore_error_handler();if($this->connection){$ro=$this->getValue("SELECT version()");$this->flavor=str_contains($ro,"CockroachDB")?"cockroach":null;$this->version=preg_replace('~^\D*([\d.]+[-\w]*).*~',"$1",$ro);pg_set_client_encoding($this->connection,"UTF8");}return(bool)$this->connection;}function
quote($P){return(function_exists('pg_escape_literal')?pg_escape_literal($this->connection,$P):"'".pg_escape_string($this->connection,$P)."'");}function
formatValue($X,array$k){if($k["type"]=="bytea"&&$X!==null)return
pg_unescape_bytea($X);return
parent::formatValue($X,$k);}function
selectDatabase($_){if($_==Admin::get()->getDatabase())return$this->hasDefaultDatabase;$J=@pg_connect("$this->connectionString dbname='".addcslashes($_,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->connection=$J;return(bool)$J;}function
close(){$this->connection=@pg_connect("$this->connectionString dbname='postgres'");}function
query($G,$Pn=false){if(!$this->connection){$this->error="Invalid connection.";return
false;}$I=@pg_query($this->connection,$G);$this->error="";if(!$I){$this->error=pg_last_error($this->connection);$J=false;}elseif(!pg_num_fields($I)){$this->affectedRows=pg_affected_rows($I);$J=true;}else$J=new
PgSqlResult($I);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$J;}function
getValue($G,$ve=0){$I=$this->query($G);return
is_object($I)?$I->fetchValue($ve):false;}function
warnings(){if(PHP_VERSION_ID>=70100){$_o=pg_last_notice($this->connection,PGSQL_NOTICE_ALL);if(!$_o)return
null;$_o=implode("\n",$_o);pg_last_notice($this->connection,PGSQL_NOTICE_CLEAR);}else{$_o=pg_last_notice($this->connection);if(!$_o)return
null;}return
nl2br(h($_o));}function
copyFrom($Q,array$L){$this->error="";set_error_handler(function($Wd,$j){$this->error=ini_bool("html_errors")?html_entity_decode($j):$j;});$I=pg_copy_from($this->connection,$Q,$L);restore_error_handler();return$I;}}class
PgSqlResult
extends
Result{private$resource;private$offset=0;function
__construct($Tk){parent::__construct(pg_num_rows($Tk));$this->resource=$Tk;}function
fetchAssoc(){return
pg_fetch_assoc($this->resource);}function
fetchRow(){return
pg_fetch_row($this->resource);}function
fetchValue($ve){return$this->getRowsCount()?pg_fetch_result($this->resource,0,$ve):false;}function
fetchField(){$c=$this->offset++;$kj=pg_field_table($this->resource,$c);if($kj===false)return
false;$_=pg_field_name($this->resource,$c);if($_===false)return
false;$T=pg_field_type($this->resource,$c);if($T===false)return
false;return(object)['orgtable'=>$kj,'name'=>$_,'type'=>(preg_match(number_type(),$T)?0:15),'charsetnr'=>($T=="bytea"?63:0),];}}}elseif(extension_loaded("pdo_pgsql")){define("AdminNeo\DRIVER_EXTENSION","PDO_PgSQL");class
PgSqlConnectionBase
extends
PdoConnection{var$timeout=0;function
open($N,$U,$E){$h=Admin::get()->getDatabase();list($If,$ak)=host_port($N);$Bd="pgsql:".($If!=""?"host=$If ":"").($ak?"port=$ak ":"")."client_encoding=utf8 dbname='".($h!=""?addcslashes($h,"'\\"):"postgres")."'";$qm=Admin::get()->getConfig()->getSslMode();if($qm)$Bd
.=" sslmode='$qm'";if(!$this->dsn($Bd,$U,$E))return
false;$ro=$this->getValue("SELECT version()");$this->flavor=str_contains($ro,"CockroachDB")?"cockroach":null;return
true;}function
selectDatabase($_){return
Admin::get()->getDatabase()==$_;}function
query($G,$Pn=false){$J=parent::query($G,$Pn);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$J;}function
warnings(){return
null;}function
copyFrom($Q,array$L){$I=$this->pdo->pgsqlCopyFromArray($Q,$L);$Xd=$this->pdo->errorInfo();$this->error=isset($Xd[2])?$Xd[2]:"";return$I;}function
close(){}}}if(class_exists('AdminNeo\PgSqlConnectionBase')){class
PgSqlConnection
extends
PgSqlConnectionBase{private$localhostServer=false;function
open($N,$U,$E){if($N=="localhost")$this->localhostServer=true;return
parent::open($N,$U,$E);}function
getDefaultServerName(){return$this->localhostServer?"localhost":"";}function
multiQuery($G){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$G),$z)){$L=explode("\n",$z[2]);$this->affectedRows=count($L);return$this->copyFrom($z[1],$L);}return
parent::multiQuery($G);}}}class
PgSqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(122)=>["smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20,],lang(123)=>["date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0,],lang(124)=>["character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0,],lang(126)=>["bit"=>0,"bit varying"=>0,"bytea"=>0,],lang(128)=>["cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0,],lang(127)=>["box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0,],];if($e->isMinVersion("9.2")){$this->types[lang(124)]["json"]=4294967295;if($e->isMinVersion("9.4"))$this->types[lang(124)]["jsonb"]=4294967295;}if($e->isMinVersion("12")){$this->generated[]="STORED";if($e->isMinVersion("18"))$this->generated[]="VIRTUAL";}$this->operators=["=","<",">","<=",">=","!=","~","~*","!~","!~*","LIKE","LIKE %%","NOT LIKE","ILIKE","ILIKE %%","NOT ILIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["char_length","lower","upper","round","to_hex","to_timestamp",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->partitionBy=["RANGE","LIST"];if(!$e->isCockroachDB())$this->partitionBy[]="HASH";$this->insertFunctions=["char"=>"md5","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",];$this->systemDatabases=["template1"];$this->systemSchemas=["information_schema","pg_catalog","pg_toast","pg_temp_*","pg_toast_temp_*"];}function
getNsOidSql(){return"(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";}function
getInsertReturningSql($Q){$Ya=array_filter(fields($Q),function($k){return$k['auto_increment'];});return
count($Ya)==1?" RETURNING ".idf_escape(key($Ya)):"";}function
insertUpdate($Q,array$Ck,array$F){foreach($Ck
as$H){$Yn=[];$Z=[];foreach($H
as$u=>$W){$Yn[]="$u = $W";if(isset($F[idf_unescape($u)]))$Z[]="$u = $W";}if(!(($Z&&queries("UPDATE ".table($Q)." SET ".implode(", ",$Yn)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($H)).") VALUES (".implode(", ",$H).")")))return
false;}return
true;}function
slowQuery($G,$qn){$this->connection->query("SET statement_timeout = ".(1000*$qn));$this->connection->timeout=1000*$qn;return$G;}function
convertSearch($r,array$Z,array$k){$kn="char|text";if(strpos($Z["op"],"LIKE")===false)$kn
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$kn~",$k["type"])?$r:"CAST($r AS text)");}function
quoteBinary($P){return"'\\x".bin2hex($P)."'";}function
warnings(){return$this->connection->warnings();}function
tableHelp($_,$vg=false){$lh=["information_schema"=>"infoschema","pg_catalog"=>($vg?"view":"catalog"),];$x=$lh[$_GET["ns"]];if($x)return"$x-".str_replace("_","-",$_).".html";return
null;}function
getInheritedTables($Q){return
get_rows("SELECT relname AS \"table\", nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($Q).(Connection::get()->isMinVersion("10")?" AND relispartition::int = 0":"")." ORDER BY 2, 1");}function
getParentTables($Q){return
get_rows("SELECT relname AS \"table\", nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
isPartition($Q){return
Connection::get()->isMinVersion("10")&&$this->connection->getValue("SELECT relispartition::int FROM pg_class WHERE oid = ".$this->tableOid($Q));}function
getPartitionsInfo($Q){if(!$this->connection->isMinVersion("10"))return[];$K=$this->connection->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($Q))->fetchAssoc();if(!$K)return[];$Va=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = {$K["partrelid"]} AND attnum IN (".str_replace(" ",", ",$K["partattrs"]).")");$tb=['h'=>'HASH','l'=>'LIST','r'=>'RANGE'];$dg=["partition_by"=>$tb[$K["partstrat"]],"partition"=>implode(", ",array_map('AdminNeo\idf_escape',$Va)),];$Kj=get_key_vals("SELECT relname, pg_get_expr(c.relpartbound, c.oid) AS bound FROM pg_inherits JOIN pg_class c ON inhrelid = oid WHERE inhparent = ".$this->tableOid($Q)." ORDER BY 1");$dg["partition_names"]=array_keys($Kj);$dg["partition_values"]=array_map(function($X){return
str_replace("FOR VALUES ","",$X);},array_values($Kj));return$dg;}function
tableOid($Q){return"(SELECT oid FROM pg_class WHERE relnamespace = ".$this->getNsOidSql()." AND relname = ".q($Q)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
getIndexAlgorithms(array$Nm){static$ai=[];if(!$ai)$ai=get_vals("SELECT amname FROM pg_am".($this->connection->isMinVersion("9.6")?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->connection->isCockroachDB()?"prefix":"btree")."' DESC, amname");return$ai;}function
supportsIndex(array$Nm){return$Nm["Engine"]!="view";}function
hasCStyleEscapes(){static$vb;if($vb===null)$vb=($this->connection->getValue("SHOW standard_conforming_strings")=="off");return$vb;}function
explodeArrayValue($X,$T,&$ml){$oo=["oidvector"=>"oid","int2vector"=>"smallint",];$ml=isset($oo[$T])?$oo[$T]:null;if($ml)return
explode(" ",$X);if(preg_match('~^(.+)\[]$~',$T,$z)){$ml=$z[1];$X=preg_replace('~^\{(.*)}$~',"$1",$X);$tg=str_contains($ml,"json");if($tg){$Y=explode('","',trim($X,'"'));array_walk($Y,function(&$V){$V=str_replace('\"','"',$V);});}elseif(preg_match('~^(\{+)(.*)(}+)$~',$X,$z)){$Y=explode("$z[3],$z[1]",$z[2]);array_walk($Y,function(&$V)use($z){$V=$z[1].$V.$z[3];});}else$Y=explode(",",$X);if(!$tg&&$Y[0][0]=="{"){$T=$ml;array_walk($Y,function(&$V)use($T,&$ml){$V=$this->explodeArrayValue($V,$T,$ml);});}elseif(!$tg&&isset($this->types[lang(124)][$ml])){array_walk($Y,function(&$V){$V=preg_replace('~^\'(.*)\'$~',"$1",$V);});}return$Y;}return[];}function
implodeArrayValues(array$Y,$T){if($T=="oidvector"||$T=="int2vector")return
implode(" ",$Y);if(preg_match('~^([^[]+)(\[])+$~',$T,$z)){$ug=isset($this->types[lang(124)][$z[1]]);$tg=str_contains($T,"json");array_walk($Y,function(&$V)use($T,$ug,$tg){if(is_array($V))$V=$this->implodeArrayValues($V,$T);elseif($tg)$V="\"$V\"";elseif($ug)$V="'$V'";});return"{".implode(",",$Y)."}";}return"";}}function
create_driver(Connection$e){return
PgSqlDriver::create($e,Admin::get());}function
idf_escape($r){return'"'.str_replace('"','""',$r).'"';}function
table($r){return
idf_escape($r);}function
connect($F=false,&$j=null){$e=$F?PgSqlConnection::create():PgSqlConnection::createSecondary();list($N,$U,$E)=Admin::get()->getCredentials();$I=$e->openPasswordless($N,$U,$E,false);$Xd=[];if(!$I&&$N==""&&preg_match('~connection to server on socket .+ failed: No such file or directory~U',$e->getError())){$Xd[]=$e->getError();$N="localhost";$I=$e->openPasswordless($N,$U,$E,false);}if(!$I){$Xd[]=$e->getError();$j=implode("\n",$Xd);return
null;}if($e->isMinVersion("9"))$e->query("SET application_name = 'AdminNeo'");if($F&&$e->isCockroachDB()){Drivers::setName(DRIVER,"CockroachDB");save_driver_name(DRIVER,$N,"CockroachDB");}return$e;}function
get_databases($Oe){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($G,$Z,$w,$A=0,$El=" "){return" $G$Z".($w?$El."LIMIT $w".($A?" OFFSET $A":""):"");}function
limit1($Q,$G,$Z,$El="\n"){return(preg_match('~^INTO~',$G)?limit($G,$Z,1,0,$El):" $G".(is_view(table_status1($Q))?$Z:$El."WHERE ctid = (SELECT ctid FROM ".table($Q).$Z.$El."LIMIT 1)"));}function
db_collation($h,$Tb){return
Connection::get()->getValue("SELECT datcollate FROM pg_database WHERE datname = ".q($h));}function
logged_user(){return
Connection::get()->getValue("SELECT user");}function
tables_list(){$G="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$G
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$G
.="
ORDER BY 1";return
get_key_vals($G);}function
count_tables($g){$J=[];foreach($g
as$h){if(Connection::get()->selectDatabase($h))$J[$h]=count(tables_list());}return$J;}function
table_status($_=""){static$xf;if($xf===null)$xf=Connection::get()->getValue("SELECT 'pg_table_size'::regproc");$J=[];foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\",
	CASE relkind WHEN 'p' THEN 'partitioned' ELSE '' END AS \"Create_options\"".($xf?",
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
".($_!=""?"AND relname = ".q($_):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view(array$R){return
in_array($R["Engine"],["view","materialized view"]);}function
fk_support($R){return
true;}function
fields($Q){$J=[];$Ja=['timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',];foreach(get_rows("SELECT
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
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$y);list(,$T,$v,$K["length"],$Aa,$Sa)=$y;$Fb=$T.$Aa;if(isset($Ja[$Fb])){$K["type"]=$Ja[$Fb];$K["full_type"]=$K["type"].$v;}else{$K["type"]=$T;$K["full_type"]=$K["type"].$v.$Aa;}for($p=0;$p<$K["attndims"];$p++){$K["length"].=$Sa;$K["full_type"].=$Sa;}if(in_array($K['attidentity'],['a','d']))$K['default']='GENERATED '.($K['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$B=["s"=>"STORED","v"=>"VIRTUAL"];$K["generated"]=(isset($B[$K["attgenerated"]])?$B[$K["attgenerated"]]:"");$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['attidentity']||preg_match('~^nextval\(~i',$K["default"])||preg_match('~^unique_rowid\(~',$K["default"]);$K["privileges"]=["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1];if(!$K['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$K["default"],$y))$K["default"]=($y[1]=="NULL"?null:idf_unescape($y[1]).$y[2]);$J[$K["field"]]=$K;}return$J;}function
indexes($Q,$e=null){if(!is_object($e))$e=Connection::get();$J=[];$Tm=Driver::get()->tableOid($Q);$d=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Tm AND attnum > 0",$e);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname, pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Tm
ORDER BY indisprimary DESC, indisunique DESC",$e)as$K){$Mk=$K["relname"];$J[$Mk]["type"]=($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX"));$J[$Mk]["columns"]=[];$J[$Mk]["descs"]=[];$J[$Mk]["algorithm"]=$K["amname"];$J[$Mk]["partial"]=$K["partial"];$ag=preg_split('~(?<=\)), (?=\()~',$K["indexpr"]);foreach(explode(" ",$K["indkey"])as$bg)$J[$Mk]["columns"][]=($bg?$d[$bg]:array_shift($ag));foreach(explode(" ",$K["indoption"])as$cg)$J[$Mk]["descs"][]=(intval($cg)&1?'1':null);$J[$Mk]["lengths"]=[];}return$J;}function
foreign_keys($Q){$Ti=implode("|",Driver::get()->getOnActions());$J=[];foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".Driver::get()->tableOid($Q)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){$K['deferrable']=($K['deferrable']?'':'NOT ').'DEFERRABLE'.($K['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$y)){$K['source']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$y[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$y[2],$Bh)){$K['ns']=idf_unescape($Bh[2]);$K['table']=idf_unescape($Bh[4]);}$K['target']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$y[3])));$K['on_delete']=(preg_match("~ON DELETE ($Ti)~",$y[4],$Bh)?$Bh[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE ($Ti)~",$y[4],$Bh)?$Bh[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
backward_keys($Q){$G="SELECT s.constraint_name, s.table_schema, s.table_name, s.column_name, t.column_name AS referenced_column_name
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
get_rows($G,null,"");}function
view($_){return["select"=>trim(Connection::get()->getValue("SELECT pg_get_viewdef(".Driver::get()->tableOid($_).")"))];}function
collations(){return[];}function
information_schema($h){return
get_schema()=="information_schema";}function
error(){$J=h(Connection::get()->getError());if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$y))$J=$y[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($y[3]).'})(.*)~','\1<b>\2</b>',$y[2]).$y[4];return
nl2br($J);}function
create_database($h,$Sb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Sb?" ENCODING ".idf_escape($Sb):""));}function
drop_databases($g){Connection::get()->close();return
apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');}function
rename_database($_,$Sb){Connection::get()->close();return(bool)queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($_));}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$b=[];$vk=[];if($Q!=""&&$Q!=$_)$vk[]="ALTER TABLE ".table($Q)." RENAME TO ".table($_);$Fl="";foreach($l
as$k){$c=idf_escape($k[0]);$W=$k[1];if(!$W)$b[]="DROP $c";else{$lo=$W[5];unset($W[5]);if($k[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($Q!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($Q!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($c!=$W[0])$vk[]="ALTER TABLE ".table($_)." RENAME $c TO $W[0]";$b[]="ALTER $c TYPE$W[1]";$Gl=$Q."_".idf_unescape($W[0])."_seq";$b[]="ALTER $c ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($Gl).")":"DROP DEFAULT"));if(isset($W[6]))$Fl="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Gl)." OWNED BY ".idf_escape($Q).".$W[0]";$b[]="ALTER $c ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($k[0]!=""||$lo!="")$vk[]="COMMENT ON COLUMN ".table($_).".$W[0] IS ".($lo!=""?substr($lo,9):"''");}}$b=array_merge($b,$Qe);if($Q==""){$O="";if($Jj){$O=" PARTITION BY {$Jj["partition_by"]}({$Jj["partition"]})";if($Jj["partition_by"]=='HASH'){$Kj=(int)$Jj["partitions"];for($p=0;$p<$Kj;$p++)$vk[]="CREATE TABLE ".idf_escape($_."_$p")." PARTITION OF ".idf_escape($_)." FOR VALUES WITH (MODULUS $Kj, REMAINDER $p)";}else{$Ob=Connection::get()->isCockroachDB();$jk="MINVALUE";foreach($Jj["partition_names"]as$p=>$W){$X=$Jj["partition_values"][$p];$Ej=" VALUES ".($Jj["partition_by"]=='LIST'?"IN ($X)":"FROM ($jk) TO ($X)");if($Ob)$O
.=($p?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W)).$Ej;else$vk[]="CREATE TABLE ".idf_escape($_."_$W")." PARTITION OF ".idf_escape($_)." FOR$Ej";$jk=$X;}$O
.=$Ob?"\n)":"";}}array_unshift($vk,"CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)$O");}elseif($b)array_unshift($vk,"ALTER TABLE ".table($Q)."\n".implode(",\n",$b));if($Fl)array_unshift($vk,$Fl);if($dc!==null)$vk[]="COMMENT ON TABLE ".table($_)." IS ".q($dc);if($Za!="");foreach($vk
as$G){if(!queries($G))return
false;}return
true;}function
alter_indexes($Q,$b){$Ac=[];$xd=[];$vk=[];foreach($b
as$W){if($W[0]!="INDEX")$Ac[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$xd[]=idf_escape($W[1]);else$vk[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($Ac)array_unshift($vk,"ALTER TABLE ".table($Q).implode(",",$Ac));if($xd)array_unshift($vk,"DROP INDEX ".implode(", ",$xd));foreach($vk
as$G){if(!queries($G))return
false;}return
true;}function
truncate_tables($S){return(bool)queries("TRUNCATE ".implode(", ",array_map('AdminNeo\table',$S)));}function
drop_views($uo){return
drop_tables($uo);}function
drop_tables($S){foreach($S
as$Q){$O=table_status1($Q);if(!queries("DROP ".strtoupper($O["Engine"])." ".table($Q)))return
false;}return
true;}function
move_tables($S,$uo,$en){foreach(array_merge($S,$uo)as$Q){$O=table_status1($Q);if(!queries("ALTER ".strtoupper($O["Engine"])." ".table($Q)." SET SCHEMA ".idf_escape($en)))return
false;}return
true;}function
trigger($_,$Q){if($_=="")return["Statement"=>"EXECUTE PROCEDURE ()"];$d=[];$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q)." AND trigger_name = ".q($_);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$K)$d[]=$K["event_object_column"];$Fn=[];foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement" FROM information_schema.triggers '."$Z ORDER BY event_manipulation DESC")as$K){if($d&&$K["Event"]=="UPDATE")$K["Event"].=" OF";$K["Of"]=implode(", ",$d);if($Fn)$K["Event"].=" OR $Fn[Event]";$Fn=$K;}return$Fn;}function
triggers($Q){$J=[];foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q))as$K){$Fn=trigger($K["trigger_name"],$Q);$J[$Fn["Trigger"]]=[$Fn["Timing"],$Fn["Event"]];}return$J;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF"],"Type"=>["FOR EACH ROW","FOR EACH STATEMENT"],];}function
routine($_,$T){$dg=get_rows('SELECT routine_definition, LOWER(external_language) AS language, type_udt_name
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($_));$dg=isset($dg[0])?$dg[0]:[];$l=get_rows('SELECT COALESCE(parameter_name, ordinal_position::text) AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($_).'
ORDER BY ordinal_position');return["fields"=>$l,"returns"=>["type"=>isset($dg["type_udt_name"])?$dg["type_udt_name"]:null],"definition"=>isset($dg["routine_definition"])?$dg["routine_definition"]:null,"language"=>isset($dg["language"])?$dg["language"]:null,"comment"=>null,];}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_name AS "ROUTINE_NAME", routine_type AS "ROUTINE_TYPE", type_udt_name AS "DTD_IDENTIFIER", null AS ROUTINE_COMMENT
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($_,$K){$J=[];foreach($K["fields"]as$k){$v=$k["length"];$J[]=$k["type"].($v?"($v)":"");}return
idf_escape($_)."(".implode(", ",$J).")";}function
last_id($I){$K=$I
instanceof
Result?$I->fetchRow():[];return$K?$K[0]:0;}function
explain(Connection$e,$G){return$e->query("EXPLAIN $G");}function
found_rows(array$R,array$Z){if(preg_match("~ rows=([0-9]+)~",Connection::get()->getValue("EXPLAIN SELECT * FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Lk))return(int)$Lk[1];return
null;}function
types(){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".Driver::get()->getNsOidSql()."
AND typtype IN ('b','d','e')
AND typelem = 0");}function
type_values($q){$Vd=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $q ORDER BY enumsortorder");if(!$Vd)return"";return"'".implode("', '",array_map('addslashes',$Vd))."'";}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
Connection::get()->getValue("SELECT current_schema()");}function
set_schema($ql,$e=null){if(!$e)$e=Connection::get();$I=(bool)$e->query("SET search_path TO ".idf_escape($ql));Driver::get()->setUserTypes(types());return$I;}function
foreign_keys_sql($Q){$J="";$O=table_status1($Q);$Bi=idf_escape($O['nspname']);$Le=foreign_keys($Q);ksort($Le);foreach($Le
as$Ke=>$Je)$J
.="ALTER TABLE ONLY $Bi.".idf_escape($O['Name'])." ADD CONSTRAINT ".idf_escape($Ke)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$Bi.\\2",$Je["definition"]).";\n";return($J?"$J\n":$J);}function
foreign_key_checks_sql($Od){return"SET session_replication_role = ".($Od?"DEFAULT":"replica").";\n";}function
restart_sequences_sql($Q){$J="";foreach(fields($Q)as$k){if(!$k["auto_increment"]||preg_match('~^unique_rowid\(~',$k["default"]))continue;$Fl="pg_get_serial_sequence(".q(table($Q)).", ".q($k["field"]).")";$c=idf_escape($k["field"]);$J
.="SELECT setval($Fl, MAX($c)) FROM ".table($Q)." HAVING MAX($c) IS NOT NULL;\n";}return$J;}function
create_sql($Q,$Za,$ym){$Yk=[];$Hl=[];$O=table_status1($Q);$Bi=idf_escape($O['nspname']);if(is_view($O)){$so=view($Q);return
rtrim("CREATE VIEW $Bi.".idf_escape($Q)." AS $so[select]",";");}$l=fields($Q);if(count($O)<2||empty($l))return
false;$J="CREATE TABLE $Bi.".idf_escape($O['Name'])." (\n    ";foreach($l
as$k){if($k['default']=="nextval('$O[Name]_$k[field]_seq')"){$k['default']=null;$k['full_type']=preg_replace('~int(eger)?~','serial',$k['full_type']);}$Cj=idf_escape($k['field']).' '.$k['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$O['nspname']).'.\2',default_value($k)).($k['null']?"":" NOT NULL");$Yk[]=$Cj;if(preg_match('~nextval\(\'([^\']+)\'\)~',$k['default'],$z)){$Gl=$z[1];$L=get_rows((Connection::get()->isMinVersion("10")?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Gl)):"SELECT * FROM $Gl"),null,"-- ");if($L){$jm=first($L);$Hl[]=($ym=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Bi.$Gl;\n":"")."CREATE SEQUENCE $Bi.$Gl INCREMENT $jm[increment_by] MINVALUE $jm[min_value] MAXVALUE $jm[max_value]".($Za&&$jm['last_value']?" START ".($jm["last_value"]+1):"")." CACHE $jm[cache_value];";}}}if(!empty($Hl))$J=implode("\n\n",$Hl)."\n\n$J";$F="";foreach(indexes($Q)as$Yf=>$s){if($s['type']=='PRIMARY'){$F=$Yf;$Yk[]="CONSTRAINT ".idf_escape($Yf)." PRIMARY KEY (".implode(', ',array_map('AdminNeo\idf_escape',$s['columns'])).")";}}foreach(Driver::get()->checkConstraints($Q)as$nc=>$sc)$Yk[]="CONSTRAINT ".idf_escape($nc)." CHECK ($sc)";$J
.=implode(",\n    ",$Yk)."\n)";$Ej=Driver::get()->getPartitionsInfo($O['Name']);if($Ej)$J
.="\nPARTITION BY {$Ej["partition_by"]}({$Ej["partition"]})";$J
.="\nWITH (oids = ".($O['Oid']?'true':'false').");";if($O['Comment'])$J
.="\n\nCOMMENT ON TABLE $Bi.".idf_escape($O['Name'])." IS ".q($O['Comment']).";";foreach($l
as$ye=>$k){if($k['comment'])$J
.="\n\nCOMMENT ON COLUMN $Bi.".idf_escape($O['Name']).".".idf_escape($ye)." IS ".q($k['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($Q).($F?" AND indexname != ".q($F):""),null,"-- ")as$K)$J
.="\n\n$K[indexdef];";return
rtrim($J,';');}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
trigger_sql($Q){$O=table_status1($Q);$km="";foreach(triggers($Q)as$En=>$Dn){$Fn=trigger($En,$O['Name']);$km
.="\nCREATE TRIGGER ".idf_escape($Fn['Trigger'])." $Fn[Timing] $Fn[Event] ON ".idf_escape($O["nspname"]).".".idf_escape($O['Name'])." $Fn[Type] $Fn[Statement];;\n";}return$km;}function
create_database_sql($Oc,$ym=""){$_=idf_escape($Oc);$bc="";if(str_contains($ym,"CREATE")){if($ym=="DROP+CREATE")$bc="DROP DATABASE IF EXISTS $_;\n";$bc
.="CREATE DATABASE $_;\n";}return$bc;}function
use_sql($Oc){return'\connect '.idf_escape($Oc).";\n";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(Connection::get()->isMinVersion("9.2")?"pid":"procpid"));}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){return$J;}function
support($ue){if($ue=="processlist")return!Connection::get()->isCockroachDB();elseif($ue=="materializedview")return
Connection::get()->isMinVersion("9.3");elseif($ue=="procedure")return
Connection::get()->isMinVersion("11");return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view)$~',$ue);}function
kill_process($W){return
queries("SELECT pg_terminate_backend(".number($W).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return(int)Connection::get()->getValue("SHOW max_connections");}}Drivers::add("mssql","MS SQL",["SQLSRV","PDO_SQLSRV","PDO_DBLIB"]);if(isset($_GET["mssql"])){define("AdminNeo\DRIVER","mssql");define("AdminNeo\DIALECT","mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"&&$_GET["ext"]!="dblib"){define("AdminNeo\DRIVER_EXTENSION","sqlsrv");class
MsSqlConnection
extends
Connection{private$connection;protected$multiResult;function
getDefaultServerName(){return"localhost:1433";}function
open($N,$U,$E){$qc=["UID"=>$U,"PWD"=>$E,"CharacterSet"=>"UTF-8",];$Qd=Admin::get()->getConfig()->getSslEncrypt();if($Qd!==null)$qc["Encrypt"]=$Qd;$Jn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Jn!==null)$qc["TrustServerCertificate"]=$Jn;$h=Admin::get()->getDatabase();if($h!="")$qc["Database"]=$h;$this->connection=@sqlsrv_connect(implode(",",host_port($N)),$qc);if($this->connection){$dg=sqlsrv_server_info($this->connection);$this->version=$dg['SQLServerVersion'];}else$this->resolveError();return(bool)$this->connection;}private
function
resolveError(){$this->error="";foreach(sqlsrv_errors()as$j){$this->errno=$j["code"];$this->error
.="$j[message]\n";}$this->error=rtrim($this->error);}function
quote($P){return(contains_unicode($P)?"N":"")."'".str_replace("'","''",$P)."'";}function
selectDatabase($_){return(bool)$this->query(use_sql($_));}function
query($G,$Pn=false){$I=sqlsrv_query($this->connection,$G);$this->error="";if(!$I){$this->resolveError();return
false;}return$this->storeResult($I);}function
multiQuery($G){$this->multiResult=sqlsrv_query($this->connection,$G);$this->error="";if(!$this->multiResult){$this->resolveError();return
false;}return
true;}function
storeResult($I=null){if(!$I){$I=$this->multiResult;if(!$I)return
false;}if(sqlsrv_field_metadata($I))return
new
MsSqlResult($I);$this->affectedRows=sqlsrv_rows_affected($I);return
true;}function
nextResult(){return$this->multiResult&&sqlsrv_next_result($this->multiResult);}}class
MsSqlResult
extends
Result{private$resource;private$fields=false;private$offset=0;function
__construct($Tk){parent::__construct(0);$this->resource=$Tk;}function
fetchAssoc(){return$this->convertRow(sqlsrv_fetch_array($this->resource,SQLSRV_FETCH_ASSOC));}function
fetchRow(){return$this->convertRow(sqlsrv_fetch_array($this->resource,SQLSRV_FETCH_NUMERIC));}private
function
convertRow($K){if(is_array($K)){foreach($K
as$u=>$W){if(is_a($W,'DateTime'))$K[$u]=$W->format("Y-m-d H:i:s");}}return$K;}function
fetchField(){if(!$this->fields){$this->fields=sqlsrv_field_metadata($this->resource);if(!$this->fields)return
false;}$k=$this->fields[$this->offset++];return(object)['name'=>$k["Name"],'type'=>($k["Type"]==1?254:15),'charsetnr'=>0,];}function
seek($A){for($p=0;$p<$A;$p++){if(!sqlsrv_fetch($this->resource))return
false;}return
true;}}function
last_id($I){return
Connection::get()->getValue("SELECT SCOPE_IDENTITY()");}function
explain(Connection$e,$G){$e->query("SET SHOWPLAN_ALL ON");$J=$e->query($G);$e->query("SET SHOWPLAN_ALL OFF");return$J;}}else{abstract
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
open($N,$U,$E){$B=[];$Qd=Admin::get()->getConfig()->getSslEncrypt();if($Qd!==null)$B[]="Encrypt=$Qd";$Jn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Jn!==null)$B[]="TrustServerCertificate=$Jn";$cj=$B?(";".implode(";",$B)):"";return$this->dsn("sqlsrv:Server=".implode(",",host_port($N)).$cj,$U,$E);}}}elseif(extension_loaded("pdo_dblib")){define("AdminNeo\DRIVER_EXTENSION","PDO_DBLIB");class
MsSqlConnection
extends
MsSqlPdoConnection{function
open($N,$U,$E){list($If,$ak)=host_port($N);$I=$this->dsn("dblib:charset=utf8;host=$If".($ak?(is_numeric($ak)?";port=":";unix_socket=").$ak:""),$U,$E);if($I)$this->query("SET ANSI_NULLS ON; SET ANSI_PADDING ON; SET CONCAT_NULL_YIELDS_NULL ON; SET ANSI_WARNINGS ON;");return$I;}}}function
last_id($I){$e=Connection::get();return$e->lastInsertId();}function
explain(Connection$e,$G){}}class
MsSqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(122)=>["tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20,],lang(123)=>["date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10,],lang(124)=>["char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823,],lang(126)=>["binary"=>8000,"varbinary"=>8000,"image"=>2147483647,],];$this->generated=["PERSISTED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL",];$this->functions=["len","lower","upper","round",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->onActions=["CASCADE","SET NULL","SET DEFAULT","NO ACTION"];$this->insertFunctions=["date|time"=>"getdate"];$this->editFunctions=["int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",];$this->systemSchemas=["INFORMATION_SCHEMA","guest","sys","db_*"];}function
insertUpdate($Q,array$Ck,array$F){$l=fields($Q);$Yn=[];$Z=[];$H=reset($Ck);$d="c".implode(", c",range(1,count($H)));$ub=0;$ig=[];foreach($H
as$u=>$W){$ub++;$_=idf_unescape($u);if(!$l[$_]["auto_increment"])$ig[$u]="c$ub";if(isset($F[$_]))$Z[]="$u = c$ub";else$Yn[]="$u = c$ub";}$Y=[];foreach($Ck
as$H)$Y[]="(".implode(", ",$H).")";if($Z){$Of=queries("SET IDENTITY_INSERT ".table($Q)." ON");$J=queries("MERGE ".table($Q)." USING (VALUES\n\t".implode(",\n\t",$Y)."\n) AS source ($d) ON ".implode(" AND ",$Z).($Yn?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$Yn):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Of?$H:$ig)).") VALUES (".($Of?$d:implode(", ",$ig)).");");if($Of)queries("SET IDENTITY_INSERT ".table($Q)." OFF");}else$J=queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($H)).") VALUES\n".implode(",\n",$Y));return$J;}function
begin(){return
queries("BEGIN TRANSACTION");}function
tableHelp($_,$vg=false){$lh=["sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",];$x=$lh[get_schema()];if($x)return"relational-databases/system-$x".preg_replace('~_~','-',strtolower($_))."-transact-sql";return
null;}}function
create_driver(Connection$e){return
MsSqlDriver::create($e,Admin::get());}function
contains_unicode($P){return
strlen($P)!=strlen(utf8_decode($P));}function
idf_escape($r){return"[".str_replace("]","]]",$r)."]";}function
table($r){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($r);}function
connect($F=false,&$j=null){$e=$F?MsSqlConnection::create():MsSqlConnection::createSecondary();$Ec=Admin::get()->getCredentials();if($Ec[0]=="")$Ec[0]="localhost:1433";if(!$e->open($Ec[0],$Ec[1],$Ec[2])){$j=$e->getError();return
null;}return$e;}function
get_databases($Oe){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb') ORDER BY name");}function
limit($G,$Z,$w,$A=0,$El=" "){return($w?" TOP (".($w+$A).")":"")." $G$Z";}function
limit1($Q,$G,$Z,$El="\n"){return
limit($G,$Z,1,0,$El);}function
db_collation($h,$Tb){return
Connection::get()->getValue("SELECT collation_name FROM sys.databases WHERE name = ".q($h));}function
logged_user(){return
Connection::get()->getValue("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($g){$J=[];foreach($g
as$h){Connection::get()->selectDatabase($h);$J[$h]=Connection::get()->getValue("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($_=""){$J=[];foreach(get_rows("SELECT ao.name AS Name, ao.type_desc AS Engine, (SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment FROM sys.all_objects AS ao WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($_!=""?"AND name = ".q($_):"ORDER BY name"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view(array$R){return$R["Engine"]=="VIEW";}function
fk_support($R){return
true;}function
fields($Q){$fc=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($Q).", 'column', NULL)");$J=[];$Pm=Connection::get()->getValue("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($Q));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($Pm))as$K){$T=$K["type"];$v=(preg_match("~char|binary~",$T)?intval($K["max_length"])/($T[0]=='n'?2:1):($T=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=["field"=>$K["name"],"full_type"=>$T.($v?"($v)":""),"type"=>$T,"length"=>$v,"default"=>(preg_match("~^\('(.*)'\)$~",$K["default"],$y)?str_replace("''","'",$y[1]):$K["default"]),"default_constraint"=>$K["default_constraint"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1],"primary"=>$K["is_primary_key"],"comment"=>$fc[$K["name"]],];}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($Pm))as$K){$J[$K["name"]]["generated"]=($K["is_persisted"]?"PERSISTED":"VIRTUAL");$J[$K["name"]]["default"]=$K["definition"];}return$J;}function
indexes($Q,$e=null){$J=[];foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($Q),$e)as$K){$_=$K["name"];$J[$_]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$_]["lengths"]=[];$J[$_]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$_]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($_){return["select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',Connection::get()->getValue("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($_)))];}function
collations(){$J=[];foreach(get_vals("SELECT name FROM fn_helpcollations()")as$Sb)$J[preg_replace('~_.*~','',$Sb)][]=$Sb;return$J;}function
information_schema($h){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
nl2br(h(preg_replace('~^(\[[^]]*])+~m','',Connection::get()->getError())));}function
create_database($h,$Sb){return(bool)queries("CREATE DATABASE ".idf_escape($h).(preg_match('~^[a-z0-9_]+$~i',$Sb)?" COLLATE $Sb":""));}function
drop_databases($g){return(bool)queries("DROP DATABASE ".implode(", ",array_map('AdminNeo\idf_escape',$g)));}function
rename_database($_,$Sb){if(preg_match('~^[a-z0-9_]+$~i',$Sb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $Sb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($_));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$b=[];$fc=[];$oj=fields($Q);foreach($l
as$k){$c=idf_escape($k[0]);$W=$k[1];if(!$W)$b["DROP"][]=" COLUMN $c";else{$W[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$W[1]);$fc[$k[0]]=$W[5];unset($W[5]);if(preg_match('~ AS ~',$W[3]))unset($W[1],$W[2]);if($k[0]=="")$b["ADD"][]="\n  ".implode("",$W).($Q==""?substr($Qe[$W[0]],16+strlen($W[0])):"");else{$i=$W[3];unset($W[3]);unset($W[6]);if($c!=$W[0])queries("EXEC sp_rename ".q(table($Q).".$c").", ".q(idf_unescape($W[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$W)][]="";$nj=$oj[$k[0]];if(default_value($nj)!=$i){if($nj["default"]!==null)$b["DROP"][]=" ".idf_escape($nj["default_constraint"]);if($i)$b["ADD"][]="\n $i FOR $c";}}}}if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (".implode(",",(array)$b["ADD"])."\n)");if($Q!=$_)queries("EXEC sp_rename ".q(table($Q)).", ".q($_));if($Qe)$b[""]=$Qe;foreach($b
as$u=>$W){if(!queries("ALTER TABLE ".table($_)." $u".implode(",",$W)))return
false;}foreach($fc
as$u=>$W){$dc=substr($W,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($_).", @level2type = N'Column', @level2name = ".q($u));queries("EXEC sp_addextendedproperty @name = N'MS_Description', @value = ".$dc.", @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($_).", @level2type = N'Column', @level2name = ".q($u));}return
true;}function
alter_indexes($Q,$b){$s=[];$xd=[];foreach($b
as$W){if($W[2]=="DROP"){if($W[0]=="PRIMARY")$xd[]=idf_escape($W[1]);else$s[]=idf_escape($W[1])." ON ".table($Q);}elseif(!queries(($W[0]!="PRIMARY"?"CREATE $W[0] ".($W[0]!="INDEX"?"INDEX ":"").idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q):"ALTER TABLE ".table($Q)." ADD PRIMARY KEY")." (".implode(", ",$W[2]).")"))return
false;}return(!$s||queries("DROP INDEX ".implode(", ",$s)))&&(!$xd||queries("ALTER TABLE ".table($Q)." DROP ".implode(", ",$xd)));}function
found_rows(array$R,array$Z){return
null;}function
foreign_keys($Q){$J=[];$Ti=Driver::get()->getOnActions();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($Q).", @fktable_owner = ".q(get_schema()))as$K){$o=&$J[$K["FK_NAME"]];$o["db"]=$K["PKTABLE_QUALIFIER"];$o["ns"]=$K["PKTABLE_OWNER"];$o["table"]=$K["PKTABLE_NAME"];$o["on_update"]=$Ti[$K["UPDATE_RULE"]];$o["on_delete"]=$Ti[$K["DELETE_RULE"]];$o["source"][]=$K["FKCOLUMN_NAME"];$o["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
backward_keys($Q){$G="SELECT fk.name AS constraint_name,
OBJECT_SCHEMA_NAME(fkc.parent_object_id) AS table_schema,
OBJECT_NAME(fkc.parent_object_id) AS table_name,
COL_NAME(fkc.parent_object_id, fkc.parent_column_id) AS column_name,
COL_NAME(fkc.referenced_object_id, fkc.referenced_column_id) AS referenced_column_name
FROM sys.foreign_key_columns fkc
JOIN sys.foreign_keys fk ON fkc.constraint_object_id = fk.object_id
WHERE OBJECT_SCHEMA_NAME(fkc.referenced_object_id) = ".q($_GET["ns"])."
AND OBJECT_NAME(fkc.referenced_object_id) = ".q($Q)."
ORDER BY table_schema, table_name";return
get_rows($G,null,"");}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($uo){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$uo)));}function
drop_tables($S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables($S,$uo,$en){return
apply_queries("ALTER SCHEMA ".idf_escape($en)." TRANSFER",array_merge($S,$uo));}function
trigger($_,$Q){if($_=="")return[];$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($_));$Fn=reset($L);if($Fn)$Fn["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$Fn["text"]);return$Fn;}function
triggers($Q){$J=[];foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($Q))as$K)$J[$K["name"]]=[$K["Timing"],$K["Event"]];return$J;}function
trigger_options(){return["Timing"=>["AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["AS"],];}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
Connection::get()->getValue("SELECT SCHEMA_NAME()");}function
set_schema($ql,$e=null){$_GET["ns"]=$ql;return
true;}function
create_sql($Q,$Za,$ym){if(is_view(table_status1($Q))){$so=view($Q);return"CREATE VIEW ".table($Q)." AS $so[select]";}$l=[];$F=false;foreach(fields($Q)as$_=>$k){$W=process_field($k,$k);if($W[6])$F=true;$l[]=implode("",$W);}foreach(indexes($Q)as$_=>$s){if(!$F||$s["type"]!="PRIMARY"){$d=[];foreach($s["columns"]as$u=>$W)$d[]=idf_escape($W).($s["descs"][$u]?" DESC":"");$_=idf_escape($_);$l[]=($s["type"]=="INDEX"?"INDEX $_":"CONSTRAINT $_ ".($s["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$d).")";}}foreach(Driver::get()->checkConstraints($Q)as$_=>$Db)$l[]="CONSTRAINT ".idf_escape($_)." CHECK ($Db)";return"CREATE TABLE ".table($Q)." (\n\t".implode(",\n\t",$l)."\n)";}function
foreign_keys_sql($Q){$l=[];foreach(foreign_keys($Q)as$Qe)$l[]=ltrim(format_foreign_key($Qe));return($l?"ALTER TABLE ".table($Q)." ADD\n\t".implode(",\n\t",$l).";\n\n":"");}function
truncate_sql($Q){return"TRUNCATE TABLE ".table($Q);}function
create_database_sql($Oc,$ym=""){return"";}function
use_sql($Oc){return"USE ".idf_escape($Oc).";\n";}function
trigger_sql($Q){$km="";foreach(triggers($Q)as$_=>$Fn)$km
.=create_trigger(" ON ".table($Q),trigger($_,$Q)).";";return$km;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){return$J;}function
support($ue){return
preg_match('~^(check|comment|columns|database|drop_col|dump|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$ue);}}Drivers::add("sqlite","SQLite",["SQLite3","PDO_SQLite"]);if(isset($_GET["sqlite"])){define("AdminNeo\DRIVER","sqlite");define("AdminNeo\DIALECT","sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","SQLite3");abstract
class
SqLiteConnectionBase
extends
Connection{private$sqlite;function
open($N,$U,$E){$this->sqlite=new
SQLite3($N);$this->version=$this->sqlite->version()["versionString"];return
true;}function
query($G,$Pn=false){$I=@$this->sqlite->query($G);$this->error="";if(!$I){$this->errno=$this->sqlite->lastErrorCode();$this->error=$this->sqlite->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
SqLiteResult($I);$this->affectedRows=$this->sqlite->changes();return
true;}function
quote($P){if(is_utf8($P))return"'".$this->sqlite->escapeString($P)."'";else
return"x'".first(unpack('H*',$P))."'";}}class
SqLiteResult
extends
Result{private$resource;private$offset=0;function
__construct(SQLite3Result$Tk){parent::__construct(0);$this->resource=$Tk;}function
fetchAssoc(){return$this->resource->fetchArray(SQLITE3_ASSOC);}function
fetchRow(){return$this->resource->fetchArray(SQLITE3_NUM);}function
fetchField(){$c=$this->offset++;$T=$this->resource->columnType($c);if($T===false)return
false;return(object)["name"=>$this->resource->columnName($c),"type"=>($T==SQLITE3_TEXT?15:0),"charsetnr"=>($T==SQLITE3_BLOB?63:0),];}}}elseif(extension_loaded("pdo_sqlite")){define("AdminNeo\DRIVER_EXTENSION","PDO_SQLite");abstract
class
SqLiteConnectionBase
extends
PdoConnection{function
open($N,$U,$E){return$this->dsn(DRIVER.":$N","","");}}}if(class_exists('AdminNeo\SqLiteConnectionBase')){class
SqLiteConnection
extends
SqLiteConnectionBase{protected
function
__construct(){parent::__construct();$this->open(":memory:","","");}function
open($N,$U,$E){if(!parent::open($N,$U,$E))return
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
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[["integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0,]];if($e->isMinVersion("3.31"))$this->generated=["STORED","VIRTUAL"];if($e->isMinVersion("3.37"))$this->types[0]["any"]=0;$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round","hex","unixepoch",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->insertFunctions=[];$this->editFunctions=["integer|real|numeric"=>"+/-","text"=>"||",];}function
getStructuredTypes(){return
parent::getStructuredTypes()[0];}function
engines(){$J=["table"];if(Connection::get()->isMinVersion("3.8.2")){if(Connection::get()->isMinVersion("3.37")){$J[]="STRICT";$J[]="STRICT, WITHOUT ROWID";}$J[]="WITHOUT ROWID";}return$J;}function
insertUpdate($Q,array$Ck,array$F){$Y=[];foreach($Ck
as$H)$Y[]="(".implode(", ",$H).")";return
queries("REPLACE INTO ".table($Q)." (".implode(", ",array_keys(reset($Ck))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($_,$vg=false){if($_=="sqlite_sequence")return"fileformat2.html#seqtab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$_))return"schematab.html";return
null;}function
checkConstraints($Q){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',$this->connection->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q)),$z);return
array_combine($z[2],$z[2]);}function
getAllFields(){$Ka=[];foreach(tables_list()as$Q=>$T){foreach(fields($Q)as$k)$Ka[$Q][]=$k;}return$Ka;}}function
create_driver(Connection$e){return
SqliteDriver::create($e,Admin::get());}function
idf_escape($r){return'"'.str_replace('"','""',$r).'"';}function
table($r){return
idf_escape($r);}function
connect($F=false,&$j=null){$e=$F?SqLiteConnection::create():SqLiteConnection::createSecondary();$E=Admin::get()->getCredentials()[2];if($E!=""){$I=Admin::get()->verifyDefaultPassword($E);if($I!==true){$j=$I;return
null;}}return$e;}function
get_databases($Oe){return[];}function
limit($G,$Z,$w,$A=0,$El=" "){return" $G$Z".($w?$El."LIMIT $w".($A?" OFFSET $A":""):"");}function
limit1($Q,$G,$Z,$El="\n"){return(preg_match('~^INTO~',$G)||Connection::get()->getValue("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($G,$Z,1,0,$El):" $G WHERE rowid = (SELECT rowid FROM ".table($Q).$Z.$El."LIMIT 1)");}function
db_collation($h,$Tb){return
Connection::get()->getValue("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name");}function
count_tables($g){return[];}function
table_status($_="",$te=false){$J=[];foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($_!=""?"AND name = ".q($_):"ORDER BY (name = 'sqlite_sequence'), name"))as$K){if($K["Engine"]=="table"){$Am=preg_replace('~.*\)~s','',$K["sql"]);$K["Engine"]=implode(", ",array_filter([(preg_match('~\bSTRICT\b~i',$Am)?"STRICT":""),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Am)?"WITHOUT ROWID":""),]))?:"table";}unset($K["sql"]);if(!$te)$K["Rows"]=Connection::get()->getValue("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}if(!$te){foreach(get_rows("SELECT * FROM sqlite_sequence".($_!=""?" WHERE name = ".q($_):""),null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];}return$J;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support($R){return!Connection::get()->getValue("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($Q){$J=[];$km=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));$ok=["select"=>1,"where"=>1,"order"=>1];if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$Q))$ok+=["insert"=>1,"update"=>1];foreach(get_rows("PRAGMA table_".(Connection::get()->isMinVersion("3.31")?"x":"")."info(".table($Q).")")as$K){$_=$K["name"];$T=strtolower($K["type"]);$i=$K["dflt_value"];$J[$_]=["field"=>$_,"type"=>(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric"))))),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~",$i,$y)?str_replace("''","'",$y[1]):($i=="NULL"?null:$i)),"null"=>!$K["notnull"],"privileges"=>$ok,"primary"=>$K["pk"],];if($K["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$km))$J[$_]["auto_increment"]=true;}$r='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$r.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$km,$z,PREG_SET_ORDER);foreach($z
as$y){$_=str_replace('""','"',preg_replace('~^"|"$~','',$y[1]));if($J[$_])$J[$_]["collation"]=trim($y[3],"'");}preg_match_all('~'.$r.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$km,$z,PREG_SET_ORDER);foreach($z
as$y){$_=str_replace('""','"',preg_replace('~^"|"$~','',$y[1]));$J[$_]["default"]=$y[3];$J[$_]["generated"]=strtoupper($y[4]);}return$J;}function
indexes($Q,$e=null){if(!is_object($e))$e=Connection::get();$J=[];$km=$e->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$km,$y)){$J[""]=["type"=>"PRIMARY","columns"=>[],"lengths"=>[],"descs"=>[]];preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$y[1],$z,PREG_SET_ORDER);foreach($z
as$y){$J[""]["columns"][]=idf_unescape($y[2]).$y[4];$J[""]["descs"][]=(preg_match('~DESC~i',$y[5])?'1':null);}}if(!$J){foreach(fields($Q)as$_=>$k){if($k["primary"])$J[""]=["type"=>"PRIMARY","columns"=>[$_],"lengths"=>[],"descs"=>[null]];}}$om=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($Q),$e);foreach(get_rows("PRAGMA index_list(".table($Q).")",$e)as$K){$_=$K["name"];$s=["type"=>($K["unique"]?"UNIQUE":"INDEX")];$s["lengths"]=[];$s["descs"]=[];foreach(get_rows("PRAGMA index_info(".idf_escape($_).")",$e)as$fl){$s["columns"][]=$fl["name"];$s["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($_).' ON '.idf_escape($Q),'~').' \((.*)\)$~i',$om[$_],$Lk)){preg_match_all('/("[^"]*+")+( DESC)?/',$Lk[2],$z);foreach($z[2]as$u=>$W){if($W)$s["descs"][$u]='1';}}if(!$J[""]||$s["type"]!="UNIQUE"||$s["columns"]!=$J[""]["columns"]||$s["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$_))$J[$_]=$s;}return$J;}function
foreign_keys($Q){$J=[];foreach(get_rows("PRAGMA foreign_key_list(".table($Q).")")as$K){$o=&$J[$K["id"]];if(!$o)$o=$K;$o["source"][]=$K["from"];$o["target"][]=$K["to"];}return$J;}function
backward_keys($Q){return[];}function
view($_){return["select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($_)))];}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):[]);}function
information_schema($h){return
false;}function
error(){return
h(Connection::get()->getError());}function
check_sqlite_name($_){$oe="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($oe)\$~",$_)){Connection::get()->setError(lang(129,str_replace("|",", ",$oe)));return
false;}return
true;}function
create_database($h,$Sb){if(file_exists($h)){Connection::get()->setError(lang(130));return
false;}if(!check_sqlite_name($h))return
false;try{$x=SqLiteConnection::createSecondary();$x->open($h,"","");}catch(Exception$be){Connection::get()->setError($be->getMessage());return
false;}$x->query('PRAGMA encoding = "UTF-8"');$x->query('CREATE TABLE adminneo (i)');$x->query('DROP TABLE adminneo');return
true;}function
drop_databases($g){Connection::get()->close();foreach($g
as$h){if(!check_sqlite_name($h))return
false;if(!@unlink($h)){Connection::get()->setError(lang(130));return
false;}}return
true;}function
rename_database($_,$Sb){if(!check_sqlite_name($_))return
false;Connection::get()->close();Connection::get()->setError(lang(130));return@rename(DB,$_);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$do=($Q==""||$Qe||$Sd);foreach($l
as$k){if($k[0]!=""||!$k[1]||$k[2]){$do=true;break;}}$Pa=[];$sj=[];foreach($l
as$k){if(!$k[1])continue;if($k[0]!="")$sj[$k[0]]=$k[1][0];$Pa[]=($do?$k[1]:"ADD ".implode($k[1]));}if(!$do){foreach($Pa
as$W){if(!queries("ALTER TABLE ".table($Q)." $W"))return
false;}if($Q!=$_&&!queries("ALTER TABLE ".table($Q)." RENAME TO ".table($_)))return
false;}elseif(!recreate_table($Q,$_,$Pa,$sj,$Qe,$Za,[],"","",$Sd))return
false;if($Za){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Za WHERE name = ".q($_));if(!Connection::get()->getAffectedRows())queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($_).", $Za)");queries("COMMIT");}return
true;}function
recreate_table($Q,$_,$l,$sj,$Qe,$Za="",$t=[],$yd="",$_a="",$Sd=""){if($Q!=""){if(!$l){foreach(fields($Q)as$u=>$k){if($t)$k["auto_increment"]=0;$l[]=process_field($k,$k);$sj[$u]=idf_escape($u);}}$kk=false;foreach($l
as$k){if($k[6])$kk=true;}$_d=[];foreach($t
as$u=>$W){if($W[2]=="DROP"){$_d[$W[1]]=true;unset($t[$u]);}}foreach(indexes($Q)as$Kg=>$s){$d=[];foreach($s["columns"]as$u=>$c){if(!isset($sj[$c]))continue
2;$d[]=$sj[$c].($s["descs"][$u]?" DESC":"");}if(!$_d[$Kg]){if($s["type"]!="PRIMARY"||!$kk){$Kg=preg_replace('~^sqlite_~',"",$Kg);$t[]=[$s["type"],$Kg,$d];}}}foreach($t
as$u=>$W){if($W[0]=="PRIMARY"){unset($t[$u]);$Qe[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($Q)as$Kg=>$o){foreach($o["source"]as$u=>$c){if(!$sj[$c])continue
2;$o["source"][$u]=idf_unescape($sj[$c]);}if(!isset($Qe[" $Kg"]))$Qe[]=" ".format_foreign_key($o);}queries("BEGIN");}$Ab=array();foreach($l
as$k){if(preg_match('~GENERATED~',$k[3]))unset($sj[array_search($k[0],$sj)]);$Ab[]="  ".implode($k);}$Ab=array_merge($Ab,array_filter($Qe));foreach(Driver::get()->checkConstraints($Q)as$Db){if($Db!=$yd)$Ab[]="  CHECK ($Db)";}if($_a)$Ab[]="  CHECK ($_a)";$gn=($Q!=""&&$Q==$_?"adminneo_$_":$_);if(!$Sd&&$Q!="")$Sd=table_status1($Q)["Engine"]??null;if(!queries("CREATE TABLE ".table($gn)." (\n".implode(",\n",$Ab)."\n)".($Sd!="table"&&in_array($Sd,Driver::get()->engines())?" $Sd":"")))return
false;if($Q!=""){if($sj&&!queries("INSERT INTO ".table($gn)." (".implode(", ",$sj).") SELECT ".implode(", ",array_map('AdminNeo\idf_escape',array_keys($sj)))." FROM ".table($Q)))return
false;$In=[];foreach(triggers($Q)as$Gn=>$rn){$Fn=trigger($Gn,$Q);$In[]="CREATE TRIGGER ".idf_escape($Gn)." ".implode(" ",$rn)." ON ".table($_)."\n$Fn[Statement]";}$Za=$Za?"":Connection::get()->getValue("SELECT seq FROM sqlite_sequence WHERE name = ".q($Q));if(!queries("DROP TABLE ".table($Q))||($Q==$_&&!queries("ALTER TABLE ".table($gn)." RENAME TO ".table($_)))||!alter_indexes($_,$t))return
false;if($Za)queries("UPDATE sqlite_sequence SET seq = $Za WHERE name = ".q($_));foreach($In
as$Fn){if(!queries($Fn))return
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
drop_views($uo){return
apply_queries("DROP VIEW",$uo);}function
drop_tables($S){return
apply_queries("DROP TABLE",$S);}function
move_tables($S,$uo,$en){return
false;}function
trigger($_,$Q){if($_=="")return["Statement"=>"BEGIN\n\t;\nEND"];$r='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$Hn=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$r\\s*(".implode("|",$Hn["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($r))?\\s+ON\\s*$r\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($_)),$y);$Ii=$y[3];return["Trigger"=>$_,"Timing"=>strtoupper($y[1]),"Event"=>strtoupper($y[2]).($Ii?" OF":""),"Of"=>idf_unescape($Ii),"Statement"=>$y[4],];}function
triggers($Q){$J=[];$Hn=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q))as$K){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$Hn["Timing"]).')\s*(.*?)\s+ON\b~i',$K["sql"],$y);$J[$K["name"]]=[$y[1],$y[2]];}return$J;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE"],"Type"=>["FOR EACH ROW"],];}function
begin(){return
queries("BEGIN");}function
last_id($I){return
Connection::get()->getValue("SELECT LAST_INSERT_ROWID()");}function
explain(Connection$e,$G){return$e->query("EXPLAIN QUERY PLAN $G");}function
found_rows(array$R,array$Z){return
null;}function
types(){return[];}function
create_sql($Q,$Za,$ym){$J=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($Q));foreach(indexes($Q)as$_=>$s){if($_==''||strpos($_,"sqlite_")===0)continue;$J
.=";\n\n".index_sql($Q,$s['type'],$_,"(".implode(", ",array_map('AdminNeo\idf_escape',$s['columns'])).")");}return$J;}function
truncate_sql($Q){return"DELETE FROM ".table($Q);}function
trigger_sql($Q){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q)));}function
show_variables(){$J=[];foreach(get_rows("PRAGMA pragma_list")as$K){$_=$K["name"];if($_!="pragma_list"&&$_!="compile_options"){$J[$_]=[$_,''];foreach(get_rows("PRAGMA $_")as$gl)$J[$_][1].=implode(", ",$gl)."\n";}}return$J;}function
show_status(){$J=[];foreach(get_vals("PRAGMA compile_options")as$bj)$J[]=explode("=",$bj,2)+["",""];return$J;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){return$J;}function
support($ue){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$ue);}}Drivers::add("oracle","Oracle (beta)",["OCI8","PDO_OCI"]);if(isset($_GET["oracle"])){define("AdminNeo\DRIVER","oracle");define("AdminNeo\DIALECT","oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","oci8");class
OracleConnection
extends
Connection{private$connection;private$dbName=null;function
open($N,$U,$E){$this->connection=@oci_new_connect($U,$E,$N,"AL32UTF8");if($this->connection){$this->version=oci_server_version($this->connection);return
true;}$j=oci_error();$this->error=$j["message"];return
false;}function
quote($P){return"'".str_replace("'","''",$P)."'";}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getAndClearDbName(){$_=$this->dbName?:DB;$this->dbName=null;return$_;}function
query($G,$Pn=false){$I=oci_parse($this->connection,$G);$this->error="";if(!$I){$j=oci_error($this->connection);$this->errno=$j["code"];$this->error=$j["message"];return
false;}set_error_handler(function($Wd,$j){if(ini_bool("html_errors"))$j=html_entity_decode(strip_tags($j));$j=preg_replace('~^[^:]*: ~','',$j);$this->error=$j;});$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
OracleResult($I);$this->affectedRows=oci_num_rows($I);oci_free_statement($I);}return$J;}function
getValue($G,$ve=0){$I=$this->query($G);return
is_object($I)?$I->fetchValue($ve):false;}}class
OracleResult
extends
Result{private$resource;private$offset=1;function
__construct($I){parent::__construct(0);$this->resource=$I;}function
fetchAssoc(){return$this->convertRow(oci_fetch_assoc($this->resource));}function
fetchRow(){return$this->convertRow(oci_fetch_row($this->resource));}function
fetchValue($ve){return
oci_fetch($this->resource)?oci_result($this->resource,$ve+1):false;}private
function
convertRow($K){if(is_array($K)){foreach($K
as$u=>$W){if(is_a($W,'OCILob')||is_a($W,'OCI-Lob'))$K[$u]=$W->load();}}return$K;}function
fetchField(){$c=$this->offset++;$_=oci_field_name($this->resource,$c);if($_===false)return
false;$T=oci_field_type($this->resource,$c);if($T===false)return
false;return(object)['name'=>$_,'type'=>$T,'charsetnr'=>(preg_match("~raw|blob|bfile~",$T)?63:0),];}}}elseif(extension_loaded("pdo_oci")){define("AdminNeo\DRIVER_EXTENSION","PDO_OCI");class
OracleConnection
extends
PdoConnection{private$dbName=null;function
open($N,$U,$E){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$U,$E);}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getAndClearDbName(){$_=$this->dbName?:DB;$this->dbName=null;return$_;}}}class
OracleDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(122)=>["number"=>38,"binary_float"=>12,"binary_double"=>21,],lang(123)=>["date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28,],lang(124)=>["char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295,],lang(126)=>["raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296,],];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->insertFunctions=["date"=>"current_date","timestamp"=>"current_timestamp",];$this->editFunctions=["number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",];}function
begin(){return
true;}function
insertUpdate($Q,array$Ck,array$F){foreach($Ck
as$H){$Yn=[];$Z=[];foreach($H
as$u=>$W){$Yn[]="$u = $W";if(isset($F[idf_unescape($u)]))$Z[]="$u = $W";}if(!(($Z&&queries("UPDATE ".table($Q)." SET ".implode(", ",$Yn)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($H)).") VALUES (".implode(", ",$H).")")))return
false;}return
true;}function
hasCStyleEscapes(){return
true;}}function
create_driver(Connection$e){return
OracleDriver::create($e,Admin::get());}function
is_server_host_valid($Jf){return(bool)preg_match('~^[^/]+(/([^/:]+)?(:[^/:]+)?(/[^/:]+)?)?$~',$Jf);}function
idf_escape($r){return'"'.str_replace('"','""',$r).'"';}function
table($r){return
idf_escape($r);}function
connect($F=false,&$j=null){$e=$F?OracleConnection::create():OracleConnection::createSecondary();$Ec=Admin::get()->getCredentials();if(!$e->open($Ec[0],$Ec[1],$Ec[2])){$j=$e->getError();return
null;}return$e;}function
get_databases($Oe){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($G,$Z,$w,$A=0,$El=" "){return($A?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $G$Z) t WHERE rownum <= ".($w+$A).") WHERE rnum > $A":($w?" * FROM (SELECT $G$Z) WHERE rownum <= ".($w+$A):" $G$Z"));}function
limit1($Q,$G,$Z,$El="\n"){return" $G$Z";}function
db_collation($h,$Tb){return
Connection::get()->getValue("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
Connection::get()->getValue("SELECT USER FROM DUAL");}function
where_owner($gk,$wj="owner"){if(!$_GET["ns"])return'';return"$gk$wj = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($d){$wj=where_owner('');return"(SELECT $d FROM all_views WHERE ".($wj?:"rownum < 0").")";}function
tables_list(){$so=views_table("view_name");$wj=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$wj
UNION SELECT view_name, 'view' FROM $so
ORDER BY 1");}function
count_tables($g){$J=[];foreach($g
as$h)$J[$h]=Connection::get()->getValue("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($h));return$J;}function
table_status($_=""){$J=[];$ul=q($_);$h=Connection::get()->getAndClearDbName();$so=views_table("view_name");$wj=where_owner(" AND ");foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q($h).$wj.($_!=""?" AND table_name = $ul":"")."
UNION SELECT view_name, 'view', 0, 0 FROM $so".($_!=""?" WHERE view_name = $ul":"")."
ORDER BY 1")as$K)$J[$K["Name"]]=$K;return$J;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support($R){return
true;}function
fields($Q){$J=[];$wj=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($Q)."$wj ORDER BY column_id")as$K){$T=$K["DATA_TYPE"];$v="$K[DATA_PRECISION],$K[DATA_SCALE]";if($v==",")$v=$K["CHAR_COL_DECL_LENGTH"];$J[$K["COLUMN_NAME"]]=["field"=>$K["COLUMN_NAME"],"full_type"=>$T.($v?"($v)":""),"type"=>strtolower($T),"length"=>$v,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1],];}return$J;}function
indexes($Q,$e=null){$J=[];$wj=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($Q)."$wj
ORDER BY ac.constraint_type, aic.column_position",$e)as$K){$Yf=$K["INDEX_NAME"];$Zb=$K["DATA_DEFAULT"];$Zb=($Zb?trim($Zb,'"'):$K["COLUMN_NAME"]);$J[$Yf]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$Yf]["columns"][]=$Zb;$J[$Yf]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$Yf]["descs"][]=($K["DESCEND"]&&$K["DESCEND"]=="DESC"?'1':null);}return$J;}function
view($_){$so=views_table("view_name, text");$L=get_rows('SELECT text "select" FROM '.$so.' WHERE view_name = '.q($_));return
reset($L);}function
collations(){return[];}function
information_schema($h){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
h(Connection::get()->getError());}function
explain(Connection$e,$G){$e->query("EXPLAIN PLAN FOR $G");return$e->query("SELECT * FROM plan_table");}function
found_rows(array$R,array$Z){return
null;}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$b=$xd=[];$oj=($Q?fields($Q):[]);foreach($l
as$k){$W=$k[1];if($W&&$k[0]!=""&&idf_escape($k[0])!=$W[0])queries("ALTER TABLE ".table($Q)." RENAME COLUMN ".idf_escape($k[0])." TO $W[0]");$nj=$oj[$k[0]];if($W&&$nj){$Ni=process_field($nj,$nj);if($W[2]==$Ni[2])$W[2]="";}if($W)$b[]=($Q!=""?($k[0]!=""?"MODIFY (":"ADD ("):"  ").implode($W).($Q!=""?")":"");else$xd[]=idf_escape($k[0]);}if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($Q)."\n".implode("\n",$b)))&&(!$xd||queries("ALTER TABLE ".table($Q)." DROP (".implode(", ",$xd).")"))&&($Q==$_||queries("ALTER TABLE ".table($Q)." RENAME TO ".table($_)));}function
alter_indexes($Q,$b){$xd=[];$vk=[];foreach($b
as$W){if($W[0]!="INDEX"){$W[2]=preg_replace('~ DESC$~','',$W[2]);$Ac=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");array_unshift($vk,"ALTER TABLE ".table($Q).$Ac);}elseif($W[2]=="DROP")$xd[]=idf_escape($W[1]);else$vk[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q)." (".implode(", ",$W[2]).")";}if($xd)array_unshift($vk,"DROP INDEX ".implode(", ",$xd));foreach($vk
as$G){if(!queries($G))return
false;}return
true;}function
foreign_keys($Q){$J=[];$G="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($Q);foreach(get_rows($G)as$K)$J[$K['NAME']]=["db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>[$K['SRC_COLUMN']],"target"=>[$K['DEST_COLUMN']],"on_delete"=>$K['ON_DELETE'],"on_update"=>null,];return$J;}function
backward_keys($Q){return[];}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($uo){return
apply_queries("DROP VIEW",$uo);}function
drop_tables($S){return
apply_queries("DROP TABLE",$S);}function
last_id($I){return
0;}function
schemas(){$J=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return$J?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1");}function
get_schema(){return
Connection::get()->getValue("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($ql,$e=null){if(!$e)$e=Connection::get();return(bool)$e->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($ql));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$J=[];$L=get_rows('SELECT * FROM v$instance');foreach(reset($L)as$u=>$W)$J[]=[$u,$W];return$J;}function
process_list(){return
get_rows('SELECT sess.process AS "process", sess.username AS "user", sess.schemaname AS "schema", sess.status AS "status", sess.wait_class AS "wait_class", sess.seconds_in_wait AS "seconds_in_wait", sql.sql_text AS "sql_text", sess.machine AS "machine", sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){return$J;}function
support($ue){return
preg_match('~^(columns|database|drop_col|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$ue);}}Drivers::add("mongo","MongoDB (alpha)",["mongodb"]);if(isset($_GET["mongo"])){define("AdminNeo\DRIVER","mongo");define("AdminNeo\DIALECT","mongo");if(class_exists('MongoDB\Driver\Manager')){define("AdminNeo\DRIVER_EXTENSION","MongoDB");class
MongoConnection
extends
Connection{private$manager;private$dbName;function
getDefaultServerName(){return"localhost:27017";}function
open($N,$U,$E,$Qc="",$Xa=""){$this->version=MONGODB_VERSION;$B=[];if($U.$E!=""){$B["username"]=$U;$B["password"]=$E;}if($Qc!="")$B["db"]=$Qc;if($Xa!="")$B["authSource"]=$Xa;$this->manager=new
Manager($N,$B);$this->dbName=$Qc?:"default";return(bool)$this->executeCommand(['ping'=>1]);}function
executeCommand(array$bc,$Ca=false){try{return$this->manager->executeCommand($Ca?"admin":$this->dbName,new
Command($bc));}catch(\MongoDB\Driver\Exception\Exception$de){$this->error=$de->getMessage();return
null;}}function
executeQuery($qi,Query$G,$B=null){try{return$this->manager->executeQuery($qi,$G,$B);}catch(\MongoDB\Driver\Exception\Exception$de){$this->error=$de->getMessage();return
null;}}function
executeBulkWrite($qi,BulkWrite$sb,$_c){try{$Xk=$this->manager->executeBulkWrite($qi,$sb);$this->affectedRows=$Xk->$_c();return
true;}catch(Exception$de){$this->error=$de->getMessage();return
false;}}function
query($G,$Pn=false){return
false;}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getDbName(){return$this->dbName;}function
quote($P){return$P;}}class
MongoResult
extends
Result{private$rows;private$charset;private$offset=0;function
__construct($I){$this->rows=$this->charset=[];foreach($I
as$Ag){$K=[];foreach($Ag
as$u=>$W){if(is_a($W,'MongoDB\BSON\Binary'))$this->charset[$u]=63;$K[$u]=(is_a($W,'MongoDB\BSON\ObjectID')?'MongoDB\BSON\ObjectID("'."$W\")":(is_a($W,'MongoDB\BSON\UTCDatetime')?$W->toDateTime()->format('Y-m-d H:i:s'):(is_a($W,'MongoDB\BSON\Binary')?$W->getData():(is_a($W,'MongoDB\BSON\Regex')?"$W":(is_object($W)||is_array($W)?json_encode($W,256):$W)))));}$this->rows[]=$K;foreach($K
as$u=>$W){if(!isset($this->rows[0][$u]))$this->rows[0][$u]=null;}}parent::__construct(count($this->rows));}function
fetchAssoc(){$K=current($this->rows);if(!$K)return$K;$f=[];foreach($this->rows[0]as$u=>$W)$f[$u]=$K[$u];next($this->rows);return$f;}function
fetchRow(){$f=$this->fetchAssoc();if(!$f)return$f;return
array_values($f);}function
fetchField(){$Lg=array_keys($this->rows[0]);$_=$Lg[$this->offset++];return(object)['name'=>$_,'type'=>15,'charsetnr'=>$this->charset[$_],];}}}class
MongoDriver
extends
Driver{static$NULL="\0";var$primary="_id";protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->operators=["=","!=",">","<",">=","<=","regex","(f)=","(f)!=","(f)>","(f)<","(f)>=","(f)<=","(date)=","(date)!=","(date)>","(date)<","(date)>=","(date)<=",];$this->likeOperator="=";$this->insertFunctions=["json"];$this->systemDatabases=["admin","config","local"];}function
select($Q,array$M,array$Z,array$mf,array$fj=[],$w=1,$C=0,$lk=false){$Ee=where_to_query($Z);$M=($M==["*"]?[]:array_fill_keys($M,1));if(count($M)&&!isset($M['_id']))$M['_id']=0;$B=$M?['projection'=>$M]:[];$em=[];foreach($fj
as$W){$W=preg_replace('~ DESC$~','',$W,1,$zc);$em[$W]=($zc?-1:1);}if($em)$B['sort']=$em;$w=min(200,max(1,$w));$cm=$C*$w;$B+=['limit'=>$w,'skip'=>$cm];$G=new
Query($Ee,$B);$rm=microtime(true);try{$I=new
MongoResult(Connection::get()->executeQuery(Connection::get()->getDbName().".$Q",$G));}catch(Exception$Dd){Connection::get()->setError($Dd->getMessage());$I=false;}if($lk)echo$this->admin->formatSelectQuery('find('.json_encode(($Ee?["filter"=>$Ee]:[])+$B).")",$rm,!$I);return$I;}function
update($Q,array$H,$yk,$w=0,$El="\n"){$Ee=sql_query_where_parser($yk);if(isset($H['_id']))unset($H['_id']);$Pk=[];foreach($H
as$u=>$X){if($X==self::$NULL){$Pk[$u]=1;unset($H[$u]);}}$Hi=['$set'=>$H];if(count($Pk))$Hi['$unset']=$Pk;$B=['upsert'=>false];queries('update('.json_encode(($Ee?["filter"=>$Ee]:[])+$Hi+$B).")");$sb=new
BulkWrite();$sb->update($Ee,$Hi,$B);return
Connection::get()->executeBulkWrite(Connection::get()->getDbName().".$Q",$sb,'getModifiedCount');}function
delete($Q,$yk,$w=0){$Ee=sql_query_where_parser($yk);$B=$w?['limit'=>$w]:[];queries('delete('.json_encode(($Ee?["filter"=>$Ee]:[])+$B).")");$sb=new
BulkWrite();$sb->delete($Ee,$B);return
Connection::get()->executeBulkWrite(Connection::get()->getDbName().".$Q",$sb,'getDeletedCount');}function
insert($Q,array$H){if($H['_id']=='')unset($H['_id']);foreach($H
as$u=>$X){if($X==self::$NULL)unset($H[$u]);}queries('insert('.json_encode($H).")");$sb=new
BulkWrite();$sb->insert($H);return
Connection::get()->executeBulkWrite(Connection::get()->getDbName().".$Q",$sb,'getInsertedCount');}function
getNull(){return
self::$NULL;}}function
create_driver(Connection$e){return
MongoDriver::create($e,Admin::get());}function
get_databases($Oe){$Ic=Connection::get()->executeCommand(['listDatabases'=>1],true);if(!$Ic)return[];$g=[];foreach($Ic
as$Tc){foreach($Tc->databases
as$h)$g[]=$h->name;}return$g;}function
count_tables($g){$J=[];return$J;}function
tables_list(){$Ic=Connection::get()->executeCommand(['listCollections'=>1]);if(!$Ic)return[];$Ub=[];foreach($Ic
as$I)$Ub[$I->name]='table';return$Ub;}function
drop_databases($g){return
false;}function
indexes($Q,$e=null){$Ic=Connection::get()->executeCommand(['listIndexes'=>$Q]);if(!$Ic)return[];$t=[];foreach($Ic
as$s){$gd=[];$d=[];foreach(get_object_vars($s->key)as$c=>$T){$gd[]=($T==-1?'1':null);$d[]=$c;}$t[$s->name]=["type"=>($s->name=="_id_"?"PRIMARY":(isset($s->unique)?"UNIQUE":"INDEX")),"columns"=>$d,"lengths"=>[],"descs"=>$gd,];}return$t;}function
fields($Q){$l=fields_from_edit();if(!$l){$I=Driver::get()->select($Q,["*"],[],[],[],10);if($I){while($K=$I->fetchAssoc()){foreach($K
as$u=>$W){$K[$u]=null;$l[$u]=["field"=>$u,"full_type"=>"varchar","type"=>"varchar","null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary),"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1,],];}}}}return$l;}function
found_rows(array$R,array$Z){$Z=where_to_query($Z);$Ic=Connection::get()->executeCommand(['count'=>$R['Name'],'query'=>$Z]);if(!$Ic)return
null;return(int)$Ic->toArray()[0]->n;}function
sql_query_where_parser($yk){$yk=preg_replace('~^\s*WHERE\s*~',"",$yk);while($yk[0]=="(")$yk=preg_replace('~^\((.*)\)$~',"$1",$yk);$Io=explode(' AND ',$yk);$Jo=explode(') OR (',$yk);$Z=[];foreach($Io
as$Go)$Z[]=trim($Go);if(count($Jo)==1)$Jo=[];elseif(count($Jo)>1)$Z=[];return
where_to_query($Z,$Jo);}function
where_to_query($Eo=[],$Fo=[]){$f=[];foreach(['and'=>$Eo,'or'=>$Fo]as$T=>$Z){if(is_array($Z)){foreach($Z
as$le){list($Qb,$Wi,$W)=explode(" ",$le,3);if($Qb=="_id"&&preg_match('~^(MongoDB\\\\BSON\\\\ObjectID)\("(.+)"\)$~',$W,$y)){list(,$Nb,$W)=$y;$W=new$Nb($W);}if(!in_array($Wi,Admin::get()->getOperators()))continue;if(preg_match('~^\(f\)(.+)~',$Wi,$y)){$W=(float)$W;$Wi=$y[1];}elseif(preg_match('~^\(date\)(.+)~',$Wi,$y)){$Pc=new
DateTime($W);$W=new
BSON\UTCDatetime($Pc->getTimestamp()*1000);$Wi=$y[1];}switch($Wi){case'=':$Wi='$eq';break;case'!=':$Wi='$ne';break;case'>':$Wi='$gt';break;case'<':$Wi='$lt';break;case'>=':$Wi='$gte';break;case'<=':$Wi='$lte';break;case'regex':$Wi='$regex';break;default:continue
2;}if($T=='and')$f['$and'][]=[$Qb=>[$Wi=>$W]];elseif($T=='or')$f['$or'][]=[$Qb=>[$Wi=>$W]];}}}return$f;}function
table($r){return$r;}function
idf_escape($r){return$r;}function
table_status($_="",$te=false){$J=[];foreach(($_!=""?[$_=>1]:tables_list())as$Q=>$T)$J[$Q]=["Name"=>$Q,"Engine"=>""];return$J;}function
create_database($h,$Sb){return
true;}function
last_id($I){return
0;}function
error(){return
h(Connection::get()->getError());}function
collations(){return[];}function
logged_user(){$Ec=Admin::get()->getCredentials();return$Ec[1];}function
connect($F=false,&$j=null){$e=$F?MongoConnection::create():MongoConnection::createSecondary();list($N,$U,$E)=Admin::get()->getCredentials();$vh=$_SESSION["db"][DRIVER][SERVER][$U];if($N=="")$N="localhost:27017";$Qc=Admin::get()->getDatabase();$Xa=getenv("MONGO_AUTH_SOURCE")?:key($vh);if(!$e->open("mongodb://$N",$U,$E,$Qc,$Xa)){$j=$e->getError();return
null;}return$e;}function
alter_indexes($Q,$b){foreach($b
as$W){list($T,$_,$Tl)=$W;if($Tl=="DROP")$Ic=Connection::get()->executeCommand(["dropIndexes"=>$Q,"index"=>$_]);else{$d=[];foreach($Tl
as$c){$c=preg_replace('~ DESC$~','',$c,1,$zc);$d[$c]=$zc?-1:1;}$bc=["createIndexes"=>$Q,"indexes"=>[["key"=>$d,"name"=>$_,"unique"=>$T=="UNIQUE",]],];$Ic=Connection::get()->executeCommand($bc);}if(!$Ic)return
false;}return
true;}function
support($ue){return
preg_match("~database|indexes|descidx~",$ue);}function
db_collation($h,$Tb){}function
information_schema($h){return
false;}function
is_view(array$R){return
false;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){return$J;}function
foreign_keys($Q){return[];}function
backward_keys($Q){return[];}function
fk_support($R){}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){if($Q=="")return(bool)Connection::get()->executeCommand(["create"=>$_]);return
false;}function
drop_tables($S){foreach($S
as$_){if(!Connection::get()->executeCommand(["drop"=>$_]))return
false;}return
true;}function
truncate_tables($S){foreach($S
as$_){$bc=["delete"=>$_,"deletes"=>[["q"=>(object)[],"limit"=>0,]],];if(!Connection::get()->executeCommand($bc))return
false;}return
true;}}Drivers::add("elastic","Elasticsearch 7 (beta)",["json + allow_url_fopen"]);if(isset($_GET["elastic"])){define("AdminNeo\DRIVER","elastic");define("AdminNeo\DIALECT","elastic");if(ini_bool('allow_url_fopen')){define("AdminNeo\ELASTIC_DB_NAME","elastic");define("AdminNeo\DRIVER_EXTENSION","JSON");class
ElasticConnection
extends
Connection{private$serviceUrl;function
getDefaultServerName(){return"localhost:9200";}function
rootQuery($Oj,$uc=null,$Zh='GET'){$B=['http'=>['method'=>$Zh,'content'=>$uc!==null?json_encode($uc):null,'header'=>$uc!==null?'Content-Type: application/json':[],'ignore_errors'=>1,'follow_location'=>0,'max_redirects'=>0,],];$Jn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Jn)$B["ssl"]=["verify_peer"=>false];list($m,$Af)=get_url("$this->serviceUrl/".ltrim($Oj,'/'),stream_context_create($B));if($m===false){$this->error=lang(3);return
false;}$sg=false;foreach($Af
as$_f){if(!strcasecmp($_f,"X-elastic-product: Elasticsearch")){$sg=true;break;}}if(!$sg){$this->error=lang(3);return
false;}$J=json_decode($m,true);if($J===null){$this->error=lang(3);return
false;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',isset($Af[0])?$Af[0]:'')){if(isset($J['error']['root_cause'][0]['type']))$this->error=$J['error']['root_cause'][0]['type'].": ".$J['error']['root_cause'][0]['reason'];elseif(isset($J['status'])&&isset($J['error'])&&is_string($J['error']))$this->error=$J['error'];return
false;}return$J;}function
query($G,$Pn=false){return
false;}function
sendRequest($Oj,$uc=null,$Zh='GET'){if($Oj!=""&&$Oj[0]=="S"&&preg_match('/SELECT 1 FROM ([^ ]+) WHERE (.+) LIMIT ([0-9]+)/',$Oj,$z)){$Z=explode(" AND ",$z[2]);return
Driver::get()->select($z[1],["*"],$Z,[],[],$z[3]);}return$this->rootQuery($Oj,$uc,$Zh);}function
open($N,$U,$E){$this->serviceUrl=build_http_url($N,$U,$E,"localhost",9200);if(!$this->serviceUrl){$this->error=lang(3);return
false;}$J=$this->sendRequest('');if(!$J)return
false;if(!isset($J['version']['number'])){$this->error=lang(3);return
false;}$this->version=$J['version']['number'];return
true;}function
selectDatabase($_){return
true;}function
quote($P){return$P;}}class
ElasticResult
extends
Result{private$rows;function
__construct(array$L){parent::__construct(count($L));$this->rows=$L;reset($this->rows);}function
fetchAssoc(){$J=current($this->rows);next($this->rows);return$J;}function
fetchRow(){$K=$this->fetchAssoc();return$K?array_values($K):false;}function
fetchField(){return
false;}}}class
ElasticDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(122)=>["long"=>3,"integer"=>5,"short"=>8,"byte"=>10,"double"=>20,"float"=>66,"half_float"=>12,"scaled_float"=>21,"boolean"=>1,],lang(123)=>["date"=>10,],lang(124)=>["string"=>65535,"text"=>65535,"keyword"=>65535,],lang(126)=>["binary"=>255,],];$this->operators=["must(term)","must(match)","must(regexp)","should(term)","should(match)","should(regexp)","must_not(term)","must_not(match)","must_not(regexp)",];$this->likeOperator="should(match)";$this->insertFunctions=["json"];}function
select($Q,array$M,array$Z,array$mf,array$fj=[],$w=1,$C=0,$lk=false){$f=[];if($M!=["*"])$f["fields"]=array_values($M);if($fj){$em=[];foreach($fj
as$Qb){$Qb=preg_replace('~ DESC$~','',$Qb,1,$zc);$em[]=($zc?[$Qb=>"desc"]:$Qb);}$f["sort"]=$em;}if($w){$f["size"]=$w;if($C)$f["from"]=($C*$w);}foreach($Z
as$W){if(preg_match('~^\((.+ OR .+)\)$~',$W,$z)){$Mj=explode(" OR ",$z[1]);foreach($Mj
as$Cj)$this->addQueryCondition($Cj,$f);}else$this->addQueryCondition($W,$f);}$G="$Q/_search";$rm=microtime(true);$ul=$this->connection->rootQuery($G,$f);if($lk)echo$this->admin->formatSelectQuery("\"GET $G\": ".json_encode($f),$rm,!$ul);if(empty($ul))return
false;$Km=$M==["*"]?array_keys(fields($Q)):[];$J=[];foreach($ul["hits"]["hits"]as$Ff){$K=[];if($M==["*"])$K["_id"]=$Ff["_id"];if($M!=["*"]){$l=[];foreach($M
as$u)$l[$u]=$u=="_id"?$Ff["_id"]:$Ff["_source"][$u];}else{foreach($Km
as$u)$l[$u]=$u=="_id"?$Ff["_id"]:$Ff["_source"][$u];}foreach($l
as$u=>$W)$K[$u]=(is_array($W)?json_encode($W):$W);$J[]=$K;}return
new
ElasticResult($J);}private
function
addQueryCondition($W,&$f){list($Qb,$Wi,$W)=explode(" ",$W,3);if($Qb=="_id"&&$Wi=="="){$f["query"]["bool"]["must"][]=["term"=>[$Qb=>$W]];return;}if(!preg_match('~^([^(]+)\(([^)]+)\)$~',$Wi,$z))return;$xk=$z[1];$Ch=$z[2];if($Ch=="regexp")$f["query"]["bool"][$xk][]=["regexp"=>[$Qb=>["value"=>$W,"flags"=>"ALL","case_insensitive"=>true,]]];else$f["query"]["bool"][$xk][]=[$Ch=>[$Qb=>$W]];}function
update($Q,array$H,$yk,$w=0,$El="\n"){$Mj=preg_split('~ *= *~',$yk);if(count($Mj)!=2)return
false;$q=trim($Mj[1]);$G="$Q/_doc/$q";queries("\"POST $G\": ".json_encode($H));$Uk=$this->connection->sendRequest($G,$H,'POST');if($Uk)$this->connection->sendRequest("$Q/_refresh");return$Uk;}function
insert($Q,array$H){$G="$Q/_doc/";if(isset($H["_id"])&&$H["_id"]!="NULL"){$G
.=$H["_id"];unset($H["_id"]);}foreach($H
as$u=>$X){if($X=="NULL")unset($H[$u]);}queries("\"POST $G\": ".json_encode($H));$Uk=$this->connection->sendRequest($G,$H,'POST');if(!$Uk)return
false;$this->connection->sendRequest("$Q/_refresh");$this->connection->last_id=$Uk['_id'];return$Uk['result'];}function
delete($Q,$yk,$w=0){$Qf=[];if(isset($_GET["where"]["_id"])?$_GET["where"]["_id"]:null)$Qf[]=$_GET["where"]["_id"];if(isset($_POST['check'])){foreach($_POST['check']as$Db){$Mj=preg_split('~ *= *~',$Db);if(count($Mj)==2)$Qf[]=trim($Mj[1]);}}$Fa=0;foreach($Qf
as$q){$G="$Q/_doc/$q";queries("\"DELETE $G\"");$Uk=$this->connection->sendRequest($G,null,'DELETE');if(isset($Uk['result'])&&$Uk['result']=='deleted')$Fa++;}$this->connection->sendRequest("$Q/_refresh");$this->connection->setAffectedRows($Fa);return$Fa;}}function
create_driver(Connection$e){return
ElasticDriver::create($e,Admin::get());}function
connect($F=false,&$j=null){$e=$F?ElasticConnection::create():ElasticConnection::createSecondary();list($N,$U,$E)=Admin::get()->getCredentials();if(!$e->openPasswordless($N,$U,$E)){$j=$e->getError();return
null;}return$e;}function
support($ue){return
preg_match("~table|columns~",$ue);}function
logged_user(){$Ec=Admin::get()->getCredentials();return$Ec[1];}function
get_databases($Oe){return[ELASTIC_DB_NAME];}function
limit($G,$Z,$w,$A=0,$El=" "){return" $G$Z".($w?$El."LIMIT $w".($A?" OFFSET $A":""):"");}function
collations(){return[];}function
db_collation($h,$Tb){}function
count_tables($g){$J=Connection::get()->rootQuery('_aliases');if(empty($J))return[ELASTIC_DB_NAME=>0];return[ELASTIC_DB_NAME=>count($J)];}function
tables_list(){$Ja=Connection::get()->rootQuery('_aliases');if(empty($Ja))return[];ksort($Ja);$S=[];foreach($Ja
as$_=>$s){if($_[0]==".")continue;$S[$_]="table";ksort($s["aliases"]);$S+=array_fill_keys(array_keys($s["aliases"]),"view");}return$S;}function
table_status($_="",$te=false){$tm=Connection::get()->rootQuery('_stats');$Ja=Connection::get()->rootQuery('_aliases');if(empty($tm)||empty($Ja))return[];$I=[];if($_!=""){if(isset($tm["indices"][$_]))return[format_index_status($_,$tm["indices"][$_])];else
foreach($Ja
as$Yf=>$s){foreach($s["aliases"]as$Ia=>$Ha){if($Ia==$_)return[format_alias_status($Ia,$tm["indices"][$Yf])];}}return[];}ksort($tm["indices"]);foreach($tm["indices"]as$_=>$s){if($_[0]==".")continue;$I[$_]=format_index_status($_,$s);if(!empty($Ja[$_]["aliases"])){ksort($Ja[$_]["aliases"]);foreach($Ja[$_]["aliases"]as$Ia=>$Ha)$I[$Ia]=format_alias_status($Ia,$tm["indices"][$_]);}}return$I;}function
format_index_status($_,$s){return["Name"=>$_,"Engine"=>"Lucene","Oid"=>$s["uuid"],"Rows"=>$s["total"]["docs"]["count"],"Auto_increment"=>0,"Data_length"=>$s["total"]["store"]["size_in_bytes"],"Index_length"=>0,"Data_free"=>$s["total"]["store"]["reserved_in_bytes"],];}function
format_alias_status($_,$s){return["Name"=>$_,"Engine"=>"view","Rows"=>$s["total"]["docs"]["count"],];}function
is_view(array$R){return$R["Engine"]=="view";}function
view($_){$J=Connection::get()->rootQuery("_alias/".urlencode($_));return["select"=>implode("\n",array_keys($J))];}function
error(){return
h(Connection::get()->getError());}function
information_schema($h){return
false;}function
indexes($Q,$e=null){return[["type"=>"PRIMARY","columns"=>["_id"]],];}function
fields($Q){$yh=Connection::get()->rootQuery("_mapping");if(!isset($yh[$Q])){$Ja=Connection::get()->rootQuery('_aliases');foreach($Ja
as$Yf=>$s){foreach($s["aliases"]as$Ia=>$Ha){if($Ia==$Q){$Q=$Yf;break;}}}}$zh=isset($yh[$Q]["mappings"]["properties"])?$yh[$Q]["mappings"]["properties"]:[];$I=["_id"=>["field"=>"_id","full_type"=>"_id","type"=>"_id","null"=>true,"privileges"=>["insert"=>1,"select"=>1,"where"=>1,"order"=>1],]];foreach($zh
as$_=>$k)$I[$_]=["field"=>$_,"full_type"=>$k["type"],"type"=>$k["type"],"null"=>true,"privileges"=>["insert"=>1,"select"=>1,"update"=>1,"where"=>!isset($k["index"])||$k["index"]?:null,"order"=>$k["type"]!="text"?:null],];return$I;}function
foreign_keys($Q){return[];}function
backward_keys($Q){return[];}function
table($r){return$r;}function
idf_escape($r){return$r;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){return$J;}function
fk_support($R){}function
found_rows(array$R,array$Z){return
null;}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$sk=[];foreach($l
as$k){if(!isset($k[1]))continue;$ye=trim($k[1][0]);$_e=trim($k[1][1]?:"text");$sk[$ye]=["type"=>$_e,];}$zh=$sk?["properties"=>$sk]:new
\stdClass();if($Q!=""){queries("\"POST $_/_mapping\": ".json_encode($zh));return(bool)Connection::get()->rootQuery("$_/_mapping",$zh,"POST");}else{$uc=["mappings"=>$zh];queries("\"PUT $_\": ".json_encode($uc));return(bool)Connection::get()->rootQuery($_,$uc,"PUT");}}function
drop_views(array$S){$J=Connection::get()->rootQuery('_aliases',['actions'=>array_map(function($Q){return['remove'=>['index'=>'*','alias'=>$Q]];},$S)],'POST');return$J&&!$J['errors'];}function
drop_tables($S){$J=true;foreach($S
as$Q){$Q=urlencode($Q);queries("\"DELETE $Q\"");$J=$J&&Connection::get()->sendRequest($Q,null,'DELETE');}return$J;}function
last_id($I){return
Connection::get()->last_id;}}Drivers::add("clickhouse","ClickHouse (alpha)",["allow_url_fopen"]);if(isset($_GET["clickhouse"])){define("AdminNeo\DRIVER","clickhouse");define("AdminNeo\DIALECT","clickhouse");if(ini_bool('allow_url_fopen')){define("AdminNeo\DRIVER_EXTENSION","JSON");class
ClickHouseConnection
extends
Connection{private$serviceUrl;private$dbName='default';function
getDefaultServerName(){return"localhost:8123";}function
rootQuery($h,$G){list($m,$Af)=get_url("$this->serviceUrl/?database=$h",stream_context_create(['http'=>['method'=>'POST','content'=>$G,'header'=>['Content-Type: application/x-www-form-urlencoded','X-ClickHouse-Format: JSONCompact',],'ignore_errors'=>1,'follow_location'=>0,'max_redirects'=>0,]]));if($m===false){$this->error=lang(3);return
false;}$rg=false;foreach($Af
as$_f){if(preg_match('~^X-ClickHouse-Summary:~i',$_f)){$rg=true;break;}}if(!$rg){$this->error=lang(3);return
false;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',isset($Af[0])?$Af[0]:'')){$this->error=preg_replace('~\(version [^(]+\(.+$~','',$m);return
false;}if(!$this->isQuerySelectLike($G)&&$m==='')return
true;$J=json_decode($m,true);if($J===null){$this->error=lang(3);return
false;}if(!isset($J['rows'])||!isset($J['data'])||!isset($J['meta'])){$this->error=lang(3);return
false;}return
new
ClickHouseResult($J['rows'],$J['data'],$J['meta']);}private
function
isQuerySelectLike($G){return(bool)preg_match('~^\s*(select|show|with)~i',$G);}function
query($G,$Pn=false){return$this->rootQuery($this->dbName,$G);}function
open($N,$U,$E){$this->serviceUrl=build_http_url($N,$U,$E,"localhost",8123);if(!$this->serviceUrl){$this->error=lang(3);return
false;}$I=$this->query("SELECT version()");if(!$I)return
false;$this->version=$I->fetchRow()[0];return
true;}function
selectDatabase($_){$this->dbName=$_;return
true;}function
getDbName(){return$this->dbName;}function
quote($P){return"'".addcslashes($P,"\\'")."'";}function
getValue($G,$ve=0){$I=$this->query($G);return$I['data'];}}class
ClickHouseResult
extends
Result{private$rows;private$columns;private$meta;private$offset=0;function
__construct($hl,array$f,array$Vh){parent::__construct($hl);$this->rows=[];foreach($f
as$Ag){$this->rows[]=array_map(function($W){return
is_scalar($W)?$W:json_encode($W,JSON_UNESCAPED_UNICODE);},$Ag);}$this->meta=$Vh;$this->columns=array_map(function($c){return$c['name'];},$Vh);reset($this->rows);}function
fetchAssoc(){$K=current($this->rows);next($this->rows);return$K!==false?array_combine($this->columns,$K):false;}function
fetchRow(){$K=current($this->rows);next($this->rows);return$K;}function
fetchField(){$c=$this->offset++;if($c>=count($this->columns))return
false;$c=$this->meta[$c];return(object)['name'=>$c['name'],'type'=>$c['type'],'charsetnr'=>0,];}}}class
ClickHouseDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(122)=>["Int8"=>3,"Int16"=>5,"Int32"=>10,"Int64"=>19,"UInt8"=>3,"UInt16"=>5,"UInt32"=>10,"UInt64"=>20,"Float32"=>7,"Float64"=>16,'Decimal'=>38,'Decimal32'=>9,'Decimal64'=>18,'Decimal128'=>38,],lang(123)=>["Date"=>13,"DateTime"=>20,],lang(124)=>["String"=>0,],lang(126)=>["FixedString"=>0,],];$this->operators=["=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->systemDatabases=["INFORMATION_SCHEMA","information_schema","system"];}function
delete($Q,$yk,$w=0){if($yk==='')$yk='WHERE 1=1';return
queries("ALTER TABLE ".table($Q)." DELETE $yk");}function
update($Q,array$H,$yk,$w=0,$El="\n"){$Y=[];foreach($H
as$u=>$W)$Y[]="$u = $W";$G=$El.implode(",$El",$Y);return
queries("ALTER TABLE ".table($Q)." UPDATE $G$yk");}function
engines(){return['MergeTree'];}}function
create_driver(Connection$e){return
ClickHouseDriver::create($e,Admin::get());}function
idf_escape($r){return"`".str_replace("`","``",$r)."`";}function
table($r){return
idf_escape($r);}function
explain(Connection$e,$G){return
false;}function
found_rows(array$R,array$Z){$L=get_vals("SELECT COUNT(*) FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):""));return$L?(int)$L[0]:null;}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){$b=$fj=[];$Ok=[];foreach($l
as$k){if($k[1][2]===" NULL"){$k[1][1]=" Nullable(".(ltrim($k[1][1])).")";$k[1][2]='';}elseif($k[1][2]===' NOT NULL')$k[1][2]='';if($k[1][3]==""&&Connection::get()->isMinVersion("20.10"))$Ok[]="MODIFY COLUMN ".idf_escape($k[0])." REMOVE DEFAULT";$b[]=($k[1]?($Q!=""?($k[0]!=""?"MODIFY COLUMN ":"ADD COLUMN "):" ").implode($k[1]):"DROP COLUMN ".idf_escape($k[0]));$fj[]=$k[1][0];}$b=array_merge($b,$Ok,$Qe);$O=($Sd?" ENGINE ".$Sd:"");if($Q=="")return(bool)queries("CREATE TABLE ".table($_)." (\n".implode(",\n",$b)."\n)$O".' ORDER BY ('.implode(',',$fj).')');if($Q!=$_){$I=(bool)queries("RENAME TABLE ".table($Q)." TO ".table($_));if($b)$Q=$_;else
return$I;}if($O)$b[]=ltrim($O);return!$b||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b));}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($uo){return
drop_tables($uo);}function
drop_tables($S){return
apply_queries("DROP TABLE",$S);}function
is_server_host_valid($Jf){return
strpos(rtrim($Jf,'/'),'/')===false;}function
connect($F=false,&$j=null){$e=$F?ClickHouseConnection::create():ClickHouseConnection::createSecondary();$Ec=Admin::get()->getCredentials();if(!$e->open($Ec[0],$Ec[1],$Ec[2])){$j=$e->getError();return
null;}return$e;}function
get_databases($Oe){$I=get_rows('SHOW DATABASES');$g=[];foreach($I
as$K)$g[]=$K['name'];sort($g);return$g;}function
limit($G,$Z,$w,$A=0,$El=" "){return" $G$Z".($w?$El."LIMIT ".($A?"$A, ":"").$w:"");}function
limit1($Q,$G,$Z,$El="\n"){return
limit($G,$Z,1,0,$El);}function
db_collation($h,$Tb){}function
logged_user(){$Ec=Admin::get()->getCredentials();return$Ec[1];}function
tables_list(){$I=get_rows('SHOW TABLES');$J=[];foreach($I
as$K)$J[$K['name']]='table';ksort($J);return$J;}function
count_tables($g){return[];}function
table_status($_="",$te=false){$J=[];$S=get_rows("SELECT name, engine FROM system.tables WHERE database = ".q(Connection::get()->getDbName()).($_!=""?" AND name = ".q($_):""));foreach($S
as$Q)$J[$Q['name']]=['Name'=>$Q['name'],'Engine'=>$Q['engine'],];return$J;}function
is_view(array$R){return
false;}function
fk_support($R){return
false;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$J){if(in_array($k['type'],["Int8","Int16","Int32","Int64","UInt8","UInt16","UInt32","UInt64","Float32","Float64"]))return"to$k[type]($J)";return$J;}function
fields($Q){$J=[];$I=get_rows("SELECT name, type, default_expression FROM system.columns WHERE ".idf_escape('table')." = ".q($Q));foreach($I
as$K){$T=$K['type'];$Di=str_contains($T,'Nullable(');$T=preg_replace('~Nullable\(([^)]+)\)~',"$1",$T);$J[$K['name']]=["field"=>$K['name'],"full_type"=>$T,"type"=>$T,"default"=>$K['default_expression']!=""?preg_replace('~^\'(.*)\'$~',"$1",$K['default_expression']):null,"null"=>$Di,"auto_increment"=>'0',"privileges"=>["insert"=>1,"select"=>1,"update"=>0,"where"=>1,"order"=>1],];}return$J;}function
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
last_id($I){return
0;}function
support($ue){return
preg_match("~^(columns|sql|status|table|drop_col)$~",$ue);}}Drivers::add("simpledb","SimpleDB",["SimpleXML + allow_url_fopen"]);if(isset($_GET["simpledb"])){define("AdminNeo\DRIVER","simpledb");define("AdminNeo\DIALECT","simpledb");if(class_exists('SimpleXMLElement')&&ini_bool('allow_url_fopen')){define("AdminNeo\DRIVER_EXTENSION","SimpleXML");class
SimpleDbConnection
extends
Connection{private$serviceUrl;var$timeout=0;var$next;function
open($N,$U,$E){if($N==''){$this->error=lang(3);return
false;}$Mj=parse_url($N);if(!$Mj||!isset($Mj['host'])||!preg_match('~^sdb\.([a-z0-9-]+\.)?amazonaws\.com$~i',$Mj['host'])||isset($Mj['port'])){$this->error=lang(3);return
false;}$this->serviceUrl=build_http_url($N,"","","");if(!$this->serviceUrl){$this->error=lang(3);return
false;}$this->version='2009-04-15';return(bool)sdb_request('ListDomains',['MaxNumberOfDomains'=>1],$this);}function
getServiceUrl(){return$this->serviceUrl;}function
selectDatabase($_){return$_=="domain";}function
query($G,$Pn=false){$D=['SelectExpression'=>$G,'ConsistentRead'=>'true'];if($this->next)$D['NextToken']=$this->next;$I=sdb_request_all('Select','Item',$D,$this->timeout);$this->timeout=0;if($I===false)return
false;if(preg_match('~^\s*SELECT\s+COUNT\(~i',$G)){$Bm=0;foreach($I
as$Ag)$Bm+=$Ag->Attribute->Value;$I=[(object)['Attribute'=>[(object)['Name'=>'Count','Value'=>$Bm,]]]];}return
new
SimpleDbResult($I);}function
quote($P){return"'".str_replace("'","''",$P)."'";}}class
SimpleDbResult
extends
Result{private$rows;private$offset=0;function
__construct(array$I){$this->rows=[];foreach($I
as$Ag){$K=[];if($Ag->Name!='')$K['itemName()']=(string)$Ag->Name;foreach($Ag->Attribute
as$Ua){$_=$this->processValue($Ua->Name);$X=$this->processValue($Ua->Value);if(isset($K[$_])){$K[$_]=(array)$K[$_];$K[$_][]=$X;}else$K[$_]=$X;}$this->rows[]=$K;foreach($K
as$u=>$W){if(!isset($this->rows[0][$u]))$this->rows[0][$u]=null;}}parent::__construct(count($this->rows));}private
function
processValue($Ld){return(is_object($Ld)&&$Ld['encoding']=='base64'?base64_decode($Ld):(string)$Ld);}function
fetchAssoc(){$K=current($this->rows);if(!$K)return$K;$f=[];foreach($this->rows[0]as$u=>$W)$f[$u]=$K[$u];next($this->rows);return$f;}function
fetchRow(){$f=$this->fetchAssoc();return$f?array_values($f):$f;}function
fetchField(){$Lg=array_keys($this->rows[0]);return(object)['name'=>$Lg[$this->offset++],'type'=>15,'charsetnr'=>0,];}}}class
SimpleDbDriver
extends
Driver{var$primary="itemName()";protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","IS NULL","IS NOT NULL",];$this->grouping=["count",];$this->insertFunctions=["json"];}private
function
chunkRequest($Qf,$wa,$D,$ge=[]){foreach(array_chunk($Qf,25)as$Kb){$_j=$D;foreach($Kb
as$p=>$q){$_j["Item.$p.ItemName"]=$q;foreach($ge
as$u=>$W)$_j["Item.$p.$u"]=$W;}if(!sdb_request($wa,$_j))return
false;}Connection::get()->setAffectedRows(count($Qf));return
true;}private
function
extractIds($Q,$yk,$w){$J=[];if(preg_match_all("~itemName\(\) = (('[^']*+')+)~",$yk,$z))$J=array_map('AdminNeo\idf_unescape',$z[1]);else{foreach(sdb_request_all('Select','Item',['SelectExpression'=>'SELECT itemName() FROM '.table($Q).$yk.($w?" LIMIT 1":"")])as$Ag)$J[]=$Ag->Name;}return$J;}function
select($Q,array$M,array$Z,array$mf,array$fj=[],$w=1,$C=0,$lk=false){Connection::get()->next=$_GET["next"];$J=parent::select($Q,$M,$Z,$mf,$fj,$w,$C,$lk);Connection::get()->next=0;return$J;}function
delete($Q,$yk,$w=0){return$this->chunkRequest($this->extractIds($Q,$yk,$w),'BatchDeleteAttributes',['DomainName'=>$Q]);}function
update($Q,array$H,$yk,$w=0,$El="\n"){$bd=[];$ig=[];$p=0;$Qf=$this->extractIds($Q,$yk,$w);$q=idf_unescape($H["`itemName()`"]);unset($H["`itemName()`"]);foreach($H
as$u=>$W){$u=idf_unescape($u);if($W=="NULL"||($q!=""&&[$q]!=$Qf))$bd["Attribute.".count($bd).".Name"]=$u;if($W!="NULL"){foreach((array)$W
as$Fg=>$V){$ig["Attribute.$p.Name"]=$u;$ig["Attribute.$p.Value"]=(is_array($W)?$V:idf_unescape($V));if(!$Fg)$ig["Attribute.$p.Replace"]="true";$p++;}}}$D=['DomainName'=>$Q];return(!$ig||$this->chunkRequest(($q!=""?[$q]:$Qf),'BatchPutAttributes',$D,$ig))&&(!$bd||$this->chunkRequest($Qf,'BatchDeleteAttributes',$D,$bd));}function
insert($Q,array$H){$D=["DomainName"=>$Q];$p=0;foreach($H
as$_=>$X){if($X!="NULL"){$_=idf_unescape($_);if($_=="itemName()")$D["ItemName"]=idf_unescape($X);else{foreach((array)$X
as$W){$D["Attribute.$p.Name"]=$_;$D["Attribute.$p.Value"]=(is_array($X)?$W:idf_unescape($X));$p++;}}}}return
sdb_request('PutAttributes',$D);}function
insertUpdate($Q,array$Ck,array$F){foreach($Ck
as$H){if(!$this->update($Q,$H,"WHERE `itemName()` = ".q($H["`itemName()`"])))return
false;}return
true;}function
begin(){return
false;}function
commit(){return
false;}function
rollback(){return
false;}function
slowQuery($G,$qn){$this->connection->timeout=$qn;return$G;}}function
create_driver(Connection$e){return
SimpleDbDriver::create($e,Admin::get());}function
is_server_host_valid($Jf){return
strpos(rtrim($Jf,'/'),'/')===false;}function
connect($F=false,&$j=null){$e=$F?SimpleDbConnection::create():SimpleDbConnection::createSecondary();list($N,,$E)=Admin::get()->getCredentials();if(!$e->openPasswordless($N,"",$E)){$j=$e->getError();return
null;}return$e;}function
support($ue){return
preg_match('~sql~',$ue);}function
logged_user(){$Ec=Admin::get()->getCredentials();return$Ec[1];}function
get_databases($Oe){return["domain"];}function
collations(){return[];}function
db_collation($h,$Tb){}function
tables_list(){$J=[];foreach(sdb_request_all('ListDomains','DomainName')as$Q)$J[(string)$Q]='table';if(Connection::get()->getError()&&defined("AdminNeo\PAGE_HEADER"))echo"<p class='error'>".error()."\n";return$J;}function
table_status($_="",$te=false){$J=[];foreach(($_!=""?[$_=>true]:tables_list())as$Q=>$T){$K=["Name"=>$Q,"Auto_increment"=>""];if(!$te){$Vh=sdb_request('DomainMetadata',['DomainName'=>$Q]);if($Vh){foreach(["Rows"=>"ItemCount","Data_length"=>"ItemNamesSizeBytes","Index_length"=>"AttributeValuesSizeBytes","Data_free"=>"AttributeNamesSizeBytes",]as$u=>$W)$K[$u]=(string)$Vh->$W;}}$J[$Q]=$K;}return$J;}function
is_view(array$R){return
false;}function
explain(Connection$e,$G){return
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
limit($G,$Z,$w,$A=0,$El=" "){return" $G$Z".($w?$El."LIMIT $w":"");}function
unconvert_field(array$k,$J){return$J;}function
fk_support($R){}function
auto_increment(){return"";}function
alter_table($Q,$_,$l,$Qe,$dc,$Sd,$Sb,$Za,$Jj){return$Q==""&&sdb_request('CreateDomain',['DomainName'=>$_]);}function
drop_tables($S){foreach($S
as$Q){if(!sdb_request('DeleteDomain',['DomainName'=>$Q]))return
false;}return
true;}function
count_tables($g){foreach($g
as$h)return[$h=>count(tables_list())];}function
found_rows(array$R,array$Z){return!$Z?(int)$R["Rows"]:null;}function
last_id($I){return
0;}function
sdb_request($wa,$D=[],$e=null){if(!$e)$e=Connection::get();list($If,$D['AWSAccessKeyId'],$wl)=Admin::get()->getCredentials();$D['Action']=$wa;$D['Timestamp']=gmdate('Y-m-d\TH:i:s+00:00');$D['Version']='2009-04-15';$D['SignatureVersion']=2;$D['SignatureMethod']='HmacSHA1';ksort($D);$G='';foreach($D
as$u=>$W)$G
.='&'.rawurlencode($u).'='.rawurlencode($W);$G=str_replace('%7E','~',substr($G,1));$G
.="&Signature=".urlencode(base64_encode(hash_hmac('sha1',"POST\n".preg_replace('~^https?://~','',$If)."\n/\n$G",$wl,true)));list($m)=get_url($e->getServiceUrl(),stream_context_create(['http'=>['method'=>'POST','content'=>$G,'ignore_errors'=>1,'follow_location'=>0,'max_redirects'=>0,]]));if(!$m){$e->setError(error_get_last()['message']);return
false;}libxml_use_internal_errors(true);libxml_disable_entity_loader();$No=simplexml_load_string($m);if(!$No){$j=libxml_get_last_error();$e->setError($j->message);return
false;}if($No->Errors){$j=$No->Errors->Error;$e->setError("$j->Message ($j->Code)");return
false;}$e->setError('');$cn=$wa."Result";return$No->$cn?:true;}function
sdb_request_all($wa,$cn,$D=[],$qn=0){$J=[];$rm=($qn?microtime(true):0);$w=(preg_match('~LIMIT\s+(\d+)\s*$~i',$D['SelectExpression'],$y)?$y[1]:0);do{$No=sdb_request($wa,$D);if(!$No)break;foreach($No->$cn
as$Ld)$J[]=$Ld;if($w&&count($J)>=$w){$_GET["next"]=$No->NextToken;break;}if($qn&&microtime(true)-$rm>$qn)return
false;$D['NextToken']=$No->NextToken;if($w)$D['SelectExpression']=preg_replace('~\d+\s*$~',$w-count($J),$D['SelectExpression']);}while($No->NextToken);return$J;}}$Yj="adminneo-plugins";if(is_dir($Yj)){foreach(glob("$Yj/*.php")as$n)include_once$n;}function
get_translations($Ug){switch($Ug){case'_template':$jc='-JLxQ
~.G$tc^:*f6Ny0Q@6r08%vc--CS&Gh8b}$Qqo!6Xsa
`PY&xot}e;<&L5M-b``Crayb</cLh)nqvU*9HAv|1v!X(}"l.=AMv
s<l?yCc3s.z)b@cTh3cljY,Sh-5bfaxbs-u9clZ<MRo"k;Lgh0by[iw`gGy&M2l6y&=3xkQ;L1Gy6D%h"xinv#``/1!X3~"l$n*2$X!VOOFONhM/+?NC)"prt9I.d+-<iG1AiKX.-Kf.-L=
tX98"|N:Nf480&Ns"$6^Sm%,P>Rp1TyCGym|1niUxR#8"#2R(T"I"$HT.&$."0CM
XqIOUP,h@&6d@Rsg-(E[:TH&Q5Xk#5<.4(`$PlCQY9!Q()%Qgg%J$P,]?%SmxK`!*?PA
O7a?F>$8>_"(V>E{$9#rD%!lD}!$ImH1"_2Ujj7("
2UZ*6KB!v7uHBMJhLg.bEdWeDwg}kk/h@>W;nzamMh67M@WWKi;qVttSR]I6O_#LYWX41<c*OOv.CJH^!ioITZ%7E!?;Jxo(mdb5,p?L1{V->Uw[M/5t4@SKDa#d]:5M3v!7,73uvul0g7+=6[c$g`[#m%E;1WfKCzG#@[k(6711b`bMtg7y4|@wP#@n=%;3-:/{;nvG`W5+wmMhPBF2;1vGm>9p7D[lQH-n$3$3/2-1C{gO)oNbDNW|eZ
3XbAyK(J0AadS1422CzU]hl%0D}?*,+-vp{b#CJk>!yrnDz3P@fY<j4wZOUPBB,Lc
6^(0i/9AGsjDY:8.-4J
YM%b/ae1=O)?fPDGnMUwwZ9Mvv9JXAEZy_a4<AK7_CVH-Ec1F7Xq`mO$]^I#SvQQXyq?Ih:&<B(w+*~P<<g&PBp*N&I/B3AShLVXYKM1hG>jxrl(q
dT%j17:oZM/+LcfQ=:~naHV[Ks(E|_s)[Hqq(dL<r;;=E06im[{
>h!^;UQ2L,B;(-02uYuja0CXIv1r/CaR$VN_02u4:`ep=a7R
L2KO@GDlWv_Ki"wlDnW|_Ki%an*jh|@m!#Dakx0x]&WlgV.~c._"())_6!LGPH
fAj1<_?UmnQhEKRVH_ZjEs/V#)<?F3jgdFEh(VHHYKoGA<b%>2$Cnj&pbuf;>HRYyph9%FCZ/srR%F0tkw8_z*lt>C?gi^=VAu/VOKCu`
$<L9V.(D0XTBj1#s{.Z._ouBF?E>o2t2H){"ZNr:
%^n(&,]X#}O%8Sn;Y=tcKB7#`UWwr7Q@%qhO$K)_Fs1Q@G*P(~nG_<V:6]yG8$';break;case'ar':$jc=')c0@qaMD9*70MN.(*Y1$Vy$T+N25QoYLf1Ws}b5$
-lRrAA@LE("eTX(ML$(<j.v^2lnX;b^0px?{Uls~hOJ-
ew*t5bh<D9H+|$7@&t5BlczMoLmn^j058?"Zv==PB[t
WkFy
%z<*Zpi4=q?uy
a}]AvK=)fLau-{eD@S`(FF&0HLktxDQv:aYfZAAhZ*arnV*3AInIY1Zg=gXJv>4$ZG&x`pOirmkErY`Mf#%pc.!FC9b+En5kvo]J6b:nOE]j(Dn%?%vyrq?&hYTHkD3ui!Ww?MtggAl;c3yFH3d!Lsx%tg.)mBNEUdJpl(ON$IU"&D^q<%.EA{b_M0,{C$]xtIvZK[7|a%f,cTc|/2Tma-udxcI6lOBEY^srI6c<JeiIoC52`bqHl/BZaXnal=w<2L3e!w!v&I8v1dDn.{^TU=<ITM+Z,KYnE^q7_LWIAG9%2aS~=IFDL3^?G|M->Xf)]45QmvJJ":7VG0@hGT$we([BIfef#nP
EtVntI4a@kONXF.D)spH61lm>TVU`5)FrV=fRdpIH!uE^V[zgQhz+3cK/yL#uKG1VDCY,qF&ea>{dj42O<Er$.ZQs@v[)&Yj.w2WUUC67%*IK]V2*S]A[T$5ahrk?Gt<[0:|A?X0BYbn<COpLXdrP80Tl<PiQaYF1d42dZ!l-fgoR],>
(cva{^EsA3%#cf4].kCK%<j4!32>B%U0
q{V7N$K,cd_S8_i}+@7&11LX4FfXlMWT)cwKx/$3!gs/@-x9,)E+kd]>&oF+m0lP,sMx
z(|D1S*J/@E<T1(?6tUdIC#Y.ffLyJ_h5Rc"01>1yef6rTa6xEG8}O^_s!_Z
v~SX].gIFT.i`!beNC&YIUP>C8P|j.0!kR/2OF7B2_-K:!YmJ`NoE:DxCqE?!k>_]|Nr#|(ucL&A,7VI=4Y-?)>Zf@=i5T2?]Y5T?|:Z=nd#_*S5(zB@$/?<6Lsmf2<D!%QT.A:whFq=<v$V)X["-woI0|n!hJiY_G*F>1!$
po
ZOu;B.;NEQOYEn=A/C*B`qL;]<a/2XYa4#&7%75QBu-[[][5Tq%q?)lYUu
v[~[mB/%TTjfkD[`[C6(V74
9U=xiP{xIVEn$>r6.=/g2>9KFXw=ZwJg>(ZsqIyf[LQmEpGm&6y:N0Ii?/[X8*3gg*LHpp~A}QUT.Xtq{$(l=u8.1&+]ziE%5A@i;*jg(]&38RF*k11l?Q`-[:w*[1^]%v0$6)N^,)JqGxuV:m
m]oI,_?~]GUW/,3}1:u]e-4Cc1z#jSxyhmdlQ;a6igT%KoAEPIH0DF,L$Q(oyY*HpKHe-F9`#I7U([0o^Q/%ay
h?hCur|h8k9iq-46QWpc!A!_QNUU7t"8LEP`0*h4/s72bvUN+979=CHO$P|$^Ue<IMQ%z@Ce$^uXMmIh]:Qf[+hMP[~9MR/&6enB,0bI*Q1]BDzH9X3gopyrF!s2:Xz55Yw-t5t2-YEX(ti1^Y(:B0>Qj9u>(dZO0y|(2csaFtSa"-c0
"_a~7F2S3<tXq>@xhbf$T$TP,}Sn62NqsTQFA%
9<)(iMc+&u0(WGRv!LGNiWAhC-3N$"^FSFz;<hRM4VEc<TMx66=%=T(^o/dXv2V+TAU!PH+bGX,fg?.o~m|m3LdI*Z.5_/{RXxvU&unqXq7PEX&gB.xYA+0qA<0f=l.(rhFR.?,41MqSd$*]{0-rr*%%3A1&dXZ>TECZSAOVxJ7qiVw1h73AocZ+=(|VR<q`(UVW?F)-)&DM}^2,N7vB^>]!i8U;.Df3fQxt}p}*6hxwegYkd+0-SDH-pftcl`u2(QM(7V%
H2%vEiWVb[@pYjCm/yfh50-;7A9G%^4cW3IH[oTh#;Yit.S6o0HpUC{^*5Ie!;+RSfT`X"6ru^Bq{uF=kPm,klXG&s);xOu;EZ7ULTj+zZ36RaQcACha!Z2,B+$whaR%2]<A}499~)7BTaoCn7ZB&)5HT;pEH%`FHe/!nZ4p;-7NfgO*wEeHWQO,RKAB=9:jc&ye~OK6
jEe|%$/JgRafNGHuwsTWTlLC;Mm+_U*!=p6_h7;q_1I0fT*O?e;m=z"$Hvk1$Ob^m$j.H@xV9B!acswDs5xrnI:AtVU|8dOE%#:(5,hr7p=|C3firP$Gp{
tn^"hV@E/]<i,[p$TUD574
8Kbk(kkQFOj.P9yolbc(6LTd#S*}7Gf!Yii~=(-Od[p2Mx?2#F/!^jZq9b!v>POO``]b.:.~UolaCzx(cQT5F&T-_xM0q=4H&JIvPR>3!SnxL{e4d6`bl?UdZ|:/f;,Y3}Gw)zB7ERjZ0xQ|D,iBJnVDRy&*D!V-Dg>zwNYK^KR1a5Q`GE#$5sC8ZZR;JhEv0@"vrv<]d
G!
8xGT=j6smb|_Tea?mFde8l-@2d!_D4JGFQ9uG5m;6pEX!PR6U.j9lr>9o9baKk;7tU?hRWl`zyh.Ek3`gt0ju_G#oa:2.mjAfrgauJuho(SYI:cK$S`9J=IWXFHs($k1ATr={_hC$nU,pd[M,Bgut<Oq"*#D!%|]9$xx,]%%/GQ:QoIJXZ[Y4Or;YlC[)MnScQ(M<QO$JRZD<l[iD)eh5Hc0Xmr:$cb)hR#doKv!k>rhJG&9t4<:x<"Y-@:kJ2Q/ZIL]qO@MZ[c,=D[p$5<4NGMTid3Y=1~V0e(o^MRvA4jisiAEE4:xtX_o(`MpDrlj^YNl1i<
&d`?(iN.DV!6A3*
oC5B<6ObXYKJLlW9"bCJlYb:M(,H/ME2:70q+mMAcW:W>fK1z$)VzW_-d"%S0H]ES7=bz59p*juN}U"V;ftE209YS>3yQ6$rp_>REgSD#%<M4XD%^
!m=i0:a>m""QxOrrXHvbwf7eWhdEk49q}omfg=.]D%??e1D2]:bBx/]1<HS?Xr~cPsZ$ctO;ua3,P3AN_iq*vi+#S#1]0PJPy
a({MU-PA=i_A+!FZDg+-ju.&q5hScB>HN:|g{mv&3q|ER>64~hekaM*Cjn;kRb"`+:u@c6!y>WAs%4TdO0%38fPiDN9CDj[;B@[:KO;3"<C+UkFT5]M9*Wt.t/JF{hMVjU-lRQ0?+[V3bN6)0[=pJFtOqih[P!@04,.`*@2p%v8mMU8n-;3GwX9#-Ex.Hmrl`r9WPsw:Zk]HP[Srpi-*!eTt5FqadtZ^k$,x(h&G}vHl**TptdgXi"VAn5RC7pFehLVY/y0>|rOJ$?noTZbSy-p^[Mzj$47lI_Kb1(&iBi38veqb9.jhTZ8G%1.qOfEY^Gxu;LA`l^wUlRppd#bXnh"pZ"E(;Ni8<.:[^s6`0>ir35OWUg?=J`R]x786QO7gg-%
`t3AdO?2.:L")c`bGE4*,ab.N)
#g"qP2k~N(URF_w/`WPAd;:E(l!e^(LebJWT]-GrRrb7nHf:b{Z?JnlZ``fHtaB]vlESQGr`c:Xtm7CHq~H5C]qw$>4DmoZvL_Fpn7Y(VyhMt]-pQhHfOtbx..j3,MaMI1VRulv6AxraOy3&EGKmDc2;#c6XYxZ8L$.F"r3wn]ij)|OBGK8tlEV$dr$`o.KH?T*kU0T:mHHp`yZO3_rM09A+I?"BZUcmOzt..r&xgU(4m|PR**#1@7D18cAJcmb0V}GOp7A2
on^GXB2!|IR8L]mDQ6/sxZ$m|3r2%TS[$<+nlsQeik?8k)"L>P2jB20?(X^h,<PZ>NOUQapj}!Vm-oFqdBrq"/E)d1"H-M1[HR=S`&?*>vDpN3yVcetc4+;o2LYO2lAP!8B]41P-@2o.rhu9Iy0&&/]p{mJZ~Wu=^3OEH4Pp?^Q:Jec7W?=d66GjPfYX0Ea:7`-Ew+pJD6aNhG._*O[,D^,R?+XfcOM9XIvQ9Yk:})2>7,+<C0wn,V#9wJTYaK9GJ(bun-p*`RO.su;[NZu]K6VGgz!hanB?LbnbXvwf95[X?bC*SuGjSy_HW?Ecdu6hbsf
?ti9@/Uk3C3cMk}.~.-`TD)4Ym"QVeFFyh:!msjq5P8b?x/ST:J)/5Pm<c*_%:g9R51bP9!BUf&uWWh(w"l^m+[
Vn3g6I^Gh>WJE?G+gvD4?^GA;cdt+!H0<w#yKyAH5r$gviB`Dt5E48LK}Io-Oi52WVQ>$;mY*-wT)a#%U<AG23)mW#9#~/([G2&X!ix,eLSp3gY=4Alc"l$;&TaN(T=Z5t]70!~Z+/c[a"/08J>rzbd/j9gCE
vINTit%s8]NZ`xu8]i!t?z#i]nr*iq5(4B2u_*tmVH#bsLa;q]_o&]U>V+{].O#rTI%nk%Jd&L`Leh=c|(Y++Dh:rXmU6D`3KjwWCm|qangEwV)iKyaMhtF<]P[[ZRp^CIeSH@T-GT_+h=MZkWYHW5,aCE&0|3"D!"+WAR"u
/0Zfm`/6[J"Cg(`/2Yb5xU%)6y*SjNRjy,r)(=5xg@BrUlP^::qEiajluLn-4{av,
3GrF"xbfH?Q@H/WvBqd$sv8<1G]DhqEZVl/67<ovqXk/;JAyGQf&.2
+=3bUa0nKT.H%?qSz&k]&:8<H1|hM#x/v0%Z6R:]
-AOP5/S0b;
A$$[pkjt=+2Gpn%c4nuz)_fRM6y]
B<o2
xp4jCmiFxrIgUi,Qgy"^W-B_
s67cE~/b
~8y=Gn[j}jd@(4!RPc<.t6XD89Y9%"Xq(Mq&
otGDJ[O
x+2XT4VPM%Q]/T)IwR&QMqGPXPxd';break;case'bg':$jc='%evF;bpD9,|?Yd<!dO[m}k6X<[3:,@Sd8#FAiHv!I9X+}vc!r3O#IdH)yB)pTD(xe#=yFD,b=XM.nl/K+Bu,u,ty1](=Al{3lDlVa$a@gbWK+
eiuC#yxpat#]%rm<*d6U[wo=H@}ZuPT!&6]:CMaAbrld
!F?B
l,JhHkzBmAY0v9U]JkHT>6)pI:$]_wn+G^+!xe9kBgM-;>Z-B?1M,;gPR:&G)Fo=VXRv#VZl<ruY(9PC9p|1YI"-
Yj8j=HMIe]][]=w
QC%}isC%O?Xn=fA-G-gNPjXp3~`FW]v;mRy#7{c|JqFfwlyHuO>e*3HD:^&>f`<JgCXyMWaMW
KA1jdKI4a]yp5vALy:cdn]K$iH_mG8!4s/y(UXs4H2nucFtFsou9_oyfeFYDl{;h:!R.
n/_eDu/r)QMZC/YJQ]*?#F+4W@.w1yqfy5d
3-.(c
ol,Q!UkL5BUnty/&aTB>`I;7+^j5Yy[qH<Y$m$C!aD1DxghqO.lH&8>V~JbZ&eWwDm&A3P?^RRDLJ=
$mb%EJdnYCvrU4t>
A#E@9P~A/;_Il6HekCFr8-fkwfry+K!XUV$C9JG/F&s!CR1SF!0?Toz
jUUYOq-eie%jA4AwaORt{j:6DNn
DtU_B$6L+7ze_.4^%ZG;yK??[j2(d],v~kV5meCdFhIUjYXP+N55-)+vH,6@`,Z*_W(=gT~H"@v,vLvO{lsP|-yokURW>(!q/IZorfBr56n>IP>6Y7DSfB9H~3}CM]#1IT
o&f-[ktbPy.NNv^iJ.`5)bQ^QzZ~P_r?yM^xrbv!,2WD^$/LP?*K=a`i^&O)R
UQb]=)2S`a3zW/UF*be9sry>MbKzL[`ia=ux^ujcmpWPK+z!u6l;:$_W`=^
4)3.[7W@d7X#4a3e_Ri0omkX-`Yf3m/71cGRX-[B(8uVU.Zdb;D^K)dzv^xz
WgRdG/r$aN`:d,K]XECrT-]tao#;Gqs/)iS$c"
qUr_e1
PMEx59j@2%>-2l;%=1,kvd~LeyR[.cP<M-Gc`yzr]N8i;)/uy%j;g=BV(])xfaG0L<!/o6YKfA?RAwF51HN<*5-W%!]`;90]~eo42_R6[ZqateR)}Mn#kASSL;>9dmG2txyyj[/cqK0(/%[+HKAXmCZ&Sk3UY
)Egkr[`0$?/1=Z=i5;U3yBGJo)-wt5&lbN)E{R#=-])dPutF^R9x8/VoQf8H^,/jXX
P<a,@xi)b(=kKZh%&8#N7,AM/ryQsGAcMpla8HK"<0v`4M/<&]p^;&
W%JRlqjZYOlRL/7o?IZ+(,"IOXiYCx6==9!
^RM;Y6"ISVoU^!(x<`CSi>P(G.6?bGYgL`m,gh5pC9/b.#)3NRa?1Ac_&"g_$6!mv[:NbWhB2x[!}^h-Q?}?Lb426ZYgm6KI>6f*CLPf!-*(>jseGVv1AG
h~R4SvqgbMnNkvRvg47JtbjXyKpN7TVcVcK9HDVFLP`rL46pAm7#OT1!<XO]WwfY90</#.qABsVh!<1r+"/~.kf`U9;zfaU-Uh/7U|bs>!+MOoKiiRK|l0XJN$UQeNkR3H4<F!rN[GV^I<l0j^&8oFQa0/ryh_y)2t,LAj1.2Sy9i*02@#th,bgYb|]{JN?ij8fsw-E|bLD[S>Pb;6DHY0,TF~D9p-jM]%E^6>.z;$6mRHl8/W[O;F]I9C+2elj7KHwJqob9xlu^x:X"b_&
!:^ANw_.5pPW(/)KhINmBFpJ8Kh2F%LvfB>)pqU2Tx<04c:w23=5r0N8g&s+70/`D)iR[e[G8yh>u9:^lAI>6n3XtE,|P|=7k?Zkk"Zqf|,`Lok=J2^xLC;&PRckM_dS0Z
M?&O6H@yxk7+#nt=m9BdROL^;(k
7(o!n5bM0bn@p(<hz<+@ae}HaB/1=PnpbD~k-_vY,pa3KEd+[9:0?GF79g=Z$iq$r;O&F.yPmJZq16PKNvc$TXOBL,M_d-hl[T.K?UZ!nvX6m&.n$i>.ab&4)2a2-/Sh&2.1242"}Kk"FPg=()EArUdKIS(X;N<:I:UO#w@[uR!m}/=;eTFi;
;;`C([m9>!=v&+PwM5~8DZBB^j
8pb~6NhP)(tv;,*`:2vg8o>93A/A(R:lQE4`Y>Nob[T@rB]LCVrW0`+6)RmNK!d"c@QeWr<A-y+D%:Tw/)i-Om_4HQ%yRVe^_[_9Wl]?:tMk;[Y
saSGA;BqvR
3lc>`hQK$0YX7KOGxW}?eqG`s2Et>?:?eR-"{qJgGjW,n"92&sZ09n).X]=t#TF0Ac)wI9p
Y`?Iw=#TRXDv#6sJnst$DU2;O;9E@5VTA5y$PU,kvf?T@bf`cC>6iNDu8-nwuCYu7o#!O/X9QF3QI.aLXNW<HYF;R,hCuwjxI#~&BQ!*Y?,Gsj@Q1W~3Kpkqq4J,`aw^f;35_p(X4AgDO.Dp#$6l}.1"REjh|[xTO#`N^[2,O)NVvEih{WD>?"+JzclZ+fKw$+2=/Xw)Y1!.Wt|Fg<tA^o!B-9eqE;.2DI+P]P47q&>%XOediXHz&4R_%J>%ElxF{@gv<=bBEwEU&88UZ?Z1NqxXm0=j4UQjM3C8d,i=spO);NuRIDg252"Q67}mzpl:U!3UvE[+&3aa)Y+S)7z1|O>+R3K>[u>-y03+1iLJ^KA0.C9pSOHSUP<78eygvltr70,Dwc9/a5W`&CcQ/r:GpJjqkWzLDQLhh7[r;"%NVe0%@a!/vgH,ftv!"o4JYI,L7me,kbQt(Wk
IDb7-1FrA7!sPNi[bRKUnHVQM>;mlpnJe>jwcxzf38f;<%p6VU%;{1<;Q5=^cHY3s]m/NjU4KS-Wn67]s$M@Vq7!to
ik=7jlj]1drgX`42-_Og5;mVQ:82SXjR5ejsG)+ZDn3l-?Y%./7>r|7{VT[:Op*+*ewEvsqHY0JI@/;R"r-98l4P7K
r!aryc}noQ(rbPZ("v]A}Gp]zsHOb
xg%Oj(Hk7dXVpAcfpf8KjUi`<$hEv*]WpJA4=?Ly[Q`P.vXc#CcXp]sVS*+Od-nXUH2@,ed5l(=(kf2b_TG"m1[NJ[{@d#Q=1H0(l!K>@5lfn$|Tr,Ygq!@_FjL4^vY7q#Oek^64yS2+=b/IT42W+u&J=<x1NOxaG*)&2EG1"eP#M?"pEf_JNh@ZR)*@x!^;CoQE]`/`ak]"f>?L1
>">*:w+,LcnmJ`8hjAlP|2fT%
6)6rvRR"a=}mbrN.hoFQLPB&TYp`+6{@4^N.
4?;EJmDnt:i%pS_FO>UOu/Z2t&aGAeXLN4_.f/NhX0vX!V"~n($F%@tzUP>^rN=4@=UC%A4uCz"(#B=Z:QX$5Pck>6`G[zu<BQ/dTaYJ&3m-+Y39R=9sH|kXR5%~t]Lp]>DhE4rm0>L92)k!uQpN;r2He->GFEF!H[^dYC0+kYZEJCsW)+cO";%_KzKw3u6XQRa{&1k]w(D1.f,$`mb}EH<QX(x?:9+HC`3)C2"?aKqPRUej=K_BpbT{Z>$FOpApUJ[oD3XCZ|H=yKg<+r!q(1w#IS=7GZs3Bo?b`B&g3,4Z@!%(kO37tsV<NY%cf{I[0^$">*0EJ_MGs}IXm+a@&PPx]Wc:rh^zt8WlsZ)>t[g2dU?!3c9Y$L2oLMsuJ][~jkW*DFohA?";.7xK`RZJkEOJ!@GRND3[!9K;/}h4e7t"e<VE>:[pGn)Jl/7M
u]H#9&G,bgtc&Ae^WQgKGddou9HHIjzn>!b.{*t&h6rpPgC;&%1&RU/5PN7a/x]/nUXq$%dI]PTb4/SLxe=?fr/2{dG2L6kPskSy?HEC!i44~GR5XwaFKiMa1B"ra4vs`>z+[y
fs"Rngq</X#RwyDVXQ!~(C-~;(W_3Op3FGJR+05eJ:9YJ>^4!QSofL&LjoA|JOG"bkVJ2Jb,5]=v.QDz9gr|E:RxSOhoCoH},on@gCki?jn3RK^AJ@(Qd&lQ=AJ{PjOQ5vH.`b[q/(b,*aTYE!Z6)"*9cG&^<`HVJwK14Z^>;CX/YzS^5.#1Ph8oc7emV/6A$4d_Q;mK$t@Q8%PO1}=SH[>?n.Ggj*[GElxa]vIU;?cX)?Ob-SWH%Mp`_wTnD[`&PG[b+0k{eYIz.C#Lw]M?]]!qES4Zu#<c]Oy?:.)->#>L,X8gJVF~1@@Y!fp;Q|N.wS^/aJCd+1iVn]w[CR&}Kp2$<xmL+tI.[@Py#7szEfkN:@B&5p@[Mb+pw40`6v()FF9M=Q6.e=3lKL"KZQoYr&>#aRY]5[0FF>3E]YUJB/:PnR;+_F3Xm^TF){6&yco5HBI>N7y/n3hz=?J1d1`X3n%g
f+ta=m&K/elF#s,S+*$t"wMJ>@v?@qFGYAdXmVZn28^R)pIB(ayWd;nQA`kS-7!rs7ni_?pmOH8h`VpY1%/@*V.(Opcf|%a!.F_UhxObPbm*#`=Xmu19F0bha`E)zMr[yGG(+r
t.`Oq#%GfO_,HIEza`OO`i>&Mo&3t.3)FbcAjmK,NQnTE>6ou[16$=b!=mCEl|fOl,LJ=-ljWp`o]`2&iUu[D]K-Cwy7or;mD?+[bIF+B)X|wh&~#5Sr3anb*1=oEo8@>3I8-dKvci390lx/9cKYr`yC8z"5]Uh;6XWt/,gr2V5aLb+?3I1DW_I*A*&9m)Z=UT=it=`1(5t&+X:;""52uzYDd!4!S]lSm8l
4?hNNBmS4~)[pQTerx%G4IcfULMzW&y[7"SPtME!yv$aaY)5.T]45>f=+:0B1pC9k8=;2*sLrql8@*78*!YeD9S{b}NHw%#.Rlq27}w<rO[4nsZB9Cy16prOK^EjVT"-Ei^2[h>3mS=i_8%>Rl!Ah$cMF4CY*v6&MW3a-y#?"B_1S^*c;0*=t;7yt,j3e`3f
N[wS>683"WmuVQL_OspSwH<I0_3lln45)UW7JVCuj.q`L6j*6Ho;9mYvd0J+a237OKkc|p5P)LosMuO/R`^aYA^LUX<W
H6UlK5lAN],$W
%%wJZ8s4R:sGSqD_n!@Ed7tZWLyRoie;3lhddTvAf^
|kbAwC5h1N*e)0sIj@^uz4rY!(jO^3q$jUcC8l3!Ip8BOUnnlM2fa5cv9`:c:;[?`NbK?bcEQPn)55rN/.7mJ"(t>Ij^m,6>[xu,+DG:VT7-;[JKB$!:rT(u6uc(
M<?oNQL%7`>2f7%Eyoj$aMtWN>';break;case'bn':$jc='$hWVkaMDG)E&/"Hd0Gj]bWqWdCI:KOC2t:c@ciwd698"=:7!S,08$S[e
C-QPlNGI-zRK(k4|P@R.!UQOdG_Pvh/6L67uhMnQ#N"
ACv<t-kWXVaty}8tam]n
t4K-SLGe?LGdb+DZ>5*[]FE
yw~WByme<2UFGNt.(vmPd`f<<,jz!;lP;L4cf`A^@/L[NA?kY,{LIKb.nm/:Cu}Vwo5kPmgxdd7-ZQlN-SVy7v}%d46d-y&?:G8cDw`G
kT8$2xy&M|^r$H$.Odrs4[&o71"5Ryjx4{Wf0LL0[iR:xL!m2G<)74oSg%-%JL0F];,{^QrytOiDX;MRfl87v}e{3x[E(
X{oeq;E87>1ze:?w&Dv{tOfSgk>Un}d#a3m
L6?n$IpFu8[!,sWHH3MacCw_?ntUq#bAAfccsgc0F}xYxzvhkXM@SMy7$ZXP;bR>P.*d?E#>AbkHt%oF9,DxS&#=#znOVX&j6vht>.L7iX>VyH5I6[/cA7;lIl/HA>esZLRK36%{k}c6bhsG!6eLj-;w)}6NrPXd*j".KjdTxcj3
kLhE%[AwC7fF&!BcspuVs4d_#D
![^"#R+%2>2Ro)rrdS=LUC)HHKv:um.^#5d.X<^6j>g4y]dD3-85<z)k8kZO$
f^B}Xz>2#*]M:6WNen<(2fv^lPg{)PevEK"jD@&00",oYDmon]kPSlK>Vp)|=KBS2-Z.6>izN]F$bNv6N33M6(V[
F(zBgUe)NO3xT(TNm;*Tb4$mNR5.@&00!t|=b^1Q*va[-&WQCjy/_C}Y=z&ugv
K.!M!jmW:Bb.@cjsyUAhP7cE?0D;$P2!Pi#"dF6<>0e;8IbTr#Ac^F]M/o8L1H4a>fEK-k]ofs>LoeUj^6$Tu+7QKz`*c%m$s5E+U**I2:<zC}rB.W-~#oFGS7Q8Z:4?8nZ2u1*KZ>k)&G[?/Dgp!L/)^@Oy:WC)s{Q_&[&)Dj6LLt8~UqEjpX9<dA-`61j)-T0fx03/u~B6a[H)CKcRfi_TPR2O0y9pWbAftpVepqq-/b8GDm]Fh>quqjw3!0O9e8[~A()Q[kw}w|
c,co2m)Fe*$#,[wr3(%#cHk>
D}vM)k`M)*oJx6ZhdJZB03nWjLA4x"<%OR&sFW_ZA}tY>:S;PKD99Mnt&dl=-UrHpY8b-TQ`s

g14l";nr#+8Xw2[@}@4vV:QR:u+yOGk?1Gw&}6Qf0$E2@*y6YV6,ZBG>B.Mn]DGM:P(+%s]LAAOk%Dd&g^B*}2z$Q?NM#_>+ZoYc>"oPYSB2(!?3@]GqpRYid1.:,!~evQea4H%ZU&@sL,*!B]u`0>dJZ1[5zTdVv<c#20/k3W5;<b.]v*y?OIqw!
SeOFt1~UBP|eLK@6.?{R:
Pr"9{;=-)9}X|*|So^nv%I|a$T_B4__wf%,0;_Yy!UN4gL(XlvSsRta>o_6cp2~c6nG?V"|]wO^ah(Lp"YGnaP;SB4=5/AA6MSR^0^x9aQ+6D&0.In{M|C])PQ*+RJw(U=f<?&(m/CW-kXh[49Oy36u!
g0I?k&+Mor`9o<a|;(08umR>`^
m)[1L_WK*+.RHT*%Sw4D^r/V~LUG=
e0KSehr0dAmuS&4A!moPktsK.sG_>*#`q%`ok^BpETR?wAwM=,R^]os=o2k`[n?hUR=$fReY~#o@Q3!FxaqN!Dncu;<FXl2m|W5K)Ht[3WJi-[rj=@(ioobOq+v
u$6a&%=i=2NU;aFL^WDo8WxDo*8!6_-)(#6^>G%,.5^v2>8[,RAXsEDc&Y:_yuE>xQ*5)4D[Gt.
!K$h+KaA?)FgE2gn>Tq%@o9Jh]ubL.`k0SO!Yp/HLy7_e-goT8QsL/jb5"HO=nP-!^*$kQb7}gzhuV8s%h)iO3Xvj_%`dMx"b"m*aQ%^~V1P8/sp~h:Xt"?+g$Pkj9av7xJZ;>:^-=X3^B[
(BRl&)HZ35
l&6`RpjlRJIEh]#CS?8}0K,vCmsiGyMC#k)_$*hBa;&;T%?
ul)3.g8[]Ibzic.sT|l^]_2vQ.KYv>=m*9dp<bwi$F/GNwm?Q>>8!o[X4:ZUWVRC_y[V#M%O.qVSuAS},loQgTB[<=XDuWA3U(uj)*Ob^&NM@vwVdoUA$*)X<%QRTfvb?zgnn!];h,TNvZ@VT2xtl]aM
ql`.{[4]GHHp}bUD+a3!)P2:X1=%PAtN[:efF)}BcyDH<Q*dKQVR2k?s}B6,9[H)F2DioT9ScYy!OYLPFF_8r&SsfNB#F_Z`(o0=!!2RG^dg*[4_=(&PtJ[T$thcYdN)Gion~uV>8TJ0Tn2H(ROd0GJp:@)hBo+n8
-@iF2];Vnt)Nc*.SWi;
23Qi#=Bsi&M!{LE,uc,8T@R5Y@7v@)kh7ZVPz)
"?"w4:c|tP5x/WyJgY
0U{q/KENE?K.
lWENx4*/1;]eQQ0_E%:H[yP0=):NeVEw=h3+/!Q)JsL*E1t87Kb(LzoE;fnObBla3>"^"N
Kmn>(O!$Dbm0{?YHtWGOOxs7MbbrI:`;"$fb7LO;Eh~32V&bjRnZEI[u$I^,=FZfQI<p0x2a`EzgZAoMtj*0eXF=38aSaG*/]%~Vf3U&>kV"t=o*y]O"DU<POHlNF#k$x/&C(1F4rn}?-<
G-#l75OrycEpEe#zR%2ao`a%<nX*5zI=^rAb00<~]DPH4@EIjgl:!"*L#:>%Yj4o0ubIXQj85!m~Xb+VkpeW)yFyuNZ9&>
#oS1-7-M2l15Q%9?f,?JFcZ`L
,]dE;hlkUCR9WZwq&$X86gQM.CR6wgxe>K:yKMYLrxg`)Gi]=KXQ`-jJiDb8HkFXcU"8n>2nAA+l,sq")a^)tppD|%b;{W/<X[X*3qmn?e/Jn?&
kuIf0T2Ri63yM-c
-<lhtlyOdW
-b![r?S3q0?wcSS:OLk&H|#/MD<Y*im59,LCf}!al:^e^5n=&SBld>Zw_uIY:Ucy3%>8$Gt^i;U2t^y.tbguVsG,Se2sN9S=bh=Bxs;488qd#38a=YFaSq/3#;XzXLBoMaN`Vqs*>5^KS6FyX+`bG?*dW@:{mIf2sW..1=nyKNQcU#AS5!a}:M?CWN^"yGgWg&=rbxPTJU6|)cq|:KVljqhvl5C#AioS,TlU1K3eEIs7$ij[l/Tn"MaIh`;blp^R!)+q2(XjrT
+0I#B9?k&V,pbJ(IL/S1ohT
nSf*ddW)%sIj>
b!IpIU9d9`s-aquPAJ0Qe3BgPp`,n@H_")?]}RF)9s"j%Ly6BK;#dGgNyeg2
hvX=.yE^8-ltES$i.smSj0>[(C)A*QXc"$wspo^3X6"KnKFD-P>]9#d)9iZCxoE_4y:pYd5u2<E$n;*v4R?qas<=!7=A^3.F[|Uj5#O;034_p;f=_-/*Ad"p.:m}:8:5[G/$--itb4dftrYoN;3;9Gl7>HYgI18rk6@Y9Wx?4G;!%Kjl[A_4b$falmyxxhRt.)*dr$3*^ld<:v=_X,W(IZe3vTqC6H9Z%R6hjH9^u=;eeNfv7O_94=[#d.`J:<"Q>{,41b_[G<u?]zqz=v1T,;W,%A>+f@s*0vk+e6GU@T0]e5C%BtRO@{<Z-!Cp,Za=l]xkC9_>>r#{VAL(.<Z8&<QB)ODE[h/g,Qh_rYxL7WV7pTId>C2/0EG<uGuh:=x{lsO17!DvI&#v`~w:J)f/]L.#,)`vMNrB)9bKhGEp,&DHutB-ivy&/W`ma,t&>Z={Q|6{:k[VVvo?&<ikH?"~0}*DD41
Ae/.RsC,YLrF]7(c,ub=0mg%`M<7D$`)JJ]grkI~(v5MOaob6DN~$>22Ps*n5}vdVu6Va%^</AboC9YEVWRvnX=%7MV-#EB8Pxme$s@//a+;v>vHxh@MN7=)1It;>})e]fQC98$^68jj>ZU85QSK4$J=C%ZaJM_|lbwMQGeJ#!26`k3g(v,D%+)_@<2<i=sHs?q{W20doYHG)nH`9ut$vm-Tjr9oKE5`FnD<pBkwTZxffK+pO2l)Xc$/B0M,1}mLd6]@b0esk~Z
yA)LO.X[j{krY=!=K#K!sVnd%Oss%te[M}C!m.]4B{K&xrZJ`<$YI;cXKh<xBq*
BMm$:Wp$V7&Y$P2xgIQ2C&Jc+WS8`]mc&
<d[.=<[3DuvPWIPK3<9dj%02"qmJ;=:NXGZ1Dm>mQ~b;9fm<O]dY*7D|3.ism,/40KJbGUyoH!kNvE(YeRNGLh4zq(Q$&[EI$)]}=0K&B`.m/8Aeb[Yp<,hOKLl
)0f[ChPwRNTyEB"=a`)BJg/O13OB;fs1NFedr|1d]%m}OG=jfTy;%Dp_`4QUeE"g[o1%5Pb%)>KkozYI^n83SS6/Cs<$D!g248+fR&Z[KlXn($N98DV`t&A/B-/2VnF(+01aHKp)r8p~B5KbnS"Nf6TKc@8&l#dWEj`e8:vOOo]J0X`d?PU.mzN^X)8`h.!~(?.tr#r"FZFJWR42X?F&-R3=@)hF
dYUG;bw9LW%@u?kVInGn1fM0UE#4#Czj&%YfRQ%vWTU*0h(G["qBAO$fqH/]WL|.yciK"9
YIAIM[lV9}>yx3.c`N#>?3WM<mJ)*2:|-C3EEiM"R=F&/^_yb6Tg7>)j"QYSkC[%&;jg>r-NXTs.^((mr{$|92
,-xZ6@O
q_0>7
t,[%8r1
TxVVRF&gwe$saUUaSZi<rpK&3t?`xv@yPCjqmgy?EMa8qRdoyOPbTX=F&pm8qnkqBvLdw-9+14<</AN!F#|oD7Eq}Rn-hd
%fM<A3C/+RerQ8H!5u*[42QA^V[Pxg6fb2N`Mr-"M.Bo#.dEf{c1.koxcf9e!oG@n[4Y:x5oUg2,z!jys@W`W.-Y>;fZSdY%t%d~BA>rkx6Te2?
e,aWXs]O`
_-hnY=-Qk&pY)MfG$`Y%IV/zMD&-ClhWi0ahewWhyaQ0x^&[0tuyXv,q#HPgb>*^U-*M
^8N+-<DvC:sZ87o1X`/>sQ=!A:}tOblLx1@IurYX}.!rbb1lI?A2y-D!;xKQY]fZ{siTFimB^yRYoek/bWP;~N$t^9l=QR@-:#FM@KfU8Nud~B[Qg;mQy4$Zg:[M0y<bnW&9Ir#TS5tGFU$r[MNS76;@P_9:{XmWT@2ko`[hLHg.+V>=0TU_.J@Gk^i5K)[M;S*2Mwjf+84uTB|1NJNE7d(`9-bo]*r5SC<R-=}Q
((8b<?<R=>0gbG0K"k+`767zI8.bXE8~c1C^(!*.:ys9RCnN`N@Qrxri:zL,!K$1-w.5)PtLv$i?d5CtX*@>%#4GgoquEWdNz#e1q|[N@et!8=RfjN4XJU=Cn?
N^b/S*)C$o]';break;case'bs':$jc=',ZuALaLZ;2M0l8$*Cko/P!.g%k]`xLDFqF3ac76uu(GGk;A7TeVRT"gcapr7>OG_8omy5ybEUV{4a"Z!MfKn1[D1dKx?R^RKC:Ku:aIr/H"<fa|3iZ{E4n",k[k/kx1]nyT#SB`x#X0amGY%,(,<=hLF*p*W;<a4b1>n%BQq<pU>EsxCxCa
QA>A)sfga_>)Yq>Id+t6=i@U,J[.]Gos%x-IOwma$tE[+cyp|
u49IRVnG_5is93fWXZGwxivAUz!yDb)t1Gr=rcTXKnfe;oh+`&dG]8"nm^Ldad{(wiIyF)L!PD7;ky~G4u[JzDMTnJc]TOag-2"
6R3U7:NO}`$jwPBE7DdlH_/ew$"&ms=77NF>}IPH*vgD4A
^k/=6=i5]`ac7r8mv|E.[rPWz&3#)FaoD<I;iCdk`N_6(:SB4^nA<g55TpD(uJ<hnV.ngZi%Lh..Cc4hug.kDs-wvyyikckBnquxm5rWVh&xG[_WFSaJW,v&u@vkh6P.nKh=^q`eo+g-alI>p!Rg2}$S-9MARWEa`6Roo13Sgd9[!w!`uFqoDq1rfK7qgnAP;K/kQ0<)4nXI<[4l={djI6Q:kMgu)Vg?4*0*K($il2i>29cJ[GE=hi8Vm#ZXZ@BSg83`BqWQqmMKn#JF6bc`AC;2isE(K[*Q5zy}7G941ck#Wlx(1?E24}oFVOW6IWeCFvuV`NiDyZSl_`Pf0lKG+A,}P`I1Zze1Zp]A?|:r^ys*y
5]K4>`R{yphqyw
j[)eRRkp9bUu^5y!_`9dk%9K$h7*!u{&u!]yyMZop?O/=Kb+%IdWlQuFU]00/iWpTv:lb2m*H_I?x&EF]aeKYy+oUNxhl*.O7e(cG<{qY.PGNaQ[QHJON69mM^pAwYl(00UC"JaCJo!@?:Mu/.+vY4K+~o/O`/S]Y>SPJKW3.eIh8+<i/a[-jjCf2)vDla!Xc_8$^_b$Z)&fcaSd*@px:lDpmn$rf`MmWX0O/&Vq+nZ>qLnpO#PUO?t!A&%%}ypZ$uJ-6x5WHSNcwR~%zMGo1bTu*/s^tq=bU%bHTvguNFbm,[q1UIyP-)_i.uA&3O1>Q2_UUk/due?-PdW@AObE+q/
Csnw3Rzh+b{J#g;bA?l8Up|nR^lu0H8I_p8t9CFl1$_c$:Sio2VM{,<9QRrF/eu<wM`qXl+>i17EB$g5Yib3E[o6pbxO1Py/+)Ae|]_sBrUKz6jA:/)"-WmC,9!iNqw@]jFiy=B6ce~+MMZK]T[3(>8cld#Oaf)w6H>%r-2!([/!TS`
*
:a72+@^%0yvsU"(w37NC.u!jt(!Pz;vUZ)~q1iu2x%nHR4{_7XEt`bT,KW&5SYz)SS;kBp9!HCDutuJEv6.xvWxj6bI/:f!x)o")l3CWf<yg
5nH].As@Om&!tQrJ
Nv`O~)tDb>[YCmzj5>B=&@bJ~TKd>i=/^`JRv3Fe(#{
IX~?4TGYk@S`w=JvlAf*z"bd:Dh^;1GW%TZ+UO_rcnJ"KHG_6M%dxHutg5e1@f
1z.hC0#_
k+D=lb2pXg/H@bE^xHEGB)]g?I}5([Lgcye0oy(WiMW=c"w:!6;6"[y56y!ac*r*klZ=!]4Zqx[%0w/JikY;6Q_1F-22v!-
i:9mtigQ79$w^#]O#${ll#
uM=9/C23R<hz_14~`d4dK;vCJZaF)o[8S6YIa=bcOXNu$6:$`Jw2]7fFZ}iyN[]tYU
R+w`y0MiYVjYqN(rF[/Wx3-X<S]eUJtI_f8Gmrq?&g/a5iz"T]E_*J1qa5
#FLCGHH0jz?&5QkEGn8in,H@*`pvi?fXF<@([[S@Cn
A3=Erj|83aXBq1OwE8CluWc&GDl_RWjEe.pnqSF@=R#){L%(WNY@0NgjmbIs62xkt:j0|OMEa#22rR^uf"5/SawQKy4P_b5TaQAjt4PoZN.FuOt=6[-C&`<X325#2#l1o%+MO(l[Amc^}M`$$9kZw]t:>22G4^Koko1Uau}LNo[(7f`;~/8l!:mjTqnQEka+;&Sejb*lDn(B]1/pxHh4c7kG1=
_ZijmFP<F19uH<`N3Bf,Kn6M#=Eo8&*~8!+LStUo.*ke;,G,#CdD2n2IDx0HVaCd3g@lw8&
%^jD0^U@TZy:NebJ4z<gqu=T&XRGlh?*GfZaG{k?j"TX`hi$ML5iW(%OoFEwMT)]
1w$L4,HTp<Qdx"hMcKO8E"Q_)GHx:/iPu_U-EG{MD,#Hhk(VfBzZA;VrnlnmBjIbkM)VZ3.oSR3@x(B@0D<mm:lR(Yx$th2E=bf8l-u@gU"c:Q%jaf3hY=h
hG8gc6_M:8EG4`d%FWgG.f~Yf2jmIi0wRv?r1b{LX`zk@=u>c47S=DAM^Lb>Bw`QX1IKCv)MLwvJ9h3TF8_h+
);x]TEuO2EEH?gS^_#5_%#IHgL:kLu!fP2P*FK`c@
d;boa8jGPg81A:NYc>7#J!2dphU8y)ga(M)5^$q4$2WwLJVghN;"(C/Z
Az7(qQfCMdLk<dZU-D#pNx7]={gaAckd?K.nNn<2cge_A,Em6fRk=.M*[Os<HajTmY1qSUEH_6k6AkGpGI5tJ"]Ub<R5Nz@11=-lr`e|291>wW[k;_knn2D|=W2}.nNloBZuU9;URP"2gg0N/?,R8_cENGI{vYx`x^[XEyRQR5F8K-s#%mW[7vQ3("-jb{Ev*{K!-g>q?~321sVq6Jr5^boQ,,F@-"gyGZN>n_+]_.A&L#c:uP!|@WBr]vOA6.J(2h`Cq`OeH&hb)BCP5luR4}oZa@l@HZ,yx#qh<|Yyi76wuU:vp]jQ5(aBRVr+9X=j[0Ffl!]uYB4,M<,N&IKNFPtB<K[xcc%1b@Jv_ek5)2<%Rc5Va!X)#IgyPb67_48t4l%L5s)wo30$Hg7~I
?CJ2bfd=i(-4ai>#Zt)>;ZgJ+s8}+He%5NGcEN56<OG5_0Crm86KP8B,R`::VShMu&>G#sb31Af<;Gt;r#^|:>.,90k,[trQd=6?P/^"WUF=DZ03S12snnX%t@yo$L$@--:IwIndgmeZh?nz1vCCq5aKgj8O#rhfi0*4
L->L=0Q
8vL]Jh{0dUKNgW[fPL-GjL3Ia^~d&Gq9si@m@dOKOv8$x@5$6t"
(r9a-:6FTjQ4$Q~c[
@$1R1,%qSl7t|$J6S^RBPT<Q`jp3h<s3V/+GR;bI%WP0N&GWh>On<k^jOHqm">p?07ui;0=x!$o!_bV[Q=@YJ$dFIs;Fa>D<vW`aY9dK_`zqk,h&RK:Id<ckrT@Qw]h3fW7<PQDt5__`Fpcl[uj?G/!y`Ukjl@(/c#Y_t9h(I.d?C4}#",=M{T.Lnm2e5RHIi7|q:xncdJc6kZVWT@u]*<w3VZYVJ9e`AZxn)Iw[0#6)})Bri4uJFHS@x9"
Y-ktCJd;r^X[lHoq!EYAYm/:?iZ`;kWq1Z
E`AIt~B-=Rhhz%3,5.
W+`k{CI
dWRPrxO3mRVhVsDDS]2Cl33v;]*fc^gUH@17*0rv~MfbTx4h)>$;iQ_>3j
@Xr[/L*%
4Kc`ijC?+tX0kuW0iYfeR4QhDRG*imz;/v&Q"b&ePnu@RW6kI2[
ETaekJ-
hpy&~<bqOs<.ay:*fhCs}D!tnZxGh#a_H*~(K4{*(+VO4B;92`#$o`PM<:Xj1:RHf^39gPTVdYtskycHBj)E]3lqvb0sLr"_ZVg1#-LT4d]f$qjaGL/x(C/:ru:nyC[?4rs"@Uk:|<Kh?v2V~5K4[n5)O/:B0B`lI.qLic":YcdB=h[,/fii:qoWFvrl3=cp)J/!@M)X-=PSONm95DUR]f`ewm.ozqM42e`bfK4<(O[swqc&75&IOiR^
gbfUXXHz`yxV*l25%q(8B^`O!Wx7%6U3O=K0kT%&KnGxbzDG=3)n1n4LcK3wRk`7_:jSb6K%<q%=!E,b@F.$ZDfAX`2>,.;l9{t9P#wh>YkLi[Vu].T|l$#4src!Lr8i4VepP1uJsc5h8C`9&%ldaPxwKIKGU&/6]JN>Gxh
Cb`+-Bt+@njgO1"w;w<915y+:lojTL
Hv0H=,^YN3xS`gq6,0W_a/{8s@<4bK1Sh,kpP7fe$uv/JBcU|iw)GxE*.WM2PbFbFt1BQ(!qkZCx:u1#)?(Lu=i/T>
T
>Z7^h?AyILB%iHv5D/r)vg:y
xsi`="l%;n&Bfs[e@PObmWznPF5nou;7lQ`Yi+xKfmPXkjDQIAoG5B1a5L;50ga%us&q[pzuvNp_4HJ$i0!1jRlo$rCxa(L8}W`9C(As7%j:v4.t@N#/6j~3|o2vRAGPQD|/D9+<q`/Po6#n5yw.5[F2l(eUpk~b!YODRRH&1I$SbT"bHq@fWtSn<d.9d4O9~pwK(&~(&#d#~%&B|2y1Wo(#H-%ttu:eAHGv-+sekHl./3U7xNt!<V9LYLce,k")zYfkKTW=gBox/wB
;x9jf*CudB5/B2fiAatwaAeX9?4CZQN[7,-^<<4qph8oHQU@I*!dN>4*@_YaY.`"q&=<t2XO$*4##xlDstzbQcadb<Xa1+,RQ.2!ZJAWh]}t0>sZ.&5kB&*1P.41rx.plZ;=QRW?`iA8=6f_nm&@9qno)';break;case'ca':$jc='.]^ALaMD9,{0L80!Z8n/UhqQ`d_E})g(hCMQw-3Lz(fEB.7od_lln0E:nxtM}3cIz!i!I-+LbXzfXA|@#b#D4/b("l(
mkWc
pvPCy@*=[]r.)XeBf/.RXKpneiW
U@;puVSEoEWdqP&&yVa.OkJ8n2ni.IK|:9+{R9q>6$U-1G+0:*
^W(._HvjK4`j`8}=PC=,02K
i:IeW3Qt$%I9i3^!TC81fA@wgI"!Rbx7KME?,V8-<)bF*pw*n!r%gLPe
GH).^fiPo$,!IsD;V&v{yE78#t1iwWS0PYpJx#z)s=da<V>D_HXkmSsV<;vi#Cxn"ih04?LmX;!JW{8Xr;$"M,xwg!)M<$G3jS*[2u28;7/2J#>@;p3
26A9SyY,x{mL*FpyuW(&s"VrSkeh-hli-![|1vQ!"Zblc>!y#$irMjeyjPe;om%9go""*OJ=+mhp)>.N2rr@JL8~^VaC7,_]U:Sc64=wDf=)*J!tX8g:S/,V:u>C9Y4.-9N"UgeM1iR@%|;U-p.:^xFqwavhgUUm/kh1=5$%wfC8Td:Gc!:DfKp^.3>
A)4y;ik{&_`x+;W=0A_&Vq_(i@>#JCkd2ya=lc^itq6AG:@-f5Q]k&)qkf_IR#/)?I8?J8/!..0Du2H~%L]!auy9uT]F+`fXdi5l
&R$?/ZRW(Ztae8`rpgG?>EMa0p&EZ7g4DLJ39*)Z5"!nQW8Nh.;?<3
(0!!,Vv60sd3G.lVRR3fx8sY^e"Q={Y{L"&*k=[lZX(Y@sqdHoeM<?U,D|szPw/0(#Tw.tr2=Krh&3teO?jU"./QN
ID8mVH4KG7nlT|dS"NqY?4$7^rp5$!h
K,y[Xwh;$:A8IZFLafl;(D<~?4#<5AG8m(y~?g7C3]7__J=h]`8U!?.<aT@/u=lm<f@t46tg,)!":3>__*?C#HI!o|UV1Zs]T8"=9vo_s)1Q-3Ihr@8D[t@eTb:a+$(Jut2*FjU1lSg$]FZc*$^wc]tGM3Ven+J#Z,!CQt<am%trJ/oOc`C8Q>Fd_rbsAhs;Es
TKm7b;144?m;2j.llHfD_&>>`q+P])|2b>{OS./[mf<)kEfFL]?HM$P3ZJ{Y95W^!6_"ep&x^2%0H%SR]QayMgKT5#w+JlYt*VkwTLhX_b#fAf&)Bb%.)u1rX2tedK%,EsewFvg5W&BKttx=5&6h{$;):TE]K,Me:7n/zqITb/kJu0VI>S@!.;KtaO@Jvf!)#$TkM>Xf/RNKT,lIjMNaX-Qc}yxqbb1R:TnSD6Rs8(_*E[T(@t;K5W{wEds]eG
)tV1?.WJ1
"
qVt~0F1)Y<?
UsopR;#zgHPxFflFcz*^5k5(-)WnL[6m,Cbs)HVgl+e%F#X01>hcqQ`&RPJ;DK>IDr[O;c:I]m#SN~3&CE5qv
HkfhuXXWsCN
-]IEXwc4"t:W)@SI"Fon`02sTKHKr:/yQiuG*S5kw[=fT:)|xBl;V%SB`v%Wvh(wq%nek)6P:WL^>7J#i*-Ye1oAxZf]%3PPz$`}Yjm8A5!huH.#/2u&SqE.pBszp[ICmr9sF+Nz,9EjGxxO$Z**?,#>%GS(^41Fm4ZrC,T~0|#sr5eCcb0O&VtJ*+fchR7tgBX=C6QD^&E:>aTkPYFv=_c_S=1J1DMk2t!BtM9/
uo*7LD3Qn$hfjE#(/(ur"x5"x={RWL@:dvi>>eO7AN"UtonjdB+k4oF%;Rz.{Pt,IrCP(*NP"v(6[
4f
yvPN${vX8BAPu5PyS.pXgvft:>*oBc:gd/GYMDJEx|,G(i(J`2WhYs5
!U<k3%:+M%>dQUp!vpm^+-[DM7P:ok<g?4V<otAta:wI:gGl4ew?kY[/C#btY`N#.I.1#>Xh(X&;:#v3;k#%c*5]K3IC%9b_U`0LX<6pmeCRS5]`)Kn}(~cg[z9<(#!E?KwMOy2B(#qHpjyqkMC,<8A`DR`c<|[y`]mKjg.}0/DHjthPN-4MnI6XH,!bV`Q9fO`>T31AvlFIds0RHhZ#6O%RuUCZht_{/;(@2@1$[!Rw*Bk#m2r7RM26.5#TBc=/r?IqN2=dq|alaJ(;S
Oq$=g?E~
+8_a*lF=abT[|Wq,)qw(W%
R;_K$}Vag|(>0qfdH
#hs-&$+oKsjA&q.D/L`ohE#M<m9mw5z)5_:%<hN=5:Heq/9^PFeV:+l%,Flvi/G`ooEv**/&^LFO^wxPsG@nHi>li|6]aO
y&yC@Q;M02L[8McOVFq[x%`_j^YFOqn!Yk]XR_gqvgdKHA]M)9KT4?m>TU)@peVsTp|CORd=gDBZ/(+.BG]?ITz<28r=gU{"_IjL_I:Jq003`hI)@E#,[k]i/a~X!>hn3@-2tXCt?uT4-<ZCj>GS4n@fjBJ=lNlQOU22:95AK6G]iCqMV^L]nbAS*]$F!LsqZ?C^;+P)2rfP&Bz=?LU6_V^QX]8dvh5D|sUh2B9(0GaI
k?u"wS-GbYJ^#|0;E]$}Xed!j
.7Q_U",](&("jNVK@VqMc]R[<2o@fLYW;aegam]hjwSQ@A;@ru08%"(0QgAwNxHsfMfYJO/{AcP1@sW)#wH}CMbYCh:
p+CrZp*A!O8V7"r^HOA&99t."3`R]$Tq@7elalwjf>6Zd>g!c;LUSWD&)2p=H
)G73ikcZidK"(-j.RTg_;1&~2f4`>3)uU,,~Nk&D/8A2C+rz<26Xc)hz:*l9<%ykIamVN
1
.Im<.]?SH*4A2!DcX
v"le]zAcN*H=n{L,
zZcyM+YiL??adRq3?%Ln`CRd?+p.l-:LNijKc`(*P1Dt<_E=;Q!-nd:4Q)XB;*?Q4qr0phkMU>Ac4nsk{%,LIL89X4-ZwZd!gl(RB&H=2Z;ny5A,!"N>BjzDrKPGVmQ>efK`.^AV&qp&YbXF>rd^nLM(A?x[#"WQ[
=
Z6KUU1XjV)`O(Z4J,J7sG]Hq4!FT[5VZN=K:p
r]yZ!07u[tGh,a9GYO_)9HEEi@6NJNnR42[*HRS@I(P0B4G9y/-7c@liu^oH1"2jLuvx^&5M93B_5rfF+G@[6KWu>T&Hn@brl:BRB={+EPIxrWzqdX6l8shujq1hi>Cb;I3+RmyHg$=U,[wm1-cFF[y(Sns*L7,UF)d:C^1qK@5H%gI&"X*:$1[TAD7r^1?mRdKD|g5I?g#s^"Y#{#pX11pGe+;a(9OYbQ|)z;]7~YD5-7xG7KvFj]j]K
6%N=M1M%+#XmTYo:P"hgM4m`8.Kk.]^D"%r3gH+LiWoyu=$1wuEboyje%c93M$5%9hSO#ESna=b]DtA`b5X`U4bYx6EXRFQW6,2h($O.wfqM6<DoLi1rrw5fEN>4LDR*yKOc0hIL;1RkL%UPECa*)JTg3Kw"L$Ey/-lj3N=P$FJv$WJZXAR[dbyA-OBt%X*lf_Cn^r^?p
?_7EwJU,|JmDzUs)
EE^K^9PkK;hZa*&|1v76QU2{4"2(i.w{8Kx+(cIX+Zw75YfG/a!:(>Hm[]B5tBGJqhve+q6:&F#D2,WP:NE<
SW&IuDp$_mbPYPRwuMzrIuFD
i%yf
DeGn=ZH9;BapN&b8ikw&x8hC1
a$~UZJoB,-|^yTiCEK&;pC]jmAIJmJJ-3hl
W(x%*&r,-+CulwY
F5gFzG$)%1eGe4ntD^r@5^e3RRjTQR%yKX]XVg9d$fy(
W.3oys?N1+?-.-S3W^5|n!Z"MSrf>5rfoZczvQBLT?a]28b.A^<C,d1l[VYQ5,8Tu3kjbOmdVy^1eil_>|Rh8q&eoLVMkP:R(%9TsZk@(|/V_GJA8Yo0QM[G^H[ZYtx3UF^qf5*%c.e1u|Fl/FO8Fh:
oTa`s@tMv"fep7i*S4=l<s=$9QAL6NwH;kQT4#c`Bxfm%ZL4?v]~X6fdF{St4!i"bhNd]vn<-Y7OA/,lc(b3.)5PScalDwxgqQM7,-nrn]WM*!]y%wN%LWV+
^(S+jT~HUmRJcuZG)*,b:s2tdKKiHc%!PUTGH6knKPf?5ewZD_drCqF)Tw:XhL}lifHnTK9^bLAVFw)!r6g3-R*Bz5poQ?fP@a`B^lY.^_dmXTg%.W{hG)h]tSY]{Go.0)
5{J2&6O_m{r=TPr7Vylb=dC/!!.BQ2m78n^Lrxb@k#D08`-c;g
[_HU
SVJSN>sKL^!eRw"AsJ&=v<mhK0j+_{6ZDu5(bPf7Fv=}y0uyc
)XAcu+GY`rL!lyco`pM=Qz=wy|A)iicro04zx1wqvi+N>-xTPfG`Zrh)Rhz)-PTu/P@926e^DGb>slL;5|HJ6~tqNr
To!grYUshrqo?){1PrXEh]wJY`
VqYk+j`Qfmf{UcZ7SkOcN_KjcR+Ne;L8P3@tJmZV"PgKn^E.K,T&
uF>!#mzoH!L6{KwFY9ftV"
ZDtc_).cu6s4t=&,4V
uK)=7U3
(7~P.ryt*n69
:hSpJ!tGFdoEw5(<TN]n^L^oI#A|7vh+SlNZ#<:(Rq?Ian-iBjX5JA5~
q_1_S#ZFE5neb((4V^$fUX2.p:{D#uuvg^Pa7CTJrq@s3V<hfQ5NwHi6GniH1oECKANx>:Y=y<zbf-R$:W?X/:wvfO7e}m:NjLE&&xtqVUJs<!0Do&4WEi!yrZ.Zsm]_9tWv%N&';break;case'cs':$jc='%]^;:bop],|?
8,2de]5_Okg-E72?RnJWbX3J.A-A_s*HAJQCeH5Auq2k$f-Zw`d:,N@}$`!lH_f~j1p+
O?DX&`4mmefxKK!XYB+rPrqhkbz6]v|X.URgkB!9ReVX)3W(qBxv{c94Lvt@%JNuOL<Vjm{cZ/"fZl?m<bSqI]I_$jp,EQXuIJP@Y$8lg*
5_hB9-u)
%go
YY)?!J!QeyFvPv$p;<h6x[bZFuvRk_6^=N<yUQBG`?8;XJ=1Jpb"P?Lo!b7INwTnuB@^h]~9^z%!&n
@iR,Mmc|B]^EiJEFsL=$JqDcpiW7w-/F3c<l@0RrBdf[gOw_
MvgAzQOAbSbLm//S6aUo_
6vDD0M
19F^G
%(Y$).$8qAp]5*7"f4Fvv/a+a@
r$?tRk"B6<FM,QYwrfEs#1vv.P&Y=7-hfINH8#NBpYZM#Qd)|<$_-R#up5Sh)HjgwE%b^NMWZFji}s83i4er`(9=~RE+#Bwq4llKcSiDHaI><]LKBY|P8yPg:N{i^WOSab8W!4$88cc2""3^^)3TppYn`MOV&i|kAj#C13j$3)
6uq}sQxY8{YA%(3%ZupE.MT!Oy88cs^]>qq/pDe@@^q-m238%ri0n3C!3fM7X>pJ.Q^Gfc+Y[&mU$>8jh{Zjywp#hFKO33n3umCp9;aj32JzG^od/]U_;yF$VS
eDTIkc*Io#.mx]/DL]m4%0&h(w?wgp%SQ8qnowuQw?`Pz]2@SnO2MQA+{CC[dOd^`u,q~8]7YL$Mc!w$+fxiO9Y;n(DbExxWN^q)g0jP7Vbso*q^XXgRx7Dw36!=ecy-h]$DbkL]:P~mv
hlNTSQ([8>LG2!hu`4|(s9:$IePhAP|QmL)3az"@fD:X#sKtP[x-p6vpTv:WtU@73@`4
wpnD61uip?7Yo%^CiTa]v-eSo3J]T`w,#rsCOpDqPk(y7(1Wm9R/Wini>L67F2w86vKmyk*t5wRTi~VHi@iD*O1+kq5.wAb@J=/1h7d^GA:
$s4Y9;sNbB]*<1K[`H2Jx|.)u7k[Z/.hCD4&KUT(xK>*%x#wGK.eae9"co6%_:&STUtfR6wr?9EQONM{&=({n]i3w/Dp8VUhCc7ix$FJd]tWTN_RIlt^XiA,c1rE<CaN9JHz^fGDs}m161%NW@aK@Y8Ki$@{Lrjm0b<$dbu1.rqg
Ed[/tUkAL:yDXNo$+EKkfgu;@l
>$#n.OnD.J1(b.</R:nUqIL$Q:6c-ExKqk>AM,uy*EM$vNq{@rc9?[B
R`"co;%"_R+qAw@7B,FIjX"dnnPUX~nS$4RIjvN{7qZ*[Tm|hG2{X]r9[op@VzeD[iS(X;6=b~4bm8ysW;J3w8udke[p&vuM
*13;%km>mF=z%a:-VhJCT<
%{#"f-evrdGs/`2V4e"z@sQ[]E;ow8WGN{U]8;K-4kfR_lC:P&ZgV>=);LR`w:g)m])0N6V[hiK$jLa,aS1C6|_7oS=n!DQFFzs9jJ;&ole~eCmlpNHGMF5dEYH@t{fwX`H(Nz5~*
vEKiXN372#
HFU#,lkXE6c$INBa!i6y)kB*U=>,<)xj84:aZo3B!9-MgumDJ>}G4pKJuvhx,_o4>YR=~>GOjn|"[9yA}1$=>cWPb8Xj~u]XCj!iHhoU$VJ#zYE(#Uj:+k@u36p9>;gK2TB#3e{gt"y#u(_t$1Fcu]](Nd/tG<^u#E6,]ql@F"R=qil:]_J-B%[ZNh8gg[9J}0u`^./U^T*!)x7.!*Jaq29RBH._!00Q0-X@WLaDCSi7^.^xACTi,bjW=o?EYp.559k99SN+oD~qh@YSpxLl@RO>maH1*xE@/H!T=3JJB--l%ZYH+9~1E#W:X8zD<A6wm=HUdrQj"6vAq^!uCRUJAr:$#-#l2ETn=noq^MnQ#`k^HV=eG?$2;2}.!m4Q;qTkPdbt?t_UUeP02%Pet62)&1Ys#q!^ce(HA7O^5vNPGo]JnfyWf$2Eu>pqwor%#CXMe=.J[1,lpHBY>A^xfS}Z;&v_-9v.:Tdv18@o}X=A5Ek1X[xUi%[4C/6cqyyYv#Ph$k"tdfg5`/m?"IzW>ZgkQ4c7M@eooad$=%aC;wIkb6Mh)s/(3_
fpv9UZ$_
d_orGIX;{6d>?3<H7uhg=RS)6)[2QQQMKTU7/_l-GV-Qdk`SPZ5qPT5J"S<k7+ZbL):.]PSZO6B(@k9%i?VFvN9.Y,-.0I:*^7et{Z|<OfF^D;XqDi2<OG(9L
4nE3e0R;_/CZMU6W#0zp#xuCWvo/:uP(YP:]fvSi/Qb+vO1O
2@>@F=czOn=|LcKa#n2d:;0VL2nN]G"ZYJYW;#wG93[6&zdGlR^M&53_?)uUyvrES#/SMV_MqKwh`]6)mkXx2>VI1Z2xawuI1EU7Ry8t;*[k7W+S#:cUq_l12yi2*)u)
de2N[2Mp-&G-cHZOm<O`CWHmB0T+JQcdu0)7H&0U~g1LZpvo$Q4xyTil(/HpD:5T[n97!=U7lj81+Mz)9a"GWj~Ur*h+ua"M-saHm!:Cg*"YC/,IKQin8Dr_mh8gVCSw;`B4[L4?JMT_dx(qL?>h@(X!TGhxM73^v;Mg,%ZI{?(kCs)aHI[
X;R?_akTA>lQ<[T?$$uCw=rN^YxSHeIMSM%:F-O?c=qy}=34$Kf0+LtZ/F2wLIV/VB,-?izds?RgYu13S.^s*]`ax)q7#+*5MYx6tW0;kC"PEl03Qw#2}H{3gB#*`
VM=nc%]$y-,v+VjCM/*!s81>!,-V$hBjv&HtO$]){K!&l"I:U
<+}!NxBOu2>WzQ)[pd2@c<W$RJFt*%cf.wjef&.-{Y("+V{.NAB,B2v6+gYGHDoS$D1Zv*W"1qcE`?=;
P2O$,4in9hC:=.-40kv#Q_Z~"lJ{#fl52)yhHl`q8Y/5x[.]$i^ZWpD1ql?ilLL=Kj^`!"g82n.(n89GGS+~[WLF$`Y1C]c"-v_%%b(.gs8k4z7O"GT}0O6AZ
wMsQqws[rU6P8;jJO=kbe]pL0r@nl
=]DbZ*sf0,c`gf7&fXJ;_o,`0>x5Lbx/uL)x>h;(DI#v);!G5sRQE+e"[#(f)g-.lTsDAq^IuAX>^`5Ci/r4.#5)C+/NSX5]-Lt2[b>ef6RSfZ/RXmG
&A=aZT`cSe3D3qjB>E4uDfe!JrO5Hv=!vxcp3CGPmn/FFsEXynhO<fFujU5.v2A`fqB48?^D[A`FWuDJpn$:61Qs`>=RcJM=0Is
/>:g&P
dOh/i5=P2Zn@&9#8$d:<b5s3{Nz3:6oTdhArr_#>@j%^f-?0(!TgXRgQ&>
EFZ<`S1Y5-NV0sI)lj$a,Ho|sqW=Y!FLdK==&CgHf^(W1j-co8YSm2_JwjT~?Dr%2
-B=d]eT1AK$Z7FUXN"yOm{5h*!rrLyQ8-,=>Gg"8tsN*6nsUgpq]nK(Sg{
DeC
X&5EokK0G=T`c`-5UY!-j^~FXJm^7t7A,UHdJJu*G"0Y(lE^Q^xthJjEmMf[>Bn2+"o)h8.PV)],DHdeA@}oBty1}25CgIXmq/H
FUbT|#ysa#uxhEY
4EU8rV6,fsFNBt,xvF[5tgEF5@*JC%(&#15JE`dJdX"5q;,FvBj_b[Y(8;n_?00l,@".8mddsi_T[DKQJUd]e)3J=9duiuc1_0jEe/aLnY"MUqOLkY"7DCqJZFTt&*91Y>xQ#Le`>L;(yf%(*UC>C%$kdD>aT@&#%E
jpl
_e14+w[EdOn|IOZ@O&QWs-v"r*7Zq=q50_^U``Qg]q?.OZaoY)j<=a;FtjWY+ZyI7eN[aL8_DQ]"Dik`iBPk,gC:qXd**GY]9b@,1Kj/Okav`BpGibba9E7>[,mM#s<^]ujcqVRiFe+SBbVQ
Yx%ihT">sPOOeM}1U4~WD
;tCjEoo
cLiD(O)EOuMH(#kXJ`yye9*Gap-9HFQ5f?m86xwZOjWu=]Awoc2YZ%lN)_&I9FYW#V*P}O:O@]yZB7UeiK1s>qpCCIyb%EdJky@nZj`(_("T2"=n`S{Pm9;`[!u#z=8]):xP~GM+?^K>v16?UNG#oCtfmPF1&
Ovg:#XO*Mfs_Q4^
*ypV
hSFL"]@IX:cdt|]5IEQ6r38<:S(qX%,h9Vl$&zRr"H)]AOVElD[
LYws"Pn@a:(7^&`qe,sTn~je3[PvetF
$e6s)zD8#z4!yZEW"zMSYdP,1Vs*c^C!lSa,1#HTTPr{i7v`R9Sf;:;?"5">b26rR*^8Xx(K3;;:w?E#;m/Ry{SsG/vHAEb,b/3b(]*7D8+qL&kn9UUAYGPsLHF5uBNXv>;)-f"I@n88YRxT;cCB0ru#gI*cqN/D>;mj-tUkV:13La2}3~4)$"MEu]sJ![Of_UG;p<#=^>&(:OIGa(XATWYWB,y6H0l:pHcLb5-FxC;wMTDe-PuAW:cP`$6>kp-^a^(u?}hktNhn3^58Wb+4ggl6k;h)!wd!a3,6k.g;[%CE"k^[nb/B.3BLc/xoWa#,Dn`GKh.>/$:}obty"8]xB(seL$-KIP)dE~pXaVSi;&"6mIj@;SM>=DvhTW4)N/I3W,6YP"5"Y16RQ)d./>q.V=#NE:E=sSUr*z+DikI3kYF>3/W-W
?l(0rv<3ZPO[BBV<kYEQ.u+0#[TIs*I%^Y!C]=l<){k
thoRE5-,P<mP.E#[9Lr77Na/=4[oAUXzlO"kgEst!z)|A@im[sOa%.wSrZ*H:h0&Q:cqY<J.RmnZTpDp6n+OoYJ2p{7nmkxwJ8xPB5JNxX)B5yQyByOXWw320UO=-,ak,m$,t:P*l>iS@Md9O566d=hCF}(!e6Ow@~b;fkd(n"I(]YK"!.xcM{';break;case'da':$jc=')X/ALaMAp,z0
Y+$lT4X*L=l]VILDQu,6]>)bi/GQKZ4dm:bOicT4OK6riTQNN[yVSIc->Shfmu)p5Cc6QWFf@1ai]hyUP,rN6M&8@"b,qDfT^![">.epK(y5tIdd$m!nER0Z]ickLwFE/-ip2x0FjcUQk8>
RCRgJYMB6*]]?KhI)H1U
RLXt+BSugt6D[J.4V_3m/<ppvuh>DAZ$QsXiPj>RH)kiB`J<vl+Hh<.F"Ar^69q;:+tc#0OvprhqN^5H5hnK*s[IeX_qbw:uW
si7jTV[?wX#WU](cz&+a;N6MHru-}^+K(8;?u
Nn-b6x=Y,-HiYk3T[:[EF>9l:186#e~b0VR:<j(Ez4MAr>qwpwimMs"lPhgWJW7`]nq&F2%lV&kZ,d#3Pd#^TaX?fUOhZxaWy02QZDZ1#wn(_cKmk.e>WHSgO;~UjRmp2eiK1l
oqLJ-Ec.5h3VH<o4:Xfk>(0c^6Fx>]yGDFM*2lnJ,+vylc?U7fGzDtQvq%7KqH4DFUNO(]>H8#_k
Br|X-BFbaB#2/P*SKLs6*_(!i
rq9s-Wp6A[6W3<RJyGpk&-K3&s$gym%le@~sCa>.:"JR2^4(8U1v=BS]X,+w{,zM?#&rNr/]|XBs(C{7YQ@+:TUU2b(*6w.
{Byb@wzW<M<8Vy+q6EcYtwf)<nX
APInGEYf1ff23dEK`sY][1Jv5PCUGjLQJIFDbiJSTGlo,LF1CQ6kWgg4vO2`]V@bYyb[9OJX2vYfDjhkDqP(9Gy1-</yDN*rTu[eY3?ee
KAzO/*AiUu7k?]8;/XFcx4[.9s2KkS.&I,7p]p:27TZ=MnGy(JE6I+Xy9Pcoxbh,69r(8/*Phl>xQt~wnu1Vl_S3*4@[N<cbf,"-+M$F`2nV|H?U0MM=I]75dR**Q(Eh4^T[zITlg,alJ:sSx)mkmv7ebT5C}qBZ?Wb2;ryVIp3!^UWBXWJxIl1NfqhZ/khlchc4-J]A6%Vm/@w`g6P!
eNuvtQTZfjARb7<QiH1:lFp31k"t`})PJy@Xdz"+4;9T
UQd_HA$B^9.1jqPD[h/[99_[i9F,;%IF`h?*tBzK^U2HSuy>vcbTlXxc
DEkOAN?2:Z1,n(6e5vn:a"E/eYD#pvlVkH3}mDXO#Pkh#<mH:.&3uljdK;V.S/l#q.NQhl[CGpmkYv@m11e-pTL1yJL{k@V#Rgst2zfW.^Ka1:Kg9|Z2;t-%8ff[qqU`WU*)>OcM[_jDT*a58PV<>,x9&Xaikz?pCq>dqX..CdQ^TF76X<QdfP#!g[?JCm>0[$ym.
8
#ftVwO62F/aB$ACvX)1:Z);8ef#cdt12S`Je<!V+:yV"x"c,<C"EP^L,m5-LXp/a#[ul.p/~ESbWUu*+huuY_c.>jZ_DYP1DazSS<F!)k*T3U<1Z1igWsL$@]f(%BmT4BGCwi_Q3ZQ#W1zM9KqYt0~40qcF&`jyqhHd<>xn(c4(4qJ%MB0CE]XH8y_*0It?6T,uU0M9M_~mGCJO"<`a<D#[~;+=GvjoGPU2IFIg^48Yxry6G]e=l[}Y#9&>/
u1ZZ`?g*DQj
y[w3R:Y^O@ObAis%ROphX>(29p0RIh*qKWYTQQ$3;:0QxlAmgI}N.E!u~&6
OkM
-fl"3idF9uswB-2#{ZN:uegfzty%E"Wh%U>>;fz&S[CL6E8Q*@bfO@eAlG|Mp@}h
W]S{
O:31RNjjNZqB6jdY"!a/""*t)?E2/7,*il{g(C;y$5*p6nl8
aYbKtc3e-@IQ)ns69L+J?O)g(BYr5@@r)&Qp7Ih~nlM[M`uCgEUc>7
q9/rTN:nzdo#~N2OMcc[OO[GOGr#yfg1S3LUZr21PH^e4C]FUXc4=&jtr%8egZYZ(y2kIuDfNlVCVfx#Z9*Q)uv1tIWub,o!-Vs
nTa;T-cFQT>DqCfLb&2/&VJC^Y5r[X
`E:{l
KZG]&rY`*Q9o)nf
.0kWC>lJ$&:/#*sdhqsU=QOJ$zww`,*YjJ7pc"gh2F:/(;eN6r5?E*y3HU$WsMPh8hxo@n?Q2M?C3=wJee>aj3<UpBD!B=QZm%/*>BV&)B+SY$G^nU%g!?3$#Sslv)Q;U@#wp1]5`g#`WETtyv
%WoU:pWhxn!FRUnr!u|uX:W!Yz)ebI&v$gwg~v"5Lada8yLL=F]]{,L`42]Fk_Mm#g<&E9&6*cx3#IcO(1WJ!)SA_P`[!:"j|vNY[LwV:uX*`ltbmn{*.y_cj@b$u?|/u
TWUr_b2K(vnE_1TxBc,w.fxYzUMgZF[gtI0qt5)`M*-:d9/#E@#&Wg%[(5nJeHL9n*#kWx]"&`:>jKA>v!8m:>aiqv/I~^Y762(c0G]og@*cz@//g/Ip&Id3b^@uVb6k5%70o2KU[RNH;dnf6K_^cR#c~KN"gg_p%w|G<1,Uu6oePY.U+`>WIJHtJ/:tl"](xGUN5clO~e1Pi^fi#$j"%@X]N,0V"E"/@*O#fs!J_`MQo*Zy|,->LNs#M"[v>?Q#h"$;
Vowr&sXR-|T@OFpTQJ/S@lGuTRq5cRFr1A4OCJO^^ljp2[]kKaj/c:0u"irTs[e_@TeG(D4H6S76C8]4n-Aw(w8q6anahT*n<[95+:n~
!#>!N^a5hF#7B;Z;4%dNBJ8#=]D5a,*lCx@d4EsoLgK-`dqq{q|>9;;PogxxZyq
(0Uw#JK9pjUW+%~r8$h6v-zL7L/?CTl-BjFVB",3~Ym-H"rRGe"%;G}2:lO+)K@+n-x/S)Yr"7haLJH,yB&3-gT-8SYjrHLR<1*G4G$q0y+s}%Y"a!mnHfG&|I}!]E:DH/)7uG2#M]2x)rxC(s_7!rN#_hHop:O]4Kz48y_Z/$.iVJx(]r3=K5uv[yYO>b
S0yAh1G.BA,Bw^H,VCf9ZGcBc],!(Gc>SH*K4kD@^4+C:A9_1904G7"7[7HXDao+jwOi<Pv/5>nm
1=Ek*PM*&5<Q,cb67%+a#/ARl68d4:+bcN9l}?FplY9-*
URQ[YQFDQtio{iF&N*;7?n~tK-=4jMQi`@B.#G~FX(6nOmgU_D~YcFaGsC`]}A..EShypOU(xUOO2h3<NQ
ORRV+{*u]AlXE^POUvC_
x4i9D!B*zwy7s.;xd^gZfXz,E$68yB96+#6U%q-SZYlf
f6&sF">oXQlc>MN&@}ywPdgXNl7OQ)(,wNDas2_c5dwbJ,=%<!SRZ:io/<xYW$gA7)ecbqR6DSxGhwDDq~WQ/lC4R{O{AmEt0TL
Soqk3*,}fcMNV]:~8dXw"wp9+$a/NpRHbmP780Jj/}QsKU+_Qg]LXyNHi5r]_%.^NxDg-CA{"]*n*;7-$afQfqgYM|>v#3L
AC4l3M5W.tRTsyW
YwEVk7k^m(Qi4:IUC.ynCN_$8$m>>Cknq0O*6hxr+t#vnCE;gi,*w6^YYgpKVE"-y>O-=h#?tdq~5[Ka*Qmc6uP$DQHOGfOC-zU,s}k[EWCu+!^4C50]p#1BT$#/gKhZV$@CpA_PD|^uG|tx3c&)"!rbB^*8"=028VXu`/lZ$wU6P65a&UX0jewd$gYkCcmzg0Fa)rU7b"7sFR,xDUbr!^waJ^X<cdE;Qg"G`pFiC!Kw8a!R`?utm4/HgBxLJaVN_kRV:(.<ND_zNmGrrJ"c!L(tH4g52?d9aIA^Cvo"PlE<M{LO+xX"65&{t!,;e,oJP^7GoT$JNKtj3m#eEg4YiX_NdPTz,"iHdu-&!n[Q%oNo4nw;SN:^cve_X@J4u06
G#tY[GurhPyqi?S2ulpIOig|]54exNGN*]M+#&rj-croF~Q_p&;ipr)52j3<w]c]:9e&w_Ayef6|/m>wB1h_]1K:T4hf+J5/Y%LKd"7WQ5s5?IZSl*#B;G^u^/p%%Q7K
dUOm|6Y-#WG0Y
}rGFkG@a6*{6!V$[cZ!b_fR@Mx@f-4e#uN,1:>)_sZYa^xeu42K7%ta=}C4O@)<>r?gh3n7C*X".1x~"Sl6<8G2Y

y-rcED8L=i8t.58BBK5lBc;gD^!@y=&+jCVJ`O6DCFiq=v]GKyT%h@rp
d#B8C?rS9XN,$wX^<eJG*~m?lwM#8m]9&`r[)L:B)hbJcUyt6xHU2C]6#Zk%A-%m!>7s3)$%Nn[{
`9"=JbdcLmA/M>#u"Il!e"4$=`oD8&*K?v`h0Dk;&;2X4>v$ScxCXMAQ$"lX?t<bRK$bIrYiM7s,#T6+Omrjg#xd+/{xdoHd;i?wKwmvk59/kmW7GF/jKqW"H;+Y%,0mFOE.)&bE#ZoWSU8LyKiHRaw5o
k]bz&6^';break;case'de':$jc='(]^ALbP.!2M0mN&*Cks=qITg%j:=([R]/Q3bP#6,O:zLG52&>_bb,gkG<M~*;Lw(ctjL_WDmQZs/D.74#"5D%$^5"Dv](wmqQy%GReEJ(P)>CX.g;D6H+Q]Bg+LG!nsgk[Yh;fqZIK[9]c3JrgX)$xI<(B9j,O~g]f4Xj&Hv>JU3T/xX9
^FwDsB4y1mLJOq
0w_TBDkTSP-X^T=&n8vPge
.Llx0(d[vy.5_mv/%i:6*j1[;;C?`pQ
O;{XW0}K(k86s<$c1U0%3OjAYG:goqM]XBq
]krGM@{n]w)kG
}gyy?Mb7xV8x#=bVGaD#=?0cTw#?tg@Jc8?ho4}x9=RiV=rr>gWpgDj#L
AD0"}<AjV>JKl<9`E$:01LNEy
.Pwc+%pWU44t6nmHFl1>nPIgP0AE8E_05]aoq55"um,tW!@L;p9tpW9uea$,%kcud+I]1F9XHbBTwEJ%Jha-OJg-+YqhJQhWqQACj1-H:)SCk@tE9H+.}2]gbIfJDO~l$SbCR2M9<kKD:rl;troVIG^AN3jO(Z(cB<j4EB.0i4_-hs[=
#ii$y[
9v^&v?+@YCoR|[V6dZ~v$?O#BJaJi14/akk9UGd>rlMGu<K1MImb(TaLQ2<,#-q,Ye$s~rSRM.PFu=LSNH%KzQ,@1G_Ye:@vo;x_W,6XUBDfm:(A89JIqM<IRaOHN>V#$)"LufI6`
a?p6XCTS;muFmpt#=>xgn^7]$t>0[&ag^
X9;lsu7N^QomfK[C0Ig?muK_2RbA1
~&Y_bf:&AH3YpD~c@=HCVA~%VJ|G(;Qh`umxjpE
{BxP{Q.n6G~vTpSD=O!+acwyk/bxt^9UaceX2t>I
qQ3hO<l~4gQhSrJZ^GbeZUR,w]Y#
>Vgo#tz>ruSO&oe96JiyqpGvC5o3~,sR3WC.khnma*2d#@n2wAXC58+fuK_g~iq-u#m%T:G]fbkeY_t]oY!:vK"vG
yXf.xg>f8`p/1L"-VdiTLU~F*a;<660XV5AO4xl8J;ZTr;1bh&gc(E-xaSZ
wj#OxX`
B/^4fZUb45nwO`pTefv+%1U]!v=[BOnJ+W9`2=FJ
N=^(/?<{0Vv.Pj@BHp(X5KS.wfaanfWyk,kWeme+adV4
Yxd<b5o]Pfs$I?TylT+GZ+2X(1Q6GK.<dh-K"P^fgdEZeg@Ja9eG8(8"uj*v!9#nJlM[3FRD{7DqOJ+,g+UZ"Zr.RB@4XlZw*m|;N^"#l/dbHw94)(;K?4W=kFxID)3*NnTw1=#jB>SxmeVm7Mc[d,[(0X_2G.`dYpY=UEJ?ZRb^ThXE4__8"b~CjMw$eC];d.l;Z5y@TQ^`5b!B>0vLU7%cx=iFG`RFVayo2@Igx8@_B83-h(gK3,a(l-<pOZrlcS1m/Pa
GliLyiBLi>|D0"xF6
bvCeDbdwjiBN$=js.`DKd*#t9N8"UusIUf~,c2/fQ/b:kP0f@5!SuNZHPDkwN9/*lh}B{IRHo?gL7`p>8(5;l9!o6^y;h#vt|FNgrKA:v_tnVLg;Wbp8v7f
0i%ZPOOyYf94-ZzggV|(o@#N(SCa^1Y[Fge/6KSUN*dX;x[Xs4,1dH(cC?]_01|INpp%;d^%F36Zu^4#B]P9U1qif/7U`u%%lY0@w9:MVM>`OvK9I2>,E,OMB$Ga
8%.2ObT|v@;CHvV;NJF)"6vh;hZ*)BeZ)N/y5k3~8,=;("gK&"C_*E,+3@OMW19y%8;q><oZ+Awi]#%^%<-5?,NTH!BtnBy5?]`9FwgH()@=$
Bx_f9Z-fL-RB%K:+OKj9g*[Ve%X=mwjLbv6h,<TM2mvu/=o
Xq8i@E<-:?c_M.yh>l!feO.,_mA
F(LI.F4H-lGj+HlNoKY,1)p+RS0jk.ZXTX`5kNghgt]y575mMWs%8dMo66rEospiTkdPdm81>UG$n(SB8E$hi6&^02NU"-?0a@Hbt"BfdkFZUt`+M9u7a-(cdI_sj,TIH{H|/k;D[0?/gp3_&gXH,v#7B.NykzS#TW]CXf8cUU)D2i9jHYXuii!)_Ah~YTqs`dCtV$i@`pQ@wl;k+^*8[8f)wwro/$C&c1N}=_WT#ipL:(@aZt.P
.qfJEjJ+}.o:N2j:wgb=V>b,}o^H<*)eU0#kx5z!]gM9}Yt%
#jT=>>+V
X:;jbh4B<n0&HU"&D9YsQ)y)F,NC@)xUeKQ*L
Rx%Hd`Z9*8n[1*}mQgF%&Ue"OTmg;^=n%`)b]M~]$C
-yrhYU0WVZE]kB`~=,<l>"ep*yhRH|@NB"L~y?TS3U]/jYB^EYs-e-n`:Xm4.Tf^S(#"#z)OJ.gy[<cm9P@N
?Ve[$(fU#k>WV=f^;f6rXLia/n(Vz__>n;VO5mZq%`vB=c9W~D%Wq._#w0Wu0J4cLqce{pu9%kCxm8`"MTkBMgy79Z)f#vPRTk$)*?s>TWZH",BIlW!K!X{Zf3QZZl}LdvFwDVgLCT@OM_xI>uB]y;kHoXY?wwocufOtmHHnIB-I.FCy%n9d),ah4,oMRo$s_2#?pP:K7n<t
l?YPj9/RR79k1oAQ@"ii&lCB6[v9>MR{fYKRhtszn5OX,$,#Tv<Kj
,f;13RI)^fbXVhQbsQtw"#T7Ev9C!XhNew[;xWX{-LZpsxw)4QUVY20;hyu9!wS
OlVg]1fjvn&Wc.HKF/Q4yUQ&R1q.Ub#8vR8>;-;(5MaOS(T]V!U`^1ep?^O,SC<E%=92<jCkf|qpJ5w%!Jdxh<h:Tnbojpbx3<$~U{SJs!OvaS$k."8*@axa]&b61^:>&4>"UK&x(/-~VOPdV5fvg4iJxK_WT(?iI]9jSv8xmo=6??E3>Nr>8a*-"LJ&xE.EBq";vYn3_g_ok4dBgIp-C&OQ/-1_uM"N7NtznH
x3g4sX6*w.~;Uj-0WS.2qMt!e?$REWw?gIS;B49dr=9aL8?SHChcr,M3}!=N~_@usSLAy$M-u$C0l]eLHE+W^n:jX"+SByb`RLOTPkq
QxC$Unkwt@):Kp9,OGA_z1j_9*`d}2W<nK|sbqO8LYJV=;2Z-GkB2NH80x4S9(u(MJc4MPGJ#>C3^GzQc0SS36b-Mt9"]8%$,d-&%ZzdTSTXIT8rV)sn>7<X
CVwW4YnfSmksWIpayTUPP6?#W]xf2H%h&cTVf,G4qvH|4AWTx@afaH>utL?x$7e]85Q&jy2Dv7Pd"Nr$xaUUH#F#q_dBdt$jbzM1j)</_PqfV:BT:L=!E4#~V[scqW>^V}6C-hS+m@m!-&VD]D(,7:I|fn,Lnb].WJ;[]W:+T~Gd%u>>Ui8Gg7,NS6?
oUGr=vm1f)N?gn4KU;Dzx8H/JHGiaR%9)-!~md_r,pq(v5bxD&%I<v=)"#tI8A_zQZK
5N1XE.
z#yKc$Zr4rJ/!E-/%X:gUBB&]/gUE%)G[d;.eO.c`Y$pD&1nAK?&hhLxZ&>`b4"P&x_/0*YNz2QQ@1.Pf1vC=;YbAYlD94y"N0Hf5>Z+2aCn_3HqKJ_iv34&U/4%s9I]9krj]#<qMKz%j:kD@cK#Ox<Fn!-h~gC6tQ4Z"6e:*QfyL4shtc/8_or7|uzNtX?^Tvw,bkvKY"p"Y44,OPWm:yS)prcl1k1_eA",~NMA`hdx!c
vTY/z(=#k$k}!0vjnru5)B0<>Q;VvSTLf9GXVZg(f[ImC5S1E[d>V~*
-b`<PUd1eH
y;{HWR6yzQviPkoF;$8Un4u^8q4+`B9p-_D/ch(Y+h+lXypiCo9#2af1fl5G?8_MEa0:mWd`<`jvCN92h=#d<+IC+9~1!(~nh&{f
p)^zm.p,R-m)<h-3Kyf".q`>2LvQZVHZSrF42WHTt!q>x4`rBG5&&!%MKTH
eR80j|6>1flKL*d+e:<`vH$k+ZuuON8.8%p.JCBrYr=K:3t5O@xMo&
!!Ak>2QO3`(N$ieC28Tu:5:vA/8P^Cw"_7u)5-c,/sBdqC)oDe{/c$wXcRPhD,:_rc|:.S8jKg~<neaI3n%!2u]e[M_,F!CoV]+RRM@x{dD")gC*}NL>eiuj~RUb`HB+dK;sUK:O[C,Peo6f=CA!aHZt#1V8}*,-:cGC`%#7e$@_v,:%jG{$yvtoI(w_PQdV#>EN>#FjrUGaDI|PmVIe2rB)re1j[oWw?_Qs:
k&fG9JbJ4CX][AcoT6xyICejqC3?Byu31KIjY2"?9.mmn:?%+3Ukgj_o
*zKJy6A}jWXPTHxUA,rim1R6*MBk
5CD$?q)et:Wq(6%_o[!;<LR^"r)V{?w)lcVb,$@_dnjoLBZI#!<xfo%:avem>O7.atx#;cdBnA$%;7xv*[H5*^+Xj:KFVgyS1+e5bx@OiPLKb./L1i

io`mKKNW%#8sh(/(;"8*qGHW!F!8j(*xy?$k
YiK<*Ab*!0a,:hP:0[S2r.&/y7inA?o$&n7lmnW#*}LrV#SJfWNb^lAHB<r0uejz@)-V3bxIub*[=ZbywO
*oGCt:nX2)}*Z=tGx=.G$-,/jrb:q7)#K[aLL"<D7(3rB4e55oBnvrF7Qfuf%x98?@KZ6k,RE=k<7T!Q.JXg*"]R6UTkXFAF#&>L]Jg?p<n.@H`uht+JmkaRW)%DVBA)#Z&
&_%y(pWLPOuMNhx`sHmKl6)F(mXTelRptxLICv7U[L+bk+ok9)E+nZaU$:K(=eBj1z$1o';break;case'el':$jc='(h_GfcsD),|?z""%8rk;,gM*.o^8!:A-fi^?pt<qOLn]YPpHi@A>(Q#!VU?fnGA@u4iOY6,C)t<Qxtrgs=!vkuzd7KkJVubD:g<FW6G"F?ZSBw#t&m
lxtqyUJRfi`~qe/|&*61?V6I@Il.H&G3RBXpSaOnv+E@IF59+mhR=m(pMvD=XEe&T^q7+
Py<[VM@6J&w+#tv=93XKVx,z3)62=P7zwOuQ;4MPXPtG=M`]P5!>7
%Ag<hFw73@
K6P3w;=n:R&Vd"yH(i&d|1v45/)H<y~K.PR&mpVqv@b67"@lS+75JEkjm&1KKyM=IKl$g$G(WAMQ-yC9++T06^JE?g:-YwmsTDZLgmNXsS(RYf"b0YXY8-L]5Gm!I.-x/Fu(ic+mZN*=ElG>Bu:>MPMhlYdw|c#s<0q>rcTB)iFqe?nbP1ZR30C)ip&@sMqnk_Ls.CTnq@%qewniPpRrx#dY8L<cTne-/qU<4aNLp?#+scpQfaMN;(;G`nVkuoXF267"_[`NAk^7hX*-Z]J]5by$Ox#+Fc9f2GI$D!fML-om<BZ#o#VCh.O)0S[P*[e1cd2Dg.<K?S0jD.Xr/P3/[I.VhnnG4-A0}WMV/*Kci&c_QKDT
FM#1A%<Cd8k-
yPv-?XN9R7zM
Z#g,-]4<>Yb*?FGA0G_DgkBKaU<X-;2&VDBJI1uaZ)N+WTukv90jFI]jX8Ly0]wY&z+QbP!)Ts:dN8u)O-:x;Um{Dc^.3pl6AUtrf[%q?{@y4_6Wi~jbt+A@pOcYV@YR^AWvC,G=i*F0@Fyv<m[5hY5KXKo;7E.

#Vk*o(-nL#"
#G5a:xkI@N#laa]QLD89OZ#Y<h_sA-jbhyW:Bujw2fiqg.*@"T!C(
lHB3EpR;=Ol[-v"`#R:#J^_Arke>h`,<^/6#4NLK3!bhR:.",3PFr-:QVG;ZFaUl=h%p~KR1"XimXI3fmn5IpF;@[2GnF2~Fnri&cdNf}9cw:_Z:d,rB^w9)%D%,cwb-#L^;*,pk3Rd^4/yy#J@xi9#TPX7-,?70Uqf_KkO-l_%#rlGH;7uE4Z}ZTPsSp-rto%dU"pb_Ibw<<k;A4>e
oCTEG)eMIoK7nZ,5d4b@Tiw0}-R^C@!+^bmes^9C|5x@n*Im$)MJFB1,
J)9<P[TG)<yHmEq-LSO;<bUwle*)ysGG:)JUwniN
+7kiP(he/XIb>;YOp2h2>R8vzb1n{0qDD_gth+H:u)-bzRdl[HEARLp
KLnk6%R5dtwDz.!7wFWo(8r>5CHWLa#P`h2sEZ
P@0],A1CStcyXMDQGCMC0aqXAB;3H1Vah:HX/:1
U~2DIR(4k]Bd;x2/+4OdSS@Jd:lcwLrU@Dk8%BkMll84TMn(0rvbM(n!OsLkasH07#C"6TKfceGyE9"fq`4`,!nhMtvzqch-vts<(IwGt9u1mcy:y)^a0%=M.qZLA["%7Y
lapq~Z2MdEH(NSBqXg?-qnX-;Lwg}9*",C{e^=[`dOq,[NM.3#:c90uu^pTke,vCB
+48u?c!2t;Raln^g-K;ix2t5wn4%b;Zg~`TVK$)"M[XH)MZDoRIe}5p`x6*ly4d/c_cm7?(pZmzTI/_ZQ)&ve#sKR6lvfJ)2]XqI6Q"R?Q;ttx1-uU=j4dt5m/Y;Z`"jRY})/1HS9R]AA01yMv{Y2dkek./-#+w,.5]&_&9=.GRZQs4Z`ol=k;rtP?Q+pM%e#,"4~Y0KWe`C6j.Dp;-fFSt*#TWV)?)gYU,a5R==86d2/5At0LSR(UWv*uB>,gZ:DOtqd:$+HAMSnU6s3=1JKR.rs!0hC/_@vc"=6gHU[!7B`D~pLyVILtfr,rqjiU{B),^(iVzPay[c}MHczv|q;
$p,NMgIP>.6nk:_MeFQ*;RwhO"9kJO#v(:Q<Ebx!<rIcj;-PanG!tN50Jc0OZSt_ECMw]o=w0>Q4LI;ENcR`L0;a
AA%J)],bX/txxh;|)>Wcxx-6^WA>dILJ:*sVy00j?1G,4#eb/r
SED(6[Mucs[[-G,"lu*!)h=F2QXhGi^wydJ!Cy%9FS_eblJEXpP=ArQ%+A7(b?X@g($f3hhW7WZo-3/=15>,3?J"}&q(TYLOrN0)6#CY@(l4LRC802FwuqW=}+S6Wb(*8Y?-<S9@;&5(I*0"-ZOhX/4"tyvZDp8-3!bF~`?&m/*8<90"krN"6._6Ml/6H)_ubYc%g7Ye"iyC1C9i7So=Ee%eqt8VQPqoPNd;17ap:[@/2BjtTbHr_d0Kdox;[hY/X)d*Lr*[4"Wb%W|QKPNHrlARaN3IO"H/>N^;to$(2W4TZJgb]Nc[Q1{;F/5udL#=})"RJ>vowuI05S98x_sq2gg>4?$s;n`yx(U8%qO7o^$$doRS04QS~(CFT)OBJ:,5^>1NjG/*W!CEk4o0Iq1ou?Tw}TMQ%(e.4l$M4@PsGC,R39*T@-Y^2>9U{=vkwx[?vM.;;X16SG?B7oGVr,CjI+^7pLhV"e>;wM,*Jvv&ARH,930=B&fNXpUr+wyogcqK|TmUp/S)c:R?S*n/6
j"Ewc.8FXiw>X+ov,u9F;@]hB_aE0-|..q8@^gstC])szcY6)rV%(EN$}&)#$t}"^$tD6].2jE+lGYb]m-`+Fb`)cT9V:
FT
"j<`my*me9s:+o9A6fTB:o3ef}7W)_OjKa)-[REM`2/1=DE6!b;7(@F<Dfgr>Gxy`UV8s{1>^lBWeIp-"#fd)!v/jsHp_*@|JLf8Z"JCx;v$,0mRM4U)&xwzfAAp$7V"ob&Jfxxk6hD6%_B>">.MZV-w[Gn6Z"cUkPDYWRr0jIHehrx7KAck&pb$AiUW>vm}JmY+#wi2GL%8FP=[xnXp)pD9*.,>kS;zez;-2ITa;Dn0YnaZq`;;8fW2*asry+Co+(hx(Q<5M8OP5Cp?AGixecQ2yFMF?0d{1h*A%!vfm_?Nc+3>`*p`a
"wpHC(C[Q5vz3tk-OGyf.XG_9bH*V<=l@Xf4v$r*%M^+r6]7SC8EIS0Xj0hC4
%1^$L/WPXEtTjYyUc=c>4,]NgS1wX8/oOYU(Ll=@C`HT-V"mD-<|"nO+G5:nQP#Rs0p1]c^XN.n|t0n_2X(i%-oUrIhi6n?E4@gcftQuD^"aJ8B{m1s@ASBV2E){)<KE7=#ErYFyC*:01B!rRJSA",+;"d+wH99l,X$q@uSNY~]C+.c&@R$bdxs0HbD8kPc7]vE[MLuYaH0|uF$zYCv_1#2r>B!}S$23_=!dcNfdC=KR.VOG/8R-^7t7tS#,f4H{/{pDJ}`<D<G1sqJX5-4vTQ(tpMSkezm&VH0x]&2zp
o&!"8
$r#_WrF0D4fJ7gLQUi"-5L?,lqW$k)uoN.wCwz[J:(fNiRP0mHP5+]>^A-x!:l-<9K:8V`&XF-O:RD!rn9KYd_
qi;:K6K[_%er$[p7#ja:K9,SKsjMrUxC%FP+H<btPX@C,E?Vx=uR3T}6ET1I?!eL&./Gh(~+f#H@*=Q:*3:$kPVK-!P0CBwO:Na4JE4"zCO-)4.al"%,2-NA+L"8yRuU_hihrXT2w3PJDdp0rM#1wyno`bdZTR@j=Ag98^+
k;v+jh[h,m%O2/
1cIPm<acusXC$.KIjz
o
Q2*0C!1^LtiXB<^h<e-`~]a1SvjUHQuO499Q.bg?{y8p"&"#egoCzBi>+RpOuV"!kJ>fy77&[<Y"SVtwCknatIiw#e~0}f&pBFL+*Z"G:N<Jh:l%2mw89<4a4:n
yFdy/ExUj/&8(`e;sXD$2f>g}V`hO)w?lJv*Vo4?%c;;[VAHh
SpEGA"Mi8O~^H;cTJln6_;oE#r.asl%M6
s.yjU*2cFq8cLOXKreT`cXb?
dKIGN%0o6)Eq/%9^<!Z-V[gj0HjT[CdR/*y?O%!xG=FVU"kW<2>gR
FXqC)j0V2-!v3YRa$*rvw]6FoB$qGFW6kKFX6W::YR&*Q[*
7?wGShj++AfV,2D83}1kide
e
-20z+A<%(*2+^[O8LURy6RuSg|PnXuEnTEHaMHv8.99@#kahHfKX?t>~,181m[*^
amC:$(Ng,<
*dUt8X<#<Qp?4^&"9H5"u=1
VuQ-
,4d$9earL*!b3-8JWx50&+f0v`)TsRlMrR76#3]G1YncRE
YW+RSwZ@o>,;oRA(-iy};<%%;;YwiaJ1LIbPyz?**:vwXuE5As%-EscSuMNH*`
D>a&r]5
*OtnSd(:,[Av.P1cUiu/=l8x>+RRuDYd<._Z~;GOs*3JL;!6vXMmvT:Q<&N7(3igZYIa
L%pU48l:*j&pM&tc8)sK`-N:]+py&a:tQR<SP#FRHUObobV.0>7fsrF`U[)@o$))Hu_1T`uUk_+
gxl:A.m9+um8,7q95Gn1d2-|u_8x](wKM;CtxJ5ebXcN"R/]f@RW,o?B:f4Gc~#k%U&(Ox<YjoHW%2q2I/se<tG.0n>_H82J^f3|Xc$UXUa+2i(|NHcIWs8sKOe"b3LQ&dv%*OQZe//.m%Bko.t:AoNw4!^-f?C0(Z3OFjFX05DX-_7_fqxq#1Ay#.89hzX?p58Y.>KA]"
a/kE^2s)[]!a:mRVYE1?OG`3hhM>de>1Vo.No54<;9%J8>>FbheC{X<=HA
>[[aimc];HmN4ME8(syOor3~dkgoQVCv#;)I.R&rWK6$6~"/P@XuTS7AU#otcwYL&0dun&1Z,*sdnCebcA9hQ6_!rdTU;CGZx2qx5o1Ai"`a4
&d)Mrl-;:>kvC9fBJ+F<dK!,TjGorW>0?>H|y`FjQqGW?0IM<3PZGnCHUZ.vuQ:7rY&v.0"}t%GOTfP*`K
BH<[B]ZVN[oIa
3L9iV1`@a*Y]MggfR]Ru>N+(CAl-}.<`uc<?se?G,CG0F<Eq{beg?[=jGt?W0T!q]_6.wj)#qkH9t&:g(a-0j57Q7j5FZlmgk^r:Cb+v1G`q#Fm7EO~
"lyi.d6tWAVD:QJF~L$QVF(5BxyA,sHx0pJQcN=HMnP9cXdy]hsx_5/fB1CHmO$gDN`1cg|ZE*SP@:p5@JN-!G%d/GQ_6:~e$T%XS*{L5*PUdH,YO7f3BFV<~6>mtVd2RC
+`$Je7nX5-jOaVo&bbNk&.oAJETIOIX5v=j$jKVK
^2)I$uBW4v5$5UNL=y@xCZX^%vUM9BU,<ucK#tLQb:8<.JVGf1pPC:0yJp%xX2AmP@Y1Vx_u0WZn8F<C>B|Y)0Vr<RR+j#ipv.(J65"V,4(Y0s>D4o"ct_+04v{=~(|S`l>t^SwpNYcWC]rHi[>hxA$)A
SC2`!8l4]Lc^:Fd8.(0E;e/xquxu:1EsF*{&EqK);`/G4G~#4g%hs^v[pMiJ]buHWa9!z/u::l
$V/Y=F7by^(;XH*kwL;N`.,"Es3ZD0,
u0=<3K2BZF?CT-;cV[pa&1*;"Q`H
ei>+#bOKni";XS[)q3Y%ZI+;
[.1*9+10J)E,Jfs6TdhCi&TlwYxjoRWd;@B0t-7TZDB`Gp*4[2XO/N@G9BqcY7(=Q*#_wz=1P"3xynS&
$,MS9y2wXqTJ1bo;.vk0iS6l^$ct!A5>EL%T{LnWU<5H,p;qE+8M^?E!Tx7l$_a"H6t:ATd7Og`R=R3iue[
8&.4~mp.bxFa$pA,&E1OEt&-KinFF)lP%bhhvmDS98=HhYt$YjY=v]SRp`i@jUgljX@b_p.g,x=C/Oe+L?!v|r;hcmRhv4&uvi<a~y{,@';break;case'en':$jc='*X/&SbpAp,z?z""$qozTE_@:E,3%v*SG]1</>RINJ_8=n_iDkH*>ST_bqi.qYY}sx5lOI1>rH=5>+bq?*XmgKRgM<OqCO9AI`xe[rbu(nv;cClDZ?IZr|Jp6[:Y9+n|`m
+>dYu/[^Bb[iqU8S#76&Tj4soU(sg`mO[YN/l
`??4}ruHv&U)DHIa!jxaPUW]<j1=j;b!4E"a,7"s9p1k
>1]UlG3txCQsVh9&L~BiqhL7],6xUilY7?12h072ST)7n|tD,}K,+Hy.`@hfXc)zePvf5Ya+kN`5)jq>w/&7/<HN7-(2nsgdHv%(y8Q[]>1q%zlyv_u7E?jPP?grqF<mC
32MS+La5hN9Hv7O_SwyKc-TJt}m(VFGjQGghSXW,;ak=qBO[o"t9[oVz?CO7/V9[a9:#cTZh=:@DC@?r@VU}q|
[CWoSKgfUh_B?Q?KlO&!4b*;maD
WsyZ#=poblY>0;nTP7ftM;"G81_NE;@(e1IZ9A8G~,kID>
GoXEuTG.<9k|WJRq2J/JICnn
rq|Go-1g1-JyEh;:)!w^,(.V@Gx:77J7+Fh$/Ztm-coq1(S-["VL{C9"tvs?jAJk`Uxh[uujU>r-{HbPP@)34nk*dnr9R=DGwl.,<c}@fC``:;v]ffL.qD}yjg3;*%z;@xJVWQ0XHohWT%)m?UM,kl1nj:Ge/I[2P,%!*-
g)+<,{-ZX%O89r_6dlAMIU]va
6`krt=AeObp&0<Qw3i,iy0BECsU@ZJJmMZ;eB
EK+|W1ViO3bX%|m9=zWT*xeHl^`O1$^5?3:/T)3]rc3`B}i.+$ZDr{X)O?F(As*yGi!?sIFgth4WGX6)^O5Juo,QgCOxW05S,878,qn*+%]CfdNeuy67Z8X+`y^9Q1l1bN2^QGk!P7K3gB.cBv`@*L+EOlRbkVEQie-G*h*nU9>B#tj<Scm@JZ")K]Au6m:=W~#CY!*8bj@gFP
BwMEY%`*&t"iMp+Wj`WUXu*1|.urA"v*Ui[M57vi:EI+zJ5e/`V?Zl9W%]DX.tHk
EB`VX+UhAy5[7H+1?:t/KX[z:}%x$5xc
vm%>Bkc^60+>Mj)i[Vab3[+q)FQM/%Wf<4
n4X#8#cictyV?wz)u8d!hR(t:s:%=Yj*KR8EdgSWltn:9Hk}dQ)Gcze?W)M2xqH]C5S@
}dX
xmaC5_I[EQ0[O?xj;sZN,$|2tp;Tz3%C!gtI(*[+O<D%oOES6P.Rnh4mDRL<2,gpFYmB;4>h`-ENdq$OJuK1Y"w+88Ta0Ed-CFaCFajUah&SQTEbDB%c|Q+Eif0(+s{Whw-Abp>YN"%$LOEZoK%ZU/"A#kCS]7Q+D>CxSo;:qz&ErN06}a3i
Q#d5s$@LpGye33?+6D4Edpj#%!C&9%5B4l/(_5DNM~pkb/Q$[Tv"h{*X"GVS/9Z<=p/oH::p/dOfV;
G08c["v:Lj|;;Cirl!qvcd<_@4xX.&$GnP3mX+W*)Lxl9no(X0cr
-|cI!SdR3f?iV%t|MBCHkPe-$y#662-dm@(/G8h`s4?P)@`NA:1U-j2tX
b`?
D"L&%_UsG6Y?[5wG24*RN5^-P~rvWI>T@9b=ToqHu!6<$elNb%*R9D9MSnQYp&o-)(Cs&/irB;k~[c<bPpAh(#)r6|53b#UYlU2MK;sK.Dw$=P6^ilM7GYkxTS_1]:6qfkHd!vwZ9|0*$.jnWdnVv~t~yiY1.P_&hFN9-5*Gym-{Xqkk8>pYsh#x1J+=p$QBC@MXg&>|#n
n4_&:wU8gC,bP)tB{c"Hj(jZM
OF7%KA}-"&npmdPtcJLXne]e.G
rt_PY0>(7/@x+/QdGyhH2xCxPe;n!
?@e7IJ$ct,:tL
*aS)9;</NG"92,i$)$="Nw"rOylYcy$.T*wpkdI1*z8Ip12snDT6tfDQn!jPDsN~d<kmfBhE>d7gyG2@@g&tC>dEj+4[!?7#%;7zK{Sv<qa|%l1WZKDzTj5U4ot2l<Ub@X_]PZm6nvu%1tPL`iW0$~cpwse-9>.{3XO;!v9i4a
&4F#L1V:s3_rp&heP!Rs!D,-_"1?lU4Cy*lFPKqrGrOI&$k/)U_*k%XO9[yY|ez
LI4.
@ECGT+G5:Qw:A+]k7SSZk%v>mwuIM7RAf??@T[^Mj_D]Q/CNdbPVny
d1?fcez3ksSKE(pfRn5xYM5T$Y?!GZSo:/ec^"k.$UTBK(__TCgAQOlE6c9<25%)cIng*h[.^SB+sfUP*>]gpR"n44SEOEa:ahg$ZHNS#"GpMC^#MYhH0e}1Ke^RO(#K-6>9L,Qt[:FR"XeTT3t6Jb=qLTs(]SVr<gC^>^:kj8<IGdp5nxn&(,(G(KquNTlqq9x=HE?KkB[@RC&GWE$HeL7K)3@gG+eI|aRsDKhdt@~+~COIiu"*%S4>{PPcZ%nf<BafAT.*Smscd<SN$fi*1^jw"v-raQ`Kq6c:&7dSWKV.^9=<zw,_}2HG
,1V:dZqiQn>7A`jW#aoMk10Rt=
&]:a1pXkA>Vs$--q
Y<8!cA<;w2U]FfrNf0>3?zuDtyx^t#[rPD2&)Z(W0YhH2pOBJJ/^2+I@g_`2jF;hfq;$I,4"0_UxqIoe_Z2Y1}!^^eC]p3A036?/7c[G+^"Mv%j,ssyeHF%cxK9O%EXNb+gcsBg8WwOLA`TATvg^NLK
S<$vOU
`KA9{7N^9!6.>R9,#/&$hN`SH:X&/7?=%bXZHMaTJVDe`+bV5+dfTbPBLk(rqJo,q-=O/5S6*s~jXqN+l2Q*J@(%|LUmeD%H0r)<KG{dPkx::b{HaE+owL5c^Cvhbu!>cIFA
0GU!O.#
c}l/te.UIVJ<F-]F)<)2L.sZ[1nKbVZv(Aa|<dMh#/1^SsTaKaZ]AHM0]OH:!3v,$e!]/x_^K<wF`Y?cjyLUT]kUY|b}b0eKufD>mL>u/{RyX]X$%MV7X"oiBzT(c*S%SZp^_TGtEMm[,?iSAhD?gK5]twa"@pV=h.<=7A:5g])d<)Ys"Q58&<.hTrnM[![+_dop`uh)1|J[,7c*]JrXb&5[*[2-XwiuXF,LF848R6!F
v5"4
.WR{d}Lb!z6qeDXVcN(<W6dJM=
gM^xpA>WFZnsuETqMF%d_gJGX@2ghdk9EK.>{lonoxc-cw|S0uD#Ak
Zy00$!VGs0m
:ss_#rip5*7jz"!Q';break;case'es':$jc='*`G@qaMD9*70L82!ZYq$ViVdN5A9X>N"O2vU0&T!H[<413NXWW`0r.MK]P^!XBqNGta_U#1R&c/^M<IrU#--nSf4oMmr!v]L7k
Vm$ZHaWcdo@gk@Z5;j9tZ4
e
@v,&AO<&QMz,}@^>QmM][k:SQ[9fo&paPIq]`rKM$=mmKp`50B5B-:2[W7nGT-vFg?7RdyBo~y#>FxBr?<Lha-fJ0@#6!T-0fnkybM|=x>or3TRLSNxXBkkkon+0@n=?s!jlstB-;5p0DC3M%HF[>M7]ry?5r&lFDy]i$jmJ6M"7DOnKXraGB]phnKt0al?C:B4+_wV9*IbgvyCKR@5QNuk!%F%3|QSTLOu<]EC5~TA(YxRv7r,A
Eh?3g+Ay1Al-I?^SB
M@BNlP=qepcr<^Q>w*=yvYAxEb(oT}^BIRkjX*khPlXfSTas_MQ~GvG%h6_XAaZ]B!H"WFRJ@[m?:wenV]3n<k<J4^<EWx66q$T}uj:DjdQQbWk#*f8;.[#sY;r5SogJ
01UkT1`T>Uy<,,23oR(yt5u>t<`=2uYc~i!v7_S@+3}]<Bn
hm^iHBC09$g<B5=";N
VZ9|<f.=0}DvPqHLfnBfWq8vRS,^$f.YX0gBU+XNRhI}eIBo8Re
h}$a4JKVnbxojg,"]HIJPl,e!#qnq>f0I:/gg[?O4l8I30D5v.`:uZN>o>TfY!+>V1Qsh~_Pk<J?
nS47-:ux3qNvPmWP]]+Nc,-@fe>;:Cu(ojEEds5ejSSjpgCEMk03vguh#?T7";HZ.W-lR+ehj!nwNvT.eM
GWM/2dFM?UV(]"OQVH&Kae.qAn31B#ObnX-?fTG(eVMstR`|(Gry&~6I[Cd:XkO6Nu8~]EJoo"p30Cg[*1:.SNh1M/1~>Qa9t4/+1JP
m:a~F^MA/DCwHF`l*157*gL(6]S=6!VG(j=:%r0
AegPJ3tab(-6pg1fRFPM"@8/^:<q_XN"G)fHs]Q^2&aY=R9mq%S<HaU+r96tD4ffn8i5X8GG7OD|,n_g2pbnq(7eGPYn3CF-nx.DeBKMBgvfRqD*wm:]M<C*f<F>wwF@AE3>aTew=Ee{(W4S=9SF(}^nO(3iAu)fe?9+2yGID?izpR<3aF
Nr[&&UH={lHj/`p7f$-n]26#~cyK*7sIEE&m-N@!Ko5]@RaqOE3A39O=D2k"Zw9paLW>)3l.ZxpNOd#UG$te3q+9c,oE12f
;xqRZ
,0816F5y0TUvxFqM)6lI{_!cSB3EdXPDaAhYg;FLbR(-fqFql]5mVG;GJuhD`ueCP3s71s+hYr/E0(mhp:&[(>=57E=j+fbts&tb5Y;1C5<_]IBL(Lr];[v/vQ6WW>%8:hAQ&Pib8i|N3"oh@q]IgSH,]l"Lf(QFV)[){Xai_jPa:=G^o!!]FS(T1JSDjpJM&p>M1niueiOrquWQ?W0o$WZ0.<~I"4j^0qYe$==e[$oyeMh?jTsx722$o]KfLG8M`INXCJ#]ZCk]E<s&LE1N.+N7x0Sb*nHK8.6r193Od[_De2Y*9@o;>5B?GV[@_8.U2n93n*P<|5!q3@A+5Tzlhx1"5a(UypB!Ib7un%f6sXWfyVkpGOTtO2v=Gvip}.YcP>[-xZw`g;9ki.uwAyVq--5V6/hx
=yyt6h59o:TW#1[0T=%@qZgvN5^ngo,8-[r"&!TM=!-5xG/9=]+&^{t@6i3v.JdG.w-7"3#(SJ<RhERBSxF*7a:4EDuiu
ZfRH&N9]F79X%O87>m]du#es;Oi<7dHgKIJ"NNZXO0
i-)PB<>YlfS]6!T<nAW02/2%<j`E005&`I9)]tj:
^%=tbfHm[r?]&rnb+}ui&~D*I&Q!$wH:Y;HAIbyG$PFk?)5HFwR[V_37;
<4.O#>In[g2;)4mn8&80$vv&oC^fRSRP!ju
,!]P/.km)gxLg|nmySylPnaH]vtNY6-~`RL4=,00k]XVH5*kLtP-=4aro("9Evw}
*$d#%>}fc!h#/^(clIfcW;80CtFSahwtnDK]d6jHPZE-")wl#aMgu&kN<d^UtixO/JbU4SXCNdA6FD6d
"/&`H#Jub$-c5qZF>!S{f30l]gGb1zO|?wr)mF8
p~lDqAODHC.bJS8HUv8*/ve42CG[j?EFkfmSJ;^^U`!G$z"i$1w!/Dxixb<NBhJ&^ph2[p<"6AVOC~=]O)I0m84eKcTLG=_T"1l)`/>He)^16AJP$g"s:(pn$v#VC
GTy0pF3-SE0/vw0Y,=)Tw4k8-Og[6K0)k)>OqnHjuJ.y&)v!b,V4-^KHQFCNn^>(!:n*wLta;UUs]N8f]!]ypfWmgj%~DzLrbN^,=?^)UTJrkh]$-ETlgr]*@Zc%j^87IOVQIGm7qE:GHB`P=)d%E!Av*1g"Xs7h4Iy|Uee1rgZ~p}p`F2h.;=L^#.6&f.L{B}"mX_TmKel2rQC*,iB7<|0%vnO?i>r&`qy*/M<;vDn"C5(fd+bvWrb42gTySZqdiP/E%7]O5*vht,#uf)*Vw5:toB?Y0h
]Y%ON^qIPmVU+ZN<:cvAvH]3T3HQ35!nWCss>G@DP8BW|
]57,xHuoxH~6&#B(~@IYe[J!,o1ypq~cCF:=vokC]cMZ?+.g$x8,Sl~Y<W}eC)|kXu!qxk{_>K.a@e_vpmI/Vg,QRRP!8E8G*2lC]kqfl1(gGkhI^YF9Lx`p>YpajLkDv$h
C1,&Tdpnc4AlkcaF0^BAnL(0-l!l{"}eX<N;PlI@SG_kTO<i>/n>{=Z!d%vt;$=FNj(O{rrR:8z_VxHZ.=~oSsI<T./#7uCafc5Sd)2cUF^0v`%/lPv3u`:PF6!mpVB0e
I17q?Y-gDo!.EKZ)=qrPFyxYT$pwT-_
$cawD(O;I(Y+"dPs)Q[hg(-UuI=kLdY0p^sln8s=35T?C@3R6r)Oi>?<DFgUUU!3*lmva`=TxjK9h&QI]X*0S!_ErPIi.(bXbVSBF
uZXU++S((vTc0+q>/>59E0=h&DC(Qv
EMJ(%
,+cQ+sgyGSeA#|
f^HYqd0^i/cc.g}6Tk<^~V<0`Y:HGd1u4TP=?SJGi]6(/9GevYtw+*x,ii.#NDW*(O{>#t3vT`yCwuL9taOsez#T-B6GQ*1v0j;_is,4MC)u|[TN`9;9CaG/9s~B9?pkU5NkY+.aA/W<3PSntOZ1xA;<$B<h^y[L@dC!)(^9?V[pPYeQ)<k_q?nkp]`m:B>$K
=e=ft3z!`L$HXU;ndJM+@S-tjk,U8Fk64O#ULl%WBC--ljlU(@L"C1"0{4s>TSjq*!UE7pZ,V0!-2#^d]OilXmsRfG(K}3:ZLGH-8RxMyeU[muQ7UXM>MH;<sc;RnWoitc%ZBZ:)#sAjG$DS,;#.[-!/tLa5crg)
l0;oB~H=Kx(Q<dGg*6_*W_*pB2:u5>p!r:r5eU=?Fyd#:lZVh:$$S3T8>^)V<!p.T(M6[!,i._4$(~rit@xQlWjb9k#y=w-aZ[UM=58]95Zh1o(:@QVIoHhE)K(tr%ap8KfTb9w[J
A0N9d]9,.~<SvZCiN7>">(@>05d<r}o!Y-;eDO`EaXD_QkP$vr)x[UGE1KMw<lXx/)iw1;pT2T?I)-O:cRbCOEDw6B7U6th5i5TlAR,w7z6fEpoB/Kk0(t<;3aR$*a/iuyMtIn05@@k?gn_VnoI{em@JQ0VP.C.XT|04LpNh--x4cNU:j>Nrx>V*g4FOQ+DtS!Y4&pWB[cLa%M)g*}&s2Y-M]fNp$`*ugI;MIH2T`^<7I(a&@"[R@W4yXN[nu=!]GI#|e=aK1pIGr@$mfBM"B/fSEbj!=LtomPd-q~`r,OUv^)S<rv,H`tJh,?S]1F[zDL?QsYYs7[PU#}/Ijcn:G64$h5)]qNZHn3"ob>f[qa)sB@%Fwds
"GN?hUd835f=OLBd`KQ@h1kdbrJ0N
8}(nB[$/;5H}y*5>1B]4SZ`--&Wh@ty&38FW(n"vdg/Ybv/oI:>yO{A_y?v/.Yy,%="(RlJ|X_o)_|``0*JnT>M#Fwr?C!AbrdkKu^dVl5`)LC]..OS*Av3c4V>w4^;UD_S.[Ugi^vMK["I^s)n/a-_9gXlda)=/U*0+6t*7inR|6;.JD+@OaT)%79SmZ_]6wiek.*LzFepCF;gZ%@f1(EF0iO;Q$)@i)(4JSlK(
#Z~anaV/;d#rrh178l,w&MhYB?9/bG(bpc,ihmkE9,wZG=sq."2D=Z)RL"=SNG9=WqQ?w;m!)J;oTgs^Lg5^2>6=#KuWI75([Hre{3n$0s$S}_7F%nQNK.Ih35xrBP&"qNMd8L3bp=;BKq
ydWfLH^<FT87:ki2xeSZ6um,TSX>3L^RL0yi[?H9Ly>mC[wo5{<sBZdz.2g/g=^gIfI_
sg,P<S&SHU@7v;]Us_H$en=y858bfdch;L_kl"D$wBmyuwD<8uKL5/O7SKO&!
GNov:#*t5vzd)Yl&[>up(%3xYY:pPPTkfYaFpL=uH[^hWwnhm-:xW-)$w)z_V?N4Xe]ud`?D0X;ZMFMr}@HQ3Yn^=%ZTk&,%SC~+6ynR*"8UGvT:V3>Q1gVo*vYUBj9#:o1^}mA%jui@KA.0,DEK+lb)sX/b9z"/i';break;case'et':$jc='"R]ALbP+^2L0|SU"1kfl+@7^]C26d@xTi2-mF.e:8K@L8^);V]Vi~
@@YRQ^exeMg1j(S+
G&FH9PB4M^,D$iF0euY|]l:PV=6;9;v=c*0cG%A~2F8~rJB/%f
mAVJ_6%/QtNG)nkf;<D,7]
I|Q<-^ACY=E`t*`b0Vp6Xn]XJe3mrz&{aDA}mK?~+H>C
BU6sWvK
=s!D&2?]8^<.p>nxV#"6cB9[BTS=CrOCov9Yj
k6J[:R0g^6-k#xN`1auW@HEjU64v
IfX_[`tVy&4?W[
zEc`1Z&mJFtB!h`jMJcn#kH/8a%x#j5ki5K
q
GYW[=xBSgU2loc_Z
C}8k?H2}AWC-[1V%B;gN7_342CSNlljWM9BoGN3%rpnsAuU_`O.1ueI&vy2YBhd=y`?<s
ABFoW
OQX:6%GWH,;|3RnBnAu=uyD$[!4>wT$k`5AITvW)@k?z>a;H;3n5[2(
c3YmjWIeJ/L:?49oK*NP3L:GCWs9"OZ0t&hv)+TWllrt]^]N1PLB&E!XxSdsj$B/YgI-Ixc[tk]QT(7I5NTeHj+~%`4Jfc.R[+<3]x3k6wy%L7<X1^W|4W1T@P^1(_(blS^Lt4H1*J<.U
DJT/jkJ6F>kQF/3|9#ELI-
)Fkf}>le@h*=,lbO8iKwX?PI.QIA|ki_W-*so"O*)?wE]A7lroL"i63s-K_nW!I!^O|P%d%UXZ=)1CK0`*}.^N*n5J;!,1|WbQ|K(A1atVkIa0kX:!(cR(a@9qLd^7ntpn3>RL7fQ]1K:yhY]i+?[XCu8&hMe#kbwUOU/ov"Ce)ssc,V4T~=w)[/1c<l8u)bU0qj/g/kVaul1Y&<#n{+NV>[
"+sFS,ATxRczyh[/ju
XQT(Ni?Qe9j4}`8hhED@"KW?[xtA$
:E?D|0rG8$<qgkC&Q3Pqua
G
G<)RPO
&pyS}^M.`c&2?,D1*Lb.p^S_RPXQ.:8ljV^hicy*}RaklKThY>$xx+9.;`551r#0{Gi0ZofI>]o[W+#S@4~4+,zPW(ZR9J=DRgam;<f4D@pjTG7s(SIu.IIrmA=18R&Z:!.A9OqyIc1h4m
)2q)Es*)Xpm~QX``FzFhnqL|Di
eql[:/"5U_t.t/Gb|AqZ/3$x?*e],WSE(Urg_6gnnsqjn/
5!*ho(lPb`$lstA48a(SQ)jAvMGduBe~?B
]lOus+=7[2HBD;3PX(`jTh
avrWyWy|qvW-lU!4?Ml4l0y7]%/T=>7hojrB(`x^G82%-^h,FJRT)Kl5ruF82x1EV-vFkXo8X"GS.%V=y4E$rDWu4bR|k*<O;:;HvWCGGj?L
;1Qlps^]BpM@OQI%tJP3.%,-x:`_.L1q*rw*wC%gdXM;f+d*G8vjMQ]ng[@).7LvgF"@%0)vKS<uQ?qet<GW0]y:7lFP%7xRAcTb8y.!D[cM,fyP0Be[kL;SeR:MoYV^,J`TNj
2$?Op#&H!n$4ZKsu,1_/;nZ1`!Itxb^:yl-j0J^LF{emVPGP]-xq03W=J~Gd)`jr47?JQ+nvNtExAC?4.zHyLx`yYs$_8F77hp5"?%XU62qT-8)|yPwJy/KoFHUUo2ejj6`{Y9xPGFuw38>5*BsYT#Q.IBQNXpInqM<0p"Yp
5L/6PtH4wr>$Rx{NmmFO"@
bvolEK!a9"KtE2_o%w<Is#d
_JP6>_F|:EKg/|<L4#?93G5i?/!R0&9gH*.JbKtklNIotAiP,ngJ8jiR/m%HOc#-xtsP$-k/+V`JEMOQ0Vo9
_?=ayG{d,/nt:N;V~S((1)?
}.`$+-+cpjuBEG3IS#B!&_v9ex7bw7kN%dAW[
ch]sTi@Xc0dKu:r^y9`3Pj&D#v$n,VHp=(5K5%EB(YXkNlv,<-u[qQC%L^2txT`uCAOg0o%$co%0Sv|5yW+,H4>#"QU<[<3`Z2cnOfh$VM%au>f$KU|F8USa+Yv_}Jk/]#_&mg]n,t"`@RGb)<#*gdWOYE;BoC-+E:1afqEDadUO[L^Jc2_tZ?H<i/(_$sHAR#L2
N|A[>tHzo1y/-ZL[3wB
-AC7"I"dYNyoJY"Hh%Bd6U/p$:dm8<&B@Bge`^:Qc.T*M)O~O<qBBh#l.}Rf@VgOoU8+P)={A$#Uu.G:`>:R`I-Tn=8HmKn6iCq1XwOJ1VB>fvAoX;"3.TWy9eZ!FF0>b0X_2KLNWG[)&#,
-.pFf-.w0pP8*-<1X[&3V`I9wT``Af6umdeF7-2duffgX?46.<sy4mlrRGTE*h>EnfOYBg-$QvNJK1F>xH1AiX!a/X0.EEE&B5ou=5Q5>P4&]PHF!DOqi^MKI,+YL@_W1E<.cTS;pYrNJ9k8wdG?V
FP`yZ[N]RycTS?y8Lb#_aobpIfg^BDLHA9HJ+-Y:^+3Eav+*"s
u[
@
%t$LV0%$>^d6dTm+`:Dnu3)<!(`CDLJbtOK]AQ0%[<3n(Ad0LOt5j8vCk*r5sQnXS(cDf)1]G"*
QkvOxu-+*Nd$9-0`NvYnkl/%vHiaQ6h?s.JBd*,#OF^fbtODIgM/iHm(68`v%B*u/KDXG}N7QQ?eW)ty5{,TcZQ9Px%ScMtXP@6&]q73NF]`GR<@-/U{"pc=!Pg;+%1gyPgiM?H}R6>N5O^IQ*e-(n"vgRTs8kLEP
l6,csM`iocLoNDLs/ZO_G`R/!WS[:24N=?dUjv!xI{q33UTJ+]1&OMX+m<y>*%XW#T*]dA]xjbP,r$U,6I1CM-$~d*cj`[[U&&"9JZmdQk"Pbf>k!B*"53S]&$7Fg-W3$P%d5+6/aC?;Jd
<NuPN3L^}Z:"*my[Y?uX%"_Fj9}b*bprJ<H,XHZn9c9F~UU?1rdIBO~37.9Ul"[S/F_fhyDL^yf7qv18!kUpa@_81%.Ma&!=Hg$i@.3mwQA9:G7ID$x9ljFQI7`t;x_M!
<5
[1`EZ4+EZLSVRA<N-G9HQ6xWiQSUn`?^!7/*`7[oF7)L4>(_d2($gQg_B_6xEKUP.d7K,gi[@8<+^bPcA.nkUaC+pl;FcS*l6^43Mk4S1q22N~dPY.*>^CIUiY>iF{
}[KRje9HOS?e:-=T$Md8TEP"1:f!6g0Nx8Kw7C4wfu^uEo!N7=f,A!*o1j0P%M"qp-D-K$7NGZyk&]:T[J!S
RH<DpDjjqh
5br/0yi$H/]DYV)uT`+#^<gsg@iKDRWl#Z./e8K,9
r!K(v$%P{^>it$:H^,jXKp~Prw<.c4lV4Mc0E"Ij>sH0cg_``VC!?!eyc7W(b#e<y5%k}SViL*I+|HZj6KsUbM$mHZANfWfoPs?jujL[.VEk*xfwlMyTfbPA_eaN>IjT}i}A<N0;Znj]rr,gJcT/i:Nwfv!mXwXH~I}hWrBBpVaPPlNvwhsE,S;n7"TTB2CAX:v1vvg_)jFt+Jj<=5^slgoP~pq]hxwa
pr.K8cc2I/sg`4(^>IcJC(k^PD7b^:_:-8Rf1yX`MTo/?U.w+t6r2|0?4Odd1A#)S/(INMIS(m(
G)7=$}ns;,9u7aIO.R9$o8TGVcmUBs8F_U"="EDxq!,])yHnr@8jcK3q+&iO;UkX-boEef[UDn1at2,b.e%6&5SE/cndqwkkA^3SXT.mIRmZ.9tj5{y"r)dARUMP&=&jC@wu-3%II$+BC|K0R1by)a@:2bq0P/.&x)vcKj2B]3/0x^xP]jSaifh^OhcBlq3xjmGk!OCkDhe|`vO.u7)V"m7(!;d9Pco
3:B%2&7x
7HG,$uuw,<7T3+,JGh5ZWPCA;d:/Ag/<B4%_a#6o!R&UZKs#h2Cwh35>wv]cQJu"^@LCV!nNl8B?VY<OyBG/EaV]hppB^Wc_eC;Xr[6-K0^@lOWMo!A#CR(pJ17=btv-f2L5b^V-`U|GZ"@e|7uO#e.]Vy;;5.eC=b<+hfa;#:Em^gZj}`uf:+_8f6wS#8o8*EtqO(J9%K<,f"gSfS4KNf^HxI$+%EOd1R,=4.s+Pep^(v8):FKlI&(-EdHw!ZLVlb?Ldwz)Sa1fQv/L1c
SFTZNBb1Ez
(g0Z?%Nt8,HX4*KLSX~
X.0Uo5"S8AMIYG>"-G*jr2S2iBg_AC7n=H]Z4Gpknk
1-g"(CsvaZP/7b)k,8ifl%wyX.?KWO,YUTTm*Ec`.jtY3H3;R[8z!CJ0.>y?o1ij"YSL9iEP^jo#Tc.}&T:Aie;p7UMvM#Yp(HG{=8f0kChm9-F4<9#JZOPW$mCuF84($Tp_:0Bb3=l&*pLQK4$_[nn"$lo,eZKc,LmH+q,^tt.#gmlVW/sx`utB0Fz"tyjbj8c"<l:po0of2is-o)s$7rZP
^=o6cFZ]LmxIo$Dqk"wy*b@QVHB3@cghCdWhxaZ9vL`v"q/1S?^ob/Qh"j<UgkZvInibbGyD!:FEsQlr"@H5[5>g/:x,!a"PMstdkNp#*nfN_#NbB9Zx[Z1<MAGe}S4o&@`PWS^+>pBeI;-b4.Sq|o-s%gXY+V6%:*S`zJ];J_A_p.6mCxk(/]UYhG/EZ@77jN[&N&vO;i5Q-A`RG^X"Yxz/&yFNEDJRN%Z4N75Jlu86]wD';break;case'fa':$jc='(c0@j5Hp=B}0mN&$qTA,1
+<bjj&U)Y2hP0K
g&;E?ZV*@Flr3v?i-|:m#LWqc}Nj%}41XGHz3^<5cYK#tdt#7]
m?Db58,3w1Km(@&
oM}I,N#qEbPn5G+oc;A]Ww0WK?R^17Jt7647ex61ddC!+3Yp)?Q7:vI-^
lfLb`xIY|n[Dg>hLDV:o"*s4cDxt,jO%pP.gAM4D>aw<^gVyQS$tqk6GD
sv{,m3(fI?S76-}.
H:5,-/[Y;&AI7
DG.R^Lh5RPb7"{LwO[J/""(r:8B-_Qvd3
C0_%(mk^)FmRRsV9tSiGgP^s0P:u=XWV>LHf>NldL5>u1}f|y]1C@5uJn}(IuzJYvMKL^%c|pDPdLN+xPbpHuWVd4Uie<3xEhlu9xCKD4!w"j6L~MJ:5#/5^sr_XcBO<BaLcUBT:*X$W$YX5k8g=tw$qL-4pwhkJ/?BAIZ.CR0;WdaTPow7&(5kT$v4A+oXD]}"dmOKn=IWg69l(@iUYlQnK2
^.Z3XHP4jWob)h7*N&6qs_I
s{-<!oI+6TaW*h(Z4^Y/k-,ErH6o+C0(O?%,ac/o8]X_.<5{8*&)[TA73$JRr^1aVi!ad~<e"7my7PWATb%vB]@bmuh}gUeK%6@X%
qmYLO9:Vo_)1Q?hTRVoo.kMnGR-X_i0Ra9qdd::dBv_W#U6kN>p>@yjOVVNR0P"v>r
_KGN}z$V6lgmoaW$(7dcdbfMr]V#~-mWmaa?&[wXSeYg#u!/e5/O4q1"=-__mbzY0#S4JYt>F,]wo[_8PCW4<Q
9EKB4/c;KGxSPlT>yfk5Gn?nvvVoOL&&H?V*NIr_YP@m#uUov!VX6JBEVBXW`D-.5N6{KGAq+W5[:OYAtRSz.cjH5oOg>^08yG:eS`G0ld)}E!YtD0Ot3%5HV4+5[}kjB`]cyX[>5p@S_/*vxbRL0]+hrmVVDUQByAL^ocVN"=(54[mek|P7ZrAFg~lfk=rLpio+9aoge,(H54tD^shz(}I;3!gK%!6`pAPl]F-0`y-;1n?/,Rau$F)^SwlcO85v7qU2=GVD6kO?GqMRI$&XFf+yR;0F2G;9w3PA:1VjYG8)`zEvDg-|LbE#x>fcFmCz60ZGS}]Z$ql7X!o#/5dY+%05R)2g#|HY9
2:.@4Uf|X(EQ#-h7BBiN7CAh#fS:jIrXDH%(?RRBh]!f_;6R+fSr@X!TL??5fl8+99`H(>:u;qg|B[pwr(_)x_9i.qG/1>rm*da_fgq/93Q<YyDeJR8DJB1N9"Mx$mS[(hB+?t+hB$vQmo+UB4M=Q!^*bD!mgi*8%=UZ?}1{U#C03Hb&ZV5z<GF|v-5+^jfW*SLRCcfg-Cv<3w:1I=`45$rBYm$UK):nVa2FcI-!7avk=j]3=y3vd(Vjo43jIJ;YO)gMaUsihuPlN.E.`7_
,:GD&qo-/D6NinCa[*UCZahrSB={>iEB-rH2:")WnXudW;4N(8N%e^q-ESK|aND4&lS4S6sfsp
0^LF^8?D|*[pCbrEE3w
l@lY)[{j{5y`~*4mn2;Cz(@>*8Q!b"b;;nU$]d9"^6c=)]]#x^(^C]%Q29T){-wiWH@]/T:7{@pDp/sAQA5.KsgZL
A?H;pO3p_CRDjL2%Hh2wQo<:?h
QFy864T"Cv6ma,y!0WiTxB_Tm3E+=R#AX^c@9|nkH*]"by6Hw02@qjaZ<qf$xj%Db"j2+B<NHY/ec5s;4&v[A]`V7&`@W|$2L2mAR)pLI(7S>=b-uf5Cdv]Je[-e_?dO.d<?,zuk1B?!8r@b;j!*4T-}Vs$!(nY3[RJFE?Ym:>IKk7c?C1-jf;1d?2Li-kYFX[0-bt%&^%;?I=AKs)Jd0(O[d.K^S3R!8&iifvlmc`v&]uwd=Q=/=<!/rz`9kYYu>(.S=,b1(OTm+w.Q?["VLk>ze)N#(.?EEWjXdVg}hos>*!#o9S/Q9`eR3F
M
,up=7[%hY7|m)vTf1w:Z{dVimtg&{UF")dH!_*5<c#a1%YJAO)#2rr`=TKw(9oq#N,-&jk?G6T:trK~
rQ;ZlV5L10qK|_:3*B`tSy}(0$"PUrxm(i_B8n@*KH#yp5C_/y9g9oMOcdWu4N@mSy:w-#hfayNyoJwx<c][=T}vS^gdV,7l$3]#GfG!,!~Sac#Lza205=9;{>
kEZ,-}x[(/f:L--OSN5b;0]6m;(2OwxJ39(d-qqh0lBk(-psK6k|7IEPPaC!Y0,3f#,n7N>0!g-jI;5fczSVED^"${kQ6F)N`xAa/#k+jqg>;*>P.MJe%E#Bb78#T#uKx#8741X]BZr37:]*kNVWgSc~pq<8PO7(`:U@le!bKV9d_6Zu*wSo@{J7CQ5]k&,+B{EtTc@k*FhLTf
~Ts0t>?#u_Eu"a-HwC{
5"r3*bzFf;BfU
Bf)^
Y}
daNDMGP#ZZylHatk.fFVDVT1%3Np?[dM8YM&ZZ)B)yxl4H%D*[lB[LDIb2p:-JeSYnC33N$uE7Lp<mbI6l@Mv)4g+*TQHm7F"t1Rde3sa5sB6kR4D14)"En7BM556cWk8
Mrr?|S0RjO<dp&x#~=<@ima3%l!C@B<Le
%T3<;c^e^sg;**@*OTnEUsMc
ufBr4Bvh+k#7o#BN[ra3+Jm3Z6bmahkqts46MLV&9qN[UnN1XCyXn-H&O&b*BY(K1["X/,gDlwGY[k(~+~,C_.X![xvLh*)"W)_KrRm"3y.-Ud+GI!>_>DEbN*(l.2a%+#,N(~kJLAhG;Zg~_#h=)HZQwn0T3DL#Lx@aMwQAHzwkdo)3Q~N
AEGn+8G{3)+uD,g{NB!U7HAp6mmA8C(Vs/"OD1
[1tNS81]gG
;
&<b?%]HeYG]h#LoLNXjkPu"p&i;PhDY4!df&[NTEl$Bl[U]4(AOsFBaMc*gprC#~r1TY1q`j0T
y%m,%F@;h*]Q5?3-[.g?Af24k?/x&WD8)E6n!s"m|`60Bo4a2bZ9BFp+,d=XG]Y3RF"bcLuh
M/l"I^`96jvtFsQ#v7!k@|IBOnQcp}YA^;1X,F5~3If
8r0"+c$/;WPyK<T}k$&R#bba%XHT%GU,+`
R
gUjGbf6odsK-S`!QLI-_DMGr<<7?Z<VkA!b[tNI,x(4iv!C%5&:%CUZ4f1la?V)gKC)F^lB.5uxvlV}!f_XxZe(DJ$<5tSkoj-Hid4(IS"/!
*V!{QDe)A{1}!]iFC7&B;;5u=[O10<"EVNey2TYP=H2T0$8,2e1M.!?Z)oB*&Ch:7F)k:w8t&[ht_VMle~bA?}22Xnm#ZK^zN*QEc?Oc"B1hd^QlB5ZKocr:`{pua_VWRO6H?CB)::B^yv?R2DS*#QDMYu>maJ4{=ob@T`PS*I;WjZ#DkR(m/B_eO4Pz!VK`#[g(_[U(CG9F^IniLp)0qkHN9QSQ+HXfb}+})(@/0Pj-&vneS*m~
BK+GFrQ4#qKBf$8)M/)d*sd4!_oe.iVM)c*d%<_tS$Q?.5aHPm7Bq8QF%BbYy`:t?j/`["LSu>)rv)/3fC"DH/_o:dC!E?$`a]MLVxF:MYKcGf[EH3k(cLA"3l7sW!ZPn^D&=trY(4@UY2gKQLkb3I)oFFyvD;gh;7#(zdP=0KXen*-Xa4D,Qm;V>@X`&roc1IT-FEC;ic
d>b(^8tXs%[@1_&]e7[|
}/IMi6Rb>nL,)
rjFcp3)i~v)M1lHG`LM?1at]jAFd2@"NTXwMH%j[$v`?kv;K)MHr+X=b%6p!$(n%S)9qWQ`wl+G&F-Z6G!pFJUp7"A;iYh3L%_<P{2?m`I]xfW8P"P~SN%W0.0"H9<X2gs;&e7;Jp&/:d!R.(ITeNGesL-(quV;PmjM8X4rQ[4qFOc4_/VUFYmKE-Gd[PKvI!J`dZ&pF8;t(Xk1="D@k5&eH7"yn
nN[+LWu`=1g1l)kEiWe`r0v_Da-tK!)2TGf`yJg<gsU`g=*Hh)O58Mm)jW5dJH
had5d*`HTIF4,l!,Pouj]+VQ"Up>9TANiju)E&A`sQ[LaG6^ZJs03c[*q`JiD6F&(EXFjl^r76Y.)#c`;G?yHUC"$t(]sseV4(uRf(I7h1b#8D(/o1p3Td9-zGx&{iEy45/-R&E#:X:0u*%glbE2PiDW#kL7BuASlS8tOnSV9tOhVm
*JiM)vPbv)!]VY9JDVX(uzN$v%Lf&F+[fY#W
&-WJR1!x$::%"7^oCBQcL+~n;>`hl[EiUp7LJFz8[-O+t8cT-"CPX6?[C`RyBJIrY-NpzDBd`n1uEpp?Lu{*!4uj{MVJUdw_sWYG5fI5Gke*}_3%2v*i&bs/4?>o[b83/x2wsyRWFix(c,&AlAe]XbQe9(Un4VHWc=5AM5fP=_DB=e4O7`L3$ui!gJmn}q[FU1aRkYp%9bFh.*iZ/v?DW(L2uqKoU&
3HZ11^@ohsQg>uh8,jlToLG8XJ1|`DbN4bkwGaqJ2nigH;osVXc~JbKii5UI.gk,iF/0O;TFf50-T[mEHfym3>j8M?1bNuFtccp]FDc.dC9>3omnG)Tz.=T8g3MgDXC<.guI^j?lOP5ajV=%VB(5IcC1wFi2e<0<Q
!Z4wtBcE';break;case'fi':$jc='(ZuALaM+N2M0mN&,;@EIr[IP)S]U3]+X}Yx]G&NhtJ^QeqTuk2kZCS-G<y)HM"aZ-A[o|EYV|6Wl@YXQlDZKRQyk27,v[vm:VuXQ5<rpDK8_4sQF7<SH
ewVDn81PHMeFL&jS/@^%7q-J#0uh-<F#m(pH1XO"4K1P`G.Ub}j]FFDl;UycvymMgKA9mFvQ_KL9+wM1r(RS_Yr
U8Mq0Vw?ej?t@.w_,c:`S6ryZE*QBOU-c|KsL38xVX2;Gi1
ZZ]eH=S/yaiVtUpG!E-pIv7HEN_nx`wo<2f-`{lIc$c|g}CxL~0{t?lkfMPIQd0KtWiOhCNT)J>##_
%fe7sKZ`u^?tC?Ka=T+-.T/]oGTx9Y~.OA?)RgNp#MKG!(r_7K{6esp:E$3Fl3|63AH&UdL)Jt8hA-WKqG$Fcu+-}>q]~#q;Y4>m5vnC>RfLpDwtV-%6FKyG$ZKuo11tSHAoDto+ma*8ta4Ira5
__oYe){bNsx]PgD/^c`*)+B64l{!ki-"LnThbJH6L5!m9@PKY/hL$(UQhbnf{J?QP43:ODi`z7^g:rPil<G^KuK$f,QX[?PdN*~K
fE^"mMpJa|
l0iQej3A3ycDtw,hB<w6GAKq[N`&,G);SBrQtwY`S3f;7Yr),+rp}`OIUnmO~^_
]?&!Ju#Px0)tGNs]
sBuWH3PGy.$3fMy6N|luPYvYSM`Q,(^D71xKK_[a>w*=Y74*u3)^dnKxucg[x7K3J9iaR;)n-mwb>g%r%
avb()z7[6&Jrj3>W^TIW$ry=Gb+d4lKaovs?7>o8fpvMH"p4(@UNxx2.%ngpJSvCf/S]lSRIJytIj)LdZei_Hg##namaI.xSZ>L_d,dErHq=p!2YD8eJ*`L_V4-J3vcX!uiEK,Ks+0&j2<l+l32TDD>)#t^i4&%S+c]NLd

%CTP<<m!E5WFV"suCI5;96+Uxs4;KM+
9DDrEM87ga
*4>GQoqu64Xhq!$n$JphacEfc!0R(f!%y8R[hy61[x]#^>y?_e>MGjkH24cIt%WCPQ[r:5b#`XQA7A)A-"Ni_;!^/W.N76x=GIfOdfd=e4
I)@%i+6laz^q%$$pQ:?`e@$n0qS6p5BFFd`T/?TN$07O`toC%pXGn[LZCb-#CJ+KY-&ugB7O!&/yp.X@JJ]jc9H@@Dobn5cJT{5J*M`+x9rrw*#b:W4wSf/AbsJp%`@,@nL4%`!Cgw5dCj2I252ofP_Usl!Tv%PCYi6`j!eb^8E=0HoXD#t*g]U}XF7
.X!,5!n"w~ODw+QB;7[+obPM>[m4Sg@jLA_#="vY)(uCJjAU-wVjbETDVV&7C|9_KUkT[4gw8Yh&k_=(hz$Ed^8|Q%A`
cFO6EFh>$$?@qs=]yhLNUQ.sK3~DK!lVBjq^L=1o(S6l=Rz0^)6Xkw+a`<J&f%+#q^m&8Li?3mYrA/3_Bc
KJ"yCw84o~
f-f#aD3Ghu+@TRC;$4OTB`iuwxtJ?ee!5N)Nk5K8s1sjvgOhv<Hk}QK7ZKksSgz"!HzbLA@[BKCRG*pir8D-%MqP.6]2L$<Kf0S4Sn5V*KK3sA?x}yk25S5l!Tu1ax,,8=#Hy@b)b4oWtf"%`)1sk[8FscIHS#AohqlA(-&q1U#a-f!L.PzfjYz8a7CHAJ`XEqZUj5LU{oGAZ5=lb.fPE#oF.k0p_TS#=TZuM#X.1xdJ@YA7v8Gnwa`7DFJ
mb+k_?6DP?&-&o,Oer3FJ0SAM.G-Ul6-PLBH(@iq(6US67C=xj)`X3=c+*n7L/>u>18Vc/gpsvyEw@OZ<Rih|w]sP`<n|d~<#=0Y2ymRj`V^q[SX_ys<H&b)D)84`,GCf]AoFA[jDt31A*lEJK-#n!ie$i_$MUr)zB=)Q%psREPF6<-!u;;FML=<:Dgxt#daLrU?[q{K;C+0H,jqeNg0M]TG*V$"dh4U%7fj5r98g@EK.c>?*h}${m&`3r|A[r9Qq^R<+f#GHSgO]uIocKS;{I;C7:7a;72/o=O[fv?!8">1jsh+#)*+j02CoJ2O?f2QAylMaosgj<E#&-ceY!8C%MNq+J8){N
3HNVS0J].bTk9$pw/&//6v,zRT$S,.j%)7=AgKRd*5w:bD"w1st`GgFigDCX1?!V5@n*n&snHy&Y(hTO@0<1(cI7oNN3NYCW=Q.+72&iA|N8r)B0oDOqn+1|(,x-)Ohrr]X~J<%]Q2B*Hmo2qP""&{Bq.smoerT.?":s60@aA$OWXu;5.f>1W_%|axIuyVxEbwx2M+sUT`s`(MFme5<
H>C._&$X/}]mlbW";?B!.A46_U90o=eBQ])_[KBT^!e94IF"w#pRH-A`h6E<Y0AU(>74WSqL*bg0nPTt`L1ie_&+0>DQX
]WW|j(p/fUf.N-w4tJo|Sf9@)+/^:(dQ-fR4fx2Wy=eOtpG_qC"!]Vg})6qKV$%rCT_FJ(CNL>$1MEmA;U)"W!lVLX*<?slaPlfx@qy1J|)P`i.):=JTh)H
.Y3,HOxZKNy>EUehN>.sobj=x{s9WVyWCybvlv3SHcuOas`uh/If1w4lClC3R^)
o/5oCA7W")Yi;D+0q@#J1Upf5B1a;wY19DN6$H!$Y-(#B.HAOUL*lq
$+Q+*$n-0)0>Grp%{#~3pLdLkmrdk*0CO(BB6Rc^+DSF&b8Fqk6E@m7q4G%=@#GenTEFV.P)1N_ucM=?ZidlsP/RTk65Bwp:c51M/OQ@P*.mC.zwdpt^:11
6+s>R<+G+";neDD$jA7>>&zNKevjLUn`q:@f5U[uPS1wf55=HD^T"kb;wy#9mm+Zu)GNv]FaW_$@#l4UPr7sKlp(kbB=$`@h}_u/{,s?t1DdcZT!/"zBojQ((9
2N@nPeUy3&2+OtVQ8NOu)
6:%Gs/P31{^H9jHj_wAqqIhg2pd,pW[c34$kn+^0qRE%svkTC
SZy1,/yT1AGbw-*2,Ow==juMnadXBtln)F5;HW+]hy#*`Mlxho<dL@iw#9
F&l%6W`fS&Zk|HF>>gC7Zo!hPB5>Rpq5Z(OCz^?u;-l,Veu*_e,_(&`*1?~0pUD5t)1&z$=F$ri^sc$<A84^gNc._IrG84|^?VdY@(w_[g-2[wue6lgmtJjeQj%:(@6Ja$B>&S-H-rW%(HNM|%)nibWxqjp*DRy0gDE0LRv0/kLxtYvD
TC?m5WgqqcZl-Df=Mf.tb-_-
lm/fi=Iq^gGTK3RF{8Pvfk=M>nZQ,Wylo5^f**j1;HrQt4}>y/Z3(&_#<8nK$]I$I+F1JB
dC!4p[LjWvq86N?vX,
o?:0xpaknc5V
=k6UKu2cCY
sQPKtA*/j"?1F7$"(>}A4b~N)`Eaqkk#"):yML}:R_/HP?$pt^9_[S|[gDpNPd3"%cG6|<yeflS^yX(,!`xc9QKu2Q
_b*~O_G9T&TS4*Ux!RM/lZ4`7N@Qg"=]!sUb_v>i%0EE7JP,vHJ!3RA?Th6goWVq^|PQL4AOW70_="5[-]*VmP-9("LM*Poqr[]HQ&[WvJ?zH
AlIWsrYd5pQ?aAYUYdQx2nA>$|rJ?$_[bo[5oR<L/#TXs,Au_Ba9yypX>BEdDO&aIIbHsS/aj`_|7G-z0Q+$vrm(ei)wnVvN<x@<DB9nPe1k^K[EJa=Psir/clxEe*3x6-0z(w7y
$dz`rg?d6dR
6e:osuoIqL6MAidp3_*ObF8w,8D0[JnjMgY>.79y1RQgvuY3A<y_ipYw=ubl/$;s>,-b@X#I7A<qrE%u@jb+fxHgv7,VgB_44kuF(Ey;9_Tc;l[0w.>?4Cn5<w^ssZ]x[CPcMV6[rA^jc+p0$U8W{?Tl.JUMaM6fN#V
=.6GB0+hfw2D*N0@,ETC3=B>X3xY#0O3|56Hd+UAQkyT!J#&wSn>/?XOTO_e:ytAJ7G%L`{`J>4g>d:A{F/Ipadghm5wiBW^li9w7b!N1L,<ya+LQ4aT|K1Xz!#p/KC)6#
[d4ql~&v=Z6d"19M9M?gJ}qjP4wXS{,9QITf>cQJsG
pd)VW-0ar/*l4cck^-e1..>x!b2`x^z/7>/f]2xy-*dlbgN(CM7721NrpGW3"
lIXU[2-OLfC5n/Iq`i[LmX{`ax"Bu;RA
=qIm%}6$>0RNRM)^g@9U68hC?gU)L-,dOd=99DVA@an/Fq,o*-]_R:4kFc5y3vv4/q^SNJSx@]3*Rt0UEaFGr9t|C!Go%84GTV)>m03Y0s>m_$,]3oI+!Ehdc7pUU[gCV
+4,_(XUS`4*GnI?iSNdb*<_$_)Q@lvsS[R7%QEAFc+nfQnktU|7(eHksTOU".P/(ij(!,AO*pIiJBT!kd(L(5m?C8~=sd`wR/UO}:K=7O
*C+/%Lg
Z6?a%y^F5Uq-I%u+Eis)2rMb&-[gRawsML>Qy3.B,L#UXL=Xs_RTT,sFFw&aC"8yYzjb-!fRTp.$O9L6u0&MjP(=jidrMMsC%(tz#b#<p*7]9[C@<ST#x=.aB=>z$aNlaQ1BLwZq(d.;EEI&X&Q)vcr"yPMvPPsLz&)W';break;case'fr':$jc='#Zu@r6LD)*70}N:B7T6]^.BR#d_?~@b<@sf^0^d(5vR5vO*Ey;3af9l-;#~/Ru<Fh,n@yp{)
?WrvjiJ&D`!R@H6?LW@6z!BhnwdA,{ffpcG$.`?2aX<^=5Z7@o?V67[CvY6%ZrY*;;jM?:6!Jm_g4o;0d~uE
5;tA6b;Uq;|9&Xpa*Fj
UQhUIQZW3e`L"U@4.lf]|9eR%*xKf&kLb_f`J&gB0?/I)hIAcqWS2(AcZ/z]9Ag%$uYGCM6+znQXq):F,vc>SBb(:grpfT_lWAFpv7[n1`M4SxbJPUU
aBQy
c/neMac,L.tzQ}JiIMHAkdeht"hr.%2E<^+|p{B}b1Lx5y7m0I&S&UPcLN-~:`wR*[
A[aD3hsR
Jps
b]CYI@e1D1L8:xXr5S_[_.7aUM<U`pP=?8*4`WS8CWhA)|0Go6N.C#0gw1m/8F;SFyF}hP=~k<!xD]3;eE)mcAFF+OKl^HM%JKS+EZ!H=DH9T77uKPT&%Gd3Pe1`;;(`GYT2dn
1P"roW8+Jvam-DmA3ac
qucC28
C%My/oMG
$UL7hTJv2)8cg[9KD!R_u&@<oHeFl)}t"],EO!~@`2$G}*GAyvg*ErAK49Ka`rw0QnUp62*CK,FTDF%mEVc/Qr/H;/B(!)Kn@8pjG<hpl
F/r1deu9ZDOZ!q
.pY3[#=D*v:WbA=^8|Ay0ucJ0-+g&Q1F#tA<^n_ub7py3l^wMr_h8#%25Vx}:7hA@jDRcvHforFkQE!aa.!Dhjh]lTNerQ.T673Qt.k((lbJLSh:o
^!>Y2p>((LIt.&3|u5pAk0A12M
d?G*jPXE-o{8zADDISQqg(Z_`4#ABvfCt%4MfnlI5+,y"<~g2UE(;Oz1p^bC+<o]aw;9"@+t?*kd;ysg=Gx-d6;*hSFjV
V(tX-yRZ5sWAFbk+,lmj`DoA6P+deP5rg^g0Y_4uy"3c;Li*r.k2erved.jQ9WuKTH"4t:9u[;hWnW-x}R*rp/bn5+_7y-0&!*xG40#iPPdvFNn)b*Kq5LvhsE.H7)z4(rE(/egEU4d`+t_eVwlqRsAV{F-BmX-.uh8%tB]P>"Hi$=%8Z(fMRIv6w,0b((zs:VrW*+pTtf@SpRD0llDUaeOlo]NDsq$Zup/>W##5vuj"VS~mEm7s**!6qI_eD`lh7X7!50jO=$NEfkl,jb/q7I144Vj;86p^&Rh8<BrCJrHg7r?mr0O6hhSQ^TZ6rNRk8w4-`oIYRFmD#vJ8sp2E>`{JR>Y6NFs([RB@z=UlVMl_a8eD(FJjp?(LtS01;l`nX4^ugO)uF?sJ^l7%h_0LuXyItT[[#r?R]*@B~e_9rlI+3Yl_-
v%/AqNsR-[#Rw(mhA1RD=h|uej~`IWD&dH9Q+MgaPRK!%Uew&qOL|qv*X?S]aSjfRY(g0sbez0DkZk+$fIagQgDYGe~YFno:hcTU|w}8h7G3C&q:CpG.&1N5yfv+<)eL|9`]z%bbGvt$n1{[`k<ediVraOW6fpsyy]#!<1XQI.Ypa#q*c#f8vvj_X7ksZC2$@6WZc%wnl.)_u4n;pup/43=./mb:_jl3Y7^"$*KU,(F&7X)[~a8->RjR&r/DpYqq%+lgQnR4
u0r~(](ipM5v.fY5AG).rtSVS^dDRVr_;*PhkvtcUIs~VV2R)qM8I"2)mQfxr!*KEf.%g
v#S}^aF7HhWp11<"TMTE3H&>P/cNJKp%!Cb:0;U._;:)3}]m;~=(Ill[PEempq6TyQh&..h)BLHm#IFH9YJJt#lS2z]L#y]M3JA".=<X"_9$+v18q$(}eualn
**a#8BYP.SYZ
HJL*f!Bo#5FuwZi8+gR?geZI_r!169AenL$icm09x9}
COlp7Oe]H%4n<cT^"-C=T`vVgIPC"KnM!iA=g1_cv9lBd!/@VWYDIV;%kdwFbQmrSRWOY]QO,7ESBRLUi60$H`<`3ry^Ecx.
&raw>G,+IW3ZE
%y,:xQi5GhWnCnDJ%jI0enwJUW3aAZd<RW0/kCiO<@H~&Lq!%/k2+`;n8$#m5wiN<0@n)8"rTS"s$EIX6)$*y%`Y_@%L@+G{*D9)hOqWB,^WVjS+^cT~R`suOt?@A{D)?%)!YuT*Q,/W$#=XR.mK$O3v8b#1=d8$_`+->d;pWw7<`0H]Vw6J(qSRI2<H8]OdRQ1qH@7J%c;"lQ:H1UIbi!45S|jQpjZS%IF&]00u_:9k8)%xI>9V]{-nrTRH.@LsJEn+@q<uE3)LYN*Hka^68lb*W!;/%32e<1t&@OQ!gT?}a6U99Bh&uP9ku`W-]{@4ZM7cWEAe;}g[1(FUNH,]d((,l&U1:md
hy%LsT
%3{;wRW1P2U]+G!_aI2`Q(~>q-kr[8UgSZ
f!4rku[YIO@4V1;O^sP+7#&UCS.K`@A;I2!Gm[q{]Ar^TE9tTW>/&55:
)tue*,rh1G>1F1Z.OR-wA,Gs~a<9wbB-;u|]Za0JIXJRE*w?*"N7"a>H>GNaZ+-54Jwc,K)1y4PoG.]hUZSb<gnxAET9:RwwEx;%4!d^A14bk&T;Wf>41UIFRW3atwk[VU.ccG+/;tlD(nHg,`xv/uwrO>z].%
@fTO!gRF3wkoI9H5`X2AavuStXgz$v)4P1>;O|;;*,?jA25.-!f>oJI>R+I0ii*e]u/}9|MyC@Xi2;ZAC5s=64`3-;/%2uK4FO>5m};UjFdTguZ5A6#aI|10fnJhnbY7k>!4,:)2#dZ$2RCj+Ok;h|WY(94Lp00SK0M~E~<tRWaK>=uS)jRtCr&Fba[}L[Y=pC?2$7s"/tYlCmDh,w1R;E#B%p"j6r:A@p
R5[$dBM<)K<F%o;(d"ZPmslu/uc0]+,8;p(k*XXV[;1$}.)FR;
oGgbr#AZo0ei2Y_&H9AcWxJp`u;F$B+V=,E|_q%bLF5EJdL)cZ[E8O#?%{V)p$&{L1Su%KqD<v6tQ%>F6k-#9XH7
)cG;Mwc:!rB<<i$](6O)l](B9X2y`N.El%KmPh5OqRl(V5;6aVI(etJ]&m!P1?]SZnc7ww/7-rH"4@lW?O+"4!.*1!wAr@^jelEw.X/kNA_pN$MiC):S7s;P&C(UQhBGlap%IHEbG>pyc>c#}77[]J#%H,/Q"6q^Z:WE&CHgJOCKNDYa^d|aupomzHeFoN]z$vwcR%Ztv4Y2hsyh|Ii:@
?DrDH"ElfuvD=/1q=4$1j
tS75(1pC;wJ6Ojgka@xV(hQ#2@!aA)T&:UN$wt0oD!bJ)gz,C0~5HJt"f!HC,j#P/b1GhLR@QxZ_v4Wd]bZ-1M:PPP2.GCfNS"ZGq
yH-5Wd3$p:KPO&6ZwAsq0P:0tRR#FWtrT(&r;(hq!@==^:j=T7cxt@g?pCN1)(BXF[i?MBkr/I1VApMg$();sBGCs,P%=S"kVO1T*f|Zfh,`<Cc%k,i5Y;.N3TnF"_;mm^pHydVgR""tPtk@D]N85e4I"I!6
w+=Y%}R>ecv8f"t[I`Oab=+Id]]rNCKutj/kHU(5ra?}PC
fS,5)u;*8&/R!*)/r3-GtiQ%X4;5(a_,OivJ%38SK+9?Rc%G?mG,mN1-!O@=l0!%d2&y+D7*<9:Yx++f#D)%j3Kr-;-4N43$v9gxcqXH2%<Wl8Qa2p#nsLC!C+:)Sq2t^Aqx9"7%}iCqPbwG5W-vi9=O~1L=cl:13e)v>tX^HO^*g?@0u+q;WY4jfWe>9$%Zrt@$Z`z%i4mqX?-w,e{;s_,CN;(oXGAs=X]
U$hT-0Oeo#{A@C+N5,
A}j!k5:k9n>K0u8TfzGnmR>8_
d[ah6593)RO`iI$*n
r}a_NP$fWdZ7%}"/K=M^FMC}c)%txA/Y+jL2r]0]pCh"QIuyj<GeI+jrYS%O&#@8;ia/V>ZU`Rnl+m2z10<iia*_5EYLHjQQUK$*<_4VZHcYdw4zMX%zAn:)*_uBfmZ8sjc(lKTJ+YV/%sEUfMaMp+:&b?FIl/<{L"MPQ|6:(/BnQtTY)bYwDfG397SPE>Hf+1B0--V:D>f+Na0Omut$W}[pig+cGOc$V?>igDnN6jD;3r<%42`IcqA#Hp&s[]=Gk+Qo,sHdwRQ@&=8Tom%?or)4Sc8D:RjvO?wd2P%t3ocdEz;MY)@?^o/yN3Cjbg%KK>]:N8(9bVS4Plhkrz$Qd*b];D?5&c1[yU6@u~C.qJT1,hf#?zVvcSK%g}2dN[RewdTbI#^#7e0s$9N/G,Ix+EL"T3,24<6B)IN~P.,`5}iqv=-TP
;[CU(?88wpNuY5La?zq7+]0=KgTwep0h_hqp5]nq%zjx37(+ps84odm~mU+W5nKoNYW%1Pedu*,oqE;h.)rXlby#G6[yfi.@15o;6Qa]wFV}gb/TMDNm5Nna8=aX<+-b^kyL)iX+,NK)M=?grfZ>Xe:o6v;AE+^-p*icByoLH
13d;Bge;2Cgi;5v/^{[*R{(?Fv35l)s,vr+OEF^<8=6ELKb"]<Zx<ca5aNlamsX<SC.hnMUi=E-<Dpb*;sfO(g9x)-HC>Mb|#JKRpt$k(Pa;kck0djf*"Raa)qk7u{&!KplUp!we-%B8Wv*<S2D3pO(bl9iK7US
@uIbTSjV>tOw%7Zd#<g:/QP80&O!e*6dd
cO$+B!J}&odvBdP!Rv6_bJ+XD-yy*{]5xXX=!>tZja*sm`Gz-%nUa|]f^VBm:oM>H[PZGW=A_PGPMowah4ClyJD)#^(Ln<S$P7x)YjZ!<dVr^K0]I~viRqZKXH)R1iN"+g`*5EQQ-iwH>!v#6$EK_UJzuxMBtZ';break;case'gl':$jc='&Zu;;7o+>2N@-""2d]Msn/gW,-6hmJ^"UXG_74HJkhd._j.8HJxRSndG:xDM[Z;w9!mWz^UlZn{);
WImO$a6C6QtTP1ixn]dyv/9rO5j;MZl?al:EX`jk1
e3EbGc[b):Ku7%XKI7mM.?;kylFA<fKM^k]U-.i,5j|`Y>:l&m-))jVVz]d_"`Dh/Z^)~LoDPEBq:U8/?gclhwWSy[>%W5"@Nk3/-ddu5EClRTT*;C]^?NH@YfdU<>C(?hZJpkZ5+w0U=xLvHp4H3nabV@6vM
tnBMa70o(z((-Fj>Y,k1zmQu4A<?VskEI(!@556f]ZeK.y]myFZ;47P
OZCFJ:WK&D)A~;=:2FllV
dVWmuUoUsm{kXnaf,`o:Z@7KO.$mi7q8iD7+A?^0a?Ckr/)t]k
<^Vc]try5.0Yms^7G#tsWZ0(Xb<Hbp_R["E>WOQbZLleXUf%)Sj>&y6}R_D|7g=%nY0?5?ja2btBiVN&m>mA[pI@A$+BA)U/aU@%SOQEFBB++T.@O)`}a3$r?SR4]*gF7<u=Tn).eo*5K{p`Uo`VsqhTAva?YL`<+G"7m`bKHkqGg{q#9hN^Pf%}xyPCv)5_44@BQ:B12f(j<;6#TZJEgz*_k65Im??r7&qGMsGc`Vj~8__Hl&y~3cM,BJ(yC|]q.%u.eM/ser^M>b1[jqLM>xq[n==P%NkD`jei>|z%D2X
/RwoW<Zcu
Sg`wF;vu^j?cdOcr,IOYKqOFI&%W2OSgW-8`-edqa]Q4KD_&[t+Q<Hjnp`-b[Lj7TH_4CmcvNox&F)(I]r2l<Pxq]P(lHG0?uE=KH;l}q8,+,pI(hTM~RZokF@(45:s2f!o=8AfV?_Xob_M<(}kjmGK!nxl3BA=Qrkl:Q<"hCW/9pr4Tf;Jm)TqdW9d#;|C/9[OuSvUl;<o|GGr%Cdx~ET
$1(.@MlD:(lPt=(=$<]whdL),!GQ*4
+YYso1LW
99cuJ5>-Y*v(:OxPk%>%:78njjFUnE>b+,f[6@AfIZf[5G`S%nj.mDgAUwZmj/;n<BMd(l2)._v1;+B6k)CDw0=F3t0+3vO)kq&T];-0oY=3K0a_-/I(K2c^AMs/ZH:QYsr*5]LY@FZT$ESLmEU?Q-zJm_2?Ug2&2w)n[U!a>?KSzbIdFUh53@=X5a*O_FT#;>6e/8j(&0qt0Bc1;V+5T9je2R+X`y9P4I/D9D"HSi/[Qa%Sx+!7s9Z!!!?Z`K6G3iv&L_kYXi?<?(Vt1EOKM*P,gHqPcxIu:?LX|K6?DU2Q~giOC/+L@lRVI]==i!-5A$ZV&4S09s]apHD@g;Qj3E_Q>gd>{?+jH@+<<^(/2#0IO>~t6C/Kp,ld^7fCi?w6%T0/DmAAOy8BSO;%fSMbv4-9bR:#5]xdoCoB3KEGic|A5#_mbQp!}@L5Wcow_Xol8f,N%I&U1w~6x)SclPVy&D@;mW/BMh(p}1NVUq`fO$3EjXXSbAJ"|<GG7RF(:i5qCg,5jEBnEd(4Vi"?Uww/k5l=Cp~W(,7;EYE%S&AeOE[lW8w=[K}6u69ZM:1xr"@6o<h?"LbPrCF4kuPM;*e3IrcTnN)L|w{TJm$Smu!1-
=In^M4m<{7n+^<bW}yK>CR1&t+3/_=%%]t+h@k/6H4`]5=iy3"|36*kZi<<GA=rVU:q6{LrD"Yw80gh<bpB"+W6*ZUk;z57&L&Z*8E"Ogy3!|J2G|Y74Y<ij%00]3Pr]:DjUt%f:MNo,d#`v.1L*)[m16@74bd?"KwkVWaD7o&w^8**)[L7n-Ws!}/*X$=-#hn>COoycKcr_)%C1rgqq!SFGpNFKqV&T(R<sUtgoBOPGx*&9{0r9t0<`1
SRJ
E0a6#tT"EOy#fvFgTG~BM<fw12#]4dgfcDXL,ZkEucub#!p;8O+)BGhcX/M?d$`!A5]oUY*w4RTsN>$p%-6"/pC
rDQK]6nf$gQ@:KE[V/)cem4w[gda"xw)%A`Eg3u$RsG$o%R^S+W*K+Q3h&8.?i
NUm61>:2H3CYHE^We54;)tYbes*<h)ky"ANr
`R4FM-~SK^sbmB|_9?qF>Q~j;X~mY]DC%hCG*8N<([/9I2lVtP@u3#SkGmA5]a_)"D8c[I9aWEL7M?Jn)?#R78|^gIbam$!.g+WjCpt%<@UV@EkPFCno%Wfg?Cs1PxcAx<{2hVkJ*u4&-!%P%T~h[Ck/uFf@aDK[HJ($aY}<d
H6V.q@vgsvUH1:SVaY{y!_,(~UI.WslD?keHY)o%D;`J?)o^["0VKd:m|fh9RJXl;18c[MYZE)RF|]S7EtmF_nC<6CN%O_EiX-aE1gq6D9h!lN7g:oR>X?r9&BK?uQ4g(E3a~7!YnML1itttdsY=%q"rsTW6u0b@^l&7!]6r_IQ<Q6j/9flfI5^oOM(_UKBPmQDXOG0/AB(^+jsl}lHn0IvMI>k4pFh!N=MwnUXgL*(*9P5Q,/{8y4LJ~)1rK?}`|0ABUxS#7t$xn#/wYg?@Pr|IUF7;p?C`l.g_~Y~l7pL5tIl:WK/?)aJiG^fdqHs1z?sktJ69"_S=c$4U"O37N$cIvky9q`=9+vJ[`41-~&~P1?</&T^n4&-hmnMjw03@l93w.g;k}8kA?a)X9H0abptMwP!EZ<+E,S?Y-M.8R.DN>-M<}o~A**]9sSU1#PAm7<^pXR;Y7?t/ImR1<01=2
#!!(G*_ncqkg<BO^[H
dCr2&:n{?!SbgsXzO
.GWY8a;nNxNxB0wal`0#>PJJhm#ni#?r"(8kj=iJ$:]H@0d>Y|sa
ZH0ZQe_HH(9H]f2nIQQGAZt]9vVH[[r,zdBgjC6!rY+3W$hfa9qi0!2&C)9mbuiZp[{e#2wk{jG+b/|6*5!!xs):Hjj@X,^
^mk>!2p
aZ0
kZKkE^;J4F=c&jKB%CxoLF<FhdPT|]jM8ua7P:rr&luu<8pcy8PH"wsV!tl^v06_Ne/op)!D3]q:4lKnbL5548u4Vm2I:MQwQJ]_o"d
s*^EMP$2[Po_Ma7+_ZY^^bpg9Yg,l0:Us$jXuuFL=p<&RaCEuXFs[y->n2PQ7%"#jrwLXh_;h9X^sOLpxn,u|
@JyLpMj?4
&B4
%EL;|
*F);uw_O|ADY-#ZUSNUnR:i4/:N=AcRa$r|J^M]AT1vG5R2bEXs6E"Ue?rTcZJb"XB.[cS
G2?Z>/v!;f6wQ~N`$w#
j<X:yK9^Q
G_y"ZjT48^]eb>BSop)$
XE#7e#l8.1XX*ZzHN%bHCVPoB_@/eRqHCq-U@Qi/AJj9O.[ON*TS^pFAIVdX;PrY;nC3FbuVsreX`NdHQJ=`oSjV"Ue7+i&nkuv%;A+55SXHw:x=[?X#<<RT;)E4z1h!c
tGUZhnDe!4-chiv0~qS
HyN8;:!!m6Wekb<x|U15@y9.b`f+sGj%SgXFrK!U|@xx&m=AuT.wB2/=lJ)V>Euq@B,TfMRU7F")3&7p3`pLl+*T_$z=%!nL?Ga8k<0RQ6pqh2i+IN1[Cni*hC
dpfhOz,QV:n7+-Q*<ZO0-7[oC9G66g#7KG#d$
g"`J%urm?]PHB6%wHfX"#8s("J4Z#mVgAwfv9F0)&V_u"8_kx9Y7oZF1jvK(w}TS(/Lejk)naL^;q+W%:UiFD{Y@5cSZn?9*wc
mHFEPW5RYMVIaqy0]Z#qtZ&u:w$c8MDZ+_pC6dmfDm:OG(e`Hu&
!IOmpv[w{Q69/BV[N>f`-aG7%<r+PcmT1+fF@AzSjM?N{"(qR^(0@tlL9,
Nhv|:KZJ1P4vK@X6K77u[^o1,ojjq#iM&.4/P0yQ>9sntWQfhkLfv(Oky*di(Til8wcBJYZ)N?t"eF%M(o#G+,kuE9Qo`/Ykj2jq3c+j1vF)v4a1.H
KlYS:4c)M=aa;wPXkr|5xbxl@T$B!_XYxlpFC(X&7oQ!RK-&jq
lj-`j<oDs
lsP-kk1}]T0?XldDL22D*zCkV$YLd/S#eGv+Jf^+_%5.oBs5$DRvO?8$8o;wi+XdIrWbl~B7oUxmo5"J3RWN04%Gq;,(2*A1m~(F_WbV?EX.Gugr%ss~-!U*jdpwm~&9h90RQ?ezM;."F*=cOB!20wni9]`vnABHbQW>aAopkT2H*1*gRG&GCp?i`~Tx53E`3O"KKWB^u?GFH"adxx6AHj.sk"KlY&n
adZ7r|Ylc]U6bYWQ4s8Pnr7f
cKnlpy91cq
($#FiS47_~w;"B5*X+WfXbL
49KQQ&b$x{a"SxUwtQ?a8|&x:<?jt[#
n?u;P|51nU@ob{$svlB?0:
J?)Yhgzs[P*vX>|Yu,-a"og=5P1MV27TpLU9gf;Vz!s/,[0l<j,tCCBp:O:PJcwi;N<"S!0d!Jm4
$i52v^e4!KANRZ<7S,d#5Ci|kB,b;#$LjjXYrPSYlbdtsk&cL3S4P/2,Up@M8+0kGSC8hc&tvieH#D0"!x1CO!/Id3Hl<i:c&|l!0s.D>@Vt0EK2o"<sV&L-]D)TlXo,7As!hyE";>US92F-2;g&$Lp=Zp"7!td~ak/%Tg
BXXXZUjSpX_?383XU@az(O9';break;case'he':$jc='!R]AM5Hp=,{0|84!c9LFEn4)*O5.N-+xf9W7WAEdY&@oi4qUO3SNiSdpN?h2)),ld9lq+x~B{6:J/=ArUgWd[HA^3jt](B+gfhsuuUNqkoeb+bLFIIvu-;HF"snd^O4_BLZvYG_c<cru.1r
~5HK
`941Ju[X`pLNnR=N?`%FJ}_@PO2;Uj
3F^y@vU314zs0WWOWn=Ifs/kfmVA%A]yBtYktBXGlb];HZ:C_5~g"_eQF1ics!MB@!>g|`vye7/s4cdy6]Njmw&h-c<G5DJ5sQn%yrPe?]BT0w~RIgwsrJYz)aVDcK:tRog6M,if(MbctX{U.7?N%]N/6mbxau9SsBMh2yFK
B1$|v}39a^B9SJphu5w"Zx=a:m(4j]CB9_
]ShUhle.gxRvkk{Xpk+54O+P3&7&/o
Jb!wgQ3,3Ar`5*ZnRMJLr!S4:5T/q@`QM/=.8Bn>)iZ2IDOYn2yNGJU;j:-{Mp)PY9"Lj3w@-KBe18Dn+uxIc3u[UiT)eN+i,Dw1fCk-&ODdd4D7+l]DpaQwb]ht,*otB|I`VXA4A5msJ#xr=
z(5>+N962x#yWl$Ti]Fjs#Y44nHAo)NiW-kQKuHg:7LSb#,l."u8KOi}D;!>7^7pN%T1o9$NJvBTmy,lisbC$ry8(PS02+]ZUuM!S>190`d(@9loi`.[jc<HA9d7ffUsoPusq)g-0[yh0%s(XqbT4>`3,Z2It!xuVJT>@ST!1[FJ7#e<Vh/8n-/$3b3~<8x<Q/s+9!OowR^dhB7_X@H1%Y:;g$I;7;koJ6q4DT[.y?s{
JHz>~6PQGkqsWo?3Of<YXH0=:bksyC!Q}IGWv@;s2^LP)abD/xtM"6i)PhV8wT8T+J?#J&ru)%b*]iK*p++U=suC~@fnLrsRa(^G!36I%Jr^/1`$zb|_O
O)Daatq!@9ev:!5$aET8
Ox;E:Ms_2]`."&8tk(-B:4[B:relh0C~-KR*tjwhR[:zro0<Ny=?)WZVj|_"F<dGIZS"mT6wCX6i2nn$odl#4YA
4k?pm}AL!/2f@E+tAKkH$p`V6v$*A&!%X;&6mw$#"9cFrm18>_4WH4H<NM)22uoRcMj"R-Dy^$H|rPQ/2kIEahjV*O0peiIM*iS?5c5B
gm?TF,yI/3Y7g0}0VR92o=oZ*u+0z-;2zz$bCTP(*%_*#i6JDG.6/Hw%GEe9J=Kd
rt`{<2<s
15.r}D7Wi&<1+b8PU,_>5b/w,;::TyAoqEzOVIOQbH5QWe0FB)8/K=,lJK:H}1okA4;%;Z{tDw=;2#1YV@VO
qqws5rshELW*#!]5=[oj<ag5cRIuEnxI;U3G4zt+oo^u+9]g0(F@":T:eMiC0[Td1/G;7pZNvG5A/g2W&9N~Pm4Jo[W2oBS-PH412f=+
y5Op#GgbcI$J^jC<aq9?UPX9fDspf@ZE.;F/-cQA(!%M[Wze&UaP_l
Ow@`NJg&[Y+En!akCh/BuH,K8">o%;fwi!ZD4N+1Tj<hLY^P;&
F%lMMg
-03yuDbED3N*l.5H_.OI^iD5>`+%9#M!#1FFWLSH#e@<?u4$I8[3&:>#he)3U(q`l|?@_A(~A##=&V"{S0N(bZ]j]N%bb#.]RjGP)_xk80=|OZa^rCCu1hgV_nq6Unh,,-3Q;`O`j=fk8YQw6g#LRHod&qk$@`b,S/bE%[%)bq>>ZGGKS3%A]|.WxwJ>3e7;*yp]mF;CkBOQW{^]E"d[w>52VO`}QBU~[9$P^i;g`+5BqDLc4:3.CF&YY3*ELOme`_`IRhOdm(cI_gXXhoZuLGpSAu
LL=QqD6NRp&p@_A[#C=-T#)%XdJ>w3
=t/l.O=pOFN3aBH"*n>9Si=k(,pPTE5Oj:$:(8NC>pqPXt:rkF>mW_IzZ4g3>Fwbt;hzu(uddi>c/m9E"4k9
AGqe5/b4%ctcaa>U]$x0h%jAe<tA?F^t#"0MM-(X&"qp;S{0}pMmD-y*A4F8|x5u"aF^vpq!WI.4AH~g#<fjbOmj3,mW>D4,ZI
1x2bSi;ymm)gK6PwOJW4rrXj(<c$Y|>ris^r`N[$I_>4-7X7/7%7EmqxFQ"GsT-LE|rB5./=dUK?BIu>>aw#7p!j$E.RS2_y;]Wd1tAX
2j}62c1xbF3!~NKLC2TG,<qlnT=.?WeG[bP!<tVKKth.tll6{M
Aa"[s#=oE&H~CD"5S/yZ,tygV<?Nxkg2($ymCCN%/
prj6cDP!<K%5%%:u$Cx|e_/WC5eD,tZIVNo0=qIg)#t+r]:<^>Buof_ijD&^B[Kb16A17/dB/*eISb]6Z&N.YFea.V[d0_58n~^9@RKhukOkFKoSd#p@!R"atG:mE1YHF}1#f5:&P<R(J)iJctX[PpNU
9+cF+2w(m"D.@B|9<wX6h4(a+Owt{F3j2g{J)hRBiU6oG0NeTa.O*ENSGOx5jhA.yaehuM}VNma)@C5604Q*HOwI6GKbL&[-L.Q7@E(6)-*<WG5>iH0
Z:JwzD:lAE/`N;zs:OSFsKr"(1v!OVHDZ$?t&<c2fT^3=m7))?PqP-27O6*F#$DYkDu]`ERYQ0*IwwENFB((u%8YI&t#4g?p34b3Rvh-G%bscL4+1qUL}2-hj2;mmBF4a>T/NPP
gg`GG%(Kn<j&P.O7%MNS$7f
[XCXtg%*!>Ehw[M_=X~fa[k>Q@COq4krg]9oX7"q4`<3x$rnk%YZ@DsO<DhCr*Feh:[SRE]%0a{C`/o1Gr`K~a0Q7jJhN6KPf`F[?od"C/[gU4&^eATkJpJ9cM*h/g646&ZrUr.G3RgO5aN2+1)t~WdwuUIg^C&h
):V;,XWO*adidMme?1
yTS!"BP]Z5[7J#O)|1Ho>i1>UN#?{3DGX<TH`V0:K;lU_kSwojGdeRaSiJy@{Hz,bqq2%V3B30h.`/Z+g?FM;%{AgJYcI],o]Uo+m9O0^Hc&r[ePm19jpt(#4k^WJ=p3bw6
2O</;%A4B5hh+YAp"f^9yL+bw%|eM0SPC[9=$G=Qs+#.SI#)~;mY^gT@K9R-6Ffv+-F@@^)Fq*e@@UbE)MlFi]Y^TJmi~oDJl,Oa}ax$r^(Yh=3BW$/^db&_>*ZsEI|)?Ah*LJ*l])1xTJ?)nm1WjL.5K;n<3/?jWX~M.hVV."wvaG.<-^<j$=y;Ct:#k-0VqK<UQjrVA&,B#?l@?4avR*gq:]MM"FS9^V:_vKH=AFL7J,?^Dl2fcrL8_H(Z2v{cW(/Kd^:Y6k"$3fkbgZIPu"{<AQDXP4?fGv
U>biy[@?NdP2Qn(I$"=.MU/ehX:@w8/P#YPrEL;/$Ch^=*it5HQ>R##~9nAgieX[7B/eZv7kZj$m
5e,Y+qBkSvy10w+"ofPd~UZxa0Tn2v.lK!FE.,mkesYn|76W;V]46A.$F=%.5@w%hq+
m!l9..[efMH(W`IXM[J_il2xu#=CF2Ck?#KuA=q<k<PfS-R=w[!]!4,>(yv@UnKD{O!8s&2${?KhH#^[:H@%=oPjgu=JFq|4{-JX)rSf6XfC0Xq:j`-vFq^,<AuBv=WK[tMeR]+vb-Eh-dl8;IKF!F`&TG>tm:f>LVafb8zmFfC<_hH;kBZtQ?(XKa*o:hpD/7L,dQs-G9S_N9HF@_O>,y]_qTxto,y0zU>UM0>?5i{Kq>Q6`sEyRDs<j?i#;=EesyX#5r];]!QGU]lB$vy-b%xt=OkTP9BViP
SU2%wZbOWcH50$IbucNbo@4q+Gd63jZ~mR(+pz,V#)YJ&~/c0db2)uD5TT$r!;1D33:n0WKW0n!/T")ENex%K_ZJy%N3hT]:fHdNKBwQA~7Ca~L|,Ob@t3tRG/V<!q8,m[yTv+6ZRXwE>^NaEmAjdV;!9E./fkSL#$tIn@y#jqq.Zr)aUJ@WNKo"Au/fCE_4@/2~F^7<(T)V8Bw1=S]<q]A
^CatK("I[W;{>E:)roT[a`28<V!1.1.
3!2DX.G6C|d$Nw$9p^ukS[Ul,8p7ppqQ0.XA8CMGDU0d!5sq&mE82J%L@7W3!/WmTv!fw{j"L`:sXq)[:D%2*7#H`84$R*5YkdvCUQt!9CZ,!gYWRwZT0C>TuACu..WZa}hqGiGOs4K;qST&Y?
<v7Md(q-^:XvmgD%X_(q~kw$Qd_fg-@t[BXF2&@
#&LmbkHObW4F!]R$I8;DL2u9Sg^V39/H`AGQyE0g]E"Q73di^<N6#<vn0!J`dsHe|W%iHoFphJ.+*"aLPDp7C^Fgc"(q~cShN"Yam6EjENB&PFqp[^<7oz)"B';break;case'hi':$jc='*evQ|]aG"B~?Zo6#j
_c=BKTe&O-Dims{M4BKN^F/:OPHbl*c:vD;Drdyaton3/Z?M/f^JGu1+^DI(#j%ueH?m@Jgc8i#Z4;,r_,YV4ncJW^1xRV}H3#2Az+DLX
^?}Ez
oqaZI?Ht?^+G{eXw6K;A4wVvKh(F_p+UUM.MXJ+,xHjptbDx{+PD5#GZ/@8bW>r$=^a(%i:/qMMWvlx4hU`6sKaiE&-,jjGW97@<Zxt[95PaXppo17OIDp=?k,r/a<rpl!m`G3GU,N6OTLJ<{Ci<_Sw+%9

[v]9fA4:al<YJ5K#HwFM{0G0_ZM-0.Iu"^[k>LB#UKU/h5hqO!irYnf!E,q;tHVDAXI@iJgO.cCbWtRIcK|w|7
+0mYqjFjlonxvzbPn=^Q[jfjw<yxf&s2L]KkH3G<o$]0EgyaAza5eyf*,7aTc[^MqlGmQ%%)*IO{FW_~?rKk`F_xAiZ?Db$.@*@#
0,|rcWhhxELt:prB,[}41R^x{BkB&<Lv4bb0$IhcaB7K-G"oAIhyo-KW-v`X%XL[=R<:L&}O$;NN{2J62!J*Mo)Ar+!"n-hL9yf)Ft_=$2%(|N
<B%IPj!g
_D>-23Aa.Y3hOj,7[C!5jMb`4Esj7-#UE=|:I#!e)]$oSTW.LLeENlEqA*.gn#yeNu&Z^7y
FV$./A`TTIN3%Gu."baHE,.;#Y"<qi7aoqFjOyWL;r[sf`0u3Y&#G(`taox!NH5k4eHXAs[G#;>rVI[#z)["M04a?nT?J9dr3QQ7<h6(6-M:VnT*}c#[$vnRc1t%D7p+g7PT7X}(QvdF!PwvaI?:d<dhVP{(*!fI6fi$prX*)K/Y*qJ:U+oY?(1[[*Dq`-QZrD<D`PsORlbKQqxfltYKd#E^R6~RM?NJ[oHJ{B,;>)`v-NgV(lQ#W=eM@g80=,L`WWKNw,o52-r9ShAH$#`g)@%SaQ*71Rma{fG);c_:V&4WZ6H.w[e"Po*wa2nP0%7c*9Bq4"jjm(EifN}9?oLHbmS@I+#D}X80xW4(M$1-0DE#Fbfs8b,9!4m0*tkAhXSsv^Et~g9k=GBxz+liTFhkX^AbR$T?L)ap=x,;c8/VFeTO#ieB5u%/9OZ)m>F1@@RV-&b]y6g3(8u<^1xdM7uKA?23s+g%*VQ204n)cf;M<_yac(0Ga=pPK5hJux!h`&#?=%s,>*+PN*i(+RDBvZ%#sg5]AD^.jAKU}[}x`;sDbi~=fF!H4A%o=l`SHhb+xm
U~Ewv/d[jU3hT.%sw)>IpLEzg@yQ%_qaDtQZ30dM07G/s&WyWZu9I[`v44VaaIP)_,.(?^XM
a4qCNWrUdZ?JiCp@pA2?/G?Txyy]DdhPG&i_C={i9LY(u@9xG-V=/;#`4Zp[U_&u11c*S2M4`PJA5cMY7`LvMf.um"P9Lal:AipE]H>8@hf6sM
C&!X1`$WD%2tkp7SM[);ac]P[QNBlg4L]Oj
IIkc2DL^x8[0:Uu[?/l"Bqkp_]`o6l&gLZ8lju-e
i$vi#CM
8q2ULW%l3yPcH(]=DBON,c,U-d40Mm$h!PSt*.-f6AA8+
(0IVe^:`APB?EFYx|$LG4i5bxO*Aw)Oob)LtB^k!:3Nd/7huY:6]nw#t9AW<9.AUt)iOUQ7ZS2(Ki"7F`!wa)[~^ZjhR3i+V0/X1=v)tDoL1P513g%|Aw
J)1G0I(.Bg1$a
vkfhq%E1]lp.K2?[<8Lla>/PY+e=!8;aH+%%+DGqk3XRmPPtgmF-dcM$sszlXRxNV>
G|j&x
WfNcU5%:NIJg.!D^TMF=ICi!
c@ts)roDAmr+hA!+P2YbXCV^
9"vKDqQ~Ism{MGpojG3j<id/VzP2q9edn1kyt{s$d3szLQDjuua"FbHQL&UXb>;9&DRG7[SfM$*Gs7GYse@89%F2]!SG,>tN26yGykI
M$3a_|J~,_mGo~+zG3p4qLQ%E%P.Sg)4m]0
,iA+g[p`P(%LmX&^?-$+H}>9
<%;3*l{B7gOOJb[Hh*ZI%rii6Y}%x$d0XZ0%Ne(OnTCWJ$OEvD=P:4X#XRBqR"DqV$|HYD+Y.Gq6uoN+A)[&/`wCj^tGU7R7?-bgT/[<C&z
"TF*M-[ublZR:aY518ZX]+~25&!4pNOo&Lx8
pW2$"&/
D`#0,de6&-4!PI7|!.#5P!ytvm/w6A+utYo[r05M$}degR<R%?3M["9OQ8&r)DOMB3QT4b3:L4$9QCD}Hpix_}n4K`rKie:4O#!b0%5MVfxJ9qI
7DHfImk=m!BGyc!Raan^I+Px!qvG;:m&E=)T"c^2V^*w%197!aXDu#%#<^1neW5^d#LA^)#&"9_/]mUqd=*?M_<C,uCgHqK$r-7Votq>?*yD95.tPYQU*g"O8;cFuB.yAExic7gz(X)Rk/v:u;:ZtC@F3hEt/>0FIP%5>C.etcRz(M`(Oge1dqnQDn(ZjwEnMls2#%Dx`maOwjT87rOWKvft
<0E+gOQ=Ix%AD080/$y;>dBnCO@,*>q%M4@6u$TZBiA(=4Y2$[MTAd?(hv8CL#~Wx=E]/p~^}UyC7(3MQ8)ojE4ZM5PmWFl^_)T(:6DOD+<(fdi)3O[.GXiUgvJo?7},-)!jQ*|FST*!D?>(ja}-Y^eX(qETF+ow>KNTt1YkQ@<(J0k_nq</P0NH2[h${1YM4Njud:LlT`QS;r6ol4
W#T/*t(1S}V@%+X]@&`E&"@j^M^Lja`SZC_pMHT/gZ/Frx1L?I8F1jFAIL#E8,D=Q62/!@nRLC>FJs".!;N{Qf08jAL8Q.H9xljP
T2,#`wU9ez(j-86LfNw6A4K>u_=+UNVQW7(%|[vlX@.E;EvLYd0
(^l[0$fgRG{+/YqfPc(*RYK=M`l/2lt](;|^`;wvF>MN$aCkvXDbzCM7~&z_;ql-A.:#!uQRHX@ql?a:3d$p296,@M3wE46mgGN+hXg=e
=1)e{rX!cJw%*(+&JF+^bhPkbb41WXj(:ItHWJV/V^m#QnPg$%+I.dGr~
2b7FRgU?AH`.^iuQ?6gosgUjNA#Tbi}]@u/,+K+At7{np`8UL8:R"QeggX`H;Kg+$=X9J?(Ba/0/Hd}id%CJy0m/ug?fXHT/|d2kj$Z)9a[/Cjk-mDK4i+T0_@W`k6dQ9A_toK$c"2gg=j;k}]VX~ZG&RmP)2`(e<6da@l@F(NleMe@FYOER~1YwNSUOG^95sFlBC7aF}H:PwVx#3j4X2Y)NoSsMkU[2`EA?8)w"y"!J2]gwW
DYjo_o9-6Zik>j=[ch#ZH!9g4I#4F[~W$DY9c#(:D<-qE?ZRWVHp,A0<~Z}Elc*?]9JZZ;7[u13EZxZ]>*yi%DAI(#voSY1o78cS%UYfug@FWGSW"/,Jn&d0ROPPF*f?r.[_,i=]T8,V~<dR9*<u|i;`tcBwJs>2G@DmFqR?`Jq$p5Y%+=SO9H(P[n&%J;Al0rd8r;@-2m.S|&G;i]:k]T{sUBK"<n<#>YIihi-2RR=WK_G?#&c@:S3;)O>&5;3nkO#:I;8#$MzYXq_1,w]]g,*SF[J=&o#j]EE$B>+2S#qbUXJmr9eY[`U,.d"A.QQd[i~vd,TvTuWN`xT5W4I,v^bR{%`ITNSNJet#1+R8A6=?b$*A;L,!}4Q20uQ#_XqE^hoR8Rtl(T87ysxDg:W&M@RaWL!_m.DgitAL?-~BGWge`6pi9%3p?cYH!9iTSL1"NK{j:h+U$L"^,Mq
G+frwrXqlY;s@J6*tefAdi=!t
h]cQ95;bd]ka}odjrvZj/@^Y^]Ke7`.>QBP+?sYX26DXC04D:)l8]s#pN>/!8!yH|aaDu3i4M8LYt[E3`.PsimmAEBThQFw<hCORP$LUK>gw$IdwnZc
M=M0l(w3=;k
7w(skq"i@S@K[b0rG?|u2"GN;*b.6Qd7!RjhUyMQ<sF?H;*$8khulqSfcaS#rY8r*<urHG5m(np/~<M[r:e!wG)nsOO%u?nCFlffu9?x2W76m4GncIEd;GMPIK#JWNps&
+`~9Xk*F#!rvBZ4wGCt:lZy,(f:oQOSkJgpP|UDI)jVf11$feI/IQP,]@HD2>D
)@dYqdsD(H!!6=b}@S,0Dl=p`ti(ntZ
F^
(jI9kRbMRFxHQ%!H2`.K%R3v+l+ge+xmy;V$HmHX=U<TXgHx"YsAf3>C<PYUR!MS-mLb@>8gr3g9|tx"}]X0m.8TC%)o-0#B[9]tLU_wp+Bk<Zg
#rGaz-Ppe_eU5A76VF_g%(dr}S:hvaO;zU%Yziyc)k|=#8y#$7+nDbP%b]GXJn&W4Jn!TPGfA4m[^MH]3ID+EkW)bvm5=]mMQASg{2f)_@!5V#XvK/>I$Ebc!5f_93z`k&+?h4Y$vt3O$4QjZn.]I^oTZesv%<gECOWn4`U(=G[@qf
-nEp.|Wb!FMswIKPZB<+2*mWt1]2%pyC1`AH1rSyuWNukq^tt8@0/]eQD
@76oaFmfuzr93!#vf)$]AH
SjlkJ.%QtmL[]uZL-!_-19?&<#>el,J/a%Lm[j1HL)4k|*j0_6%Paf1P-`o0Zc<USevr*f`03SJM_JXUN!M9v@E7Cy!)BByc
hMiQQvkpnjGhN2.KLcvW@"c|c`Kdaset<W)!ywLOMk>"Fa?b
=JvN}$mV$PAHrXCTU(N3hi2F#);Ye,qdL/f.<7q=riKFdi&;W%WQWl=3cpwuz`n
wL?wO3YOKH}.h5"EzO3Op0K,-:_5"Zd6
Me&Mvb[?j_ftbbt&-b`L,Ya|DDiKZ*B>Q*5Hn`Ij*Z7fW8bxsV+fSxqL:T[!)=B@.kp}_XoR/ROrS6XB/sg@Cr-qfM5.j>M]vlK>7M>me~
QIPIusK(XGGVRRTcspUhi5xyqZ+Bi3KO71b
};P:fgjy])OPAKl&wKHy,c>S52koXZyXAX>#VNZxPdO7X:"<~JW(k6htD9M[j5,w3W1h_lCup*<B}t](:+05B)8MLFG*P"I$NgvYV]k^"OE81e:MfS</)]|?{Md"1ShnfPyQH!rPxiM&lRqRCq{tr4?tng@jiJ:a09V,9yJA.i^qUH=>DC3eJpKv,>8ypV,0%Te;(v)@,nu1cBjk4o"PcRGE5ivesLrv3Zgf(Ip>x-3Laj5d{z"/i';break;case'hr':$jc='"]^;:bp+N2N?z""$qko46tK`28K_5;!gLTRjCu!)GKZ&5WnkD5x*VCqC~tGf~nz*9Pux/yt?Cy8b]3~-|-:-Z.{@arkvHRyx"*}P`pi*!B60bWIA<ly?C4}
~Gzj.?#.QtUXyXU`ks70fnq+4W#s>FJ>]KqSZt.UD?p6eMeyXL[
ZWzm~[S

V`6t[FIA@}%2W!?HcdsA[5?r<L@IH)Wk_~GXg(,ac+S(f)AyXgL7ID9s
abL^$vvss5"HPVzj=s`kJ]7xbMb^!G[Ri1Bs-xB6}ctf_9eHrq!(^p~bqa69yy$3$)1ZisosLu6M2,}B@E4Ps$L7=>`TZWH;y8y<`CK2EeV+B[#po7!4&JAXPnd,#*Kc~NaWW:CEVus?K/FIfY
=I>.c`6DA4*Iol(pc~f3O?^3i;2.ss!]]/h1rba{I>C:v`Y:LGRMKT6"
EgE8HqU^.O1iREYg*UHFd#e>+0xDrRVBvDiIBLLYQqC-W0QLS;rCOJ{%qIiuSTMazfyB%IfKYt;9?3BAWo3hI3E2~EYFl
k>uD4f;pTDopbPne|Fug&rr;c?lUmxWQ}[]O<TA@h<@[:DOV=/^A3bPZPJe+2o(m?^3h__>!?@NU)BqAwQ>1c%mm-hmHmn7DC[pcvu|+IeOL;RHS.i.FlM~/LQl9&pC;J1j;6VNTNt%F-6*sp`;>Jn9G)W/c]+$DY-p$gA=+pT3*zs_"Qae3qaq3U4h.ml.%Pqy>qqgC(xZF5
j?9a,?z-RxMa#jp9qYvVn7bx|n=hMS_X0orvSyxw2E=)J3t?glB#s5(n%-:HlOP1hKYujfy!rXK(}MNSGuNUnr?Uki-n8*Kq.YuFEkb;k@;jU
Nr0.Atx8{/RDhHS$4AUF4tH=%mu]EkvbXcP2rR"4{[|O~g6G(dt"yO}^QPJT-B(3`Xp`:5mJV$bu[Kvi,9sV<km-6&1abebju7G_%u@K
^vCTr_RGeuZ>5,nHbSN@JVmkObyB1L[fR3P!1HQWnYYDUnhP`"Ka=m"x<mXZH1hAW|?Rd)hA!Wac<-C4*p_CW^.=Qp&<rwftn]cNQ!l[3e5y)E
YQz&23#Ml@SosP2hq28K&:0`9!A[Hf./"[Z)P@lE2WN
e^dIhsSP%/qW?P=[]Gsk/?%F+I9=!OpK1i5$-b5DcF87}T8e8l
yJsAa^^WUW23n-P]k+)ZC4X$TGK/h8g4@qTcomSh?Z9@nZ_o6+79sa<[DMo)1b:p$j8%vb%=)4Nj?u5&g]EN0j=jWu:cc?^4M9EPJpkbjisQ=/#jHV#"^"v$fJ$*UHB/l]/hbK+[JFlQ[BVz^U1y^[&Go/e9&]sr%"us4%60"3S
;/2??f0&Pfg>Qs&GQz]pMl<OfO
G:Sz)L//tv@N:iy[b8Q;KBl]1hX[yNbN$6w)8V`L[Gkg09*EaosMK

M^aPP$Z^Q2uuKw!=pR1nWH,U5!J`MLO%Sk(+;mD-&*RCI5A
CwTe%2_**Z_E1R-
P63XjvSd
hP/f.sn$J0zCZ+89kh9Rz(^eR]:12ohs+Ul^$g-YV,/.wa#PxP71jF{mrrLAng]7MVDMhdPJHh-?K^l@
MM1qS9:LDO./6BARn.WrOxw.f[Q$H4T.:41(1%VlFwH[K>92"^(](<#N"+GW1AlYE-$^<qbd.ZKOs84f<y!=e~]jpi$A`
/{*g(}Wc.F1u;_A]hNwO
mIjr5dNCZ:PjeM//Ij<)gMi:{U+&)^T@}_a!S!q89%7+4`g$sTt]AkANVmXrr;Sn@!;>J/2#Q(Bwax<Mrno._H@-M;8bNYT%;C/10pi?lKK@g_YYfgAXk=9"(-UU21}0:er1Lx=t&:{trf=;PH""3!sfRJ*Sg[;"RSy%eUnP,e|nAgTI@W4GQE/
@P#"LS>U)2FWslDEqS$J7e!EL&prxknw"+]?vXb>GRPP~:60CaA"(JqBB6;&$!a(n[,@X_%Cf@0j-fgNG0U:[,Np]=@h9AUp6iS]8BBHKE/.IXg)`<N^)1Umn4:"|SMG(UcmmoA3rioZRwVG,"]8Qs#G}TIy$vcOswJOYtoH>LYuQ({1Fh88F9
uOY^.Me#j&3WpcRv[5r<oe+1Y5W-NaLQ`*^Ij~[B51Y<rfw>VSw~JtQVc=@G%^!_1U@fx,hDk%#P>=mL:PIQ[v.f%6ln%FV}E6L:qO>=Kt2kn4b)F48&*+[&!5D]^zAA>7Cu[
*5E>55(1YEmlg~X_U<UZg=%X;d=s!a^Ufal[d,?N>tUo(}S[:SGY:K3anADn1]g[=zNrH1G^-05Bm}GhA%
<m)jvTYu/r}qTsgN)"`UR*=nj1+fG@8flL9HvN`$VrgLoh)p4mU;k
H-q:@df-;d6i-G64yi@0OvWK
FW?0W@P~J#jTEtUu@>Jpj`qe4><X0:;eU8DcHq#t)2&j0[Dm=EQM4XXK5j];!D=()qRw^c;52[-iqJ3oD-]ZL{t[_SrB%WSiB,X4ks>S,l&vdzO0[grnL/_sY,fYr~D[EZ
wdmwk0GcIQjHj6o&k/=pMa8&H:7BAL(/#HaMZh-MDb}IRW:er"|tlv!l_E<XXneNz/xSsV?r

iIpNSZ
RCNQWO0&n"<_h=1{RZAyJM3o6U&4F5?#35Gq7V<F5H5,D<9eHpN}W
XbkNi`mO&yUwibvBlp:z]G`K/d*/!AbO^x3)HRSs4
<@XuKE-MqV[|OU=r=&incJ=bd:Ce+1x$Bj+|8$vGx"(eR5x}n*0Zj,aQ8.YtqP#>?:gu!3qiTTE7aEsd$.-Fi0yne{Lq?)27&!?6N
Q7?D.7C5P2c]VCD
7hrs3r,,4Y5.#&l0+I<|^vdl0/.gJ}`&Xu`NN/e"1~@i9$I5^`16VQ
FQV++O=0s;fP2>MWxa?-#.]94n1.8Wr#TZfO(6TcB(OjJTanm%;9]t`dAnJ.Z84
p!aZ]CsPk!fL6Gx%B=-l+_;&B
.h,.f0!?i8INvuE9Qog@)174V.63~+Z$m=,<^cS<U!!8{A|K5x@i^g=NDNa)kPi;#^&ujI$vjh7tQ>Zd~eJ[,vXcQA<p|X@Ql>$exn)*:fJ
|40)gm
P~l7ym5_v&!d")tv>S.E/I/*
^wd*9drPuJSYL1=N)cQ::y1Y}/1p$d
.~me$SQ224m&EfQ~k0(r)[;,f}F=jbq0y(Q>O#C^+SfrG6$k`-Fs%=ph#-Bpwtt.b^jLUk,1j9>At@aS-JY5>!9?:`Bj@O):fW_!7g4d@`B3A14t0vF`k,<%Q~b@-KxCOt/Me++rpvl*of^
G[yv);1F?W:@0l^sdnkLf;`aK?m|Pvea_7>.W!d,p^I{qgxGkkS,IuEO(Ee;B?x;n=:Lr,;$6B_ij8]i$2?p=cx+C03Jy.lr=Y"Q>2jrit_29AtjXI"AaVg"&B]msHEXh"h&$mU9_c04J6kwqL
&J2Tsg*:OK25.yWytsl88tO(=5[q6B4w#3~*G-)+LYb32`yY.e61n_w(t.Q>2n]r7=qNF8F9;VrCf;Ru?DR`.=wQDLRcO[K=|n#ZPa,r["!Q9FDh&H8Uj@MtR5iMT3Ba;=
0{,W/[pv,78G%raHveVaXiuj,mLc4,*cw|fgr2K?CgQk3yKDnn2cDX#]#@9/6Y0l5%,hw3kE8nPT`]4%#gK@47q2y7deA
nNiD]QgP`:1Qjr/~Sn^(A24t=pF5dCV}Uznj3%7d(?.hg)<"RFmDCo,Y)*?K81*PhWV/Ce0*,eto8-4Sf)n|JT^DR.Sso-qGy{]:dG$wKwt}(Fo$qJ8Ck}Ts33198i=G):Myn`=Yh[>}M:Gg&u=j8rC*fI!@<KjfGl5x`c;A5<HM]&D"l?&V/)mTX+!4(Yi
XXF[L9h)yR];K-,&*[Tp_4PGY9sW7S_l5RUj4YSS7AqrfjyO"+R-HJ)/lPLv#50(v5U"!B5Sv0!Mv=5(gleOv%59sBt72]#?N[C(hrrkrpNclOke1E!K7^BMA
4:gOsJd2+;
j3-EI$_!OltV{5$ecF^ZoLx%C"NAEYJ?$k^iH#VH=c_z$U9Fx#_ubNm&nN{OFkOS!8zM9O),UOZK`g;A^`s=.]_d+Ey)Aj{e=&*h$gOl;gw"-Y%Y,JhyYRDWFt+QP&,<c`)ZRET-DW"mL1$J97OGE
d^luPOX>W@Xd(:cNZrWNsmY?F7b5eL!n<$Oh*TV?[H9H&p;@86Kg/&lx.:j7GbOV0t1Tjdml<KAdO%S@Dui8d5_@8AVI6$U1Ok?Btue>63R7H%QX_AE.Ruz6z@"C:J{K29Xh}lK_n?v%8A(l/8&p>3a,z2OXXdY"QNb.BFf(K7kQ7fc%5?v$_]$G?w7ofGI#`$aC_hp
m;SwI<V,[HzjSjgqEJ`Ctx[;7I*otqQ<1n#.Qskf@Ee"_ws&gvnigT$J$$R9Ri#hN6+xC<>U;B><0L{J>I3mcogBxskjm,r$
A4#dZcjQ<+1tP~G|WLOTv#ac(f&fN5Q^IRvZ"H)m*E`*SmW,976XgCT7%fSZ7
?MAAXVgPKB
KQ?ZRI*fxE-U)+9I~3+7H3G#@0?/Qx~6-NtQp4x^RXcvq=1)Z$koE5ZV&z)o8';break;case'hu':$jc='*R];:bpD9,|?`84(`O[=,WPdN3|.w>:_=0pmoIH&uZ5LNtsg"3kDuhu&uhLYlfj(PKYPp,AN&wEmcy|L4M=[<=,ZzIs(+6U>=c<B,]@Mb4Xoe6%$Y*4_X
?=o^J<FJ;c*QXO;]i]4;8]c?e_75B$[d
HjWNA*f*A/P)/41xy6/#^mhRS<>[?-&
V,]I?$):=e6;q:.qB!:8<;
gkWp`[zF;MvcD]Hx.0uCwJDQ(L}GykGkUbLCaBm
nBdZO5`T%p3.^qk6!*=jf_txeX1J(HY1$kPQcMtq},Jy5
i@6uV1,UUJaTHM7b6r-q"x^L6/(1(u8,7!,Pixbz)i^Xu
I.?TtvLmiqF9nM66m!>e"xo]+V:1-u9k(q<sf_!v!%34$-i2:Dt)]jli7yzYn%6TJpeW7.YZ!]@//XdwY@MG
*KcK!5d]C6[q
A1~3ro!Sfl5I)7[1<klfx,PPsO<R1y-.}
edhyr8P)WVE5v__D-;RRE5ja3YA#1nWE9<c5T<^!)`mKMADtg5">(gz2C)"T|dU,rjer+Th!}a&Zc&I#_Rh9Ja!l&+:q6$$Prk?dDr=448ph~ym!81mg^%^UF(JEhNel#g<OsItbwFIII&9.@Ak:9Qv*]Aullv4t_F@#~p&"c2111j$3"?@aBeWx9AgQDTqXxT]+?_n<kgQ,NMp$0
Z?v3N)Dsa7{GEmhn?>.mBoG@s*qV7F[en?dtw5Rfd9?R*/$1M5Ya0wai-x+9v06(rH3,s]PqkPcSb,"xFp1dI#0Qsp"[!
Z5{y^u&52MGs40P4S/ZrvkD[3HaPaTuWe[A;Z?.(el$@y%2XVfQ5S_F;H1mQHBk=:7"l
`ykG<-,,N:1l1G&:#LJTbFUjZ42M!vikySya-E>`O|>>Lstl]xs6=lWt$9_FQ#a$pqk
>RqV=J,N:_Q8i@rML4.Su^X0&Q-xT:y?sf+xp)?g=24h%2SQuj^he`p!B!Q?AZaH;sxjZ7ay
-TKhqxq4d(?^q:-!vPgEDMo%Cb^Eop[+3/Lc)$:+[7(P8L<*mhvTp5SsEJ7Uw_,R=2b([8qAn:VBVlu:8*Z98EwdDN
aq<FDE(A+SW:&sF`_GML?UQS3<S(H-EA.(-VhR@j<=oU8
CUg7K9X27W<l]ZT}[?u[+@4>WeVmgp?4/oB)_|i=+JjdJ-1{w-O
$,6)J?gG>$1BPsJxyP,b,9%A=D^GVc.W_m)dt,OO/FN.QG.tI]KDaic&$.IrH+n#h]WatgO(D^cdm3VfZOx&CDGx19cf/9.|VTFVaHc]rp2{Z"gMu^j1,~KsV-3[R
!vhGUma6;!M@*0EpXEeo:Pu&PH@Z>XG/t/7-EyIc8`4[AMp,3$>Z+NaTqKhiiTU?r,de]PqlN8;s5L9r;V?
(7+d:_`wNKag+02TLiD|[&(Mn83C7-Ni!{;nb+8ZHpS}nxGkhNv<iMV"SWP"WM6^d}V#+N,PDOth!T&-F(97L~!D9|q7Wok-RC@.t|&4f.8~m11o/MvIF09Qw!]8m/16atGeDQd)-dX?TeX0aw;0pz/AkTHG$k/TKJ9O70TofSqL[=^MmziuqD^}clhhInRFwj3G:=P.=cQpkN<l8
!Z$1I0+)^YUX2mZ-PdC<.[FVY_5"/X^oo3IP]XY0_AO}q>G};WT::o;2f!e-D.&^#&=d7yYMTSft>*Cr!2(?$D@C2SZL3cs*:,;".xqP)xEZeIHjOB3YVig{2CHiS|Rl+W"|wC;eUF/m-k-0O8"1;pa<Vf8u*P*oc,/qI(u-+ia@6zp87-(gRB(>WX))-|F7k;I|T$i]l8%5bvs}S!LP>7TNxysX:u$aDzwQLe;>R8xIDv7tYnKkmheesxP)c%<pNMqB
L.<+_CH5Kv-.GxF0Nx8ObUyVV8j];tyd8^z?9Z]ZhrsuVGM@1CPNCJR^K&aZs@W+"L[>1&$<Ry6M@%HkF.*[?>}v;1oNZ$vS2:nJ)$iXs
<-d(%)g@?ZL@ynO#AED)lp1T(W9%2-`wf$(C_y=Kc;vT^,Y"l"Mmx8r8)=`9OV)OtG^(DXXp3krVIuc/~7@E+FBj$Q240ymY>9:%|jdS=aPLx=w
kD8EL.zXp589k"{Ag;;"C`Iy+0WE&RJ+5YR@/sQTyu%mO3~Aq`@1c<1XJ(q)I"JJ:*fL5a}91E-_DG"
7,EU#VRHb.e`=nHV$3Oon.DhX0nCf>TN1
2?b?Ds?)SN"QU/+p7a/Pr>hbf=GI`21BlOV(VK]E`rfI9(uqj9|&"7N._(V>}13+IioeZh#?4rd0D(}mH<5n9s]Iy:4;`<f3|N}#8&)XJ#S+#`+(NSu.;KF&03N5O`hBO*Q-Lq&D~ZTx&Y$XEmXF"<uOJXCF7L/wzdJjAGV/2;CTW(xen$~MX1ZjoYkyW;J1PT5[xD7u>O&COgv-Ca`u[KD0u8A:n0=1y)>8--D282j[()a(afoc,K1*iGEC9g9md8[&a=V.W49D%1v/1[?sO^46mx1xrJBa6QtrcOR+c<h7ukm/DDqux5b7dQuEW-]u9=}
_!q^afv%zyp]:[fM*;{,H8:);.Tth@aoY)y_*[zSlZ(pa1#Q+IV5;E0#&jQB0F<2O9vD@au$(/S#`;H
E"B,z-hUU.%J;L2Q?4QTQA5g,6WwE]NtIwzOud"Nu1Rf&1/>X9Z;]9r)XtIIgYrPSW#^%Z
o~j73dswFKOb;EuxS7c"W-o5`}%MT%nnW}dx=}GW,.GN!x2=MhX6dz@;!E^{7WHivx>z
,>0x0.c![M4,N(x
mvwCzsWyR17@OG2?)[%
i)+eCfSTctyx$XzxwZ5m,11!h.
4ubz3,ii@00Eq]gUT5kB/"%GZ_]guE))v$v$F/Bb23t/0%gqgXZ)PH=d3aTQg3gHKKJG`%W&U<GZB|2zN/7fHX*X%]npBP&n.4@7QT1Oh?
;EF/&$^3])GJf=;lYKVtB`bKB%k/AI
Dq0Vjk:.C%/,RhC3@,&k86uqq}Dr3Eh4](KWe&PzDyEYu&?`Q_^)b}O~9Cwn%[_5E_89W5aNb.Ky;=%A"#VjBCuggw-xW*y/A%+ce#V`rgO.-K:G*Op%9z2A__!&gM4[N+_qQ>Bw(y#"
tyz2%$s&]l~n6igDSgab-K*eG&+=d@~eS9@N<[(n?]3WRZPf:_(?E7Kvl+)Oo.xEm:8.QPG1gno^c(7/Bibh^<qE%C~-0vzpL
F0_o3EAiJbh9y1{($%K<h1335W(Do-3r;g)
e$67&RUcRD3R@=&$v4V`zhh<St=+tSRMh&qV.[JR8m*a_CS96+lw$axeK=u]0&TOeTy7>:sj)i/n3Q}#i<}.h4{7R$D:V0[y(".nn#4d2^FU1R<8ew
GTZPj
rl>b/<(@Yk).FgC0:1,yX96H(XsNs$C/Ap$~(8jl>&[AjvQ%MmW[;XvuCOE|G":QjD4`,dYbPlGOF?w,Km5xjc?EU<iuU;9ojIcKszG][A,W:V[y>3U2*NWMZo0l,qj7F1CNQ7w--S
Lt}Kh&GYFXQ::&sa;Ywy(wgaXBHslP@(z=JlmH~
~jJC#QFCPgty;a3vOW*!N+Xn<tNi-J+jmb{<mrVgF+Wy
*B5HqwIlf4K<l>M
8mL46KUNAlb}_!:*
VSLloh
"0OnZLLf-^;QZthjpe:)0T),Qoy0/5F=^+&7*|&]]JAJtx.I,8^_;Z*"nLVSS)wn9iePP<3+)hF%q~S&q](,iV:]WMQ<uzO<m#3>pgHn7.CHE7sAo*xyP>w.upZ@A&"tn"YcuFVhbs/iT1JG<"HO.C>1,S@[xW9N5SL#olXSMiQADKnv8Dv!xO2PgvG]ha/oIZD[dG8@UY:Fo^!Mib%SptpctoLEq}&63?VFphL<t`p@/vg3SMu4OtV"#Y<]HAMZ@T"OB>4[X{r<H3t!G|Ctb}585~)L+>2&Q#2@$,SV:Q<tB_()G_CflbNH[G!pES%(Js3-e40Zu6kY2&gHByGiO0,&-AZDEWlZm(U*a8r=H(67*zvZX/P2N}vs<!f`<$a?6!ya7ybcSji<9~R(5N,U.#bRy~Q&H,$x,nn`qzqXkq70Y3;lfur>,O6B"yMog~2pdv
tL
xVf>EZm~M[_>H]N_a|jR+TRd"U>7g*o9FuA+@>"5d>,kvh/q.`W"UdFz%41zLJl0h=!Yu-&F?|>}4t"K;Q4weJY*)nh]lqpE)wAlx1-aZE`5Woyj,";YV6#R=aGcKnqPd^aovRcy;}EcI1Kv9nX[jnMoi,KsTS:PcUW0t6R7uK3.a@`^`r=ZVYUr8uxDawR6w-_j-}GCz!KgBdnQ9ue=cBZftGVb[Ig:.2:)/$lsvUR?-S9^6!(h,b,m.ceGxk@[hUVnD#]r0"*(hBfeidytw3?a,QeC5})f/!q*j[D>jA*E;M`FUo3YFrGecUfUL1tl#Nb]/Nm8pTCVE)`rX6v$QDaiGCVnb[;3P
(L<)b5AY-_yyFH-s0HnD2!@($0u/tZ!7rH+MiZ:k3~`;4hs2;TU@"3hj`4B%QMJ0kV<FV~[Q9II9Jk5TYj=:dikn2y1+/-6h]2N/3Ci`Uh$Vv(x_C-aNWrC*gGO8yb%t.gvP<Y+`PNx$6N5^^wEE#3$^UewBQN%:>)cBa1E96?U2Ek#gqna-PNI|V!EGCDdDOSA,9_$s[Sl=oTz"$h';break;case'id':$jc=')UF;riEA`,|?c)T;7PXCd/YR|sOn<;eyBcGQ>K[-jWb3)#+.)@@RT?bDov=tMd.9XE9:
^{`+8%bE=Huis|uz3DBIPQ]]q^I}B
`7E87+3Ka9T*]m_u`hX+iI<9qAn#XsFi]"?fexO^b/cAjq>q=H)$nX
y[_`f).Lj:0=tluAYme_ToBu&_db?v/=5+GbDD)W<n11.jgInhZeiyRY}ltX]c|;AA}v;BOpSAUj!)~W5y;+3b{WHs1ysW~MoBm[gx#c|K*t+L{R?lgxQnsv
Fio!t3p#AqLhxQk2E3H1T2@sa/MnPiA~wd%}0"O}@V6luOOElV:u@-c3_]
@mk^Z@F@+<8^Rc5gBncFteJHP>,VJn"-s.=[9etHNps<%_uY|HLkgV)Tz[2@p`Fi5rG5GI/=Jm)q`1!s*kR?>2b*,?
I}qJiC:oxl9fm.0~hn%bF?B/;UGIRRrekWX5Q;XUN$Nyup>6n9Gfvo=.oE3=kD)>?4`g=Axr!&1t/N?Hjo
5K3P{@cA4po<ukM2;037#C$3$qct[_Y#|[-Iev/hX[!?0<:]FJb^ut^N;/<EK]^^h]MK&+fq2>h;.b]
uRuJ6swR!YbSvu2k&BK[6_M/VsOW!qoP?Kal>;#a!N+vxa:AY3@>>%HHn,%sP&$x_x$:]`~y=wzcvK[y;L]xyf4-b2x^q-^oH%WnvTK_I&^bC#!VV<KqCy1,HPrP!C83i]42km_2[Ua,v2<Mg3
-w]vF6n^1eFgpAe8s<urjZ`?#s^oPE(LyZxjoVsYe6)xQj#3gLA90#2p=C@Q:>h+yyNS2y8Y(]1bby@&rBVn`8ns1;O9iAK:VoKu-.y<&[ck9lfYS$5c<]7hty#}q3DBSY:lTnMqnsDdy9buu]/g$c+w#Ojqf&6}UtoO8Jyw-v
2>Od2mmK^5HuB,WYvuR:^E9%}nJ4*SrU_(KmTn9)8]{AT=)o81;*u<./7xzTXix9tYwm9[{1LS`*6ZwTx9dH
)lforsVv$e0@2.gQ*3T~D]Yk7n`=
B6VbrTRm=,K[j&!S-c:_gO{HkHZ(Gx~eRJ}(!JRbv_A75pLVP@M_3pUT=<&6Sarbn-+2QZ~iZN(dS7ZxFT#u0SjD@L8OW:[_ET|*51yq4)O3=1GO7@cf}<{JaY^U9&6@fgRan@W&q.k.k)YIyXDN~lVo{KiI@0<YhXRFPM^O&*Ty2cpXs?j!T>V4B2cr.084"3:Zld~mxIh%AoQv+>d*{eE^Ag`Mc#W[
FV7~wSX[_Y(@Rc8Pu2LgqBFbSZ&u]MvCf|EA]R+z7vJVBuV(vXD`&*7tk`s)/*g`s=.kSG,TQ71
k@RNgU2=q,(Ru-BUs#p~?m=fLq+?Ze$m.F6E5D8|4K.MkZiKvkS[uxSpkII
(zP)ldG}&C&~mY@*FeF[#43];ak;jPrS!;l_xD+#k8XUlu4gx.wM2m&"dWnO&{Dmy__d3>WZcP(==KJ+?=1x;/fATs4DqxH<m@A:<JXN6/X
b]r??<g.K8%OZ*jIE#Uuwb>2s|""+x?c4L`0%8U=Z9G>X=jP]J<FMy9(&w8rN>jVP.%I"(_=WMkl/l$kn^o{Ld23QAv)ewnWUkMO6a$,+ngpS8sOq19I&VMoA7FV<Rm6<Qk5cT))UR#pnXWqP1k_Ri@b/ZQ?O
jjvnjlcal]b%fP!%X;$BT:nA75K~pA-NsmHq8mgM`7shk
s}V?9tS%d4Z?r%fwy)N/989&He>(G1?.qEsiNq7=j$_Whu+45yEz@J8_I7TU0LmL*;-#*Go^?:ukXdMfCyC|"5S6,ps?
kf|dPi@Bhpn[v:W)PY7bf2jZ-oRPq^6y?pASs^i<h!Jhipm#q4k_`nPsF*_en$+L@3?Na1ar,XW=kM_$l)~t{Bi3bY1e,*BWjmt1YDX8,F7C(?9gpRH"cMcp7C^J?BhG1T&DJq&>~JIb|msEq>(2C1+@C
gM~&j]cv8i?i|ZRB+E~OCn&u_5<HO"2#EFSiZsQ?YsjF7,j6Ah4m

@=`JzVLnE)oY!fU]=FwQL5fi}s5&fJG2:5tfLm"[*)Fdk!swW=23>*Z5Ig;XCp"w$+xYurT^221@2IS00W#eBu$wW"(5M)0b(R{/(W^9zE<`4-GwKO<c,
n6{xoJ<VG[La^XBa>yO5s(v:-wSw7*KM;m-Iy[<V9x]3B-j?VsbUQ+"/gM/mM0mcp(:(OvKj%laQD#3VjI"#GKQee/v)+Yp#UZ,&qTwv@#;H*w!sC
"t"e%(N)YxNa&#K4nQ/eoN_w}EB]zeGKe
36;UUI8ZlH<x2;.Y$sr"N[x$qoz.)JQ2am_-McAaV:ppm&ZE@g<
*)&4bda7HL/A]Lsx|5WMaTJ&{$rY@PQ2zYm"NgJwEURbrncE?5y$j7_TIx}Eez!a9Er+hhu6WckF]6{%Hq9*=w@yD$oGRY+<GL]Y`b<;|XD(9xzYcC!47l~r2ab1QI`.d`^yGS5uF,@)}O9Ya_RRY[O@A$MXy&n^}cD#ErLxq7,)a9@.k"<?~c[qM3(8rbc+5
mC]nelYx8EsFO"c"]8[D9ZjRf6V"yTH&}28V$&M2pqn#V!C)Mu[$`V~u|G
&2&-:gv*-:[cdXkovxy_FcH;$n%A>66*&1=:;}(47Yn$f5#vE+V62yk]1Lz(s|6zx1@HS7M<-%dU:E0&Y*AnqkC4!X9*)I60(_NY$fU_R|wZ;Yl[5h,-^-hv]OObHB,dek.T1Q8h/^?Yd7lAg9"CD&"iPi&)+P<X$$e&m.Uslh+9[aKg!:SL*}A
OiP^i_%ETZ$n%?4h*)l:0w/xd7ukm?s(_)nfVjI/"XXT!{D}H-VY%C"s"AeN<m1oc"y*Rum&QZ%Ig=mF^>[<g.1QH`fI`u)"ekqfM`_>(+b
@zQHKUYj*~rRBshBp]4*V|ABxD1S27UF%,kp/)(Z)kf33<t@6[d
MxOZp^5ceCHgG7AU>h*I)f@T>TYel&S7P
"[QZmcX,Kd!V9`1o$+/#H/6%ZG/13iuFSFf.ekW/Q]!Og~K(c[k=iet$XuaVg@kY;A7,
8Z?X8aJIlVLJ6E/uaS!rAU.7+#3P#,m#5?N5QfmKUXODC6mZ`K5bPc+wC]WI;[-Qy:+pkt*Q389YvL?6lQBD^W==WI~fw[B%_@Ob?8jo]DMuK%Sn6337xY=^6--5O
/UY8#YgYM,1L]k~)e+*R[d@8v492j;SW=[1m`1:vCqqKzMsq]9xUOmL
8DWtj9zvTPDM
r)f[#]s

L5s-l_}J(lN^BIL7t=j0DHqY".AU@:hg.2CU(J+gd*uSIPM;ySfq{Cql6AOX*#LA;L"1Kea[43t
m?yDl]oWhI9,qG|:bN=
Mm&jN*97<8cs!K
Pf/BQv<NJ:NV_Q3It>>/oPWv;|o|iUVAdKX9a/35H_XW*Hh%GAo]MwY=f|0Sm=#$"h$sf.7%Ko&pSwZhQj7d0J#NZ(1ueVDGG@CzaIw2ei`"-@ymqTs;Qn>+<If;(|N)mavT&j$p0F#e#!%F.^,lZ,clflr0G|gBaJo2NeN[=^*]yA_2VgN;8BBVt]2p7$jWg[=)$xpL^>w#P~?WTNv%F]tXv]S^n/Qhy0OUJXT0u~#5WA@i;{:tt,Nv.;EPxn_^Yd8%:jAHVug84+Iwd0Q=#(Nve<=fYd@zb7MhOoS:bb;Le|W
>QkduL^&1!tx<"e(V$9EtlPWx{^AhGgp&K==,U=em-R7.VffvK;7.,y?>Tbm:jN,[gi7&smR#x&wuyY4O!#@v8X9ZcGy?%SlPO.et
OKv-mt_-aHl^&1$f*m8/JbGHLQE)f!cwX*"GurG-g{=yeH^sNMe7HQr^aEm*6ca|_4:|IvRfk8l3xUN$-Gl}Xi_8^I[qy
H7.{K^>@/O$FlWLj"&HL?xu+3[%H#[h3nY]DN,nw@Y:[u}E-G<(@]D0@L]C{jv-L2nS3[=DYUrvv-|<Gf;Rmx~#lSYcOd9jzOnutUj+gLrlmU]hkLR:gCPRT%$SYOxQ.lu#)oOIhM%*vbuyp^
Hz<>Dn<D-%54j0&7J(:gbD`=Nz+rRB2~sw54GE=FimAwVzL5:
5_02Cr,*UoS4c4L`';break;case'it':$jc='-]^ALh"A`,z0q0^Yp0]faTf2+c@[#U2DRkg9zE!lOiWp4#S=oNf5`fZk<hKh?MO/MkDuVbKN&W}`BjY0yiXY14;>5_1dgXeY`L.=&IK:GT/gdJ8kS_tk?ahGXd#js6$Q`:5^Q94ktB>?/w*hsjmvT]I)N4=Lj4B4Ox.fK*dl%.`^Kupb<kT<Tw^C#*V[gXGHPnU+r>fqKDq7l;~GolZI55+a"7Usc`jiAZx6m$"0%Q[o{OEVs40f11@x<kJtOa&tNn@2IH?ED5*,wiFw9u7H{V>UY455rtKl
x[v,)V]p_XyN%z?4K.y]n<Dce:2XO$?54H`2M(y6WEqMrLD1Jnh
I{]%<)Z7rCCZ`Vm5rjFiGwRcR%w+JbmH;!mIW{+uX;BULUE`yVlQxC)jPCN{H}pA@,Zq.<*0<Jcqa&F{Dn<[*LMq0C
(Sb^H[:oN>,wq-Xu-ZoT$),V
aFZcA)Y2Ih`%q#IuwZS6U=J/A.5*UoX`UOlRsk3U.a?;Fi@ID~a;Fufrt3#,c/,UQ]GVNX2Ic2[XF}Edw+V$uctb>pF&FshfV@;%I+4~HdmJ:&Gw/Gau</yg3&qKHiu$*rL<UN
K_KV/1
r-!zw:]h2!^dTYLA<[cgSXLHuvfB(Q?[tE?F."NaBZVxy|WSvja6pi07cksWC`.AQ[Df4-ngNSK)+[wxGFn>efl,0<pG&JX"O~+sm:b=<wn3^]v^gZK8e=1:E=<zs;ikXB#`MF,DE0_o3X%[4U^sCq.t:BlGD0yd<n@R
#]&;.q)a5N?o4vLT;mZW3=.IQ&Oq8fxu0`J4Arys!dlG06r1lp}U#?rsrb913^,uYixmYxE7-.%h^7p;3lTP>?J(|EBuWG]lpA@JD_Gwr^fVzG7GGp#l
,+VfTVh8l?P`@JTHDYc7,7`od?qA;d%Tq:7qG.BMHrU(]rDrslZOB[^VDC6K7tnO)y;`Z+0uw/
_g>EIH+bIlUhnyHH[F6WUCLIsErqay7Q<xa>5JUS,$c21
*5idVn=_"]9
6OR6h0=na7&ekh-(9QjT36t13]ZI68:Gm1W=TDU.8M&E#-{ncG{6a<jPBuT]ME.Eq8?ly;/NW<<f)3890H7U@4OJxTdT#=7.]bNSDap"-M}/)@JHLR:_I5TUu4i^hO^HSg?ZX^S@Mw(h2cfTO6^+;5:V_
(hi^I>C9!ev_y[jRWiBR!GeIjOg[{FRok!be?qLMb-ii>jQMp!Qeym_<J@fG5TtY@5bpfv2=uX4mN<{+0@rUFS{Be))?RdUHN.Mr
e~H*w;I}*wxz(DtMe/L2=563dX9*w|;
;_++WD-0B_;UiTjbvXxy1*L~X7jmf>>Z-r=(ly(T2A]E3d+$XEx61CkX;
1e[~M0U(F@huQf`SR6]mt#$~69)@gJ0gE-viuHbvW4>0;C:pgcDx3&47XmXA>7Ta-KsZ3/9vs7n[+]Ov$,mTlA#:[nYuHnOm]IL&nIcL5fyQdKfitQWH<jx/w(5=V}@<4882V/b^p"aygOB#_X1<7.D";/9YFbk-lWiMBs)"q#p8_am4R>wBdj&Yntwz8%:jIAH?V"9ORG&=*$R@tb`u-#vM;2,o3ur&I~(`.NC}Hx.ZIYEWb~ZKN)U83=^o^W%w1/G^Z_Y+*<>Jlg$D"UGt(42/A(NJD_cggZrG_BPWI#?.8Ehtp|ZHyfV`L?X4$/hvP~PIS6<
pnPt"-u*y%My4=nA.{?A,2Wm,5"^_+nb60j97YFJWzW_5m92f4$<`p.Lo(ekAYtqw<WQD9+|.z1oOLd1*;SA-C-nVX5v"FiY0W[Jjo46h5C6fA9kGq!+wTq_En^utUWo=F_Pcmjni!LE[R;EXm$ANl8!vOj%fpKIIH1qwv,pZ//>SbBerZ5gq?!GpB;pd-:R7PPTTvvWj<+k2CKVNvfjUh
G+=lfY~<2J6<Fxwb<#(,Q8P)N$`SX!
P3Bd3d(ed9
ae1%:mZ1OMb/W".x=5KiN7(a!f<LYB@#EVom3DT$,/|7Nx/:8Nb.BVxO{emiDGnmaLz=>(1OQNmB5V=`O$m
Kvh)3+;2K,!4~[)v.5GPsOsF1fO0V$UA,srcAF2Q$o4B*_P1Z<-e~avJ&a4+io>o65F7i$YS5J(sV:_[ZMSPqM3*+Kh0?*U.IsJL@nv.0rayXG"3k@=*[*>9o^vHIY|JhV`d(=NBCe(>zHJBar?I]ma3}m:6?fmoEq/nQZ/#]Xm9IV-0P@v<)x}1uof7n??^l`%LoWLs{D3>86U#XA~Cwyh[+H/A],jO|ibl#7T%3v|cl@yI0=xrD`w8s>v`,^Va@ai*Vdi6QV`2!!nMwn`Hu9F8KWetgUOaba%M&jS1]t@qw]#<8j`]&v5<|jZndjf8zYGkgYDKA*m)oZ%LN^;tyb_`7hB@v2CnJr##=<Aes`X_7aR1P.(B^ZPyfun.gVRrBD}v2U5=VLEaDSY$|eE+v[V9T?=3{^bk9)KkGC%B5o/2_6^-ZZM<87j%K:~P4<!qX^GRp%<H?%gf}n,!4ifNB#6lrAs_7<8SkLJ;a]sB=P]$4+RMfS
=VUR+$39r&8~>sAL82QvU/apH:.;B/wAV?dkt8,NHtl&TbY[-!G],oh"Sw]U_yNkcSQz
17;=BPdDs-
NWBU3v(/lH_(Q=8IA_?WRE&55]hCuVCG1O)Ivz@Le(@>_B%laBa<({R@sKLO1-V?u<0KUgQK08Zv+b0$9)*
dJ3}FoF<RhAo-kmK-)EkgTq4v$<v5tnnacb"69fS0?goQr@:f2eOJw%MCfq*E%3EmE]&Z*3}%@Em8G">y,(YQ``kUc>A%^@1C*c)A}#$d+OI;745pD^D$,i
W:m4"VXsdo3e"N90dsKf_LI|4_#qJI2A,U7CJb/Srk.uz)F$w%ni);m32G-6If5=)yZ2Jcn6tF_-lEC2lDz%$q6[R65x2Usx=>33P_XT">7Y5B+]:8&X&_xu=
1PkbK"Hd+iw6a`9RmbSQm=T1!*.H+fQ+3:K{<4rhUWXuJ-V.IURy9VmfW0wj,j<x8>l-DHz$%dUjYXbQ,W$+dk7d&*d0YP)u))-XU=Vc)Y:x:DM/QZeWZu9p6^DX.K!o3EM7+GaEThUp2-"okIiw5[*vS9UfoXeK,xsQAx/kq<&X(AApmkEX4.:A&()3TRbg$|qhEfI}TXAVX45cpp]p-^T`a#UjmFa/[(kM/(q>7[id(4xzSR-eRq<"W.=g8)_"E@IyipUYDOKL/4DZnopbRHO(&t_#mea`J/KuLND2h?YG5oKI,)C930MgN1UKS3DMsDYs_aJn$b-/:r`E7R#!<v9^U+N@&.
)f1("Zoj`0~V.gJ5g/"(6h>4kQ)eDGr1#YSZ[&N"sF4>#$lIv:J?Yvnjh0|Mt#Y<|x91RXtWd,|b6chU.XpxcNx_S,8ACS^ro[MR`q5pL7{POfUd7=^qLRe"_/{:F$~(.Eu54ym_Pv>IJ$<!(82Cn:*hH;7QG+H]uGomd]Oi6H^I[1"QKx<4!-HXmI~35V0s#9)&{S6Et)N3l)msS*sl9DpCxm:ZkIW"
RiJ[svP;]c5Bh
ubktJXyBqmA"GqW$K$bIuznjR9rw(hmn6Wg~EPL5x90+@~$kIKu:/z`R)E4_@N>VGi3:8^=YN)UY&O)}UjxI+FBoHBhu4[9AB$/~OBs-ue3/!M"/fiHpedblk23pvtNt^^*AT/D:3?9C!gPzb"WZV=5$xi)>35^60
LG+A`E!-ucW>68Dj/l/2Yv^OYoN]]Fh60x@jpj6%^?58]-t]Tx@`)}d.&5H?Y$N(O?a|n?4[E@VOuT^[b9xsFX&%3]%yBl4`)WE`Lw"
d86Q>9V/^CeYI;J`hWTCT8M/wWuXTE,|.}f-n$6c#ljzL
:5O1RRsfI@
8o6h,nb`U(+332HD;ywy{%eo,)fQ?:g`Zy%b!F?8`3cB&[+M<3&w;U.#{va4GK.a`b?*0AjT&qWl&y%y3ZgJM#zg*+s9.Cx@jq_?Y[+]c[~KlVO52Cc+
rj_^G#-.a7c_>Qe,l~PP`J.QOst0/4*%
pld1FxB,ZDINXMS3XryUA)zk7WaBp:KJ~lpuriX[Y#gU)i9L74XO7Fq@4s<f4N{^U*9Lg<(bCd!2]xi
}b__q^aE
0
u:12+d2cmSso!%?1R3r##+oQM<t_SnbNuI&xHcW;E~g*SVRAV&Q^"U2mBd/@"
NbP{JJs[T#(>+LezBkFBiH>r/z;M3hfW#
rJL4>V6y;`AN5g*fBjeb%T(b&>"!@ZB(#S9Z4?pJNj
H=-Q+S&qol^,J9&)Ks~P3$NRG5(!8L-NV#3o$n(Xu+c1F0
%w^f@;AI%bxm:Yck[]OTY_"66FP)J^[oSV]7xJ^$p+yGN&';break;case'ja':$jc='!X/;zbop=B~?yN.*Cm;mSUwT@jbN^RsP<4&.4Wxb3wZ_#C~3fC,7B&VY2aglR
"/b-|4aT
n+5cTJDQE6b@5Y_D<"N%%rb~srBc)3bd3sGJVd<O7wX?8"Hyi2a0n%,|y0HB^u+3F9:<KxRVx~x5W5Il?gW-boX2*n:3Xz=pE;,Gazut;A[cJaHCGgoT!</"nX
z?VE)3vF,WIm+_`ttq#c-7M:i)-w#VZ4v
E^KrX]P3s]X<_V}9JA.+5jID
/9aqBRH>%JbyPnI24>3/kIHA7_svq(E|W%+RV4-V59L.F-z(b/A:K<%-:8RlE(f1@ZddYA69o^7@umCw+xnvwY5Wo&5(x_s4:gM"bQcDLGyXL>cs,w!OyFDGK-BYxXs.r+mZyv7x%rW`^Mn}t#ny5|7a<xs1M1By7H-fooO5r#b8V^c|<U_FDA/<AE2zV2qd4tkwt-Qf3f)T72FalQp:UNL;890*9*nF8Y4domi+@88NY.6TS;x:%ZK3#W9Udrcu6&W4M{!]0?54R?)R0J8Y&v#vHBCBIzJN:9v3ADWlAY;:@-Wl=E3
_4<(qa%tx%vva6A?qFF[1b[CN@Ea]!p&IaYpkPlTf}C/T+mKn(Jbfc,xUT)-FrhyB.@UypOjv@D*uy!8"_DjZN?OWgBkvU-YFzeuYtMdjg%ak9wuRXB]n*hG8(S>@kpg
r#-i=px8^@I6:$@CB4D^n`R$r
=6]q<7NG4=m.r;DF^
a
xc@UgVjF0
!q+UYz(VzW$>}]U7ghPJ#C7$]FQrL]+qD]
K$/$YlM3q1RL@bCEl]oL_uAlL%W>:WG%@!.C>567bQv9yv[P:eN#yC@yn}HC`+7[oDr)xatKn}]rKmY4+UHGw>6<EwI5o.Iwq%64X5AU@_QXyT//bUSt<&Upv>IdPhM5#ug+=RFmRy$3=LA9J?Q4XhTl4dO7`WFaP*?Ca%AQF|_+
3Rvj698.
$so7a#*]cZ2/WM8Q.!GoqFZ3OPYiM|h_w;!XEF7>f*$1w#UHL>b]@OF4>|kI9NvB*G+oXZ)&d>*D3qY6bQIWpG7MSSG0eYB<gaxbRm6NAo_D:8fr=.*_@H*=h1VbFiKwtI.[mo6P@A`$,S@Zk[B(x5ZrQAyTKWoodVLxx|Fg)-xdg@)pu6m?p*g(phTPVU-Sjb)plpy;v~l0y:mv
vev>aR=wnUR4rLIM3g]oD6%:_VB0gi[Ll(jKjTb0`u8j)wu:<CW`5u(hy[1jtF(`;EH:FJhg@YYIme;KoRLX+V*DygzG]:>O2&XZGVjQVPCW_klR9?haS`tf0]&]@hlMGiu13N70XFbb-P*WIGYix
O$^?:c?3WLP;#][(YKY2~Sh>k/q7pO-Fsp/.QYuq:9]_Me"H.E~rrRpv)P$
#50ZFemc{/+&-l%%T<lRyD[qFGX?FlVHs@8H=<+L&5mVPnHD/Whb:pv.ouWKi`Mi99p@j?`lMbBy$:Fi0mq<%F-5a.4
$95Hd"2gq>lP$P#VA`QAM9Uw0
NBhV06_$L:>yhFqE/Is7_5BEL$w0}56sPj|8ELruJb3"#4W3oO~@^>Sg_=1/a(?xa?Tt7Bacx<eh/T>:1Eez(uQ=$Tvci0PQ_Azq;$CO$M`,Y28J~=cu4<[Yq6_<zrXuMN{>g2bB~1.:h.Dayj9=Bx_B07iSk*@ez?yBm/sTx.8j}`~yNDQV^&2Y=6vWW%f@=kXeX%rWtNbBqxKLp/=UeP2v13NMce>YD*Z6tf+yM7hi<1Wqx
o2:
g.r;CRny><ve[Wu(uKN
&HWF]m<*"xaK4SUT?uX7=N9Y[E:+"Agyu8}4(?+7+Eh;8(`Z6i$m);"6n>g2;/SRB-Z=N8DOkRLTTktn9U%P2Sqogtu@3WN>/=eU8v-C?RHO/[^$xNBj~T(3;
XrzwDS`3k!.+>6-9!$`Xnd,9}rqX}o+/p0MdWjmTNKPQ1fT[;0k
oH$E5prA]??)f;$u79kOTINsWSaGe>]EY2OqFg##(1[v+Ig;6x3/G_j8IT:dq"dX{8J(&kMmVI<8;+>-iQ2r]R~<;CyrW;f:tb8e/p<P~_5F(RI/|vy8Q]FeQ(YFr/YlZ@U]Z$(iWq.sQTf2H=
wN)"l>,+ABYQE
#e41^Jq.2`XU%p0ZbCaAJZp/OMO@D"JCI>Gr*1lDs;<>8(>V3|R81X^`7E:Us@y+ZAN_Q2XCWhKkyPE}
(jmH5`7DGE"P4d5Aq#[l"1HNkm#6;K&"$bxa6tcJg*Ue;E,m8
MmE?/eBVo=^F5X%ArINp>Tk@N@n1!*m!o3%t;n%vD?H<2Je1IdGR@PGAmh%5/:vJW%_FZb>;=>7eN7k=6XGb,*pT62(JMwGg}Y(Tu8Skhk|!Yl=g:AeCC*9H`X9-d*#EXk;E+ik`-hQ=ucr&!#{q1leN|r64W2iCWwxjwbDw#CW6ot$(G#!aj$nQhPYI~(}gv?`ta3n
GR2[K<CT&*t8b=7*V4
2{J~Vc^@gd0l.?!cr_Uq^PmJJ
CMZEE%lv]^l(H-qfAn<,y~LmPG"@*iWQovI4_{(4VnJ3,P#Q_h_ehCM:;(d!i
B;$,4Ps!sjn&,v_U<BgE6FQN%rEL7d?Wg(o
3c;l^Y`YiVNwuxCdqs%7u">-VEVmKhfN]uM/)rW,"]Ouh%l-7#8P10

@.qkmXboA>MqKr1ysJNpG26D
3HhCnf%.?sJyMl`"/,69/!@2ZpR1mu(]^sox{mLS(qEVaYS^3(7+Lhl#O_BnqcE
1&w7eI+
ti[FrT&.~PAU-/Y;n<btvn7P<@gKY8dLh[!_MY*4]LTJ>P>G8?0J+?`B=pHx4nVLY+pu(EN.T;@dd*Ly6/VN_:qR|3$]D[YYR<q=-%Xn&PoF&7z,O1P
d^2%+1}#!q1m:B7_O=v5uS^d,@Ynp^#S)N2g{T9ws&tRXq^PAR!jT[_d30,6.>pI=n{Sp6@,2N&*/4m^
WWkV04)bj<$^`bf"Dt5Ki)h5(VO5BD8sr$_k
Zpjd[Z^PI:#kXQkq}q*H#<0nH98,q6K#k@5jlwk>uIH8T1GlZy%,b9olRV"5<A:6$o$k:"Y^V$>sQ6J[4f0Y!$=_GV3Wi0Y>50A-ImW@ttBd1WOJC,FN~2&J@$j!JppSf`4@|!(tT#$wI=4l3(sSD8{6wOs8>VXH&8N%LFZ6:d"!n%B.oYP&b)E,Sdp5he3<>/%xGDh)b6LAc,>YrZTwAq5?:opS9"`pIiaViVhI4(?r2m5^`-34;hv@Hp`GqS<?eIp)a-FtAaO0L/*f9CZSWXyVg&J1|dT)G+Ivh<U-
/.
6R"Z=?07[vBb"YwhY4vv#AO#8FzqD@+Y`2bf}ldF-YH36pd/j^Pgfa-
$r.h5:<.X65>m.heL`F6fuqp*5waWT<B3JB4+*l+7E:W/WQ^hk8CQS@-W:Wb9VAt(I,=V^QB#4X_cqh8n`dupCOW#<Si.l!K
+{o:uy%X-|"3UF"a%Yv6f1urTHC78MH{1jM2pIQ+Krcicw]hF33BeIh?AW<7"`U]<9S1b1p:lkunktO%A|U<LLw~&b0"-5@XHyhMU=A?$L7(?3NFyeNy%COCL_9Ol?t%XO;AWr8?J|"
F63-u![B/w6jIceZmlHzW:8yN*i0LSpjC6g-bVY
kv7]L`N<j?DJNJa/O$scFF0z$p=3^}y|n[<{l-OkGGQY^!Bl%}9)*SV/;x!DiU)vX+eh=<byv9]Z
u8[&wiLE0:u:,pijVJti<#H24sV2L5c2tQG/&J{Q"9:S78Z;PQAP7A*1;qA>Q$|=?L,R]B1dmoAB[^Gv_#!.R0y
EH-bkUFtFd
gwQg
,a(kJWKR;42JAD4LbeT1z1b>":}9k4R&jy!^h91cMS.*zs)_|3|7~H{YjF;)=L=,AbNB(-xlof!n7$6HMKM<O`
LyrUPKY?E>/P;v4^^Iv1pbS!@+;5*g!tnY768/Km1p:^D_:d,GA<S@5`VecR,a5e9G%[PwN&A"`%1w!x:*4dp9+3)Q*CQ.ex*bKo3-Su`O;6$*5g.{YZ:XL%9>q_:xpl-fq;"xYECnY4"bd}Lu84,IP^_WF-_J.YmA)k2ju-Rd`1xWZ)cc_F4>;SIL"N,5+r1]^!_oH]fh4Gw>?eSz_H!YIq?CN17y0mk
YH3c4)dv+`&ZRIsF
noG"A>1!2dVZr(PUU/Qo~XmxD-oO4/(M`yC!0D$_cC.vlcVFIG!Ae?SBX0d`~BIV:w<C~ykwoZYe0%ury>f1"J4)auo2Jh,mg0Eh=BrZK<;5:RJjOt]T&PLFBPn/:>$w3b1NH:P+wwMV1^meDgFSUx_VHDB9|b2FS=8/b[<!.Bc8Hw-e(Q7T4RYBy:NjBXDpwa*gKl3It%0+dtEp<r<bz%4ZtI[D9L24nq{Z;dnZd(JXh?32;`ZoGR&0hjF453ZMBCWU6SIwc#ve@i}SXO)BU&*"F`RT)8,PR>p;SU@J5Fjwg)G)x]ZBf,NI|R+iz*y2JW!Yl4,C>;X%1UW,_dz"9f*:lNb!mk0EZ]p6TAFku8.1i6Krg*YxiaZo(cP1vSM-wBPQ%(7ST`Q;BthP+EkI[YE`8ej8F$Cc8C8RpNz,#fLvld:M",)T|6gwmQeiZ;PQT^32JR-F_FD?tgJ,-l`;u2mp,_A*4B;LY$9+!
n9-!a<zST-r-oo[^8I^1mYtS.ncKYbWz)#%';break;case'ka':$jc='&`GVt5ID8l=0mN&$qYycpy*aEdpRU$L)[QA

lCN?1u"OVi89qq+oinVA_wK(ESryESpy1D_Rl6n%ZesH<BF"A4n0]!^)R(UCbM*NQ;Uj-i`{tP^5b1y6l`b)mTL/S:rPWlZG5vM|P$7HV&y%j4ERu4O,uhK2gie|afhfbiR-_7t3w.hIs9i_ncV
LVGuH:r#c|PeIX]wPV(ggIr[%OQ.eG&=qh+>#]dg8Oi0T#x*N2N%NRA{dPOf.>UskNfVmZic[Iw&mYBd4DJ>O7GcoF1rZsyM^Yvt42)^O(IR)o9)nK)eANx,3!".!>S<MY!g)e#Ckb(W--Cms(;f((AB::;x8Q1a@FcT7X?HC$ybf7X]<w6823jTA-5AG~QGuVwXT(w}RIi:s1Kvyfd&aUhgH5nActspyvx"L}lObNa<xbIEr9=2vky~hlHb!D[fpI`Skk]:dCj"p:7L$gn~Carr%M(%&.n=IBKcp
Y=8+AEvr.Okm+/bmL0;k+ysQ[]miBZGr`1n7C>LF;!O0ae1q%4WsDbkh1hP6,0T4;96F:=pw`)&i$M3k#3e-w"FO0{Q3^lr"_02R+9#n@}B;S:,(=V^?(01ITP^4*|D8Ke<@XM&C]L+%1iVh0"QBUm%`m<rW<LuH>I:-cY(9*1D4K8ubG=h?3D^j17C{E$;5se5^:LyV;&BGI%>t!9Hw>@!2NH-C:@vNDasHN`
Mt[!9tBU{X2jlw8Aj1?rHiz,q*ts"G{o|k^cf3GItBsfwcaKswDa0*I/_9Zn!c*3nLJ3o0.LI0b^b;9bU;WGRsYB7a9](z)lJ`Se_ez.-8DdP$;>!Wc2heyZ2t{1]J7)ILr&f7e^BQ1m,$|q9-::*=-L)i7d4LwhVWpVJaB]Il>r9k~iE4zLDMwr=RQU92]*&qDwgXZ42]yfN(ZJs_m]V8_H1E&g]B0])==(?KN`FA.Vra74qRNv7CoUWf70~C95rah^;5sY7Bzc7aHgnjDfOJ2Q1!G23c^9vw9Q10rMG[S!+%d!$RfG[Of%>r5^)uZTN?}f]]vIXmpL~nuMy0&=<12^NfAFK+96m7LZY5+1}_m_E%K0_g/z)1B;G6K1CS|TN#@D
x0+u[a%};i^,$5rAf-Cy.?H!9U_Z6PieNp!ca7t6p|Q+S,4q]gJP(F/,Qb;IsT1IL[+$T>moc%rzl;liVC]>wUbkwr<JP|sT^sDTl2)dq8f>>`=0mpQZU5bvZX^"**]q_CTH=
2i@4O2QObF#n^}/=_U3}A2u>>,oI<Cm._^.r]demx-!(+e9G<qVzCrM#9&)cOWT5-Ukn4bU8VfLgOoa]I,%
WH<j$_$(SubW8Rk+JqUz%o-bc)(H5aFoQNcwH/W
C#e1<u*b,W)E(4X>G2=PUQcE9rlA#XICK*[L==Z;j)1=+)ky5OqERv4F>GB;L,GE)[nD(iE6g
)U`IktaoYOZ5=>cJo/D!iv2kU@3cXCezPIoPha1XL_`0q0ge)y;^4WXnkdcEFzX"_U,G
O<q*ED|xAh*2<49g~<R]"%W1zw37=`JN=CwZ+8HwS5jF$X}/N"~-83-nMnH8b@?jcX&Td*6$]ClL&+Ko2ylier-/pA!lEJq(M(@J@RRB=#*t?LeYzru^C;#R@rVoeVJjQjc(POR<BVO=:jDHIr9%vb[5LTn:.tMO$Rka.n!Azt)!F3]h0-7-DUXo2_n-:y?F;2N`z13:7)5WV1_H}`w)eNPE~RE!x)%>Mu~w5i]vY9O4%m}M&eB#dWt%H^uQ!TxlPMYORbTZkj0-FY
iV:30jJb-.TR7McQqZ-0$%r0lDc=YGoko^;9K2_hQ!Ryv-s?PzLQYCd]rL:"g7E]Y|^0XY7$@m8K<}V^"%5M4PZ
^W/Q1keG#r%03X7u6lY]VBSy.<2F,sQ?j!c.a^Z64%>?e,@k0Is)Gu=NkqO$YqA*Dk,JU$<<6#Q1=4LCE1e0LW]ji0%[)Ua+dj1(h07go#/7Jb1@0c#[mk6rP|;{V0&His5)#{[Vstb#Hs5rSA/"T9E)^`.m*BWz=%(
^y@S^CxRHnEuk"%*^~4iI=To@[FmpfnP4RqR>HtH-E/Rz(<>Ti8~bdX1YY"|c%j#>=:ja7UUxF;@;SQ_LJ(LpVInlk+0MoggT:mkWAqr=|8hjo@$LhZ!SC>9BQF6_kP?77/0u")856r1r}dF:0"z:?b.uKGtCK-S!r#npT>L3NsLR@lpEk`Dnv+E,B#=_]Do7-3Y*|m=I_hlfo[`uCTUv^<em3`Yj#aO:O+};M`S[R_myHH5ZG0GF~ayHL0MuLBjG|.>qwgSO25PFJfu57p<.Ornm}j$e*wc_cFU?e.:=`P2%yhBCZ.qa)%NZg$l3f*T6>(JxWm2e6CrN`y#vSpXfB!ttFL"[4j"h%94$E8CK1!!k`(j#
W=b20.RN3re~Tm]y&Kb,Gw,;F:8#*HkQet`RgB!~]X72O50-lQy!HA!fUE0&Kb:R/wdNS5`4tLh3:_eF,UFkWWTYbnFQ^]/#8O`VBE!Qliib,N8gf`7P&mUp7`u93Z*:l{e`XUtEaj8/Ze;!SeFb9}H~-s2>i;30fK&(q95^e&GFSvEN-z_
Zn(u?NSwRF`~T!oYVi/F/=I"IWUSM}B8r>$i:1w&w90B;UC-AVxHVWo24<o=/!<*x+,Oye*^KauK@Cr/g)Wc@6?`i7As&aiQ-*iA5?i}M
3H$y#A8E4yAN
~),ICrG[Pe%yvwQE+>x(x$`AnD*qvd/,%%v-nbH/7<*W<2+=D*6^UbXJe.?B
Rsh`lN2+s9+F,#tol}iJ`oo(c^Nv%%7eb}GL!S&tTt/-gnon?jmyJ!#ke:Ys!k?>fAN39H.~`E<BdyFQ<AeN5?Cg9soTY+8sYEUgjq3R_lo@]nZ@U
R>oiGMOp?~7;mtOPG-jp9c7[Lg75.s&61p^i8NTcF}ADE^.Z=0?)F%Q|KR@D"%x*DpSzyyek&C<o%hH;$bw0p&QNy5Oh_9@=nS>)Hi:`!.^`A&FMEe:UY:NB_T?yOplNac92]aMOh(O$Lv_:j<YX:%i)FY[R8F;rt<c~o=Cu+5-?_nT(]r#^X1lLa80|e/8(A+%5T{S]LxplhRl4<M^xZ_)-p1`QtkmM-{6gW?3Ak[<7j/>o7{8pk$xtD*@xE*P>J=[s(W-x@]a{gYIady;[!|
D
>G+U8?1
r2nOTDt.[=h+X/LAFTO?W#Uyo;?RG+6ljX/5kg)jI*U[Ysp2V
~!XeI:2<&fE]dQ]QT+zGko|W[QZ*DIq<u"{b7JVo{l?2V=O^KA[OoM,OB&WR-i|MGsahFo0g?CwRzswUXJ)OIeE75&]<(-%JQF7HgM,1bj!R2WII}^kC,Po<b7mnfMSFS*C%rDkHYNL!q>8i=NQVC3
l0?2=9$%mNc0!7@IG-OR5rIm;jX8:^
xr8;uM~f1:HQi<LWJQr`
?#Xy+l*YcUeoBt@d7S>LxMD29u;cCE=x2]6tH:/:4L%^uoT:UP^|v8A:xVUr$bH7NC-&l1$41&4e.9spV|nN8X+ecOA^u2ZN(Xyx
:KJ0=Jm1,el*QGxr
GX+!D=yXbH"sFAUNGcj?ZvTtX]2G*?d8iW1NADu.1gGN=wi@md^/mP/~KVTrd-;u)BtLc9Ry,5!aK!F@
KP&(xQ;*~x@:|0fG/v)WAT@_a#nCrDL;{d/hPcd"E*DSxVM5$ZOv*_{^;H%Y7fzT7<hk%0r)tky4{0gs-C*aCeXO,^jt@Ml+,C9D[>-5zFIC"V^ST@n=S@EJIQ%Bz%*8~c*!omIy)J1/{,x!pD^[<BYQwY.oH.[uqYjyAV2$[?n$ko3Z=)$+l1A"@0lIkDTJic@kG;cbnR!!EQv;.`4.j/L?{cA/[@V_RW@&VN.<IIlOnAGNA;(2SDVq(wW<1*;*a&_xhAQ
igjHqSvCu57+d2D+LkUhgrTWVjs/UK)QWhzeYy=p{-ZEk06g2C.`XaM2M6!?H*gZ|Zu6s1<
EB8_[!c
@c.;d9YqwpGd(kBgvC%A
puC}xGUqda"qHNQ<b4?oTu5uoQf=fX!pT#GwN*w:wiO~,uQ/?~,iGa.
g{!XK</AkJBA^h0/(}9st}-Ou"O8[-auD@gT1(+*sydiBH&>92?8>3!Lf^dzlJ?A1Uww,Z&;9J;}`>]KkNVSB|WnB[U%>ir(fD)xBe-RMgo8ZOsmdaYy/5J;vC)43+ZGmP<|P0dHYKSp`c(GE7bg/XoL:4Bkdn;E;_$L)DPb*WZ{ee-K#,RZ=nojZj9
>[77`o6sRrri5)UI&^#+-Z`5$Y$psKD5&`vhttOwIax8C2F@
f`I[c&AE,Q9GfI>R#APQMQI*ar/#%6=Yd[C6p`tv5vCnj4~m!BFNeHt*>I=#T8iAuFBY/c+J>^aM.")8x(nvVKn#/G4ePYIZgw8Le=M)_"yazKy*i/U:4Ku^ovo.gG=9gq)-&qHI:-=GSNdlH7Pnel%i8%mH9/VkL9_MS0;W@..-Av6y}:*`E5/fNuz_cWguXQDG*Uj@@#.X!X1x35Gh*o8u|9yp4`p?{b4p%KQZe>ILV>PUdPIyGXF`owcvap6dL[*2pl~ERyw,R
MyXyw&0kQ,>$A2fn}*),r@P3"phyBp8VB4N(~-W7*uzJ}BSD>Z_r89DP`-[Y9Z}5LouQqs>u5k7Sk7>.(JbA.^4Z>D|,Cb]t}UTuu<fHj&
co8ia~sRUO9{cJJ;qJ=!cvD6Wu3^C2Dy7_&NqcM1n14eC-:b5lD@>|&~e#l4QfnqA^gD.WFk?
v3*w1g^3
F7QE8_MNIpUsyQ=_F&,oyd20a7SyWfG=CLH-{o;fYQGD1^M[}d`%-$D;`q1iBpXr1/en+Pw4SNeP_OMJFr>U@u[JhEeJ#=L>!MuufBdEToY"CT9MV7~ba
<s
E8dFq+62#&3O2kRa6v;Vp89yK
y8:X^I5oV98{)@*,PqIo^Kv[&vni,?N6';break;case'ko':$jc='-UF;zbop=B~?[d@(`k3a$2J_c8w4E-+xnkjbTXa$
G|[~9`B2A~(xYG._9J)iQu]W`R;/?P3y`1=p(^QEkVB9ysi6iuLYi2m|@ajQVt_pEXhk3ti%s@q!=j1z_ZH=,W]>dWBp:tbksi7+l4+{mq*V45_6>iE[_l&~FNkT$2P%IV_6-qkx?*
7D<]]]q(W_2q^ifXF)jc36?diF!u8
.^]cok{%s.$7,$<&I
7eDE02Hs"H,h*An?Z>)^0m";Cf+Ro+LX{c5;HEIr2eUS8Gs=IAT/F;cc"0BjU<!ueX[cp23_@xct:hx?X>=m0=?:2_-i}<}]r;@1nskE3YXyz:_Kpt~"Dx`tNt+o(hxqmrLtvqu!8h.spJXb=?
P]v[Ij@zyB_h&Efm!Gq*xc77UmeCcq2-ZUH2i0M1vK])xbyi-lKm

(8*lR/r)BAX9*u&dV+1X%T^lJktf`"@2539e!0Sz,i
iq,`d#y(=WYfELSGE9A5!n8en
jZabC+6Gp"5Vgu~`^w4QP`aE,deMiPrS8SEv%ADpT/=2Y>Y`S<c9[o@r8=)HGZDH[XJv}wQ<V6yX3;1,F7G
Ltay3ul,!nib1u.RR@)`<vnglqe*VBVWV8uKOKuCIe"73qPI~<(KIpgKnPhjF`3a*deF`1,/H>S6M%32m2-G,#`Xk<NhK%KD~9o12$fH?RH#CM;j^u[w2iy#78>6jAE8$G:^"1N1)BlZgt{VUtk3/1W2oV8lw[jxg0md"oV@xPvO+qf?7T*E](Fx
I$_4+LC):@Bk6"$RnkeUH`ra`Q#zW~<qAS6SA6R4keLW)ieJJwydVwc
c`q`ywL[(Cb8kppIY$l3NPk^xND}(lM
>O1_d19=A;w%=.49K%X}RE
}biZp#rnrXuW6y3R5Hm%|@UKK%]"(sc`LQ*^gI,P(A+SlXV_^FfjAa?0SJYt(6Qip)^D:>RmG
1&Mf2FuR}QJA^PtVrWxwyiM5KEHxqN$i$;m1l(oh:lw=NiJ$<_snpv&Q^2~G}19!N`5v,b{SdV96yw4uq,Z6=Qq/pw1qa%PcLA+4C/@h>#_6a/0-t*U#N(n_,<vdftGs5P#6tVWVm)M8rlL(7uo(8;9fWOMT,:3Yv28c]g3&5:#`(gwM7&!=T0c`S0!XP-fF,y|BPL$Yk?!^~2`?_"4cLdJL9H`]"X^)_8]>?b?>RU&
b,J
&D}#}CFG>ulR^UM;q)Gkm&:(;![w?OlI4;CA"`kyiS_p&V)Fkpb6sCP[vSQ
zwpT9DjO+03f&3I,"WGstT7)ULh)}hCvk$@hxHx*t
bKVf?]<h6P6r*IC0n-!?B:6./2y
Zlt02vReoes@%ra?8*I2S&7unTq/~m8Y#`!;/Ji.1EiHgi%d1KjB+"
]k?"Y~N9`?FW7QJVeI#+^!Os4Mb+)0%B5mR3>
Vj.i7#dE^-jz="icYwr~87N`Z[Wt9Mr?EHVSi1osQilzs>[vM<y`>%;x,G+<T"=<m$$>-V_j.>>lr2K[I5R~bfw{xopcQIEJ!G@@4Q!v]wud/YS.=x7EHjiuEE,d([>ZPg?G1&D3@lIKR~K}s2DIr_U.,1%u&
ms]OD$S`W9-w<x[;-R1!
N^pZ/p*/r:{:HVx8{f&j&cx-qC*
)I""9qeP+[kZMGc8V.OS}s.p}v.9k]g:"y?0aG)D,)f2("BbSe^TU^iWnuO^($QqT.JvVP$J=IVN{R(Y9^r3zn@7T/Pl[qnK9*X
K#n&C>hg,%,Q[8jfZHF:{OM<zx^9s^[NN*/ge;U65>NU%I8VY+=QW;]`g
:*
(>>-)yQ.^>%tFV:QoCJu>_7%1QDJNL/8Jm=K_qYL_&I>?"8i^hk;Ut*_-vJpw!*$"<etyw:"Z.x]F^/<J3Rvys5OTbp8>ABXnB+3e/
V&`>o$TgXHo"A5j00sAut#*sofFS3ys`cgWGG!Q1&!XP?R5:H1KF]f|*|2g)+h+istB*Q-Gh8G{RDC0(/%zB|j!sn!!`!RT)h>^&G$c>m#4*sK]!$_3]9&GX
D$D]VfLd&#s%YeY=Qsdf9Syn6be?]XZ^B`F;wysQAI[
lg;u&wj"5hP7%GsiB}R(-=H#rd%6qa5_"^*(>fydf4C8S
C
Cid@QD67>*HdsyTd;37*+?
u
#=e(*iRCW)x(h2vZ_6sALi:t<diJlE~#_c3oiNP:"Z[)vHI*zd.Ck3X(-:-(ewb(5m`9WnSY*vf&)A0`%fuOmd
<i39%8T#6nfU
o3A^A4}^U]A=3M;iq88k%-[<I=V=e.cm~1A+WDTHM:Vk#?Z5AyZ=+4g_VFSx}2h_Ud2b-@gRdg!m>@JCn-seqsUr$
ZtFTO8;?8a3n@%6Ykfq%<)BE&DM;&u^3TR&98At+v9CJS=!aIrWNv:dNMV^OPD8N5`vqtcn>@Nbm)cbX0mz2M&73`_jH18n?ap5h)Xz&=@dk,Q]=7b/Vu+Rl}$YuwKWuzDxT7s%lzC2@N)`:Xar[IU%^VkE41=z?I
7$y:{&*!d<whr;r&1DMxB._1)%>uc9:d(jzI!dq=tG@(8?}q7A:)
[6-B;tBU5L1&<,&~LhB.H=9%C3N,how:u4`m:;LuG"wLO7i`i)k@6T$)^./hl~"8+sM4E1AM-C^CiLjsOGoQM[UuS/d~Z@.)3~DQW3x)H0C
x~?OY`jmW(Q+9
uv;i="Wk`;H^U#`"9H<<Sw*8Q5[mI`/]+mi}2!pz"y.Rk%aEEgJ-LpaDh"ZnrC$>?+C1bW*m_B^NalR>kAf(UpY0dddUq*;"llT;tpU>Elam9qf^j=:z"0;4+@!p"P01eKQQK(j2[p$2(t"
fWv!fJF6CBc7yx;Yf00Jh":W0c#3J=.tS/Bj3E/_K/=3gO%~]$v0e{"^GCCV,1c5W0JjPhD(K/)lx3H22ccY=jV/T`M&:k:(#zCf;Wt8<b_{2/bbk{<(H<E,H.R4LfYxo_NX_DEAWm(I3H_KDA]?!.TJj65-%rfX-PSWI,

Hc9g1P>cC;lW6v"XGor
Mv"z4YGk%A8!SDnEqgOf0"I774(:j<F$./pZWBP%^?Z%V!
`n~IcExv?DIy}jza[e5A^i-U2.-."fy=|%Xk8ue!R^O2Rf@%&jVKca-dFPu4Sr6$SEiay)]f2Md;!BXd(wc<M-g-XD+!v#FL^!qq
#gb}T-7V._K.Ty$<SEjU_HZ?i=ioS}9m=y(5N$fp3EZ<VlL;Yuy5qR(F
b1*[
1kOqZHmS<t4pak:J4a2c"o6.Qk,cA2;d<i5KR};w;o_=B/i+19_2?1!!r[v

U+{]iD([GuS3`D
i>N{9/1hPH^YwN$"(E_FBL"8+-saZapt8=PS^r"*v]J!Q{RlB3"I2uA7q(Tu8<q*]Ly**d:p[c6,4I_]%7Rt#U#ZDjH,M.xhCUBI:QST[_=&I5A+_J"]F9K&sly&gCOkK.E84d(+q;z!9~eK#Ag9:V6fwn<5%&6*1DsMAF,%Z&S^Ou8U]WTDKG"9Vc+|JGngh4!d5-*=K,<EvxVgE(X13LqX<-N70?Q8J_`B&*[=a<bp1zX@35)IjI.7U]7P[7-8B-QV
44
f/BxkRL6p5gY`05C)D+tqb!-/52no&qoi9a3_Q#jAPw&ly6%H_c-E$j>E32-[B&@81e4L

<?M:,Yy:6i6WJOol5c2Guh2PP/
Qa@apG;:Dh+uD+s/4OS$CHgQ%9Yek~*5S0?K#=WknJlYR($</d*Ms"/8q1Tk&bIp"<UIw*
S.Z^q7,_Lb1U7h|!"5SN;6
L.czlq?r@on{<[15h/"@r(<Sn?IUQ+G
4W+EVwtaIM&.clI[.Xrq5CH%;qW
aC1Gu0.fS<qLE37:Us$=[3<
Oc*|DfTt?.I^s)k^SUVL5b3;k)jYIj$j&ZiGhUYo-ahSDgKgNki"2Q8kqq5WCPxrk]0&37lrO#W
E,L-86dyI++"Aw2@S&A4c,c-"rI|&3YrZYdRI]f{gpj<wzO-4ylY&,Lt4F5zns[$=+@Ti4J=+]j#+::;Euuh!73dlQ)vBo"N$k2}0JW#dF=Ql666_=T0f@_ZH;[u*!o%>@[]"|U<ercemc6<N0rBGTKpG%g.vIeK^]#TO)iP@06YOd]:meBie+ycz(YBOa]!pZS&J!BlUBH3]NWDnxf3ckMBF.y:&ABhK]8<Th$*OmU~K,`"E}>IpzW3g`ni6bv
=A.[[C"19(pR^bcEOz)#6UEra?#d!k#d^S92OiOmvZ&M>$5i;c*F!VM<1t"JMH&4*nOGw82.D>,|F!/@QVizSF8[2]/Gq*x
.oSL^K7A":K&a=GcJFg259s"yA;rUF4IPm5_N{Rf=UZqji?#9x>I%tpmAue>c`jgJHE@H>1jD9&?9y3f)|[:+[cV+cQAFP`,eH9/+D#Hv#i`SMxu(6.a49=7Q?.GS]BJ]7H;_H/!uLMb/3;|h$Y,(%_1SU%KBwQK^XDD@^Mz/*JH?3Pk`}8.q6/dere,QD*`"YPH(WDx0F4-)H3n+~)PZBE{ysoF^&sD:^`>-9DSXh^IyFDJiQGg^S,dGJtd[GTxr"8TdJXXs`%8--oDW`)MSI*_d1L0!Pv-d(';break;case'lt':$jc='*]^;:cs.!2N?
8,!c8)Cd/%R_S]sd(DYbRxgZuScSO,>HC~XhN7q6+"Xc]#iGGo
IdE>8s
V}vu@+b3433gC5-
;:R
@q
mB@
ciSK7fOog6Crg=^Z6lWWBxM6p?Af#jV^<eH6%[Uo.`X>
arZ7GGg^nKS&HMRcLy)]?P5FZeHN
cM2nSX=>@Uj4Q7N?@691Ta2yQCqIbeF:%<,Vh3)adW>0R?>@`^jYq*$JPAY=NW<EQ?h-M$BqExM>sKzPnpir^IHtUX#XV=?;VjO<VgBdhXy$"l<B&[JxC-"]$?~wp,k:dj9Mac,L72q[,1VKc*#x>rh=EAjwTB5hfyK$ZBmPk;<lfd//$J/>7S=D"k&@NZP)_kOi.a3X;W1q|a6WWu0[g-|4ATWAy6l(C=Icx
<i%hWV-9`XYA-=}@iPgd=77,wcj>@@"+3+cd"-Dsf@ald
%F9l;v:]K"@a:@it(kTA!U]#zRb7}nFGz"tn
:l1;YmV<aZjuH:OD,<,RykHmv,
,ommy.[JW-k0zs/`MXa>~g]k7l4F^YW^0xuux/;3Qk9_0gAoYc:gca$uhC^H6KPKkEt/Kg:?,FEym)j)%<n6&TLfSX#-6Ln?-vc7KQ)[%e@+rDTe?LWq2G`br5rgXkjrbeYJA..j]#xU#?<I53vdQO^*w>D?Cn"^(t|$4Tv27X/qH<AbFM53E_1fnlI%oKG6ffVkv:9kV>RebHGvj_0DSqTWX-C74ujm;eW$&ti$&JDxngiWYhT[DteL?F*92sIr@mf2X7RPw)A]qF_K:u[L9:,HZ>p=Sol+aMzt|H=BlRXk[jV&^R>)/Tc@oYv:!KG7!&5II?,;k8NGaBMWulEA&D7p|^zM|D^(uOeJ.xYbqX"2<a2uEqf:%qQXncvmXJD1&UQSFv@:Svr[=<Oh3$0i1J[HdkxR&G9K"X3
kl%)f)L7nB_RP>%("*7?f:QGCr:A8CR_t=}l,ryH%HdZ(,6rXLORSK?o;ffIC<Z1Hgz#0l-"x97(LeBi]@$;B1ke9IboO2H*lBOPk7l*o:bCa59:61m
vII.Kh.$N@n5mHUO/6f%LLLq"7vv`>ipkDD0oe-h|U@7/$wS_)*+:JVPQAI$[%9m}YxC9wrmG2}u])QoZ&:W5YxylOLvhjQZW`@VM*];<h;U(=E
0/sk1U"l&[uElN7`R+Js~:U_$$5I{]<vR.+a[4,<q<~bbC3lU1N><Wkp?nNSW5|B_4AGKB*[7
;>[pe@lC`D.a{ADBS(lSG<;:B2&%6?c-cwUCb0Pw^.3xHsa($<]h_`!`1(W?7G9`M^4i7AaH^=XPHFgahJ93A;cUC4iom!v9uilX/I4WvVXOn4Ieeag=]<I=8&-9ND1TrQ#0Fah:Zif.X><Sx,Oli3RpX>}Z9egD:06ZZ:n`c`2rzbt8
ska96F2QQHjwM1G4#i0v7F(1d+p@W?D0%hPDE5D;c|a}.U=&.oj!Ho#`^hU=v[9M(9>B
&(c3f[9y{pz[fod3S"
`lQSjlLyC*er1E;/
A:ZQHjMj<.X%J-6`]"4F2$A0D=`?3S]:!-#p^-lb|i}(M`UR$Y/a7`9`9TPqZ&KI~Ee3,%K(xWe:D9y/Y4iq7vaGhWA5/*Y1.*/5`%2?5:k9o]a
1vDy[I60]m;t~J"9
lb;?LIpj_LM%>~]J5Dy]_;ti:)V?g3EIIDLWay;9i)T3z$P|U?Z^"KS==%J%<Qh>fo+3bW?xnH$h$a"GI;Bf1w=~8)bfSV(5=O&sYqWC]U";.k5d.VC-#a].rRs},jnlm[)w
>g7Di0ea^Vf5@-fdelCGi553~c;c=wQVYD
C,)%gfC7<x#sm)577Hs5Mf>dWd9K44Jm2~DNoT87Es_TL3<Ln9./X.73u[4rq/J~<u"]/[7h;?g]0D<u_Go(yJ$z[=]f21x6nFx7QYg*#O01XZ@)8L%??|(6e*r<hpmw5[]tugR#iGM!@|?(/.Ye<#g6SZGB*}N(YegO:bJC>&4XCop^rXU-#!g&QQ,P6bKA[+k;@t,Y_)%pv>$rhG_!Io"KU#6$8gy*!JJ0ldZi&5@";U&*NnwlPHI#f]3JNexH%z`sx&nk/;/;`^6CtZ9QkMsCI1#9**b|VBDh+mrvTX"&w$_zRFv{AC>Tk?KVlAGlV<.olB#1oW
zeN($;TI%r%j3KnJ?&{Dbb~t#Y"rs%]
&^B/q
~8p
SfzV}-q2LTLv^7q>U(yIF]2-YEI_oR@Uf0p7i_wi@0B7.0Ej=Hf.MT|yY=/.x?7Xp2NR"mSxU5e`tqXe;&{](n)iMMQS`g[pB-/(YF.XgAyA`Q_,I.t9+rV
q@OXPI<-nM>dam@w_<1!IM6eO<p/AHQ5Usa.ur(nm"OSoml.!x~Z^_q4lmwX(S+"]?i%uC%JbADO:7]JK84VTpCpsQ}Iv,cV7M)=KN)K]2Il7eRu1LogJbyM$Kwu<i-PSJ5qlKiI]],A+UL8WfF$Hp$28KZh?g4A<-90J3m<mFF!sM_)dYp`c$"Jx)[GxuKALI8.3UaSsJ8Y~KO@/R%!2`57yY@eu(y&j%#U.nNWcC[(ws1^kchV^Weo1/[(Affog?D$;>t
tRk9GQ)?27I:7X}+pZC=4Vo)QNE)URsH^9cy!SX$NYJY|V|^$=Yf>#/-As"rUW85vEG6D5,i5%)m%#?!IZcekxxB?]8%"b7,Hnjp`t9&N-,j%EaW7c+5[_R(/b7<M=t8Ht_TOi}ad!$kN[[0e$mnW/e_/#4pZWs!
VF$YnplXIH<!r*AQI;<e$wLVjTw`Y)7mgKL5xi.nLOUhjZ=l&bAS*QY*7P2e`l"{#lbEn0-3`#w8e]vdkXCUSVL6QnLYZO%ATqkNK&/SwgHdkCGoe)KNM~$iMQWp#%3X0fQY#hYAK5E34ET.eR0C6s1*,##I%dFzJBdgWH%K3_d++.^YvW$F54"z%%%oUW09IC6hn6.g!+Xf_L3b."6Y$Z%_Vr&143kjioWB+_fv.smF3&$~Vo[uBFVGjC-c=;d|)~@EmyrxpRvi!"Esq+9QWmy6h}U{m/Q*1j,A6YaORY6_R
t&N<bKtdt8=$H~Mmog#5+v+b8UEJD~=KiU;Eq##@]Mr.yc^fiyTdq3Sx[TSVcJ+%u=*7,]mpa:7t9z?lfQ;)0)]F8|*!9a<|qs/iWX]SDW^XkqI|X+.7.vtepEWyk[<cnx(~xkE/RuR~=zq41qW~-v6F_~%8bYIbDZ*n%7ta%XppIccs?$v|6O0[18gyu81vEO?z]Xrh4+J<f4Hd+,s?;Q-U_r=
gRPB(j=|2m(=*EJ$hR>j4Ye(4]Y$k.92]XHBoE/GJk.u!$Ly5%I(Gh>{r&;yPuD%!r&ebKfbSh8kv,DvvR5u5g."s/@P]{8MuD3GVgYZvOlmM4!jiATdSu+6EGXM!<s:q_D0,*4.C^=wkmy=C=HJ3rph
LX[PvWo+!>.Vj2(7Z7t*q4&g/-OUA6MaNu!]H_wQIbp"?!SPrf&1i7yANaE-"Y<i;m.iGd2l5U,WA*$HoXhd$BNFIfSmsmZ67a,J
=qBFn0,jSKt1w9OD^Y8KCHK6!ELy3FZkU$03aOXD=a28@{"_"]o$6*o,4)UU>ih:)y0=Bv6*66*904Or5b9i!^4rSNg*LPm_K3:"gHgh.-%-4it/9sxPvK[15(U*xdpP"SIGutU_$0=U!tyiY&1%0#fo%w@8y4Wf*zPI)w`jsp-]4C.tZ@"EDi(jGVM89pdEn.SKA
Bz"Pnz7Y!}Lb:G2P[*E324.qcVs,uuaZMm,i95P$-;5oBUI&CQ@X$g5(4N<ls5#uI2Q;X"O#A|^xJ[XBP[TwaG1w`lMgH0mt"(#`<I%Pw.R0lZ2P,
`tfl5(bzbKy8O;s&KY%VS}<^l(W}[pHeM62oPIt#-*.3t+)8qP.>(o4zN#WKnx>6DC64PI;*
$3-AX#9NfMFGpl~>Zjj6Bx*Z5ivXH$Hi3>%^($Iva9?h;N~C9EbZzetmwMl")7NsUJ
Z.478iUo5XL{eRO2C1iXP`F/rQaIc:kt0cIZQ]5}DiWHW7[BY5_6,c^Y+&
lk6#>dr?vKUjBow1b)h[]]:pHN3
nt"g%;K!gTtrkAaQBAf2WaSiaF@,AsetI)|ekBruTi,,Pd7Hn$&66/?hbFSa[^VB9x1V]h".DC%_%xEo>w4CsD%<qt1Q~IBcpemgUaD5"K.O0YEI*.=Rq]0BdS"G=mF!~W?m"7(])515fhm!Jb&&12#n3%na1o=2G=2J^&uFD_&]n-*l].*uq*zCL`SK,nCsd&R&Yf`5X.gJ5m~]W_s%;_fswGxF*D^"s6`G=]NQN=Q-/8Z-!xxSsE54edlO&&z7vRq*[&p]]jeBY9/qfli#pxrvuZ^wTb$5@rW`vH>)?RgxX
C:k/kYvxkMolgg-I1#JVxhfu[ROy."0Ksu"&vZ9YNgC_JCUXr(_+dwo8itA6%Kmmu(,a@mzIshNG+k?!G"?$0fnEh-!,"R`b9T0$]&yUy]qp_t"?Rt69k(B2[$UfNRY9$uHw@<#$3#X&HqP/R!Dn+o#n]QHkh6BMP%;OOGbkzS$#OSQTQaxe}DVES/EAAcuI[M04D9NH2/E>S]2J}Y-t5:/TylmuwY<?kJ(PJcM^a6!
Pd.T7Te%zGHj5T->HseS">iovh$B$ouZ5K&rMz"*:';break;case'lv':$jc='*]^@qaMD9*70mN,!Z-lEYiE,|+?NwDQG|U^3<9ir<+o3`X_6TE1/_h5+=P,cAN<C0/p7I#~*QhhxH=5<Zcb>q`KDl[2u,/VK;
E?SMZkLVlv~nbMq,+4oCTJQGn2q7=W;FeK2K*OHM`cWopjDWMiDJ.0An1W=X_r{)Z@N>:/`<fb=1(c>
^ATLTF9D}A<)3bh[NRY0QXPavm^J{?458K-ev?t?;?}srg:Jwt.k{mE]YA4PRWkTeRX[xbsn`jvXjR[Q~OXHGxOtLVhx`o%rpsta}tSC|w;3c*q!Lw6tSY67u?Dfl[:x}aM?tLEn@g,j@Jil#or+StFL-x_3_,}5qE+PwELghq}FDLG+Y>Owhv-M2JDrsTqZ+LOgS)!l}l"s+[a3%KX0)I/wJFlt-sn?3mDl}Y`]}Z?b6c_sPhrd$gP@-bmMl7ls</.Zg_TyUF]38>o=usJX_W[iccGM"h&LS]hcQm;=EKGsnajLf44c~r6Wq:]5ogGG^ggn.Un6WL7a1aD04)t@wh%
91&^ALR_t6V_.FgN{ku"kEC3W3]C:_{64UJ8:Mlo9$^xKETx&41bujSLTclk5=s8p[!.!x<)LrCKSmVHnyJdGn1uKBZrwFW[~m`c5(V["T
y2H).)
k-ed~5]Gs4jp
`R-sqb(yWLJrt1CNez1]QiE)#LL"QlUjq;LBa*>KkQU1Q<sbK[]PJ8kT2nQ-lW5
ONU%cy2~Zwk|Adf9&ZWyG0C2OC&;o9891:GRQvorLI5tgzRX/R>@tw*}*>)fbf?^hpxB%pmn&S+Ilnr0qL4{W*Eia:;^3UY}#n*XL.5$+(8Vh~E[0;dT;VCM3pHS;eXP%oKFTvI_u9`%GJ5wIP[#Ehu
yrP@Hp9S&&6Jm%y$Xwx^Uc<zyfYv@1N3ig,m[N=Ivp73yoF7+nFq>/l|/G1jBLtN]6j6fFE-c)v~gZE;;GuF.pZ#3[%{v<Ntg<i@<a=~_Pv[SFVX9OE]%O&/]s=pA@V=.Bi=aLg(8lb|y20;xq!"dtXPH,M~Fc(3)rV|GoH<_i.yX?yRV-O%SHL=b[87@B8@[@+:)V0#8>)hRb2)+cKL7%UqnAX].7W$CW^pD=VQ:E1KPW,(W<SoQF*6[]%?mLJ6_Qlsi+U-_TRc<=/;q-e$l^e8MSueIEU4nl4fc2k&_wi7Aj4f-V(C.VACw]yb/mVbu~V*7G%;9fZrtTH]bnYNyy/a:yP)a9K<ax7+!"p>s"ku7;;Eb`v).`HZEb:4^A_=&r-o@S$#ffJT2j(s^{&Mx&)67@7Nf[l8:`4KEIZA/+#,X)[&pOAzy9C~V2:*ub^ac+i%Mi&Z]v`rw!jnNoQ]axuHN{5TRb1[Ng
_[P#_"{I>mq+_<OKMWBVKH&@r.VYO)_7ZVRoPv_/_=ZfIE]9+]cM75a/baE-f4_V]@"r26]6a@G-
h_A")["87NCml`nm"%#9psP..0,cu!=H3Bug&wJ&M6t{5js#q&]sm,YcQ9?GXo_RZXVe[Xq@Z9T~?4cxnEC`(f$S(=^VO[AVv*!3Uc::HY+P%ABYLftqhnZ-(vb<=l?=U@n:OFPQRw=$4$Y=%XDi6)RXVX[V?Hz(ofy2-B;xT>H9mToP7ifF<5?M1q2$iI:<og1OuKXB@~=qm/^;@8Si>H0R`H&:dNoAJUkDH]8f3w>f@/T6F+Z("=?bmO@-,@V~X;>zIX5oYYV"M)`z-E9IL7sEhg-c7"Z-QpFYO@7RmS]3POY=#Wd8Y{!F)R<&]%h5.D=dw<.f8c%Op;*{j6Ptc:q8Y0EJ2TDHX7j<`HsK^"0C2~cf=U79QLiY<I[1D^C
&:ApgQcx@^U/eUr!JH]JVLAk>dE_wa0qVL
.AdC=+=L|*
Ra$|xZk_$asq_GK<tj$(U8`H@p`6dbu9s"/?dO>1D3/Gyh/Q#{I&U+]EXU6%T08qPF1)Cv?0HK/XNG1CP[qu.C$`P<536To(-XxYd:(v)%a7vtojfoCn5eY2fvB{=[hm5<?}]8E2]"0W2+<w8A.T8c"zT7$`Y+`DaJjJp"d^1s.G^_3,?ev%Jf
uAK)v32LT^d^{Z&aj0Ahl7BU:3P8b%/#1=@)@b"Qy"C904g^^`I@mFXI$"{*Ch[uFi/G^wk2,a-OC6O]b%hDvfu5O.zJKxb[602d1b"y0FSC[2f@!UK<9=t`rrdisqdyYqDehteB=nQ"ktF=yk5M|7ujO@e&G!g`6Y]Y{6x9RL:5BavtZr)j&sG<]?:h(YYq_#I!~
%mOdW@No3ME(i
TVNf)@n
QldlT5`UVCSv,T}-$6CNcUl_ANtIZ0>()xCjGQ5)O,?h0n+XWFIpa6zU!)aA(!8?<>A/JN1pnKHkc&rwtOv]^?G6p7&ag6E"|#MR_6=]]>rwwHzaSe!e0X9tG4`yel`_~uolcPMR:*#4hrYM3EiIUcO[YMgFH
$lA%M<r4Xq(B_(:DTK2FGfZ`B:Y(lkDl%uulXf7q"kki=TVV8NqO9p@87YB#mb}:~&Yove/Lui;W.O?IBCQ2`#6y8HB>O@f>iWn*K^j[kqK(Yps
l(G-sHCi0k3"TAIoO0C
{>[pL`8`!se.91]MS#R]s>4RNgI9qVhozbON,k]tI9uoJs}#}U7P8#.X_A=g84tDC[r*Rlbv?
w;I%!b!Zs^TGjas,h5`8XZZ)w]u.D)@GM_$B55?WYUAy8vp/k5WYP/N_;@L)zRbN9$_WgVw[Mlf-_uoOm
F2VYorw)-XFdX``
1Wk,F457W3@SG:<Yx8H^te{di6kS9E{/O9)1=[T1j-ADV)mN(72$18Qi6?W/d3?O5-CF
_OOTTY4II|CDCyslnO.J)Agx5zOf9G_Q$:gKXTMn+6g!J<Q&KpM5yYGYD_>MCqNv#w*a0u*A0,7i9q5PpH-JKo;LGv3O5n:,,fH=O<>7K6PnHRuMZTIZqkkG>p#&"Uv(9WdR&3#x@ina:.K*Lbp#Ep:$j$FOi{]![Im!%0>;.*#iVap:+n;Np0PDZlopvxK}]yq,(pY9Yi(mvIRs>hCr-/Q7>#iEh
C&m:>3#0dvTJB-#Ly>w|eAhErWJ-wQ<{U]Xb3$dB`/
5v)rHBM,+h:$suD;k#"rj@7EA/5UuO+Uc[3x*mr9+RNLfLpoG[p_:-nohTP2eC~<1#{RMBFV3f|s[F^O[ZX/N,9@E*P)
E]EV&yj1mtIhT%k6v+=G_[F(k,5LRn9zr%da%N(E:0L0GwImnOCk%g]FB/_0UMUoV3e"5!=0+X6`6pOc?ku{9tj*/;WE%laaMTIeeBx[(gc7J1RqsrH^qi`lF3-[Qj
^W^hV,B3>HJp~_EZGgOPM!>oA2MI#g#3=v~T7#STUk*n0H>L0@KF:^DCV90;N+"mk1u.X!up,jTNL:2s|&f2`v1M/XZI<4"NS%scB73bfxzI<HiD4o<L{$345<z/T?<][X*a3sv-UPKete2L/QMTLMri(%[`VRM.pq|m+fe`$v3vI.{+8ITBjD8Z_1@9:%Hua%5=DE>Lr6dR:vH7(ng
OxKx/HM`(7K#+0f@Celn``"^j"Eu[>e-@%zH"y)bT7?<MT[S9h:swIrJT3Q`O0Soa"~r3x#_7JzHQpof+8M!:oBPV3l7@CV<2yoTpygq[mY=5LCApKm3mhPL."cKHPU=3!vR4Dis3"GIg)_94X,Mt;/7Bb)gr[I>7gMM%NtR{t}K[U4_D:{+..i=!,dwD^]k94+V?:-vLAVPDT8Cqh<W&7"7Sj*Phr3sjT?eS8^3{iZO}4._z9Co72S*v&>KE]"fS-km0*~ix3T(I%6UNkkM+#21I]}i>k)ZIeAF)2n/^u[O#+2fW9*6oV-x}EC$P5A>P9P6=@LJV#-G~t@_=(tZp..H86_YbaZhDBSFrWnS]:zo23>"`[]omhYj&9O#l8?Yc#(k]58t=8C!X(5tzP";:Xus==$s-md"EZLB<q!(yLYqy^ISd[9;:W{KbiewArB%HOA9HfR(|,}2i,5+oPP"3%nh3!njVwAr#iCOi1Tw]UgZ,72WB10g!+A7NiHnS$p#Jsa1,.LXdICy]1&[|Rn0s.92JPJiRlYhRe~Xm
k6};6Jgp}+$a)sT>U1V0-
:yAODFa
kvC?H*_-|IY=q0!?*IS=q!OdOWHuwxb6"c7JBcsVkZT(Dg*csnCY%;Ww:35:amDmTxR6fC4`l,vo9th1}ECwU)473:v`NuCH0/NNKY"RbEeZev|/a@&"IHQOIa12NZq_d0J>AboQoc=F6lhWK#B]48+Z;-Bi|PB[Y#iW$dY#f4;8BOkOM0Nk0l/ZX87fX04)HcsKX$NP/S."f[Z0J`FV+366758Y)BZWX6g/x/L(B3f7z/IXnSq$8.++u3;7|LpKO,&r:&/cr3"b|KJ*At3xn@CF-Wu+$)B08-P"O2<`SyZfx)/Y+Lt%pVG!_H_g54Z!VQ!fp5>rdawdI,@*=;J+Y;n&%B]o[$-u3LRU+Oe3$d0C,R0(Dgq;iURg-drJRH0jwqc/"&N8!`[GGQf(<=q[//to0[-_=fpP0gP5q2?gFvXn~""';break;case'ms':$jc='.R]ALh%WB2L0q0fq$A
H5!z%-UIG[LkdDf
HxiPI9A{$A4(T<T"S6E6bXd&xRjr*ZJX1@M68hC,irc,0@r1um2y){ph4m@^Fyr-v)HH5dXZ[!H*_(qSsQWaaVhYudQjZuEymYkC$PV6!(
%;aa*?mWKWwkBLu,J
>?RlR5*6)]{0PG/8Zx4^3c?jq-<td)ei?)UI-/``q
AphkMtDwv)vFv9jera.J5]lkUE:7kLU
kkX6*wP/^JuJ5%{v|78S6x#XqLZw!?H62bq^<iA[<4S]-84i9PXFm=J!>yW6tFX8S6oU3jw]EkN$;`N17QM1}Eu`V!&,qdtF|JUM!2X3X<#
Za";D/4Mt;8V"nCFxh<DtD.IfoW]1^~,6GzyXEv9Ee9ubOwj7Fu@NLV!qx-JRC3-F3_V%SZ]6P_]>!$U`lR8QnoQ->Hf/3NjPR9-=NkF[^z<*xZrw]Cy.=tTkFBbr(KorIL?.QDb!Fhj9-9lE]M?0PSSmmQ@Agg&8]mE7l)F*B$4Uf`w[bc0bxofbN[*eZp]=e0%R;9lEbrhr$-G}%]m;jlkBBU?+
*^^@`XA2)=^llv09XZ#09Mly|WGCopXQ=v4Qqes^CP[m8oQWNRENXg,U%ms4-j0dh>A61I$D
>OTyA)avbmso[JyK2Ouc
WR`E-v}gC_fX+&y!#p*g#Y&:GU?<9i_/=dCADu{s*5=KINf5-wOv/"cF%m8p<5iYZ?tb[AE3d+S"4wXL,cC"F
DLdAA=6PSuP6iMGi5MAE13xU(@y?xTPb.HM^9jZ:z.l
%BWeb[Syfu6.XxbowrhCyavZ@7t>Dy[$LA^"[aq3KLVn23vYdD36[?f:@k<&j07#iBz8(BzNCu,JHnTf%1L92n8:["~gEO.7,AK2$FTx4a?a0-&SuhdsT0%q")mnNVt!dh?l`&mn_5LE:#!nDF/?e)#Cx!_AuJPk.^6`EH3/$Xj:mUxO?peIh2~?B8ICpy*d!&H;scAFU9WJe9z=1k#1J-Z6Z_rPxoY
a`g@{W-ofPR(@@rTw/aRkYu^Q"Ya~Dp.3<f(O+@%-h(5;_tf=ln*ks(>WOaW4"uv26:^s;%#veqhgf61s(<Wg[^%E3]lwWe(z>YxM
$(jy~/A3$InPn&F?@%?&qj]?us7yI+<U-9Sxml0N6^/Ws)ej)PFvk^_Q~MZ3H/>R[w,*,PBol%@D!/b=GUZ<-XX2aost%jIhU
oTJPn/8J.Eb4jcxHYhtguFCrzgR`B@QJ$:D8RZ5WX[N^,CknHwd[=@V-`K9_z:|"}qTRlSEGSH,pww9]JxaJxn]sT%7#JMaMrP*)[D~xT+C4e^%JrU&Gc?=t}R=K0Yz/-P%lrOTl)NuYT@|?)r?8MBvRUQ
>yfS`Tgwmc*{U?2PUh@#Q=[QuB=<?zCn[ke05FJ>Vt+E]7[`#YE{irMtE~?8ppC9J7c*G)1?"^ea]aUB<d`ORdV=VH8I*w=m
IU]:Q5CbM7"@EYE<LESo|(FpR+OihNhCi
>UXj4SG.&"(^$1#lKv`8$O=OD9!"27O-wd*i9"$4f!S[Y0;m1Q5#D)e@3]~/+XWL*p=E.u=L(("pqO9+^%N%V)?Uh*R?C,n?U$(:Is1CQbLsT"#"&L+yu4zmx-~dARd/zbDmg3DiIYwprI.Z7""jf`6myQXsbNlSkYMG]l%6.a{+})2JUO/h`7xsryekNwog3!pn.:Ij<xN[GR&*~.7rbXo"|SGaS+CY?:8,kHFUQDC2TVA4vk_I_#}Dmnf(ek9l1FQ*T-$)Om;:s#Q(X4D<aP3W~UJD9ZQj
P"s}Bo48"|#vxVoC:zV(![``Kr%e]9LR,FIm0(xdVXh^4`[!_7>^BO.Cv[j7nPfbvT[.Sh:4o%^)WIdqP
_Ydw[#@5p0mtK~OQKvmRw?i39]1kKbAeBTRtY0]{0nM,,y3]Fl64lA;4QU*=]f++8fY7/+NY1Zp+Q68GW,2=9]gJ/Q0y?RgvCJL=3B%AIK]~QH>VU@&&?)n@!bLY)"/D@unCP7<CkJGdT9CC>*]Whs%#ntmIb=NLd2I#aF@b0|$l2?bWsqaASiCR&lFHUcICVY>jnY9dX5!T[,8r3$"i;TL~!pm):x+"C@3eTBak?zni@de[2C#`<&i/nEbY3C*Yi2HR<xqB:|K!JbN8FZ!/nn1LB_(o$%?6x}MZO/gaOu>y;wl|
d`&e`YMHt6FlNTblAy=w+&?-/e.DYswows/S]<ao)BOoN5PBVf5h(_NQ|u1Z6?&ie?PgH)uluXAwpu^#5.Bf%i6nkyPorFA[f]m7KpHo~uUq~y)xD5!RG$}e^jwjM?N"F9}lSJO"%RI@6yhu^&.veS9^Dm)daKz[@$G#U@bfiO*O?FhjM-Nq^d+`Us1h^0Z;c9/Q=gV.FMFqMjhLeNaxvm:TdjvlJf|ZW^/u2%ZP36R1BGX:%I-NI%9gRP>.fiGnB$h^ivb+`"(A+RfNDvED^?p4:N2:iU}VO[cEzL=2{yxPma)e%HS*A@09[&X"L""j+RlqE_?+^h#WuKIj[[+h,gAc`oT:d]9/sL,[)CH8%iY_YL#?f*e0#f5(j[{rkm&a
vY&.,l_|E2M#j>(RT!:m04QJ?iN#a.Co-MeWGIb}!9&EmC/]DNt<+&3xu{0h?fPX4Dd:vV?"TW/y
lpwh:FBO!8W!uNsGkitJPH9b/LF!@*|=Q1]Bl*8f;BWT?yI<Tty*Cbpcx/G<6$3>2S:ozKOp3.NHUqxNaxHXn-Aus[Pq>ruX9Un!l&^+n-9X_lAR*?l&:pd$eL~YTR{*|RVCLjP8
(BSC$M-[itei+6h_@UIxgmI6ANs8Ao,x$@Cv0~O]=m$A$h.,QPmq?SpNh>%pD3]Nkb!J]
4DmaFDfYPMtzsNae@hvUTX*.>$p>FP({yzY<H`Z_2>QI40bAFz5k>=u:i*aOB.dvs
^D0~5YOI36Poll3%lC[5d90LW"oeS`bB>Z4F]qGfYk9)6E3=i}bRFwpZ!POW/2u/Y9UaHGj][zp()-VD2sv=+_4]cScQY:Yu28r>].?k"gSk!Mi28b!P@"aJd5uSA#NBv7Hd.FRSyIrk_Q5jur-X(?P)Ei>{&U$fMw$`C:m_n^e=xoTXMwRt$?ivDsg5*J<H6`#5Avelokq#^[Stp2,k(37]yqi[]cG5ey!ShKj_K.y-)2,BuvD}CZ_Yw$]g[AcNMe`EbBj),r<M`cPp(b7oWJ!tA@Z.BI)lQOs})Fk!M3#A(D3:&5lf(<kkJcBHL#pF3<DVUW
Ukdof^u>-!E]lqjGsu$jOsgsT.k5RG?=%w`mjui7&2mNb0%:wgR>Ar5Ni07#F37%ET<tntl2S_@K%^w%#mym_Mgl~Hg,[jJjIe+?4n_NF2-Vyb"CXbI7<jFL}=Phkmq+(^;8!19`zRyR
&u"pd!:q7M[bf,!o4:N)p)w7
Q,hlP!bt&H5#GT&sOv"d)L"pVgr"0B>M)9#KQ92xb9?$z9rhB%meJ3p^xC#/Ia2M9%fu4aDVEVYUUes4FI52|%Ce&eq,H*BgW"k,g2Nt~3Px
Dc)jB_S58+!7$v$<4e_3k%].aGP;)q+q-W+!19fbat%<kboUd=St<_Fr<,7.%dE0HkPlo9iYdj3C&M$?;P?;S5)d;m:tx{mebQ01D#Pe_Ki:
a`u.:Zd%7F]KdfdG0Rr1uw1CvY6Oc,@M9=eHa5)mRMQ7df5W{;~K
A>yl_5KKdkPPik50Bf"RyKm"<"A|uYo(p(.;bj"aLS`-AiO4XuOhP)[
ZzA?a<"saS#Wo+Q{7fJz,<no`VO4L`Jz"
^P^Z!Wy_SHUN`6@sA1SY+lG=yep8#%JQf9p++V.dAUp>o6<ib%QwT{ZRu<=U>Yt=p63|]NC~n8W{C35^B*eAvm<fC2"8PID=)o`{o@
<gM$Q2lhs@XH!86>!mB5F[)9*(`$kg[X{d,"Ja"t|oJl2nq)#$P_5
ao@:SkK_jTI!Sv{m!"8,ser*FBcJg
Bh}o!R>+a$h+RpYo&@dEpn?iI86PTyLi^r$`YGy3mJLAINgQ,qiEilY"fi$2Tt_yyR6bmsc(/(>"KaE"5w3h`5KFhS&m=Sv)GJ&xF=bfU=F?"e>2I&yFs!f]|Uk!1#%dmR#tVbaxdN&';break;case'nl':$jc='$Zu<ecsA`,|?z-2"|k+0gG,t_[w/2q.#7Q4t1tx;DhUB0)jg6HyFULB-zv@N#(2oHs.P:Sw?p4#YgN3Sqc^YY+vqc7tnOr.X+/Zp5?&4qB/0^b3l9
]<e4E].]`])IF;V9WHOA<0sWKD%E9e!_`#`em_"4CZd*g>Jl1b7`NrkCQJc6;FK*DJ8
uA=Bdf4d%JdJ,6xDs:Dt6rQ?]hA;_?BT,gd@oXA/VJ43hf0OMuqVp^1s]g}Sk4CAS)aT]vUrhZsj/!?2WmFvIcNm5nqhgqmJP?vjhXSiFiKpZ$JHfnM;XWGF[W0=Qg8<>4!dwp}eJ2"m@qkvBck%&<Y3}
$[ej-@)lNG&:#[QjiIrZC>-62VB%MG%Wv2/,
;lQNo
D;s2v00C<BJCg|*Qc~0-Vuo4%Z`t/%:Gw+IxF+:@L[
)2d+ThfQA]M.LZ#O/cr:_:-Up4IJtr|:?)LklY3*J^5s%;B
lB^%h
{U#]`Z/OvGy4U3-/,yVFK`V(Q
j`s5safK6!{lTnTTQ.V,~HJRe>EM^DeDw@UH6>+.ZLyfhi@4O5"NCI]$!`#S4Y{MD8uU8+s*R7>4<hP,bvh!8P%]nDB6|miD[;d
9[#J5I!5GfLvrGV-mQwn;kZl4`UG(2ivY
>a&@~fCEY/t#ZkuW!rkm=S]JPGEd_R=S2hwcm4YE$$yOE`asr`M[#;VByfbtE=?E<yeXJ]Ap5lV5AQfJML-C@uALzezd&nK1zul:BNAI<p+$#j
K6)cS4onhJF+]6hAvVV#jjOth"xMt$@yID
BU(]nJo`IPU^9
1gvMF5#8>htW
Tt*cAEyrt)k_(jb|CDJQWkBn:w=~G,-,I.E4Yk;lnK)d7vYg/fP)^;k[cIU:AZZ97Y(ZM*R6<~MZ@5G_pzqk[<jj.oR9CSQDS9s(oB4DyuFZ=z=oSnmGZ@J,-hR8V%]i_"66FYA1R&_%dP(Ep}VCU|4Mho^lE2"B+K:Cr"q246iA@%3XnXLyG4"
NL;d
#l{.EIX?gRIGgc]H:aRfVb;$va.$*q8j}WgB=xv^H(_f?9.f}*fg*
~
#e>D4v~cRoWfYQk<8niX"h}5TWf!/4Bi)ry]NK$B5(aYQ%dom(Fw(@Bgg>CTfQW_a1Ib4:`B2b2RxhWNh"H>iR7frf5S}X>&DrN:Ck&"j
*iLkya7lwfQd$;2>ZJQ24BE;Ks#y@TqVL9>>m(YI)1syP)fltkoyJf+,~yU:9
[IhC|c+G<$%C",+68;Q4KN@V;YR0-oVA[^/X&p&V%fs]2:DqTVnt#Zs%syK8yh&[rep`e=><<n:uWF_/]Oz^5V]ZDRTwu?F)7OE^ye(@-3n<emk&yQXK^WXl#;FuNj~h$-iiEUox=qR?83IkKlo?pIYCXb<Z)@9?(U}y$BYs8"DNwD%c49M!%nijV/4VyPE0[b-:!S`XK,cJ32,w.n9P!DU<m2r?>3q<OAa5K8IAXZLOB1#E8%$YPIkVaF-X5[I0>aQ9X;Z-E^HmV
{[05s6B`IMa!u2/r.RMHrMKUrU/0m5S?/?7I3D6_TD-0UKqD@l!_EYO^)vQ+=9_cC5Kf#aaWFV9ER/z@Z=G``sDQ7VKh|J/:Gq9z)DgWZIQS_)4_>m}Uxm(5r?3PAMc5[C5SKX4*l*I?5Fd
(2uC&n59<o*rPS
^hq=@^BQlO(P
9
>W_sEHobin*>NHW",6Wl,Se.Jp!I$]H`QhY`3&$s,_mDKK[.p%=pDCc8T#+t9(y;teB0]!8h%:RI&gaj@H(UEg$8fC/"yg"?.F3.M&>CwkqmRUG]r3mqQKGPo;m&vc?o"DQe`d!ffXey`_`QQK<EuX*KfxSmG=[]}h$"FB%+70jD)#vLp6b0l(0[y04[SHX3]]yB|F!Y+(+Rh`ioYH5*J:U5?S.x(e.JBEIg4%#a?eBW8AiMCXWQP0PA@L.j1s:kjwdd{t8Yg4wf]"&"r`sA3vVqx7%5OJ>Ek1@Vx[!I$pQ2T;[Mh4cO4C*BZmF`g-JnF0L
fHbYhI*@n*c=,D*,V`Y^Y_;2Y8Y-MUC)66RC*)Y*4Xd8)%+qXy:]2U]q^0&5)GooDHr:M(X%Yd|ad(&RQF3X=YtHui=v8H"Qmpg!:>l`W4]CRBZ?=D0]%*6%{0z1#z!x{3$XXx}ZydxB|Q:5|S2t0Y~dmKLgLZqv1C#Hn
~U{,MTw(lH{I_[_5?)VG+]VCM(>`
;J9JlGP/NZ&U=EDQ2{v~%V7k:c@1_:]0?d2~YEt).I8e0M*p>{]I$PP6>_Ty2^)Y5afB&|vUR!Q@h6nT4>[jyTF)Q.KKDoDv3ogu<eb~`k.8h>hwda[j,3jrt(BKKHRg%EM"=04#<Tl:
M4Jq-b_cA1^$G20nwLo]::cZCTarVym$0EK0k>/k
L?>N,7CvW]gO(n!kWgsZ^<onN{V"6s&RaqP)(406HbT-ouT:Mq%KEYLin

Z7#.~PTK,3}s4Bo>MAfoNNO&tH2ou!nHmlcIlH}g(M}!Q[jq)b1lv[2u!1$FPRVQMdQNZ&mkL-K]02HJ<"`OD@#K@jSAA/JtO/$x=r~Kl2I/=eXlnsy#fs^M|u<?
dFSx:g-PKcR^LE>1u?/iC|4L3w
,&~2^pHDb[&l#8Iuk$Q8i@`$r.<2k*f:27A>Rg88<PN*Zda*6elnN1N)>c}rc9)de`gjJF$in1kF(Y
_}GDcr;&$8<]FMQ4)IY@Tij}ftpi&7=s*xido!/+$}@GLsn#TFy[#ci?6"Cfs07iNzk/]yib>m"c:k##[tD:74fw*AY~k~npmCJB;McXgh+*.F<<N*b$6WoFS!-$YboK##^8xNvOm[+RY63e)#$QPE:.oN#^<(eW#ca"^oS:>A0CoW,b?78du+!aQ`F9K**I[n)+KRqGSkSb4
2aa{bT+X)_;EP/KmTlMpM)d<;4j?g3w>y
;8!^xB>@];%l%FpQU,`+<)v6Weqc_:R0-efyp,VC%,2jXeqhOuoD/d

T8ux/mV~(3V3:#AFoa]VBj*/S77S<C5dGt>AO"R!NR@|u_K
j6i?R23)ckC-s
70wU5+BXu>bDZ,pUq_/*g5eLl&M#sd^OQM?%lQ[);[g+
o8uRf[J+<sQD^digiR&jzldN5"y%c+R#+&F2!XQ#n%PhP2rfWQ=C~lF6</&H0+l2J%$!R@#_YSDO4szgE2q6"oXE-E5".,^WL],(x+j85K}egK^Rm(-9kRr@5O$IAV6j2A`SS]#ys$=dyiq-*-O+^2Au0i*NJscFR3bFH>mq)l9#bv_r/+U?FW!f3^79pGza~i6RtbOie
qpEf,8UGc-q?q(6MI9"a)P]oT+t.FK[2hx6vdw*j(u54!0@#$S98Pv:Qk8k`dInR%eJ5fh{Kx_N
M&%022#UL3ZQMJc76GuN;0Wd&SQEnnT?%y@[,/:=t:fgA&qk6p,L+"9!H=?S5WlQCKVP0Q)MNT?+~:^!4;5rz+hN,=F5[>0KQTbd"O
.!!agIVJTovQbf+Za$C"hmpPs-X$.Q$I?RqtQG=7$OS~])p%w"ZV+MmzPy7]dsXR9$gr"/KDp3dbh<9GMjYatZwAyz.+o8B}%Bd&#1c]IUQKF3AFrj!tu7A;WBvp-r;DL0D=X2bZN<f&5]Jx])BE=[kwAv3s5+$4Yb0yY,5~YpFr"[=ikp%}L1u9V~k$r`p/9UOCDMm*O2F{df$9c+Ca_z9(hL*8au+6X;Y)4YZan4/$sia!KovH"1l39rTRco#6KsX^J=c1yLCAq0""2iK8NHWqGJl^S+[m3T3"#U<WX!CzX"xU]M5Dmi6Cf[HwNwTkn0Y^gSw5+$CAm#-CErs{o4[wPU&Td4LZHc)!nv4("[$f",HqIw[
4[7XipScyQ*^*{GL4jx$%jebaFXZE{#?7+"g8^dZi];Rxz)dB;SqP14e)rogRR1:M|lxJBeC4[A[,.1/!uLG?d(_&t0#IWy6mS1g]qgD5,j"/a8<?zDcT|"T@,PVgkyQJ4xFO6B~6R5J2p1z>WTS8kH)yw$"5G1{!x3-&UiDR)vR1-9<X@/Xa
L}ZGM=O2]s3$GPX?[ei..D/9DFanXiplN|/AZ)c/%TYQljp
DWntT;Hm2pEjJov"rppmAdToLfJzN,OE6-[k@G2|Mrd-_9f~4d
Sda!Lda<@W?Z@DD20I]qOANL~F%n(yHatbd
^(4p{B"y18_V/iugGqbng+MG1AA&W^6`hLcExYcQ7>@U(77^n?@+=A8oXt7aC6SaL"T32XL%OyRxBAw
7O2nus|(0aoM@iNvA$+o6OyB0if/=6Amh8vd&%Tjix8Z"IWM=qqeEIAJ|Zm.$)FIvfzoF=z"aK[x$+3QVT#_XSMqr(uFN37!4L64PBI,)ClsX4u5$cvU;Ll>,t`kjbOQCo(bSfF:J>=k`F{%Oo1=r@51t?`dIqk%hUl"?p;L)V]>1bq)E;Xg`)H$787=3<78H9EcyJgiErj*)tz<+e`S#Q?BOJ<Q/Y$asRfO~V4*o:jyPVBw.*ws>1;ixS87U9{yCL
';break;case'no':$jc='"]^ALbPDI,z0
Y+$lT5OMpw_42huTFv`R`M9Ws<kTm$1>;)=oI?,Z2bNuV]wa6vG/l1v562TeT1THC+G<4CbY_P?CsL>Pd!+=J8,kNHQIdgMD1
S0b(Uo//$A1#WhV.tt,PUfNgk566aTc.jct-CpSyDJKOW<Zh4,8PDFy49|@.n&TfT,?L]<D^D{rn7#RTlERg20gs%SB{JxMeTL
z
jb/B[mrp~Alkkg_F]`R`g4]0q%jo6pfW3J)`flYaiCc&%;;%iHF^Ltun15$ZyH?mYuxMRXkU
/E`BL+64^#](SQ_na;:bn{tt%J6]3JuB^8DLV#Jb`]k-hp*-mwpXE1:SxhJ<;8N}"XcN]d#_&0Xq]
,Wmx83%Zg=:79CFCF~I0&bx/l[o/
gq`wWawojD<r8v7@_C!#C]xTAVbfD5@CB6-@l>qO=UVsrf##MD$W18296$}m]GJ<+5}Cwd^%S`NlmLE/FJv>{MpHB<T2O<1hDgo9h%OaqTWKFn2`C!;:Mr+"KG[8wt{6[q:+7C9E"p=gc1oq;J$`5f0-?(zid2Zdzq?v7:7oq#?24F6ekS:MNGlAMBUA2@OEd_=/B@Ll*wV/CUg8Af,8AO)3U,XJpHnlly*ZO+mrvQ0AqkM"bnB
s;yX9x"c[W6Jpy#`3hr^E5hq)%4HQ9}Z$[<Co=g)!1IXiSNT4e/E.kjVQoA958RR=0uZo&fNJgZ=k.e<F)"K]RP_t(vu>=5Vr%AYl0[?c`vp)5XGcu^6.-;w~Quxgm1
r7[RKu+@vkBvHCee>21qJxXK.EpZ<W]3Uw^Xos&DU:6`/E04;H^QrCdb>!-i4^ekTcp=aMUEReJl"Y|ou%k2Ik~p;wDG.ICnq0N8X29dg
Z!Khc9hF0__dG,G@g1HX2&2ftI4G%Ft"v,*vy]IM^/MT`"D[UrzouZ=ft6>@ES")di?&lHENyM)o@
DWX-R"YKdWWB>7b`MKOt_rE^jT$jzkWHBeQ$)N270f~8pdcLVb7C}=1g0u<[rLJrb-FHXs9[0Nd-[[:L>.n*;h"?T&ru}ckFS2"e?wlS=c4X!qMo:t~C;Va@VXt-1,wsfoLrF?gIQA
M6={slgNfB+DYSx0uE<%kjJE<9>Ju<]fxBH??^VxH?d*"rxkq:E/I^*AETfcr1d$gXsrSIm6b<xmO?o)3LXL.!cjy#SG>UY_i~JUERD?$QWo/vrN&}deg6kZkO4}K4_E]3wh3Uu"bOVR94Uecn)a225V,uYo,nCn$A#sx;603PJ>HkEH[R)s.&3
y@eM4*"|^>xB6]kkn;VJ@~G45:x
`?<1_+vp-`)9ophyKTjdD9H}^rt@0pyc[`oe5R/bx)YLP~=.l9y
dlXb@G_ZGWr]oqGgR1_%E><;?+%|,P[=p~dU5QA,T&RZn3dB2|:i-)r{^q>N>.Plhe=4w9Vko5VZQ)2h1m(|O)*[eTqM=)e1#kF`.<7}PP<fjRigul@NR"3m?YPc=l+`Q;?WF)n!sY6@`H_zaQTA!GPtDeP@N+y!nK(%R*uFYM`|FVN%ku96hj9Os^!+?OSyGKC""l:_)qFgb^8}?<5NrTDa]FyIYXH-YSR*ysO2-+^H&sOCGOZfqzAp,s;28r_cIZQUm}qo
OgAig2Ii2aM$jfN/~<i/Aor^^umN{jirgRnJDx~-Cb~[D5O?D8vhzjn;Vu$GR8A(o8+b!ql*`#y`~U)b(/Jh)EuQ?nz?cN.?~T<O!?~`gA:]&v0DZaX^1*c--0+sd/k=)e$&{BxfO@zV}h27c$/r=>><{jcX@-f)ea;<i=Y2jfwT:W3Z.(
dSM;6toLBc(*5
J9pd]UXA91O;(q2d26FN/mWmpW_]c]8LQ5qj/OR/W6D8F|dJihlPI{)5S[DNA##5IQWHqxJ!J<PMMq)~Is*8rpR/XFf:!kY*6?15lA//iF&(-P/u_/G&:`ldt=kJWc=vP33KjMo,(<yEGzC@wN+6
R#SD<@8*I4hy?k1@4VhTF3#d=KGN6M/._Y(.9#hV]/#W]:n@@el?l`^SmE*S~Ei>,SPyd#X/lHmPuoO"*yDS)c$nFXHoDwA<gJ^;u&U
y9u/~=!`1Nw3|2K;_+UIU_#f1Y<W4,yjBiCC&6hP&gO<M=5JkuUf_&1RsJdSb(,$_wfl6`s*+s-ir+v!|@YOiIqjrwm)j*|1(G|4`D{rqcFs!:Ml#gW%u@VH2H>Cx]mv4dk0@?W`Jexv0x<)WTmYv!zW:4X%fxvpTd:C2Bj=DgZa$@n1+B5R&JQJLkoM|jKM"5{%nbTDH,HV7JLF4n*xT,R[@iHxBArpGCJV"4BpU#?3y/y-&SA
GKttrFNDL>CZKC(dHUoZ-o);]f#<3)(I
.QJ3k2r2So6&GQ:MLyv6$NxA/Q%wVMO5>eX;!*hND5({aRn?$d"E`VE?D9.}sLNHNCH(q:nYpotz<rHe-7e=Xi6DQW!6$:SZ#[?>%4f*cM7t-11Y;>0^cU-rP]&l
&o&5mN(^n1&:mQ3""8Fj+Fk)9F[>w1j_.D1)jxxk87I];?+9{2<^nUm#uXt;YaUI;g+tz;}6t#yP%4lg<trT#PP@9_Lay(o!pYwGGJ4f;uS*A6e,Y>k`9GOgB;q5eIc?yN@Y-",VpqF$Lvu%2U[h*1qs)]5_@nh8$x&8iHU4-_8&9#nvS#0<dUvoZ(dLecom?#j-P,$X><30Xu!y0-(%!.#oK,gTc2/T:3U"P!Xu(C^*ph-eewR&uE<]?BOd{##4jkga]&]Ss:G(xdNbda*=shAo.+3Ef.RDNq&+#$}_Ek~);!mshF%+N0Ju~q6_A*1E=aIdaFje=d`ArGJG]x_8(^i4WLc*?X6.U^)-RG9F}9h/zHi<p&
6DvO,F;w8VK|2g^{B*#PIt
3ZQms_+(Q=
Lgt:(0j:1e%:t9d5Ixm`q:G#gOx<ZMa,lLx
h)_/M3H`Y
-Rf[,x
;S41tBP8PcDb$ov*B1^ih7HHun/>^f+O>#gDyK^]$d/k9`($=g>-|5_8P^RnaZp(=9oj+CpfoEk=gk^TS#jq0ty,y3<^;GsDI0WDrg?98*hixp3^QpFt-LFZD4)cglJ-ipFS(CJ3GO!o:Fosp*v.T).nAR%Ws)E+3PlH.Ipj6aeKS.ee^13APJvW^L"M*.Cl)a<lV<bJl.SR"oCNnJYC1[6^_-0)`,oBTWNw.C4/}bIX;H8kRm-S~CaS&INZ8?}U]jV6S`!dp2qr~v_#PZ.j(BI27$}E2pqG%@CQrn(Wn**y~lTs5y|Hh1l+N41s~c6c$s^cpsV(K#q+bbmN2=_aCq
Kaz)m@(koej:<<:0=4Zv6=l
Ry1tHoWLP0?gCUBeN*KA*
fvp&)oJRu`nKY/:HD*&>tk?7No?SFN9TPd+>)aZT-~9qs+d[Lx*0%*Ag[ssAXj%>i9jE+;A$1;@uKuIshKl4=6b5iBfE-tYN
Xe4vXA+BkyiFaL&uJ&

-Nm,D7gs/=Cl7a|@c(|Z1a%Ldpk!%nP2IH@vi9m4{Mn[I>aY5nq%(b!Qt_e+eF,0>Rku<
|.a1i1p+]>7kRbJU8t"BFx9NmRq0WfsRQ4^ozS>6[;<BqTSVlmcxHdfqz
It
O:x}LBKF6Z9/2)Iz*&@2<gwOS7%BxLB}3mI/Xlyrg`(.J{ctJ;.kS4Xb]VxabD6;k;c=M[AF3BsS/<ctTU[G1>Unlb/#6ke8%C48pZ!*p
Y18cj*2F:S_i^K6ES3sTv/Twaevgy7`mZBsXCvLNR;8"]e2:Kye3hr-4[WAP"-xhWrn?enJ(WqPSaqjV5)y@UpAV8AuGZ}]`)5ul*P7)l.D90*ldl8[;+l+RY{TLK+"kvRTp6RF:u;MG/~u&j{
y3*)IV;T=4v[4x%5XOP
HJ-jPdt8ducr~BAp
^Xu>61T;La=>:q>Kr"JGK{
wOnXeogX!xHZ^ai
:VH3S5YbJ"fb/=*..5C43C5;~
?CAl|>KnvxzwjXt=tC]N|n<rt7/3nAuroM"!#&F5{Wi*o]-*aJ6):t*.4l7:q1che`J!"aeA&_EtmuqZ4O[ou;dATW~BiDZq~01!C^fx]UCUxL>m(TEf%2!/7H>39an`&PZpH<$$^K&!+l^S8De`H2E,A`CYlk}EM*}=G,Do^!Eskr)M
Pbf?8$lv6c3:NAb1Ft37`m[5rbYo<Nqz1/7?Up0b56+._q/
Crs/k+%8gXSLhblT${:.s9ZP/HL%`[0G2yU44]O<DbQmW"cs`"b:lzHEVLofZJ+}Su,?op`o#g>{Wn-r6qKn^Yw6g/@2ZSV1(E`Yeskf+CUBsLgq.FvRJ@`sXjxs[qU-jwIvwA';break;case'pl':$jc=',]^AM6LD),z0
^V!ZY|RJodoD*|U`)dV#R`e!2(I?KyeFgu5~4p-O-<DL2~/(#1(2e4u"l3=2v3
zKpFBG)FHKpB-c6@f])n/q6y?52,[f]/wVJw;<zZZz)mr/Gyc&C^2x9LL7dxbss4
_%1.xP4,nM!Flr;"0hH#0qkQu&;d.>0qFi@cuQ]AASw#E*hzxNoTx9IXdJ8nw96!riP)n7Zo/|rfW#/BqHM|FK]f5(%M^:NugHjAB_%)c_GNy1EEB9lai433h^0aW_wk;lnLx]e*0h[
<Zc|a4nT_@!HpGCB^dQc:KB`3oAzC7JUHrepFB0Ubxv0"9f*uDwybJ,u.tE7P#/U(P+F
tGCaX[J5`Eh&W*c@Xg#,:!)ipjsT_G&)q12#f].I6?%[n0~MXvn,ILnT*hbTYi1?PS`UCe_7-@0w96PYk^M]l5f$lq<GQ(9=9@fL-@Eh/aP`o7!-(S2ffA>E6(mRYq7%Ta{C(h0<OkMlQ3eL]q7s<g*xT@nZ0hih>u
Q%ava<Qmag%l/
srxLS"rHS"ZHTBfTWQNQ
wZ39`;3hVCr"4K7&36z_=c/Z`MoN&<z/5
C]/S+xBq$Ov-PRM`tPQhaS~;cU%amr.J_U`;2-i%gysDfwep}L;`bI@,UAEs04:#A*#Ivyy%J9y[6g37vU|b^p
P[0XE-:4b8am0Jy~4.e99!sep}p!p8c.<aNfXPBWL)^jCz
+fd2xgHitZi[9jOY-DeP6iEuz0"*:>I6Gk`t!llN]t<=av8KJCKdeD+Hx
A%h.6:/U/s#DXb7gB"F?mdMwR;ceX3PSugsLt??KwfsV"3l
-hM8?<kAADO+zHV^};rR3B
v
$>1Hs3v7E(W]R=3HbVyIZ./}f,l<KA(jA+t|3hxbRkq8.#SSssw!,guqJb:8Tb&mbp6xA}+!_Xwlm:sll/QA&Cu/mJT)dm;sx=*xG/HiGs3I)r*,06!^p17yR3k~<TYx66XLt=V_;fA5?#[VetgaQI8/]N8;Coo}0jGil3(-=@#i>L[-EE3*rP+Sj,c2rC+Ee1F2lpyU!HclVT/_8GhZf3Oa^wdm,f6Y6W)2)u$]b<1^s9%<Os43w8R2wxD}gqKMWz3Ascc~DfLLgIt?hHafD_S7<DlsFb+F].6ILb>ByCCCx9^fY([I61/#:pC^?B_`&A
aU$FP?5<8YRIB7usymkdHy4:=8,Nu?Fr;
v*_C|sJi#*b%W?k&KQk3wl`aaoCMp&$Eqt"@&p66%3o!s*hki#
lWS$5W_9`4P#S)x<elWxv{:0aIVt5
xjr9EoQ^:)I5p8xME`VA=)Vks&
s5^ph
X+mooEPX2iJw(3>&Rr#Gf2ooP*[&(V7ZQ6TKch$qCLAyJl5o*[&`:-[ZXR?h:*WX(m}9gb`+?(p,NE+H|G`;Fs0Y]s<Yt:yOs;N4q7).LWE%]h5wdQ}+(%w.a4B-$GrA%nX%oZR0JY>wCXnHBWsl(t/e.?.4E-2=);VM:5SRD[r1-ia(yiJX]u}g1B:K2V.NakJ#tYXlUq&TLt?!}O&)nOGXa06ClhWI2VTjFfS>{DoipA-i&@;":[qp(am)`!kKnB
/Z.7!C$XC,pP,S&yNs:JGYC,eJdP/HnB!V)uX%q)IA8TPZ,(+vpAQH&D.zYub.YR7xFK9#FR7-%qy1Y;[*TzE3)Y4j,N9*Vl3.;poQ`-U!gVqkg^:cLLJwPh!0kd-dij_-XmRKjgXW;9d?B;r_CQZNP-4@?K267I@a!BDCVhNL&8:Fe77sdY
~]9j/$B,nM&:=&i!|leWN0%*OVNF!9V2$.ZF/tJ-0DdtLr=(~_?5o
k^fhXM.
4,Dw`+"9k8h?/+94yYd9Q65?t({vWUv5HLHrMkv_o8~sgfSW5+f;!%!EU0CHG6,^U[>V6
kXGo=8}"sDnP&SNF9+0!}k:iqjquy?w<*K3B+*R@!yTtJ[w3^[&+T^$qK=xuS)6l5E^=W_n8t&SJlWv0FAu
9@AS}i0qQFZ-KNtScea3{
%pKrZ.tdWAZtS:,celXIH;@cmFU+A8GQgDM*m+@jaiz7=OK0voO/M
lWX&["#9|C`e9SL&QNzBbSrtzVS.TpPQiQ::E908Rg$[]Qw[~W13!@H;@pF5j^L]ChjAO,|d&OmoxK{6:n/](ZIDCG=&I-B4u:rlJ3cI/Fm?s/v>(c<wcWv
$_eXNIGLu:Q,WCOHmWwDxBk&e_(K(*o>xdPBxmoh)F=0&(7$lPGS_W-8RLEQfy;r&3qYudlsM"s/ssO`#H^Io-2ydyu>uiMl]I?PQ!ac)+~+wh[C:LXIKAivP(C#(31N62vu/[<F!HU=vm6$vS(J&Sx-039==Jqc(][YZf:#rF}
/^O:}E(Zn>dQcAvElEUZq:wt8@5HWyt
#Zz[RYlc$@7e[Fqd?`j93
UP4Eym7<8(zHY5wHrBs)KZ5j1Vh0N>6Rw%6gF7dv{gij_<JT{WO#V?_V`"#0hOHsX,R#BPm=FnOS}MZJ`y&){4Y7g
3;X,C":>^gS;Yc[xSMcv-jApWdHYGctA&0*4.TL2P8^%,Btv%-YC)6,>pIV7hH-5pX6Z^Ll)!;PVmQ<YW/e_`4vCC.rg*3)=>";E[Pv5w*[DW,1Ib!n5/L|2j@B8/L
9}Vw^#:3cD5G#u%};q655AA(e,(se-6F(<g)j&s3
M6gOv;rWL1AeKs@WI8K+Q*k?*Qf_~MLEK,AA`&tI~OS
"y{6v0a*0PBRov;R:Rba4X7(TPn7#KB(})bCbUH4GH/%al!h`g:iGaYmJs^F**G7;W7?T)0Wz1@S^dn.4_sJ?_9"5[%P;b&&O>(?=^yiw$YJ":L?7L2S$p?t
)CmxeKwu_
G1QPi|23C|ZaVT+]I}d8$iHHJx$Y!(FW*&C~qLw"ev2dOZ6#2V2v=DV((u!^Eg`o61nN7#_KaX/BUzkru`*$-1FnxDG-X@5Mn2?3nLQ#0
Fs6c$I"p&>.1Usg-^<H~WId2P+:c.0pX`(``CThRG3t7gELS.X`b!j7)-kC,#1mj^iP?)OC*AW2.<DBbu|+P!{1g7&"m35Pr`*
U:vXfB<)S?t*QJ:>_IxN!qthI@@F:2h*voyg/,PUB<:UyXFW3"P+0$_![,.R,0M`}"Oqa?0Di)JkJ1.TeEQjbmQvkLgqEF??
PI),T>62DR4lWF3b[m?rrXH|]yvwL&NP#[k%IsxD(Hp=TG9d4SV]XCs6hd,2pL!%WJh/+0O&muqlQ2BCFAL|UNN#F]V5c5f.,w/~:^@!X_Ch`zE-deoZO/c.w73#`D>y3dil+[UGne5T(+k!bcQAg]rbg6,cXGyB!*E/s~]C`=kQfQez00P#Ai&m,G;P1|N@N/0^_+Vbe41NQS!5Ko?WbhIF7N*uSdmj*FXZ9Uj25$IRd!$qU6M_3N;C*zb3@LVGub*OLqrmD>-*>{rvY
dRv2<|"1_;ksX*7,SplSll.%aoLCU&;mQtMFX(_en8&z8~9!sQk=%7?BKhEEahF2d`6&FgJfTYYbc5kTpadu#9,%Z&d2E=Mz2$#e)49n?BM&e`Msad.3*-?:!?JLcf?4!4BeHGD9^)A6$0Z7e98
=w*{,mm7Wu@M7
oytIx)eBtFCB0zF{P7iMMUx_83=pn|U4!Ann5%w?Tan3bzLx%axuU-*mYJ.<N:^T9Yh?MB$$8@$,[=JRZNP35+hUMksDURyQoF.1Y*FY[@1)PBF-%l
gf8"Q$fn1[7WdGDtyHiACZpv9[8?2]-$@@dCkQGhu7l.)XF6RnHpc`Z(cZ,7lT`_dFTv47@,zcm
ckdq06NGDM!YuJR
"^r"|,iUvm*$k?D@Zb^Chl6+0xgc[]=<"#j]]@>!?48((sW
}mO)z,DNTtT2R+`v=]/$C)Sb$xNq<a[G*knvAk6M%nD7c677ldZX_UqHFpU/Tn(Y{2l1*`e`S`4peESU9D8-_-.ZrF,MY0gao3jNz]%%(OdHV.f>J!0rW8T8oMh/SVT(J"M,]"{gLiD$0r80_x"8!!oYd>i7TahTF
8$NM*L3sIn^U0j7rYqJ^ea^dc^yr:Nk9+a]R|Xm:*N;pl3artg(a{$==4R2?ZrYk!#5UBc5Uf(T!_46pTv0w)AsW?`C5)#95fC8+Vg(NF+x[zq2`_9hDKai"t&Ii(,hh5IXn9=|R!%*gy-}Jvlu!~6U$nugLr48MI_r6dJW<YS?KiCNw/]Pt{JdW~ZQj+t-W&iS1t?7v+vIcM8<P@-Y@4=s0tLpIx:JPB[$;hZdc@/JZ&iMCqx.tBCrr{&#I=0NbHTPpz
Z4kUY8`(9v{WBny!:[FC/9eqD,8nR!O23OkLzP@K")Uj#-LxuY10~;i%zP&#06R)ecg,^$.iE<Bsho2QT<dOSx>Bpx5h.AD9"FS;>/;W
xdXFs~y>JO:at8m^xt[[H&L<WU$;u:6DhSqmA1o,VQb,vTyG&e1lupgkYa;,sy+&5FJXxl*BTAhedOn_#wB<.[F76=X5>g$&W?)WxmQtsl9#DCD4Qw:oTL]=G
RU6`./2UoLY"-tMf
aIaX0&u^qTkd,+ah)v$1By7LjUXO?ALLVLI&N)a_B_Z*re2alJmje=q"QB7=5u&E=Uh<z#Z$,n`G:R2*mmWy->{((+Awe_W0$w)8:I#o`UQdM[/i+B-AJ4qW
CiNr"{^0L]
T0iW>@^m@C;C%al_7a<2^m5E6Dm+>sw1vBDGJ2k!H:x/ute#j/Gyh8H2KJn/~XM[O(k^Jh9BgBi%w!/j9Sb?NYfpQ#1!(0<pQd#!8m<P4SI>Q&%/Z;3db*NW
=[?]g)djxUM_V6,?lkGdw/VM.TbNTZEKAU7@M%;9AsEbvnC)Q:/;3GN5OB^(>9%pX)(7hp$pMa(p@d5>G
;eGh$Gt?Mc-#';break;case'pt-BR':$jc='!]^;;6lAP,|@-"2@`Q%Tcb1>t&^^hFl9G]E`p]bT9xU[d)el=*@a,,Y8SfwwC%75{$6P5h,bAp}?HQ$ly3rD&8im)9r/>I"jv:#ma7$.EK
,!IMGw
(knkFv2EV<$Te+
5B5$h~$t4G`*VkBmb!7Xl_stu0S:.g9nr[:W&}kRi&FbvM`{`JAUrI1Yly>QRUWGeZ15FTqM`)
w?j^|55b-0e<nlO#O)ZW-W}y/in`{Ax
>uZ<UHum.[K^/jLg}i6>)]`e4iUs2J(H/H/a6B}tEw8v{bP=9[*CqM-:H^AxaGfIdn2XC/D[ibim2c^raMlh?3f-5XtgYM8pshSl3@s`C>{l{0hm=[AnYmwa7gSjf*>&Ewv_!Ko)YDWs"R8=gYpu,8p7mwW*{DY_"*uJsg6*IYMZCgjl,WUENKcm,i%WW8
7-ZLy
.VGL#N%z/b_>FvC~iYp^;AD+3NhHkxtGk(:GT#>kY8m(+G0ZHaZ2H{.^.2bpr@m1*8!m)";SUP#XX)tU^l8y]|lisTG@q+33@mHt_6FM5i-(1$*7cd[T%_s6sTJ4Yv0^lWs%nI.;U{R0a@e"+H^82$4a+D)N,2hKDS

CN&+DZ=G1dZw("
$#A6dW6,5x;gf9u9|kyjiwQ]CPlpFMnerHMe8F^r,@2ryiVTQK:Blanckp}i/cJs+o!qtOZCE#
.c+^1aN)l_7efnbqqpE@XW<})a]`&E1@TMh1<L;/d}X948s
x^6qV:h.2OK)+ZVT
_R,e;8w$~)^64%^,McbVK#818LGaaR-1[g62H"`pJ1U;%*/Aj^IyP=THKRX_Ru("q1tuwhtb)[WQ(n=[pJ82Pq@a7Vje2F"*AR]3b2/uhmq/QM*)#[c9
DZ16IOZ^j1!*A2^/`Ja"xLs7;cC$&mF5dK=8dhy-`S_*2u/~<`<R<rUs>9la2~T]GWn3wAuF5Gq~Xmt06#_nSBS,<_E4TKx~9O5pQn@IdXx$hHk6_9"|C~f4o{(~jBe"kF%~hJpsRnYuOz6u2XhP3PLWj_V|"q"5szIEfuA=JWU[>]%78cX,Bn+VA-k:Z7<$CJC<VN_rVMhQ1ukeePl:u/;>x-(f^e]qMIbOdMj]FJXaG?yHDM5xQ)ALPfb8q:)kc*gQI?h--.cH*;m6D5jCSB:|-UgPluEQGBjS(S4uu;!rAM<zC}i|K3V1j48$Sa#>[OY
E0^qp@P"G<SH.7,~]_%m_A.T<gm$<*tpTKeZ32QdL,FgvLE0X`SJU)w3H|oE?qk$?_>^s0]t:_.hs?p[yz-akio"=V.N/rQzl&P~fzsk4TyPM05N,4H+V6;M:H5!OiS&05At_*`tIO3NFy-})N(|/
"}X+r^cTXw]-KuovQ[6e=@r%%<7*g5Tt*.u|1Cfd_qm!-Zy|@p>Ke)hDQVRC`Cs4mSAdOYx"n+jia&w7g#)EqB%3q|Su>"HJ!xeFTNmZdq+GHc_FHi0ipl2EG8r[UUK$`of0Q`
+Ja.nftjNsP+)^.=VUA>_bxwjq~F^GHBrA7so$f8fSA5p@Ca`39_JwrE?Lr3xFLVWX)gN4Vt>BWvY;e3Y3}x(]j>|?+(/LrGnA~k@p$<&;FhcMw)O$5`e:ae$+=<k
s*<NxDmI#hPSv(D#2DLan/Nkg2#rHC_R9M(yW]%!CN&.#OfTD"$D]#!ZtUmmXxjxBaT&!W-D+euh
JkEGSu.h"0xq_B,CQ8y`C%<uCbcB*u)YVe#6)V=w1sGt&9:$[OFQ82R$^JRkDx#|n5&_B0sfBL_{GD+a:J:Kkt7K*|^pg`C+Bp*&H1bp5xOMTy^Ne6DUeePJcT(-C[`ieqH[0*DP4N$N!?Tl8,mZ>6&.T9_J>T;o.mS`!E%QAoiSHV`YEK0kw!rSgI]HZMU3+>QO3If3y]+
JtRxpriZ<6Qb#kI:`m"]8.iy&%mDyK8$4M(zPN)(iuuOsk[Dnp5^FX#uUTw`w&w%Dbq.5HG/jr9MfNf*TvkEc
k5uo6O]:f[qk`>CKnJv>*EEzf>*Urdr&I`_0;{:
r(
S7OPLxy7O`Nx{*RJDc"Dn;sPVcOR3Rs8%-=Z;ntW^$IQ_q$$AF|8*VGPV%l)`f!MsyrS`W_gA7HDgS]*
U~kqRi4T!Dj.Inoz`>+vc<+:#%$6Fst+VPmc(
B?M;VoBA4Mi$Oj+Npli{P``ptf9pu&Mb5[S|MPB$i{[
TCGtl(7prN1#.;"=pp&v^Wn7lti*T|Gkj$vwO?T=:jm6ptKj>HFA:v>99kWLsnx5C495=$6Ym!G#]Z4xJ>cyB|q*-|K2RKV$1U7Du_w~#!Ed>t7oiRfSh7#F?.,+&><>K-D+O[jGCq"X[,^M[78`c4,Arjr:_k)Hoqj~];C4a,ea7vkpU+NJ<<iR(=_K+{]n5lE#
9xE]wJs%[xWND.#*pe2]5Frk-kg8fH0I;GH$jo:l,nkRUQHYP_MBK/ep&@n,|Fy%<PN+[9K.ZlSFkMRb@T[>}q.CDg$,0`,+9AiyjHjDV-g1aL^4U*Dj6cTa|=W2iMxe&Y2*)yBSa_EvmK
wo-mVQaq6CTTl2e<4ob(&BOQVru0v@p??}-iP2(1JyG8Rh/1jzck@KR6,l%tWn!;MLj|GjGgBu/U*9T:.<93LEQ"5MJ%AYyf&L!O[?>2/+iK)|+^#(Vn%s%:Ii+:1FKFM=jA*h<*!+&mIp9/29WoY}&=d
<ExDn|bw+0>?]DgE:=XU+a3m@&kgZC&lXh+ug[P?;,!%RD.F7~i5jy=a)yBE:^dpH$vhE|..q,Vh>B=ug6l6Y|!q6
6]PJ1?+-NZL7DH>2,F,s*0mH?!J#*mVo#O[:!5ace|@tDQn:%{0PWUT>cQ6@vrZW!;KvZ"/]U
6HKHW3T<<@K8X:oYh+*n7M-&Wjnu`cHq+/Q<ga9A,e?75rB-XovP^m:03!Q6iqC82zG2xt8-]V+3cpLP*QIMtaFR3pc*!bGvCpak^E>-/ZqJ2)Yg;#^{2K`<%*YE[rBMqZ:f.Lt&oYLH?I^us&fnbV2rFx0*pgD#!Xbg)+2lIEKP[(]@j6Ax.GuVvvH38:1flx&dRUBD95o2NA%"ey4P)3+qY1g:RSyn<EJGdV?<pLmkQPb3)2L-)STF>
]D#y6;bw..hI,OSA/dDr+}P14)rU<$L|sa5oRW(RQ@c91Cn%m:-m04]@+81;3]>LR!g3>c_U14w!!H?qKd">RbU|(9#+g/9D!%%pc]Gy_E]~
=N&
)8m6"(b%r?(-OWUD_H$4N;)N9y=<0buj_+Cb9oZ/QmL>r+FW!!ukW9Lu;k1WQT:nsWHX,n5a-A)?jKp

7r5m><).8^hv`*7&6ZJahYQ>@%Zw
*sh:8>m2`W/VBPiJc0C;<kV^orerA:C_Ds70r9BH#]r@ZB$*aFhO^$76oI`v.)I3y[{Hb8"k5<-1qpvUW>uJ6Ba6qsRlmA=E&al>J+2aN0E^d(MuHI-d
@{9uI?it*/6,Y%Lc?i`XG5@G1qYsnqnMhT]G38F6<}SuxWf.h}_}))K|pT8OAKE=6SHo)=MDZ.Kaaf^$FRAKoW4Vlkf3!@$MtuA#B}QTnG$9yIX$:;kg2kYj_c
H7tI"U>H;U3q7(WS=dCTl0(GCLmGBL[+ndh$lZw&M.@[xSta
S6v>f=fTLV;?6,6ql
rO(U"/;QTfInA<8u[llRi:celx:kHY.MhhszvapYEb!+-Qn
$TMBlAuDvf"[3PhJvE:Fji(+V;LQs2uuSFmDK~n=E%&
g%,76yKdtOe-Sy
&t
.eki4{<2Cj#zel*!7mQ,vf(1[&&bL)Uh2=]<lJ=]qM
zq=%%JzBA:M$#X1c#v1=7cJ$7cSrGXam/P_"S^uK:@~9/tPLD$Vu]syF7kfd+sK]|*]"YM
c:7*To5M5>geM7+F_7.^v"i$S""irks?A<v{$ZK#bM.RVN<9]X@=BcwE@!USIx
sr21G,tw_grE;yjfvSloHt}l[R:iNWa!lx+:1f(;t,[No7fxrENn`)xR,g{t~?Hia-/=g?8t58|GqeC@;4jiWgdx)%cxQ:*$9QYxzjUcYiv)7DaH+>ggzi{(B7~O6Qfq#27%proLz]w_fxyD
QLZ2y2oYS<XSirdu)RZ~
N7+5_P}27pKMQp}MHLMSyOl"?h3(De3aqKL"kn?v+aK2YE`cmL{jBcWy*8:jylb?"ynvM)~--4L.=w3+N.SiQ`$wH"Wwv3FVP6voM`P)M6Y<"1"o0V]w8w$-#K(L_1z=9H8d6JX3"!$hSwqbrelv+1)q<7d@?nkGJ#@O<f8WX>-#]$pw0!)kjK)^o,Vlb7(,]3NsrFPY1+e7bVzlO_{SDaBuXl6[iIkBBXUi(t_%,u+C}7r&;9@uMYg$
.>v"POpaTUdh*"i9K"<xcBg_si_@CJySNGV>4K
~+,UMN2]#e5KCr-yd:vY6s!^f%;`mvNcuo*&Ib&X6MK=Lhl0v,/[a"a5eD-Y%B#iR.=Xc]sIE(R6_&RKb&h>9/1L$1rJsVV!`nWBl<B?C!u-e"Ivg>+$swTY@Pt@mQ>bBjOl)-YyAVEyx7a';break;case'pt':$jc='(`G@qaMD9*70L8,(%-mEYiVdN5A<:IS"Nj<B82D-Z6+Ir,CuQj`DuF.d3eK6~_B&>"U_*I5H%J1
eiEV{9?:p%}!|+SkK
m@!qdM>w*oG<qThEwF@c?Mt7Kw88_F_F5>Py5S(e?!SEwnk1;Q8^KK&9Dd&iUbn:s&pI%U/_8R8>K?t)7T%_V`v[zQdUVbl5oo;qTG(c+0y?<
aW.FAB6g*AMv|w&9]5oo:q^2$H^jagT@4Ap?>^S;/:$4C;h
qLG^~x-Q#n`nus<4xqL4qm!sqL?c$i4bPg/0wUr:T](EDbp`uLMn0i2j=b1R&7
y1LK6M,UKzE:6p#7+"hYqC4M`-
}Ep
5,i[&[jZ%;d9lnY+Je59rX.1"ZvFd47$Rh^Y75lA^U.%F,"2=iYgyY]vG4Q
?!WH;77GU^!)+Jv`Tlm[?
juZWM^WEFX^U/U,6v[:y0G;Dt@X_bX#[:W
/1_Pe]em0%q1Z1qKGLQEkEVksoPoq|[~r53J)Pe|"vUT)|ZU>PUSF%@h%V6F")Ls_xa#.Tf9lKZ)sdf#kKY|_W>Pk(`:)9"z60H0M|e;`BgC2-E6)
#Ur]8U,I[Zxv:bp2j#mz5V#bcR@E*4JEO&ULh:tNB&5IP)4iY|oI#(V5<cpbS@x8TCU?
kK~y)(v1S#V+G>KD!P5lc>Q5[m=_CSjxbdunt!Nc^77;
x44Qkk#)%ZW9mvW_r;Hj1C3lpe97o_nU,UZPB_iIX::@yoY|<T/0$Ua9?mE*BV?6UVw4V|u:./d&kC]q2VoP3_pfJ#Qw4<,3Z4VjqiwHF>+zAoU?rtOzy!<!Xu7YA4T9K0s0+qxFLei*,!KKi<qO
J<Yk]i-G
Xg:cTsh!nw"|)4DXa-)SD}auWNKzA@J.BTmH%&FDAFMme839Da:ju(Z&Jt[2M`nLZ^)be:lpKP?fO`Ag
s7.f>vJH=8pqg-vlN.?mhOM7&U}PxB`/sBz+C*vS^3tngu33hh8?hAIBA/.<vHV-MdJx5>"<JPN)abv5Q*<%=cIaAU7?{6WUP>gX0q^5pDt&3RNVbkLCoE7-ocX7YPM(-O{;0b&r-LqE=n6d@uronO$;kkeu01gm_jexHB,P8<EVDX.60JOE)63>,*oN?w
L_h|h-/w9M<a2qZBtPC6*RX=7_M^.,DyNqp*Y=nUHyjr`PMd<f^;-j-wtlg!75XA]|HdqbPslVFElj^$-LFiluA.:~]23g(v?M"v7-OdyE)SAMf0wS*Ny=mVW6XK*,Hrj#%PD$N*GeAT>iM}-.4_IJp1/]/8wgVH@A"j3!yqmxuJ1xV"cP&@wi@@
fYr8r8pB@wy;#9vhj
C7UmN^:sBGlIG4A
CPDUv-qJ?gb[Ekx^11l[7Iq
Q>nNkcbwRM)szrP6rDoq5<urM7Mg]hSMU^z4QJq]Ufq;`l]z!AS#3@/L]5I%N4pMilGr1?Mf#Vm(acDx{@F)$CKp&8VrB9T98-g9F4GUR3E7-hp6%it-R*vVLrqCd=oZ_(m5UK$?-i2``0`;/p
YW%lQsh8=|s+-cI]S&6<p&2JZ(4p6,5c7,^m0_9V%:?hYoXmsLgt:nwv<Eh-0~=%sV3JYE<]D<u!NZoKXeA*g;MXtR"g!r4q0JwdBZo*!{GrOfg7(C$C?b@j.7H47[.|Pz8AU!(F%x.]y*fv;M$YvH_s-TSgD5,F@X]@/tCz@25b8>R+4
DQ9ER!th#19<,M^
(aSkAS"=3+->5QS!i,,Pc?6?HvmH:T0
6yNG0=qr"CA4gbF`:*hNa+eh#5xzl,[|r@Y5i{$,".A}9
Xu:L*{EfaA6YAI*gc60mpaU3T"yhCk9/JCZ5G"+o,KEg>^"
PNG6D(L:HiX7(-aT=
"S(/Sx2,fx%KIEkDh7iS
9<k>9o7*xjLa[[*oW[7<<HU00jYq$m]57^h3#p&I2?$^)ZRO"i(N^XmeGI6p)*;PmTbYh!R/|
`f#>pb6L<#OCo%L$@)8lOhXpYb

wt68:v6&(Od(}
mM2IA5#u.a^c~Q$p|+65C^7sI$Qy5h4dIL}xv0kE0Az:J`$!z>nJx:KWrLI`D>[F@BNb0k;15%6&cUHY3::vP@{fzI>o5
z;c
_Pp>9h6FjX.(Jc$,}wkg@Sc15U!0_C*G_sGa9bS$("G5000)fmAihhPWT1e(BGB%]kH5g#ndUWGyH71
9Xqg_G;K}?rlvM:J1;V0!6QmRCa.r=ur3TsZ[ffZX-!vGca7vnUj5mOeQZl,x)Sq2:j.!<,>bZ2@p>GyyWLDOQx3kbUyhnXg;7x]=,;y@kv*JRkGkO_i,1`3!wXD<2K(
6`D4AF*%R]7Kt9n:Ww!!l~D.%KH)-N1BKYuKmg&8j;=X8;vOxN3L=8kV?4wZHVTok!f9
!1UVPWZK/!%qMBxj$gk=eM]pr;1yp?x=vNF
O8T/~ck9>ahKSOq7{c_)>LJMl<fH3`.W~)OY-TK]!w?r!e_B!F705&ceyl2f{l
c|?+sOk^8-"wAG9".]<SQ);Psd;%/#RqOEX-3xy:=*G62kr3ELj%`~3h_muK?G?95*t5[l0LBwU~P5_W4{TrS7^q!i]?x!;zVlH{qiE#edq(FB-fQ2-Kv%W1Q[M`k94~E=BD>snn%1drrmj8YJX.-k)vGp^Zi!5}$[dP;rO@bHHa,+/D[_Mwa?C!CQxUI^d8,_.WAPVYYIIIWW5DZ_NK(f#?RNwdkL*w
W@{bdHc(a7QvT0.O]D9YH6G%?8;i(+y1;"3h3rB<wDUQ82TOm95OIT_82Jyv3b;%a0l*[w+V<pD%48c<Hm@C6%t1F4
YWdv:I^F3(9M71"=9N5>:]bRwZA=TH*7[`9x!Pk@<"<=!DfFT:ZeYK-~
a1k`x7{&N]
L:
sJ#;LpY"GkpKOU{W-NWFg=jB!#wt7Z.`E6HMT3LrarY5nINbZ9AS~x(Q24#<)?r]9%(k%gm?weO"i9P"q
O;J"R/CW*L>9!f;n!]kupMc]U,
6VR%R0Ibbgl@G<"u`t3Zf|F)
=5m!4c4uf;H?crI9YZd^$-ScmE[_xFxfomWoJE?g>TgIno0M(
=t<TFaDYj?`Lj(zF61Ey=&#er&+dU#r"pl78"!Sh.Q
GR&0/&"Ih(r;XxFbqg.w4ylEL(!k5*v0`.@>:?_Gl.,|)mRm:yaFDQGEOI*#mM(?V=EP=l`PYBo<a-*_5"yYgsb4${:Hgq%}D[Z/qR16JgWJ2~%zz)G$_$W(7L"<U|8{?!`:Jw&"R[
/gEpBK*IY[Pg[cy.Q(C"}=*<Tqsnts}dpkTd|a!(Zh8b4gIR+N]tUmAryGP(Q#F8cbQ%>?UaDO)])l90}Xi(b-G:cZMcx&:)k,>6p1
^C"J7wFjA#VbawrS<q
^QKe)ZC%;!p>^P&_FUc6c/0$qZGc*./31SMDn$drNx(%6QQxe:XP+ssDZY5a(AwZ+FGR5hHG}N-n*wl^Z[UXv3je?DxFhY`P}_!1"Pw=A#[8O-nV?SHPtnN-fBno+XR9:PUA`
k`!rN$o!pgG<Zd49RHJSs(w33t!QYNv>qmm7sv,Q]=S,eQ{^S6(iqPO2jW#!E2z9.G@GyYI#_r*;~`WLWp_Vsv&RYYrMC=[oc*Io2!W7h95tmOI-35HddR]8%(g4+[.:]YMI=Y!:*^hvsW;EHn+yKg|^q!K5BD52WK@P@4#iCh5t(EriL,Hn=^Ck*?D^p]34aA
,/kt
cw8mE,<P6v
Ar4HE#I0k14MstHREilo:>V<-eF
,T6YLZ@^:*8/jOGJdKS+<-NLY^P#x7c70&Em!RO?*]#&1;wY4~22JS4V$f,
;<[{$9SZ)9W?p^-/NDp/B"+MMT$$e2V_W^!tDKEm;*_<`@*T!(+j6oqOsFe&Z(Ry]Ogtp(XiII"3%p.pp{Q`!W_7UwTnOLmE2u#.dG>>;e,."0#E
)x74`lL=mJI+;NpyOxO^wl~/,SmR}h#,%99s(2>H@Oq+pM8sY[D.n&~7Z6Th`r>,W[$B)(YJT8(hz2}Kpe)w^$"g;PPapN2NtWHM|vk&ipka979-TG_
eft;+NKQB6"DbG4$|KJcKIUgQ1hgzG{ND2A2%tRr[/_G7c5&]IUbym&m%/UHGidC&LAtjB9HuZ)l^.jc0(P=c(gUI1IB;/xy97pJ$EODw<X!ZYe=sR<qlF%HZLRyHq(!`8{[|*h+Yy8tdV^DgcDq@[_)=8<f,k%.$Cd,P::4eH>G}7AaEI@yTXTY#;]V^D}MwK.b7;a9kNQ&2Cjp?4XT"h"Ro;y>|@le!K&Dg-c<Lc-,=bnFwo)eCEWeEtXAvLYNqw}*aVon}cR2R]/ezX2b`(l%6x
xIdkdM38Nnw,<
4z]|?V1}i,3<CbxD8<S`c>(<9*b]ob(:7Ph_TE*.4%g3WDr@/+"Il8!C#C
`"3ILGvO7-{w>H>M
C!4P>y0H+ttgeX:%^D6,Tc5W+%jCWU[3<A>~>IFhs&sMo}5&-8d]>*a@![E[7wM{6k!C7q]3FD0BvR/&h>p
yV6
""';break;case'ro':$jc='*]^;:cs.!2N?z""$qk^uBhO@
S}IJO/eTCj56CDA"ODfKRy.J!L6#Q6n4KvNGdTU~#$CBgjQc#Cl:yd@5W$.]#|2RNbsJn@b7l=Dwf}c5K4tUc9B"hQ
wUQ/nf(&
;z@x`>qIZ3C3<#uz_ovKc/"BLmR,gf;:--@S@sf9B9i34`;1qkib(mRfdCVgvK#!@YlnE"vu/BuIYKqoqxn7wx5q!20PY{Un)EkQIxD&G?7=JLn>v1)
=]e>5fV-n*hc@+RU
"9GekQxgC)]F^L?Ajb}`xm[0*Vz&Cbmclxasah"cJcAt6MrcTn}rYC@reW
VeiIw|`p[2G&TtyRc
eC`EL*7PHG>5$R?"+c6oWxP<nesHgf[^5-v=iuj>5qJ%1<0iI%Dw#p!$ugdupjgFw`ay__#p=ry~
f`]Fj>18{+-J>KhoN[9)Be4!-`qr^F[[yL1TvF8x5r@(IX`C(Z3rn]UD&s>Ea!=CPkY!,e/?T;)mcmfmgqINb6gvEbP%v1v
y?[0
NsMgBz4yCq1mkce!ZZc0N%jkG[!St&pnBPPO^KC5?o@1s@YPVo#HjErf
lTC_|go-K`jJ#ob7gp2hI3A$}a<P9&y=<;X3
3S`q+6;8EUm5[!&fjnh6lonyoDXg?;[r?hjG9]fpeeP*mM#H;p=t"r><x/.~jK-X[9ek9&b_(d5>g^PRc^nLB"V%;9gYYMp{s~28SR;|aO<;Mv-tT-1.M6KZE!_!r4B
0"MpGvfV
[AhWKv/2@dzO-^m8K0*eB9JR"^r2BCfa`o>Jo3U#OKoO~f.`Nbo5w[^Ol!$5>HD-DNZGnOYYIH9VD#(Y(5ZE9H6Zpxu]4Q}<lS/J?vbRH=U40X6>M(8*JGGMi]zYs$venajAu3?U#2bQoX3Xt]0?t7rqu-=I<Cr.U+hjt<<39^btEgchS"A6
EfU)7R%&]&R&-?j]nGVzJ[%P7oRUv,Ix9|XXii^sRy?kQ&?e"4hBIE7[:u*1(J`zL8m%
$s>Eq2or^rkT)1r4m8v3;POY-Hn9n>{QT^y*RPo@tpSZ+63b0Ox
J++:RJ7w3ClZ?^BI7y7JD46r>MCx`/3n+ggo1y2n~YhUjN8<S],JE>jl_NNH083)#&wr#1-UjN]a;?Hg$ci
v5>m5yHe=$m2~PP^M<[.V>rR532FzpK;$^f6-(*Yn*idW1hO%$$oS<~G.5T=:o-b1wtBT3mkktw.fa:E,
A,Ff4=^.QLN1?2u,
nEHfbVN>WbfHgU&*R5!IKMGMuArUF?(A_qx[q5:_M^h;8pUBfg3HR^K2JbM;Y{A>=539Vf+w4ajc!9Y55m*e,SeL85y0;I,NoVr_PUPyY$*D1>)%e{4*<g^c^T/EmVq8RVrz[v&*eY5_E0J>mgx/(Bsc!+!b*`(=A*TAUkMx[:`WZq2xB,E/`/DNX9P.p3=l7>u.hYkG`7=j$|9@0:q"lTKr[,VxNqn$!FYZZ/3bNxWK7FX%S!:YBm9=!(J|6jK&SX
i@UGd_ZbZ1Xi.?vG*4e2F&urMp[VH7wo}YpNF8Yu^rR-uxX<%@<Oyo85#h3U8$L*!ni.v%IR|O~VHU]bG?X>khXcYP=9O^$wvj=,(G$K=2cwi/3pe5(d~NZ)?1fO(bn
"
7xRQeLX+0`)j_6("KbUQ&OPoIRRwL8`T#KX8N3,%6Pj9xIlpzlH16R61yOMIL0^A)nj,+h%w>OcNqjSE,2P+jA9<TC3OORH%~L>9XP2`!5WsP^9or^`Bo@fdA,%l8OUvf
uCSi.8(Z<7V=l(0GrPn
<g][+"L`.WgvD8v],tx
yb;VuCI"`0{%*k=6J]v@&/`Z#:6].wi5e(;a&Jz8<$l9_7.1.(EmG/ov`,QRSYM&,e&8<,P38YO:5a&ui@6N/]NpERD_0`IZWI?5^K,^^o{IqBSo][SKWAUArygOStOER%Uaip1@rTQ?ul]gy+1<?a]ntVXFUd{.)vHBJ.$,i,KkMA$VcL:ct&}cjBLM&9s`_Im/%J$G$BuWA6dW{FCDp^Qy1Ut
W_9Z2EnNxU,9(ZrII`dWY:B]Ue_?jdn
Ri!`Iy$Hxby[5q~UAEq5jdfsg3Yyo/{.?acNs#F`AA,lz$);6/mi_?Zm;`g#VQ$.r@0CPZX8d0}YrBd_kldQMhW[AcN!r8WI,TtxX9iOHb:<,9|C[Kag*EW)D)X&7ycrOew9`W,KnAj2wAO$ZYOfwEH<UQr4-?]Uzx9L[*7Zc*DV5LeOUhI^-rToz_1kg0<hk&XSd%NXM`<^V]Ch0SxC0J|=Y
QGm!w%.RX##q%9z9g
C3)
|x2F*Yv8)%Tt]>!.|_H</Fw$7UaTYiSf-BnJY66`}=-W$"[VMP;GS@}O-"W5$S+P+UZ*;:tx-$_a0y.:z?q9M`4+eTgKq$qxl^V$b#98g*a*U6I8Mp1
N3nAqTP_iq1@-;/=}qYQ!]?*VuvdgnJPC=*>"]X/N#
WhXhNNaLau:)[)KTRotk6f&SDXk>!VjIpho*brHOW8]ICusa"g8!)7DToKI5U`q9VP1[mv5>S4-BJ&yH&Gp|N,o]^l0y@Fl*BS;w`q3of>=b>8aLlzF{%i;7CCMq]~
.h9o+m$
I?rE;k$bauu#ar/)~<fGc.QKfG"Ix%Uq@11m3L-RuCM1y,~dud`m76TY[#P9Dm0D"pF2!2MaqSDU71gWsF/O!"+DCQeZS:J>nCM=ID)Uo0n[uhGdQME]@#30rXK"6(fqe>4FjMr)5ox%|?LI?/0BeVj8XTpl^Uo.G"@WPMY>!8m>.c!)0Zr26T1S(egKWK!EAM/N/YpB&lv8
G)(~%O:nW=Zf#SDd09B)pkW%(mtA"Al$Fz_~R0
cFIR8RRph4U$PUi7:j}Os,4)omD5U^p
Hc{ir?yp}_,LLm-(V4]I<wVf](tUPg`CGM9I)XMT9AuI+Q`[lO#E9x:R4Y%e(D;"Zw)eB1}LY/j+|bOWt,Sr%ob<Oe&Z3Mqb$P
:(*@yZpOvjI2[s-mermZ=y.}=="nCXGo
L0bfQ,9"~L8K[V{
y2Dl#c^w~)^b<-k]/?WJ)xdE*#_`hfQ`=G8qQM89;M+/mMt1WK@@,
Vsjud)yBSve$P[8Z,2tavKEAgweh`ye&I$pw9s~mPTb?}o_=9h&LG$w-|#<sRsd^l%}Kge-;3c`
h7
KEM{vOG"754rMb&h;ViQPsTphTZxVA]]`v3P[Mq.`Vo!S&@a$Ump^KOfc"u-txxuSx72F:;yZEHn+p>Shoo#K$nToJSDz#$|LQj`hr1K_jc@4P<MmF_OyH14jd%BjgsURK9=IqvSmD5vX&2/AUFxMc9
%kZw`PHqG*sFQ<>>Rfc>V(t!<thgwJPop%Qq:Skb9~F!?Xb9SP]~fi[gTx$TIqRnQZDS%4H:<@oA<CLK4&!5[D:.=mKs?3H+;2X&"{r;j.&S-p5=PbE"0y0)u+M6Z-Q88iahPmyWlu+83i+K"g:#pbNa%{herz-a8J,#XfKp5g;.)yGop{[x>|p=j;6c/6_H
N23B%wbt|*1XG"59zV9.iGY:UBDB8^j9jI5(/Bi,(b"4Jm|len
b|1=bC4:uH4/SZY8rV:s0{b_ef^T-|$_icX1rGOTCO]`_HVOY4)ew"jLt_$
N|Lyq5ElanL|yedC]uUZm/+il($SnBd7Ik!@po42sl
~>7KALfWH#MP[/F]
xLBfIwb*lJ0M&3FQQvLg-J*9xFuc47+9@BI,HqyKJ`i]]02<l:mHw9sG%]v879G4v=DUo(m(Z{`9&UaIv!(64!=8p*WJX&CkV}c>uy<vg3I+Vlg;M~NjOT1%%qp;`"d76,gpi+8Nw+=*??/nR`4v=NOU$?_DVqT:9Sa/J*LfOgK)Y-=[,U_=n0yv@_>ii65(6Zx)3lek^,
NTyvd%gc$qS,-IP`&m_k_wj:BOb`[[y(P4@T"H$]q&ht
!z3P?XQ7OmGK2"c5FUy>h~jI4a=~pR54aiq%(2j(@7`"FRc-Rg6gOK1.F9cMA_GZ`*RmMLA[9-^Qudnew"Ez8=QIri.^>hJD<bYpw[;8EBAja:t"Nr5WEyrGn5NjN&(=JmW>x4o~jg_uKdA4.hL-x1s1j~%<_qllI/FD<C4j&c02A&<7B,o-(
knuUWyJFXY,%>?E?iH)D1_@5+f77n)EpIL.5LCfOfA>qn#u0&l*22oESujlT3tcR+`2yp,QysiM.dO"]:}B[/h,k$r*8ly,W<jZ1-BPU+$
T_JOtYaj>fU:5[?KN05>@DQc:!I-THN_ojpm>qQ70ZNft$z
3`1Du%T#kyZC`a;Dy[j)G1C@W0>[wcfUn7W1tl)`vk8kYM:>dVh(&R"S+>).cyLQ@K<)~/8QZ!>]2qzX9WG.AoFM|A;UbE8f<`#$CjjycXa>@OL`7U{CPqIFC;)DKgOXZq%g>W=J6S;h/2
s]^8rr>RlWeE1auGmsWdkHeA_u1&<pvxj,cz?-Y@p_yqFa8-A$H>Glnx]t[4_|BA"0B@@MhSsANK[T<(Gb3bl4&TYv>2#ogv?x8G2.[B&JcW%P=H#73}c.y2b!JJZ&^PK`Y]-FWIR_
f9KD>sw?PJ{VSpWZ$KUG9>,k?g+eS##sF41kwqCvt+vPUE:w>05+W0cuk[
lpy;Q(JdY
elXBJ`OLYDVtS!8zMt+{dBdx0m1NGsF<X#td9#@tD@#6wIq}g1)z^z:uuicYm+<C&gPZNU7-
:YF/[,0JLR94z8VKI%""[=ML}Z,c|E:k`Z7&{z(S_`_QX07u)j(hE.`kgXu:qx":/kuqqNw1]KIZfW!*&M_;[e}TYoI%]CGt>ECb~W#J4^yx=(_57V*CoZrntu)w@yhK=';break;case'ru':$jc='-h_Gg6l.7,|@$peDI5VEWi4X?`HSf"4QM-)*Ku@Ky+a+m;0a#3s8B(N6OMQl{VQVdPq"2,mOYNiQFa6H1KXrM0#hCSlweU<rgn@E:=,bRppNK
>ntron1](7(W6i$gnGF%pGP@}J
#{gExENAEiDHH<fh4q/lsH&,SThsSrY:FTMxc$
<h
n#@g1#-X2q>*-*9]lcm<+uj4qBtysplr)/DACi7V4Qv}hQN32]_E#B+[Ch=:/RPx>gc=D9$NW]fZu"q>7z%dfR#Y[F3m(VN5u{[=CB3M+Y_doTo"[J";6GCMy=RXMz^-nu2GY"[Zf7#w80`53*tqX4rvTH4w(aR7LOC6jbq!xkrr3$A`6Xw}vxndbyXSn
]qx<am,gyzw|JwcTB>3Kc4n=,]Re@6ogz%jPkwshJvc4>X(sT4lEWZnAeOUGtRMN!5,$7u6]]8h0kWw%1]Qo>=f!kC[RqGvP<=+iZdT:0h%fgDwQ^tS|&xAJ3|L/LSrT<_Dxg^7eQ)x?chbX9]FAoM1@WEXf-f_7Co7PY-sce#4to"932SD4i]Uw68I2gPg?>/
DH;K#v2jP0"=AC+<(sk?I1Ro}9_AA%q;HiTt%j]v+KF*Z&vv(.4G">r<nC+I:mY,u,V"d<7K(3.Y:KU66Mlu(bkqP7%2+&?+%/ztMq:hU*NfM/]
N/bb3FUZmW.^#xgn|y6Q1m?#~7B7T;#V{ie?V.FtU8/-;
AO&CaP&`uoJAz4}%5d7En9}oG/01C(Z3_58:.x}[Y6|ll?4*k>0Sg9+ALXtsv
"?U=(`IUdLQYZhNb{9.mc`$a.F!jx#hxTyAyC*V>fTO+X*B&CQhP3Q`J_If;vkADb^o.)9cvd!blqppMd*<<?/BB17+*%`)Us)QWeR;2vZqkK
pIfnq*!l>pwc4c36dv+WWx
jmI#1d+Dz)?s1ci`
N/0.0@5!.Ypj$qjhHW#S*Ud
s
rpFR
gJGqTjML*MVwp5+.hBTrL4@>atgyI$P$me>HYdH&kW*!_V#!K8dtj!yxF.t1lB0H-/de9qu_,+rvlf.zr%B8rC6XY3,5ef8&m2xMG;-
@:QIDSk>vN(6X])
,/IDB>`
i|hnBwv1nyd~1oK{8{ar^mr?>Ramkd;/avkh$EDq;@;`W_CSs+MeEw]j
3$x^s8u7jQ~4))HHMj`2OO6V>m2UKK]giO*alE&i}>BO2Q6w%Ksei^C<$PA":8j)znbd^0,s_](bsIn9cnhBY&u=A_h]0o
-vPlho]MyokH:N`r+$Wq@lo#"?*ZYsvUo5Y5@H3U788=gM/%k9pytsB)p6A/6`wTa%I@,|*xoSO$0+[df(Kd
|5X7QXy4:QjbJn$C(mFe{[g5<3$#=Kw&$"OHdK"IUR/^s+bn/%ILBmeJwQQ]?@Iyz-aJ&taF{^[Ou!B#bJCF|fU;3b6S.Hfr@hJB;sM^^*%+@X:%-ba!K)X?FEj.=
!gVv1%6FM@)bQcD!sA8/G^I(zV1A?BU+5NnSfW_@uUpJgUH%-lADlQcihf..phb0i*b!I/[LRu(3kriR~"o:OI^7LI0^;-78H
9rdhm#Y=DC/E/lcuIF,etZ
&pLOnuM8LaA[^;fs_=5evvSp.++6SUUX)m4[j/x):a"w;J3BF."H4umHu[O~S;shJ|:.g<l3:()ycHdyn9i!-[3~7[hR#+UQ0P(?!{k0>Kp%H0QShT^Eoe
t7$Po[P11uyOg,}66KU.K)Ir)pb/)eAds8FH?@7wl,<G!q
OzC-]G$vDv$Q]bA]Lkdplefy:NLhw|C5.9ZfdQ1jZEP6QcUv*l!g4cvU"V+oN8t<v2]54wjx#/Rq2ONPZBDIVPC6G6k&v)H"6A%#>W7SRVVb"w9Ang>?AzlD5}h`EdwVbd4WP?DUsjT[jE5CrK_([}^yp-1*X{R~m}Y>5
h<2-I8d;eU`He1dd:>4,.7"Y:1O:>Wf6b|dewCbB*qyVo=Lc3bD{/LR{!ySc2=PtT=Ct(AJsDQGb<<pQ!Mqo2KQhv.?<aZc)W3K*h
@;*{]Qms6mWjgtNShCmJ^+Y=)h*O4U]CV02%N|ffNJ^Y<W"r>I"}73f4SgP0uP@k+#dq0/LC=U?S_8=swD4dhfpSYP5q7cP9?Q]T"!gM.?tF0mPYKT82wdOVp%es!"SEOZfFI|/EyV43UAy5ZFh9<>+$F
]+sV$Za2c]H>/=hcQ3t2hh-Wr%%bi7bjLx6Z[C$nP^>0?<<9eQ8q@m6P&_p28>hRNf:-@Kvx]3c1wM@h6nBm!b.bg_ToWsk/L&xZN`pE9.@ibJH.76i:j.?<PPHe,bQ~x!6?T-^`-M&C>i8PWBDL#Y*^iOWCAqw@&m[-@i-V7:+9lT4@Au=K-]m8:?:R#Ifh2|GEis?|K+)@_p?:2mW-Sf..Vqy!
2x?-8^x*-jrih5`#DbEobAAYpRKg"oA""dhp3Cl?4_Z9;$?n0[QOyWT01gNA|]m42Ldg>eAg=D8RUOZdo3seVU9=Ly$mt`Gotj6HzK&VGkcm3(ZU`/c1ja,mlcNN0`XGuR,$#(^:D.-Cp$tq{R$a0)xedjR>;2]aY8<&I&7[=.Gb[gVA;;uNY7"F?oNG[[&D&h:@DST)YYwR*CKWsm+/B;zwKG9jVh_Xt7g)FQ[?:^wa!$leJ(bG,!nmHXx"NfC,WC[[Q3+reS#"4>)#7U~.~"%7gM|lWo(NWH"Ir9?.jKv)A5=CN*ZND1Bg?,EyE#e-DEmxM
o@H"fbd+NYVIA?eYno@D>=g)h2`e;sl@,;W*0KO.~(jM%"B5b"eY+1@[pS}9lY2Eqgj!`)`:v[H
A@=q6VNgX3?9=dVv@0(hRA8g
1,.GR)5+Vh2@8C(zr{7uvXu53Wlq_N_?;l.WLO[A3)pEoiHt,pq7*`3~Y0KH*dMf7ve=3I>CAPvMhYXT18l"uE#`Cf660V-Z]"%[T
YI<4G*CV%/t!-9$Vn
s)_fvhZ+)n%MI31mJFyBFg
Z9X.e+=%Bm}9Fmxklnu]_mk9@3`f_*&+#`GhOYg`;aCBE:F-8RU*KUW-3=.
*plPM,*Y,cB9NAw(|bEC+^Z9PiKvrNGt0d(k|t~hwalp(k-
@*Z)yFiIj<96EU!/,?k]1QF(_9J9Cg8Qm<n=q@8[)DI!}EsHu]yJstf0+ctn,^[XLS7:ZW*sU<M
rbC
Q%!nO6U?M]LJu*b[V7!?hTtBuaBGKe&p[u,20y5T=OT
.F!q?0]T$9EN1o>O!or/V
aY~;JCy!UYJj:X(/![xQm8{T0O"@_q|+kQ}=mqt@(R5)#4NG%s>C.`Q;;WIjNVHVs6`,wigV}$Q3U<K=i5[J%3$cSnL_l_atzW9Ab(CD4S&*_W"HlTKwJ1dj@i+=7L4S`9J2*J06xDQSi2Tb<$-+cqpH,Gj=TDef(*-Uo1)e@ks9|2gPRdmuQ"AePx!^)iy:{bg+.H6`
<-cZ3]8)
/]gYK_/^Z
m%bCPERNA6yQ]bAU^U"En?Pj[01ru9GHaE2CBw4-hVrPWmcn
r&@pC50i`L+OS5=)q7r}+b8v(OV`.:J1dk:O-P@t_Re+#t1q^9NAM";RG7?B8BHo%U!=(7W}z(Rh[%9Z259]DQSq;WHMF^H"N2>G,Q<B%$;MhG1%WcLaG!:"`ZWZ1{%HkXUtQZ1:h~n{oymD28f[(L2|`;)RD}JZ==VnI5(ci8"w*"x@W:CwPD2E7W;S[8B`
b2`JH${/z0*rX9W5y<wX-!8Q`=_SLpjEmxpe_C"Vgl8W4IE;x<e#]/MS/51Rkb@JK+z1rfa$F0kUP]&&7J/kQ0;l;I`aCLEi+1tEdw
(EV^@|i2%NX%r#%^uw4;^XsC9QCyxU[:1=@]:B]%;b!<5@RprU+<=^Jce;*DJ8:q`t4fmZ8Q;?)HH?Ehfui.]*hZI"=3BSQ=
a]FGHp",*sf`r.#?{3UO~y59XB#[JeSR85wjDm)J-G(
LY
+B,oOi
=t8UkOvwXM;@EYxJ2.hF8t%,Ur9+a9#=gT~1^=YsV86M"T=oYCU_mJ,mBSWW~iag>c1+a`x84v/B
Q9
2+9#u(Reu)-qbij@S1T_.[XiANPf=(T9oBJ+Yk**
.~G1Z(UDM/Ox2<BQ>>Ss^?uKB`kR=cx;
"b)>MQmF^wBwUK~Ew01#q!I0PIxmw
RO|Ks?&L@k._^id,m;f=:3~@
6VH"Nq9?yo${G0r(W|YwJ8:.E9"&)R_Z.+>ZQn-w^A:OAb_}UZa!Rr@.l^w>`G_OC|sML9A<%Y0j^1cZ,V4k,g#}C6OA6nnAe{bLm6lrd3@2Ad
z_kstluqce8W*V0YCq>vzeZ0OA}x-^Pcp?BEf*2)=7[>bIs5;pJ+p$Jak>Wi|67gk/YG<X<q??SUH8.wRWb$}J
$b2x6F)-dz@V_z<P+~TzP}PeU"eiKSf?Fn1z16c?kE<V2T]EsB6k?tV/saIbUSA)/M@(`WZzOC1kMLf5JU1RuEP@%LN%lop%wSL9Y~+z4DerJVZH9Qj!nqJU"jN10Z2iGAKw4_X9D3a-X5=[C0>@A3H,k)WyWXs9XYwm67FieA=qa>%D(d"
nxIor<qh:_Gj+E%%x`eoKRt8K-x~[fYFTM#WcJE+_i$(21RpxHUpLK:Y:|at@@t87HBF/:XSr[[<W+EHcscx6}=(8OX{/{K0bKqN:XNpndKwRQ`6>UWJu9wtG^><d|5(Ucp["H^UrxH]NBik!gZU"?^Jqi#r6V$e!$40w`EH9+^S!7D@JN#4sg7fZ&C5t1J}qRA^ej;p7fmqVKCH<
/c[Q0.BU5aI~
*nI_f*766RC@;>DA&qq]c;JIqhr,1Hyp&Bb>~Plp`ZJ
(e,!IVpx<<M#OS"7o:;Q3MTFs)uT!uBS[Zr4+evHlZ>s)e;72Gm_5Bo>G&EXP>G,Q,VQx?nKf+=AAtxcs1Zl8NYm`jKE"pxpm0Q0LXxG+9Hq&36<zgZ*(+$=dD"dweBOD
!IS)uY-U{M<9M1n$0_@cEAPu|)]:&w<fU^Pt2p@tanlQo!rUi6L?e5"Iz9EC0dI&or/k3mO8O+%$<Yvxt"ppbh2q)wzndgPo.3a;-2j2"bTVwKHr?.80259xqI<czxVGb;*5fU+m#;cK{&1W59f:JBFJF<DwR>]l+l5G:x}YHfOy}k2j.NTFOACj"V0]
jvk"@T6G[)fp
=o2"C/U]%"YOs]#RakyP/5-M??bL]5%4C`@?~5s)}gL#OyJVu9Hw&!;t=ZdPKnCcjOl*.9qNcrpqdo:,nP>0Wt6C<AY8k
#NcGLX@dgUl;rE3kz6=5HD:+".p!gxmF*q?:L""?h@3%]Obb<h1(2J]mmB%6<
WrXpg+34y^;mGT!?]hk(Eqcy)1H%k2FR+H&Io"ZYMk`"`M6>x8(4$4EP]jh0C%Y-Z$1
4eq<9(--SnJGnZXMZ+(cFI,.J+PH2aP_BG~>g]zf
DH4A6R`.%@JCG+bLY$d^>yk:C*;DK`6vF(Xn_$p:FE?E#wObQr"O_lcM4<7L8$v<)J=]^LezgQpS3#B`O)ywpdJp$&M(+Deq.7mM4aib`b.t@TMVB930rxE*
!#M&y1_XXYI3fk+K9?yG}@+nBX+U<n6>hu8jg8b#?%50LbgK:vc8$';break;case'sk':$jc='$]^@j6LDQB|0tN&%>*E.nh#dTKJ;:
s`wTSOr[=grK`TL%7A+:::&[VwBvO2imdIq>!Fyks#oO}*Dz%Bp7!r2+6r8mjo4a]<+HOVZpTy%Kd[QgJ5JIDriW[gfJdj~F]sDna9Zrzph2y7Ra96HoQc)XMk{]m9q].dHY71l>Fuj?v@+)<L-aEgvq?n`A{/Yk53)p~]+XY.T_/Bwkj0?=7^?b@6/D"/NEG>4[dcmr<&-Rk-HS5Fe>5;7HC>db;M5Z%V+evhVy
B]XAeex9!H<,Z~iUv9uz6i-~)48#7dp=u7x"MRk2p%
iZX<~c|h,]QMyguoQx/oTrl)LN%7CfkQcZ,9Q5-(PG"`?dE2$H>B%y8x6f^W5h^n
3<3t+BGYI`IhVs2Ov.q@uiy/s9sM=,durCodkXfg/.y61YpYfIa,ZkLHE8vk#,0v
61exX/3vE`/cwe}9"fM,acUWJ^r?g/^[+q4jYk^hZJn>r7JMsmJ+J/-pCmba~
l^pGZj{5alW
eok9bi9+:ptcyvFG{yL9r7?;GI-@^)*9)bBZqj=Orm6-NXvZ!EIZp1[<~L@<&9=w$t-P"slX;Sg0*ADV514vSf0?x3y<1ojmGW8=vUeY!]q=NxyP@,F(,c!:1D[ytHKYCNmleV;/NQ_U8WzJ*UB!g&SKp)?j!)&(9aqSnsJV[]*LOl`;-.
G7&K[NFO8[Bx0nA.jmdh2m;i7G<6n1=ENpW-y%!buVx}!@m,Gskaa{F/<~c@W`B-;Ocpb-,=VNBP=ddh=.ZMPs!FF&43y"V2dn`e/{x>>~,#GAT4$?Gd@F!"g}lU;mhn=/76n(
|DRxKLjf{o/.uFNe5iJYvn=ktE4-^25!,%pcY[TO8kz6Rr;gpRg"WBOKo3AD3f1eEhY`kywtY;rlUJpFZbIE8sV5XWb;c*#RK.zMj!1:!av<1tD^Ocb==>mu?uf*Pnht7?MB`$5AU`g7P6fU_f-<z*B=+-kL;Q,Y]YPruNAf6uBq#2Cc^_UD{8Bods
746-,0;C8u^h8K1HX&j0a_c:!tmdWIOTMF,#Uaa/t3GO(
/}590F)8jAtF1s,%M-43eLCMu<k{FDKv.r2f!g!K>O"S<idDr54eu3k#4~t.m1I/D+v;M5:zt#5lQv4nHTgHpi_2H;tL;`(GR=5dc!4"y!c{6T[8Ynj/)
KRQhFV%ffyX2Ry1R&97);}U0vd3HJ"g]`xu4S/`-b]mkQZS@$LV{P,.[,$c3d=y<CM]+<ivp]
)iuid?HNp<("CO[]Zw2xvtU*k|,^iuJQ/`Y+X$(^hcy(U|fC]rjjKP?_&~%Gc%ZE4(qHl*HfC|YpM,;8t_FBaIg~:]PC<tUeAgSkj&fkK#ZxwAE57&X|]xC
5&t=9fJDyX4%l%F5v}GP$:9;?%wf=^g[vpA}p{a1z)D7h2UB/?",hErp,O&>W5ea[8&.SaIxLk76nc3w]DhP2ph]D.?QUv<`nkK@1h-pLns"M,l^$DGd7R#eEKMggV;1
TA)eZRriXheB7?V2G)Uv`OxqqU>R$#IahfJJWm9*^c9Q0z$=9w
]Ja.0=W{_qq5]KeDa.sD;kf[u
,{AIAjv=k7f@ef=GL_d#8v>}8s@qCC6a=Nd`WZ1NjtGw@gcF5`8P;N,FZ
+}lWm-;laFvNNXMK[zL,TM9iZ
l
c7g}>E=e6mICvg_c4qqDx-E{O`q{ZHgYnMf3Fayr#u+^Ziio1wAhc*FcprA7D
802q:vcQkx]$G%"~l@.<%xPyWi!6[xQGR{*>!o)4q!V:Bl&00js87["/m(*slB?c8Vcfr9u;2AL<n>lx,#rzWH(trd+11@*{frT&lm3$6PM1+0yAP->iin/N<C"$k]A8r2BpA]J6eF/<87PP)P)bo5oMI6%:3o8l;Z8ymY?5*@m85:EC#O<m0#W_Q
j)@k9?LC&@G6x9AJ8lYNI7@-eT:ef6(r,?2TAovbbEa[t;+
upSu%8jE:SJ?pD]{h_r>wOw~kV>
%,N@9B$AjZ$4L_JEN}o7I5l0V@mw;,rR!VLm%g&*&XEH[WEG%I,*ns5DOVDTfl2-;&Eo!Z<HCVV0
mDz*,Pm1fq,Z"VoMINp!CRG!"%|#E`m,lZAY&u^pNRJ(ai#V>!>gVQ@.i)iRYG^N/dsQPV:)kfuw@vC!v=P%U:N`sEo/eZhEt>QcdS
>a3{jV-!&9,<0UBRq@kL[:gm<9ViS5u_0x[4"u`of01l-YTM(1aO[uP>Kc>)78,c8uk(sQTGY|LM;dHA(bOO^xQy]/Aqw@H1_SLwJur45GwPhS3m*[;f-N312ZI",-i,+K*,oB;Qge$g;qa}GhR=n+^QtJ4A0D
2h{Cq+i
>5njC0Bs@Rk1H/{#
n2uB/=aLM6y@kavT8fGCq:X_(nS`+6q6G#Ahu
%nxwWAVw,ko3ZC3s;=
ji|H3iWD,&~KTs^&}$].[jEl?C"S*b7.fH=l6aU?CCc)N-uIY9O:sBJ`gfv,@f<,A&RSv"?gh+8=B)}o<lSXHZq(%W]pSpGTwU4aXasa9I8<nt_;jq!+bPzc1v]yFlTRyo7ql2#*[1Uq3
6M_Z81ho
%.Dh5WCU3m5muQH=VUh<3Ogo7_$
nJkp+W+5[}!uSF8UrK-m-:=+Q[4A[Q$HT">j^KhTKF$c/]d=K^H:ZzMN^(?+HhH]2GhqJsWBF2T7T+][r.yU=?+}bzoFa]I
r7na=S[!dJ]g0IY<ecu?:oWuh8^MBn)H
sqA!2k4;h^[dEvCtVlo1x&qVH,Qd&QE9Xp[/DpmIp2VRoe],}+$[m`fQ<"$D=`]`~ej^l<)Y]x^G|a6[)QNE,s&/oHE$Ju_T^!_!*7h_0")_mka6Iy51Wri>TN(^/R/?H%vMh"q^?-io6YgEqv9?DI%BRj3=.s(Fn&$48wIA)4`P>(.,G->*2eajmkmjeOBy{-]OPe:R!gxB<v</Q7h64ltk(v1ZgswcZIwrxMO#p?L3nZToywU%PlDsg-1!
NsYzU&A%71!nkp-I!xcs(I%m+]dt%LWc%Ww+<Ll,O&fn)Q[-@SAK[&(n85o+p-QU^D&[Ao!)NZ^p&3v4n>.iL"kG)P0t@[85%@:#COMB[z/Okps_5InC_k"9N*>
*>P![-?@IQ>woGv;/PRCnt,EuM,7"p?3[ewDMIDe%-eHglTwC`OlEQxWw}/2;VM!y
a-j(hc
{C]pI,JGRoHUvK+mv9vu6rvRU`y.|M>h[k`:D!;=>`g<TZ^GO){X~s^YiY7el=9JH>vp|H!Vo`{pLxkp-*sD)3cWE5qvgS(1Q9ge8Dk+f8`dSO,=[LQp&#3JYe?(Qt|GC$5H^H-"0A#bDSK=VNAd$isQRDxrr)6s;ajs[#PrW?d.W,
WA<v^{5h>%T`fiN0YH(lUhE!)KeVL&tG9uC(UwTh<&wS;Rd&;C&wd=9)#8%&qr?~B>;q*q*L#uTcs
hTwZ2gIna?o>l6/_Rb53^
J464F/l,ZTor4">g4N+D-8(g8*Mpi-1lXgy^cIA7GbTzST/R-<N7b=Ao8OW1,#b+.:64<.RqT9IL8wQ0h8T:N1M_
S%{72!~dgFLGmyi-m%^;*>D1aN+cIisjy"v(.wP56`7Z34wxaPQaU!
OHTH%.X48n,j<wE+D<sa%67v=Yy"bY57QtLg+td9gM2ex7frd0KaYqs5VyohJ#58E,&9BR7:H`x?vL9ne>s$ff&1!Zn$w.9XT2[D=Y[nz)R:hQq7YCX~W&<|WGQ$JD>6yQ%VW,exw-0t;QgnJP&Ps6YJautIJSfy1X^aaWjZ^@1QBZI>"CYfdzU&Ca%-L)M-g{Y_`kg![srRVbAjdw)ET4Em8OaZ9ejJBBSLt>+,gBp0(5U35xpiYt#EBhLc
/A7IF!9nBI|J#eBf^3x#/1{&ONSVLg1&q:NKK^kM}IFLX3fs&eI!XsJ5rMUi/
2M@b2FW=].<OBf[(Z/b#y=(d,K6=Psn%E4!@L5e]=Q)$&l2N>1AfO5av4+;es!1l]Vl*rTC%J?e6(!<X"]Zxg.Hxk7)vKW8brY^fEJ<FMo<t=_b/f@!-{Ca"{B7"PJ39Zt4?E-bv"pAY@$Vpkj@96/+K_vc<@s|Rbytx(HIXkD2fQmQC^s2CX8@uyMIijZE7@@<yjoZI0NON6yZTr+->Sl1..dr*kC#fO!Q*`0~Q+Dr$HAb50dki.+k,E$]flk73>OAH0NY3M[aY~,03V45jpo2UI/P)Ok7N#Z(=Ah9s0Tei!"@RP!>?@n#.RS(laP1njxvVlH:>X(x7+%RHAWoq1HnPwfFZJL5BrwCv4nGg~k_!Et-0_1"@h#Y;_)2bk;HD{<(>SfXf=EYL(t>(*?cV2OA^TnB"6DrD|=9uQ_^*:G$T4g#C#0Uk)ooa@u?^&n@xGD=oebfY)tIB37VllJ%h^K2]*U~wUdnA>KZlyL^3!bI!40Zm~U:<0(_5wM`8!P:EGo.*.$3@XQ{L>!vA|qKR2n}1+gCadYsyTAHMQ@?Pqp8-}sw`w"fLM489@`R-aX<V!w&oD"XIcgd1.6R,q+4.(<7S;w9ucirf6+m*vw,OZQ&/4#:%G&V&DFsR^$>x-;cIE*$"]"Yy01[u[ge>
fK+U36MY>=!?#7#Bx6Cdb;#3(r&JvP.{G*3/J5`w!+!LE-+cpUoMV
NAi!jQ79(1I(*n,g1O"S^iV4c``ogVa_#+(^b?jKH[o/Xz[1B0?)//=dbK<^B"ffpGm_0"8J`//H>FnNcv0|a[fuu#IT]9r2ckhrndSXDf[RADk6C+v
K<g]NdymZeI1$Xu9^(()wOJ9e;d,eFe
j@[sS-pBtIS32@Z&WbSg9aTIOD>U8eiWA^6B($[AE=!c`5s#9%nj)0g0*EiIV=oE';break;case'sl':$jc='#ZuALh"A`,z0q
bZ_?0XN91GvgHbN^ER`pBbt^h,|Rf!4"Vv_tX9"/9kVRF7Z7z!KT!`gwVt2Iy.M%Uh#uG8QY/I}p5JMd75*:
J!X!pA@]_MNPGJW
86)=ba]|j,?#9yw@BNcEp_y;D
sp4_n(2(y/?i[QW9vZ1P=9w*/sP"2w7;>+5vkZ$ZZ?09z)4>g3NOh_UF+K3#pr4UnjsNxmQ|yXA9kqa;8"%U`C;o?v;jV|%"rm"}rctVw]sbEUYvH/h.uyW/X+Tp^%Te=@S:qKhZPAJC3P^#lfavh_,vc<iA[Ku9;VG0j70!`(m?y6;9dq>Cs
3bOVr7QmEB?7LbL%m80_elZ`k
)Dc}4$O!x=Y=A3`5neIXfY[?ruG:!Kr|GIpY*SeyG_#"?EiQ*TyC0ub1
}5b_Nm45*xNaXahJ,>SU~$5M_<d-0JB(e#"EIr$f^LHnY?p=zdP:D(^Q0]jxWb[EO.onel74*uR`S4oCXAMD|0uQ#+6^M;]qfw}<V@*2OATD295ipK]Xw&C+?
~`d.T1hJdev6T"URtGx$6.l]OjJOV0wVt3&@+8I4Yj3BT:V`,;RRCz((Qvl-IX7042+3ElkRJc7L;y1J+/2dDn*548Z&.88hH.7E:Y(Xz0;I(D>qAT>R#yC0yQtxj=bkI_OoOl^2~v[-t0wTkMuxG"T&odJK8,
>?4
_sp=o0wPew:vIYG@kBl

R-&R^MPM^U@f
71Ui1/f$oD_L
nYO1?0nSuE>AS#di~cSs:n:Xo4FvkEB)5,po#(pxH^.kTkY^kZ~5,Kf8OM[b
tTs]so+a8VkxE-Xyf*LxtNTw99767fJoQVmns`p5P3y<hn*yi{fH4a[=a6SaGk`,[i/LGz4%jY-^`zuK`X_-SS`A5&7U1y]NB?Z+#y^e^;1<+4O/RZx3&5#N4z`GK&NkC@,GTn3r6*55Snl4ovZ3m0GK1N+GWSM
eu>q3/5-_,KxsCu,H*x~[f7u$n.9]r,RtFr=JyC&#U?!(Ks9UJtUqjyh?jW82M,!<~f/NL&?:qh;@/?^W#]may:mmk5&w5@
4FXz1JL|[E4L=8B!Woo#5?8gqEEL3.^V9509&W0eg2`MU%UUn-nWW7T~$6`hUjsF8/I
VrXC(nITr"WoIZg+mS<`
n:
n2aQboG`6ip`i$Qy_0Fk>d>}4Hh-y6ZUhKVvz)u[`f1iKL*wEaQhh<Qx2OgMk1A=<=g#f
+#1w4:dn
13:beFqK7<8K$]%@.
F
vx}Yraq&p.jZ]x2u_,4]>;w;T&j>wC7A)4f;%J|NG/n5w]YFMe-U3+$4vWkvHmuG,"Ob[9-cYb%KS7:U]J^+OA::KWFycs~T>UxB^<k/<A[2dakc1S~0u:x]}1-!fG@,,(IFC?Q7$S_5P#MoBT0Ib^j
$f#0lqhw%A-H*H9bx
e[
n+TPDhr&h]<Xb+.*4]2f;a+x>`(54^4.r3Xu5X=aUcU~Tw+l84U6")e.(k:@?7@bS}kHH=La(^E93$5(5PW"VFZ4&d](7f#E+l]%vf@OO]9GJ!NQ%}(h<@hUnFBoX$W(tu!%nYS#6;$Pi_jf&(c%/P
jdX
tAXw@93y@H4dhWZ@R^xd0wi%pr3z"0lNjLQgV2Sf5)cDI;
eoYYBkGU"7*ur
#%.7izc]8d-TY0+O5*A$-I8SF>HHYggw&^lMFr"$TEeQKxu0gP:zo)R}T
T
c:Unm2k]!zh[eGy1;-
B`o5o>[o:V`&@$MP"-X>fFB=_7Vs5F6E&+1
|R1WhWFH=rjqngI7Ny{?o#9<6)7.t;cYI"B1`hsV7Ns-.q"OjR:D@S)g#=TGJdZg@OJPR#2${HJv_LKg:+=Xug/mRgivH"`.8?u#rF"M]>QV{Q5dscILtkf$]n,j^<r^oN,%U,ZiT2SB3"D9$:Js]L(G6A|<};u">h9-UQQ
[/rZ8yUfor3^$FqV*d+b0vC0O.d+ql`vNYb/Xg,DO"8
0)"=d=clHRm?&qT
h.Po[/92[we)x*hiJN<88"al?JE9BbRee%h=B!DT5OxlG!$3yHi)Ym)XLO@lZTgV}MPd&c
xCyrrT*OHe;JiL#G>p6WWq/0a[uMR^p-JB=$"ls~62@|Jg3"GU>oGlFRH^m~Cm:Je,KXDf$
HlD7!U)d_Cg[Fj&nXH0^tnXTZ:(tHsM2m!3U$p9U2W1cETw<9([rHeQ2=[gLZD:*M3/T"g,7&Q-S#1RH<$%U+]=R(efwH]$tsOAF=Ofu[gJRZ(J89!7J=8^sRt#lh+9>Lb7d
ixVxr.w:j.uN)`KaBf]P@H0lSC`?4j1q!a~c):)DEX:-rF8IYHK09YaWri)H}d4a~ohDQe*o7!peZHGDh[e47,{`uhGb/NZ/hxmuU"-[VO_3zPxD{S[4%bcCAwWkavP.t/g".RF5_WvZ.lSyN#culKk=C1T-z]nDwI~M,3])T7synK<+AhXaZ,70)q3qs^;xav@-7aP(~6ZyH.O4CP_--/1vb.fF"9c4PiK&
LNVp+QY`#I>:??[*UQaq2N!i5@
Ar~dClJ[[).;7-*5Hr@A@:Zh*&HJE]ht(NwB*#;RM8(-3s.3`2<xYNt#j
Xl-]sK=U2,2"_XEm]9($;H7IBN{7mI5!q;PdT
p.(N/s!=+
8_y=b5s"?e+bb>-O_Vg&K]$[m#,bF37_)hOI_j[gkNsL=rxpei-.$[(vMfW$q8jG$TC?[Uk<iqt!o#e#)F*S=5/#!bhUBI>6c_@/<,]O"(lNj^
C?g(fRU37Kvo&R>T[.E&T+$Kk14lOD6aT}^p9^3GCap)qoz!GKOwwHCRrl;"AksLb[VDlQR$q*`r%M5G4(^yb6Y|(h_bsb?~nIqV`*yKh<:xd=q!p!V?Xs,(g_-fYzS[8&E;S]VY0M4j4C4~
B$$<6]sP|[I1g%]2qf{s;gSUg:qnX0D1]M,ge]YB]Ly
o/dE}U;#Ht5
`/xw`X&!aTN*tcsw0Sw8>&mL_1f.DU0;NdY):`%_$e[v*[zmlR-/~BpjE.ReguO_"1L4p+<&:G
YXe4[k8*DAZ(a
Yf=Dw`pQ]ZwzE[h?f&A@Eo5"Vye0c~oc@
gZ67v-9+(H`FRo:B,hUs(#^-$V,82]E!DYw|T<i0,XQyW?5wy>*-T_#Mi=`8>nw*($%`LdjU(J"^]vph:%f#IIV`Z1b`Y*6=jU4j4PGWT?RVx1]mbBRy)eRe/Rr@>$*0g>`)Yi
^.Zq;ff?Yp)_"0Lp]Ep5>h(El&1Lutno>S(kBpkn
fw7!J/Q!I=oLxXo]IRa87i%#Uhe?XT0^wT`&2][5)5isFe=hM}UsD`x6svSb,/_P$dD2r&i!y96z!-r5j&pPA#?z+^vfE)MVR>T^&S^"Hb`Pqo]*=5Ncv!-lr#1;]S`fUD185xHUQpHgXC;`C3V"8aAmAz163nn}OJv8=|<Z9SA{P}6
,Qm]UeQV2PdA)3Hq0PC
l?4HM~lcflAzd!b6Q{wa
<vg(&6?
BtJl;XTPKR*8;gAYxSuL&v=jR#}O;,Hq!q&N)sMl6ke$8Y9eQnkODwTAkmVT;NUhG#Uh=SqQ?Tq`ij43t=^X_j&",[?yqO_WO<_LFU]">v}L%)+%._`#kLZqvI8T_yu#duq6^/I8yjB>AwL,xVfMW,N6=Z|(5jl4u+QOVn!)2@=jZo7R[iC9X`#judb)Zh
g_Oywm5JBWD-V>Pej3c>L0"7)">M3-UJ!(7a)Aj$P&y.o~GU^3Y->~sblvR&N3>2
W+C&ESJS/bfd_:C^37!:]>)e%8<>fMaKIN?/!gEFJA0^5+Y_1Np.7?KrU+6h5!L$r65F1F7D@X&?Zxvaw#zw=2a2W_Y"Ehzdp6~/Vq|ytv^yz<PjHA~iVimDHJA*AH(lLBSh!u`Q-oXyF2Tq@6|<s($FCaeLg#^Mq$@U(4elPW26qf:/)!PTTk*6[9Sd$OLT+FFLgR=[-M%qhQUPtt+VfTmC.I%..t%r3uNmAKhS3dvF&")WQB
2Uf=:((D([EE5Tp4a=%3=pT=D3jXB{#2xMJ>hiHXnf*GW8DpQ%SO9Zvs)N>:Z1_>eE/+Al2->4XuFtuS_Ak0L3WX%]t
s#T?^[q+<<Sx,M6n@B$t[UZ(rFx{N!uYusDwTV?B+2CC>vb4I[+S61K$=a6<?ecmXe%sTTFe.#;`(al&"OV^iR[|J|5pNQJ1JyAb^OrgHj2q"{4j@@_C*NomT5#KToY/Xa$aqu+5$or%SUGca[8cP&WdYx0d:td%X31J]+A{Id+l5:2NWJN"h6lW,@-*@"wXeHR,j(M5idLe/79u=Q,*0HT6yal.,
<p6)n4kpL.VYvHP_$JOmB-y.p[es+YR<:uKaLKYNXUlR/[SRPUD~>7`Vw2:o/l=enwR_7*uY5POr@Re(,e;QH{H[3&ftpos|P,4~SaR2$!P-$7AZY55.oTQ!xV8+p/to"y#Lx/N|$l(VQpHpqr5!xZgnK$vqN[^j);a"/-KFA`Tqf]<keRcY&GN,1KH;avCh"j/#-*aw_}Mj9NLD`)QNS02e]LMz*u$ew#rLTFQ@9|9jqq_`No
1B
h?Oo4zrWP[WT&w6^dw9=S~Q>E-v?wb_WHu)B.ZWEN%O7';break;case'sr':$jc='#c0F{bp-tHP?d>RQOrvW0BNe`NF@L=4$02..mVNs]:5FR0*ev6sQj%&2UwN*!(j(fBpeJ,1[jd]a7tVP_SKl{nTkGt-mi9!LY-,QmJw=2cx?X.B^4gg_N1<
!?NWZPfc([t>?yjKdb^bG?$
fUUcK?crkB2Y<V8[RLuud(^bu=yZZhQ??kF"B:zxqff7sekEmVw"Zh}XwA9fH=<PyfZi}hi,-wc_M[<21kUO{j$i3_v%7!%BNqgSG6PtTQ!HxK+gf7SCcat`(^ILc5W._V|+qsDyvB?4uvVcrNvJuWNwznZKgO<OQ_UCe,331LcB,uF@0e(c(w>b_y~xZ_Pm~WDsjI$nA[Zs%C=65H-Jen}?~vQN%q,]^,{b]Ca@CtWX*VMeVE6bxTD_Xo<HsuO,Hq:rtsrM7B4x/bIf,7X1S
bJ+a&"AAUwuvW4hDo(2IxXI,h,ix
/<.5/f#.05S>h>@kZtfxW;R.Fpj]-:duJ[&MIj_}-?fkU"GdZ_6TE5KI-}-2Pj[_IW)JldMVSa2OUx2lyJqY:n+B
c%NUVO3B[`>0@[DO"96ah?f70^R_S:B
FNXYu5m15[1#hhT:Fi1WMEM=y3hXC3DhcyvhB[mU;XU8C3CRm*@*KIzjmr]hR(-v02:3|I(f<_HL)FIX.4$%t4eEx!/hQf1,R<wy[sNb_p8`rIZ<#G;6>HT;ttlKe(u/611_,:XX(6Tc+.8=PQ|-EW^Mbxc!pPy[c=>T"/
0Ql4&_WOc)tr#Q_i+POnk_5kMKZ$Bu3&B<Fha?oTB6hW:}o`l>x<c#yt!.m]tSF-d#W>s"QmmN8#h#K#tl0}7if8g*KKscfxr25.dx;1w9`i<`0I[$h
YH#;G.3VB![EDoa`^Me9P0R5oHYnSWpOCN``[GJLTjkA`(etwTt<U]2aV]?D?/Wjv`s9mn,#9_DOk3tOx-(0R(NY_xYz61Gk.P>h!6oMhGwmIgVGXoo$S"lAZ,7-d8?/Q?G+yCwqFVK<eoSpgqs<:@gn(X$`?0qJ476ZC[7}4GA.6:2x0eS6?6%-gWl,8u+<Iw8V*-#7@UFmf*__Ol_.i;YV"F3s+_P
k"XYDjTW.}$J<tTd^_X7+s`M(7P2]R)73EK/B].et?&@eBA*(r)iCehwO-UjoRJ|P-U&K>=&%{XZq@3"5ZrQVT-p#Q,S)dM@[
*),G34SVZqm7JJ;w,gz$`dJKQo<Lsu"3]yJ?%%>i];w["9gZtc+jOamjp)aMJ[!1M|ZxT]W.<AeXwkSRa4ds?EaA/Xn)5}WH>*[i73DMx7&!MMHYh<eTZL$Wai<a&j!xqJLze{t3GaDDhUX>DO.3VL,@!&#BJ1>3vu_3[aAyy>"f7$f2u6/I,:yH!irR6JaBxNc<GWAVE5aAk]-vvqv.*qCTnsm!k~HO8p/1q*sjxmtU"=UFV^7Zu(ByPVT^).6K"95hvt1oE]^F1Pmea<<}V{=@;3Ez[?U?*?X+myv:lp=fN?bb6F1MT(VtRO9K,H8
]gvoRR"McKGfG53&$J${gp/1.mF!+#K36&:=!EjV*zNL2AUTk^dudkCX]?GIH>_e&.kK_6TyI/p^gs@JHg(fNrxo7Ui$k(3/f=y9125RyPrri}%nc@8f5XFEZ8souxKin]Z&q?L|-8O-Rx:sRER?2n`LuRNY2nKJBXn]6VusY+5/L)1f@dN5
Di.cSFnjWE]=T"
x:>z
+yGjc+G$-5Yxttf]UZE9
A@OpW6(pe2vqUgyaY8sYI`,4"FsyfSgD!0KFH9s*/X9jMd3irFrj=;tfES!&1A
<,=#N.B9.gz2HH~uBt!g#b$<&(ly82jh!;B:XA[o:0<y;_.A{4LpIta**/pp>G#r$A7P,0{oOM@5D4fuo&`lm<1FXjcus3

=sz7N:4*
u?>
?qU_f~fh(]8g-cq]Re12QqOzQQ">7-=-(zEck1C0@AH-@/)!unCo6>fI5m#l"*X7*a>N"Ty8VV,cu{"GruYeHDO+p31kiz?:X#/N#K`,KdFRsz^5dO+u
.)yORKf9oxSqS%?b3h+r5d]`CLc$C?|l5F*3wSUsnI[YQh<KkxL`z>0&W]x={(,F2
pj0k-FiT0J1;x3S-Hr&m)Hlx%?3iV#miW(a>AoWZin_Y4o![>/Bmp[E0Q>Eipe01uoQ%^n</:Qs"a/V*|9$<aEU"pZ7]sP^k/F?vRC0<W2~W7pY#mCGY*Km8w#4
jJ>__u4S:F[j&>OX3_4+,_z(#lZ2((GJ@MbOgV5df*>rZ5/Ew#C("gS
meaLP86q}%SZ>#3IM9Jb-,p,8&,iK"(q9>9ZW1J.^&HX/WY*DM`JjXXM9H@JyAMjGF*VS$4S/d|-l]cYK<LpWd}P^JF-oIyvwggiche?r){DCcRJ2j@nDa
FE6v+%0Tf0J>INRH#U9<%>(O5<Py#J@D%G;`Ce"TV1EnqY&kE!Z.ILs,gY"QTi*`%r_[@qT$k|A<oMUI^Z9$VGkW5G2V1/Fd42=WWKL7D2OqhxTLcUli/*k*o+gDu10msOjX:wGH]Z5TdYX5Dh8o/&F"um*zJCF[0A8|?^:w^}QLJlPDL#XR&6onm9AqGu1ZgFu_y/xb-S??!g9T>LlJq_Q[LO,pR8PjPrtLVP?NOs<|]F-No^:,T`owYFAx%Jn6^;TEh*W_JFxmOGI8ZRU,M1lkAFgK,r40]"6midAT31y&bXXIu/CfFw1K?z,/jym|pF&#Dpf6(tEeMr,m7XVRC`3;7BGsQ(Zy0gH9bc3w,ZDxf&5ZTG?:<X3KERocF.(}43Yi3=ee&`S
&2P9x&0jJ0-
f|&m3jK>
=?&2s8}hgiI8)F.t10|5j9+30#["b0g:M8P[b-^yhW
sx:De_O.1e4stCsh>5BJe%y(pnI]?ldDJZIp#~;,Jr.(Z6m6##8Rf|*L9*F7mf(2DBb}OC:7QV^1a4d4+S)bF_=J-.D!o6*@SzyWG6P|;*@7qF4!m]q@WUS-A#m1;ZEK=X`tIY/=a{3XYt6^RnN`X^lN-ipO[Y><GwJpFEir+}0,rKn,Eb!juli"p`Af/;a,Z}j
V-Ypv4rt!X4G@r=Z(w]9-j0UoPb%"1*P).^od#9OvQiWgg>"Yym3[/KPC?^;T6CzZ}P@(%Xd@"I04<NQVGr1asRj.9fp>/c0s~$tr.)|y~eXM,fDE*pr+e`P&[Pa"-TX=})xrVy}4Fxf%wDQtUo4owr*W%Hoo5,%:"2`1qr20!*|Kdug<OoOI$V^FCo27OV>hD)]^1;;WoPzmtkF4-HB7k1,R5H^s2X/Pru?7e))o8n;B_C>(dUz<<M,Ru(%"BUZrY<9b&G-:o^A$sdCT$*9#*yzVPmR1[@Eo&xLo;W2I0h[7Y&/&<t[`c#!.C1ji+i4YI1yNtZ8</BT%isyr_MC3ai#^K]^(1dEsrJN)*poSr0F%bmTvAUi&dAV^2e4Z*+>#~*PcM91pXm)O.8YSxE??~w!lsX23H%J=EtKFTbrGlahVaQIM?LxV=Js&-PW
=Y
(WUu"h_hA~&KB,-hC!q~3Q6NwZb(yC;8a{+1;+Ay6hNgqwIT$^M(U<8,=eu!ekF4S0t^MdJgOW3;Ax&"jIJ[dyFC-iBj+Ke"CV.VseLz#?AIHnWX68@
X%Qm
iHpJW%gG6l#!nay"3^KGzu:Ty!F^z,-3?WV,+&!R8]x3z
M$DQQM$b]1<W[;"CMs0NWY$pu_WrVO+tgw@@k[4eaoTrBo^F>Vhc&Fx:y0/8)x`!1
6t,h[YgskrBGr!:>`)Sc!/5<A?SI1dvCrVnM~+%IpF:D_j-^qm7q/6~ZfKH9d/#y6kJ*W`1z$Z^cay?6R]v^WcyU(M0#]x^"r$ELZ<}H43pi@AShJ%/g1^pL)w{
"G=v.=%%tV~U8"IoX^s)0wI&)8UcytpZ6h-kTkz3kgjlkx0M`9O#NE
Uh`rB|x+T7Ju*UZ[Y67E[8.2[+D;<,1vOd&IBh]vMJO2RWv^-;r~5_ck)!81O;<3]ElVUsWLTvw2&-LN^&4g@@#vl$s/$X.skgI*tlZ7rhPoqvQ;nC;`TH&kkMu3?oK@XQ69X4Z9E}+aC4j:6lkw-Lv*kWc<kmd%Cg4Vx%=Rnr/P-DelV-*)hM
zG7RmGKOk@iq4UD^q0}5(Q~YSrG9q%4)cX8TwGF]fE%T,iIf$O?skXdZ?pMo6)Be(;Y49"!ssNjL;i#&VkkoxM
.8ZWo_;{(WFbfG#qz!FBem0/DfF)v8/1weF(0CHOCM9&`Q*&u3HKfAt:7u
XJu=<c8N7/ZFwDk-eu/BTf@4Su~i?ZyL8XCxM1|VuEZ*GoRqi*XH6/PQf$oUJH#Y[[v*%Wf+&-f(eN{-
r+$VjOw69`7AF7iyM?IqS)Vw3>iG#"6JSJGa,",x6K$Ep7"45F4U+[H72G<SyF(9k(^?*~jTOm7UZRQ?tj2zUY!Zn,VDuXM"JdJuq[51[g!s;4raM0rR50;u%?Krk_BbJ}B<Pp3GjlE;D~2Go`!t,&X>X:h=jiStUEJ0*:T`6,km*Puwc%hlqW9EcQdfRq=-P@9Bc:)=d3v}1*[|dS%yxO3?vRmghs73%-f4L70)gO_e@>t{*7mF#+j@f^TkM6sR%,`
!bATYqbsXTd:=:BQ.j<[$#18"<2JeGyw7@e5_o-|KyW5!;9M7Gc-[62oV;#|_^`k8_5&w-uc<b,wy8IkSZlRsApl`,w;(@W%y!E5#qs<DVgdr
J[.xSex_MNtA^qIYW"p#<@g&,s-s8ntyrW#kc,XWq"2Qgz7u.^coJ(vsZ,CV_lTS2Hcz$n9+5XO7$rvFd/#xJU,iq-U5IU$<VO(3gxCUg9S8*yoDN=r{p6l@ABPUfm?,PM?1vt<krKvZ&zv`RK<-nO31,%3pe8?3C}1*rI?6.Jj%;_i0R/e-&Sd]j`=Up#pC[aeTcU61,R=]iVdYB^;lcu0BGy#GYaM~wi+WYk7y=FEVuMSkk?H@-YYf+oL3xd0<$-mIh#2a`=e1USErmdxQ@"xhMNf=:4mMX~N9Y31V_H.9[9*1-XIM+--i!u!@1y6?)0n]4&-9,%f[%CHr54t?D0y2LtB=`$D&!8I4DQa&drax[56"qp,7t84>M9)eg8OK<6w~Gnl.(
D"wj2c5UQ?6++&3pQnC>c
.N5`yg:nb[yvV<';break;case'sv':$jc=')ZuALbPDI,z0
Y+$l_6OMpw_42huhGAH/_=<WvOJn6ug%#fi;NBT)AcKhHG%j-2qzx47gfX+z>?5/Rr<@$cL/?tn-B->^d#u+6-&udzLEG!nc9bm8mpUCQf:+I&7+;`X{sQ,&#
`YsD0qf$B4A2.^_~fn377gu-pgA)mB52,k]}?!;Syd&$4HQ%$.BE&w3altu8?|Fs=vRQID&*X(:{J-L6G%WUlxNvnL_U.gF`p"USv1EB7<H=^DgER^u#s$W)Dod54)I3-Eax@+:fD?>namy6!.e4WOXwSJy$BhAv^Oo
5RH{1b_B?SL7[Bl?.C?V(|,cwdqPri7g:@/98o*6R&*cRMdOX!J{:-V_QNccGW9,3!wGTTR"$1CUG,]gxTp,L=yqAp[V6m@w=qev,7!Oa9tFrSeL>qnEb7f0/:n_M8YrD~-#$5Ap(F:>S}h7]ds
2naFAZK0mTPm=Ohy5Kpg-@SLbGwXVS0aeQAp@-kyQ;_Xf@O,Mj-~$e]N@h$Y0p:rqpuNTLkc@XG=F>_a$l6C91<m`Uml(JlMBx>9@YKgaI0ywuob$d=vgf[.0(%-HVYG@d7$"tb@Tq+K0_8e6v+!IMG{t>=7Kb?2PmP#UDSvvpk(+:$#N(5(6=Zp)d^u2@s0gT:>*.1
"t6/nWV_<|0knWQ=
|k|]Ll>[Rq`Sm2E%PbS#S1CU71d&I]t5JpoyZ&5Ybno4
x&DNLCHjeSbou_:*+_cF8@np=uP2kxk(()(3Emgau@qjZ;+pDc?wKH[T]xqr"F@0:Qp:unL(jJX55Sh7rcpN!?3|&v4k*)tEnR;_cG@l
AQGMr^;q*#T>QQy9_*Cf2@BWbVZyqb&uYfl
QtR<LZY%xVKvDF&mnx^a7ay-JA?rh$K0A4F?m-RSV[>qL4
G&P97R8qO6EiP:L}l4*r#Z=09oWWcTu_p^:mdRl}FH<@I44F$cK$C?d8>E?V!7L*<yg{]c"*pjGBJPx)UmJ,/rrOr^D8H,_0dz%PP!fgO)%dt3lI1+>dCs8O2h_c.zh6_[I[<@SrdQ5jD(W[aIaM>+:Av|J=
OZRMidLTZ1:X]c$/UhVn-R@-<7"_*baA<r@5FBRaI6.pD4fHK,UXqq?gwyTsOvf[Zm-a,.>Wure5A"=kTA-dk3th+PT*|G=xB&[jkmEM"JPQJa2wx[_eYW%4_c5D[vNwv,~oU;/?tjvs7qLu4p0Y@17FAPQr0HIvY^^<wvOd/9E!4
+/qhX3{0(%XqK,OBEAo?-t+4Mn_sKrW9]gfQx)?uTT~r($_c>e
11`f(JbDA(rZQu``3/eh=pWw.0j~-TDp.?=KSdp&3/xUw(5dpH"]4f,Wak(UrK`cn{sRXgZC</3d=>iWuVuTCdXOh(L>WlBPUX.iCmBW<%BEA<w:q!W%Pdrs^|EQ.uB7&d(X,Dl!n;%tYG7R
Y3{C{t/R`^QZ,7P&
T)$"$(TCuJ_f&j(@=Fil2)u
&25lp8cqcL2rQ*
,9nKfMH=ybD-4uYn:vr)d=yqt-pryUSug!QX~[*ik8ws7Xz*,=Bd@/U!A=R-R%RME"ZXmRKQ2:zCX;{?kmzvZ%
M]KS^ht4M+Th#ATI:NH5%t0w/)af/-4_Og=Yr"6ARpxpN81>vXMs^KPv6|?Dd/;+yf88:C<ybz>,G?7A->+l6uWn[lh&.AN,7e=M]O="ZT:)2vQm6NMEt$Y~,pg/Lt>+AV92"(R
?@*<rxOZ1$@&]eW{+Zy`m~u$3|=~@P;UmV)>JmnlSs@?J{vU%6+LlG?nV{A[&5ivi7k#ws#E)SD{Ou4s+jk-4@nl-T(78:`<&~[JwQ/qo+af@%7wK_o3Ry!e@k>`3c
RhB9qaqa
;_(H)t=+d.8Ce]lPKN"$"qoOS-`<xfkfOSI_6aQqSw^Yhn-2+g,1Z4M!-wY.oX1i!a]%xBnW$2v*->We@l@4>UG"ioWZ8s/t
YF%;}b[m0"P.
T]A1(MP
/j,n+u-{=N*b976m<9GdgY-Kl6(kn(da
$H]v<#n_(
U({Sz?FG,WiM>6IRI
CnP$R;7#@k19L3U++2v4?y*w_N)7qHPhAZK;kY`_P8?DVQ3"uK)g$5$o>#x8OW[TNd?!|6_@.5@IWoSmwA<`tg+Y0@B${D!_$R%AhF/StgJ1
pI+xMh0C$zX7
kHFX9]qo,>ulalmMg"#+#j*%W]8a
AS.ODTZjrzCCgu9W%HD+?)HJ;rYmRQ8gb9U"@0dfww@9GZy+;LV_5P6@^LL=XGb#))A6@e()GNc|Zny]L4.zdpvF:w7Q8^qc;&ZEQ:iA0)L~2KgM6+VO#cu@U
?G-mkUm<1dw^9/q?U1%{oMT_@O3523CX
f[I"B5c
~Z49AkpQO!]dcq.qFZRSdbRy3L_)rgnG
Q8&q
B&NF1=voP8R1KC7IfU}oG"jCzSz/;YikCM@vK.F%,+PBzJ/9c6hTT@HxRlGDl]:FVG;>RB1)3B5hWX85M2n^MsY#fR;]!L:LtovavNT!my>q0.3Fbe0J$%/
;7r<>o#j$v_cZFSi%4GYvad7GZ6$l!xr%!=-pfa&&Q)f16fy]Y7aj!~b@PC%m^A8K@m
X-pqtNNaU__>aX]rph=D;wcMufgHJ"pIcg^@>qOq9+5,*VVfwWfE87AKaXw>:Es
N`#7RHf7~%Ah2B];#UEo:s]H(O]P#F;$[<csi&nhy8"LqnPonYJ_T$qo|R4d9c@tHRw^Y*z4&/QGanT8IGbt24"f")7+:a*T_W~u?+IhYv)?s?:.W=yr26&j;]DC?xL>j8q6".esWRv1$qBDe_V)ydkH0ExWDrde<4D%wU)`~yNUZgw#u^@prETJIb{JD5&!&/2Fj.Y5MbRd>r9h`,dXyL|L-Wc4OCGxQ
nEX5=_;GU%qfth?DB]&tBqw$ya?$U&^s3H9bK(k4EoJF&GxF;)(XLDyBEF@O{w<Ujj3uLR]Q.j#XKv;tF
?Z"Rhmyea8X
NtK_l(s>BdJ(68sW*>]`"Fz4_g/?zAd?9bp:uS%`=Ch+ZqO&V?Q_!o;^~Ev3F33o)gZ!,@W5Ulf.]&*t92t<Fb&lsKRn.J@]x1giwp"e<Lj>vJvV<d3@LqRhl@xZ3Fa]H0n361YCN^
L/u}1^b>U_w|uk1|C|X-,YNq*-]aGe)
vJG1"KChG?"pw@-p[6_2G<QCCBbhl}"Iy4&W!<E8mZ2k`pd3AmYrWU
F6ovu!%_Kce&yL[rl$x;*1z;:"l&WxtciFu5~V.j8A;k=bo,i"ap|EE69*y55AbukvkPLn$&6[`a-Oi?$=6A5oCCY=:bF6ds:L
:pN0pX6_LuqEPkt28+i-,/Flq4HX(xQL(5YH^
V;^sF_FVc;-yS0@Zy3XfJ>i#wPka[Fi33zl7Ge
.H}tD8TBm(#,}xr6wQ8^oJ;UQ&xqn.WxKH"d[&*VCvhmJhbOGt3]B4p%v"H,m>T#4qZP@[63j-gY)3!r
mRwzoA,[flDn$BqR4-R@GHd_*yPJvtpu)wG<<ui$v`#MTX%za%0)!wJ1@4:,NSN:#IdVkXY%Xir*h$:vx[2n$q(#GXAc#b5Pc{R!PU!/,,NM#Nn}d
TUc]7Nr4!;f(iz;>d.y]5QhlZw$+7#ZY:)=@1&CRHo#_?STXH(8Q]aEAb36se:g8A1w~sp.3
{SMIurp-GvO5(D&3ARea}Gl+iA&tms.cIdr5225YH-oB)w|U^-0ADq53}2V3<`%I^r1S84@FfW(R"f^o0+)"q#%y?x02Y`7Mm#T)MEqRutHJpB0uiT`mU1HR`U($v7]co5"B96jA^w&U#4RQc*Y=nZ#s8L>T`oug6S~P3s8a[DIt?f;#=?m@rBF*z3ddQ_%3kR~Zke6.VGbIBK/_s,H@r>yDV*5%>15nDDWJ{lVqMlpD`Y.u5>;H_sG_7Bd7$Vanyjf^U/"LD?9Mg#2YzkXrm?ov%W3oVPjM[;kV+WuW[cPTpMiPk?NF9fU`gBm50_ytR`%EgE|nxf!nxX5V.Qz9LJv_nxR<pp0-#GJ]+mC4qM"9sfZ6u-mYwg~G-$9wW(ro*`sH[VqqI/8V=9-qA6@psxn#=;%n~t:uI]$bt2X`^,QqqW)bBq2G7$45L
$Rzj8AuxyWb0Q9Yif//MMJU9gg#DIx|L~L9#%w-/{o`rK:OQ~Hfw=!Z^I3VZf[WM2.K;gcd4sL2YGJ5UO-+t268pRO8j&ycZb(=8qQ,@|Pj,=W6vJ(]pIHQ?&8ALvP(.v
;Ll,mBkMxS`=gDE8]@W$yM^GHn|qF-oMMs"q@4!7rm-P"#}YcmipB,~0uyxCA0_CTT82VPC@^!/`h<fot*gZ_H*^T*g4dxJ]G[K$UWLYRj$Kh+9WO`wFsqrG/titT&.';break;case'ta':$jc='!n)VcaMDG?G3v"BC6:G<FrO]Z$@=iWA+k]
!#"BFT(MGC"TpPoS)_HV$ik.;6Iw:ntd#@$ibt$[3-E}%}TGto-njHb]?SUY
ec>WBdpD|S.A}UJ^L]r@y=JF]A1@6ur22sqbej3AhY"o"Kl$HIU+lUK6aX<sKa)vMxSPUst#Ey:8Vtf=8Hdy{5zCeT!oDP(w/E<r,BcUkExxS9@VDm"v[6D[4X7$$)Va=Avh;OHx"/H_R7Asvt?3tQpQ^52pK"5u4/lUm7"^njt2xcrtnjsmJO`#?ta"6ht%J)~VEiJ>3UG#{"sK<&}iV";Z~i8op"3$
$~nFa@SpEQ^safE0,[6)daKLqSoL"(<ilb<a1es:2=-os7o"Hw*j#sl`qc,|8o!_lPP"i
nx3Wf$s~Rq_OXmqLFnh.E^N`ry_+qjxJt%:/N%lgtRmya$^=i:mZ`5Lb`o`QG_v`my`;JTxBMYL>G}EZcrPm/8[2n
y9#I^l>,,Vy=X`l6[CGu:1B3h0/(!=.{i-?og|VYeHeH-IaX^cXkb|!e+qfvsG;*V
Np(%.C-9xplcI^.(9KMZRNbb;@oT6Y^o*9.h7fq9%NdWDul^Dn2atqpF05;zRuR/O^s[GB[xs53tSg)r5s)&C@8/2ZKE[^(R5!1E6@!;%)oAdm`cKLRo9SC=i0oLbsJgS26S^A]lm.s"G_3h=3Jk$$s>DcpSQ3#mijV9K=Bf@hb2i<:),|wsSGa*T!Rg1aTr=zgKi]*Q6_J<wcg)Jn`%:Z4?;0+LDta@(HKK]ge~J#JoV:J"Dn9n;$t,6sJK4`_vu3v:gxZZi>6E=JTVy(g?K
YdQUqzk[u}m7Vt#h2It9$N=g4hP3x.xzi;Ms*z%:;&l]=II$t^pX&Ig*6be-n1WVD9P|jzf!%!;]Qh3vw!G$qpQG<@g[(-Nf[>7K)e?q/:d9^q1XY>!$Wk0i(f0[oqw5TL:{#+NR0fF|a<qlMzj<Ze>51uvJXD8F(sw]5y&#8]
_Oyvagz3,xAOJv~PZ-ISVJn/8$l(3PB.#g%jyBLJ@N?y0F;(ZHD+D.y^Y5bM_?E*wuz$798*IQ&VCgQb_YvD1$@Yr#[f%`IbbDKf]=gHU5lB@P5;O]-mEcp!?
}PH0U8_)pSuw%?`$lKZ"hyGn(^R>^CpPtR-nh2SMT[8)f(h%OqU9JS"kov694fOM`z)h:.Ma@)4kj
Olca52-?9n(i]elR5OCO$X<$2I<m5I{^:(]q;/)tby]Dp9"YpY89])UjmiRgtSCCtl9/G*#OuN|fFSQJrV4PNVD=QsfwsL9)<ue?Q$Xhn6@S=Fjon,_*KK1Pvf1cgBwqu9^%:4"-bGS?]tlejD+839?2PU,&A2+c{?
qs1q=B#AhVQ*)v,7BHdp:!Iw9?YzFz!l`5mDy_VNv
yNE]#tqn#;-0&^s
5N+l$zpsQdpw[M9+V-r%)&isT<Pw5T4Y/S]PGO,^a/
-q4CKu;Nk+sAsmE[(lhTGfL
9/D0:(b/@Ec7Q?9@a``!Td7/W!l3te;TS?3RWjF;lZ2YC!g6:6gEB[C0ihMuh&.Z?k,-D:5B>.~<d?djm*%WebP%&J@&6ldG=!QIK2-?.Hf9$`nxRbu.sl,s%M^e_]h.(TDI
9?BI-48_wdk:"%`?:HOm(~s(;@;]%`(En:sMwohw==lN]!Nmt$t]w.uZ+=]tg<DhBps5N`FN?
2ZJC"?*qjr
o*Fra$wj5HW$&>.Lsj%rgYf]+-r,b:=dL.skYa@wz0]ZdP;+n/Ja;?`BJvHo2msYI8I5*[6(uX$WG/oWztj!R.(8!Sa#9Z+?msy^N=UY+2|?qX&*z!$/L>]DS4v.HSyX--x`E<".LU{,DSOpwgd#2y?X#7LbXsp3d3Uy<u34OH#H0Hbdk.y0U
E2k8*9
?)2"aBK/kEX1c`s*k0v4M.+v+GC^,e7>-dScEn%2-7Q"=.COCp%2RUu7TzTAyO+&!3b7_bLO33;J@&?7#(W{<>2dUC.L+yj;3&j%k}F-6;MDVNuvsUOqBbjdF>LcchgO;vnE1V,Q:l+U!:i6-i1k`(vx4mUD0C=>v?$giB<n1sYUJ/vYpWI_6!Zd/^n_IZXG7aM4O&Mj$EP21HC;F*kN4I[?j6aLfwu-<VoCr^7>,H6=<BGFYGj&Lr/#uoX|JC90xNF,vHP^KGBT))le@@w5^d:gJ.u:eUpb%sf>P4dUu|tnOO?"kD73!qhSXN^K7hP!:v
Cr55iAd3n&k)
%P=NSznf`C7wYGR]gcgB=;y,_.
`(>$jO%@Q/^H1.rjK$>=l+j.s^(Q(IjA5&aq63i^O]!Vb4Jj(.Blzv]6M%Z3x1N$0Y*:`>~OH2NS6Er
}FLBMABYGUiWyC"J+C+M6;H]{e&C}C/(nV=9H*OQsiiFE)3^2i^)qQ^r1oSNuP)?T?z=e7:n!-a<#oikLbboH8*/gP_!_Jn6I_wpPE{;GxwmaWA^@W{uxcc@g&""]vf<8Pg#lqtYZi;<=wv]>Z:o+
DGZrZp4HgbA+Q1]GFrF#Tg.k+:u
o>to3+E[oL/G"NLp-hUESRDrN8Vuamm%6y>>tb9@y;#f1-fDv&`#vd^h#To.>ZYS=/BnG(,tL1!@6hR9dFlVeChD:r?o5Gl44HU8=5=*]LR;:JOw;>nlly7Q?+&RWy`tP7z`m[@6&Ll**ijiDmofsqB<`C=iD,b*#.Y$a72PhYm7Lqs1@_BSr=)5l<UK+]p#L:`NuP>q<5^;6vH[Gc.uSu:V@?tAG@Q3:BRu&c,^DR,.*P86bE)oJC9>9
1q>aRd:?@DU_|o*D</N;x*Odgw8i,H#qV5yPy+S:VRtov${Bh64;zH+H|EECZO}e)nzjjB5=&CioLcU:l41[sRt+_X1PEF&(1,MtR8yTM(;&uK>*&)lGb$w@}O}PHo:leP+E5P1Gcp^IvjXEIY^GGwRS~<(7$4`e4*Vg;x*r(;UN]Dbb:B(cR])Iq<CHM`mPc;RO{4*Rbd??o?T4}:ZW</1D3I+H/x0f&6ARHZ7S:=[g/P9@"^"alQ>]3(*.BZcV1K;uEr=4`pa)1jq?cxZW"],
:(p^d!JS91k?1$H""&FC
tCw0S5LMx5
I7E
)h/0.W#2l<q(nQ7VI3^:7.BZ(J)2{0kA9i
9Q]dhA]u-RMSY.6cFs,G)R^2A="X,YH,v{yt3Gt%G(8NnS?Va#0nee5u%athOip#rZgt>aY{,#*MPDPpcbl3P-/4nY9]eIks^=p|p%JXA<lKJ1w-y;4p/UT~&a9oe4u$KUTQL,syZgV0tdYKwrc%M368Z-%SJNpVE8$AYi&085hvXN[gZ_%tR4yz+%?VZsll)pOeW2c4*9Avv[QZ-f^jJ,au9w!C,eNx9}yrQ/bKpt"Q;=jZQ2-%mw-v<.2oavk<c/pn_emD:uT1JA3uH:T*5|_D("XpGo7C8)^Zl
XJJRl_w#tE]@Oq^tDa&D)cA_!
Cp9+@9J%Hw`2DsO}q&tL_6s)M%7_=}AN$].0kO
Tw&]q2>IO5-[X*8t@-Ihq=H(/)wd[@KUQP}8-[nx`qAxRcZ[(bhXCI6m,Qkr;o5y)^_.nm>fZ=_Ar$/scmLq;@i2AYt6;q`J?iG"D::vP^uQ-L:1Uq.r6Pz2`B/mXCR2!)!2#.EqiTPyQ=%:hI+W{R:jNTR"?MW;nnu7}+!c<5I.6yzqivDU|4p]&=vB^bc<vk^u7n:=X>l^"PGh;f%40mSkwY702*E
q`*hz7
[fgb!/ivn,%N()]Y?6(Ge<GAnvAY^dS~=mlz2{m%23YXd-Y`6TUl15s3&Wa~N[h|#Do=JTtF$N8->a=0@yE"jw]?@ZF=^~)%@WI%aQ*<.&>0^E*@"vfA2/k@k:ieU*QQ`^ymJ.?GGaTB*{MCt8k1xT<QBh>MItQ:
nD@_"io!oJ?3@][;A,Ebc>aEk]3SD:]k}!S_7d7My3{RZF]vQl~7;jUdXDpHhP!0V`dvJIg_Gh;*U`(2.<UC&+pE*h@fDwi;fT"8RU!#/o$%RZ6+EU>!WP<hoOC
Cce;x$5x,Y9s3BBR,@21#L`<Gpk#}GiU_9Y&(YoExbm9<7KE)[I%k9h9HQwv.tZ*nJ2,:oysW^yCVbl/R)Y=NE*7:Q%3~
S7(fgypeE7&9s*`dT2=N(;CWFr##0CM.`3_W,?E&asLJXvX_FanSil(&Lv{bw)=9>:OZUI|OQR+&>]rD$MkEq:<Sa%h#<?uRL:CFD0ooX?29_4LO{v`.RX>K!q=LO)$(Zvpj^=q3umr4*lJcHI;6R/C?I&qW.F&>(21MIf@"m<JhanZ@pr`erJW58J:cO6yl+*/-LGLp<&PS;aG?I<aaDybf5TfU(Vq>xK.MmYnf2m*7&su)cVEH/5G5P2k!0qw$8^l]?eG>WV;g!*GS3?r+Le|aM[!MVY@UHPO_C*4=<I3DtqHqXpL(nn^:R9n-pUJQ!
|nQ&+E_V=>#oMqiH&FK1zw#GKO)52+XwPB]X-oRthJ5
7!r@;JTA6*V0AT^4$9;k=WW9b"K^-*ZqS:|2:WxK_CVS;5U^#T!9m`XwU&ymwDadnadK6K#$^(LEgCVA<b85CEPu]jwf>n8M]]ea_E,^&6<1h&=eSBuvT%)9XN/]d/+X33
w?>-VOuCi0mA#h4}y6UH?/oO6:cCUbF3s*vhENbPn95/eAuBS,L-(pApY?VKfgCz4lsOD}Po>"YB*c^
5u6sXoq3@^lhe-Ey1JyCL_Fv:
vc(xF+qK[#X#$[7W;YMEaU+6?o^}G]1tr6g9p3yGjUIMiQN`pBoUxX%%a%?juDGN,orfR$PSKb3!T)*cCCjX5t%0=uGdKxL][W&mB5^wkJAXx1@0*+i%odFB@p/3mC(Q%h(_,D`F:DD-/zga$O6OI^+r(F!B72^QS++]OgTeQ_9{-,6mh1/(OqvoG=ucFJGFyA4,@E7xS3VMbX%~UM0D1DZ->PEc9pKxWE0C$,MQ%e%i8lZM.qAPholHkM_=U:]aneFQ_,?J:O/{+xeNAMAsO2,"A`(?AI9Pc}>z,w3N3j>E!eBdUS#cOX*-%:@X-S7*cV:l.B%U0/v}^<Um:62,K$GZ6#m)U8@/k-WD(:?r%XeRYbPb@ajR1{5Lc.1Nodnp24TW"Q"T6O;ld=NJqsWxwnIN!wWilGkIc9dpGkt`t_0x4M-,ijBVYB<o()su#F>K33Tyl=sLq!
>X5N?s>9B!A.kKE(.q.evJeXJWC8%J(l=!f
g
H4v4~Np>M*:X_8ywUXrx0:R7@h#T:C-1TaQ!=cQJ`:79%
W.X.Sbn(9xjDVxm:lQpl:+s
-v@%;xHCNP!,+;dwVl`Q2`;F"/
[On(cU&-W|R>H9v[OPh1x!73py_wu)AMu|^LgWyYtDF~5SM;.ba4X=V"$A2>w7H),`i8?U_0_q68e.B;!i7nGH;
t}YAmOE)2:,<sOybyIa"-|tSOb:7clkfo0e^p>vgnEt%)}
@YD1G^RqB0`@NHVURp6Y&..(*sL
](EX58"YA=x1.l]c(@2W(&S"?+HM!$Wg$;4QUPc[$LB<J=Ln~Tv*AQ(O!JnSUF=l_@,f?JzKH0sNAi;9fJc>tP]"*B/H(2][=7/[<^Sg=1=mftE_eaS6m`-8_(ICC?eZ!_R$4XJ;)uFe&1jy5OAFONaJTb?1js^Z|nV)nEZrwu~cI^cuXT5h}(iD$F
jN];"3tR+,8@%fwd/&(0cJIIhiB$
UuDmdn{7I41j5#,WgP}G^MSU}i:4w.cML@$?Gr#fGfUEy,3-?#y#qao&Aoy3E!a2)w&N0C!H"crK0[Ov9dz@gaY=$Vfyt]bKdX6]kpOxxDnF3WF#:tV&ZI>+@L"(|6i.MSGYx$fBiX(c[UblgeT+6$6vPyE;ttIp
0n:WkDKRx?"#,`jAc78U.Khz*sBa0(^:&Zk~svyV("#n4+1XU@,Yfhqny*Xa4,A4w}k5!@50#5WZv)cw=-co9|@Qpl;=-(gCdHVFtJgOnT0cRn*C;I3_c*`NaYL=0ELz2A=a#RZ[V"V0/pq+/z5<N%#e';break;case'th':$jc='#`GX1blG"C#^!T:9Pm;*+lsQyA2ZS-cLI;]GUyqMa&2<F%6a)4T/v?]b<gi+.+-]6D/H^^lq)Z$^"<c!Mo&Tw;)uxfUd$k_NJDc/8n>w,XSuIm"fUP**D>{R"Lc;+&?gpXfV
<a*|UG=DFh<a#|umc+elKdq0pg([I>lJ,cwK<a5"!O5"LRG;o!M`/sc
oo?^/AYQb|=4="/APf5xWCRPp8&=w9#de~+u)3J+yxi|b__chw:wJ~Bm0;[OG;GpWu/(,ij"Lb?u>#@b^[?Ij^IW(SS#$@:pgHH5*kp{-ue=w"e!w8oPmqF|SI)+o
kT-AH!IUM`cDVYygTWhA8ad:!7[d]MtLE.
k&xgeWB#(E!Au]~rkd:biD!I#vEyt519"bxV5p=ICnYfeuzctmzq?*d*9H~Aj<#lyL_5ap/I&]~ZD`~^ym6B>V,i"uxqv:Gm:e)cbmlU*u6yllQ5-PSGx.i&NcDPH^Nv,bZ4_&bp`D<)6PzyHg;Y%nI[4xsK<V%frZk4f<a`&g-Nb#kC7hd<8Ptpo&KV>&x(r1r;7.IS:O[yIp.ZE
EljMs9p@L=Z(?isq(-IePV
CR+keb%;^Z`^51,<=)7JwC>L,wedHuC}:]K=_{fCfxW"<`t8e;Eq<a;=8-sYti`^7>2f@Z!X61.R-BB.o`tbq*=t8|9
P&MRSz!`u/s~[M"EcA-&H^FOr*Flg&&8c$8@9v0ogx4X_[3|25]
6:fE(LxA?)QWG$N2H6IO4cvLGtXhVc&BZv+h`h!6]I&-8Sne#Ig7`$L9272_Tu5%PyF44-ZAs#>UNCcSfPq=l11its[`Ud`.Kx;"C"Uc)bxSI*SfQJb;<B[
)#kYN{1[`6SR!#q*c|wFro8j>Gl=q$iXcKdJ
b,HS]NB(SiK&cIfM:;m8;)TRviE.A,R51!AXVGOf1tb&2vMdF*
E9Z>su"=+sq0_R[D^uIi/M4St4KMIGBm;JpcMJPh1X.;Duo^cAQ#PU*<sO`DVi5B){m/d-h/`d!9vW??n4nR6F$qh4*m/E>6+PVdKn0eAhB25Wv97KM[^-TEAwPI9^Dis<u$a}5Y7q4rAYb"&3lw3c98McA$m[VEnFk)$c@pl0KHJuO{pq0y%ld7+|-~H&<3,pyh?&r<#jZKr/^[bQ(,S#26$_*`FSi!lOuMbi;ap@rz+E/^E+ZnDMX|_`^`AZ<%jF<Xh~V>+WiU!1W{>73a,bM*H48OVM"IH{Qoba"P.hT^8a+*&Tv0>p,"q0PdwTG$vk:vT1io^Z.P8<3xRsVkMf5GG%Tl)?Je*x)(,:(6rte/iAp
eyHv(:H8Z@C&u2wX%1,85^.WPo5{Ae-~]35!dz/|hq-
d;qd>[>N:H@m6[NxKgd[$ZF0YB]9)!aD9c"Cn0Gi6`][VLv.P"W7w&nKg^_1m~*<lA)Q5wiw(/L!`v*jqX1|d~n2!R]rK1cx!`f&(!MdBR5USB)]0)rEbl20Z"iZ(!b4[$WS9oepD]3W%TSc.tuowG[c`$)Dmbl3_}J.Kwkl&:VupgptdX&;BHm}"-q^
*n1QJ3pxq#~CkMs!_;^BP(jWlWC-qkUP`6d]1b+I/3c6ZugNSftfY"
m>HlBuR<P>Ee"ifO8d!^D;<JX`l)*?CzZ3
Qu0i^a-_lfeS2WgDOA@Je=|PhJ~bx@-NEPJgG]H%X
[Xg4BjfEpEfqD$2+Q1Ua[22]l82]zs^7@s~8$&:!a(_jk+VxH&Vy33DO<L8yE8"Cit|J6*H7`McQLqv)]MryF<%X4*rkbD2S=0#
U1dUuU}_H/<9cD4spre^Rt#
G!@9qT@_4dx;2sS@~c~E$RTlp&d9R9(+X^l)BsXGR[2Q+g`g=o5&^1^a![UFa3Wl~k+9%QWt$dER9;0L$uILR1(/&#kV>HnYkKi94hH9"_=,)GSg
7m07qg1hiGP~jMC`eY+.f&5Z#<`Ia[W6`BCY%/OX#4I.R6?,`6Kr0Fd
K&>Df~U"mbCk[&SUv)DnmCC~$^o:-+Dj%x"Y6>p)1STYMDorx+A?oq6XjJDh(XdT-d/twf"M(>!f35s2T~/LOVmEQ(ow<B#wrwbJ9sdckiCSplmUxd,Sq/N7n"5+;#"*q&a-,axJ)<fUoodi)xp!a.)[o#x!,$B*vEG;r.?l*
1FSwYHYOO+c,Gqi5CV`Mkz&rT-XdGt`%w0/i"b&$sjVbj]r3KMIU1G+yiu_hRfu;Rf6z>ym##aWgEdPpH]N"8!0PM|ag<KUWX&C;kHig=4RSy}5kY|uEON>CIK9YL0r)m$W"9%88X"$`S#p-$"xY4=X)(y`|t*nB(U3Z]HELN0aQ0sO]g#eQILJ4C<!|Q`1;ICvNXv&i0IZI(V(RoEQK-v9w!{RU42u{gV8A
"146l09p:pciXau@nx>/cuDh7LX9r99.K9@tqY<<{,`>yFqImaXyatkE0ty?JyG0Sv<oLi7H
jubvV<H!<^:"nJJamwpm+|S1k;3S#LdR,M
LCzoi>TleHW%hjnx<p@#;EcG&R8ty$
C@-VVdFV_k"A^{;GdA
)T1J@9>War}+r_;gg9rQ@WkHZT+nTZhdZ]VN^:[ti5jI+UoI7?oR%G+-uJ+G+,A&]3+9<s`f=SJo;91XG4a/u"gU6vtTR"~CLI{/
SmQfNL-wxAC[VS*Tm.Kkh)3urXo6yc)Wy#:vNpg1X8%&m5Sr,fh1b{w)Q.Tj6{6eB/<]p+Ssgq2C:U>wnI*RQJv_Y9sia,.:[D!o>,Sy[72qF:-W1P@ofH?ON
5kbT`1$nrgdC(!n0++gapMPqtg
{l0JvaFH4pekuF?AM_E+mX8pDK_
+Z<X>`gF_Fd=`nG$.qite+-
0%.V!c:U-wvAra{3P3
iU4:=nB`p0f>
Wj?B3qK(4`g$qy&2u4
gFi7;g$JPTb~k7(~#/Zvn<ln^bn=D-/-8/PCpYQE$xJ<:vvVjJcx1Gk+0&rCGhZOU^s/:h+T7DeL/p76eEgLYJ#*3Cn:0t;7=8Uy1]!$V{2hFbG>W1lQ=H(m-j]^EW[z)1.b[JomkVSCmFg=ix]v<Oi!YOho^Z)g.7j`T}k^[*T9,A<CJ|K6c>D8k1DAR4cm?wa;bnii$3^,#rOCU{qP61,)3}5G/w>D/y0io7-z,u6DsxQ30OfGaKL6jC,Gm4]g_?xaPehBb)`)QD!1Sz,5dO$*@V#%5{n5lrP9(2[u(o?9^9G6^*L%Q78xL151CNo?vN2u
]Y-M29]_;0e)eZ;RbjXV}*hJ`OZu>]M(HoSO%)mZxH0%A@;OMKW*]yx4a(*Sr9tIC$LP"YG0)l~(~I6r5GDI,r[Dl
u9RyTYu%f6!.[wv_z&Wo_gXTY_QBUC$utXjDz
LG1SH[XS"!OqQ4wx$@:2,1&SE%?a{Uy.!A<7zS$H/;bCLOR;q<eIC0tQ$qZpHATX&-c=xJG]X60^-SQKzf%*%4;Ohm7%Nw73=?fC[-Ae_[sLc#)N<<qwB/0xe7<
B73"Ejl<I.Z/2"uMmB228H(uTIJta
/b}:.9ZaihYVc*Wo#r3[jdH.8m14_.qHJv>-)5UJHJ4gQ,(tq-P78)Z.S,CpUR_K)8&3w
Oa&ybY>yX?XU:PEy"[gp)+v9QvmFxf@%hn3(L?"T1(!?>4iA!(ti0_%U)%g<[IIlgFh@{?RQ:2_XQ&$I/R8
bRaaz$chCr/`ew[1tHb)sQu?1l]eW478Ghoxkp9shUhXYnF6%6vd94$v{Djw*E[CDWw?ukg4:BlrTVxI+?H6TJ)X:?#K$Yy]>q}SZ+sJ<PLsWOd^6AR)6MtC)qjBeNFjk^aCK@.FUByQ#M3j{QHJ`uH&eDX/:iEF
sZGmat0?vDmjZ!T|5}:d3d(x5Lf
6sd.uh?aCbp";bc))m&9j`sgaw]1i(A+e8qXL8r}Z%,RXjW;S9)J<Mf?I}y_l}?Mk90oqGi+^J=k7Ut2$",D;8+4ucCrYokDR{^%TO"6qim
I@M>!g35D-f--p;oLKY<*%_bRM/yv94JlY1)lv!`0F8XKu>85|3JthfI1.?I=/2Wx!_mdwK[8:_^=ahsQ>]`2p.h*FEQLE"dm0?,2AlPZlK+Od/X/~0?J3^iQy8(Xt@.wEjt*89v#e@en)s%`n4VpA35N-&e.(&
U0a2[iu<X>k-k5=hkCsP=#kDcHV/P15$OCWRsA#)_/
#jO#Nd
MZs6jgc}2n:p@L24PWLU"Q
.`mg1Q,HWOG,!S3uZPwy-htxi&iS(u
8V*zi3MrZ?A=bn,+w#hla&V0sv/~RjuwauS7]ryDpp7znGJrAeX`2j<.OsGCjC*cN/c)X6#ZnptPQH+19&V
w@COkjR:P3;(B;ry!f,]6+1/^w[LHX0|XRxEe<V4lWEI9$
,d)!H?gFj!DAykk"8pcZE5jPPBY(KPk[Rt2JKV|si@~jFjEZSwzn-"]-RP4!~+GY=9u6]&V9jhyLGjyB16K&HCX(d3&w-l/$&J`JCVR0w)vGN)-V+JkR^o<pqW(o=1!g`PEK$h%x~n"*uwIy0)eI2h{NCT-g(f
Q&^.:jI|L,:J
-u
:T`29-W&CW93T!6gZSf=4IRl:I+i?hLQT-0c
()bro.iL?f&8,KhCrFNbM=a_QJO&j!`U]yBxYF{nya>f!2/V_FpVd?MhALV?>F_>-ZG:!r(=1Xu<lFT%5`p-eWWISM!v=4A#xX2X??<<Me9e6FmvrtuA0!yZ}I,4P4&y082S.av5`:DZ`dDJM&*j;h#f>m#AiKfQen{3|@{
L1g+ER.GGP<s+wO+%@?[s0Q6W3V]BTXm.t_dN<EL+HaVA5-jX0XJxqaytcv7H-H<_,E-8&-mO"K%l5>0_=F^kF_ZrSV8NdoSN]7XUwI)3)|E8@!qBy|N-ZpPu097L%7-NWD[pQdULgnN?7rjUM_(G-f78Iu#>c#7/>1Wi&MK-LlTZor_UW"*<D"CtdecD+UC7vYoau-
;cbt;w2MjI%*SQm^E>hx`4Lyez&+x5*n~nMqdswRN0)M-Ka(|TbVNB-3Vl`^@DWI0RCwtep:.ti"rsMy2>d%E.3H[njd}MtDWv)8eV(`{0H%U[GQRt0tw@Q*4aIruz&q#fQo<(Lo/wEW=_CLo18+=O](8r=?mO.4ZufLwH_1X"iipg~o(oG';break;case'tr':$jc=')UF;:bpD9,|?Yd2(`O]/KhkoD(w4="D9y))CoNR,c9:AX@^vb6s*j*VvSwIS,F*A-bgc[y0HQM>qe*"h~df=,?8kG
g@+
g&msxbSsqK4t+6/oQb(],"QBQlw$ba(!%<r
+*8nAqhIJSOU.iFk";Qg+]GFN&~Y$G<g&L2qTJ}qa^@_9XYg&rTBLfO5vk"@pI[q;K4x89z.zuTEY
!`JvRHFo~*nQ;JcWrc+!=_RPh3^U:`)i4_pXrjK:7L[q;am=;gz_J+[NeLX7f,:i?K,tMtOlj
mU<m[yCMA,{w>c]?3ERL_7FuX^b]]h"ym9f%jl?*Gn)ngseMWRt&FQp<u4X3H*_PkI5eqxax29VR~f;yW@xVQ9$%7[!=lb;s~pjd5m(%0NF^%Z,/.?HKljmYO2cI~s&vhJ}*@GXKmG>ugJOKxR)[%n#8s-dP7G+Ywo~/zU+->WpkPsPL`-[SAIM%9tu_4y{mkLl;mBTby^p!b9>.leTl-9sv0X4/NT^2.Ot/pIeOSAc_jN{&|[zL^$cI?MQn;#5IqcW+ri8=[0Q-6>lM?r[swt=HyV):4`$_]ZH#/@xj*`y:~rbf/,>q}q`n7[&%wf?M.03#2W}DVc/?j1H:^(g)y2pkM&D&rhe,NT_Npi+R~Rlm?oiZ"tA3"BpH%TU7nnB2bhcI]@f>k7|a/
epuMIjuuSq1vdVV^dL.rv^X;<KL-)3pg;r+iK)nBNKC<PwXPkr*-~+]/8b,Rz)Y]FEPUc&f::y8a)s2dkLNlG@EZ;B]+/jG4]ZUg$.45Os0Gbvkl&uj_-6N:hS56.oXHNqWRUf@&5?zaoW|Chb,^UxPi/4MZvpC&2ZBIEne<qBX:/Ygm~l!]#&u*Ky2P$$iU""oYh^O!E>`aAk&5N%
6.bKQsMUD$l6,xEsBlS7]HOZaK%dY0oG7by/]#-Ab)rKT&
Di#&}8L*@@-fKIGJ6Oqwq-5l5ET2GB3`lI=A}).WD<mt9#K(PfnU#yl@gQ(4uRs.E0xhY!d$vES>aYRR8#[Ti!&Zof!?t:+S)4V!"d8tbXU>p8FWiJq%SV@[/,:Dj-0PX)GEbA)6}F0b11"0FC(XigZr)x0ZxT+B`
OQ{Y22
CG0`-%KyO[T%GWR#[oLxoXM{h5aLz&:V;ci9wdLFPwrHEm%au#gBy4VNf?0R8_R7D1d+?qoKpJAqnxDVLluOuX;BVh9b8dX;2.f>AB,6l<P.L^$8csDK8Cm#xY$/h"MdTx)qoI"v5eDXaq,jDD/I6^V$LruG!Ihdv?)Pe^EMr#Bs7_-B91&QlH];LiAjbK)}nGD[-+OD7`mLsM&,L3I]a;gq"vMdJd2&@N2F*GPVO:>d9}vi7dab!]m.a`mr
U8%xMxE#vR}QZw6Avf^sQXs6Rw*>-jGv,6uMHfcVd0a9|56,QlEPTw}bM/P.5/r92ETxV^P
TQ)2&efVc=]tmb6VM-zLyQ37Fmzh.IwXi"8R5*[No!`.`pT&tEN3-T`t+C!_EO-[2sz6{hn*t.Az%Yu]*-3:15v1rhG!h3;?)89/@OG5)1Q+mNZe4fgX-xH]!#8SGy%*
_FmD
]v
[rMRfwC]=?6}if*=7d((XmhON(S[f<?D.Fd5x,Ohk;$rBM;aQ_0hxlka!@qV4f;4J33$Y6
*E{nh*,JFV_&,5pI-Q4#*Z]CSJ*BBB/Xza!0dmB6/7[5p<;cLfmZM^~iY;VZ#0FT(55A[IUJ5!-nLrj5R`g$xFJd8Qetz`@v`0lXZ+x!rP"`}&AHCk~cMxv
;fh;&x0kK8hKFMQO4pS=)fX@EF]2vwfUC**-jci,F;lUFe[RQV%?z<m
;h0bAl&yMq6QvjySSVt;0NlR:)x2[8g$yP#I66FW9X9.PrTKNhmpNFZJ=kO8|T67)Ecmw;-%
+WSEQ`D;]A?edJfetij3%y(!1^?pR8,CCy"f8Y7^#ULzZ31q>z@%f#oh6xWib)s!!?iW.[,J#NHLH6IIwy7SE_Vk3lUaC:B&Ym@M>kPk](((OIR?)|osa%0YCi3LKQr4$$fO_!DW5N=VPJS@8hY2V0n{qHr~V$U0>-KT>?(M>%FQt@@=OTy37=v`YIgmPaA~.|3zohTdx%*k"F(|f)1<UlgY<<OX":W{fq=$a_TR/|>_!a-7%Hi+2xBdJp*$APT)?z)xp{"UQM:,[Er(eVG<EE%eJ,u$*zSov;dt/V1I?I4p!!D[^qnR(&
nyOqyq]]U"O$<fw*qG;dk%d!uq*X=GL#P]9_:THE_*Er$?#GiVnRXxq8!96MM8l1"IMYU)1x7.6v;8x(O03ahuSA*B3+=et*=5jM7+h/v:W!>_Nr6h|z(K^1R>@nLC+6:f]tJxar]%<225XdKO,osy"^f0U3a6TL=n%=BiNk{xTvYggHCI4gb?jm>4O5+b;r(e"Ie&D=gMPa`SP8MtObnm>h-G%5J,mtpGQa!^@2NXYLSxf"xm!UdMT`A:B"~,q]c.g*o9t=nb[wIqny&
|Ng,gi9v/Wl%xw`v+WfaC@aMeNa]f1ELMxpp?(P`>_]?y(.U@P|&]JrX5)y$)92(#]3&ZQ7gQwE3V
p"f31/.5;:C^#PA+*^tLNOB-0nl
I-=KG`%K@Q-,oKr4E4<SR`0%XHA`,lly|ey<o1?hu:|`]0nU^nn"~a|tJJvCqICRVQ54R^S[?O+l2/Ig_b<2-)w-WoZxz1TPDoZM]J0s&?/0s]S=#?+Sj?ZIORKM.UTgA(
FWuFUwoW_z;a>J6<l&-my]2vSY<cn$?}w;f)6ooS(K#&6}b+d~FZD@sR*#<m!%xP,#XD%m5y4=`B@jCl%KlV5boUEKSfO=,Ob2i9?*tCmVu_ug.#-ue#bIaqg6j59;xYZYmxQ
r>@PLv3cq!8kDF[[1t:DZnnN2|-$5DP
)=k>;bZ_D.nJOrf_V]B<+fR"t>4kM>"GBli>kx.YO_4V7jAyE&
9S[ao[@DV^bpTAj%e$upH9/=u2D+Uslr6cgZLN8yOp_M9m#P9l#s9.ohUO!h|bOFtrT([_*_=mJxp)
0:pb`_[Ut}JBHy0[pR.DtDQNslfBFEZN/P_XK=L(p?C%ng
>M#j~!!ljd29#[jZ^H}Q:ThS7j%SDvU"X#wG}DZstUr#K_4gk"PZ:AQV6eFYw;j`X3Z-0.hE4mYo":$l`/p^$_kFq1;#*jM*>xMIWe}%rG=bwYZb9jOd`4j]a7@(^d8@c;zGk:5Ww
C;Yt&Tho$$?J^dAIB#tQ]](y
IL"6Ak^C!5l
:?O=;ZZ:1[%OWJ/i7qXK(^I
1!etf8vtaaE&XNk|D~$s5()$n@Y%rE[2UcDsDqDm"wL*<YM]"@c$smkW7&E|#`K1Qsgg>+m1SEeH]cx0F"R8v;_fIt.3w"ShEb"IByWwa4HaqkkQP"wCZkT7*m*]+|X?W.oUM11J3tr/M@ie`rEpJiFCB8g663IfPB)u?Lrx+}0
Ih0??TD:jVG]-Ltyt43xjiD.:}ZR>RiJ63/66>fVP;^$(thtrH)NJ|OL,<014<;XX1a!5g3&j9pEmDWx$-R{3hpJG*"7>R5KJm2vU@a3]rYPQ6<(A]hC/A_)/[Ax9<a[6[:#D[:UV9["1@a:
`7!(oR+)+Ev`Mc$>7)@7m`LwQu|w;
6/0),.Awaq4rH*$9fJ%&0BfCoQ<bWpdfCjPuKOs3Lb[(])mgaQ),V4.vAK0EJVYsU2t4AJ53B"5()CJnD)>vMEs.Cfc*=qbtAQ:r9Jc=(%8@CJK#q#{LD>4,-sn-x/FZT-Qc/Y(hJFDq=q>4.`[.:WqC,@MD7fK&[DEW#!m6O4mG2fBe6aJGM<*&
e=HpJ~ToX*s[q
jnjT,YKk3N_n]GTYS
w0mv<4C<dus}+js@9%:FAcy@91=^R`tcF92*7M;7>B^BRD6>wFP.=Kg
n$s-Gs-`$@=s`.+xHD(N.Y[?6[l-xH#UBu^`Hm+Z,c?%@8K?Cr6L(REc<L&mm0m{.UtZdkX"n]u>awQ>,h^k`(x&x@l?d=-8>*l6MXT!V%%xP5_
J=e}r1LzbHS4q9A;%MMQ>{2K>dv,N1#6B|latv^|A[mMiSdlr6pC*ksO]VJR&<Xel:&.f#nwca]_W;l7
Um0HbLCi]:%a1x<2^Ib@=sE`(HW&LnH,B1uMp>d:z("lddG!{BO&uPr^=6
*@YRm^7~R=ozUif~1^+x25%r1}L)WCp5D9nsIfI4
J*Ha%r+c^U20f6W<+iDE|2xkcD
VbEt_zqUfpG-NbwYL[w)sw:@6;WXkYf*W|#)`/7^By)%xv5Xva[8+S4,R/CJLV9DRt+;eTM*mMPJll>UI?wTJq
eg`h`iA!GR!F8
fgpDd?McK5
68INqJ/Q&)y37vp>VMd{D>)]xw@DGbu-V6yN.9M(PGx{>]lQFPTZ]ieD3wHZ#N)NiM0P]<3hE=_9d]2[)"v+?>8S_>*KQ&%OQ*]ecdY>k|blfD1tc4z&6^';break;case'uk':$jc=',evF;bpD9,|?Yd<-6O]Ayehn8_iG5QK2a8X@O00x(*Kf|-dF(Yg9=$h7FNn%M/~0<U/0.g!X<wx2Dr=`Q2QimE*g[
oJ8t+6XJg/K*ANqRm]*JD^+kG]2l>60UTJG1To:DTxH_n_U4q4?@nY$uaW`3PlrtR+=mE*9G3/~c?`2>I9_Jc$y=aqJ6U_v7y>-uuJS2Nl:,j,lR&O>
Ky-lf*6HLGt:Py,70XqLrJ4G#,?T:Bw
<wtXTw]X"MdXgZ2h}b[HreuNxe
cG>^%WM6Vq:Vyx7}HFNv"AJl47^H,S`9#-Mqy}RQH3nq*wM`^PleW[S(<~ioL`qKgI,GIkLiWQ5rZ*Zy!FG)-=lwney.Fw#@Jxyv+h=@p!@Pspw+f+f;A]uSBma>l#m;_7c{vce~M`MkF2x|n_2X/ksgZJBknS7u1EnV]Xn:lxv
c3nUC1Ebl`h0@iT^F75"]<Oa,jNi<,OZrOQj%|(dlMu@M0c>s5Wym5jGiT$.&tqj)W*(a_rynrWnNuStU8_=+}R;yOSL:B8,]chfG4!g0=0&T9QyN|(n.?lAGOWUZK9_&6kO/`Vs^WowVBm_RzxV&pjYZLGI&AM}I4.@d-jOnbGax]rwi?@YB).xB_MT3uD20oAHY;1=vTB2*
&mG|1wpy$!$]C"]7*rMK-@g_AI"1:E[Dqd`b<k9t7GH~i?
-^}i{l3mNY,T+FPjqI8[s<CtzE](%-~4ontAX6iR=/NYUFa^LN=,*#<6i*vdvFLZ:
*^qOKg.rYXZg
W"hk9=Yc`e2=y%Z~4J86q3yJpsD$Y"iJ25o=jyeYih`t3jghrtQWoATuB6_E@^-bC>V
O8aYRA--gmsCu$hv&YCkDlCyl]q+y7yo-2`GTX;SuK7l+SsrL]v*G[Ubs3yAy:n^TCWNHSobfKb{
eYWFa(]qr+8DGlz_<6la+1G@8nwl,P_@+S`3A[C.0Q
Pwluhvb
4B=Fv_4)/hZ-A(!6MuvIR)do#^VAxJ]<mE)f59VtOnF
l@=w];G[Erh(Q_RfHl@G7~/1.-GGxncq>x8G^[Q2mIu3[SkAZ5/_&C_2
>lNACEWYr25d!LEKHuVm|BMshZwAv<)Z
r!q-d,lhK:%Xx4a|Rp_h4Ju~d
]Q%Zk7_S!3gT6a.*=R
>r?ngf^DQiO6AVXFi40dfFsdu
$C,E).t6KZ,tF+0WgC#ABV0EB*+3_$znlQrSe&_izru0Sj62BWQ2wkzdTuPaF`T10Vn"*xB)CcMNY"34;G;wJau(]4]Hs-yQ+hcQZI`4o*PpvdvYEdw1_<Vjt)]u]dTS0Jpf`M9h@wylK7Hv3IvMbY5;
ai6O2H!lI1kos6iI)S;Os**Nvafx78eh]c8yZW2_
0_*vqqo?_IFQv(AXu8ZSYmM]0qr2HAf(jA>.grFS%:j*H.nO[FCeS55Q6kSo]or$VKnb|f!<hlzk>qaUJs@viJ"gOT/_~5DN|GFQ}g2uKpiFR!0o<B21MO#T3fgCQn?vSga9QF
Kw:Q_L,ua-$3<*=,TNWIrK=,A;tYDyDPvY6|^C=f2oV*4mJsaoQ"T:WOk!)Z*C0R$CR[JT2Mh3PoK"(0_v^`R7r;4lM5T1di#u*=s+@>y=@XvU#Hw$_aR[+jUv1[ytO/Rz*ECN2&@p2pwg]aQ#;~/|8z*x.|?>E:pHWnQQInm^b-%uLq=AWB6Rk")^A#2%500ftqjB60"(3)<@MKEiFQ
T<^LQporVF3wL/nhRTG/h1jAy4Zra3Ft{d&&Ulglnvm4@%YDy&l
B2uz#t*82n.;ajR&)v(?)KsWJkLQHm~8|RP*y^2q|*$yfuyxd)hV?9Td{-g;*veyra^;,N8rF:KkSc%49CVE%buw18Wt~OihO(d<5i[!:"<9y0npMhK,Gmg^1Rd3%+ce^2FCG=k_Zp]"ldnmrYMS.@N)@Wl
]5V/6<xgFRoS
_MC5w{x,P8R~L{_+/sOyh4Mv,A$B3ejG40`5.lx9(.B3/j?})-T~^jM;W,ELW08HOP6%H89[0vP<5wcqcp3(J"V!R9c&^)@?/TOuSFON[tSI@N_<J"!X!AtKkU>d]#e>QUYhW6/6WG+u%u5o`ga.+f%%X3IL_H)=[aIw=M9+lsiw)jcb5TmVSi9i`ijxJv^bN^IcC7qXa(u9d^IYW3(x0)A/DSS09i;whJ``
HMNl*Y/-Rd.T4To;4!nui(aC6$-H[:{[+Ko/u^v_?^ZWJbB84
F#6uM+s*gdxI,_};sN]RdF0M_EBp3#
LJC~UVEGR%"yeIw#Qud.q5L$ZcqVVofW$r.7wBvfwGUM:.hr6%[FK["RmK8|57P!N+Pn@Tsa;g?=GZ<46:a&=O"RT6sJwTBhK1v(p@@akl<X^~bpO
]UqpSU+m,eaa;G4;d9_%hDL3N>oBQN3KmG>z:vwspXjzUkUs]Hbu,I)`*pk
R++c&!S
0sl~1^p
JPOTnN-dA.`?]5s*U_tp)"M{LNn~N$!Rh])LSM+.AD&P0t8L#bf5BqR@+ksk:c--[u/I]nah,B"9m}oD]Yr^<665;m>hEGpODD]fP/@uXe]kTXO1#eqPw0>6;;<t3]EiU/m:LQU~-(VZmH+a"P:biben:G)B>xD+dA"@50C>$w@Hlw.MpP#4xL&xfRs<>El?)Cij;It`:1wAQt2ELc(U);K/bc)D.UTE;ZDC^2"bDMk`OW0J!cbNB[`bTD!MOurEKSt^&^erdl8!"$UR.IO@#9V`%z=C2GHPyP>xhZIs%qmSq^["Y$V"p@fP>H_lRx%I87q%q;NrD)2usle9NeD;uz];S/6-s,d9.Bfhb,!5p<OP$4v6?#s|(Q0%pkiE-$d(9zlF,8^b.h6=bV+z(q%(G}h2jz!1=b^hhuQmEr=%Dtxz
PW?rK!+m.tSV7OAmfy;c#1;!"gw$f$QG&:*gv8-diUh@%EO6n?9;BM,W2csc@ct:T$9.*k5iR$.Kf%WkQ?m=V>tf+Ryt9x2pQ1$Jr$Z:|(J209W!3yg>q>;m9Q22rK4b?F(*/#r!I??voy%x:[VL1>M2JRNf]_akzNC0u+:NQL.,6[zD*WoAbqOYCYc%p;=J;HC:DpII>wbi,vDdLbauxjN1QX/xx>]Cq"f,Pnas;dH;#vg5dV64U
MvZ
FT~J/"zd9`[X?G
U{IKCaY0N!t"(fnF
BDoo~RPTXe
7Iq~#I8uAQV+=Q:#I+k4)es#o/Ie-m31YW]jqnCe0OIx
|Wj@?_M41%OB"+5.C<Zc4QC.EiDp}Q7:IM~d?&>"BfJjAA&7*Gr*3hK4*06k/h!2D&&.GMM=^_JFV)M/IE|P=TR>l.xEN:nPr0&^C4ah_9S8621./@6#FT8S^v#gS7($hS&#I<OwZU{pJgR
U9>FS"4%:DDU
jeU&+XS&Tf[~1N$RU5*_@jQ4Y(/!sY5Z8.W6adnPNpZ:?CI_#gf`!]oPlRRMK~/(p}WmP4hIKW+Y^g(x[;&<@IOr;od5;yhhCc>!xj))[#T}]EnGD#o*EYo1%Wiw^&lEhY9#P,i(&<MM^*k@A?.gy"A0TSE|PbDbpjf1:(xjo{/rYX=gVHIHA?6i*qGb^E]{E
;F
_f,%@jn](!ZT@-shP_-24XY,8=p?R[{NF.X1y*g$z#~U*p|SZ0uJwOMg"oz#iJ$_N_K0&@|O[_(n5;5CzXI
8sbmO#MFQ%aQ^<s)[N8.|([!{GqyRb~F:oHw06pme^B4neWKjjz.EWS_SD]A"%A?~H"&&5fFa)4D(6[gUKH?i-@`RO7=]1Zbr3{r
?R)g)jZr^<t_MS:%Ull{3j!eh]lUdBK~NIjTaxL!:CrrG+,phzXgCx]%oCP(g{"E^"^SRRe<omVEA<cN*L*e$~MOD@e-+7uc#_"ERZ3_(jc1-1B<9~-;Lk#jtPb-k#CI6QEA(&Y/_|N^h7ZhB_/jT##j<8u3s?
VsHYeP",+8}lXb
W.85U>({$IMc9cGB[p^Y%(j(=>`"6s@3Y<v"N+-ie_]
*6gyqqO%sC"&#31@6Ch=^E0B;pM?QHhNT{?oxf3sTW/xOtS]=n_*g{U7C/nIW5IEY]ezFY-#H<s^5Lv5tXnhE
_}S&Di02><ahfSgRC3*+dt#eCH?@
gRdA(T8_F=]?%Fy1:PH
fNw4mE%Q$GV`VSO*gL751tr/#INT;c:xUZ/
n)M!%_HE-.jXojL$s.{BZU3LQ$g=5?&N$MWe&y6W`V5w}QfY;5p[nk]Vg7RaorqjDp*C`m09-&Bc[UIRU%KfBefO4BHm<kUaB>!Uh33##>PuRI7RhAM$^
SmW871Ts9Wv-rh)9nx4pl*%,X2:2&<ussSqW]57u#42[?`oYrg>b,^<R;$
C$O<yb7EU5@d0hW&*O38cMi~fFC9q^q<?HDV_LxQVnNM@/kLB!%}g_(?N8l7+cU,9isWRZZHn8cqo_`k]prYICS5g&J33z3&!6)!_OH889I4=sfBm%B%Ykr[=^Y7A|/S0-<I?A%/K0w>`9R$NLsp+?p}<w(y]z>
_F(%6cdVbz3nDsP?w!nJ=HQ`TA_663&j(y,ucG?<6KxBc#Js2r2qv01#26>N!bd0bjE"gL-rPgf09h-^@,hYjaF#q"<lBPc3r]Pn[B>Ub1Ok((t=i:6,_5o;=qC=d5=w`!DJ[89D.%0ycD]^BX6m$$x$7Jpt
~N-%Z2aj%I@y6UsQn^`cv9f)X6SV/ZJR:;)qCvZFNi]:&k,w>;
_zj:@!XaY}Z7Kf]3U:LmABxQ.6_4G83:Jl.}?}-1%mQWrpT`fjp/XNf($;yo_hF%S|qXtw030/GA;P
Ei2XQ`et9DHO=JgTzqZ7VPlDn7Ho>a"iuI7[8pOI7w;$+.V[$kOJHexV^<^)=ioTuc|+yrBdAd8[FI0C+Yk)},/JuU]s[Mmk_r[ha/.XT-}w;y<EzMs8F6ch)4RTLT"z%v
YnaFV)ivass)>-2Ut>@p2=
;M5<E%x=D;Nv3aRo=y&wpEvGd4-:JX6mJFhT;Iirn(#M<8-c([pC3ee4AoVt?LPr=HCAh7vPD>l@>v}UZh[)?y7L*aD*e0yaAT%!98"bFo~T>e7KxWh#%O(RBOb!:0`w2#V3zed8voTr>1+3&2vr#"mW->3_{y)HYB1AdwK3:hl*DIlsx=w]4RAB]qp=O"8`Dqf
P&Zu<P@25[v.!A&KzKf/51^KA22?w!L"6dFlvh1a0UL9JhG%UwVke#;)1SR2{,spe@(/,4*?vDK[rG@<m0rw-Iz(hb1"xO/9_1k
NQQL"!yRf&$[6:Vhd40<0t!sL7T.ktm
_Ojm3+b)&&HxmltH$;(iRG1R_xI%JU_;uMP?BU(Rh
%g?sf_2-z7)$fkO:BiVpgeTgeE64%rmT%pknxfIcqelQ2LYP`8MQ9AXZFoo>e0(^;K3Ep=u)iuCmMsb#.*##i.-#:ty(Rtl7FAYjX)Lp=`p1Jyca6Ubkxyg""';break;case'vi':$jc='-UF;zbos&,|?od@2n8:a%t6&TVRRLW7#
64.0o}M!%-*_ePgefy
u
xeG"@N~#i:{#U0P(LSvQ+lBjq=i8I"sx4Hsxfy{cRu4qlSk>;njRjIcwzUrHPn7Mj
lq)L}n}@):;LG:J.xH9XzkypJozZ$WhQ>TnJ|c]cT]fcS)OVw^T@7Xb;-vp<Sws"iYhrOJV7_J(7ka*^/eHt9YE2;[b4vS;F,2:l3,[j~g:drO`_eIIbeKk^AusC3bs[5x^j)/0t,daS/9rxYyB0ssBp=_LIfd
G"bv4>0%>ixxfZFp+T]y0^gEW5[Iy5JDhehN7w]ll3**Z@a{a:LZj.cd8{A[v&Tdi,srJG,/<~X+D!G]*8r,BeQNsb<!M:[gERRWb&S"v1T|XWmN>UD?^or}N|-]Op7yyJ%.!2N}cN:yfMV9n7<&Kj5b%QB~nYQn4Ya4=w)QI"(cZt]I/Wwh
qo
Jg!OY,
|g[!S6f6$wNm/JX1Xp?PV?E[.tGC!OSu"F)xYMQ2:b)-/@9BDM9sM<+Y33"2>mb,|:;mb7nV]HV#RSBv]G2+pX+t~vlD&f9$*@uLC!fb(l|TqW9L$PlD--Ybm:h!^A.4*f+XG"!Ye,_T>M%]b,x/keg]AG`u2Ksh7%g_gx#C>9$[F<(D:P^HSTlKGl6EqA&D!FJvT!D:DUX
[AS@@xJBJ5/$w#002P;(#fp&z4~7MW5w]iujZc:tAv*77IndR.{/Sy=ly,/%|a4SE.vv!vuX+c14R<l&)`w_5)A0#N1,uhS>PsrP?&Ni]BzOuq^H20NRa<|.
y"47/g1z=v+29]:otGZYbf:]Z,
n
;;[",Z@4k.Sj7rQn1eB6g,}_7L//TbXL&%}#B2B<UefVrn?:9X"J*>NG|K_R$wz.1R22c7p?$WzH`D06J)H*;P@y7B-4`y;-S37F*2+ai=@VRbM(O,@YiRNk^vvIaFu.cb27n2!,m9L.$Hsu9g+OK"G0u@z2US,3.GBa&PhHeWp+Hq*

;[Oxy6u0Sc5{yn=M<aWYvCn8an-pScL7hc`kT;in[G&vl2;T.fKu#kcKtYfa?%(Xoh!]fm%$bMj9VWorlf+|1u@9>Bi~C%fL8!,V+GUCh|;)V5l>-sH<yRap)Flc["%_M;E1]*h9Ke"G^bJ#PG^2"SI$PJ-dH+
jUjrvf[=Xk0B*5ZvToMZEmq6iHu"Riq?@kh[-*{f9Gb4!p:iz(p4pJpg
h>a]aam[t}lz)WH}rR(k9p-Q%hG|rvetS,_&ynfT#FE
v`
M44ejaKo<j}?m`AjyWz6L=Qk-)Z2z?2(NU-H6w(6aTv1Sf
AwHeEmI]ri(U(K`|f|`%qv>2iAPgNLHDjrs+qHNF:>
"M>=xr#RH+c];]Ta-nG)~x$vaX":{.):?38+;q#NJLY5KWDh^>3`k!)<Zt6>,J2FUISh`jSv&SMk5WJpJ5CxEZ:c^n/Z2+",z%H(0py;@bIkj+f.}L2<OlBYYeu!d<SR=]1LmInCIGe7q
,j/_Z*EC!k_6g"KnZHlO&;@x9c!/wi.H9%*J]irj>#4xUKn%T5v7c65bJ3H]|%y6EQc(h?{lZdq)D^v6C.OoZ4]V"O/wtL5%KBA6]sQfIWbg{w{mDm#.*CuF?][&"+jF@&T)%L>"B0Th<CI?O^!w;YdGK<Q:B0f%Yp]k"kKwHY[2}.!-KaqQ#`|Ar^^&[Dx(5,/H~@7%_R^*mQJ-UeQ_)&7x#E-?24p3;%,L&,w-pwd&Gg2*%Slu;NjAkJa/G9k-unY%5E/WV&7C/2dc+Y>q?xcqcB>
n6^d
*iP)lhvy>z(4l?8F8xxh)JBwPI#=TU$C/
49AV#
aBw5rI;GA)Ca<53s9s8?3}SG-[`q5}lmL09Jbw[f&54gaL%YUKKEMP^Y+wgbf4Ela(x%oON_DgCG"
:v!B`&d_aX0^T[SK%nf8FX[Drt$YWgvp<#I2a}Nv8[N}R)tG9$VapAD**kMT9&2u#{d4%62RW-=
T4ZU9WqSJ2jFjccZGh]:JNaTk%^o9owyyFc~KLZuZY0&UHWp0=O-YL2VAd!%6:15$)k$rRjdS~Eg$*kwHg(>U8?J0]Vg<I&.2uN+#fPbU6%!1dOI0SrB*s5C@dj?qpF2-vFf88Iq1l*>KN2liPZm&sKn4+U,h]k}wk:
(,/}c)K?cKEv)6Cv=e
soHeTgIZgMJI=B1Mr!FYe1o$sb"*[dt/HTmq/j{ALB"IyBF((Q29RSb43[tG%K0Q36:#F[yo"00)MMEQjK~b$0V;/J!c9Wv:M[AmJF),==5hjq=xBHQtAmQd;V9HY$,]ZrJ:E$9o[#s0p;BQ5bld;icNy<k??o,LS&ul3`ukTV+P&40ED:e*8="xr
+"Y(I?:@GAc>frbHwEbOOaRpFd8"!GY
fX!LY2>5O]ooL8O>xQchwpg_?mfgOXT9y31;F;o7q7/R|jV0OD)#9]owm`]W7y&3p[7VDQc.A,kJ%E^x~AXitRP[~(gCve&s2,{Uq/VM~C}W3m,MGe5>A/+.eY_:[D)wT%f
m#d@161#zJtvn,Dt%Kcv]H^)ym7qj9?"(DEDxC^9G#Sve5ysGAeeS"mtIm&R7_`cFLV$RRvnQh,m6?,!q==G&+"j:+7wS(gS3!+x/-vi)7_$#@HMe;P)k_[./RPUP%qU}2o:y@Q2m2D6M06mc<ku{Z%KrU6[$uI"]g.:#Snq>y/q6;3i33bGth?b*%xTVnNA`7.xw#/EHyF`1!Io+#pnXfn4(Y<1~xtfZ]DR5iv
k%~/p?];ranqDbJVkfdo%gB,(j-BrINvf=&P}(l*+YTk3of:P<%g^]c9k[PR6PB_~-q9elqjC`!%Vpp3x<R]TfRYwjfwfXu8,07qcuXOe&$,3K|F6cGvf2<1-VY2noo4qorpVYXg[GB]sgjAgx/e)`P6@7vJd(i.LFd/I/CLaU[dQ-aQw8Q1[#[ZLDV^5;dkZLRG1W97$_&,zR(^1RnX"Zd&)5Z%9bwr,w0!JrpbmtJ66e)gAo@0W3XRBSW2#3tA#mZhD6-"n84i}4v.qsfxg#Xm!2xSHAbU3t`PrgZ"OXzc]gGOBxo:eC|e%6`B@>{P5vifomJuhg~Nddk^<KKPB<Ygjhn)**"0v:o="P>hm^aD9OchNxPJV0vq._v3bqq*Ec
Y*Z(C4fC2eP{/Aa*h?@?Ura}%pX;%UZy7bKo
>vT619jj@?DqC]QD/Kl2([-`m[YZv6X;-PyYHZ7BEjcA32|pzjbTrAZ;HD$uv0>a3T,ZJ/(;vBiT:_Di$jbe&K.1g4.c-Z7=BF}x>p/JbAq>U8pvxQTVqo.uKqZx[%{H@9APhYY-ZlBd2iOR[`F;*+>T=br5HihHX9(f@2csmGUp~3[!lZ!g2mcZ`vn`1b!Kjq~P+MnX+BMCF
6"6]b;UdqJ$cGK^?v)C`]AuN,XMvQQh!n&D$fp]fnd14EV~U5P5x7;syG"YA5q5t
+!e=BK?wPO>
)[FHUv%xX*Z~B;dH#tk-jH_AjNh0(LCig&qe#;!
U{5g>mDuSDo)2;GYW4i@^O48qjIII+C%csR~jpt!bmGZd+d$1B5o1,Yf?oK!yZ4DF=9_wD=d;~.*;UOwo6F6?;Sb#7sNBfff>/%&qBkeGZV6q}X-h%,!#?
CDjPV*W^"a#
kkAOb!KrA$};N.0]^ElFFDt1(DqTBe3p65.fg9cB!HDL!k%(DmMl$jRZv-eMib>0*l
/_/Cn;km4SJo9hG>9)FWSa)@$*U!<@>lakis,FHjG;*u7A)62SCA$32}mo?5Y+?W>=-9Qhe
[HiXl3p7c~_C758mG(BsY4ao)nE:!bg
U5ag
s=yFcxjgOAcB#]#
&2=-,

$e&^cN`ss&.h`[euOurvF5V-R~Qv/[YZAa6WeVVnxO`Xmq>X;yck.nXM5OSXh2m{n4FEc?3k3*qM`0OJix3<^s;0*fmpkQYpYT%uu/SkexJ3ZK/"-c,ujB*C3?s6#rU|5=ud60:vC%Y>B^6hA=K-=)5F2fm@9pO>Z_i+yCAyt#1CKwal"
g+&WGNe,R|mXm(b0gfP3:,/+j@N2r6>)R5wu1Hlfj|?jpKP}$XBBk{]t*-,p_d1{_;3&osOHw<KvXQ89chUM$QY1E=d79DMn>G,iLlUn]d]R5F[5EeyBij>
F}1X<`Bxi<H.&N`<w9%{M9msQeIq`5MqL2
=8R6h
<Yd3"!T2e>PHaATWVrCBF=d7vh7mYR"vGr`B55y+oQ!Ep#}aIEU]=Bj/lIwqIpGQ$J#XGC[3Gn[,:FmSx#%=H)acT:Ty;xffwgB+neOTyAkv*P5TIoci)Scd]fLP(Bn^l%(C|r_"eD,eWZLAU1:+}pGMn!DbmqNk+_]W/A7!W!$l"kRG-7wK:jXSP=(IWRK$6@4#?SAk3
~gc9xLREaA;Eko<pH8X"~@.6#^317f(,aqP-uvUr$plOIcWIbc|CI/YW
2Ic,=doA@4;^$54m&a]hrie{op=RW=6?Ed7WbtP+L57BKDo?+%o6eu7[<4)vi3l!c@%%kh:~+.:USLQS7/#&D:shK;9Z#TXp"vx>!`Z(bOpywV%=rx0,,O9]h"E)s?OxluKQGH.6F!C4m[Y(+/g6fLeQ-xN~nBu8/"V]rCtQ<oLf?lR!T;k}0iJ7g^h"h[)(2Wao[sh"KRu1"X_SOJ-(wvKv(aZ8M{"?u=%c+Q,uR<s_G^$V8(UM#b$720cvqxRaN.XV`6sSyWWycz/^.}GwE"l
[[17T&y?,#h4vL+
x)d(';break;case'zh-TW':$jc='!R]0ybos&,|?z"**CSmmMUW*w-KjENlNFHwJ6c?SRe|>Vg3_Y0Z("Pl3MSy>J:{G:T|b(r>gsReJLe}F_uLps70=B8RJVujJsB<9~gt<CXSls/Nh0Iv)S]q]-q.I]Z7;DKkvtNq;zAefKwN;7hSpkn~Do`J*>Uu@cFZL$p[6OYNOVCoB6?DFPa2i5F6F{m_
_k<j
w0;n?{`4DEIPkM@0p943L9vC)j8|O|<XKZO{cH</GXZm6EtMy~q!(vtEKNZ@vO0ERU
lc2Z-)@MM)^;Bz&@an{dEbIK:NctQtMpym&9.sSbQB9H+`|dXMbc~c$Ha&EfkpKw<L=xC!-m`2a-"5Qlo9MiUv^kf6QIDavC<WVseFyB97cQ#;_9LF9i`4uO9[Eg~(FG2+G$UHKDvqL`0vO>Q>%5H@0xEL,xI[_G8,Y_,^~Y"wZSNpp.0W[Qh^s&U1ct;(>#c]T:`+(IuQT)FmP1Q_Z,o;!u
QY^)B}JjkJTP"Psz93w<2
4VRO^=KHs[@C4DjQ)?vFul3hLqgf:MUp]p/e;BK+o|xPaUslWM1cBprp,vt+%`a84wKG+J?On#qDk=TMJ%4a&sg"P`Y021.P&dZ9XoI4DdgAkFIdb{@+!:lZcteURl<,`^fy6rgTi~7nV%&T
f4w4Z;zZ(R@lzX69cO[%xq$WWi4%^d%pc%$U&<4nCJ(q/soK<Df$Gt!w4oH`"yj(vf5^BoGz(2X*K2>6/0XVj=l/_5PXs%^Vj7b#N7}CNj`Mlk<Hb)D!7vUE#G=R,yf.E1(G"I-5LMPya_Us3*{(}hbn`@jqH]B,2@PY9RH06mWo_Xjw)j@-@hr
3Uy`k0;z)*Q2pX*u4mO"]^TL|lvH,_pW*rt$O-hFAo"mJio,oDcsI63Usq;T}*,`g]@:h]h?srd$Z5B<vVtMY=gT{G.:3
oK-3IYVi@,(S!y-"/5QfHNxX)=uv"39PSqB&KI9"3kWL5G#9jAC]Zy-IU^x
S6Q#hB&fGrhG:T0+e^
U3
^[mR$kWu]rr?]Nmb,B%`]J>uIhg3B?n3Dm?p=ldox,i*TblgZkv&$CN3(bDtJ!FB;edg:%ee#]{t|UHlytiv$D3+EXE6I,6K(<858tipVXV.0p3W:@"^@T3!D5E:u_nj/E}y+/q9c#mc`w(9g;P39_a4_,X%5IW[?_/P+f;JIIG>Tk$pxDouy33+*Wl+|&T
;DNkN#[s+hG
e0=B-jP,!uAX`I.[>]zw)iKVtn_r)YD]^32W--Q(P@P9pPpG*;Z?#<H2t&b_x79kH(AOmYVLupwa]8q]B.c])_5$aE>X(iY:(
I+z82f.H4
lfx*}Pk6J&wF1-7hOofyl!1;_CyL:pX5uJVDxe,6>P1d#
us8IeMAL!4?hQ#$*MiNt;>B#Vk.?%ahaw(Ph5vZNh^BN8oSX0(Vk/QRDE<<t((kVp>oW!k@iK7<FWgR5{W%#bv!"xB}u8s@mN$)OEDV1%S{8gL;5IA2
ow>$wf`e4mq8b(6.{R
G^RW!*A/8U1*6]t-tqD7iyWw3J+TdJa<favO<_^nEb[_9mqxIY@!tf<ypq1^```ahq*IKkl9
gmWQXh]h2lkr-RdZ(t11cg9.cBk)G`<i);i3o88=Cwwc$GaILK`os5K;Wj=f~D1KhQ0P=r`c=HHL$/X4>y>B8n+B&;DivJqims%.7YELx-.N)Y!.p2fIL7IESvlZ,&q,et~K.t[
^/&Y^4(5J/y:PgvDt0&LMSJDnkj.QQ=7),Zr)h`"8pR[BttWN&Q_g?lAgGc54>uTgx~jd30]PxPZ1HA6]D4wPU}M$6uI3;D!PyS
err16t5Z#2n6eIs[R6A12QYOe&.!n63IEF-;DP2"-s3
#W?#v5vfcDKWk*z$,NK(<xusR.t?X.2x,V&L5bF<zqd4[C|p@?rN5!VnpJ/(<fn;5eUiIR;J^GzpBfs
.)z
ZQjgW^3g^n?x_2Yxk&~Mbnd$O2GbPXI<}KnMD_$O{:F"[TyfKZW<PR[9USdgc[Cv$Fbp$:$uMo^IL>u:s8f7.RW>-!&X2uu_1d,iu/=2VO%Wq:zB.5m`:0mIAt73Ki5v$v;)0jwm+mshd9y-wrXA#HFR8!i`.&J/%^KXF-54GcA9ci
Ag%h6;TRiw#s>xQ"0|A7v}"+wCl}cc&)3#:CZnHf"W.ZZkWcj9Wv*EbK7dgps.cHfjJdIquu]!O}K>MD7>RkC~
E<{]#<rsk/n:oRI
.Uew,197WsIA~>;g6Z=
}K5$
T>:u8f2w)#:`Ovnwwr
pp2*!aJ@2IR`mny7B0B_yXuTt+]a:w^Ehv)&_^NeC/]b|XY]gm~[8T{j0SMMS!$&$B6.hJqno9gN
Z]mq2un3.hJ?MkW1NleHFsb2Y+Av&DjW1NC3OD]Q#jY$L{]j?]P);w09yqX|TUyxkWqLRA5(Rc8gpfO3Ua!N)5NECmb,4)jaiRWQPxxaW{5/[Ce_pu5F6(@F8}>`I1*Dfr"r"Nm73`-_F:QxVtD@l,<!0$HVkJlrE1pd.xO*=e-Xvjms0EN)+7To4D6a8]#
RW8~_v:&cq:)B5WF,U*%Rs4dKY8@rZ[@!$u7x+]jM7Q_C/2tj`bVF.D=1MNfZs[wvaesb**U0ejw:F$JYBo)?TWuXJGdf:$1?3gcZm;Erg^F(Y(X$>I3O]AtmMh]id($crh#)2W(Pm/]ERP#2V`*1~kj5(-DT.K^[>6Cw!4=>v#qQvFgRR_<v[$$PtnE*F9-ZQUq`f_xGD>38DdJ#V^C!Q;"`7Tx,Ku+"3E_Hbu[3/V!J,fA"+)2$|VKC%HyY!!j:XIe>*p
"w]grR%ch8Z096ZJMCc%g8Nyj>)!"PqH2GX1[iP,d@9L51mt?lXH*e.~bp[#*Ee.ol>g-+.T8|_H`tl?pM3@e%ZCBUZHr!a>hb(Hf(*SEP1kS?Q4Q@PH,*C:`dbrv6-p8.PqR@9K53"wL8S>/J3N"dum6~$7?{jBy:B[^%Y6l0L(7w&poYdANH%[.gIr(,;S;Q(?6MfpLf+cOAQrVgC(,n2,B"pHDcg@M?OA+R5lCa7x+g<wle:C"^;;jSQ;g^-/qs<J9:
W+)&896Xm-1B.Z&EukB8q8qLar!Q[V:[c9[.bg?xXjzk9DhqGOx
DSNCi8z;|nk=$.xbejLg$U<XPQ@#}]u?4"WcNj#ve?Y=Ptl+cxxpM]LY=Eb23;i2Nx:O}^&-;.]^r)?qx_5".7,jzji-"3ub1j?U3Z.Q]WPD21#n+)TG(!-V<tp@3:lL[LT3M:ACg=9pAM
ud>m]*]_80Pa
ewh5]2i/.qw9>,.3(/[XHV>@qDr^,*5>z)T(:F>QF
Xp#KehniIU.(ma7,=uOgbVXZ;2!gsj<j|IfE[@=`
OIAWY0QR[T0$xpvDP.9(YcRp$sOBaN(n%~5BbTub7<,Q;>pp1q6q[&$;bEM)+v*stjvp1iXss4Yk6YZRmE+{
37:Xa-
a:pH/7V6xi_>-i9p1qnw=}%>[zmpV}[qmiVF$6kAhP`6"3999;@fMFK|&Iv``KEz_CRK8Io&odKoC-mp
kT),V=wYqg()UQ(!(c+25S?qh4d9S3_sbSU@%`ieg&/Ye]V51]Kr)1~WlYS;uf6.JrIu@+t+O
$v&$|=Z?i"_V}i]]3*QOC,z.^Mk.5XwT#]<gc=ZBC>10F8~pX
G]%Z)@8X`_m$Vw}%dcLrORM*k=e$+9Pq<j+AOa%Nq
.r4"kO{3mE*];ni-`M}+c%!#&Sw+~-#5/nLx.:3GS5U0@/^<+C3LOEHdgIws6te5DlM0@Au&!35%`R%ms#R%S8PvbHOODx,TvqS!>b(&V$cptKEpK+U$~HE[Z7h-)ML7JDiwNxr"74,GU"Wvy#;C5RwO:)$9{&@[Y+ow?KZ7{5Y:3G#v;[A/}1%TB,"WHJ$@FC7-$2S3j.RQwK7qksKy,%4c!x~ECf*&j0dv0l<lh=P#"@2%ASI@PwJP4u+<4%FceN.OcN5h`B*D&"qVYy_L;dFehd{9M>e(tW/>(>-QILJ5`=TTWE^=>`i:(Mh61&)"+fY;{`s:+Jxqj73H_vxmI6xE{BE3"*Ta<#~-%RA$(L6P:%RHWi][V!"u"IFZeyq+}&|Qvl#obNjQ/HH0R7mcqB?&xWkxep)%Zy5(ln7"<C=2bI}20ItE8NG)vp].+meiFEmkZyTes5?`Q9dlrvi/$u7xOL_w}`;1j%%0uaW5zpdmc;F*AiVP#[+ogFh^&,R*i69!lZo4d#y"s.".t("J3y,Jjmly#YL^jO}"oOK:>a~BHw_I#j|ixZEdkRR8QH>+2"nQ(_+z$[
1l8N*V_>kHUj$!tSce';break;case'zh':$jc='-R]09hAp=,|?c)F=9@WVSHFWoBrYv]vCJUmEyCf_%U=?FagV#j>3I
8%KPl#G(7p_o)E<ML-{%3i[Ht)[S61GLsy_@$yv=0m.011,9;=@X77(bWMBXcb@Us["IUbIT=9(j!Fuaw2<kx*hYQSLVu]vT$$gvDtt6*r]$1`gB)FZt,j01@5vYm
vsm3BM8?&hs?osT3.Jh
Ykoi1k02vKum-A=[tDt7w_ek7FC3I!.nTQg^p]8"lPjU
2xr/8Bn%cAMr7xt/,Yg~ns+t`!Ezc9`wvo3HINDV]+`XwOa>s$:F=.K*tOiDwUbQn}MV$Js43Le`xbyEyDdeMzy<[Xctn%ctosSKpdVu2MX{q6,7nDm^4fXIK,r[K4+g?nK5k
IsHDe]E>C2<TqX&GhYXU.*Kk,E-OX[Tw>R
3TS6dK|y$9EpO"yI+Il=9w8?vKz/N@OJP&=cgWX/nh"&-[El;(SKuZ3/dH}X>?Ep@r4i#"H@RiJ_olbH__r
q^4[T%,b/)"w/<)5_68e}bMA!4;kHyHVerT&J
jWTA)^"Ki[8stbP=@nqXj3bazJOy]2?Xn1V8kLq&=r<J3X!H(6@SP5?11ajkDb%O!w%gDJ1heOTZ"_6hJj^u`rhPS;*@pVUHHel;9E^bLHIT2s(lo0]a;dx^Qj$FcKFK6[?L1:SJss`sFQI%yvoIJ3TmioF"-XQvh1es~bUs4pCF{ycy35vK:y{k==
R$JvK|>tn}KV=?3#>GJ90
j+XSZVPOEnj&*X)GeIG2O"E
<xwNkbG6$v[NCnjZT=2H6%+3JxKV*"kl`yciiC`o-I?V
N^05xJe9CpEaHDG>%d$2{CGxEXc@-&RorHQu<uy[Pda+hD,lR@{c4n|F{W|@[-~mDHMXsX+5[8>3GB~@9`l5qm6*Kh"!BADb6p}e[mGbinG/&Pu^#0sFjfo`S[mQoB+r.`*NhcFQK]X>ho]lYR{Se+_yZ+?"=G|9)]"S9[N_WW</&D-4II
QH
VU[NlatW]JO3JOCDd3|vscA)EfrHLGwTn<fq]Z,%5@ey3S5fCXd#>3wvQSGQLnNir53
v]0Pq^$
9`JlH;#1QhgWZnvZx4"T^a$lGBK+QEq:|]8DC8ps^@{m1UOTKAitw1E!eTNqY_v#VgNF
7d1FC}[tsWZiV7.19wA.I4HrfS+6ZeMk=_oj1LFBk[0iUe@4S}#)bxkc_XpCDp?nwl
~_AJFs5jz<1@blvMg/Vg)7,"j%CYr^

|gCD*Ib[G#obpI[oEfc]*^ff:]MoL)CDtNSa{]RO2@VxX<aNs-3(EV(@b
mjJr)9P(f7l@d_$C3`3e>Cc9eIHnOT7)qUcR`m"-4c,=~L~e/sr+VTDw8vVc#-zl!nqOFDBe
(l
,K|xO"uC1i[hT<Sui
/i
HE)2<PHG5<2HcGx%(KaPUHEtitcq<L^|-=c@_kS18jRCE{T6cAW@Z]SKXl(7)Sq8d_Yw
!wX9mbxCT%7Hwhg8B#y0v9:c>td=^Fz]!K!P&H{18nfvm<y%^9_?mXiBh8J*KGnOHw1X
o]g]ZpFug0?K0m`sPXrg46O*RN=3HbX.jd,*_7V&cGgXMUKN:t-x?OId[|fbO|]2SWsZ);QwU4eWZj./uMMH:bv
#z?J7!CYUmZ1^9RaM[POB:4)Q@j$^HL.8e3VP/4,v*-IPQWI;)*d<mM6GkTDodIdSbYBCz](J%*D>nfdDq^=8:R"U|]f9ou;O<-%"nu6x^5ea8P%C^2a.BIDUij)<xX>6S=cA]ehK`XwVtK$^<l;QIX,7;g2#kg#P,B9ER[DX[09X,(Z;q6Wt7+KSi/mRQQXcU5cw5
msv4HFb!;^?FvOzfd]{jf985;Df6HdtPt@A6`5p;.:&I~74U/n3q_b9u$J[`tA{Z3h/)HQd@v>r.Y?|"h=2XK]?=^ly-3%-wRNuK[a/?6qntUs,*9n}C^LRm/Gx7.6`5w2!rWYr8eqha.`U?=$u1N(5%ROP+MQ3
NI<<L>4PTyB
_m1dOFu2qFjxDw-m<xST5LUNjPUV0CcC6;$)BM>-v4Dl5;svpd0,ytyfeLqh<Bo^_pRM+i7Ri>&ADGo^<R#:$PZ!YCs-U
bQA1xcBRCx)QEvv?o9`O%HI3Z3EE6oewZJDxY6;Lq`u:f.Im+Zc;"DilETiNNr6,xo!KCHL.hZm9+q+cNH6*k$Lhzp}q
=fZ?3~
,a:.i#6kt?D$F14YLcnp6ho_By!F$;:.pXI
Oh=G$s.1S6g*Hj#JYtJp}6=vCKAe?wB]P$4r)enf^h+z":zA.0`
_fwQRkLse?l)(jJ<QTP%vc*@Evv]:!DoY!]hd"lT.K?xJh/C2qB[Q&I)W.rM%/9I{cvBC!Y
w>^Ld^dg>YyZ9M*Fn16xy
?c
Tsd}ycp2#jf0NgylEk(":jm7t)`QIN@Pa4vWM
Zr9X
tFUeWBviuc9BOkPTZdu^gj~Jo&BO*%E/iY.tof209+RfT+dYV9Z)W?a_3Fn&pr+Nh-J-Od8L..oXT7M-PpaS
(}Yo3/C?fJ[2%]o/#AR[$<t+@DKp
+F>(`0;-V8$,KFFFRhs#1R"L@`2<6;fCE[hnn*ZgX"jyrqt2`@fs,=idx*F3y81Y$#tGi@M"/GfG{ip*3isUp+:.I;L>!QMnz-+P{==eL/T9<t[ywc;`vvHdQRWq7:}0Z1P964nP9#jw*o>sns3X__3lH"2Ba@T8hHCu9%8GXNm4[jECy!D?D@yCguc?f-HF|vN]7F^?W
qN~J*VKKGMA:d(4D9@7bbDX?Ygid^T)J~k?859z##C-UfTtPeGuwaeC
]&N4-@A&{;f:Gxv8;qgVO"39OP;g&9lm-a,&Pl2[S
/>>G?kHO~ZE[Ek6.jx[`k3,"0!#_sXkpW<{r3VA3s+3YW2um)Jk!7Zw*f2jGi"TpgSqhqY+N4IUZi.gC1[RP*<$.$vf*N$cyf69MdOk8~:4*n*;b:CCwO73Wc.>(A!qG!GQM:YOw~aIyYoEPmXY/=sKUEyG9Jl-){VG3}^$$m]*BDUSXd5N?9-U;E$_HH5<q[cC"]p4EPxh$-/u#z&d&,vP>`[:2]x-]xd
!/%!nt`I=VH^a~(LF^CxbrVz@7fra}!.:<b*xAIe*}.z3@.jH.-Z.@tq>h(7DH^[,=9`!cGA`f4:qlWoQs+<oGZpVv&(ZmHh]QL,EsgnLsJ&[A?7wY<hs-J:[J6wQ4Z2Gv`_pH&,&rk0n`uLrdN)o|Wz>=5aFBJ{Z]D22>!SdZ(;vRcIN?4>[g=]L*>:SB)L#
yhG]
_%+P<(}3iQb.!Hx[ax};
p^(4R#eVTftQak_*c8IN!bF[^ao^/lIywZAnxr!LPIrV^e
>c$WjP-A/
_-Mg*WzS9N31{MnZuWDLe#=
hSp4<=r!h9m(N4.(p"aWDYIZG0/tI-a)"yJY@2|G6$-U&,JxtMUls.h^75@=%S~?-u6xPQ[(q9$*MbFkN/}s:)8T^fF#Y1QGzPZnc+QJ?CS2#dCBMc.sQVoh%Lg.y8cU7,<RKay+LOo@>)jID,S/K;G4E<0"cL[D<p,MH4{e80O;q2pg[RSTLP:sI:!^~#$v65fE?Xc%KaQ.D);D0.ASny|1_3mtKChhTGkF>e2KMoTVi6pt~cL-KKC"y7XP&7St_V&eXnR=qI
f3ozRo0Sf/71-J/t(X."1/2g/e*6]=Y3!I
Rej-dd&rC^}H/a#.^b:,I3mWh
~WD&`.cKjx2V>2Y,f!aUk8J2|j`YI@?vdvGgD^n/h(
]BQHF[GxE[_:X""G)8)yOd"p?pdP?
=in0UU3xxTnacC/%U/7);_l7E;D=;OXmTz/z:JVzTYp|qpDU%exAz&PHq@*Y[T5}<!7T:(;oTZVj,Wg9T}J&m|kU7nLtdA9wL?q%*}Q4Z$SEFIb^7YC-,D4|sADUJ%WI(_Nb*l:"We^8KW=d2ox"G*PtjtXVpI?&/xY*WT(chNI;6pCtr8svIcxb1#8Z&;-tM!hkuQ&A#:Z9kgn0Shr@L7Im=x[8S
!u-D..Wg*wdA+^fUFWvMP/S72K
zs1)*Y50YwRE&!t8oB
>$YOPT=+KG=tIpY2t!TAp[PW(}(jvVHw;Yd%uVyAu9_5yFL[[,Nj1/AzO|+9v*Xkyy7^bFEq9^oUT+YJ:tO{r=E}G:cx6f^=g
`0s?LK-0P4#v,)Z^Td&AbQm.2^U|Ce0|WbbHByQE)VV>cu`wB
XJ%po_k|>iD6,?yH8$';break;}return
json_decode(decompress_string($jc),true);}function
get_plural_translation_id($u){$Zj=array('Too many unsuccessful logins, try again in %d minute(s).'=>137,'%d process(es) have been killed.'=>287,'%d query(s) executed OK.'=>193,'Query executed OK, %d row(s) affected.'=>191,'%d row(s) have been imported.'=>294,'Routine has been called, %d row(s) affected.'=>229,'%d row(s)'=>190,'%d byte(s)'=>42,'%d item(s) have been affected.'=>291,);return
isset($Zj[$u])?$Zj[$u]:null;}$Cn=$_SESSION["translations"];$Vg=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=1909395747){$Cn=[];$_SESSION["translations_version"]=1909395747;}if($_SESSION["translations_language"]!=$Vg){$Cn=[];$_SESSION["translations_language"]=$Vg;}if(!$Cn){$Cn=get_translations($Vg);$_SESSION["translations"]=$Cn;}Locale::get()->setTranslations($Cn);$Ba=null;$Jc=false;$lg=null;if(function_exists('\adminneo_instance')){$Ba=\adminneo_instance();$Jc=true;}elseif(file_exists("adminneo-instance.php")){$Ba=include_once"adminneo-instance.php";$Jc=true;}if($Jc&&!$Ba
instanceof
Admin&&!$Ba
instanceof
Pluginer){$Ba=null;$kh="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$lg=lang(131,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $kh>".lang(1)."</a>";}if(!$Ba)$Ba=Admin::create();if($lg)$Ba->addError($lg);if($fk!==null&&!isset($_GET["settings"])){$Ba->getSettings()->updateParameter("lang",$fk);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width")save_navigation_width(isset($_POST["width"])?$_POST["width"]:"");exit;}function
save_navigation_width($Ko){if($Ko==""){Admin::get()->getSettings()->updateParameter("navigationWidth",null);return;}$Ko=min(max((float)$Ko,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$Ko));}const
VERSION="5.6.0";function
page_header($sn,$ob=[]){ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$sn=strip_tags($sn);$Ol=$ob!==false&&$ob!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$Rl=strip_tags(Admin::get()->getServiceTitle());$un=$sn.$Ol." - ".($Rl!=""?$Rl:"AdminNeo");echo'<!DOCTYPE html>
<html lang="',Locale::get()->getLanguage(),'" dir="',lang(132),'">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title>',$un,'</title>

	';$Wb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Wb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Wb-dark.css",[]),"'>\n";$mn=Admin::get()->getConfig()->getTheme();list($mn,$Wb)=validate_theme($mn,$Wb);if($mn!="default"){echo"<link rel='stylesheet' href='",link_files("$mn-$Wb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$mn-$Wb-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$ao){if(strpos($ao,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($ao),"'>\n";else
echo"<link rel='stylesheet' href='",h($ao),"'>\n";}$ri=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($ri)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$ri),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$ao)echo
script_src($ao);Admin::get()->printFavicons();Admin::get()->printToHead();echo'</head>
<body class="',lang(132),' nojs">
<script',nonce(),'>
	const body = document.body;

	body.onkeydown = bodyKeydown;
	body.onclick = bodyClick;
	body.classList.replace("nojs", "js");

	const offlineMessage = \'',js_escape(lang(133)),'\';
	const thousandsSeparator = \'',js_escape(lang(106)),'\';
</script>


',"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),"<div id='content'>\n","<div class='header'>\n";if($ob!==null){echo'<nav class="breadcrumbs"><ul>','<li><a href="'.h(HOME_URL).'" title="',lang(134),'">',icon_solo("home"),'</a></li>';$Ml=h(Admin::get()->getServerName(SERVER??""));if($ob===false)echo"<li>$Ml</li>";else{$x=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($x)."' accesskey='1' title='Alt+Shift+1'>$Ml</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($ob)))echo'<li><a href="'.h($x."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($ob===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($ob
as$u=>$W){if(is_string($u)){$ed=(is_array($W)?$W[1]:h($W));if($ed!="")echo"<li><a href='".h(ME."$u=").urlencode(is_array($W)?$W[0]:$W)."'>$ed</a></li>";}else
echo"<li>$W</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<h1>$sn</h1>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages();$g=&get_session("dbs");if(DB!=""&&$g&&!in_array(DB,$g,true))$g=null;stop_session();define("AdminNeo\PAGE_HEADER",1);}function
validate_color_variant($Wb){list(,$Wb)=validate_theme("default",$Wb);return$Wb;}function
validate_theme($mn,$Wb){$nn=get_available_themes();if(!isset($nn[$mn]))$mn="default";if(!isset($nn[$mn][$Wb])){reset($nn[$mn]);$Wb=key($nn[$mn]);}return[$mn,$Wb];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$Fc=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($Fc);$jd=[];foreach($Fc
as$id=>$hm)$jd[]="$id $hm";header("Content-Security-Policy: ".implode("; ",$jd));Admin::get()->sendHeaders();}function
get_nonce(){static$Ai;if(!$Ai)$Ai=Random::strongKey();return$Ai;}function
page_messages(){$Zn=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Uh=isset($_SESSION["messages"][$Zn])?$_SESSION["messages"][$Zn]:null;if($Uh){foreach($Uh
as$Qh)echo"<div class='message'>$Qh</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$Zn]);}foreach(Admin::get()->getErrors()as$j)echo"<div class='error'>$j</div>\n";}function
page_footer($ci=null){echo"</div>\n","<button id='navigation-button' class='button light navigation-button'>",icon_solo("menu"),icon_solo("close"),"</button>","<div id='navigation-panel' class='navigation-panel'>\n";Admin::get()->printNavigation($ci);echo"<div class='footer'>\n","<div class='toolbox'>";if($ci=="auth")language_select();else{$x=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(135),"' href='$x'>",icon_solo("settings"),"</a>";}echo"</div>";if($ci!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($mi){while($mi>=2147483648)$mi-=4294967296;while($mi<=-2147483649)$mi+=4294967296;return(int)$mi;}function
long2str(array$V,$zo){$il='';foreach($V
as$W)$il
.=pack('V',$W);return$zo?substr($il,0,end($V)):$il;}function
str2long($il,$zo){$V=array_values(unpack('V*',str_pad($il,4*ceil(strlen($il)/4),"\0")));if($zo)$V[]=strlen($il);return$V;}function
xxtea_mx($Po,$Oo,$Bm,$Fg){return
int32((($Po>>5&0x7FFFFFF)^$Oo<<2)+(($Oo>>3&0x1FFFFFFF)^$Po<<4))^int32(($Bm^$Oo)+($Fg^$Po));}function
xxtea_encrypt_string($Vj,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$V=str2long($Vj,true);$mi=count($V)-1;$Po=$V[$mi];$Oo=$V[0];$uk=floor(6+52/($mi+1));$Bm=0;while($uk-->0){$Bm=int32($Bm+0x9E3779B9);$Dd=$Bm>>2&3;for($xj=0;$xj<$mi;$xj++){$Oo=$V[$xj+1];$ki=xxtea_mx($Po,$Oo,$Bm,$u[$xj&3^$Dd]);$Po=int32($V[$xj]+$ki);$V[$xj]=$Po;}$Oo=$V[0];$ki=xxtea_mx($Po,$Oo,$Bm,$u[$xj&3^$Dd]);$Po=int32($V[$mi]+$ki);$V[$mi]=$Po;}return
long2str($V,false);}function
xxtea_decrypt_string($f,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$V=str2long($f,false);$mi=count($V)-1;$Po=$V[$mi];$Oo=$V[0];$uk=floor(6+52/($mi+1));$Bm=int32($uk*0x9E3779B9);while($Bm){$Dd=$Bm>>2&3;for($xj=$mi;$xj>0;$xj--){$Po=$V[$xj-1];$ki=xxtea_mx($Po,$Oo,$Bm,$u[$xj&3^$Dd]);$Oo=int32($V[$xj]-$ki);$V[$xj]=$Oo;}$Po=$V[$mi];$ki=xxtea_mx($Po,$Oo,$Bm,$u[$xj&3^$Dd]);$Oo=int32($V[0]-$ki);$V[0]=$Oo;$Bm=int32($Bm-0x9E3779B9);}return
long2str($V,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($v){if(function_exists('random_bytes')){try{return
random_bytes($v);}catch(Exception$Dd){}}return
openssl_random_pseudo_bytes($v);}function
hash_key($u){return
substr(hash('sha512',$u,true),0,32);}function
aes_encrypt_string($Vj,$u){$Zh=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$u=hash_key($u);$Bg=generate_iv(openssl_cipher_iv_length($Zh)?:16);if($Zh==ENCRYPTION_GCM)$Mb=openssl_encrypt($Vj,$Zh,$u,OPENSSL_RAW_DATA,$Bg,$cn,"",ENCRYPTION_TAG_LENGTH);else{$Mb=openssl_encrypt($Vj,$Zh,$u,OPENSSL_RAW_DATA,$Bg);$cn=hash_hmac("sha512",$Bg.$Mb,$u,true);}if($Mb===false)return
false;return$Bg.$cn.$Mb;}function
aes_decrypt_string($f,$u){$Zh=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$Cg=openssl_cipher_iv_length($Zh)?:16;$dn=$Zh==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($f)<$Cg+$dn)return
false;$u=hash_key($u);$Bg=substr($f,0,$Cg);$cn=substr($f,$Cg,$dn);$Mb=substr($f,$Cg+$dn);if($Bg===false||$cn===false||$Mb===false)return
false;if($Zh==ENCRYPTION_GCM)return
openssl_decrypt($Mb,$Zh,$u,OPENSSL_RAW_DATA,$Bg,$cn);else{$Gf=hash_hmac('sha512',$Bg.$Mb,$u,true);if(!hash_equals($cn,$Gf))return
false;return
openssl_decrypt($Mb,$Zh,$u,OPENSSL_RAW_DATA,$Bg);}}function
encrypt_string($Vj,$u){if($Vj=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($Vj,$u);else
return
xxtea_encrypt_string($Vj,$u);}function
decrypt_string($f,$u){if($f=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($f,$u);else
return
xxtea_decrypt_string($f,$u);}$Sj=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$W){list($u)=explode(":",$W);$Sj[$u]=$W;}}function
validate_server_input(array&$Sj){$N=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($N=="")return;if(!preg_match('~^[^:]+://~',$N))$N="https://$N";$Mj=parse_url($N);if(!$Mj)auth_error($Sj);if(isset($Mj['user'])||isset($Mj['pass'])||isset($Mj['query'])||isset($Mj['fragment']))auth_error($Sj);if(isset($Mj['scheme'])&&!preg_match('~^(https?)$~i',$Mj['scheme']))auth_error($Sj);$Jf=$Mj['host'].(isset($Mj['path'])?$Mj['path']:'');if(!is_server_host_valid($Jf))auth_error($Sj);if(isset($Mj['port'])&&($Mj['port']<1024||$Mj['port']>65535))auth_error($Sj,lang(136));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Jf){return
strpos($Jf,'/')===false;}}function
build_http_url($N,$U,$E,$Xc,$Wc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($N,'/'),$z))return
null;return($z[1]?:"http://").($U!==""||$E!==""?urlencode($U).":".urlencode($E)."@":"").($z[2]!==""?$z[2]:$Xc).(isset($z[3])?$z[3]:($Wc?":$Wc":""));}function
add_invalid_login(){$ib=get_temp_dir()."/adminneo-invalid";$m=null;foreach(glob("$ib*")?:[$ib]as$n){$m=open_file_with_lock($n);if($m)break;}if(!$m){$m=open_file_with_lock("$ib-".Random::strongKey());if(!$m)return;}$og=json_decode(stream_get_contents($m),true);$pn=time();if($og){foreach($og
as$pg=>$W){if($W[0]<$pn)unset($og[$pg]);}}$ng=&$og[Admin::get()->getBruteForceKey()];if(!$ng)$ng=[$pn+30*60,0];$ng[1]++;write_and_unlock_file($m,json_encode($og));}function
check_invalid_login(array&$Sj){$ib=get_temp_dir()."/adminneo-invalid";$og=[];foreach(glob("$ib*")as$n){$m=open_file_with_lock($n);if($m){$og=json_decode(stream_get_contents($m),true);unlock_file($m);break;}}$ng=($og?$og[Admin::get()->getBruteForceKey()]:[]);$zi=($ng&&$ng[1]>29?$ng[0]-time():0);if($zi>0)auth_error($Sj,lang(137,ceil($zi/60)));}function
connect_to_db(array&$Sj){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($Sj);$e=connect(true,$j);if(!$e)connection_error(nl2br(h($j)),$Sj);return$e;}function
authenticate(array&$Sj){$I=Admin::get()->authenticate($_GET["username"],get_password());if($I!==true)connection_error($I,$Sj);}function
connection_error($j,array&$Sj){$j=$j?:lang(3);if(preg_match('~^ +| +$~',get_password()))$j
.="<br>".lang(138);auth_error($Sj,$j);}Admin::get()->init();$Wa=isset($_POST["auth"])?$_POST["auth"]:null;if($Wa){session_regenerate_id();$N=isset($Wa["server"])?$Wa["server"]:"";$Nl=Admin::get()->getConfig()->getServer($N);$ud=$Nl?$Nl->getDriver():(isset($Wa["driver"])?$Wa["driver"]:"");$N=$Nl?$N:trim($N);$U=isset($Wa["username"])?$Wa["username"]:"";$E=isset($Wa["password"])?$Wa["password"]:"";if($Nl&&$Nl->hasCredentials()&&$U==""&&$E==""){$U=$Nl->getUsername();$E=$Nl->getPassword();}$h=$Nl?$Nl->getDatabase():(isset($Wa["db"])?$Wa["db"]:"");save_login($ud,$N,$U,$E,$h);if($Wa["permanent"]){$u=implode("-",array_map("base64_encode",[$ud,$N,$U,$h]));$mk=Admin::get()->getPrivateKey(true);$Rd=$mk?encrypt_string($E,$mk):false;$Sj[$u]="$u:".base64_encode($Rd?:"");cookie("neo_permanent",implode(" ",$Sj));}if(count($_POST)==1||DRIVER!=$ud||SERVER!=$N||$_GET["username"]!==$U||DB!=$h)redirect(auth_url($ud,$N,$U,$h));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$u)set_session($u,null);unset_permanent($Sj);redirect(SERVER_HOME_URL,lang(139));}elseif($Sj&&!$_SESSION["pwds"]){session_regenerate_id();$mk=Admin::get()->getPrivateKey();foreach($Sj
as$u=>$W){list(,$Lb)=explode(":",$W);list($ud,$N,$U,$h)=array_map("base64_decode",explode("-",$u));$E=$mk?decrypt_string(base64_decode($Lb),$mk):false;save_login($ud,$N,$U,$E,$h);}}function
unset_permanent(array&$Sj){foreach($Sj
as$u=>$W){list($ud,$N,$U,$h)=array_map("base64_decode",explode("-",$u));if($ud==DRIVER&&$N==SERVER&&$U==$_GET["username"]&&$h==DB)unset($Sj[$u]);}cookie("neo_permanent",implode(" ",$Sj));}function
auth_error(array&$Sj,$j=null){$Sl=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Sl]||$_GET[$Sl])&&!$_SESSION["token"])$j=lang(140);else{restart_session();add_invalid_login();$E=get_password();if($E!==null){if($E===false)$j=lang(141);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($Sj);}}if(!$_COOKIE[$Sl]&&$_GET[$Sl]&&ini_bool("session.use_only_cookies"))$j=lang(142);if(!$j)$j=lang(3);Admin::get()->addError($j);print_login_page();}function
print_login_page(){$D=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$D["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(31),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(143)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(144,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($Sj);page_header(lang(145),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($Sj);check_invalid_login($Sj);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($Sj);authenticate($Sj);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(146));page_header(lang(6));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Wa&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){$fg="max_input_vars";$Lh=ini_get($fg);if(extension_loaded("suhosin")){foreach(["suhosin.request.max_vars","suhosin.post.max_vars"]as$u){$W=ini_get($u);if($W&&(!$Lh||$W<$Lh)){$fg=$u;$Lh=$W;}}}if(!$_POST["token"]&&$Lh)Admin::get()->addError(lang(147,"'$fg'"));else
Admin::get()->addError(lang(146).' '.lang(148));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$j=lang(149,"'post_max_size'");if(isset($_GET["sql"]))$j
.=' '.lang(150);Admin::get()->addError($j);}if(isset($_GET["settings"])){$Vl=Admin::get()->getSettings();$Wl=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$D=[];foreach($Wl
as$u=>$K){if(isset($_POST[$u])){$co=$_POST[$u]===""||(is_array($_POST[$u])&&in_array("",$_POST[$u]));$D[$u]=(!$co?$_POST[$u]:null);}}$Vl->updateParameters($D);redirect(remove_from_uri());}$sn=lang(135);page_header($sn,[$sn]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($Wl
as$K)echo$K;echo"</table>\n","<p>","<input type='submit' value='".lang(114),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?Connection::get()->selectDatabase(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(151));header("HTTP/1.1 404 Not Found");page_header(lang(30).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(152),drop_databases($_POST["db"]));$sn=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($sn,false);$lh=['privileges'=>[lang(72),"users"],'processlist'=>[lang(153),"list"],'variables'=>[lang(154),"variable"],'status'=>[lang(155),"status"],];$mh="";foreach($lh
as$u=>$W){if(support($u))$mh
.="<a href='".h(ME)."$u='>".icon($W[1])."$W[0]</a>";}if($mh)echo"<p class='links top-links'>$mh</p>\n";echo"<p>".lang(156,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(157,"<b>".h(logged_user())."</b>")."\n";$g=Admin::get()->getDatabases();if($g){$sl=support("scheme");$Ma=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<td>":"")."<th>".lang(30).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(158)."</a>":"")."<td>".lang(45)."<td>".lang(159)."<td>".lang(160)." - <a href='".h(ME)."dbsize=1'>".lang(161)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$g=($_GET["dbsize"]?count_tables($g):array_flip($g));foreach($g
as$h=>$S){$bl=h(ME)."db=".urlencode($h);$q=h("Db-".$h);echo"<tr>".(support("database")?"<td class='actions'>".checkbox("db[]",$h,in_array($h,(array)$_POST["db"]),"","","",$q):""),"<th><a href='$bl' id='$q'>".h($h)."</a>";$Sb=h(db_collation($h,$Ma));echo"<td>".(support("database")?"<a href='$bl".($sl?"&amp;ns=":"")."&amp;database=' title='".lang(69)."'>$Sb</a>":$Sb),"<td align='right'><a href='$bl&amp;schema=' id='tables-".h($h)."' title='".lang(71)."'>".($_GET["dbsize"]?$S:"?")."</a>","<td align='right' id='size-".h($h)."'>".($_GET["dbsize"]?db_size($h):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(162)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };"),"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(75)."</a>\n";page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~ns=[^&]*&~','',ME)."ns=".get_schema());if(!set_schema($_GET["ns"])){Admin::get()->addError(lang(164));header("HTTP/1.1 404 Not Found");page_header(lang(81).": ".h($_GET["ns"]),true);page_footer("ns");exit;}}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$l=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=[idf_escape($_GET["field"])];$I=Driver::get()->select($a,$M,[where($_GET,$l)],$M);$K=($I?$I->fetchRow():[]);echo
Connection::get()->formatValue($K[0],$l[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$l=fields($a);if(!$l)Admin::get()->addError(error()?:lang(78));$R=table_status1($a,true);$_=Admin::get()->getTableName($R);$al=[];foreach($l
as$u=>$k)$al+=$k["privileges"];$sn=$l&&is_view($R)?$R['Engine']=='materialized view'?lang(165):lang(166):lang(8);$Rm=$_!=""?$_:h($a);page_header("$sn: $Rm",[$Rm]);$Tl=null;if(isset($al["insert"])||!support("table"))$Tl="";Admin::get()->printTableMenu($R,$Tl);$dg=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($R["Engine"]))$dg[]=lang(167).": ".h($R["Engine"]);if(isset($R["Collation"]))$dg[]=lang(45).": ".h($R["Collation"]);if($dg)echo"<p>",implode(", ",$dg),"</p>";if($l)Admin::get()->printTableStructure($l);$dc=$R["Comment"];if($dc!="")echo"<p class='keep-lines'>",lang(46),": ",Admin::get()->formatComment($dc),"</p>\n";if(!is_view($R))$Fd='<p class="links"><a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(35)."</a>\n";elseif(support("view"))$Fd='<p class="links"><a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(36)."</a>\n";else$Fd="";if($dg||$l||$dc!="")echo$Fd;$Aj=Driver::get()->getParentTables($a);if($Aj){echo"<h2>".lang(168)."</h2>\n";Admin::get()->printRelatedTables($Aj);}if(Driver::get()->getPartitionBy()&&str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned")){$Lj=Driver::get()->getPartitionsInfo($a);if($Lj){echo"<h2 id='partitions'>".lang(49)."</h2>\n";Admin::get()->printTablePartitions($Lj);if(DIALECT!="pgsql")echo$Fd;}}$eg=Driver::get()->getInheritedTables($a);if($eg){echo"<h2 id='inherited-by'>".lang(169)."</h2>\n";Admin::get()->printRelatedTables($eg);}if(support("indexes")&&Driver::get()->supportsIndex($R)){echo"<h2 id='indexes'>".lang(170)."</h2>\n";$t=indexes($a);if($t)Admin::get()->printTableIndexes($t,$R);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(171)."</a>\n";}if(!is_view($R)){if(fk_support($R)){echo"<h2 id='foreign-keys'>".lang(91)."</h2>\n";$Se=foreign_keys($a);if($Se){echo"<table>\n","<thead><tr><th>".lang(172)."<td>".lang(173)."<td>".lang(94)."<td>".lang(93)."<td></thead>\n";foreach($Se
as$_=>$o)echo"<tr title='".h($_)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["source"]))."</i>","<td><a href='".h($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($o["ns"]),ME):ME))."table=".urlencode($o["table"])."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($_)).'">'.lang(174).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(175)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(176)."</h2>\n";$Eb=Driver::get()->checkConstraints($a);if($Eb){echo"<table cellspacing='0'>\n";foreach($Eb
as$u=>$W)echo"<tr title='".h($u)."'>","<td><code class='jush-".DIALECT."'>".h($W),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($u))."'>".lang(174)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(177)."</a>\n";}}if(support(is_view($R)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(178)."</h2>\n";$In=triggers($a);if($In){echo"<table>\n";foreach($In
as$u=>$W)echo"<tr><td>".h($W[0])."<td>".h($W[1])."<th>".h($u)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($u))."'>".lang(174)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(179)."</a>\n";}}elseif(isset($_GET["schema"])){$tn=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(71).$tn,[lang(71)]);$Um=[];$Vm=[];$ze=[];$pa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$pa,$z,PREG_SET_ORDER);foreach($z
as$p=>$y){$Um[$y[1]]=[(float)$y[2],(float)$y[3]];$Vm[]="\n\t'".js_escape($y[1])."': [ $y[2], $y[3] ]";}$yn=0;$hb=-1;$ql=[];$Ik=[];$bh=[];$Na=Driver::get()->getAllFields();foreach(table_status('',true)as$Q=>$R){if(is_view($R))continue;$bk=0;$ql[$Q]["fields"]=[];foreach(isset($Na[$Q])?$Na[$Q]:[]as$k){$bk+=1.25;$ze[$Q][$k["field"]]=$bk;$ql[$Q]["fields"][$k["field"]]=$k;}$ql[$Q]["pos"]=(isset($Um[$Q])?$Um[$Q]:[$yn,0]);foreach(Admin::get()->getForeignKeys($Q)as$W){if(!$W["db"]){$Zg=$hb;if((isset($Um[$Q][1])?$Um[$Q][1]:0)||(isset($Um[$W["table"]][1])?$Um[$W["table"]][1]:0))$Zg=min(floatval(isset($Um[$Q][1])?$Um[$Q][1]:0),floatval(isset($Um[$W["table"]][1])?$Um[$W["table"]][1]:0))-1;else$hb-=.1;while($bh[(string)$Zg])$Zg-=.0001;$ql[$Q]["references"][$W["table"]][(string)$Zg]=[$W["source"],$W["target"]];$Ik[$W["table"]][$Q][(string)$Zg]=$W["target"];$bh[(string)$Zg]=true;}}$yn=max($yn,$ql[$Q]["pos"][0]+2.5+$bk);}echo"<div id='schema' style='height: {$yn}em;'>\n","<script",nonce(),">\n","gid('schema').onselectstart = () => false;\n","const tablePos = {",implode(",",$Vm),"\n};\n","const em = gid('schema').offsetHeight / $yn;\n","document.onmousemove = schemaMousemove;\n","document.onmouseup = partialArg(schemaMouseup, '",js_escape(DB),"');\n","</script>\n";foreach($ql
as$_=>$Q){echo"<div class='table' style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($_).'"><b>'.h($_)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($Q["fields"]as$k){$W='<span '.type_class($k["type"]).' title="'.h($k["type"].($k["length"]?"($k[length])":"").($k["null"]?" NULL":'')).'">'.h($k["field"]).'</span>';echo"<br>".($k["primary"]?"<i>$W</i>":$W);}foreach((array)$Q["references"]as$fn=>$Kk){foreach($Kk
as$Zg=>$Ek){$ah=$Zg-(isset($Um[$_][1])?$Um[$_][1]:0);$p=0;foreach($Ek[0]as$gm){echo"\n<div class='references' title='",h($fn),"' id='refs$Zg-$p' style='left: {$ah}em; top: ",$ze[$_][$gm],"em; padding-top: .5em;'>","<div style='border-top: 1px solid Gray; width: ".(-$ah)."em;'></div>","</div>";$p++;}}}foreach((array)$Ik[$_]as$fn=>$Kk){foreach($Kk
as$Zg=>$d){$ah=$Zg-(isset($Um[$_][1])?$Um[$_][1]:0);$p=0;foreach($d
as$en){echo"\n<div class='references' title='",h($fn),"' id='refd$Zg-$p' style='left: {$ah}em; top: ".$ze[$_][$en]."em; height: 1.25em;'>","<svg style='width: 1em; height: 1em; float: right;' viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='height: .5em; border-bottom: 1px solid Gray; width: ".(-$ah)."em;'></div>","</div>";$p++;}}}echo"\n</div>\n";}foreach($ql
as$_=>$Q){foreach((array)$Q["references"]as$fn=>$Kk){if($ql[$fn]){foreach($Kk
as$Zg=>$Ek){$bi=$yn;$Ih=-10;foreach($Ek[0]as$u=>$gm){$ck=$Q["pos"][0]+$ze[$_][$gm];$dk=$ql[$fn]["pos"][0]+$ze[$fn][$Ek[1][$u]];$bi=min($bi,$ck,$dk);$Ih=max($Ih,$ck,$dk);}echo"<div class='references' id='refl$Zg' style='left: $Zg"."em; top: $bi"."em; padding: .5em 0;'><div style='border-right: 1px solid Gray; margin-top: 1px; height: ".($Ih-$bi)."em;'></div></div>\n";}}}}echo"</div>\n","<p class='links'>","<a href='",(ME."schema=".urlencode($pa)),"' id='schema-link'>",lang(180),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$Vl=Admin::get()->getSettings();if($_POST){$Vl->updateParameters(["dumpFormat"=>$_POST["format"],"dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),"dumpDataStyle"=>$_POST["data_style"],"dumpOutput"=>$_POST["output"],]);if(DB!="")$g=[DB];else{$g=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($g))$g=explode("\n",rtrim(str_replace("\r","",$g),"\n"));}$rl=isset($_POST["schemas"])?$_POST["schemas"]:[];$S=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($S)==1)$Nf=key($S);elseif(count($rl)==1)$Nf=$rl[0];elseif(count($g)==1)$Nf=$g[0];else$Nf=Admin::get()->getServerName(SERVER,true,"server");$me=dump_headers($Nf,DB==""||$_GET["ns"]===""||count($S)>1);$yg=preg_match('~sql~',$_POST["format"]);$Mc=$yg&&$_POST["data_style"]&&!$_POST["table_style"]&&DIALECT!="sql";if($yg){echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$ym=$_POST["db_style"];foreach($g
as$h){Admin::get()->dumpDatabase($h);if(Connection::get()->selectDatabase($h)){if($yg){if($ym)echo
create_database_sql($h,$ym),use_sql($h,$ym)."\n";$tj="";if($_POST["types"]){foreach(types()as$q=>$T){$Vd=type_values($q);if($Vd)$tj
.=($ym!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($T).";;\n":"")."CREATE TYPE ".idf_escape($T)." AS ENUM ($Vd);\n\n";else$tj
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$_=$K["ROUTINE_NAME"];$cl=$K["ROUTINE_TYPE"];$Ac=create_routine($cl,["name"=>$_]+routine($K["SPECIFIC_NAME"],$cl));set_utf8mb4($Ac);$tj
.=($ym!='DROP+CREATE'?"DROP $cl IF EXISTS ".idf_escape($_).";;\n":"")."$Ac;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$Ac=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($Ac);$tj
.=($ym!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$Ac;;\n\n";}}echo($tj&&DIALECT=='sql'?"DELIMITER ;;\n\n$tj"."DELIMITER ;\n\n":$tj);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$ql){if($ql!="")set_schema($ql);$an=table_status('',true);$Sm=array_keys($an);$kd=false;if($Mc&&$Sm){$Jk=[];foreach($Sm
as$_){if(!is_view($an[$_])&&(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["data"]))){foreach(foreign_keys($_)as$o)$Jk[$_][]=$o["table"];}}$ij=dump_table_order($Sm,$Jk);if($ij)$Sm=$ij;else$kd=function_exists('AdminNeo\foreign_key_checks_sql');}if($kd)echo
foreign_key_checks_sql(false)."\n";$uo=[];foreach($Sm
as$_){$R=$an[$_];$Q=(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["tables"]));$f=(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["data"]));if($Q||$f){$vn=null;if($me=="tar"){$vn=new
TmpFile();ob_start([$vn,'write'],1e5);}$Bc=($Q?$_POST["table_style"]:"");Admin::get()->dumpTable($_,$Bc,(is_view($R)?2:0));if(is_view($R)&&$me!="tar")$uo[]=$_;elseif($f){$l=fields($_);Admin::get()->dumpData($_,$_POST["data_style"],"SELECT *".convert_fields($l,$l)." FROM ".table($_));if($yg&&!$Bc&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($_);}if($yg&&$_POST["triggers"]&&$Q&&($In=trigger_sql($_)))echo"\nDELIMITER ;;\n$In\nDELIMITER ;\n";if($me=="tar"){ob_end_flush();tar_file((DB!=""?"":"$h/")."$_.csv",$vn);}elseif($yg)echo"\n";}}if($kd)echo
foreign_key_checks_sql(true)."\n";if($_POST["table_style"]&&function_exists('AdminNeo\foreign_keys_sql')){foreach($an
as$_=>$R){$Q=(DB==""||$_GET["ns"]===""||in_array($_,(array)$_POST["tables"]));if($Q&&!is_view($R))echo
foreign_keys_sql($_);}}foreach($uo
as$so)Admin::get()->dumpTable($so,$_POST["table_style"],1);if($me=="tar")echo
pack("x512");}}}}if($yg)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$_=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(74).": $_",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(74)]));echo"<form action='' method='post'>\n","<table class='box'>\n";$Sc=['','USE','DROP+CREATE','CREATE'];$Xm=['','DROP+CREATE','CREATE'];$Nc=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$Nc[]='INSERT+UPDATE';echo"<tr><th>",lang(181),"</th><td>",html_radios("format",Admin::get()->getDumpFormats(),$Vl->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(30),"</th>","<td>",html_select('db_style',$Sc,$Vl->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("type"))echo
checkbox("types",1,$Vl->getParameter("dumpTypes"),lang(108));if(support("routine"))echo
checkbox("routines",1,$Vl->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(182));if(support("event"))echo
checkbox("events",1,$Vl->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(183));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(159),"</th><td>",html_select('table_style',$Xm,$Vl->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$Vl->getParameter("dumpAutoIncrement"),lang(47));if(support("trigger"))echo
checkbox("triggers",1,$Vl->getParameter("dumpTriggers","1"),lang(178));echo"</span></td></tr>","<tr><th id='label-data'>",lang(184),"</th><td>",html_select("data_style",$Nc,$Vl->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(185),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$Vl->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n","<p>","<input type='submit' class='button default' value='",lang(74),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$hk=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly'>".lang(81)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$ql)echo"<tr><td>".checkbox("schemas[]",$ql,true,$ql,"","block")."\n";}elseif(DB!=""){$Hb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$Hb class='jsonly'>".lang(8)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(184)."<input type='checkbox' id='check-data'$Hb class='jsonly'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$uo="";$Zm=tables_list();foreach($Zm
as$_=>$T){$gk=preg_replace('~_.*~','',$_);$Hb=($a==""||$a==(substr($a,-1)=="%"?"$gk%":$_));$lk="<tr><td>".checkbox("tables[]",$_,$Hb,$_,"","block");if($T!==null&&!preg_match('~table~i',$T))$uo
.="$lk\n";else
echo"$lk<td class='right'><label class='block'><span id='Rows-".h($_)."'></span>".checkbox("data[]",$_,$Hb)."</label>\n";$hk[$gk]++;}echo$uo;if($Zm)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$g=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($g?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(30)."</label>","</thead>\n";if($g){foreach($g
as$h){if(!information_schema($h)){$gk=preg_replace('~_.*~','',$h);echo"<tr><td>".checkbox("databases[]",$h,$a==""||$a=="$gk%",$h,"","block")."\n";$hk[$gk]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$lh=[];foreach($hk
as$u=>$W){if($u!=""&&$W>1)$lh[]="<a href='".h(ME)."dump=".urlencode("$u%")."'>".icon("check").h($u)."*</a>";}if($lh)echo"<p class='links'>",implode("",$lh),"</p>\n";}elseif(isset($_GET["privileges"])){$tn=DB!=""?h(": ".DB):"";page_header(lang(72).$tn,[lang(72)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(186),"</a></p>\n";$I=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$jf=$I;if(!$I)$I=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$jf)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(28)."<th>".lang(5)."<th></thead>\n";while($K=$I->fetchAssoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.lang(38)."</a>\n";if(!$jf||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'><td><input class='input' name='host' value='localhost' autocapitalize='off'><td><input type='submit' class='button' value='".lang(38)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){$Vl=Admin::get()->getSettings();if($_POST["export"]){$Vl->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Ef=&get_session("queries");$Df=&$Ef[DB];if($_POST["clear"]){$Df=[];redirect(remove_from_uri("history"));}stop_session();$sn=isset($_GET["import"])?lang(73):lang(40);page_header($sn,[$sn]);$ih="--".(DIALECT=="sql"?" ":"");if($_POST){$Ze=false;if(!isset($_GET["import"]))$G=$_POST["query"];elseif($_POST["webfile"]){$Sf=Admin::get()->getImportFilePath();if($Sf){if(file_exists($Sf))$Ze=fopen($Sf,"rb");elseif(file_exists("$Sf.gz"))$Ze=fopen("compress.zlib://$Sf.gz","rb");}$G=$Ze?fread($Ze,1e6):false;}else$G=get_file("sql_file",true,";");if(is_string($G)){if(($Oh=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Oh,strval(2*strlen($G)+memory_get_usage()+8e6)));if($G!=""&&strlen($G)<1e6){$uk=$G.(preg_match("~;[ \t\r\n]*\$~",$G)?"":";");if(!$Df||first(end($Df))!=$uk){restart_session();$Df[]=[$uk,time()];set_session("queries",$Ef);stop_session();}}$im="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$ih)[^\n]*\n?|--\r?\n)";$cd=";";$dd=1;$A=0;$Md=true;$pc=connect();if($pc&&DB!=""){$pc->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$pc);}$cc=0;$Xd=[];$Bj='[\'"'.(DIALECT=="sql"?'`#':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$ih.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$zn=microtime(true);$Cd=Admin::get()->getDumpFormats();unset($Cd["sql"]);while($G!=""){if(!$A&&preg_match("~^$im*+DELIMITER\\s+(\\S+)~i",$G,$y)){$cd=preg_quote($y[1]);$dd=strlen($y[1]);$Ve=Admin::get()->formatSqlCommandQuery(trim($y[0]));if($Ve!="")echo"<pre><code class='jush-".DIALECT."'>$Ve</code></pre>\n";$G=substr($G,strlen($y[0]));}elseif(!$A&&DIALECT=="pgsql"&&preg_match("~^($im*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$G,$y)){$cd="\n\\\\\\.\r?\n";$dd=3;$A=strlen($y[0]);}else{preg_match("($cd\\s*|$Bj)",$G,$y,PREG_OFFSET_CAPTURE,$A);list($Xe,$bk)=$y[0];if(!$Xe&&$Ze&&!feof($Ze))$G
.=fread($Ze,1e5);else{if(!$Xe&&rtrim($G)=="")break;$A=$bk+strlen($Xe);if($Xe&&!preg_match("(^$cd)",$Xe)){$wb=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($bk>0&&strtolower($G[$bk-1])=="e"));$Qj='(';if($Xe=='/*')$Qj
.='\*/';elseif($Xe=='[')$Qj
.=']';elseif(preg_match("~^$ih|^#~",$Xe))$Qj
.="\n";else$Qj
.=preg_quote($Xe).($wb?"|\\\\.":"");$Qj
.='|$)s';while(preg_match($Qj,$G,$y,PREG_OFFSET_CAPTURE,$A)){$il=$y[0][0];if(!$il&&$Ze&&!feof($Ze))$G
.=fread($Ze,1e5);else{$A=$y[0][1]+strlen($il);if(!isset($il[0])||$il[0]!="\\")break;}}}else{$Md=false;$uk=substr($G,0,$bk+$dd);$cc++;$lk="<pre id='sql-$cc'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($uk))."</code></pre>\n";if(DIALECT=="sqlite"&&preg_match("~^$im*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$uk,$y)!==0){echo$lk,"<p class='error'>".lang(187,preg_match('~ATTACH~i',$y[1])?'ATTACH':'VACUUM INTO')."\n";$Xd[]=" <a href='#sql-$cc'>$cc</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$lk;ob_flush();flush();}$rm=microtime(true);if(Connection::get()->multiQuery($uk)&&is_object($pc)&&preg_match("~^$im*+USE\\b~i",$uk))$pc->query($uk);do{$I=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$lk:""),"<p class='error'>",lang(188),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$Xd[]=" <a href='#sql-$cc'>$cc</a>";if($_POST["error_stops"])break
2;}else{$pn=" <span class='time'>(".format_time($rm).")</span>";$Gd=(strlen($uk)<1000?" <a href='".h(ME)."sql=".urlencode(trim($uk))."'>".icon("edit").lang(38)."</a>":"");$zk=Connection::get()->getQueryInfo();$Da=Connection::get()->getAffectedRows();$_o=($_POST["only_errors"]?null:Driver::get()->warnings());$Bo="warnings-$cc";$Co=$_o?"<a href='#$Bo' class='toggle'>".lang(39).icon_chevron_down()."</a>":null;$he=$lj=null;$ie="explain-$cc";$je=false;$ke="export-$cc";if(is_object($I)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$w=(int)$_POST["limit"];$lj=print_select_result($I,$pc,[],$w);if(!$_POST["only_errors"]){echo"<p class='links'>";$Ei=$I->getRowsCount();echo($Ei?($w&&$Ei>$w?lang(189,$w):"").lang(190,$Ei):""),$pn,$Gd,$Co;if($pc&&preg_match("~^($im|\\()*+SELECT\\b~i",$uk)&&($he=explain($pc,$uk)))echo"<a href='#$ie' class='toggle'>Explain".icon_chevron_down()."</a>";$je=true;echo"<a href='#$ke' class='toggle'>".lang(74).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$im*+(CREATE|DROP|ALTER)$im++(DATABASE|SCHEMA)\\b~i",$uk)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($zk)."'>",lang(191,$Da),"$pn $Gd";if($Co)echo", $Co";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($_o)echo"<div id='$Bo' class='hidden'>\n$_o</div>\n";if($he){echo"<div id='$ie' class='hidden explain'>\n";print_select_result($he,$pc,$lj);echo"</div>\n";}if($je)echo"<form id='$ke' action='' method='post' class='hidden'><p>\n",html_select("format",$Cd,$Vl->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$Vl->getParameter("exportOutput"))." ",input_hidden("query",$uk),input_token()," <input type='submit' class='button' name='export' value='".lang(74)."'>","</p></form>\n";if(is_object($I)&&!$_POST["only_errors"])echo"</div>\n";}$rm=microtime(true);}while(Connection::get()->nextResult());}$G=substr($G,$A);$A=0;}}}}if($Md)echo"<p class='message'>".lang(192)."\n";elseif($_POST["only_errors"]){$Li=$cc-count($Xd);echo"<p class='".($Li?"message":"error")."'>".lang(193,$cc-count($Xd))," <span class='time'>(".format_time($zn).")</span>\n";}elseif($Xd&&$cc>1)echo"<p class='error'>".lang(188).": ".implode("",$Xd)."\n";}else
echo"<p class='error'>".upload_error($G)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$uk=$_GET["sql"];if($_POST)$uk=$_POST["query"];elseif($_GET["history"]=="all")$uk=$Df;elseif($_GET["history"]!="")$uk=$Df[$_GET["history"]][0];echo"<p>";textarea("query",$uk,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(194)."' title='Ctrl+Enter'>",lang(195).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(196)."</legend><div class='fieldset-content'>";$rf=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$rf (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(194)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(197);echo"</div></fieldset>\n";$Sf=Admin::get()->getImportFilePath();if($Sf)echo"<fieldset><legend>".lang(198)."</legend><div class='fieldset-content'>",lang(199,"<code>".h($Sf)."$rf</code>"),' <input type="submit" class="button default" name="webfile" value="'.lang(200).'">',"</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(201)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(202)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$Df){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(203),"history",$_GET["history"]!="");for($W=end($Df);$W;$W=prev($Df)){$u=key($Df);list($uk,$pn,$Kd)=$W;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$ih).*~m",'',$uk))))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$u")."'>".icon("edit").lang(38)."</a>"," <span class='time' title='".@date('Y-m-d',$pn)."'>".@date("H:i:s",$pn).($Kd?" ($Kd)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(204)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(205),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$l=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$l):""):where($_GET,$l));$Yn=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($l
as$_=>$k){if((!$Yn&&!isset($k["privileges"]["insert"]))||Admin::get()->getFieldName($k)=="")unset($l[$_]);}if($_POST&&!isset($_GET["select"])){$uh=$_POST["referer"];if($_POST["insert"])$uh=($Yn?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$uh))$uh=ME."select=".urlencode($a);$t=indexes($a);$Sn=unique_array(isset($_GET["where"])?$_GET["where"]:[],$t);$_k="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($uh,lang(206),(bool)Driver::get()->delete($a,$_k,$Sn?0:1));else{$Tl=[];foreach($l
as$_=>$k){$W=process_input($k);if($W!==false&&$W!==null)$Tl[idf_escape($_)]=$W;}if($Yn){if(!$Tl)redirect($uh);queries_redirect($uh,lang(207),(bool)Driver::get()->update($a,$Tl,$_k,$Sn?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$I=Driver::get()->insert($a,$Tl);$Xg=($I?last_id($I):0);queries_redirect($uh,lang(208,($Xg?" $Xg":"")),(bool)$I);}}}$K=null;if($Z){$M=[];foreach($l
as$_=>$k){if(isset($k["privileges"]["select"])){$Ta=($_POST["clone"]&&$k["auto_increment"]?"''":convert_field($k));$M[]=($Ta?"$Ta AS ":"").idf_escape($_);}}$K=[];if(!support("table"))$M=["*"];if($M){$I=Driver::get()->select($a,$M,[$Z],$M,[],(isset($_GET["select"])?2:1));if(!$I)Admin::get()->addError(error());else{$K=$I->fetchAssoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetchAssoc()))$K=null;}}if(!support("table")&&!$l){if(!$Z){$I=Driver::get()->select($a,["*"],[],["*"]);$K=($I?$I->fetchAssoc():false);if(!$K)$K=[Driver::get()->primary=>""];}if($K){foreach($K
as$u=>$W){if(!$Z)$K[$u]=null;$l[$u]=["field"=>$u,"null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false)$K=(isset($_POST["fields"])?$_POST["fields"]:[])+($K?:[]);if($_POST["edit"]){$Id=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});}else$Id=$l;edit_form($a,$Id,$K,$Yn);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Hj=Driver::get()->getPartitionBy();$Lj=$Hj?Driver::get()->getPartitionsInfo($a):[];$Gk=referencable_primary($a);$Se=[];foreach($Gk
as$Rm=>$k)$Se[str_replace("`","``",$Rm)."`".str_replace("`","``",$k["field"])]=$Rm;$oj=[];$R=[];if($a!=""){$oj=fields($a);$R=table_status1($a);if(count($R)<2)Admin::get()->addError(lang(78));}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($K["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(209),drop_tables([$a]));else{$l=[];$Na=[];$do=false;$Qe=[];$nj=reset($oj);$Ga=" FIRST";foreach($K["fields"]as$u=>$k){$o=$Se[$k["type"]];$Mn=($o!==null?$Gk[$o]:$k);if($k["field"]!=""){if(!$k["generated"])$k["default"]=null;$rk=process_field($k,$Mn);$Na[]=[$k["orig"],$rk,$Ga];if(!$nj||$rk!==process_field($nj,$nj)){$l[]=[$k["orig"],$rk,$Ga];if($k["orig"]!=""||$Ga)$do=true;}if($o!==null)$Qe[idf_escape($k["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$Se[$k["type"]],'source'=>[$k["field"]],'target'=>[$Mn["field"]],'on_delete'=>$k["on_delete"],]);$Ga=" AFTER ".idf_escape($k["field"]);}elseif($k["orig"]!=""){$do=true;$l[]=[$k["orig"]];}if($k["orig"]!=""){$nj=next($oj);if(!$nj)$Ga="";}}$Jj=[];if(in_array($K["partition_by"],$Hj)){foreach($K
as$u=>$W){if(preg_match('~^partition~',$u))$Jj[$u]=$W;}foreach($Jj["partition_names"]as$u=>$_){if($_===""){unset($Jj["partition_names"][$u]);unset($Jj["partition_values"][$u]);}}$Jj["partition_names"]=array_values($Jj["partition_names"]);$Jj["partition_values"]=array_values($Jj["partition_values"]);if($Jj==$Lj)$Jj=[];}elseif(str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned"))$Jj=null;$Qh=lang(210);if($a==""){cookie("neo_engine",isset($K["Engine"])?$K["Engine"]:"");$Qh=lang(211);}$_=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($_),$Qh,alter_table($a,$_,(DIALECT=="sqlite"&&($do||$Qe)?$Na:$l),$Qe,($K["Comment"]!=$R["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$R["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$R["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$Jj));}}if($a!="")page_header(lang(35).": ".h($a),["table"=>$a,lang(35)]);else
page_header(lang(77),[lang(77)]);if(!$_POST){$On=Driver::get()->getTypes();$K=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($On["int"])?"int":(isset($On["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$K=$R;$K["name"]=$a;$K["fields"]=[];if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($oj
as$k){$k["generated"]=$k["generated"]?:(isset($k["default"])?"DEFAULT":"");$K["fields"][]=$k;}if($Hj){$K+=$Lj;$K["partition_names"][]="";$K["partition_values"][]="";}}}$Hg=[];if($K["Collation"])$Hg[$K["Collation"]]=true;foreach($K["fields"]as$k){if($k["collation"])$Hg[$k["collation"]]=true;}$Tb=Admin::get()->getCollations(array_keys($Hg));$Td=Driver::get()->engines();foreach($Td
as$Sd){if(!strcasecmp($Sd,$K["Engine"])){$K["Engine"]=$Sd;break;}}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(212),": ","<input class='input' name='name' data-maxlength='64' value='",h($K["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($Td)echo" ",html_select("Engine",[""=>"(".lang(213).")"]+$Td,$K["Engine"]),help_script_command("value",true);if($Tb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(92).")"]+$Tb,$K["Collation"]);echo" <input type='submit' class='button default' value='",lang(114),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$Tb,"TABLE",$Se);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(47),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($K["Auto_increment"]),"'>";$hc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$ec=$hc?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$hc,lang(46),"editingCommentsClick(this, ".(support("move_col")?7:6).");","jsonly")," ";if(preg_match('~\n~',$K["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($ec?" class='$ec'":""),">",h($K["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($K["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $ec'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(114),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(lang(214,$a)),"</p>\n";if($Hj&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$Ij=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset_start("partition",lang(215),"split",(bool)$K["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$Hj),$K["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($K["partition"]),"'>) ",lang(49),": ","<input type='number' name='partitions' class='input size ",($Ij||!$K["partition_by"]?"hidden":""),"' value='",h($K["partitions"]),"'>","</p>\n","<table id='partition-table'",($Ij?"":" class='hidden'"),">\n","<thead><tr><th>",lang(216),"</th><th>",lang(51),"</th></tr></thead>\n";foreach($K["partition_names"]as$u=>$W){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($W),"' autocapitalize='off'>";if($u==count($K["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($K["partition_values"][$u])?$K["partition_values"][$u]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$Zf=["PRIMARY","UNIQUE","INDEX"];$R=table_status1($a,true);$Wf=Driver::get()->getIndexAlgorithms($R);$e=Connection::get();$_h=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($_h?"10.0.5":"5.6")?'|InnoDB':'').'~i',$R["Engine"]))$Zf[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($_h?"10.2.2":"5.7")?'|InnoDB':'').'~i',$R["Engine"]))$Zf[]="SPATIAL";if($_h&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$Zf[]="VECTOR";$t=indexes($a);$l=fields($a);$F=[];if(DIALECT=="mongo"){$F=$t["_id_"];unset($Zf[0]);unset($t["_id_"]);}$K=$_POST;if($K)Admin::get()->getSettings()->updateParameter("indexOptions",isset($K["options"])?$K["options"]:null);if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$b=[];foreach($K["indexes"]as$s){$_=$s["name"];if(in_array($s["type"],$Zf)){$d=[];$fh=[];$gd=[];$Vf=$Wf?(in_array($s["algorithm"],$Wf)?$s["algorithm"]:first($Wf)):"";$Xf=(support("partial_indexes")?$s["partial"]:"");$Tl=[];ksort($s["columns"]);foreach($s["columns"]as$u=>$c){if($c!=""){$v=isset($s["lengths"][$u])?$s["lengths"][$u]:null;$ed=isset($s["descs"][$u])?$s["descs"][$u]:null;$Tl[]=($l[$c]?idf_escape($c):$c).($v?"(".(+$v).")":"").($ed?" DESC":"");$d[]=$c;$fh[]=($v?:null);$gd[]=$ed;}}$fe=$t[$_];if($fe){ksort($fe["columns"]);ksort($fe["lengths"]);ksort($fe["descs"]);if($s["type"]==$fe["type"]&&array_values($fe["columns"])===$d&&(!$fe["lengths"]||array_values($fe["lengths"])===$fh)&&array_values($fe["descs"])===$gd&&(!$Wf||$fe["algorithm"]===$Vf)&&$fe["partial"]==$Xf){unset($t[$_]);continue;}}if($d)$b[]=[$s["type"],$_,$Tl,$Vf,$Xf];}}foreach($t
as$_=>$fe)$b[]=[$fe["type"],$_,"DROP"];if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(217),alter_indexes($a,$b));}page_header(lang(171),["table"=>$a,lang(171)],h($a));$Ae=array_keys($l);if($_POST["add"]){foreach($K["indexes"]as$u=>$s){if($s["columns"][count($s["columns"])]!="")$K["indexes"][$u]["columns"][]="";}$s=end($K["indexes"]);if($s["type"]||array_filter($s["columns"],'strlen'))$K["indexes"][]=["columns"=>[1=>""]];}if(!$K){foreach($t
as$u=>$s){$t[$u]["name"]=$u;$t[$u]["columns"][]="";}$t[]=["columns"=>[1=>""]];$K["indexes"]=$t;}$fh=(DIALECT=="sql"||DIALECT=="mssql");$Yl=$_POST?$_POST["options"]:Admin::get()->getSettings()->getParameter("indexOptions");echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(218),"</th>";$dj="class='idxopts".($Yl?"":" hidden")."'";if(count($Wf)>1)echo"<th id='label-method' $dj>",lang(219),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types','pgsql'=>'indexes-types.html',]),"</th>";echo"<th><input type='submit' class='button invisible'>",lang(52).($fh?"<span $dj> (".lang(53).")</span>":"");if($fh||support("descidx"))echo
checkbox("options",1,$Yl,lang(98),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(220),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $dj>",lang(54),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(99),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($F){echo"<tr><td>PRIMARY<td>";foreach($F["columns"]as$c)echo
select_input(" disabled",$Ae,$c),"<label><input type='checkbox' disabled>".lang(62)."</label> ";echo"<td><td>\n";}$Dg=1;foreach($K["indexes"]as$s){if(!$_POST["drop_col"]||$Dg!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$Dg][type]",[-1=>""]+$Zf,$s["type"],($Dg==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($Wf)>1)echo"<td $dj>",html_select("indexes[$Dg][algorithm]",array_merge([""],$Wf),$s['algorithm'],"label-method"),"</td>";echo"<td>";ksort($s["columns"]);$p=1;foreach($s["columns"]as$u=>$c){echo"<span>".select_input(" name='indexes[$Dg][columns][$p]' title='".lang(43)."'",($l&&($c==""||$l[$c])?array_combine($Ae,$Ae):[]),$c,"partial(".($p==count($s["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $dj>";if($fh)echo"<input type='number' name='indexes[$Dg][lengths][$p]' class='input size' value='".(h(isset($s["lengths"][$u])?$s["lengths"][$u]:"")),"' title='".lang(97),"'>";if(support("descidx"))echo
checkbox("indexes[$Dg][descs][$p]",1,isset($s["descs"][$u])?$s["descs"][$u]:false,lang(62));echo"</span> </span>";$p++;}echo"</td>","<td><input name='indexes[$Dg][name]' value='",h($s["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $dj><input name='indexes[$Dg][partial]' value='".h($s["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$Dg]' value='1' title='",h(lang(58)),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$Dg++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(114),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!isset($_POST["add_x"])){$_=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(221),drop_databases([DB]));}elseif(DB!==$_){if(DB!=""){$_GET["db"]=$_;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($_),lang(222),rename_database($_,$K["collation"]));}else{$g=explode("\n",str_replace("\r","",$_));$_m=true;$Wg="";foreach($g
as$h){if(count($g)==1||$h!=""){if(!create_database($h,$K["collation"]))$_m=false;$Wg=$h;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Wg),lang(223),$_m);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($_).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),lang(224));}}if(DB!="")page_header(lang(69).": ".h(DB),[lang(69)]);else
page_header(lang(75),[lang(75)]);$_=DB;if($_POST)$_=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$jf){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$jf,$y)&&$y[1]){$_=stripcslashes(idf_unescape("`$y[2]`"));break;}}}$Tb=Admin::get()->getCollations($K["collation"]?[$K["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($_,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($_),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($_),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Tb)echo
html_select("collation",[""=>"(".lang(92).")"]+$Tb,$K["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",]),"\n";echo"<input type='submit' class='button default' value='",lang(114),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(163)."'>".confirm(lang(214,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",h(lang(99)),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["scheme"])){$K=$_POST;if($_POST){$x=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$x,lang(225));else{$_=trim($K["name"]);$x
.=urlencode($_);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($_),$x,lang(226));elseif($_GET["ns"]!=$_)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($_),$x,lang(227));else
redirect($x);}}if($_GET["ns"]!="")page_header(lang(70).": ".h($_GET["ns"]),[lang(70)]);else
page_header(lang(76),[lang(76)]);if(!$K)$K["name"]=$_GET["ns"];echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' id='name' value='",h($K["name"]),"' autocapitalize='off' autofocus>","<input type='submit' class='button default' value='",lang(114),"'>";if($_GET["ns"]!="")echo"<input type='submit' class='button' name='drop' value='".lang(163)."'>".confirm(lang(214,$_GET["ns"]))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$oa=$_GET["name"]?:$_GET["call"];page_header(lang(228).": ".h($oa),[lang(228)]);$cl=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Tf=[];$tj=[];foreach($cl["fields"]as$p=>$k){if(substr($k["inout"],-3)=="OUT"&&DIALECT=='sql')$tj[$p]="@".idf_escape($k["field"])." AS ".idf_escape($k["field"]);if(!$k["inout"]||substr($k["inout"],0,2)=="IN")$Tf[]=$p;}if($_POST){$yb=[];foreach($cl["fields"]as$u=>$k){$W="";if(in_array($u,$Tf)){$W=process_input($k);if($W===false)$W="''";if(isset($tj[$u]))Connection::get()->query("SET @".idf_escape($k["field"])." = $W");}if(isset($tj[$u]))$yb[]="@".idf_escape($k["field"]);elseif(in_array($u,$Tf))$yb[]=$W;}$G=(isset($_GET["callf"])?"SELECT ":"CALL ").($cl["returns"]&&$cl["returns"]["type"]=="record"?"* FROM ":"").table($oa)."(".implode(", ",$yb).")";$rm=microtime(true);$I=Connection::get()->multiQuery($G);$Da=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($G,$rm,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$pc=connect();if($pc)$pc->selectDatabase(DB);do{$I=Connection::get()->storeResult();if(is_object($I))print_select_result($I,$pc);else
echo"<p class='message'>".lang(229,$Da)." <span class='time'>".@date("H:i:s")."</span>\n";}while(Connection::get()->nextResult());if($tj)print_select_result(Connection::get()->query("SELECT ".implode(", ",$tj)));}}echo"<form action='' method='post'>\n";if($Tf){echo"<table class='box'>\n";foreach($Tf
as$u){$k=$cl["fields"][$u];$_=$k["field"];echo"<tr><th>".Admin::get()->getFieldName($k);$X=isset($_POST["fields"][$_])?$_POST["fields"][$_]:"";if($X!=""){if($k["type"]=="set")$X=implode(",",$X);}input($k,$X,(string)(isset($_POST["function"][$_])?$_POST["function"][$_]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(228),"'>\n",input_token(),"</p>\n","</form>\n";$dc=$cl["comment"];if($dc!==null&&$dc!==""){$dc=h(trim($cl["comment"],"\n"));if(preg_match('~^ +~',$dc,$z)){preg_match_all("~^($z[0]|$)~m",$dc,$jh);if(count($jh[0])==substr_count($dc,"\n"))$dc=preg_replace("~^($z[0])~m","",$dc);}$dc=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$dc);echo"<pre class='comment'>$dc</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$_=$_GET["name"];$K=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$en=[];foreach($K["source"]as$u=>$W)$en[$u]=$K["target"][$u];$K["target"]=$en;}if(DIALECT=="sqlite")$I=recreate_table($a,$a,[],[],[" $_"=>($K["drop"]?"":" ".format_foreign_key($K))]);else{$b="ALTER TABLE ".table($a);$I=($_==""||queries("$b DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($_)));if(!$K["drop"])$I=queries("$b ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(230):($_!=""?lang(231):lang(232))),(bool)$I);if(!$K["drop"])Admin::get()->addError(lang(233));}page_header(lang(234).": ".h($a),["table"=>$a,lang(234)]);if($_POST){ksort($K["source"]);if($_POST["change"]||$_POST["change-js"])$K["target"]=[];else$K["source"][]="";}elseif($_!=""){$Se=foreign_keys($a);$K=$Se[$_];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=[""];}echo"<form action='' method='post'>\n";$gm=array_keys(fields($a));if($K["db"]!="")Connection::get()->selectDatabase($K["db"]);if($K["ns"]!=""){$pj=get_schema();set_schema($K["ns"]);}$Fk=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$en=array_keys(fields(in_array($K["table"],$Fk)?$K["table"]:reset($Fk)));$Ui="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(235),":</span> ",html_select("table",$Fk,$K["table"],$Ui,"label-table");if(support("scheme")){$rl=array_filter(Admin::get()->getSchemas(),function($ql){return!preg_match('~^information_schema$~i',$ql);});echo"<span id='label-schema'>",lang(81),":</span> ",html_select("ns",$rl,$K["ns"]!=""?$K["ns"]:$_GET["ns"],$Ui,"label-schema");if($K["ns"]!="")set_schema($pj);}elseif(DIALECT!="sqlite"){$Tc=[];foreach(Admin::get()->getDatabases()as$h){if(!information_schema($h))$Tc[]=$h;}echo"<span id='label-db'>",lang(236),":</span> ",html_select("db",$Tc,$K["db"]!=""?$K["db"]:$_GET["db"],$Ui,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(237),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(172),"<th id='label-target'>",lang(173),"</thead>\n";$Dg=0;foreach($K["source"]as$u=>$W){echo"<tr>","<td>".html_select("source[".(+$u)."]",[-1=>""]+$gm,$W,($Dg==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$u)."]",$en,isset($K["target"][$u])?$K["target"][$u]:null,"","label-target");$Dg++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(238),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(94),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$K["on_delete"],"","label-delete"),"<span id='label-update'>".lang(93),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$K["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$K["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(114),"'>";if($_!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(lang(214,$_));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$qj="VIEW";if(DIALECT=="pgsql"&&$a!=""){$O=table_status1($a);$qj=strtoupper($O["Engine"]);}if($_POST){$_=trim($K["name"]);$Ta=" AS\n$K[select]";$uh=ME."table=".urlencode($_);$Qh=lang(239);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$_&&DIALECT!="sqlite"&&$T=="VIEW"&&$qj=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($_).$Ta,$uh,$Qh);else{$gn=$_."_adminneo_".uniqid();drop_create("DROP $qj ".table($a),"CREATE $T ".table($_).$Ta,"DROP $T ".table($_),"CREATE $T ".table($gn).$Ta,"DROP $T ".table($gn),($_POST["drop"]?substr(ME,0,-1):$uh),lang(240),$Qh,lang(241),$a,$_);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($qj!="VIEW");if($j=error())Admin::get()->addError($j);}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(242),[lang(242)]);echo"<form action='' method='post'>\n","<p>",lang(220),":","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$K["materialized"],lang(165));echo"</p>\n<p>";textarea("select",$K["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(114),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>\n",confirm(lang(214,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$mg=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$um=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$K=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(243));elseif(in_array($K["INTERVAL_FIELD"],$mg)&&isset($um[$K["STATUS"]])){$pl="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(244):lang(245)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$pl.($ea!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$pl)."\n".$um[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(246).": ".h($ea),[lang(246)]);else
page_header(lang(247),[lang(247)]);if(!$K&&$ea!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$K=reset($L);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(220),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($K["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(248),"</th><td>","<input class='input' name='STARTS' value='",h("$K[EXECUTE_AT]$K[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(249),"</th><td>","<input class='input' name='ENDS' value='",h($K["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(250),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($K["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$mg,$K["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(155),"</th><td>",html_select("STATUS",$um,$K["STATUS"]),"</td></tr>\n","<tr><th>",lang(46),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($K["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",lang(251)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(114),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(lang(214,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$oa=($_GET["name"]?:$_GET["procedure"]);$cl=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])){foreach($K["fields"]as$u=>$k){if($k["field"]=="")unset($K["fields"][$u]);}$Pi=routine_id($oa,routine($_GET["procedure"],$cl));$vi=routine_id($K["name"],$K);$Ac=create_routine($cl,$K);$uh=substr(ME,0,-1);$Qh=lang(252);if(!$_POST["drop"]&&$Pi==$vi&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($Ac,' OR REPLACE',6,0),$uh,$Qh);else{$gn="$K[name]_adminer_".uniqid();drop_create("DROP $cl $Pi",$Ac,"DROP $cl $vi",create_routine($cl,["name"=>$gn]+$K),"DROP $cl ".routine_id($gn,$K),$uh,lang(253),$Qh,lang(254),$oa,$K["name"]);}}if($oa!=""){$sn=isset($_GET["function"])?lang(255):lang(256);page_header($sn.": ".h($oa),[$sn]);}else{$sn=isset($_GET["function"])?lang(257):lang(258);page_header($sn,[$sn]);}if(!$_POST){if($oa=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$cl);$K["name"]=$oa;}}$Cb=get_vals("SHOW CHARACTER SET");sort($Cb);$dl=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(220),": ","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>";if($dl)echo"<span id='label-language'>",lang(9),":</span> ",html_select("language",$dl,$K["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(114),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($K["fields"],$Cb,$cl);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(259),"</th>";edit_type("returns",(array)$K["returns"],$Cb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$K["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(114),"'>";if($oa!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(lang(214,$oa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["sequence"])){$qa=$_GET["sequence"];$K=$_POST;if($_POST){$x=substr(ME,0,-1);$_=trim($K["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($qa),$x,lang(260));elseif($qa=="")query_redirect("CREATE SEQUENCE ".idf_escape($_),$x,lang(261));elseif($qa!=$_)query_redirect("ALTER SEQUENCE ".idf_escape($qa)." RENAME TO ".idf_escape($_),$x,lang(262));else
redirect($x);}if($qa!="")page_header(lang(263).": ".h($qa),[h($qa)]);else
page_header(lang(264),[lang(265)]);if(!$K)$K["name"]=$qa;echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' value='",h($K["name"]),"' autocapitalize='off'>","<input type='submit' class='button default' value='",lang(114),"'>";if($qa!="")echo"<input type='submit' class='button' name='drop' value='".lang(163)."'>".confirm(lang(214,$qa))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["type"])){$ra=$_GET["type"];$K=$_POST;if($_POST){$x=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($ra),$x,lang(266));else
query_redirect("CREATE TYPE ".idf_escape(trim($K["name"]))." $K[as]",$x,lang(267));}if($ra!="")page_header(lang(268).": ".h($ra),[h($ra)]);else
page_header(lang(265),[lang(265)]);if(!$K)$K["as"]="AS ";echo"<form action='' method='post'>\n";if($ra!=""){$On=Driver::get()->getTypes();$Vd=type_values($On[$ra]);if($Vd)echo"<p><code class='jush-".DIALECT."'>ENUM (".h($Vd).")</code><p>\n";echo"<p>","<input type='submit' class='button' name='drop' value='".lang(163)."'>".confirm(lang(214,$ra)),"</p>\n";}else{echo"<p>",lang(220).": <input class='input' name='name' value='".h($K['name'])."' autocapitalize='off'>\n",doc_link(['pgsql'=>"datatype-enum.html",],"?"),"</p>\n<p>";textarea("as",$K["as"]);echo"</p>\n","<p><input type='submit' class='button default' value='".lang(114)."'></p>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$_=$_GET["name"];$K=$_POST;if($K){if(DIALECT=="sqlite")$_m=recreate_table($a,$a,[],[],[],"",[],"$_",($K["drop"]?"":$K["clause"]));else{$_m=($_==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($_)));if(!$K["drop"])$_m=(bool)queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(269):($_!=""?lang(270):lang(271))),$_m);}page_header(($_!=""?lang(272).": ".h($_):lang(177)),["table"=>$a]);if(!$K){$Ib=Driver::get()->checkConstraints($a);$K=["name"=>$_,"clause"=>$Ib[$_]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(220).': <input name="name" value="'.h($K["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",],"?"),"</p>\n<p>";textarea("clause",$K["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(114),"'>";if($_!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(lang(214,$_));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$_=isset($_GET["name"])?$_GET["name"]:"";$Hn=trigger_options();$K=trigger($_,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$Hn["Timing"])&&in_array($_POST["Event"],$Hn["Event"])&&in_array($_POST["Type"],$Hn["Type"])){$Si=" ON ".table($a);$xd="DROP TRIGGER ".idf_escape($_).(DIALECT=="pgsql"?$Si:"");$uh=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($xd,$uh,lang(273));else{if($_!="")queries($xd);queries_redirect($uh,($_!=""?lang(274):lang(275)),(bool)queries(create_trigger($Si,$_POST)));if($_!="")queries(create_trigger($Si,$K+["Type"=>reset($Hn["Type"])]));}}$K=$_POST;}if($_!="")page_header(lang(276).": ".h($_),["table"=>$a,h($_)]);else
page_header(lang(277),["table"=>$a,lang(277)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(278),"</th><td>",html_select("Timing",$Hn["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(279),"</th><td>",html_select("Event",$Hn["Event"],$K["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$Hn["Event"]))echo" <input name='Of' value='".h($K["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(44),"</th><td>",html_select("Type",$Hn["Type"],$K["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(220),"<input class='input' name='Trigger' value='",h($K["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$K["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(114),"'>";if($_!="")echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>",confirm(lang(214,$_));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$sa=$_GET["user"];$ok=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$wc)$ok[$wc=="File access on server"?"Server Admin":$wc][$K["Privilege"]]=$K["Comment"];}unset($ok["Server Admin"]["Usage"]);foreach($ok["Tables"]as$u=>$W)unset($ok["Databases"][$u]);$ui=[];if($_POST){foreach($_POST["objects"]as$u=>$W)$ui[$W]=(array)$ui[$W]+(array)$_POST["grants"][$u];}$lf=[];if(isset($_GET["host"])&&($I=Connection::get()->query("SHOW GRANTS FOR ".q($sa)."@".q($_GET["host"])))){while($K=$I->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$y)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$y[1],$z,PREG_SET_ORDER)){foreach($z
as$W){if($W[1]!="USAGE")$lf["$y[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$lf["$y[2]$W[2]"]["GRANT OPTION"]=true;}}}}$Uj=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$Ri=(isset($_GET["host"])?q($sa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Ri",ME."privileges=",lang(280));else{$xi=q($_POST["user"])."@".q($_POST["host"]);$Nj=$_POST["pass"];$Dc=false;$I=true;if($Ri!=$xi){$Dc=(bool)queries("CREATE USER $xi IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($Nj));$I=$Dc;}elseif($Nj!="")$I=(bool)queries("SET PASSWORD FOR $xi = ".($Uj||$_POST["hashed"]?q($Nj):"PASSWORD(".q($Nj).")"));if($I){$Zk=[];foreach($ui
as$Hi=>$jf){if(isset($_GET["grant"]))$jf=array_filter($jf);$jf=array_keys($jf);if(isset($_GET["grant"]))$Zk=array_diff(array_keys(array_filter($ui[$Hi],'strlen')),$jf);elseif($Ri==$xi){$Oi=array_keys((array)$lf[$Hi]);$Zk=array_diff($Oi,$jf);$jf=array_diff($jf,$Oi);unset($lf[$Hi]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Hi,$y)&&(!grant(false,$Zk,$y[2],$y[1],$xi)||!grant(true,$jf,$y[2],$y[1],$xi))){$I=false;break;}}}if($I&&isset($_GET["host"])){if($Ri!=$xi)queries("DROP USER $Ri");elseif(!isset($_GET["grant"])){foreach($lf
as$Hi=>$Zk){if(preg_match('~^(.+)(\(.*\))?$~U',$Hi,$y))grant(false,array_keys($Zk),$y[2],$y[1],$xi);}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(281):lang(282)),$I);if($Dc)Connection::get()->query("DROP USER $xi");}}$sn=isset($_GET["host"])?lang(28).": ".h("$sa@$_GET[host]"):lang(186);$tn=isset($_GET["host"])?h($sa):lang(186);page_header($sn,["privileges"=>['',lang(72)],$tn]);if($_POST){$K=$_POST;$lf=$ui;}else{$K=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($lf)$lf[".*"]=[];elseif(DB!="")$lf[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$lf["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($K["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(28),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($K["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(29),"</th>","<td><input class='input' name='pass' id='pass' value='",h($K["pass"]),"' autocomplete='new-password'>";if(!$Uj)echo
checkbox("hashed",1,$K["hashed"],lang(283),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$K["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(72).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$p=0;foreach($lf
as$Hi=>$jf){echo"<th>";if($Hi=="*.*")echo"*.*",input_hidden("objects[$p]","*.*");else
echo"<input class='input' name='objects[$p]' value='".h(trim($Hi))."' size='10' autocapitalize='off'>";echo"</th>";$p++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(30),"Tables"=>lang(8),"Procedures"=>lang(284),]as$wc=>$ed){foreach((array)$ok[$wc]as$nk=>$dc){echo"<tr>";if($ed)echo"<td>$ed</td>";echo"<td".(!$ed?" colspan='2'":"").' lang="en" title="'.h($dc).'">'.h($nk)."</td>";$p=0;foreach($lf
as$Hi=>$jf){$_="'grants[$p][".h(strtoupper($nk))."]'";$X=$jf[strtoupper($nk)];$tk=strpos($Hi,"@")!==false;$ti=$Hi==".*";$La=$nk=="All privileges";$kf=$nk=="Grant option";if($Hi=="*.*"&&$nk=="Proxy")echo"<td></td>";elseif($tk&&$nk!="Proxy"&&!$kf)echo"<td></td>";elseif($wc=="Server Admin"&&$Hi!=(isset($lf["*.*"])?"*.*":".*")&&!(($tk||$ti)&&$nk=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$_>"."<option></option>"."<option value='1'".($X?" selected":"").">".lang(285)."</option>"."<option value='0'".($X=="0"?" selected":"").">".lang(286)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$_ value='1'".($X?" checked":"").($La?" id='grants-$p-all'":(!$kf?" class='grants-$p'":"")).">";if($La)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$p'); };");elseif(!$kf)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$p-all'); };");echo"</label>";}$p++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(114),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(163),"'>\n",confirm(lang(214,"$sa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Og=0;foreach((array)$_POST["kill"]as$W){if(kill_process($W))$Og++;}queries_redirect(ME."processlist=",lang(287,$Og),$Og||!$_POST["kill"]);}}page_header(lang(153),[lang(153)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$p=-1;foreach(process_list()as$p=>$K){if(!$p){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$u=>$W)echo"<th>$u".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($u),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[DIALECT=="sql"?"Id":"pid"],0):"");foreach($K
as$u=>$W)echo"<td>".($W!=""&&((DIALECT=="sql"&&$u=="Info"&&preg_match("~Query|Killed~",$K["Command"]))||(DIALECT=="pgsql"&&$u=="query")||(DIALECT=="oracle"&&$u=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($W,100).'</code> <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($W)).'">'.icon("edit").lang(288).'</a>':h($W));echo"\n";}if($p>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($p+1)."/".lang(289,max_connections()),"<p><input type='submit' class='button' value='".lang(290)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$R=table_status1($a);$t=indexes($a);$l=fields($a);$Se=column_foreign_keys($a);$Ji=$R["Oid"];$al=[];$d=[];$vl=[];$gj=[];$ln=null;foreach($l
as$u=>$k){$_=Admin::get()->getFieldName($k);$oi=html_entity_decode(strip_tags($_),ENT_QUOTES);if(isset($k["privileges"]["select"])&&$_!=""){$d[$u]=$oi;if(is_shortable($k))$ln=Admin::get()->processSelectionLength();}if(isset($k["privileges"]["where"])&&$_!="")$vl[$u]=$oi;if(isset($k["privileges"]["order"])&&$_!="")$gj[$u]=$oi;$al+=$k["privileges"];}list($M,$mf)=Admin::get()->processSelectionColumns($d,$t);$M=array_unique($M);$mf=array_unique($mf);$wg=count($mf)<count($M);$Z=Admin::get()->processSelectionSearch($l,$t);$fj=Admin::get()->processSelectionOrder($l,$t);$w=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Tn=>$K){$Ta=convert_field($l[key($K)]);$M=[$Ta?:idf_escape(key($K))];$Z[]=where_check($Tn,$l);$J=Driver::get()->select($a,$M,$Z,$M);if($J)echo
first($J->fetchRow());}exit;}$F=$Wn=[];foreach($t
as$s){if($s["type"]=="PRIMARY"){$F=array_flip($s["columns"]);$Wn=($M?$F:[]);foreach($Wn
as$u=>$W){if(in_array(idf_escape($u),$M))unset($Wn[$u]);}break;}}if($Ji&&!$F){$F=$Wn=[$Ji=>0];$t[]=["type"=>"PRIMARY","columns"=>[$Ji]];}$Vl=Admin::get()->getSettings();if($_POST){$Ho=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Ib=[];foreach($_POST["check"]as$Db)$Ib[]=where_check($Db,$l);$Ho[]="((".implode(") OR (",$Ib)."))";}$Ho=($Ho?"\nWHERE ".implode(" AND ",$Ho):"");if($_POST["export"]){$Vl->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$af=($M?implode(", ",$M):"*").convert_fields($d,$l,$M)."\nFROM ".table($a);$pf=($mf&&$wg?"\nGROUP BY ".implode(", ",$mf):"").($fj?"\nORDER BY ".implode(", ",$fj):"");if(!is_array($_POST["check"])||$F)$G="SELECT $af$Ho$pf";else{$Qn=[];foreach($_POST["check"]as$W)$Qn[]="(SELECT".limit($af,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$l).$pf,1).")";$G=implode(" UNION ALL ",$Qn);}Admin::get()->dumpData($a,"table",$G);exit;}if($_POST["save"]||$_POST["delete"]){$I=true;$Da=0;$Tl=[];if(!$_POST["delete"]){$Dl=array_keys($_POST["fields"]+$_POST["function"]);foreach($Dl
as$_){$W=process_input($l[$_]);if($W!==null&&($_POST["clone"]||$W!==false))$Tl[idf_escape($_)]=($W!==false?$W:idf_escape($_));}}if($_POST["delete"]||$Tl){if($_POST["clone"])$G="INTO ".table($a)." (".implode(", ",array_keys($Tl)).")\nSELECT ".implode(", ",$Tl)."\nFROM ".table($a);if($_POST["all"]||($F&&is_array($_POST["check"]))||$wg){$I=($_POST["delete"]?Driver::get()->delete($a,$Ho):($_POST["clone"]?queries("INSERT $G$Ho".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$Tl,$Ho)));$Da=Connection::get()->getAffectedRows();if(is_object($I))$Da+=$I->getRowsCount();}else{foreach((array)$_POST["check"]as$W){$Do="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$l);$I=($_POST["delete"]?Driver::get()->delete($a,$Do,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$Do)):Driver::get()->update($a,$Tl,$Do,1)));if(!$I)break;$Da+=Connection::get()->getAffectedRows();}}}$Qh=lang(291,$Da);if($_POST["clone"]&&$I&&$Da==1){$Xg=last_id($I);if($Xg)$Qh=lang(208," $Xg");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Qh,(bool)$I);if(!$_POST["delete"]){$Id=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});edit_form($a,$Id,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(292));else{$_m=true;$Da=0;foreach($_POST["val"]as$Tn=>$K){$Tl=[];foreach($K
as$u=>$W){$u=bracket_escape($u,true);$Tl[idf_escape($u)]=(preg_match('~char|text~',$l[$u]["type"])||$W!=""?Admin::get()->processFieldInput($l[$u],$W):"NULL");}$_m=(bool)Driver::get()->update($a,$Tl," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Tn,$l),($wg||$F?0:1)," ");if(!$_m)break;$Da+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(291,$Da),$_m);}}elseif(!is_string($m=get_file("csv_file",true)))Admin::get()->addError(upload_error($m));elseif(!preg_match('~~u',$m))Admin::get()->addError(lang(293));else{$Vl->updateParameter("exportFormat",$_POST["import_format"]);$Xb=array_keys($l);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$m,$z);$Da=count($z[0]);Driver::get()->begin();$El=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$L=[];foreach($z[0]as$u=>$W){preg_match_all("~((?>\"[^\"]*\")+|[^$El]*)$El~",$W.$El,$Dh);if(!$u&&!array_diff($Dh[1],$Xb)){$Xb=$Dh[1];$Da--;}else{$Tl=[];foreach($Dh[1]as$p=>$Qb)$Tl[idf_escape($Xb[$p])]=($Qb==""&&$l[$Xb[$p]]["null"]?"NULL":q(preg_match('~^".*"$~s',$Qb)?str_replace('""','"',substr($Qb,1,-1)):$Qb));$L[]=$Tl;}}$_m=!$L||Driver::get()->insertUpdate($a,$L,$F);if($_m)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(294,$Da),$_m);Driver::get()->rollback();}}$Rm=Admin::get()->getTableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(55).": $Rm",[$Rm]);$Tl=null;if(isset($al["insert"])||!support("table")){$D=[];foreach((array)$_GET["where"]as$W){if(isset($Se[$W["col"]])&&count($Se[$W["col"]])==1&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$W["val"])))))$D["set"."[".bracket_escape($W["col"])."]"]=$W["val"];}$Tl=$D?"&".http_build_query($D):"";}Admin::get()->printTableMenu($R,$Tl);if(!$d&&support("table"))echo"<p class='error'>".lang(295).($l?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div style='display: none;'>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),'<input type="submit" class="button" value="'.h(lang(55)).'">',"</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($M,$d);Admin::get()->printSelectionSearch($Z,$vl,$t);Admin::get()->printSelectionOrder($fj,$gj,$t);Admin::get()->printSelectionLimit($w);Admin::get()->printSelectionLength($ln);Admin::get()->printSelectionAction($t);echo"</div>\n</form>\n";$C=isset($_GET["page"])?$_GET["page"]:null;if($C=="last"){$Ye=Connection::get()->getValue(count_rows($a,$Z,$wg,$mf));$C=(int)floor(max(0,intval($Ye)-1)/$w);}else{$Ye=false;$C=(int)$C;}$xl=$M;$nf=$mf;if(!$xl){$xl[]="*";$xc=convert_fields($d,$l,$M);if($xc)$xl[]=substr($xc,2);}foreach($M
as$u=>$W){$k=$l[idf_unescape($W)];if($k&&($Ta=convert_field($k)))$xl[$u]="$Ta AS $W";}if(!$wg&&$Wn){foreach($Wn
as$u=>$W){$xl[]=idf_escape($u);if($nf)$nf[]=idf_escape($u);}}$I=Driver::get()->select($a,$xl,$Z,$nf,$fj,$w,$C,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(DIALECT=="mssql"&&$C)$I->seek($w*$C);echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";$L=[];while($K=$I->fetchAssoc()){if($C&&DIALECT=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$w&&$mf&&$wg&&DIALECT=="sql")$Ye=Connection::get()->getValue(" SELECT FOUND_ROWS()");$Jd=false;if(!$L)echo"<p class='message'>".lang(90)."\n";else{$gb=Admin::get()->getBackwardKeys($a,$Rm);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($mf||!$M){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly'>".script("gid('all-page').onclick = partial(formCheck, /check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(296),"'>",icon_solo("edit-all"),"</a>";}$pi=[];$ef=[];reset($M);$Bk=1;foreach($L[0]as$u=>$W){if(!isset($Wn[$u])){$zl=key($M);$W=isset($_GET["columns"][$zl])?$_GET["columns"][$zl]:[];$k=$l[$M?($W?$W["col"]:current($M)):$u];$_=($k?Admin::get()->getFieldName($k,$Bk):(isset($W["fun"])?"*":h($u)));if($_!=""){$Bk++;$pi[$u]=$_;$c=idf_escape($u);$Kf=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($u);$ed="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($u))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$cf=apply_sql_function(isset($W["fun"])?$W["fun"]:null,$_);$fm=isset($k["privileges"]["order"])||(isset($W["fun"])?$W["fun"]:null);if($fm)echo'<a href="',h($Kf.($fj[0]==$c||$fj[0]==$u?$ed:'')),'">',"$cf</a>";else
echo$cf;echo"<span class='column hidden'>";if($fm)echo"<a href='".h($Kf.$ed)."' title='".lang(62)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($W["fun"])&&isset($k["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.lang(59).'" class="button light jsonly">',icon_solo("search"),'</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($u)."');");echo"</span>";}$ef[$u]=isset($W["fun"])?$W["fun"]:null;next($M);}}$fh=[];if($_GET["modify"]){foreach($L
as$K){foreach($K
as$u=>$W)$fh[$u]=max($fh[$u],min(40,strlen(utf8_decode($W))));}}if($gb)echo"<th>".lang(17)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($L,$Se)as$mi=>$K){$Sn=unique_array($L[$mi],$t);if(!$Sn){$Sn=[];reset($M);foreach($L[$mi]as$u=>$W){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$Sn[$u]=$W;next($M);}}$Tn="";foreach($Sn
as$u=>$W){$k=isset($l[$u])?$l[$u]:null;if((DIALECT=="sql"||DIALECT=="pgsql")&&$k&&preg_match('~char|text|enum|set~',$k["type"])&&strlen($W)>64){$u=(strpos($u,'(')?$u:idf_escape($u));$u="MD5(".(DIALECT!='sql'||preg_match("~^utf8~",isset($k["collation"])?$k["collation"]:"")?$u:"CONVERT($u USING ".charset(Connection::get()).")").")";$W=md5($W);}$Tn
.="&".($W!==null?urlencode("where[".bracket_escape($u)."]")."=".urlencode($W===false?"f":$W):"null%5B%5D=".urlencode($u));}echo"<tr>";if($mf||!$M){echo"<td class='actions'>",checkbox("check[]",substr($Tn,1),in_array(substr($Tn,1),(array)$_POST["check"]));if(!$wg&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$Tn),"' class='edit' title='",lang(38),"'>",icon_solo("edit"),"</a>";}reset($M);foreach($K
as$u=>$W){if(isset($pi[$u])){$c=current($M);$k=isset($l[$u])?$l[$u]:null;$x="";if($k&&is_blob($k)&&$W!="")$x=ME.'download='.urlencode($a).'&field='.urlencode($u).$Tn;if(!$x&&$W!==null){foreach((array)$Se[$u]as$o){if(count($Se[$u])==1||end($o["source"])==$u){$x="";foreach($o["source"]as$p=>$gm)$x
.=where_link($p,$o["target"][$p],$L[$mi][$gm]);$x=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($o["db"]),ME):ME).'select='.urlencode($o["table"]).$x;if($o["ns"])$x=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($o["ns"]),$x);if(count($o["source"])==1)break;}}}if($c=="COUNT(*)"){$x=ME."select=".urlencode($a);$p=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$Sn))$x
.=where_link($p++,$V["col"],$V["val"],$V["op"]);}foreach($Sn
as$Fg=>$V)$x
.=where_link($p++,$Fg,$V);}$Ci=$W===null;$Lf=select_value($W,$x,$k,$ln);$Zd=bracket_escape($u);$q=h("val[$Tn][$Zd]");$ek=isset($_POST["val"][$Tn][$Zd])?$_POST["val"][$Tn][$Zd]:null;$Yn=isset($k["privileges"]["update"])?$k["privileges"]["update"]:false;$Hd=!is_array($K[$u])&&is_utf8($Lf)&&$L[$mi][$u]==$K[$u]&&!$ef[$u]&&!(isset($k["generated"])?$k["generated"]:false);$T=($c&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$c,$z)?$l[idf_unescape($z[2])]["type"]:(isset($k["type"])?$k["type"]:null));$gi=$T=="money"||($c&&preg_match('~^SUM\((.+)\)~',$c,$z)&&$l[idf_unescape($z[1])]["type"])=="money";$in=$T&&preg_match('~text|json|lob~',$T);$Gi=($T&&preg_match(number_type(),$T))||($c&&preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',$c));$Nb=$Gi&&($Ci||is_numeric(strip_tags($Lf))||$gi)?"class='number'":"";echo"<td id='$q' $Nb";if(($_GET["modify"]&&$Hd&&!$Ci)||$ek!==null){$Jd=true;$sf=h($ek!==null?$ek:$K[$u]);echo" data-editing='true'>".($in?"<textarea name='$q' cols='30' rows='".(substr_count($K[$u],"\n")+1)."'>$sf</textarea>":"<input class='input' name='$q' value='$sf' size='$fh[$u]'>");}else{$wh=strpos($Lf,"<i>…</i>");if($Yn)echo" data-text='".($wh?2:($in?1:0))."'".($Hd?"":" data-warning='".h(lang(297))."'");echo">$Lf";}}next($M);}if($gb){echo"<td>";Admin::get()->printBackwardKeys($gb,$L[$mi]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: partialArg(tableClick, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: partialArg(tableClick, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($L||$C){$ce=true;if($_GET["page"]!="last"){if(!$w||(count($L)<$w&&($L||!$C)))$Ye=($C?$C*$w:0)+count($L);elseif(DIALECT!="sql"||!$wg){$Ye=($wg?false:found_rows($R,$Z));if($Ye<max(1e4,2*($C+1)*$w))$Ye=first(slow_query(count_rows($a,$Z,$wg,$mf)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$ce=false;}}$yj=($w!==null&&($Ye===false||$Ye>$w||$C));if($yj){if(($Ye===false?count($L)+1:$Ye-$C*$w)>$w)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($C+1)),'" class="loadmore">',icon("expand"),lang(298),'</a>',script("qsl('a').onclick = partial(loadNextPage, $w, '".lang(299)."…');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($yj){$Hh=($Ye===false?$C+(count($L)>=$w?2:1):(int)floor(($Ye-1)/$w));$td="<li>…</li>";echo"<fieldset>";if(DIALECT!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".lang(300)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(300)."', '".($C+1)."')); return false; };"),"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$C);if($C>5)echo$td;for($p=max(1,$C-4);$p<min($Hh,$C+5);$p++)echo
pagination($p,$C);if($Hh>0){if($C+5<$Hh)echo$td;echo($ce&&$Ye!==false?pagination($Hh,$C):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Hh'>".lang(301)."</a>");}echo"</ul></div>";}else{echo"<legend>".lang(300)."</legend>","<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$C);if($C>1)echo$td;if($C)echo
pagination($C,$C);if($Hh>$C){echo
pagination($C+1,$C);if($Hh>$C+1)echo$td;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(302)."</legend><div class='fieldset-content'>";$md=($ce?"":"~ ").$Ye;echo
checkbox("all",1,0,($Ye!==false?($ce?"":"~ ").lang(190,$Ye):""),"const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$md' : checked); selectCount('selected2', this.checked || !checked ? '$md' : checked);")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(296),"</legend>";$kl=($_GET["modify"]?"":" data-inline-edit='1'".($Jd?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(292)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(114),"'",$kl,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(162)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(38),"'> ","<input type='submit' class='button' name='clone' value='",lang(288),"'> ","<input type='submit' class='button' name='delete' value='",lang(118),"'>",confirm(),"</div>","</fieldset>\n";}$Ue=Admin::get()->getDumpFormats();foreach((array)$_GET["columns"]as$c){if($c["fun"]){unset($Ue['sql']);break;}}if($Ue){print_fieldset_start("export",lang(74)." <span id='selected2'></span>","export");echo
html_select("format",$Ue,$Vl->getParameter("exportFormat"));$uj=Admin::get()->getDumpOutputs();echo($uj?" ".html_select("output",$uj,$Vl->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(74)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(73),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$Vl->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(73)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(197);echo"</p>";}echo
input_token(),"</form>\n",(!$mf&&$M?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$O=isset($_GET["status"]);$sn=$O?lang(155):lang(154);page_header($sn,[$sn]);$no=($O?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$no)echo"<p class='message'>",lang(90),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($no
as$K){echo"<tr>";$u=array_shift($K);echo"<th><code class='jush-".DIALECT.($O?"status":"set")."'>".h($u)."</code></th>";foreach($K
as$W)echo"<td>",nl2br(h($W)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Cm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$f=[];$Rc=null;foreach(table_status()as$_=>$R){$f["Comment-$_"]=h($R["Comment"]);if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){$f["Engine-$_"]=h($R["Engine"]);$Sb=isset($R["Collation"])?$R["Collation"]:"";if($Sb==""){if($Rc===null)$Rc=db_collation(DB,collations())??"";$Sb=$Rc;}$f["Collation-$_"]=h($Sb);foreach($Cm+["Auto_increment"=>0,"Rows"=>0]as$u=>$W){if($R[$u]!=""){$W=format_number($R[$u]);if($W>=0)$f["$u-$_"]=($u=="Rows"&&$W&&$R["Engine"]==(DIALECT=="pgsql"?"table":"InnoDB")?"~ $W":$W);if(isset($Cm[$u]))$Cm[$u]+=($R["Engine"]!="InnoDB"||$u!="Data_free"?$R[$u]:0);}elseif(array_key_exists($u,$R))$f["$u-$_"]="?";}}}foreach($Cm
as$u=>$W)$f["sum-$u"]=format_number($W);echo
json_encode($f,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$f=[];foreach(count_tables(Admin::get()->getDatabases())as$h=>$W){$f["tables-$h"]=$W;$f["size-$h"]=db_size($h);}echo
json_encode($f,JSON_UNESCAPED_UNICODE);}exit;}else{$bn=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($bn&&!$_POST["search"]){$I=true;$Qh="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$Qh=lang(303);}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Qh=lang(304);}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Qh=lang(305);}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$Qh=lang(306);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$K)$Qh
.="<b>".h($Q)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$I=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),$_POST["tables"]));$Qh=lang(307);}elseif(!$_POST["tables"])$Qh=lang(78);elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($K=$I->fetchAssoc())$Qh
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$Qh,(bool)$I);}if($_GET["ns"]=="")page_header(lang(30).": ".h(DB),true);else
page_header(lang(81).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(308)."</h2>\n";$rl=Admin::get()->getSchemas();if(!$rl)echo"<p class='message'>".lang(309)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(81),"</th></tr></thead>";foreach($rl
as$_)echo"<tr><th><a href='",h(ME),"ns=".urlencode($_),"' title='",lang(310),"'>".h($_)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(76)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(311)."</h2>\n";$Wm=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$Rc=db_collation(DB,collations());$d=["Engine"=>["label"=>lang(167),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($Rc!="")$d["Collation"]=["label"=>lang(45),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$d+=["Data_length"=>["label"=>lang(312),"doc"=>doc_link($Wm+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(35),],"Index_length"=>["label"=>lang(313),"doc"=>doc_link($Wm+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(171),],"Data_free"=>["label"=>lang(314),"doc"=>doc_link($Wm),"link"=>"edit","title"=>lang(7),],"Auto_increment"=>["label"=>lang(47),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(35),],"Rows"=>["label"=>lang(315),"doc"=>doc_link($Wm+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(33),],];if(support("comment"))$d["Comment"]=["label"=>lang(46),"doc"=>doc_link($Wm+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$fj=(is_string($_GET["order"])?$_GET["order"]:"");$fd=null;if(preg_match('~^(.+)-(asc|desc)$~',$fj,$y)){$fj=$y[1];$fd=($y[2]=="desc");}if($fj!="__table"&&!isset($d[$fj]))$fj="";if($fd===null)$fd=isset($d[$fj]["link"]);$Lo=($fj!=""&&$fj!="__table");$Zm=($Lo?table_status():tables_list());if(!$Zm)echo"<p class='message'>".lang(78)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(316)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' class='button' name='search' value='".lang(59)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<td class="actions"><input id="check-all" type="checkbox" class="input jsonly">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$ni=($fj==""||$fj=="__table");$Qm=($ni&&!$fd?ME."order=__table-desc":substr(ME,0,-1));echo'<th><a href="'.h($Qm).'">'.lang(8).'</a>';foreach($d
as$u=>$c){$hd=($u===$fj?!$fd:isset($c["link"]));echo'<td><a href="'.h(ME)."order=$u-".($hd?"desc":"asc").'">'.$c["label"].'</a>'.$c["doc"];}echo"</thead>\n","<tbody>\n";if($fj=="__table"){if($fd)$Zm=array_reverse($Zm,true);}elseif($fj){uasort($Zm,function($ua,$db)use($fj,$fd){$Mo=isset($ua[$fj])?$ua[$fj]:null;$Oo=isset($db[$fj])?$db[$fj]:null;$I=($Mo<$Oo?-1:($Mo>$Oo?1:0));return($fd?-$I:$I);});}$Cm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$S=0;foreach($Zm
as$_=>$O){$so=($Lo?is_view($O):$O!==null&&!preg_match('~table|sequence~i',$O));$Sd=($Lo?(isset($O["Engine"])?$O["Engine"]:""):$O);$q=h("Table-".$_);echo'<tr><td class="actions">'.checkbox(($so?"views[]":"tables[]"),$_,in_array("$_",$bn,true),"","","",$q);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$wa="table";else$wa="select";echo"<th><a href='",h(ME),"$wa=",urlencode($_),"' id='$q'>",h($_),"</a></th>";if($so&&!preg_match('~materialized~i',$Sd)){$sn=lang(166);$Yb=count($d)-(support("comment")?2:1);echo'<td colspan="'.$Yb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($_)."' title='".lang(36)."'>$sn</a>":$sn),'<td align="right"><a href="'.h(ME)."select=".urlencode($_).'" title="'.lang(33).'">?</a>';}else{foreach($d
as$u=>$c){if($u=="Comment")continue;$q=" id='$u-".h($_)."'";$x=isset($c["link"])?$c["link"]:"";if(!$x){$W="";if($Lo){$W=isset($O[$u])?$O[$u]:"";if($u=="Collation"&&$W=="")$W=$Rc;}echo"<td$q>".h($W);continue;}$W="?";if($Lo){$Fi=isset($O[$u])?$O[$u]:"";if(is_numeric($Fi)&&$Fi>=0){$W=($u=="Rows"&&$Fi&&$Sd==(DIALECT=="pgsql"?"table":"InnoDB")?"~ ":"").format_number($Fi);if(isset($Cm[$u])&&($Sd!="InnoDB"||$u!="Data_free"))$Cm[$u]+=$Fi;}}echo"<td align='right'>".(support("table")||$u=="Rows"||(support("indexes")&&$u!="Data_length")?"<a href='".h(ME."$x=").urlencode($_)."'$q title='".$c["title"]."'>$W</a>":"<span$q>$W</span>");}$S++;}echo(support("comment")?"<td id='Comment-".h($_)."'>".($Lo?h(isset($O["Comment"])?$O["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<tfoot><tr>","<td><th>".lang(289,count($Zm)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($Rc!=""?"<td>".h($Rc):"");foreach($Cm
as$u=>$Bm)echo"<td align='right' id='sum-$u'>".($Lo?format_number($Bm):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($Lo?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$ko="<input type='submit' class='button' value='".lang(317)."'> ".help_script("VACUUM");$aj="<input type='submit' class='button' name='optimize' value='".lang(318)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(162)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$ko."<input type='submit' class='button' name='check' value='".lang(319)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$ko.$aj:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(320)."'> ".help_script("ANALYZE TABLE").$aj."<input type='submit' class='button' name='check' value='".lang(319)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(321)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(322)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm()."<input type='submit' class='button' name='drop' value='".lang(163)."'>".help_script("DROP TABLE").confirm()."\n";$g=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";$tl="";if(count($g)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(323)." <span id='selected3'></span></legend><div>";$h=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($g?html_select("target",$g,$h,"","label-move"):'<input class="input" name="target" value="'.h($h).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(324)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(325)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(326)):""),"</div></fieldset>\n";$tl=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo
input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $S);":"")."$tl }"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(77),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(242),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(182)."</h2>\n";$el=routines();if($el){$gc=$el[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(220),'</th><td>',lang(44),'</td><td>',lang(259),"</td>";if($gc)echo"<td>",lang(46),"</td>";echo"<td></td>","</tr></thead>\n";foreach($el
as$K){$_=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$_),'">',h($K["ROUTINE_NAME"]),'</a></th>','<td>',h($K["ROUTINE_TYPE"]),'</td>','<td>',h($K["DTD_IDENTIFIER"]),'</td>';if($gc)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($K["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$_).'">'.lang(174)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(258),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(257),"</a>\n","</p>\n";}if(support("sequence")){echo"<h2 id='sequences'>".lang(327)."</h2>\n";$Hl=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Hl){echo"<table>\n","<thead><tr><th>",lang(220),"</th><td></td></tr></thead>\n";foreach($Hl
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"sequence=",urlencode($W),"'>",lang(174),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"sequence='>",icon("add"),lang(264),"</a></p>\n";}if(support("type")){echo"<h2 id='user-types'>".lang(108)."</h2>\n";$ho=types();if($ho){echo"<table>\n","<thead><tr><th>",lang(220),"</th><td></td></tr></thead>\n";foreach($ho
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"type=",urlencode($W),"'>",lang(174),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"type='>",icon("add"),lang(265),"</a></p>\n";}if(support("event")){echo"<h2 id='events'>".lang(183)."</h2>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".lang(220)."<td>".lang(328)."<td>".lang(248)."<td>".lang(249)."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?lang(329)."<td>".$K["Execute at"]:lang(250)." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.lang(174).'</a>';echo"</table>\n";$ae=Connection::get()->getValue("SELECT @@event_scheduler");if($ae&&$ae!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($ae)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(247),"</a></p>\n";}}}page_footer();