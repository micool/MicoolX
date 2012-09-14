<?php
header('Content-Type:text/html;charset=utf-8');
include_once 'conf/mail/mail.config.php';
include_once 'conf/mail/class.phpmailer.php';
include_once 'conf/x.class.php';
$PkeyLink = 'http://127.0.0.1/micoolx/mypasskey.php';
//echo md5_file('mypasskey.php');
//$vars=new MicoolX();
//$vars->email($ConMail,'test', '');

echo MicoolX::xcode_en('micool', 45);
//echo MicoolX::xcode_de('bWljb29s', 45);


if (isset($_GET['ac'])) {
    if (function_exists($_GET['ac'])) {
        $_GET['ac']();
    } else {
        MicoolX::log('ac参数错误！');
        exit('error');
    }
}

function search() {
    if (isset($_POST['keywords'])) {
        $thenewID = base64_decode($_POST['keywords']);
        $thepath = 'pss/x/' . $thenewID . '.xmd';
        if (!is_file($thepath)) {
            $back['status'] = false;
        } else {
            $back['status'] = true;
            $fp = @fopen($thepath, "r");
            
            $tempinfo = MicoolX::encode(stream_get_contents($fp));
            echo $tempinfo;
            $back['info']=$tempinfo;
          //  $back['time'] = $tempinfo['time'];
           // $back['title'] = base64_decode($tempinfo['title']);
           // $back['value'] = base64_decode($tempinfo['value']);
            MicoolX::log('查询了：' . $thenewID);
            fclose($fp);
        }
    } else {
        $back['status'] = false;
    }
    MicoolX::backPostjosn($back);
}

function doin() {
    //新建状态
    if ($_POST['dk'] == 'new') {
        MicoolX::Counts('+');
        $thenewID = (string) MicoolX::Counts('r');
        $thepath = 'pss/x/' . $thenewID . '.xmd';
        if (is_file($thepath)) {
            $back['status'] = false;
            MicoolX::log('添加过程ID存在！');
        } else {
            $in['key'] = base64_encode($thenewID);
            $in['time'] = date('Y-m-d H:i:s');
            $in['title'] = base64_encode($_POST['dt']);
            $in['value'] = base64_encode($_POST['dv']);
            $word = MicoolX::encode(json_encode($in));
            MicoolX::write($thepath, $word, 'indata');
            $back['id'] = $thenewID;
            $back['key'] = base64_encode($thenewID);
            $back['title'] = $_POST['dt'];
            $back['status'] = true;
            MicoolX::log('添加成功！ [ID:' . $thenewID . '][TITLE:' . $in['title'] . ']');
        }
        MicoolX::backPostjosn($back);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Micool PassWord</title>
        <script src="jquery-1.8.0.min.js" language="javascript"></script>
        <style>
            .search{
                width:500px; 
                margin:0 auto;
                border:#CCC 1px solid;
                padding:50px;
            }
            .zhepass{
                display:none;
                width:500px;
                background:#FCF38B;
                margin:5px auto;
                border:#CCC 1px dotted;
                padding:50px;
            }
            .doing{
                width:500px; 
                margin:0 auto;
                border:#CCC 1px solid;
                padding:50px;
            }
            .callback{
                display:none;
                width:500px;
                background:#FCF38B;
                margin:5px auto;
                border:#CCC 1px dotted;
                padding:50px;
            }
            #input{ width:300px;height:30px; line-height:30px;
                    border:#CCC 1px solid; padding-left:5px; color:#F90; font-size:16px; font-weight:700;
            }
            #textarea{width:300px;height:100px; line-height:20px;
                      border:#CCC 1px solid; padding-left:5px; color:#F90; font-size:16px; font-weight:700;}
            #buttontoin,#buttonso{height:35px; padding-left:10px; padding-right:10px; padding-top: 0; padding-bottom: 0px;  background:#FFF; line-height:35px;
                                  border:#CCC 1px solid; font-size:16px; font-weight:700; }
            #buttonso:hover,#buttontoin:hover{ background:#999}
            .doing{}
        </style>
    </head>

    <body>
        <?php
        $values = isset($_GET['s']) ? $_GET['s'] : 'so';
        if ($values == "so") {
            ?>
            <div class="search" id="search">
                <label for="textfield">输入序号：</label>
                <input type="text" name="tid" id="input" />
                <input type="submit" name="button" id="buttonso" value="搜索" />
            </div>
            <div class="zhepass">序号：<br />名称：<br /> 相关值：<br />
                <a href="#">修改</a> <a href="#">删除</a>
            </div>
            <?php
        } elseif ($values == "do") {
            ?>
            <div class="doing" id="doing">
                <h4>新建</h4>
                <label for="textfield">输入键值：</label>
                <input type="text" name="thekey" id="input" value="new" disabled="disabled"/>
                <br />
                <label for="textfield">输入名称：</label>
                <input type="text" name="thetitle" id="input" />
                <br />
                <label for="textfield">相关的值：</label>
                <textarea name="thevalue" id="textarea"></textarea>
                <br /><br />
                <input type="submit" name="button" id="buttontoin" value="提交" style=" float:right;" />
            </div>
            <div class="callback"></div>
            <?php
        }
        ?>
        <script>
            $('#buttonso').bind('click', function() {
                var thekey= $("#search input[name='tid']").val();
                $.post("?ac=search", { "keywords": thekey},
                function(data){
                    if(data.status){
                        $("#search input[name='tid']").val('');
                        $('.zhepass').show().html('查询成功<br />序号：'+data.title+'<br />名称：'+data.key+'('+data.id+')<br />相关值：'+data.key);
                    }else{
                        $('.zhepass').show().html('没有查询到数据');
                    }
                }, "json");
            });
            $('#buttontoin').bind('click', function() {
                var thekey= $("#doing input[name='thekey']").val();
                var thetitle= $("#doing input[name='thetitle']").val();
                var thevalue= $("#doing textarea[name='thevalue']").val();  
                $.post("?ac=doin", { "dk": thekey, "dt": thetitle, "dv": thevalue},
                function(data){
                    if(data.status){
                                        
                        $("#doing input[name='thetitle']").val('');
                        $("#doing textarea[name='thevalue']").val('')
                        $('.callback').show().html('添加成功<br />名称：'+data.title+'<br />加密序号：'+data.key+'('+data.id+')<br />获取连接：<a href="<?= $PkeyLink ?>"><?= $PkeyLink ?>/'+data.key+'</a>');
                    }else{
                        $('.callback').show().html('添加失败');
                    }
                }, "json");
            })
        </script>
    </body>
</html>
