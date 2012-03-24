jQuery(function($,undefined){
    var $content = $('#content');
    var $toc = $('#toc');
    var $toc_nav = $('<ul/>',{
        id: 'toc-nav',
        'class': 'nav pull-right'
    }).appendTo($('#nav').parent());
    
    var $dropdown = $('<ul/>',{
        'class': "dropdown-menu"
    })
    
    $toc_nav.append(
        $('<li/>',{
            'class' : 'dropdown'
        }).html(
            '<a class="dropdown-toggle" data-toggle="dropdown" href="#">Table of contents <b class="caret"></b></a>'
            ).append($dropdown)
        );
    
    var getDocUrl = function($header){
        return $header.next('ul').find('li:first > a')[0].href;
    }
    
    $toc.hide().find('h3').each(function(){
        $h3 = $(this);
        $dropdown.append(
            $('<li/>',{
                //                'class' : 'dropdown'
                }).html(
                '<a href="#">' + $h3.text() + ' <b class="caret"></b></a>'
                //                '<a class="dropdown-toggle" data-toggle="dropdown" href="' + getDocUrl($h3) + '">' + $h3.text() + ' <b class="caret"></b></a>'
                ).append($(this).next('ul').addClass("dropdown-menu").find('ul').addClass("dropdown-menu").end())
            );
    });
    
    $toc.remove();
    
    $('.dropdown-toggle',$toc_nav).dropdown();
    
    
    $content.removeClass('span9').addClass('span12');
    $('h2:first',$content).replaceWith('<hr/>');
    $('a[name]',$content).each(function(){
        $(this).attr('id',this.name);
        $(this).removeAttr('name');
    });
    
    $('ul:first',$content).addClass('nav nav-pills').wrap('<div class="subnav"></div>');
    
    $('.subnav').scrollspy({
        'target': '#content',
        'offset': 0
    });
    
    // fix sub nav on scroll
    var $win = $(window)
    , $subnav = $('.subnav')
    , subNavTop = $('.subnav').length && $('.subnav').offset().top  - 40
    , isFixed = 0

    processScroll();

    $win.on('scroll', processScroll)

    function processScroll() {
        var i, scrollTop = $win.scrollTop()
        if (scrollTop >= subNavTop && !isFixed) {
            isFixed = 1
            $subnav.addClass('subnav-fixed')
        } else if (scrollTop <= subNavTop && isFixed) {
            isFixed = 0
            $subnav.removeClass('subnav-fixed')
        }
    }
    
    prettyPrint(); 
    $().UItoTop();
    
    $(" #toc-nav ul ").css({
        display: "none"
    }); // Opera Fix
    $(" #toc-nav li").hover(function(){
        $(this).find('ul:first').css({
            visibility: "visible",
            display: "none"
        }).show(400);
    },function(){
        $(this).find('ul:first').css({
            visibility: "hidden"
        });
    });
});

