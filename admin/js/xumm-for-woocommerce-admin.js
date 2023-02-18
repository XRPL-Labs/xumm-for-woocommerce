(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    function setIssuer() {
        const IOU = $("#woocommerce_xumm_currencies option:selected").val()

        const curated_assets = xumm_object.details

        let arr = []

        for (const issuer in curated_assets) {
            var list = curated_assets[issuer].currencies

            if (list !== undefined) {
                arr.push(issuer)
            }
        }

        var i = 0
        $("#woocommerce_xumm_issuers option").each( (index, elem) => {
            var val = $(elem).text()

            //Disable input if Currency is not available with issuer else enable
            if ( !arr.includes(val) ) {
                $(elem).prop('disabled', 'disabled').removeAttr('selected')
                $(elem).prop("selected", false).removeAttr('selected').change()
            } else {
                $(elem).prop('disabled', false)
            }
            i++
        })

    }

    function disableIssuers() {
        const IOU = $("#woocommerce_xumm_currencies").children(":selected").attr("value")
        if(IOU == 'XRP'){
            $("#woocommerce_xumm_issuers").parent().prop( "disabled", true );
            return
        } else {
            $("#woocommerce_xumm_issuers").parent().prop( "disabled", false );
        }
        let list = []

        for(let exchange in xumm_object.details) {
            exchange = xumm_object.details[exchange]
            if (exchange.currencies !== undefined) {
                for (const currencyId in exchange.currencies)
                {
                    const issuer = exchange.currencies[currencyId];

                    if (issuer.currency != IOU) continue;

                    list.push({
                        name: exchange.name,
                        issuer: issuer.issuer
                    })
                }
            }
        }

        $("#woocommerce_xumm_issuers option").each( (index, elem) => {
            $(elem).attr('disabled', 'disabled').hide()
        })
            $("#woocommerce_xumm_issuers option").each( (index, elem) => {
            var val = $(elem).attr("value")
            list.forEach(obj => {
                if(obj.issuer == val) {
                    $(elem).removeAttr('disabled').show()
                }
            })
        })

    }

    $(document).ready(function () {
        setIssuer()
        disableIssuers()

        var button = document.getElementById("set_trustline");
        $("#woocommerce_xumm_issuers").closest("fieldset").append(button);

        $("#woocommerce_xumm_currencies").change(function() {
            setIssuer()
            disableIssuers()
        })

        $("#woocommerce_xumm_issuers").change(function() {
            trustlineButton();
        })

        $('#set-trustline').click( async e => {
            e.preventDefault()

            let apikey = $("#woocommerce_xumm_api").attr("value")
            let secretkey = $('#woocommerce_xumm_api_secret').attr("value")
            const account = $("#woocommerce_xumm_destination").attr("value")
            const issuer = $("#woocommerce_xumm_issuers").children(":selected").attr("value")
            const currency = $("#woocommerce_xumm_currencies").children(":selected").attr("value")
            const url = 'https://xumm.app/api/v1/platform/payload'

            const option = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': apikey,
                    'X-API-Secret':secretkey
                },
                body: JSON.stringify({
                    "txjson": {
                        "TransactionType": "TrustSet",
                        "Account": account,
                        "Fee": "12",
                        "LimitAmount": {
                          "currency": currency,
                          "issuer": issuer,
                          "value": "999999999"
                        },
                        "Flags": 131072
                    },
                    options: {
                        submit: true,
                        return_url: {
                            web: window.location.href
                        }
                    }
                })
            }
            const response = await fetch(url, option)
        });
    });

})( jQuery );

let issuers = []

function containsObject(obj, arr) {
    for(const index in arr) {
        let test1 = JSON.stringify(arr[index])
        let test2 = JSON.stringify(obj)

        if (test1 == test2) {
            return true
        }
    }
    return false
}

function trustlineButton() {
    var button = document.getElementById("set_trustline");

    const issuer = jQuery("#woocommerce_xumm_issuers option:selected").val();
    const currency = jQuery("#woocommerce_xumm_currencies option:selected").val();

    let obj = {
        account: issuer,
        currency: currency
    }

    if (!containsObject(obj, trustlinesSet)) {
        if (issuer == undefined) {
            button.disabled = true
        } else {
            button.disabled = false
        }
    } else {
        button.disabled = true
    }
}

function trustlineAvailable() {
    const exchanges = xumm_object.details

    for (const exchange in exchanges) {
        const currencies = exchanges[exchange].currencies
        for (const currency in currencies) {
            const issuer = currencies[currency]
            issuers.push(issuer)
        }
    }
}

ws = new WebSocket(xumm_object.ws)

let cmd = {
    "id": 1,
    "command": "account_lines",
    "account": xumm_object.account,
    "ledger_index": "validated"
}

let trustlinesSet = []

ws.onopen = () => {
    console.log('connected to XRPL')
    ws.send(JSON.stringify(cmd))
}

ws.onmessage = (msg) => {
    let data = JSON.parse(msg.data)
    let array = data.result.lines

    array.forEach(line => {
        trustlinesSet.push({account: line.account, currency: line.currency})
    })
    trustlineAvailable()
    trustlineButton()
}

