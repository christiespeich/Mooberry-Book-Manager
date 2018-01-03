
// make Yoast see MBM's custom fields
(function($) {

    var MBDBYoastPlugin = function()
    {
        YoastSEO.app.registerPlugin('MBDBYoastPlugin', {status: 'loading'});

        this.getData();

    };

    MBDBYoastPlugin.prototype.getData = function()
    {

        var _self = this;
        //var $text = $('#acf-field-yoast_text');

        //_self.custom_content = $text.val();

        YoastSEO.app.pluginReady('MBDBYoastPlugin');

        YoastSEO.app.registerModification('content', $.proxy(_self.getCustomContent, _self), 'MBDBYoastPlugin', 5);

    };

    MBDBYoastPlugin.prototype.getCustomContent = function (content)
    {
      //var mceid = $('#acf-yoast_fancyeditor textarea').prop('id');
      return tinymce.editors._mbdb_summary.getContent() + content;
      //  return content;
    };

    $(window).on('YoastSEO:ready', function ()
    {
      new MBDBYoastPlugin();
    });
})(jQuery);