
$(window).load(function(){

var drawme = "svg/AI/tufted-titmouse.svg";
drawme = "svg/AI/all-girls-end-surprised.svg";
drawme = "svg/AI/no.svg";

// AN OBJECT FOR MANIPULATING THE SVG:
var drawing_dat = function( fname, $el ) {
var $svg_drawn = $el;
var drawn_stats = {'pts_loaded':0,'pts_drawn':0,'paths_loaded':0,'paths_drawn':0,'pts_in_last_path':0};
var $svg;

var $paths_drawn; // = $svg_drawn.find('path');
var $paths_loaded; // = $svg.find('path');

var statnp = 0
var paths_requested = 2;
var first_get_xhr;
var secret_message = "wrong";
var secret_code = "HaHa";
var key;
var iv;

// CONSTRUCTION (kinda like constructor. On instantiation do the following:
first_get_xhr = $.get('getsvgcrypt.php',{'svg':fname},function(dat) {
	// and, once loaded, do this with it:
	$svg = $(dat);
	statnp = parseInt($svg.find("statistics np").text());
	iv = $svg.find("crypto iv").text();

	$("#iv").text(iv);

	annotate_paths($svg,false); // false, keep empty paths so they get into the namespace on load
	$svg_drawn.html($svg.clone());
	annotate_paths($svg,true); // true, remove empty paths (decrypt will replace them)
	// start with pts_drawn = 0;


	$svg_drawn.find('cipher').remove();
	$svg_drawn.find('statistics').remove();
	$svg_drawn.find('crypto').remove();
	$paths_drawn = $svg_drawn.find('path');
	$paths_loaded = $svg.find('path');

	statnp = $paths_loaded.length;
	$paths_drawn.each(function(o,i){
		$(this).attr('d',"").attr('data-pts',0);
	});

});
// END CONSTRUCTION

decrypt_division_with_key = function( key ) {
	var clt = 'clear text';
	var $ciphers = $svg.find("cipher");

// <cipher id="cipher-'.$division_zb.'">

	if ($ciphers.length == 0) return false;

	var $first_cipher = $ciphers.first();
	var crt = $first_cipher.text();

	iv64 = sjcl.codec.base64.fromBits(sjcl.codec.hex.toBits(iv));
	adata =  "copyright_2014_Roberta_Bennett"; 
	js = '{"iv":"' + iv64 + '","mode":"ccm","cipher":"aes","adata":"' + adata + 
			'","ct":"' + crt + '"}';
	try {
		clt = sjcl.decrypt(sjcl.codec.hex.toBits(key), js);
		$svg.append(clt);
		annotate_paths($svg,false);
		$first_cipher.remove();
		return true;
	} catch(e) {
		return false;
	}
}


this.decrypt_next_division = function() {
	var key = $svg.find("cipher-key").first().text();
	if (decrypt_division_with_key(key) ) {
		$svg.find("cipher-key").first().remove();
		return true;
	} else {
		return false;
	}
}



annotate_paths = function($svg_el, remove_empty) {
	var clearout = (typeof remove_empty =='undefined' )?false:remove_empty;
	$svg_el.find("path").each(function(i,o){
		var pl = split_d( $(this).attr('d') ).length;
		$(this).attr('data-pts',pl);	
		if ((clearout) && (pl==0)) $(this).remove();
	});
}

split_d = function(d_string) {
	if (typeof(d_string) != 'string') {
		// console.log('not a string:');
		return [];
	}
	var dary = d_string.match(/[a-zA-Z][^a-zA-Z]*/g);
	return (dary==null)?[]:dary;
};


truncate_d = function(d,n) {
	var dary = splid_d(d);
	var trary = splice(dary,0,n);
	return trary.join('');
};


this.draw_test = function() {
	first_get_xhr.done(function() {
		$svg_drawn.html($svg.clone());
	});
};


draw_another_point = function() {
	if (statnp == 0) { return true; } // wait for first download

	$paths_drawn = $svg_drawn.find('path');
	$paths_loaded = $svg.find('path');
	statnp = $paths_loaded.length;

	$loaded = $($paths_loaded.get(drawn_stats.paths_drawn));
	$drawing = $($paths_drawn.get(drawn_stats.paths_drawn));
	var pts_this_drawn = parseInt($drawing.attr('data-pts'));

	var pts = split_d( $loaded.attr('d') );

	var curp = $drawing.attr('d');

	$drawing.attr('d',curp+pts[ pts_this_drawn] ).attr('data-pts',pts_this_drawn+1);
	pts_this_drawn +=1;

	if (pts_this_drawn >= pts.length) {
		$drawing.attr('data-drawn',1);
		drawn_stats.paths_drawn +=1;
	}
	
	drawn_stats.pts_drawn += 1;

	if (drawn_stats.paths_drawn >= statnp) return false;
	return true;
};

droing = function() {
	if (draw_another_point()) {
	 window.setTimeout(droing,55);
	}
}

this.dralp = function() {
	first_get_xhr.done(function() {
		droing();
	});
}

};// END DRAWING_DAT DEFS


var draw_da = new drawing_dat( drawme, $("#svg_contents") );



$(".testbut").click(function(e){
	$(this).preventDefault = true;
	var confno = parseInt($(this).attr('data-confirm'));
	switch( confno ) {
		case 0 : 
			draw_da.dralp();
		break;
		case 1 : 
			draw_da.decrypt_next_division();
			draw_da.dralp();
		break;
	}

	return false;
});


function BlockMan( ) {
var blocks = [];
var $blockchain = $("#blockchain");
var $blocktrain = $blockchain.find('#block_train');
sameindex = function( res ) {
	var same = false;
	$.each(blocks,function(i,o) {
		if (o.x.blockIndex == res.x.blockIndex ) {
			same = true;
			return false;
console.log('same???');
		}
	});
	return same;
};

this.add_graphic_block = function(res) {
	var $new_car = $('<div class="block_car">'+res.x.blockIndex+'</div>');
	$blocktrain.append($new_car);
}

this.addBlock = function ( res ) {
console.log('add block'+res.x.blockIndex);
	if (!sameindex(res))	{
		blocks.push(res);
console.log('yes, not a dup');
		this.add_graphic_block(res);
	}
}

};


var blockManager = new BlockMan( );

function showTransactions( info ) {
	$("#transactions").text(info);
}
 

// showBlockchain( "the blockchain div");
showTransactions("the transactions div");

var wsUri = "ws://ws.blockchain.info/inv";
var confirmations = 0;
var latest_transaction;


function initSocket( req, openActions, closeActions ) {
    try {
      websocket = new WebSocket(wsUri);
    } catch(e) {
      $("#output").text('Wesocket failed. Old iPad, maybe?');
      return false;
    }

    websocket.onopen = function(evt) { 
	$("#output").text('opened, sending'+req);
	websocket.send(req);
	switch (typeof openActions ) {
		case 'function' :
			openActions();
		break;
		case 'string' :
			websocket.send(openActions);
		break;
		case 'object' :
			if ($.isArray(openActions) )
				$.each(openActions,function(i,o) { websocket.send( o ); });
		break;
	}

    };

    websocket.onclose = function(evt) { 
	onClose(evt);
console.log(typeof closeActions);
	switch (typeof closeActions ) {
		case 'function' :
			closeActions();
		break;
		case 'string' :
			websocket.send(closeActions);
	}
    };
    websocket.onmessage = function(evt) { onMessage(evt) };
    websocket.onerror = function(evt) { onError(evt) };
}


  function onClose(evt)
  {
   // $("#errors").text(evt.toString());
    console.log(evt);
    $("#output").text("closed:"+evt.reason+" code:"+evt.code);
  }

  function onMessage(evt)
  {
    var  reply = JSON.parse(evt.data);
    var unconad;
    var tx_index;
    var message = "";
    switch ( reply.op ) {
	case 'block' :
		tx_index = parseInt($("#watchbut").attr('data-index'));
		message = 'searching for '+tx_index;
		if (confirmations>0) confirmations +=1;
		if ($.inArray(tx_index,reply.x.txIndexes) != -1) {
			message = 'found';
			confirmations +=1;
			draw_da.decrypt_next_division();
			draw_da.dralp();
		}
		blockManager.addBlock( reply );
	break;
	case 'utx' : 
		message = 'unconfirmeds';
		unconad = reply.x.out[0].addr;
		tx_index = reply.x.tx_index;
		latest_transaction = reply;
        	$("#watchbut").attr('data-watch',unconad).attr('data-index', tx_index).text(unconad);
		showTransactions( evt.data);
	break;
    }
    $("#output").html('<span style="color: blue;">'+ message +' : </span>');
  }

  function onError(evt)
  {
    $("#output").html('<span style="color: red;">ERROR:</span> ' + evt.data);
    websocket.close();
  }

$("#watchbut").click(function(e){
     websocket.close();
	setTimeout(function(){    
 	initSocket('{"op":"blocks_sub"}',null,function() {  
          if (confirmations < 4 ) initSocket('{"op":"blocks_sub"}');
         });
	},500);
     return false;
});

initSocket('{"op":"ping_block"}', [ '{"op":"blocks_sub"}','{"op":"unconfirmed_sub"}' ]); 

/****
blocks_sub:
RESPONSE: {"op":"block","x":{"txIndexes":[114231222,114231237,...114174872],"nTx":256,"totalBTCSent":218353079497,"estimatedBTCSent":4361627497,"reward":2503892793,"size":82358,"blockIndex":471815,"prevBlockIndex":471814,"height":288456,"hash":"0000000000000000f2829bb3ce981035da3474b09e6f51cc31b49fa82971d0a0","mrklRoot":"4c126a5d7cb5351c0105abf390767554bd861b8d681033fa30d9a7cb6a5e42ac","version":2,"time":1393687371,"bits":419504166,"nonce":1757605296,"foundBy":{"ip":"178.203.5.197","description":"178.203.5.197","link":"/ip-address/178.203.5.197","time":1393687426}}}



{
    "op": "utx",
    "x": {
        "hash": "f6c51463ea867ce58588fec2a77e9056046657b984fd28b1482912cdadd16374",
        "ver": 1,
        "vin_sz": 4,
        "vout_sz": 2,
        "lock_time": "Unavailable",
        "size": 796,
        "relayed_by": "209.15.238.250",
        "tx_index": 3187820,
        "time": 1331300839,
        "inputs": [
            {
                "prev_out": {
                    "value": 10 000 000,
                    "type": 0,
                    "addr": "12JSirdrJnQ8QWUaGZGiBPBYD19LxSPXho"
                }
            }
        ],
        "out": [
            {
                "value": 2800 000 000,
                "type": 0,
                "addr": "1FzzMfNt46cBeS41r6WHDH1iqxSyzmxChw"
            }
        ]
    }
}
consider using these class in the svg for manipulating the prelude:
<circle class="prelude prelude_element"cx='1' cy='0' r='3' fill='red' >
<animateMotion class="prelude animation"

****/
});
