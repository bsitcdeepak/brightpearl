define([
"jquery",
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('preorder.view', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var url = self.options.url;
                var payHtml = self.options.payHtml;
                var msg = self.options.msg;
                var flag = self.options.flag;
                var mptoHtml = self.options.mptoHtml;
                var pType = self.options.pType;
                var preorderQty = self.options.preorderQty;
                var productId = self.options.productId;
                var addToCartButtonLabel = $("#product-addtocart-button-default span").text();
                var stockLabel = $(".product-info-stock-sku .stock").text();
                var preOrderLabel = self.options.preOrderLabel;
                var outOfStockLabel = self.options.outOfStockLabel;
                msg = msg.replace(/\n/g, "<br />");
                var count = 0;
                var isPreorder = flag;
                var boxHtml = $(".box-tocart");
                if (isPreorder == 1 && pType == '') {
					if (preorderQty > 0 ) {
						setPreOrderLabel();
						$( "#product_addtocart_form").append(msg);
						$(".product-info-stock-sku").text('');
					} else {
						$("#product_addtocart_form .box-tocart").replaceWith( "<span class='wk-date-title'>" + outOfStockLabel + "</span>" );
						$( "#product_addtocart_form").append('');
						$(".product-info-stock-sku").text('');
					}
                }
				
                if (pType == 'mto') {
                    setPreOrderLabel();
 					$("#product_addtocart_form").append(mptoHtml);
					$(".product-info-stock-sku").text('');
                 }
				
                if (pType == 'pto') {
                    setPreOrderLabel();
 					$("#product_addtocart_form").append(mptoHtml);
					$(".product-info-stock-sku").text('');
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
                                setPreOrderLabel();
                            }
                        }
                    }
					if (pType == 'mto') {
                        if (title == addToCartButtonLabel) {
                            count++;
                            if (count == 1) {
                                setPreOrderLabel();
                            }
                        }
					 }
					if (pType == 'pto') {
                        if (title == addToCartButtonLabel) {
                            count++;
                            if (count == 1) {
                                setPreOrderLabel();
                            }
                        }
					}
                });
                $('#product-options-wrapper .super-attribute-select').change(function () {
                    $(".wk-msg-box").remove();
                    var flag = 1;
                    setTimeout(function () {
                        $("#product_addtocart_form input[type='hidden']").each(function () {
                            $('#product-options-wrapper .super-attribute-select').each(function () {
                                if ($(this).val() == "") {
                                    flag = 0;
                                }
                            });
                            var name = $(this).attr("name");
                            if (name == "selected_configurable_option") {
                                var productId = $(this).val();
                                if (productId != "" && flag ==1) {
                                    $(".wk-loading-mask").removeClass("wk-display-none");
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        data: { product_id : productId },
                                        dataType: 'json',
                                        success: function (data) {
                                            manageAddToCart(data);
											isPreorder = 0;
											$(".wk-msg-box").remove();
                                            if (data.preorder == 1 && data.pType == '') {
                                                isPreorder = 1;
 												if (data.preorderQty > 0 ) {
													setPreOrderLabel();
 													preOrderLabel = data.preOrderLabel;
 													$("#product_addtocart_form").append(data.msg);
													$(".product-info-stock-sku").text('');
												} else {
													$("#product_addtocart_form .box-tocart").replaceWith("<span class='wk-date-title'>" + data.outOfStockLabel + "</span>");
													$("#product_addtocart_form").append('');
													$(".product-info-stock-sku").text('');
												}
                                            }
											
											if (data.pType == 'pto') {
												addBox();
												pType = data.pType;
												preOrderLabel = data.preOrderLabel;
												setPreOrderLabel();
												$("#product_addtocart_form").append(data.mptoHtml);
												$(".product-info-stock-sku").text('');
											}
											
											if (data.pType == 'mto') {
												addBox();
												pType = data.pType;
												preOrderLabel = data.preOrderLabel;
												setPreOrderLabel();
												$("#product_addtocart_form").append(data.mptoHtml);
												$(".product-info-stock-sku").text('');
 											}
                                            manageLabel(data);
                                            $(".wk-loading-mask").addClass("wk-display-none");
                                        }
                                    });
                                }
                            }
                        });
                    }, 0);
                });
                $('body').on('click', '#product-options-wrapper .swatch-option', function () {
                    var flag = 1;
                    var attributeInfo = {};
                    $(".wk-msg-box").remove();
                    setTimeout(function () {
                        $('#product-options-wrapper .swatch-attribute').each(function () {
                            if ($(this).attr('option-selected')) {
                                var selectedOption = $(this).attr("option-selected");
                                var attributeId = $(this).attr("attribute-id");
                                attributeInfo[attributeId] = selectedOption;
                            } else {
                                flag = 0;
                            }
                        });
                        if (flag == 1) {
                            $(".wk-loading-mask").removeClass("wk-display-none");
                            $.ajax({
                                url: url,
                                type: 'POST',
                                data: { type : 1, product_id : productId, info : attributeInfo },
                                dataType: 'json',
                                success: function (data) {
                                    manageAddToCart(data);
									isPreorder = 0;
									$(".wk-msg-box").remove();
									if (data.preorder == 1 && data.pType == '') {
										isPreorder = 1;
										if (data.preorderQty > 0 ) {
											setPreOrderLabel();
											$("#product_addtocart_form").append(data.msg);
											$(".product-info-stock-sku").text('');
										} else {
											$("#product_addtocart_form .box-tocart").replaceWith("<span class='wk-date-title'>" + data.outOfStockLabel + "</span>");
											$("#product_addtocart_form").append('');
											$(".product-info-stock-sku").text('');
										}
									}
									
									if (data.pType == 'pto') {
										addBox();
										pType = data.pType;
										preOrderLabel = data.preOrderLabel;
										setPreOrderLabel();
										$("#product_addtocart_form").append(data.mptoHtml);
										$(".product-info-stock-sku").text('');
									}
									
									if (data.pType == 'mto') {
										addBox();
										pType = data.pType;
										preOrderLabel = data.preOrderLabel;
										setPreOrderLabel();
										$("#product_addtocart_form").append(data.mptoHtml);
										$(".product-info-stock-sku").text('');
									}
                                    manageLabel(data);
                                    $(".wk-loading-mask").addClass("wk-display-none");
                                }
                            });
                        }
                    }, 0);
                });
 
                function setPreOrderLabel()
                {
                    $("#product-addtocart-button").attr("title",preOrderLabel);
                    $("#product-addtocart-button span").text(preOrderLabel);
                    $(".product-info-stock-sku .stock").text(preOrderLabel);
                }

                function setDefaultLabel()
                {
					var addToCartButtonLabel = $("#product-addtocart-button-default span").text();
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
						if (data.pType == 'mto') {
							setPreOrderLabel();
						} else if (data.pType == 'pto') {
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
				
				function callFirstItemSelectionMsg()
				{
                    
                    var flag = 1;
					$("#product_addtocart_form input[type='hidden']").each(function () {
						$('#product-options-wrapper .super-attribute-select').each(function () {
							if ($(this).val() == "") {
								flag = 0;
							}
						});
						var name = $(this).attr("name");
						if (name == "selected_configurable_option") {
							var productId = $(this).val();
							if (productId != "" && flag ==1) {
								$(".wk-msg-box").remove();
								$(".wk-loading-mask").removeClass("wk-display-none");
								$.ajax({
									url: url,
									type: 'POST',
									data: { product_id : productId },
									dataType: 'json',
									success: function (data) {
										manageAddToCart(data);
										isPreorder = 0;
										if (data.preorder == 1 && data.pType == '') {
											isPreorder = 1;
											if (data.preorderQty > 0 ) {
												setPreOrderLabel();
												preOrderLabel = data.preOrderLabel;
												$("#product_addtocart_form").append(data.msg);
												$(".product-info-stock-sku").text('');
											} else {
												$("#product_addtocart_form .box-tocart").replaceWith("<span class='wk-date-title'>" + data.outOfStockLabel + "</span>");
												$("#product_addtocart_form").append('');
												$(".product-info-stock-sku").text('');
											}
										}
										
										if (data.pType == 'pto') {
											addBox();
											pType = data.pType;
											preOrderLabel = data.preOrderLabel;
											setPreOrderLabel();
											$("#product_addtocart_form").append(data.mptoHtml);
											$(".product-info-stock-sku").text('');
										}
										
										if (data.pType == 'mto') {
											addBox();
											pType = data.pType;
											preOrderLabel = data.preOrderLabel;
											setPreOrderLabel();
											$("#product_addtocart_form").append(data.mptoHtml);
											$(".product-info-stock-sku").text('');
										}
										manageLabel(data);
										$(".wk-loading-mask").addClass("wk-display-none");
									}
								});
							}
						}
					});
				 
				}
				
				setTimeout(function () {
					callFirstItemSelectionMsg();
 				}, 1000);				
				
				
            });
        }
    });
    return $.preorder.view;
});