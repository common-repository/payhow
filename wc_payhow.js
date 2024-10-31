/*
 * Payhow JS Plugin for WooCommerce
 * Don't be ashamed of your code
 * Nothing is created, everything is copied
 * Beans with rice!
 * Comes with dad!
 */
 
 console.log(window.Payhow);

function post(url, data, success) {
	
    var params = typeof data == 'string' ? data : Object.keys(data).map(
        function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
    ).join('&');

    var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xhr.open('POST', url);
    xhr.onreadystatechange = function() {  

        if (xhr.status != 200) {
            spinner(false);
        }

        if (xhr.readyState > 3 && xhr.status == 200) {
            success(xhr.responseText);
        }
    };
	
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(params);

    return xhr;
}
function spinner (show = true) {
	
    var loader = document.querySelector('.payhow-loader');
    loader.style.display = show ? 'block' : 'none';
}

function init () {

	
	jQuery(".woocommerce-cart .cart-collaterals .shipping").css("display","none");
	
	jQuery(document).on('click', '[name="update_cart"]' , function() {
            setInterval(function(){
				jQuery(".woocommerce-cart .cart-collaterals .shipping").remove();
			},3000);
    });
	
	jQuery(document).on('click', '.cart_item .product-remove' , function() {
            setInterval(function(){
				jQuery(".woocommerce-cart .cart-collaterals .shipping").remove();
			},3000);
    });	

    var currentPage = window.Payhow.page;
	
	if(currentPage == "checkout"){
		spinner();
		if(readCookie("wp_user_logged_in") != "null"){
			
			post('https://api.prod.payhow.com.br/api/v1/ecommerce/woocommerce/cart', JSON.stringify(window.Payhow), function (response) {
					var data = JSON.parse(response);	
					
					if (currentPage == 'checkout' && data.checkout_url != '') {
						console.log(window.Payhow);
						window.location.href = data.checkout_url;
					} else {
						window.location.href = window.Payhow.shop_url+"/404";
					}
					
					


				})	
			}else{
				window.location.href = window.Payhow.myaccount_url;
			}
	}

    
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

init(); //start routines
