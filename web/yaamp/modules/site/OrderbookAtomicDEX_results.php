<?php
/////////////////////////////////////////////////////////////////////////////////////////////////
$symbol = getparam('symbol');
$coin = null;
if($symbol == 'all')
{
	echo "Please select a coin";
return;
	//$users = getdbolist('db_accounts', "balance>.001 OR id IN (SELECT DISTINCT userid FROM workers) ORDER BY balance DESC");
}
else
{
	$coin = getdbosql('db_coins', "symbol=:symbol", array(':symbol'=>$symbol));
	if(!$coin) return;
	$ob = atomicdex_api_query('orderbook', array('coin' => $symbol));
	if(!$coin) return;
}
echo <<<end
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
	//echo '<a href="/site/banuser?id='.$user->id.'"><span class="red">BAN</span></a>';
	echo '</td>';
	echo '</tr>';
}
echo "</tbody>";
// totals colspan

echo "</table>";
//echo "<p><a href='/site/bonususers'>1% bonus</a></p>";
