<?php

$algo = user()->getState('yaamp-algo');

JavascriptFile("/extensions/jqplot/jquery.jqplot.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.dateAxisRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.barRenderer.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.highlighter.js");
JavascriptFile("/extensions/jqplot/plugins/jqplot.cursor.js");
JavascriptFile('/yaamp/ui/js/auto_refresh.js');

function ln_parser($invoice)
// I don't run LND + BTC full node so I used a website to convert the LN invoice to payment_hash
{

        $api_host = 'https://lndecodeapi.slamtrade.com/decode';
        $params = array('invoice' => $invoice);
        $query = http_build_query($params);
        $body = "";
        $body = json_encode($params);
        $req = "POST";

        $headers = array(
                'Accept: application/json, text/plain, */ *',
                'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_host);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; LN Parser API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        curl_setopt($ch, CURLOPT_ENCODING , 'application/json');
        $res = curl_exec($ch);
        if($res === false) {
                $e = curl_error($ch);
                debuglog("$e");
                curl_close($ch);
                return false;
        }
        $result = json_decode($res);
        if(!is_object($result) && !is_array($result)) {
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                debuglog("failed ($status) ".strip_data($res));
        }
        curl_close($ch);
        return $result;
// ,"tags":[{"tagName":"payment_hash","data":"6048ad681228111c2b92d0b7ee048418cfdba8f598235717cef406e8d6cd6c2f"},{"
}

function generate_swap_address_submarineswaplike($timelock, $pub_0, $secret_hash, $hash160_1)   {
https://github.com/submarineswaps/swaps-service/blob/master/docs/chain_swap_script.md
        $out = "76"; //OP_DUP
        $out .= "a914"; //OP_HASH160
//  # this is the hash in the invoice Bob sent to Alice
//  e61b3856d9bd9fa6fbda05e2b8b23c233d0d1b19
        $out .= $secret_hash;
        $out .= "87"; //OP_EQUAL
        $out .= "63"; // OP_IF
        $out .= "7521"; //  OP_DROP
//    # Alice's pubkey, first branch from above
        $out .= $pub_0; //    024727ccb84a422147672cbe753cb21d60...
        $out .= "6703"; // OP_ELSE
//      $out .= $(printf '%08x\n' $timelock | sed 's/^\(00\)*//');
$old_path = getcwd();
chdir('/var/www/web/yaamp/modules/site');
$output = shell_exec('./int2lehex.sh '.$timelock);
chdir($old_path);
echo "little indian hex: ".$output.'<br />';
        $out .= preg_replace('/[^a-e0-9]/', '', $output); // pack('v', $timelock); //"0016BD80"; //$timelock; //  # wait until block 1490304
//  1490304
//echo pack('v',$timelock)."<br />".encodeHex($timelock)."<br />";
        $out .= "b1"; //  OP_CHECKLOCKTIMEVERIFY
        $out .= "75"; //  OP_DROP
//  # and then refund to Bob, second branch from above
        $out .= "76"; //  OP_DUP
        $out .= "a914"; //  OP_HASH160
        $out .= $hash160_1; //     fb5ae6f8db1ce86021250e8b7f7f0c5f1b80c647
        $out .= "88"; //  OP_EQUALVERIFY
        $out .= "68"; // OP_ENDIF
        $out .= "ac"; // OP_CHECKSIG
        return str_replace(' ', '', $out);
}

function generate_swap_address_mm2like($timelock, $pub_0, $secret_hash, $pub_1) {
// https://github.com/KomodoPlatform/atomicDEX-API/blob/mm2/mm2src/coins/utxo.rs#L309
$out = "63";
$out .= pack ('v', $timelock);
// Return the memory representation of this integer as a byte array in little-endian byte order.
$out .= "b1";
$out .= "75";
$out .= $pub_0;
$out .= "ac";
$out .= "67";
$out .= "82";
// $out .= "32 bytes ?
$out .= "88";
$out .= "a9";
$out .= $secret_hash;
$out .= "88";
$out .= $pub_1;
$out .= "ac";
$out .= "68";
return $out;
}

function paymenthash_from_invoice($invoice)     {
//val Success(invoice) = LnInvoice.fromString("lntb50u1pw2ccvqpp5n9rqyqg73hat5zcyqhy062vzmn50xdnft4cd454le0nn32vtcddqdqqcqzpgxqyz5vq4g47trzjl92w3pjv0yvle76kv3rmkt$
//val paymentHash = invoice.lnTags.paymentHash.hash.hex
}

function script_to_address($script)     {
/*
script hash = RIPEMD160(SHA256(script))
The resulting "script hash" is encoded with Base58Check with a version prefix of 5, which results in an encoded address starting with a 3.
*/
$out = hash('ripemd160', hash('sha256', $script));
return hash160ToAddress($out, '05');
}

function script_serialize($prev_hash, $index, $scriptsig, $value, $scriptpubkey)        {
/*
    :param prev_hash: The id of the transaction from which the output is spent.
    :param index: The place of the output in the list of outputs of this transaction.
    :param scriptsig: The unlocking script.
    :param value: The value IN SATOSHIS to spend from the output
    :param scriptpubkey: The script setting the condition to spend the output we create with this transaction.

script hash = RIPEMD160(SHA256(script))
The resulting "script hash" is encoded with Base58Check with a version prefix of 5, which results in an encoded address starting with a 3.

    rmd160_dict  = {}
    # ZEC/HUSH/etc have 2 prefix bytes, BTC/KMD only have 1
    # NOTE: any changes to this code should be verified against https://dexstats.info/addressconverter.php
    ripemd       = b58decode_check(address).hex()[2*prefix_bytes:]
    net_byte     = prefix + ripemd
    bina         = unhexlify(net_byte)
    sha256a      = sha256(bina).hexdigest()
    binb         = unhexlify(sha256a)
    sha256b      = sha256(binb).hexdigest()
    final        = b58encode(unhexlify(net_byte + sha256b[:8]))
*/
    $tx = '\x01\x00\x00\x00'; // # version
    $tx .= '\x01'; // # input count
    $tx .= $prev_hash; //[::-1]
    $tx .= $index;
    $script_length = len($scriptsig);
//    tx .= script_length.to_bytes(sizeof(script_length), 'big');
    $tx .= $scriptsig;
    $tx .= '\xff\xff\xff\xff'; // # sequence
    $tx .= '\x01'; // # output count
    $tx .= $value;
    $script_length = len($scriptpubkey);
//    tx .= script_length.to_bytes(sizeof(script_length), 'big');
    $tx .= $scriptpubkey;
    $tx .= '\x00\x00\x00\x00'; // # timelock

    return binascii.hexlify($tx);

}

$height = '240px';

$min_payout = floatval(YAAMP_PAYMENTS_MINI);
$min_sunday = $min_payout/10;

$payout_freq = (YAAMP_PAYMENTS_FREQ / 3600)." hours";
?>

<div id='resume_update_button' style='color: #444; background-color: #ffd; border: 1px solid #eea;
	padding: 10px; margin-left: 20px; margin-right: 20px; margin-top: 15px; cursor: pointer; display: none;'
	onclick='auto_page_resume();' align=center>
	<b>Auto refresh is paused - Click to resume</b></div>

<table cellspacing=20 width=100%>
<tr><td valign=top width=50%>

<!--  -->

<div class="main-left-box">
<div class="main-left-title">YII MINING POOLS</div>
<div class="main-left-inner">

<ul>

<li>YiiMP is a pool management solution based on the Yii Framework.</li>
<li>This fork was based on the yaamp source code and is now an open source project.</li>
<li>No registration is required, we do payouts in the currency you mine. Use your wallet address as the username.</li>
<li>&nbsp;</li>
<li>Payouts are made automatically every <?= $payout_freq ?> for all balances above <b><?= $min_payout ?></b>, or <b><?= $min_sunday ?></b> on Sunday.</li>
<li>For some coins, there is an initial delay before the first payout, please wait at least 6 hours before asking for support.</li>
<li>Blocks are distributed proportionally among valid submitted shares.</li>

<br/>

</ul>

<h2>Hush submarine swap Test:</h2>
<?php
if (isset($_GET['invoice']) && isset($_GET['refund']))  {
        echo "Invoice: ".$_GET['invoice'].'<br />';
        $res = ln_parser($_GET['invoice']);
//var_dump($res);
        if (isset($res->tags))  {
                foreach ($res->tags as $t)      {
//var_dump($t);

                        if ($t->tagName == "payment_hash")      {
                                echo "payment hash: ".$t->data.'<br />';
                                $payment_hash = $t->data;
                        }

                }
        }

// from refund address, get the pubkey

$coin = getdbosql('db_coins', "symbol=:symbol", array(':symbol'=>'HUSH'));
$remote = new WalletRPC($coin);
// TODO: adapt to mm2 addresses
$b = $remote->validateaddress($coin->master_wallet);
if(arraySafeVal($b,'isvalid'))  {
//      $refund_pubkey =
        $my_pubkey = arraySafeVal($b,'pubkey');
        }
echo "My pubkey: ".$my_pubkey."<br />";
echo "Refund address: ".$_GET['refund']."<br />";
$refund_hash160 = addressToHash160($_GET['refund']); // TODO: check that it is not only for BTC

$payment_hash = hash("ripemd160", $payment_hash);
echo "<h3>Submarine swap like HTLC</h3>";
echo "Hashed payment_hash: ".$payment_hash."<br />";
$redeemscript = strtolower(generate_swap_address_submarineswaplike($coin->block_height + 100, $my_pubkey, $payment_hash, $refund_hash160));

echo "RedeemScript: ".$redeemscript."<br />";

//      generate_swap_address_mm2like

// script hash = RIPEMD160(SHA256(script))
$script_hash=hash("ripemd160", hash("sha256", $redeemscript));
echo "Script hash: ".$script_hash."<br />";

$hex_scriptPubKey = "a914" . $script_hash . "87";

$check=pack("H*" , "55" . $script_hash);
$check=hash("sha256",hash("sha256",$check ,true));
$check=substr($check,0,8);

// script_to_address
$address = hash160ToAddress(hash("ripemd160", hash("sha256",$hex_scriptPubKey)), "55"); // encodeBase58("85" . $script_hash);
//hash160ToAddress(hash("ripemd160", "85" . $hex_scriptPubKey . $check), "85");
echo "Address: ".$address."<br />";

echo "Address: ".hash160ToAddress(hash("ripemd160", hash("sha256", $redeemscript)), "55")."<br />";
echo "Address: ".hash160ToAddress("a914" . hash("ripemd160", hash("sha256", $redeemscript)) . "87", "55")."<br />";

echo "Address: ".encodeBase58(strtoupper("55" . $script_hash . $check))."<br />";
echo "Address: ".encodeBase58(strtoupper("55" . $script_hash . $check))."<br />";
echo "Address: ".hash160ToAddress(hash("ripemd160", hash("sha256", "a914" . $redeemscript . "87")), "55")."<br />";
/*
echo "Address: ".encodeBase58("55".$script_hash)."<br />";
echo "Address: ".encodeBase58("55".$hex_scriptPubKey)."<br />";
echo "Address: ".encodeBase58("55".hash("ripemd160", hash("sha256", $redeemscript)))."<br />";
echo "Address: ".encodeBase58("55".hash("ripemd160", hash("sha256", "a914" . $redeemscript . "87")))."<br />";
*/
echo "<p>";
echo "If the LN invoice is paid, coins can be redeem with aliceSig(mysig) preImage<br />";
// <aliceSig> <paymentPreimage>
echo "After the refund delay, coins can be refunded with bobSig OP_0";
// <bobSig> OP_0
echo "</p>";
echo hash('ripemd160', $payment_hash).'<br />';
echo hash('ripemd160', "994602011e8dfaba0b0405c8fd2982dce8f336695d70dad2bfcbe738a98bc35a").'<br />';
echo hash('ripemd160', "53ada8e6de01c26ff43040887ba7b22bddce19f8658fd1ba00716ed79d15cd5e").'<br />';

echo pack ('v', "1546288031").'<br />';
echo pack ('V', "1546288031").'<br />';
//echo
}
else    {
?>
<form>
<label for="invoice">LN Invoice</label><br />
<input type="text" name="invoice" id="invoice" /><br />
Enter a <b>Hush</b> P2PKH or P2WPKH Bitcoin address with a private key you control to use for a refund if things go wrong.<br />
<label for="refund">Refund address (leave empty to generate one for you)</label><br />
<input type="text" name="refund" id="refund" /><br />
<input type="submit" />
</form>
<?php
}
?>
</div></div>
<br/>

<!--  -->

<div class="main-left-box">
<div class="main-left-title">STRATUM SERVERS</div>
<div class="main-left-inner">

<ul>

<li>
<p class="main-left-box" style='padding: 3px; font-size: .8em; background-color: #ffffee; font-family: monospace;'>
	-o stratum+tcp://<?= YAAMP_STRATUM_URL ?>:&lt;PORT&gt; -u &lt;WALLET_ADDRESS&gt; [-p &lt;OPTIONS&gt;]</p>
</li>

<?php if (YAAMP_ALLOW_EXCHANGE): ?>
<li>&lt;WALLET_ADDRESS&gt; can be one of any currency we mine or a BTC address.</li>
<?php else: ?>
<li>&lt;WALLET_ADDRESS&gt; should be valid for the currency you mine. <b>DO NOT USE a BTC address here, the auto exchange is disabled</b>!</li>
<?php endif; ?>
<li>As optional password, you can use <b>-p c=&lt;SYMBOL&gt;</b> if yiimp does not set the currency correctly on the Wallet page.</li>
<li>See the "Pool Status" area on the right for PORT numbers. Algorithms without associated coins are disabled.</li>

<br>

</ul>
</div></div><br>

<!--  -->

<div class="main-left-box">
<div class="main-left-title">LINKS</div>
<div class="main-left-inner">

<ul>

<!--<li><b>BitcoinTalk</b> - <a href='https://bitcointalk.org/index.php?topic=508786.0' target=_blank >https://bitcointalk.org/index.php?topic=508786.0</a></li>-->
<!--<li><b>IRC</b> - <a href='http://webchat.freenode.net/?channels=#yiimp' target=_blank >http://webchat.freenode.net/?channels=#yiimp</a></li>-->

<li><b>API</b> - <a href='/site/api'>http://<?= YAAMP_SITE_URL ?>/site/api</a></li>
<li><b>Difficulty</b> - <a href='/site/diff'>http://<?= YAAMP_SITE_URL ?>/site/diff</a></li>
<?php if (YIIMP_PUBLIC_BENCHMARK): ?>
<li><b>Benchmarks</b> - <a href='/site/benchmarks'>http://<?= YAAMP_SITE_URL ?>/site/benchmarks</a></li>
<?php endif; ?>

<?php if (YAAMP_ALLOW_EXCHANGE): ?>
<li><b>Algo Switching</b> - <a href='/site/multialgo'>http://<?= YAAMP_SITE_URL ?>/site/multialgo</a></li>
<?php endif; ?>

<br>

</ul>
</div></div><br>

<!--  -->

<a class="twitter-timeline" href="https://twitter.com/hashtag/YAAMP" data-widget-id="617405893039292417" data-chrome="transparent" height="450px" data-tweet-limit="3" data-aria-polite="polite">Tweets about #YAAMP</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

</td><td valign=top>

<!--  -->

<div id='pool_current_results'>
<br><br><br><br><br><br><br><br><br><br>
</div>

<div id='pool_history_results'>
<br><br><br><br><br><br><br><br><br><br>
</div>

</td></tr></table>

<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>

<script>

function page_refresh()
{
	pool_current_refresh();
	pool_history_refresh();
}

function select_algo(algo)
{
	window.location.href = '/site/algo?algo='+algo+'&r=/';
}

////////////////////////////////////////////////////

function pool_current_ready(data)
{
	$('#pool_current_results').html(data);
}

function pool_current_refresh()
{
	var url = "/site/current_results";
	$.get(url, '', pool_current_ready);
}

////////////////////////////////////////////////////

function pool_history_ready(data)
{
	$('#pool_history_results').html(data);
}

function pool_history_refresh()
{
	var url = "/site/history_results";
	$.get(url, '', pool_history_ready);
}

</script>

