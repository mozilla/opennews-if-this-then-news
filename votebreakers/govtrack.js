var _ = require('underscore')._;
var request = require('request');
var csv = require('csv');
var util = require('util');

var options = {
    delimiter : ',',
    endLine : '\r\n',
    columns : ['id', 'state', 'code', 'result', 'name', 'party'],
    escapeChar : '"',
    enclosedChar : '"'
  };

var handleVote = function(title, votes) {
  votes = _.map(votes, function(v) {
    return {"name": v.name, "group": v.party, "vote": v.result};
  });
  // @Annabel -- call function here!
};

var loadCSV = function(csv_url) {
  request.get(csv_url, function(i, e, body) {
    var title = body.split('\n', 1)[0];
    var data = body.substring(body.indexOf('\n')+1);
    csv().from.string(data, options)
      .to.array(function(d) { handleVote(title, d); });
  });
};

var loadPage = function(url) {
  request.get({url: url, json: true}, function(e, r, body) {
    _.each(body.objects, function(o) {
      loadCSV(o.link + "/export/csv");
    });
    if (body.meta.next !== null)
      loadPage("http://www.govtrack.us" + body.meta.next);
  });
};

loadPage('http://www.govtrack.us/api/v1/vote/?limit=500');

