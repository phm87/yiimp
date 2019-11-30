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
        $ob = atomicdex_api_query('orderbook', array('base' => $symbol, 'rel' => $market));
        if(!$coin) return;
}
$adr_symbol = getdbosql('db_markets', "coinid={$coin->id} AND name = 'AtomicDEX' AND NOT deleted ORDER BY disabled, priority DESC, price DESC");
$adr_market = getdbosql('db_markets', "coinid={$coinm->id} AND name = 'AtomicDEX' AND NOT deleted ORDER BY disabled, priority DESC, price DESC");

echo <<<end
<script>

function showSwapAmountDialog(method, sell, buy, amount, price)
{
        var priceE = document.getElementById("input_sell_price");
        var amountE = document.getElementById("input_sell_amount");
        var amountcurE = document.getElementById("amountcur");
        var pricecurE = document.getElementById("pricecur");
        $("#dlgaddr").html(buy);
        amountE.value = amount;
        priceE.value = price;
        $("#amountcur").html(sell);
        $("#pricecur").html(sell+'/'+buy);
        $("#swap-amount-dialog").dialog(
        {
        autoOpen: true,
                width: 400,
                height: 300,
                modal: true,
                title: 'Buy '+buy+': Swap '+sell+' to '+buy,
                buttons:
                {
                        "Swap": function()
                        {
                                amount = $('#input_sell_amount').val();
                                price = $('#input_sell_price').val();

                                if (method == "buy")
                                        window.location.href = '/site/swap?method='+method+'&amount='+amount+'&price='+price+'&buy='+buy+'&sell='+sell;
                                else
                                        window.location.href = '/site/swap?method='+method+'&amount='+amount+'&price='+price+'&buy='+sell+'&sell='+buy;
                        },
                }
        });
        return false;
}
</script>
<div id="swap-amount-dialog" style="display: none;">
<br>
<!--Address: <span id="dlgaddr">xxxxxxxxxxxx</span><br><br>-->
<h3>My balances</h3>
end;
if (isset($adr_symbol->balance))
        echo "$symbol $adr_symbol->balance (locked by swaps = $adr_symbol->ontrade)<br />";
if (isset($adr_market->balance))
        echo "$market $adr_market->balance (locked by swaps = $adr_market->ontrade)<br />";

echo <<<end
Amount: <input type=text id="input_sell_amount" value="1"><span style="margin-left: 5px;" id="amountcur">xxxxxxxxxxxx</span><br />
Price: <input type=text id="input_sell_price" value="1"><span style="margin-left: 5px;" id="pricecur">xxxxxxxxxxxx</span>
<br />
</div>
<h1>Orderbooks of $symbol/$market on AtomicDEX</h1>
<a href="/site/MyHistoryAtomicDEX?symbol=$symbol&market=$market">My recent swaps</a>
<a href="/site/MyOrdersAtomicDEX?symbol=$symbol&market=$market">My orders</a>
<div align="right" style="margin-top: -20px; margin-bottom: 6px;">
<input class="search" type="search" data-column="all" style="width: 140px;margin-top: -20px; margin-bottom: 6px;" placeholder="Search..." />
</div>
<style type="text/css">
.red { color: darkred; }
tr.ssrow.filtered { display: none; }
</style>
<table>
<tr>
<td>
<h2>My balances</h2>
</td>
<td>
<div style='border: 1px solid black; margin: 2px; padding: 2px;'>
end;
if (isset($adr_symbol->balance))
        echo "$symbol $adr_symbol->balance (locked by swaps = $adr_symbol->ontrade)<br />";
if (isset($adr_market->balance))
        echo "$market $adr_market->balance (locked by swaps = $adr_market->ontrade)<br />";
echo <<<end
</div>
</td>
<td>
<a href="/site/cancel_all_orders">Cancel all orders</a>
</td>
<td>
end;

if (isset($_GET['newuuid']))    { // isset($_GET['newuuid']))   {
echo <<<end
<div style='border: 1px solid darkgreen;background-color: lightgreen;margin: 3px; padding: 2px;'>
Order created: $newuuid
</div>
end;
        }
echo <<<end
</td>
</tr>
</table>
end;
showTableSorter('maintable', "{
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
<th data-sorter="numeric">UID</th>
<th data-sorter="text">Ask/Bid</th>
<th data-sorter="text">Coin</th>
<th data-sorter="text">Address</th>
<th data-sorter="numeric">Price ($market/$symbol)</th>
<th data-sorter="numeric" align="right">Max volume</th>
<th data-sorter="text" align="right">pubkey</th>
<th data-sorter="numeric" align="right">age</th>
<th data-sorter="false" align="right" class="actions" width="150">Actions</th>
</tr>
</thead><tbody>
end;
foreach($ob->asks as $o)
{
        if (isset($adr_symbol->deposit_address))
                if ($o->address == $adr_symbol->deposit_address)
                        echo '<tr style="background-color: #e0d3e8;" class="ssrow">';
                else
                        echo '<tr class="ssrow">';
        else
                echo '<tr class="ssrow">';
        echo '<td width="24"></td>';
        echo '<td width="16">Ask</td>';
        echo '<td width="48"><b>'.$o->coin.'</b></td>';
        echo '<td>'.$o->address.'</td>';
        echo '<td data="'.$o->price.'" title="'.$o->price.'">'.(floor($o->price * 100000000)/100000000).'</td>';
        echo '<td align=right title="'.$o->maxvolume.'">'.(floor($o->maxvolume * 10000)/10000).'</td>';
        echo '<td align=right>'.$o->pubkey.'</td>';
        echo '<td align=right>'.$o->age.'</td>';
        echo '<td class="actions" align="right">';
        if (isset($adr_symbol->deposit_address) && $o->address != $adr_symbol->deposit_address)
        echo '<a href="javascript:showSwapAmountDialog(\'buy\', \''.$symbol.'\', \''.$market.'\', \''.$o->maxvolume.'\', \''.$o->price.'\')">Buy '.$sym$

        echo '</td>';
        echo '</tr>';
}

foreach($ob->bids as $o)
{
        if (isset($market_symbol->deposit_address) && $o->address == $adr_symbol->deposit_address)
                echo '<tr style="background-color: #e0d3e8;" class="ssrow">';
        else
                echo '<tr class="ssrow">';
        echo '<td width="24"></td>';
        echo '<td width="16">Bid</td>';
        echo '<td width="48"><b>'.$o->coin.'</b></td>';
        echo '<td>'.$o->address.'</td>';
        echo '<td data="'.$o->price.'" title="'.$o->price.'">'.(floor($o->price * 100000000)/100000000).'</td>';
        echo '<td align=right title="'.$o->maxvolume.'">'.(floor($o->maxvolume * 10000)/10000).'</td>';
        echo '<td align=right>'.$o->pubkey.'</td>';
        echo '<td align=right>'.$o->age.'</td>';
        echo '<td class="actions" align="right">';
        if (isset($adr_symbol->deposit_address) && $o->address != $adr_symbol->deposit_address)
        echo '<a href="javascript:showSwapAmountDialog(\'sell\',\''.$symbol.'\', \''.$market.'\', \''.$o->maxvolume.'\', \''.$o->price.'\')">Sell '.$sy$
        echo '</td>';
        echo '</tr>';
}
echo "</tbody>";
// totals colspan
echo "</table><br />";
echo '<div style="background-color: #e0d3e8;padding:2px;" class="ssrow">My orders are colored</div>';

echo <<<end
<h2>$symbol prices on CEX</h2>
<div id="markets" style="width:30%;">
<table class="dataGrid">
<thead><tr>
<th width="100">Market</th>
<th width="100">Bid (BTC)</th>
<th width="100">Ask (BTC)</th>
</tr></thead><tbody>
end;
$list = getdbolist('db_markets', "coinid={$coin->id} AND NOT deleted ORDER BY disabled, priority DESC, price DESC");
$bestmarket = getBestMarket($coin);
foreach($list as $market)
{
        $marketurl = '#';
        $price = bitcoinvaluetoa($market->price);
        $price2 = bitcoinvaluetoa($market->price2);
        $marketurl = getMarketUrl($coin, $market->name);
        $rowclass = 'ssrow';
        if($bestmarket && $market->id == $bestmarket->id) $rowclass .= ' bestmarket';
        if($market->disabled) $rowclass .= ' disabled';
        echo '<tr class="'.$rowclass.'">';
        echo '<td><b><a href="'.$marketurl.'" target=_blank>';
        echo $market->name;
        echo '</a></b></td>';
        $updated = "last updated: ".strip_tags(datetoa2($market->pricetime));
        echo '<td title="'.$updated.'">'.$price.'</td>';
        echo '<td title="'.$updated.'">'.$price2.'</td>';
        echo '</td>';
        echo "</tr>";
}

echo "</tbody></table></div>";

