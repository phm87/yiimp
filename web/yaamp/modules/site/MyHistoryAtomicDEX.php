<?php
JavascriptFile("/yaamp/ui/js/jquery.metadata.js");
JavascriptFile("/yaamp/ui/js/jquery.tablesorter.widgets.js");
echo getAdminSideBarLinks();
$symbol = getparam('symbol');
$market = getparam('market');
//$newuuid = "";
$newuuid= getparam('newuuid');
//var_dump($_GET);
$coins = "";
$coins2 = "";
$list = getdbolist('db_coins', "installed AND (id = 6 OR ".
        "id IN (SELECT DISTINCT coinid FROM markets WHERE name = 'AtomicDEX'))");
foreach($list as $coin)
{
        if($coin->symbol == $symbol)
                $coins .= '<option value="'.$coin->symbol.'" selected>'.$coin->symbol.'</option>';
        else
                $coins .= '<option value="'.$coin->symbol.'">'.$coin->symbol.'</option>';

        if($coin->symbol == $market)
                $coins2 .= '<option value="'.$coin->symbol.'" selected>'.$coin->symbol.'</option>';
        else
                $coins2 .= '<option value="'.$coin->symbol.'">'.$coin->symbol.'</option>';
}
echo <<<end
<!-- <h1>AtomicDEX Orderbooks</h1> -->
<div align="right" style="margin-top: -14px; margin-bottom: -6px; margin-right: 140px;">
Select coin: <select id='coin_select'>$coins</select> Select Market: <select id='coin_select2'>$coins2</select>&nbsp;
</div>
<div id='main_results'></div>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<script>
$(function()
{
        $('#coin_select').change(function(event)
        {
                var symbol = $('#coin_select').val();
                var market = $('#coin_select2').val();
                window.location.href = '/site/MyHistoryAtomicDEX?symbol='+symbol+'&market='+market;
        });
        $('#coin_select2').change(function(event)
        {
                var symbol = $('#coin_select').val();
                var market = $('#coin_select2').val();
                window.location.href = '/site/MyHistoryAtomicDEX?symbol='+symbol+'&market='+market;
        });
        main_refresh();
});
var main_delay=3000000;
var main_timeout;
function main_ready(data)
{
        $('#main_results').html(data);
        main_timeout = setTimeout(main_refresh, main_delay);
}
function main_error()
{
        main_timeout = setTimeout(main_refresh, main_delay*2);
}
function main_refresh()
{
        var symbol = $('#coin_select').val();
        var market = $('#coin_select2').val();
end;
if (isset($_GET['newuuid']))
                echo 'var url = "/site/MyHistoryAtomicDEX_results?symbol="+symbol+"&market="+market+"&newuuid='.$newuuid.'";';
//      var newuuid = ""+"$newuuid";
//      if ("$newuuid" == "")
else
                echo 'var url = "/site/MyHistoryAtomicDEX_results?symbol="+symbol+"&market="+market;';
//      else
//              var url = "/site/OrderbookAtomicDEX_results?symbol="+symbol+"&market="+market+"&newcuuid="+newuuid";
echo <<<end
        clearTimeout(main_timeout);
        $.get(url, '', main_ready).error(main_error);
}

</script>
end;
