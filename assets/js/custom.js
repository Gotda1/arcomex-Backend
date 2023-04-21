/*
Template Name: Material Pro Admin
Author: Themedesigner
Email: niravjoshi87@gmail.com
File: js
*/
function init_plugins() {
    $(function () {
        "use strict";

        // Admin Panel settings
        $.fn.AdminSettings = function (settings) {
            var myid = this.attr("id");
            // General option for vertical header 
            var defaults = {
                Theme: true, // this can be true or false ( true means dark and false means light ),
                Layout: 'vertical', // 
                LogoBg: 'skin5', // You can change the Value to be skin5/skin2/skin3/skin4/skin5/skin6 
                NavbarBg: 'skin6', // You can change the Value to be skin5/skin2/skin3/skin4/skin5/skin6 
                SidebarType: 'full', // You can change it full / mini-sidebar
                SidebarColor: 'skin5', // You can change the Value to be skin5/skin2/skin3/skin4/skin5/skin6
                SidebarPosition: false, // it can be true / false
                HeaderPosition: false, // it can be true / false
                BoxedLayout: false, // it can be true / false 
            };
            var settings = $.extend({}, defaults, settings);
            // Attribute functions 
            var AdminSettings = {
                // Settings INIT
                AdminSettingsInit: function () {
                    AdminSettings.ManageTheme();
                    AdminSettings.ManageThemeLayout();
                    AdminSettings.ManageThemeBackground();
                    AdminSettings.ManageSidebarType();
                    AdminSettings.ManageSidebarColor();
                    AdminSettings.ManageSidebarPosition();
                    AdminSettings.ManageBoxedLayout();
                }
                , //****************************
                // ManageThemeLayout functions
                //****************************
                ManageTheme: function () {
                    var themeview = settings.Theme;
                    switch (settings.Layout) {
                        case 'vertical':
                            if (themeview == true) {
                                $('body').attr("data-theme", 'dark');
                                $("#theme-view").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-theme", 'light');
                                $("body").prop("checked", !1);
                            }
                            break;

                        default:
                    }
                }
                , //****************************
                // ManageThemeLayout functions
                //****************************
                ManageThemeLayout: function () {
                    switch (settings.Layout) {
                        case 'horizontal':
                            $('#' + myid).attr("data-layout", "horizontal");                           
                            break;
                        case 'vertical':
                            $('#' + myid).attr("data-layout", "vertical");                            
                            break;
                        default:
                    }
                }
                , //****************************
                // ManageSidebarType functions 
                //****************************
                ManageThemeBackground: function () {
                    // Logo bg attribute
                    function setlogobg() {
                        var lbg = settings.LogoBg;
                        if (lbg != undefined && lbg != "") {
                            $('#' + myid + ' .topbar .top-navbar .navbar-header').attr("data-logobg", lbg);
                        }
                        else {
                            $('#' + myid + ' .topbar .top-navbar .navbar-header').attr("data-logobg", "skin5");
                        }
                    };
                    setlogobg();
                    // Navbar bg attribute
                    function setnavbarbg() {
                        var nbg = settings.NavbarBg;
                        if (nbg != undefined && nbg != "") {
                            $('#' + myid + ' .topbar .navbar-collapse').attr("data-navbarbg", nbg);
                            $('#' + myid + ' .topbar').attr("data-navbarbg", nbg);
                            $('#' + myid).attr("data-navbarbg", nbg);
                        }
                        else {
                            $('#' + myid + ' .topbar .navbar-collapse').attr("data-navbarbg", "skin5");
                            $('#' + myid + ' .topbar').attr("data-navbarbg", "skin5");
                            $('#' + myid).attr("data-navbarbg", "skin5");
                        }
                    };
                    setnavbarbg();
                }
                , //****************************
                // ManageThemeLayout functions
                //****************************
                ManageSidebarType: function () {
                    switch (settings.SidebarType) {
                        //****************************
                        // If the sidebar type has full
                        //****************************     
                        case 'full':
                            $('#' + myid).attr("data-sidebartype", "full");
                            //****************************
                            /* This is for the mini-sidebar if width is less then 1170*/
                            //**************************** 
                            var setsidebartype = function () {
                                var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
                                if (width < 1170) {
                                    $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
                                    $("#main-wrapper").addClass("mini-sidebar");
                                }
                                else {
                                    $("#main-wrapper").attr("data-sidebartype", "full");
                                    $("#main-wrapper").removeClass("mini-sidebar");
                                }
                            };
                            $(window).ready(setsidebartype);
                            $(window).on("resize", setsidebartype);
                            //****************************
                            /* This is for sidebartoggler*/
                            //****************************
                            $('.sidebartoggler').on("click", function () {
                                $("#main-wrapper").toggleClass("mini-sidebar");
                                if ($("#main-wrapper").hasClass("mini-sidebar")) {
                                    $(".sidebartoggler").prop("checked", !0);
                                    $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
                                }
                                else {
                                    $(".sidebartoggler").prop("checked", !1);
                                    $("#main-wrapper").attr("data-sidebartype", "full");
                                }
                            });
                            break;
                        //****************************
                        // If the sidebar type has mini-sidebar
                        //****************************       
                        case 'mini-sidebar':
                            $('#' + myid).attr("data-sidebartype", "mini-sidebar");
                            //****************************
                            /* This is for sidebartoggler*/
                            //****************************
                            $('.sidebartoggler').on("click", function () {
                                $("#main-wrapper").toggleClass("mini-sidebar");
                                if ($("#main-wrapper").hasClass("mini-sidebar")) {
                                    $(".sidebartoggler").prop("checked", !0);
                                    $("#main-wrapper").attr("data-sidebartype", "full");
                                }
                                else {
                                    $(".sidebartoggler").prop("checked", !1);
                                    $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
                                }
                            });
                            break;
                        //****************************
                        // If the sidebar type has iconbar
                        //****************************       
                        case 'iconbar':
                            $('#' + myid).attr("data-sidebartype", "iconbar");
                            //****************************
                            /* This is for the mini-sidebar if width is less then 1170*/
                            //**************************** 
                            var setsidebartype = function () {
                                var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
                                if (width < 1170) {
                                    $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
                                    $("#main-wrapper").addClass("mini-sidebar");
                                }
                                else {
                                    $("#main-wrapper").attr("data-sidebartype", "iconbar");
                                    $("#main-wrapper").removeClass("mini-sidebar");
                                }
                            };
                            $(window).ready(setsidebartype);
                            $(window).on("resize", setsidebartype);
                            //****************************
                            /* This is for sidebartoggler*/
                            //****************************
                            $('.sidebartoggler').on("click", function () {
                                $("#main-wrapper").toggleClass("mini-sidebar");
                                if ($("#main-wrapper").hasClass("mini-sidebar")) {
                                    $(".sidebartoggler").prop("checked", !0);
                                    $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
                                }
                                else {
                                    $(".sidebartoggler").prop("checked", !1);
                                    $("#main-wrapper").attr("data-sidebartype", "iconbar");
                                }
                            });
                            break;
                        //****************************
                        // If the sidebar type has overlay
                        //****************************       
                        case 'overlay':
                            $('#' + myid).attr("data-sidebartype", "overlay");
                            var setsidebartype = function () {
                                var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
                                if (width < 767) {
                                    $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
                                    $("#main-wrapper").addClass("mini-sidebar");
                                }
                                else {
                                    $("#main-wrapper").attr("data-sidebartype", "overlay");
                                    $("#main-wrapper").removeClass("mini-sidebar");
                                }
                            };
                            $(window).ready(setsidebartype);
                            $(window).on("resize", setsidebartype);
                            //****************************
                            /* This is for sidebartoggler*/
                            //****************************
                            $('.sidebartoggler').on("click", function () {
                                $("#main-wrapper").toggleClass("show-sidebar");
                                if ($("#main-wrapper").hasClass("show-sidebar")) {
                                    //$(".sidebartoggler").prop("checked", !0);
                                    //$("#main-wrapper").attr("data-sidebartype","mini-sidebar");
                                }
                                else {
                                    //$(".sidebartoggler").prop("checked", !1);
                                    //$("#main-wrapper").attr("data-sidebartype","iconbar");
                                }
                            });
                            break;
                        default:
                    }
                }
                , //****************************
                // ManageSidebarColor functions 
                //****************************
                ManageSidebarColor: function () {
                    // Logo bg attribute
                    function setsidebarbg() {
                        var sbg = settings.SidebarColor;
                        if (sbg != undefined && sbg != "") {
                            $('#' + myid + ' .left-sidebar').attr("data-sidebarbg", sbg);
                        }
                        else {
                            $('#' + myid + ' .left-sidebar').attr("data-sidebarbg", "skin5");
                        }
                    };
                    setsidebarbg();
                }
                , //****************************
                // ManageSidebarPosition functions
                //****************************
                ManageSidebarPosition: function () {
                    var sidebarposition = settings.SidebarPosition;
                    var headerposition = settings.HeaderPosition;
                    switch (settings.Layout) {
                        case 'vertical':
                            if (sidebarposition == true) {
                                $('#' + myid).attr("data-sidebar-position", 'fixed');
                                $("#sidebar-position").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-sidebar-position", 'absolute');
                                $("#sidebar-position").prop("checked", !1);
                            }
                            if (headerposition == true) {
                                $('#' + myid).attr("data-header-position", 'fixed');
                                $("#header-position").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-header-position", 'relative');
                                $("#header-position").prop("checked", !1);
                            }
                            break;
                        case 'horizontal':
                            if (sidebarposition == true) {
                                $('#' + myid).attr("data-sidebar-position", 'fixed');
                                $("#sidebar-position").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-sidebar-position", 'absolute');
                                $("#sidebar-position").prop("checked", !1);
                            }
                            if (headerposition == true) {
                                $('#' + myid).attr("data-header-position", 'fixed');
                                $("#header-position").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-header-position", 'relative');
                                $("#header-position").prop("checked", !1);
                            }
                            break;
                        default:
                    }
                }
                , //****************************
                // ManageBoxedLayout functions
                //****************************
                ManageBoxedLayout: function () {
                    var boxedlayout = settings.BoxedLayout;
                    switch (settings.Layout) {
                        case 'vertical':
                            if (boxedlayout == true) {
                                $('#' + myid).attr("data-boxed-layout", 'boxed');
                                $("#boxed-layout").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-boxed-layout", 'full');
                                $("#boxed-layout").prop("checked", !1);
                            }
                            break;
                        case 'horizontal':
                            if (boxedlayout == true) {
                                $('#' + myid).attr("data-boxed-layout", 'boxed');
                                $("#boxed-layout").prop("checked", !0);
                            }
                            else {
                                $('#' + myid).attr("data-boxed-layout", 'full');
                                $("#boxed-layout").prop("checked", !1);
                            }
                            break;
                        default:
                    }
                }
                ,
            };
            AdminSettings.AdminSettingsInit();
        };



        $("#main-wrapper").AdminSettings({
            Theme: false, // this can be true or false ( true means dark and false means light ),
            Layout: 'horizontal',
            LogoBg: 'skin5', // You can change the Value to be skin5/skin2/skin3/skin4/skin5/skin6 
            NavbarBg: 'skin5', // You can change the Value to be skin5/skin2/skin3/skin4/skin5/skin6
            SidebarType: 'full', // You can change it full / mini-sidebar / iconbar / overlay
            SidebarColor: 'skin6', // You can change the Value to be skin5/skin2/skin3/skin4/skin5/skin6
            SidebarPosition: true, // it can be true / false ( true means Fixed and false means absolute )
            HeaderPosition: true, // it can be true / false ( true means Fixed and false means absolute )
            BoxedLayout: true, // it can be true / false ( true means Boxed and false means Fluid ) 
        });


        var url = window.location + "";
        var path = url.replace(window.location.protocol + "//" + window.location.host + "/", "");
        var element = $('ul#sidebarnav a').filter(function () {
            return this.href === url || this.href === path;// || url.href.indexOf(this.href) === 0;
        });
        element.parentsUntil(".sidebar-nav").each(function (index) {
            if ($(this).is("li") && $(this).children("a").length !== 0) {
                $(this).children("a").addClass("active");
                $(this).parent("ul#sidebarnav").length === 0
                    ? $(this).addClass("active")
                    : $(this).addClass("selected");
            }
            else if (!$(this).is("ul") && $(this).children("a").length === 0) {
                $(this).addClass("selected");

            }
            else if ($(this).is("ul")) {
                $(this).addClass('in');
            }

        });

        element.addClass("active");
        $('#sidebarnav a').on('click', function (e) {

            if (!$(this).hasClass("active")) {
                // hide any open menus and remove all other classes
                $("ul", $(this).parents("ul:first")).removeClass("in");
                $("a", $(this).parents("ul:first")).removeClass("active");

                // open our new menu and add the open class
                $(this).next("ul").addClass("in");
                $(this).addClass("active");

            }
            else if ($(this).hasClass("active")) {
                $(this).removeClass("active");
                $(this).parents("ul:first").removeClass("active");
                $(this).next("ul").removeClass("in");
            }
        })
        $('#sidebarnav >li >a.has-arrow').on('click', function (e) {
            e.preventDefault();
        });

        // Auto scroll to the active nav
        if ($(window).width() > 768 || window.Touch) {
            $('.scroll-sidebar').animate({
                scrollTop: ( $("#sidebarnav .sidebar-item").length > 0 ? 
                             $("#sidebarnav .sidebar-item").offset().top - 250 : 0)
            }, 500);
        }

        $(".preloader").fadeOut();

        $(".left-sidebar").hover(
            function () {
                $(".navbar-header").addClass("expand-logo");
            },
            function () {
                $(".navbar-header").removeClass("expand-logo");
            }
        );
        // this is for close icon when navigation open in mobile view
        $(".nav-toggler").on('click', function () {
            $("#main-wrapper").toggleClass("show-sidebar");
            $(".nav-toggler i").toggleClass("ti-menu");
        });
        $(".nav-lock").on('click', function () {
            $("body").toggleClass("lock-nav");
            $(".nav-lock i").toggleClass("mdi-toggle-switch-off");
            $("body, .page-wrapper").trigger("resize");
        });
        $(".search-box a, .search-box .app-search .srh-btn").on('click', function () {
            $(".app-search").toggle(200);
            $(".app-search input").focus();
        });

        // ==============================================================
        // Right sidebar options
        // ==============================================================
        $(function () {
            $(".service-panel-toggle").on('click', function () {
                $(".customizer").toggleClass('show-service-panel');

            });
            $('.page-wrapper').on('click', function () {
                $(".customizer").removeClass('show-service-panel');
            });
        });
        // ==============================================================
        // This is for the floating labels
        // ==============================================================
        $('.floating-labels .form-control').on('focus blur', function (e) {
            $(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
        }).trigger('blur');

        // ==============================================================
        //tooltip
        // ==============================================================
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
        // ==============================================================
        //Popover
        // ==============================================================
        $(function () {
            $('[data-toggle="popover"]').popover()
        })

        // ==============================================================
        // Resize all elements
        // ==============================================================
        $("body, .page-wrapper").trigger("resize");
        $(".page-wrapper").delay(20).show();

        // ==============================================================
        // To do list
        // ==============================================================
        $(".list-task li label").click(function () {
            $(this).toggleClass("task-done");
        });

        // ==============================================================
        // Collapsable cards
        // ==============================================================
        $('a[data-action="collapse"]').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.card').find('[data-action="collapse"] i').toggleClass('ti-minus ti-plus');
            $(this).closest('.card').children('.card-body').collapse('toggle');
        });
        // Toggle fullscreen
        $('a[data-action="expand"]').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.card').find('[data-action="expand"] i').toggleClass('mdi-arrow-expand mdi-arrow-compress');
            $(this).closest('.card').toggleClass('card-fullscreen');
        });
        // Close Card
        $('a[data-action="close"]').on('click', function () {
            $(this).closest('.card').removeClass().slideUp('fast');
        });
        // ==============================================================
        // LThis is for mega menu
        // ==============================================================
        $(document).on('click', '.mega-dropdown', function (e) {
            e.stopPropagation()
        });

        // ==============================================================
        // This is for the innerleft sidebar
        // ==============================================================
        $(".show-left-part").on('click', function () {
            $('.left-part').toggleClass('show-panel');
            $('.show-left-part').toggleClass('ti-menu');
        });

        // For Custom File Input
        $('.custom-file-input').on('change', function () {
            //get the file name
            var fileName = $(this).val();
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
        })
    });
}