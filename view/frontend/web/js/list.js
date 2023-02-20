define([
"jquery",
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('preorder.list', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var preorderInfo = self.options.preorderInfo;
                var payHtml = self.options.payHtml;
                var showMsgBox = self.options.showMsgBox;
                var count = 0;
                var isPreorder = 0;
                var isMadeToOrder = 0;
                var isPrintToOrder = 0;
                var preOrderLabel = self.options.preOrderLabel;
                var mtoOrderLabel = self.options.mtoOrderLabel;
                var ptoOrderLabel = self.options.ptoOrderLabel;
                var outOfStockLabel = self.options.outOfStockLabel;
                $(".products ol.product-items > li.product-item").each(function () 
				{
                    var productLink = $(this).find(".product-item-link").attr("href");

					/*  check if key exists   */ 
					var hasKey1 = preorderInfo.hasOwnProperty(productLink);
					if(hasKey1) 
					{
						var person = preorderInfo[productLink];
						
						/* --------- bsitc check for PRE Order  ----------------*/
						var hasPreorderKey = person.hasOwnProperty('preorder');
						if(hasPreorderKey) {
							if (preorderInfo[productLink]['preorder'] == 1) {
								if (preorderInfo[productLink]['preorderQty'] > 0 ) {
									setPreOrderLabel($(this));
									if (showMsgBox == 1) {
										$(this).find(".price-box").after(payHtml);
									}
								} else {
									setOutOfStockLabel($(this));
								}
							}
						}
						/* --------- bsitc check for MTO ----------------*/
						var hasMtoKey = person.hasOwnProperty('mto');
						if(hasMtoKey) {
							if (preorderInfo[productLink]['mto'] == 1) {
								setMtoOrderLabel($(this));
							}
						}
						/* --------- bsitc check for PTO ----------------*/
						var hasMtoKey = person.hasOwnProperty('pto');
						if(hasMtoKey) {
							if (preorderInfo[productLink]['pto'] == 1) {
								setPtoOrderLabel($(this));
							}
						}			
						/* --------- end bsitc update code for MTO and PTO ----------------*/						
					} else {
							console.log('The key does not exist.');
							console.log(productLink);
					}
					
					/*
                    if (preorderInfo[productLink]['preorder'] == 1) {
						if (preorderInfo[productLink]['preorderQty'] > 0 ) {
 							setPreOrderLabel($(this));
							if (showMsgBox == 1) {
								$(this).find(".price-box").after(payHtml);
							}
						} else {
 							setOutOfStockLabel($(this));
						}
                    }
					// --------- bsitc update code for MTO and PTO ---------------- 
                    if (preorderInfo[productLink]['mto'] == 1) {
 						 setMtoOrderLabel($(this));
                       }
					
                    if (preorderInfo[productLink]['pto'] == 1) {
 						setPtoOrderLabel($(this));
                      }
					// --------- bsitc update code for MTO and PTO ---------------- 
					
					
					
					*/


                });
				
                $(".products-grid.wishlist ol.product-items > li.product-item").each(function () {
                    var productLink = $(this).find(".product-item-link").attr("href");
                    if (preorderInfo[productLink]['preorder'] == 1) {
                        setPreOrderLabel($(this));
                        if (showMsgBox == 1) {
                            $(this).find(".price-box").after(payHtml);
                        }
                    }

					/* --------- bsitc update code for MTO and PTO ----------------*/
                    if (preorderInfo[productLink]['mto'] == 1) {
						 setMtoOrderLabel($(this));
                     }
                    if (preorderInfo[productLink]['pto'] == 1) {
						setPtoOrderLabel($(this));
                     }
					/* --------- bsitc update code for MTO and PTO ----------------*/

                });
                $("#product-comparison > tbody").each(function () {
                    var productLink = $(this).find(".product-item-name > a").attr("href");
                    if (preorderInfo[productLink]['preorder'] == 1) {
                        setPreOrderLabel($(this));
                        if (showMsgBox == 1) {
                            $(this).find(".price-box").after(payHtml);
                        }
                    }
					/* --------- bsitc update code for MTO and PTO ----------------*/
                    if (preorderInfo[productLink]['mto'] == 1) {
						 setMtoOrderLabel($(this));
                     }
                    if (preorderInfo[productLink]['pto'] == 1) {
						setPtoOrderLabel($(this));
                     }
					/* --------- bsitc update code for MTO and PTO ----------------*/
 					
                });
                $('.action.tocart').click(function () {
                    var url = $(this).parents(".product-item-info").find(".product-item-link").attr("href");
                    isPreorder = preorderInfo[url]['preorder'];
                    isMadeToOrder = preorderInfo[url]['mto'];
                    isPrintToOrder = preorderInfo[url]['pto'];
                    count = 0;
                });
                $('.action.tocart span').bind("DOMSubtreeModified",function () {
                    var title = $(this).text();
                    if (isPreorder == 1 ) {
                        if (title == "Add to Cart") {
                            count++;
                            if (count == 1) {
                                 $(this).parent().attr("title", preOrderLabel);
                                $(this).text(preOrderLabel);
                             }
                        }
                    }

                    if (isMadeToOrder == 1 ) {
                        if (title == "Add to Cart") {
                            count++;
                            if (count == 1) {
                                $(this).parent().attr("title", mtoOrderLabel);
                                $(this).text(mtoOrderLabel);
                             }
                        }
                    }

                    if (isPrintToOrder == 1 ) {
                        if (title == "Add to Cart") {
                            count++;
                            if (count == 1) {
                                $(this).parent().attr("title", ptoOrderLabel);
                                $(this).text(ptoOrderLabel);
                             }
                        }
                    }

                });
                function setPreOrderLabel(currentObject)
                {
                    currentObject.find(".action.tocart.primary").attr("title",preOrderLabel);
                    currentObject.find(".action.tocart.primary").find("span").text(preOrderLabel);
                }
                function setMtoOrderLabel(currentObject)
                {
                    currentObject.find(".action.tocart.primary").attr("title",mtoOrderLabel);
                    currentObject.find(".action.tocart.primary").find("span").text(mtoOrderLabel);
                }
                function setPtoOrderLabel(currentObject)
                {
                    currentObject.find(".action.tocart.primary").attr("title",ptoOrderLabel);
                    currentObject.find(".action.tocart.primary").find("span").text(ptoOrderLabel);
                }
				
				function setOutOfStockLabel(currentObject)
				{
					currentObject.find(".action.tocart.primary").replaceWith( "<div class='stock unavailable'><span>" + outOfStockLabel + "</span></div>" );
  				}

            });
        }
    });
    return $.preorder.list;
});

