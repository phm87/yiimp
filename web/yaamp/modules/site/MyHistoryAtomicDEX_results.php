<?php
/////////////////////////////////////////////////////////////////////////////////////////////////
$symbol = getparam('symbol');
$market = getparam('market');
$newuuid= getparam('newuuid');
$coin = null;
if($symbol == 'all')
{
        echo "<h1>Please select a coin<h1>";
        return;
}
else
{
        $coin = getdbosql('db_coins', "symbol=:symbol", array(':symbol'=>$symbol));
        if(!$coin) return;
        $coinm = getdbosql('db_coins', "symbol=:symbol", array(':symbol'=>$market));
        if(!$coinm) return;
        if ($symbol == $market) {
                echo "<h1>Please select a coin that is different from market ($symbol/$market doesn't exist).<h1>";
                return;
        }
        $ob = atomicdex_api_query('my_recent_swaps', array('limit' => 10));
        if(!$coin) return;
}
$adr_symbol = getdbosql('db_markets', "coinid={$coin->id} AND name = 'AtomicDEX' AND NOT deleted ORDER BY disabled, priority DESC, price DESC");
$adr_market = getdbosql('db_markets', "coinid={$coinm->id} AND name = 'AtomicDEX' AND NOT deleted ORDER BY disabled, priority DESC, price DESC");


echo <<<end

<script>
function toogleDetails(id) {
  var x = document.getElementById("details_"+id);
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
$(document).ready(function() {
setTimeout(function(){
        id = 0;
        while (document.getElementById("details_"+id))  {
                toogleDetails(id);
                id++;
                }
}, 1000);
});
</script>
end;

echo "<h1>My recent swaps of $symbol/$market on AtomicDEX</h1>";
echo <<<end
<a href="/site/OrderbookAtomicDEX?symbol=$symbol&market=$market">Orderbook</a>
<a href="/site/MyOrdersAtomicDEX?symbol=$symbol&market=$market">My orders</a>
<div align="right" style="margin-top: -20px; margin-bottom: 6px;">
<input class="search" type="search" data-column="all" style="width: 140px;margin-top: -20px; margin-bottom: 6px;" placeholder="Search..." />
</div>

<style type="text/css">
.red { color: darkred; }
tr.ssrow.filtered { display: none; }
</style>
end;

ShowTableSorter('maintable', "{
        tableClass: 'dataGrid',
        textExtraction: {
                4: function(node, table, cellIndex) { return $(node).attr('data'); },
                6: function(node, table, cellIndex) { return $(node).attr('data'); },
        },
        widgets: ['zebra','filter','Storage','saveSort'],
        widgetOptions: {
                saveSort: true,
                filter_saveFilters: false,
                filter_external: '.search',
                filter_columnFilters: false,
                filter_childRows : true,
                filter_ignoreCase: true
        }
}");

echo <<<end
<thead>
<tr>
<th data-sorter="text">Events</th>
<th data-sorter="text">sucess_events</th>
<th data-sorter="text">error_events</th>
<th data-sorter="text">type</th>
<th data-sorter="text">uuid</th>
<th data-sorter="text">recoverable</th>
<!--<th data-sorter="text"></th>
<th data-sorter="numeric" align="right">age</th>
<th data-sorter="false" align="right" class="actions" width="150">Actions</th>-->
</tr>
</thead><tbody>
end;
//var_dump($ob);
$status_event_success = array();
$status_event_failure = array();

$out = "";
$id = 0;
foreach($ob->result->swaps as $o)
{
//      if ($o->address == $adr_symbol->deposit_address)
//              echo '<tr style="background-color: #e0d3e8;" class="ssrow">';
//      else
        $error = "";
        $out = "";
        foreach ($o->success_events as $s)      {
                array_push($status_event_success, $s);
        }
        foreach ($o->error_events as $s)      {
                array_push($status_event_failure, $s);
        }

//      echo '<tr class="ssrow">';
//      echo '<td width="24" title="';
        foreach ($o->events as $en => $e)       {
                foreach ($e->event as $dn => $d)        {
                        if ($dn == "type")      {
                                foreach ($status_event_failure as $f)
                                        if ($f == $d)
                                                $error .= $d.'
';
                                $out .= "
".$d;
                                }
                        if ($dn == "data")
                        foreach ($d as $xn => $x)       {
//                              if (is_string($x))      $out.=$xn.' => '.$x.'
//';
                                }
//var_dump($d);
//                      if (is_string($d))      echo $d.' ';
//                      if (isset($d->type))    echo $d->type.' ';

                        if (!is_string($d))
                        foreach ($d as $ddn => $dd)     {
                        }
//              echo '<br />';
                }
        }
//      $out .= '">';
        if ($error != "")       {
                echo '<tr class="ssrow" style="background-color: lightsalmon;" onClick="toogleDetails('.$id.');">';
                echo '<td width="24" title="Errors:
';
//              echo $out;
                echo $error;
                echo '">';
        }
        else    {
                echo '<tr class="ssrow"  onClick="toogleDetails('.$id.');">';
                echo '<td width="24" title="Everything went fine:
';
                echo $out;
                echo '">';
        }
//      var_dump($error);
        echo '</td>';
        echo '<td width="16">';
        foreach ($o->success_events as $f)      {
//                echo $f.'<br />';
        }
        echo '</td>';
        echo '<td width="48">';
        foreach ($o->error_events as $g)      {
//                echo $g.'<br />';
        }
        echo '</td>';
        echo '<td>';
        echo $o->type;
        echo '</td>';
        echo '<td>';
        echo $o->uuid;
        echo '</td>';
        echo '<td>';
        echo $o->recoverable;
        echo '</td>';
        echo '</tr>';
        echo '<tr id="details_'.$id.'">';
        echo '<td>';
        echo '<table>';
        foreach ($o->events as $en => $e)       {
                echo '<tr>';
                foreach ($e->event as $dn => $d)        {
                        echo '<td>';
                        if ($dn == "type")      echo $d;
                        if ($dn == "data")
                                foreach ($d as $xn => $x)
                                        if (is_string($x))
                                                if ($xn != "tx_hex")
                                                        if ($xn == "tx_hash")
                                                                switch ($d->coin)       {
                                                                        case 'RICK':
                                                                                echo $xn.': <a href="https://rick.kmd.dev/tx/2a92695960f512960f511b2a5d$
                                                                                break;
                                                                        case 'MORTY':
                                                                                echo 'MORTY TODO';
                                                                                break;
                                                                        default:
                                                                        }
                                                        else
                                                                echo $xn.': '.$x.'<br />';
                        echo '</td>';
                        }
                echo '</tr>';
                }
        echo '</table>';
        echo '</td>';
        echo '</tr>';
$id++;
}
echo "</tbody>";
// totals colspan
echo "</table><br />";

echo "</tbody></table></div>";

