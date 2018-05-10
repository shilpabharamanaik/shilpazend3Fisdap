$(function () {
    // make the buttonset
    $("#upgrade-mode").buttonset();
    $(".cupertino .ui-button").css('margin', '5px -3px');

    // switch upgrade modes
    $("#upgrade-mode .ui-button").click(function (e) {
        pickMode($(this));
    });

    // apply activation code
    $("#applyCode").button().click(function (e) {
        e.preventDefault();
        $("#invalidCode").slideUp();
        var code = $("#upgradeCode").val();

        if (code != "") {
            blockUi(true);
            $("#codeInfo").slideUp().html("");
            $.post("/account/orders/validate-activation-code", {"code": code}, function (response) {
                if (response.error == true) {
                    // we got an error
                    $("#upgradeCode").val("");
                    $("#upgradeCodeType").val("");
                    $("#invalidCode").html(response.html).slideDown();
                } else {
                    // we got a valid code summary
                    $("#codeInfo").html(response.html).slideDown();
                    $("#upgradeCodeType").val(response.codeType);
                    initApplySerial($("#applySerial"));

                    if (response.codeType == "product") {
                        $("#purchaseInfo").show();
                        updateTotal();
                    }
                }
                blockUi(false);
            }, "json");
        }
    });

    // apply coupon code
    $("#applyCoupon").button().click(function (e) {
        e.preventDefault();
        $("#invalidCoupon").slideUp();
        var code = $("#upgradeCoupon").val();

        if (code != "") {
            blockUi(true, $("#couponContainer"), "no-msg");
            $.post("/account/orders/is-coupon-valid", {"code": code}, function (response) {
                if (response == false) {
                    $("#invalidCoupon").slideDown();
                } else {
                    addCoupon(response);
                }
                $("#upgradeCoupon").val("");
                blockUi(false, $("#couponContainer"));
            }, "json");
        }
    });

    // check/uncheck a checkbox/radio button
    $(".upgrade-table input").change(function () {
        // update the total price
        updateTotal();

        // update the availability of the other products/packages
        updateAvailability($(this));
    });

    // click the "enter coupon" link
    $("a#showCouponInput").click(function (e) {
        e.preventDefault();
        $("#enterCoupon").slideDown();
    });

    // submit the form with the "next" link
    $("a#next-link").click(function (e) {
        e.preventDefault();

        var upgradeConfig = getSelectedConfig();
        // if we don't have a config selected, show the error
        if (upgradeConfig < 1) {
            $("#noneSelectedError").slideDown();
        } else {
            $("#noneSelectedError").slideUp();
            $("#upgradeConfig").val(upgradeConfig);
            $("form").submit();
        }
    });

    function pickMode(button) {
        var mode = $(button).attr('for');
        if (mode == "choose") {
            showChooseTab();
            resetCodeTab();
        } else {
            showCodeTab();
            resetChooseTab();

        }
    }

    function resetChooseTab() {
        // reset the form
        $(".upgrade-table.product input:checked").attr("checked", false);
        $("#package-0").attr("checked", true);
        updateAvailability($("#package-0"));
        resetCouponBox();

        // get a fresh total
        updateTotal();
    }

    function showChooseTab() {
        // show the correct tab
        $("#enter-code").hide();
        $("#choose-products").show();
        $("#purchaseInfo").show();
    }

    function resetCodeTab() {
        // reset the form
        $("#upgradeCode").val("");
        $("#codeInfo").html("");
        resetCouponBox();

        // get a fresh total
        updateTotal();
    }

    function showCodeTab() {
        // show the correct tab
        $("#enter-code").show();
        $("#choose-products").hide();
        $("#purchaseInfo").hide();
    }

    function resetCouponBox() {
        // hide all the coupon stuff
        $("#invalidCoupon").hide();
        $("#couponDescription").hide();
        $("#enterCoupon").hide();

        // reset the coupon values
        $("#upgradeCoupon").val("");
        $("#appliedCoupon").val("");
        $("#appliedCoupon").attr("data-couponConfig", 0);
        $("#appliedCoupon").attr("data-discountPercent", 0);

        // remove the discount
        $(".total-cost-container").removeClass("discounted");
        $(".discount-cost").html("");
        $(".discount-cost-container").css("opacity", 0);
    }

    /**
     * get the configuration of the selected products/packages
     */
    function getSelectedConfig() {
        var config = 0;
        var products = getProducts();

        $(products).each(function () {
            config += parseInt($(this).attr("data-config"));
        });

        return config;
    }

    /**
     * get the selected configuration for the given inputs
     */
    function getInputConfig(inputs) {
        var config = 0;
        $(inputs).each(function () {
            config += parseInt($(this).val());
        });

        return config;
    }

    /**
     * update the total price
     */
    function updateTotal() {
        $(".total-cost-container span.total-cost").html(calculateTotal());

        // if there's a coupon, apply the discount
        var coupon = $("#appliedCoupon");
        if ($(coupon).val() != "") {
            applyDiscount(coupon);
        }
    }

    /**
     * calculate the total price
     */
    function calculateTotal() {
        var total = 0;
        var products = getProducts();

        $(products).each(function () {
            total += parseFloat($(this).attr('data-price'));
        });

        return total.toFixed(2);
    }

    /**
     *
     * @param input
     */
    function updateAvailability(input) {
        // if this is a product we want to update packages
        if ($(input).attr("name") == "products[]") {
            var sectionConfig = getInputConfig($(".upgrade-table.product input:checked"));
            var otherSection = $(".upgrade-table.package input:not(:checked)");
        } else {
            // if this is a package we want to update products
            var sectionConfig = getInputConfig($(".upgrade-table.package input:checked"));
            var otherSection = $(".upgrade-table.product input:not(:checked)");
        }
        // go through all the other section's inputs
        $(otherSection).each(function () {
            // re-enable it first, so we start from scratch
            updateInput($(this), false);

            // disable the ones that overlap this section's configuration
            if (($(this).val() & sectionConfig) > 0) {
                updateInput($(this), true);
            }
        });
    }

    /**
     * enable/disable the input and it's rows
     * @param productConfig
     * @param disable
     */
    function updateInput(input, disable) {
        // find the row(s) this input is in
        var rows = $("tr." + $(input).attr("id") + "-row");

        if (disable) {
            $(input).attr("disabled", "disabled");
            rows.addClass("disabled");
        } else {
            $(input).removeAttr("disabled");
            rows.removeClass("disabled");
        }
    }

    /**
     * Add a coupon to this upgrade
     * @param coupon
     */
    function addCoupon(coupon) {
        var couponMarkup = "<div>" + coupon.description + "</div>";
        $("#couponDescription").html(couponMarkup).show();
        $("#appliedCoupon").val(coupon.code);
        $("#appliedCoupon").attr("data-couponConfig", coupon.configuration);
        $("#appliedCoupon").attr("data-discountPercent", coupon.discount_percent);
        updateTotal();
    }

    function applyDiscount(coupon) {
        // add styling to total price
        $(".total-cost-container").addClass("discounted");

        // see if the coupon config applies to any selected products
        var couponConfig = $(coupon).attr("data-couponconfig");
        var couponPercent = $(coupon).attr("data-discountpercent");
        var discount = 0;

        var products = getProducts();

        $(products).each(function () {
            // if this product is discounted by the coupon, add the discount
            if (($(this).attr('data-config') & couponConfig) > 0) {
                discount += parseFloat($(this).attr('data-price')) * couponPercent * .01;
            }
        });

        // calculate the discounted price
        var discountPrice = (calculateTotal() - discount.toFixed(2)).toFixed(2);

        // show discounted price
        $(".discount-cost").html(discountPrice);
        $(".discount-cost-container").css("opacity", 1);

    }

    function initApplySerial(button) {
        $(button).button();
        imgToSVG("img.icon");

        // submit the form with the "applySerial" button
        $(button).click(function (e) {
            e.preventDefault();

            $("form").submit();
        });
    }

    function getProducts() {
        if ($("input[name=upgrade_mode]:checked").val() == "choose") {
            return $(".upgrade-table input:checked");
        } else {
            return $("#codeInfo tr.product-row.include");
        }
    }

    // onload make sure the forms are reset and we see the correct tab
    if ($("#upgradeCode").val()) {
        $("#upgrade-mode .ui-button[for=code]").trigger("click");
        $("#applyCode").trigger("click");
    } else {
        $("#upgrade-mode .ui-button[for=choose]").trigger("click");
    }


});