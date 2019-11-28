<?php
/////////////////////////////////////////////////////////////////////////////////////////////////
$symbol = getparam('symbol');
$market = getparam('market');
$coin = null;
if($symbol == 'all')
{
	echo "Please select a coin";
	return;
}
else
{
	$coin = getdbosql('db_coins', "symbol=:symbol", array(':symbol'=>$symbol));
	if(!$coin) return;
	$coinm = getdbosql('db_coins', "symbol=:symbol", array(':symbol'=>$market));
        if(!$coinm) return;
	if ($symbol == $market)	{
		echo "Please select a coin that is different from market ($symbol/$market doesn't exist).";
		return;
	}
	$ob = atomicdex_api_query('orderbook', array('base' => $symbol, 'rel' => $market));
	if(!$coin) return;
}
echo <<<end
<script>

function showSwapAmountDialog(symbol, market, amount, price)
{
//alert();
        var priceE = document.getElementById("input_sell_price");
        var amountE = document.getElementById("input_sell_amount");
        $("#dlgaddr").html(symbol);
        amountE.value = amount;
        priceE.value = price;
        $("#swap-amount-dialog").dialog(
        {
        autoOpen: true,
                width: 400,
                height: 240,
                modal: true,
                title: 'Buy '+market+': Swap '+symbol+' to '+market,
                buttons:
                {
                        "Send / Sell": function()
                        {
                                amount = $('#input_sell_amount').val();
                                price = $('#input_sell_price').val();
                                window.location.href = '/market/sellto2?id=AtomicDEX&amount='+amount+'&price='+price;
                        },
                }
        });
        return false;
}
</script>
<div id="swap-amount-dialog" style="display: none;">
<br>
<!--Address: <span id="dlgaddr">xxxxxxxxxxxx</span><br><br>-->
Amount: <input type=text id="input_sell_amount" value="1"><br>
Price: <input type=text id="input_sell_price" value="1">
<br>
</div>
<h1>Orderbooks of $symbol/$market on AtomicDEX</h1>
<div align="right" style="margin-top: -20px; margin-bottom: 6px;">
<input class="search" type="search" data-column="all" style="width: 140px;" placeholder="Search..." />
</div>
<style type="text/css">
.red { color: darkred; }
tr.ssrow.filtered { display: none; }
</style>
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
// ,{"coin":"BTC","address":"199mE9XndxUR76kVmiPe3x3dQHKFrxZjW1","price":"0.00007272727272727272727272727272727272727272727272727272727272727272727272727272727272727272727272727273"
//,"price_rat":[[1,[1]],[1,[13750]]],"maxvolume":"0.008","max_volume_rat":[[1,[1]],[1,[125]]]
//,"pubkey":"38b307bd44fabdbd26919871e39b212366ecd16cd9e29ac4b6c323d6a3138817","age":8,"zcredits":0}],"netid":9999,"numasks":8,"numbids":3,"rel":"BTC","timestamp":1574617411}
echo <<<end
<thead>
<tr>
<th data-sorter="numeric">UID</th>
<th data-sorter="text">Ask/Bid</th>
<th data-sorter="text">Coin</th>
<th data-sorter="text">Address</th>
<th data-sorter="numeric">Price</th>
<th data-sorter="numeric" align="right">Max volume</th>
<th data-sorter="text" align="right">pubkey</th>
<th data-sorter="numeric" align="right">age</th>
<th data-sorter="false" align="right" class="actions" width="150">Actions</th>
</tr>
</thead><tbody>
end;

foreach($ob->asks as $o)
{
	
	echo '<tr class="ssrow">';
	echo '<td width="24"></td>';
	echo '<td width="16">Ask</td>';
	echo '<td width="48"><b>'.$o->coin.'</b></td>';
	echo '<td>'.$o->address.'</td>';
	echo '<td data="'.$o->price.'">'.$o->price.'</td>';
	echo '<td align=right>'.$o->maxvolume.'</td>';
	echo '<td align=right>'.$o->pubkey.'</td>';
	echo '<td align=right>'.$o->age.'</td>';

	echo '<td class="actions" align="right">';
	echo '<a href="javascript:showSwapAmountDialog(\''.$market.'\', \''.$symbol.'\', \''.$o->maxvolume.'\', \''.$o->price.'\')">Buy '.$symbol.'</a>';
	//echo '<a href="/site/banuser?id='.$user->id.'"><span class="red">BAN</span></a>';
	echo '</td>';
	echo '</tr>';
}
foreach($ob->bids as $o)
{
	
	echo '<tr class="ssrow">';
	echo '<td width="24"></td>';
	echo '<td width="16">Bid</td>';
	echo '<td width="48"><b>'.$o->coin.'</b></td>';
	echo '<td>'.$o->address.'</td>';
	echo '<td data="'.$o->price.'">'.$o->price.'</td>';
	echo '<td align=right>'.$o->maxvolume.'</td>';
	echo '<td align=right>'.$o->pubkey.'</td>';
	echo '<td align=right>'.$o->age.'</td>';

	echo '<td class="actions" align="right">';
	echo '<a href="javascript:showSwapAmountDialog(\''.$symbol.'\', \''.$market.'\', \''.$o->maxvolume.'\', \''.$o->price.'\')">Sell '.$symbol.'</a>';
	//echo '<a href="/site/banuser?id='.$user->id.'"><span class="red">BAN</span></a>';
	echo '</td>';
	echo '</tr>';
}
echo "</tbody>";
// totals colspan

echo "</table>";
//echo "<p><a href='/site/bonususers'>1% bonus</a></p>";

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
