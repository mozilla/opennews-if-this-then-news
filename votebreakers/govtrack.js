var _ = require('underscore')._;
var request = require('request');

var loadPage = function(url) {
  console.log(url);
  request.get({url: url, json: true}, function(e, r, body) {
    _.each(body.objects, function(o) {
      console.log(o.link);
    });
    if (body.meta.next != null)
      loadPage("http://www.govtrack.us" + body.meta.next);
  });
};

loadPage('http://www.govtrack.us/api/v1/vote/?limit=500');

