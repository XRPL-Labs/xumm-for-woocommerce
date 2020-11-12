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

jQuery(document).ready(function($) {
    var button = document.getElementById("set_trustline");
    document.querySelector("#woocommerce_xumm_issuers").closest("fieldset").append(button);

    $("#woocommerce_xumm_currencies").change(function() {
        setIssuer()
        dissableIssuers()
    })

    jQuery("#woocommerce_xumm_issuers").change(function() {
        trustlineButton();
    })

    jQuery('#set-trustline').click( async e => {
        e.preventDefault()

        let apikey = jQuery("#woocommerce_xumm_api").attr("value")
        let secretkey = jQuery('#woocommerce_xumm_api_secret').attr("value")
        const account = jQuery("#woocommerce_xumm_destination").attr("value")
        const issuer = jQuery("#woocommerce_xumm_issuers").children(":selected").attr("value")
        const currency = jQuery("#woocommerce_xumm_currencies").children(":selected").attr("value")
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
                      "value": "100"
                    }
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
    })

    setIssuer()
    dissableIssuers()
});

function trustlineButton() {
    var button = document.getElementById("set_trustline");
    const issuer = jQuery("#woocommerce_xumm_issuers").children(":selected").attr("value");
    const currency = jQuery("#woocommerce_xumm_currencies").children(":selected").attr("value");

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

function setIssuer() {
    const IOU = jQuery("#woocommerce_xumm_currencies").children(":selected").attr("value")

    const curated_assets = xumm_object.details

    let arr = []

    for (const issuer in curated_assets) {
        var list = curated_assets[issuer].currencies[IOU]
        if (list !== undefined) {
            arr.push(issuer)
        }
    }

    var i = 0
    jQuery("#woocommerce_xumm_issuers option").each( (index, elem) => {
        var val = jQuery(elem).text()
        
        //Disable input if Currency is not available with issuer else enable
        if ( !arr.includes(val) ) {
            jQuery(elem).prop('disabled', 'disabled').removeAttr('selected')
            jQuery(elem).prop("selected", false).removeAttr('selected').change()
        } else {
            jQuery(elem).prop('disabled', false)
        }
        i++
    })

}

function dissableIssuers() {
    const IOU = jQuery("#woocommerce_xumm_currencies").children(":selected").attr("value")
    if(IOU == 'XRP'){
        jQuery("#woocommerce_xumm_issuers").parent().prop( "disabled", true );
        return
    } else {
        jQuery("#woocommerce_xumm_issuers").parent().prop( "disabled", false );
    }
    let list = []

    for(let exchange in xumm_object.details) {
        exchange = xumm_object.details[exchange]
        if (exchange.currencies[IOU]!== undefined) {
            let avail = exchange.currencies[IOU].issuer
            list.push({
                name: exchange.name,
                issuer: avail
            })

        }
    }

    jQuery("#woocommerce_xumm_issuers option").each( (index, elem) => {
        jQuery(elem).attr('disabled', 'disabled').hide()
    })
        jQuery("#woocommerce_xumm_issuers option").each( (index, elem) => {
        var val = jQuery(elem).attr("value")
        list.forEach(obj => {
            if(obj.issuer == val) {
                jQuery(elem).removeAttr('disabled').show()
            }
        })
    })

}


ws = new WebSocket('wss://xrpl.ws')

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

let issuers = []


function trustlineAvailable() {
    const exchanges = xumm_object.details

    for (const exchange in exchanges) {
        const currencies = exchanges[exchange].currencies
        for (const currency in currencies) {
            const issuer = currencies[currency].issuer
            issuers.push(issuer)
        }
    }
}
