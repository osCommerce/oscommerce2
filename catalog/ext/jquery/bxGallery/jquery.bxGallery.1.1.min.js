/*
bxGallery v1.1
Plugin developed by: Steven Wanderski
http://bxgalleryplugin.com
http://stevenwanderski.com

Released under the GPL license:
http://www.gnu.org/licenses/gpl.html
*/

(function($){$.fn.extend({bxGallery:function(options){var defaults={maxwidth:'',maxheight:'',thumbwidth:200,thumbcrop:false,croppercent:.35,thumbplacement:'bottom',thumbcontainer:'',opacity:.7,load_text:'',load_image:'http://i302.photobucket.com/albums/nn92/wandoledzep/spinner.gif',wrapperclass:'outer'}
var options=$.extend(defaults,options);var o=options;var cont='';var caption='';var $outer='';var $orig=this;var tall=0;var wide=0;var showing=0;var i=0;var k=$orig.find('img').size();var current;preload_img();function preload_img(){$orig.hide();if(o.load_text!=''){$orig.before('<div id="loading">'+o.load_text+'</div>');}else{$orig.before('<div id="loading"><img src="'+o.load_image+'" /></div>');}
$orig.parent().find('#loading').css({'textAlign':'center','width':o.maxwidth});$orig.find('img').each(function(){var the_source=$(this).attr('src');var the_img=new Image();the_img.onload=function(){preload_check();};the_img.src=the_source;});}
function preload_check(){i++;if(i==k){init();}}
function init(){set_layout();set_main_img();place_thumbcontainer();set_thumbs();}
function set_layout(){$orig.parent().find('#loading').hide();$orig.show();$orig.wrap('<div class="'+o.wrapperclass+'"></div>');$outer=$orig.parent();$orig.find('li').css({'position':'absolute'});}
function set_main_img(){$orig.find('img').each(function(){var $this=$(this);var $imgheight=$this.height();var $imgwidth=$this.width();if($this.prop('title')!=''){caption=$this.prop('title');$this.parent().append('<div class="caption">'+caption+'</div>');}
if(o.maxwidth!=''){if($this.width()>o.maxwidth){$this.width(o.maxwidth);$this.height(($imgheight/$imgwidth)*o.maxwidth);}}
if(o.maxheight!=''){if($this.height()>o.maxheight){$this.height(o.maxheight);$this.width(($imgwidth/$imgheight)*o.maxheight);}}
var captionheight = 0;if($this.parent().find('.caption').height()>0){captionheight=$this.parent().find('.caption').height();}
if($this.height()+captionheight>tall){tall=$this.height()+captionheight;}
if($this.width()>wide){wide=$this.width();}
cont+='<li><img src="'+$this.prop('src')+'" /></li>';});$orig.find('li:not(:first)').hide();$orig.height(tall);$orig.width(wide);$outer.find('.caption').width(wide);}
function place_thumbcontainer(){if(o.thumbplacement=='top'){$outer.prepend('<ul class="thumbs">'+cont+'</ul>');$outer.find('.thumbs').css({'overflow':'auto'});}else if(o.thumbplacement=='left'){$outer.prepend('<ul class="thumbs">'+cont+'</ul>');$orig.css({'float':'left'});$outer.find('.thumbs').css({'float':'left'});}else if(o.thumbplacement=='bottom'){$outer.append('<ul class="thumbs">'+cont+'</ul>');}else if(o.thumbplacement=='right'){$outer.append('<ul class="thumbs">'+cont+'</ul>');$orig.css({'float':'left'});$outer.find('.thumbs').css({'float':'left'});}
$outer.append('<div style="clear:both"></div>');if(o.thumbcontainer!=''){$outer.find('.thumbs').width(o.thumbcontainer);}}
function set_thumbs(){$outer.find('.thumbs li').each(function(){var $this=$(this);var $img=$this.find('img');var $imgwidth=$img.width();var $imgheight=$img.height();if(o.thumbcrop){$img.width($imgwidth*o.croppercent);$img.height(($imgheight/$imgwidth)*$img.width());$this.css({'float':'left','width':o.thumbwidth,'height':o.thumbwidth,'overflow':'hidden','cursor':'pointer'});}else{$img.width(o.thumbwidth);$img.height(($imgheight/$imgwidth)*o.thumbwidth);$this.css({'float':'left','cursor':'pointer'});$this.height($img.height());}
$this.click(function(){var x=$outer.find('.thumbs li').index($this);if(showing!=x){$orig.find('li').fadeOut();$orig.find('li').eq(x).fadeIn();showing=x;}});});var $thumb=$outer.find('.thumbs li');$thumb.eq(0).addClass('on');$thumb.not('.on').fadeTo(0,o.opacity);$thumb.click(function(){var t=$(this);var i=$thumb.index(this);if(current!=i){$thumb.removeClass('on');t.addClass('on');$thumb.not('.on').fadeTo(200,o.opacity);current=i;}}).hover(function(){$(this).stop().fadeTo(200,1);$(this).click();},function(){$(this).not('.on').stop().fadeTo(200,o.opacity);});}}});})(jQuery);