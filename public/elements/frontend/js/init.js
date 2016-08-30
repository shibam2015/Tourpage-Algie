function addToFavTour(tourId, vendorId) {
    var actUrl = base64Encode(window.location.href);
    //var tgtUrl = getUrl('tour/addtofav/' + vendorId + '/' + tourId + '?acturl=' + actUrl);
    var tgtUrl = getUrl('tour/addtofav/' + tourId + '?acturl=' + actUrl);
    return confirmRedirect(tgtUrl, 'This tour will be added to your favorite list');
}

function addToFavOpt(vendorId) {
    var actUrl = base64Encode(window.location.href);
    var tgtUrl = getUrl('store/addtofav/' + vendorId + '?acturl=' + actUrl);
    return confirmRedirect(tgtUrl, 'This operator will be added to your favorite list');
}


