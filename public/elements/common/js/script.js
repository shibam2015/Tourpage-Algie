/* 
 * Common Script
 */
jQuery(function () {
    /**
     * Attaching BootStrap ToolTips Plugins
     * with elements having class "tooltips"
     */
    jQuery('.tooltips').tooltip();
});

/**
 * Clock
 */
var Clock = {
    time: null,
    months: ["January", "February", "March", "April", "May", "June", "July", "August", "Spetember", "October", "November", "December"],
    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Spe", "Oct", "Nov", "Dec"],
    weekDays: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
    weekDaysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
    displayClock: function (element) {
        var self = this;
        function intervalCountdown() {
            self.time = new Date();
            var h = self.time.getHours();
            var m = self.time.getMinutes();
            var s = self.time.getSeconds();
            var ap = 'AM';
            if (h >= 12) {
                if (h > 12) {
                    h = (h - 12);
                }
                ap = 'PM';
            }
            var timeString = "";
            timeString += padInt(h);
            timeString += ":" + padInt(m);
            timeString += ":" + padInt(s);
            timeString += " " + ap;
            jQuery(element).html(timeString);
        }

        if (!this.interval) {
            this.interval = setInterval(intervalCountdown, 1000);
        }
    },
    displayCalender: function (element) {
        this.time = new Date();
        var calenderString = '';
        calenderString += this.weekDaysShort[this.time.getDay()] + ", ";
        calenderString += this.time.getDate();
        calenderString += " " + this.monthsShort[this.time.getMonth()];
        calenderString += " " + this.time.getFullYear();
        jQuery(element).html(calenderString);
    }
};

/**
 * padding with 0
 * @param {type} i
 * @returns {String}
 */
function padInt(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

/**
 * Javascript uid
 * @returns {String}
 */
function generateUid() {
    var d = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
    return uuid;
}

/**
 * Check for valid date
 * @param {string} dateArg
 * @returns {Boolean}
 */
function isDate(dateArg) {
    var t = (dateArg instanceof Date) ? dateArg : (new Date(dateArg));
    return !isNaN(t.valueOf());
}

/**
 * Check for valid date range
 * @param {string} minDate
 * @param {string} maxDate
 * @returns {Boolean}
 */
function isValidRange(minDate, maxDate) {
    return (new Date(minDate) <= new Date(maxDate));
}

/**
 * Get all dates between two dates
 * @param {string} startDt
 * @param {string} endDt
 * @returns {Array}
 */
function betweenDates(startDt, endDt) {
    var error = ((isDate(endDt)) && (isDate(startDt)) && isValidRange(startDt, endDt)) ? false : true;
    var between = [];
    if (!error) {
        var currentDate = new Date(startDt), end = new Date(endDt);
        while (currentDate <= end) {
            var localeDateString = new Date(currentDate).toLocaleDateString("en-US");
            var dateString = localeDateString.split('/');
            between.push(padInt(dateString[0]) + '/' + padInt(dateString[1]) + '/' + dateString[2]);
            currentDate.setDate(currentDate.getDate() + 1);
        }
    }
    return between;
}

/**
 * Number formatting
 * @param {int} num
 * @param {string} ds default "."
 * @param {string} ts default ","
 * @returns {float}
 */
function formatNumber(num, ds, ts) {
    ds = (typeof ds === 'undefined' ? '.' : ds);
    ts = (typeof ts === 'undefined' ? ',' : ts);
    var x = num.toString().split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? ds + (x[1].length === 1 ? x[1] + '0' : x[1]) : ds + '00';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ts + '$2');
    }
    var formatNumber = x1 + x2;
    return formatNumber;
}

/**
 * Currency formatter
 * Format number with prepend currency symbol
 * @param {int} m
 * @param {string} c default "$"
 * @returns {string}
 */
function formatMoney(m, c) {
    c = (typeof c === 'undefined' ? '$' : c);
    return c + formatNumber(m);
}

/**
 * Javascript Url generator
 * @param {string} url
 */
function getUrl(url) {
    return setting.baseUri + '/' + url;
}

/**
 * Javascript Url redirection
 * @param {string} url
 */
function redirectTo(url) {
    if (typeof url !== 'undefined') {
        window.location.href = url;
    }
    return false;
}

/**
 * Javascript redirection with confirmation
 * @param {string} url
 * @param {string} msg
 */
function confirmRedirect(url, msg) {
    if (typeof msg === 'undefined') {
        msg = 'Are you sure?';
    }
    if (confirm(msg)) {
        redirectTo(url);
    }
    return false;
}

/**
 * Javascript base64_encode like PHP base64_encode()
 * @param {string} data
 * @returns {window.unescape|base64Encode.enc|String}
 */
function base64Encode(data) {
    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            enc = '',
            tmp_arr = [];
    if (!data) {
        return data;
    }
    data = unescape(encodeURIComponent(data));
    do {
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);
        bits = o1 << 16 | o2 << 8 | o3;
        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);
    enc = tmp_arr.join('');
    var r = data.length % 3;
    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}

/**
 * Javascript base64_decode like PHP base64_decode()
 * @param {string} data
 * @returns {string}
 */
function base64Decode(data) {
    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = '',
            tmp_arr = [];
    if (!data) {
        return data;
    }
    data += '';
    do {
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));
        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;
        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;
        if (h3 === 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 === 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);
    dec = tmp_arr.join('');
    return decodeURIComponent(escape(dec.replace(/\0+$/, '')));
}

/**
 * Validating Email Address
 * @param {string} email
 * @returns {Boolean}
 */
function isValidEmail(email) {
    var emailRegx = /^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i;
    if (emailRegx.test(email)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Validating Credit/Debid Card Number
 * @param {int} number
 * @param {boolean} checkLuhn
 * @returns {Boolean}
 */
function validateCardNumber(number, checkLuhn) {
    if (typeof checkLuhn === 'undefined') {
        checkLuhn = false;
    }
    var validNumber = false;
    var regex = new RegExp("^[0-9]{16}$");
    var card = {
        Amx: new RegExp("^(?:3[47][0-9]{13})$"),
        Visa: new RegExp("^(?:4[0-9]{12}(?:[0-9]{3})?)$"),
        Master: new RegExp("^(?:5[1-5][0-9]{14})$"),
        Discover: new RegExp("^(?:6(?:011|5[0-9][0-9])[0-9]{12})$"),
        Dinners: new RegExp("^(?:3(?:0[0-5]|[68][0-9])[0-9]{11})$"),
        JCB: new RegExp("^(?:(?:2131|1800|35\d{3})\d{11})$"),
        Meastro: new RegExp("^(?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?$"),
    };
    if (regex.test(number)) {
        if (card.Amx.test(number)) {
            validNumber = true;
        } else {
            if (card.Visa.test(number)) {
                validNumber = true;
            } else {
                if (card.Master.test(number)) {
                    validNumber = true;
                } else {
                    if (card.Discover.test(number)) {
                        validNumber = true;
                    } else {
                        if (card.Dinners.test(number)) {
                            validNumber = true;
                        } else {
                            if (card.JCB.test(number)) {
                                validNumber = true;
                            } else {
                                if (card.Meastro.test(number)) {
                                    validNumber = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (checkLuhn === true) {
            console.log('luhn check');
            if (luhnCheck(number)) {
                validNumber = true;
            }
        }
    }
    return validNumber;
}

/**
 * Implements "Luhn algorithm" to validate Credit/Debid Card Number
 * @param {int} input credit/debid card number
 * @returns {Boolean}
 */
function luhnCheck(input) {
    var sum = 0, alt = false, i = input.length - 1, num;
    if (input.length < 13 || input.length > 19) {
        return false;
    }
    while (i >= 0) {
        num = parseInt(input.charAt(i), 10);
        if (isNaN(num)) {
            return false;
        }
        if (alt) {
            num *= 2;
            if (num > 9) {
                num = (num % 10) + 1;
            }
        }
        alt = !alt;
        sum += num;
        i--;
    }
    return (sum % 10 == 0);
}

