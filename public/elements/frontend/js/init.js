function addToFavTour(tourId, vendorId) {
    var url = window.location.href.split("?")[0];
    var actUrl = base64Encode(url+'?vid='+vendorId);
    //var tgtUrl = getUrl('tour/addtofav/' + vendorId + '/' + tourId + '?acturl=' + actUrl);
    var tgtUrl = getUrl('tour/addtofav/' + tourId + '?vendor_id='+vendorId+'&acturl=' + actUrl);
    return confirmRedirect(tgtUrl, 'This tour will be added to your favorite list');
}

function addToFavOpt(vendorId) {
    var actUrl = base64Encode(window.location.href);
    var tgtUrl = getUrl('store/addtofav/' + vendorId + '?acturl=' + actUrl);
    return confirmRedirect(tgtUrl, 'This operator will be added to your favorite list');
}


