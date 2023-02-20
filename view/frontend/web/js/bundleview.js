define([
"jquery",
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('preorder.bundleview', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
				var bchilds = self.options.bchilds;
				var isbuilderproduct = self.options.isbuilderproduct;
				var use_fabric_stock = self.options.use_fabric_stock;
				var fabric_sku = self.options.fabric_sku;
				var fabric_qty = self.options.fabric_qty;
				var fabric_stock_msg = self.options.fabric_stock_msg;

				if (isbuilderproduct == 1)
				{
					if(use_fabric_stock == 1)
					{
						$("#product_addtocart_form" ).append(fabric_stock_msg);
						/*
						if (fabric_qty <= 0 ) {
							$("#bundleSummary .box-tocart" ).text('');
						} 
						*/
						$.each(bchilds , function (key, childItem) {
							var url = childItem.url;
							var payHtml = childItem.payHtml;
							var msg = childItem.msg;
							var flag = childItem.flag;
							var mptoHtml = childItem.mptoHtml;
							var preoHtml = childItem.preoHtml;
							var pType = childItem.pType;
							var preorderQty = childItem.preorderQty;
							var productId = childItem.productId;
							var addToCartButtonLabel = $("#product-addtocart-button span").text();
							var stockLabel = $(".product-info-stock-sku .stock").text();
							var preOrderLabel = childItem.preOrderLabel;
							var outOfStockLabel = childItem.outOfStockLabel;
							var parentClass = childItem.parentClass;
							var childClass = childItem.childClass;
							var pcClass = "." + parentClass + " ." + childClass;
							//msg = msg.replace(/\n/g, "<br />");
							var count = 0;
							var isPreorder = flag;
							var boxHtml = $(".box-tocart");
							
							var chilSku = childItem.selection_product_sku;
							if( chilSku == fabric_sku )
							{
								if (isPreorder == 1 && pType == '') {
									if (preorderQty > 0 ) {
										setPreOrderLabel(preOrderLabel);
										$("#product_addtocart_form" ).append(preoHtml);
										$(".product-info-stock-sku").text('');
										$(".bundle-option-" + childItem.option_id ).after(msg);
									} else {
										$("#product_addtocart_form .box-tocart").replaceWith( "<span class='wk-date-title'>" + outOfStockLabel + "</span>" );
										$("#product_addtocart_form" ).append('');
										$(".product-info-stock-sku").text('');
									}
								}
								
								if (pType == 'mto') {
									setPreOrderLabel(preOrderLabel);
									$("#product_addtocart_form" ).append(mptoHtml);
									$(".product-info-stock-sku").text('');
									$(".bundle-option-" + childItem.option_id ).after(msg);
								 }
								
								if (pType == 'pto') {
									setPreOrderLabel(preOrderLabel);
									$("#product_addtocart_form" ).append(mptoHtml);
									$(".product-info-stock-sku").text('');
									$(".bundle-option-" + childItem.option_id ).after(msg);
								}
								
								$('#product-addtocart-button').click(function () {
									count = 0;
								 });
								 
								$('#product-addtocart-button span').bind("DOMSubtreeModified",function () {
									var title = $(this).text();
									if (isPreorder == 1 && pType == '') {
										if (title == addToCartButtonLabel) {
											count++;
											if (count == 1) {
												setPreOrderLabel(preOrderLabel);
											}
										}
									}
									
									if (pType == 'mto') {
										if (title == addToCartButtonLabel) {
											count++;
											if (count == 1) {
												setPreOrderLabel(preOrderLabel);
											}
										}
									 }
									
									if (pType == 'pto') {
										if (title == addToCartButtonLabel) {
											count++;
											if (count == 1) {
												setPreOrderLabel(preOrderLabel);
											}
										}
									}
									
								});
							} 
						});	
					}
				} else {
					$.each(bchilds , function (key, childItem) {
						var url = childItem.url;
						var payHtml = childItem.payHtml;
						var msg = childItem.msg;
						var flag = childItem.flag;
						var mptoHtml = childItem.mptoHtml;
						var preoHtml = childItem.preoHtml;
						var pType = childItem.pType;
						var preorderQty = childItem.preorderQty;
						var productId = childItem.productId;
						var addToCartButtonLabel = $("#product-addtocart-button span").text();
						var stockLabel = $(".product-info-stock-sku .stock").text();
						var preOrderLabel = childItem.preOrderLabel;
						var outOfStockLabel = childItem.outOfStockLabel;
						var parentClass = childItem.parentClass;
						var childClass = childItem.childClass;
						var pcClass = "." + parentClass + " ." + childClass;
						//msg = msg.replace(/\n/g, "<br />");
						var count = 0;
						var isPreorder = flag;
						var boxHtml = $(".box-tocart");
						if (isPreorder == 1 && pType == '') {
							if (preorderQty > 0 ) {
								setPreOrderLabel(preOrderLabel);
								$("#product_addtocart_form" ).append(preoHtml);
								$(".product-info-stock-sku").text('');
								$(".bundle-option-" + childItem.option_id ).after(msg);
							} else {
								$("#product_addtocart_form .box-tocart").replaceWith( "<span class='wk-date-title'>" + outOfStockLabel + "</span>" );
								$("#product_addtocart_form" ).append('');
								$(".product-info-stock-sku").text('');
							}
						}
						
						if (pType == 'mto') {
							setPreOrderLabel(preOrderLabel);
							$("#product_addtocart_form" ).append(mptoHtml);
							$(".product-info-stock-sku").text('');
							$(".bundle-option-" + childItem.option_id ).after(msg);
						 }
						
						if (pType == 'pto') {
							setPreOrderLabel(preOrderLabel);
							$("#product_addtocart_form" ).append(mptoHtml);
							$(".product-info-stock-sku").text('');
							$(".bundle-option-" + childItem.option_id ).after(msg);
						}
						
						$('#product-addtocart-button').click(function () {
							count = 0;
						 });
						 
						$('#product-addtocart-button span').bind("DOMSubtreeModified",function () {
							var title = $(this).text();
							if (isPreorder == 1 && pType == '') {
								if (title == addToCartButtonLabel) {
									count++;
									if (count == 1) {
										setPreOrderLabel(preOrderLabel);
									}
								}
							}
							
							if (pType == 'mto') {
								if (title == addToCartButtonLabel) {
									count++;
									if (count == 1) {
										setPreOrderLabel(preOrderLabel);
									}
								}
							 }
							
							if (pType == 'pto') {
								if (title == addToCartButtonLabel) {
									count++;
									if (count == 1) {
										setPreOrderLabel(preOrderLabel);
									}
								}
							}
							
						});
						 
					});
					
				}
		
                function setPreOrderLabel(preOrderLabel)
				{
                    $("#product-addtocart-button").attr("title",preOrderLabel);
                    $("#product-addtocart-button span").text(preOrderLabel);
                    $(".product-info-stock-sku .stock").text(preOrderLabel);
                }

                function setDefaultLabel()
				{
                    $("#product-addtocart-button").attr("title",addToCartButtonLabel);
                    $("#product-addtocart-button span").text(addToCartButtonLabel);
                    $(".product-info-stock-sku .stock").text(stockLabel);
                }

                function setOutOfStockLabel()
				{
                    $(".product-info-stock-sku .stock").text(outOfStockLabel);
                }

                function manageLabel(data)
				{
                    if (data.preorder == 1) {
                        setPreOrderLabel();
                    } else {
						if (pType == 'mto') {
							setPreOrderLabel();
						} else if (pType == 'pto') {
							setPreOrderLabel();
						} else {
							if (data.stock.is_in_stock == 1) {
								setDefaultLabel();
							} else {
								setOutOfStockLabel();
							}
						}
                    }
                }

                function removeBox()
				{
                    $(".box-tocart").remove();
                }

                function addBox()
				{
                    $(".product-options-bottom").append(boxHtml);
                }

                function manageAddToCart(data)
				{
                    removeBox();
                    if (data.preorder == 1) {
                        addBox();
                    } else {
                        if (data.stock.is_in_stock == 1) {
                            addBox();
                        }
                    }
                }
            });
        }
    });
    return $.preorder.bundleview;
});