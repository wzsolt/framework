/*
Template Name: Minton - Admin & Dashboard Template
Author: CoderThemes
Website: https://coderthemes.com/
Contact: support@coderthemes.com
File: Layouts Js File
*/


/**
 * LeftSidebar
 * @param {*} $
 */
!function ($) {
    'use strict';

    var LeftSidebar = function () {
        this.body = $('body'),
            this.window = $(window)
    };

    /**
     * Reset the theme
     */
    LeftSidebar.prototype._reset = function() {
        this.body.removeAttr('data-sidebar-color');
        this.body.removeAttr('data-sidebar-size');
        this.body.removeAttr('data-sidebar-showuser');
    },

        /**
         * Changes the color of sidebar
         * @param {*} color
         */
        LeftSidebar.prototype.changeColor = function(color) {
            this.body.attr('data-sidebar-color', color);
            this.parent.updateConfig("sidebar", { "color": color });
        },

        /**
         * Changes the size of sidebar
         * @param {*} size
         */
        LeftSidebar.prototype.changeSize = function(size) {
            this.body.attr('data-sidebar-size', size);
            this.parent.updateConfig("sidebar", { "size": size });
        },

        /**
         * Toggle User information
         * @param {*} showUser
         */
        LeftSidebar.prototype.showUser = function(showUser) {
            this.body.attr('data-sidebar-showuser', showUser);
            this.parent.updateConfig("sidebar", { "showuser": showUser });
        },

        /**
         * Initilizes the menu
         */
        LeftSidebar.prototype.initMenu = function() {
            var self = this;

            var layout = $.LayoutThemeApp.getConfig();
            var sidebar = $.extend({}, layout ? layout.sidebar: {});
            var defaultSidebarSize = sidebar.size ? sidebar.size : 'default';

            // resets everything
            this._reset();

            // Left menu collapse
            $('.button-menu-mobile').on('click', function (event) {
                event.preventDefault();
                var sidebarSize = self.body.attr('data-sidebar-size');
                if (self.window.width() >= 993) {
                    if (sidebarSize === 'condensed') {
                        self.changeSize(defaultSidebarSize === 'condensed'? 'default': defaultSidebarSize);
                    } else {
                        self.changeSize('condensed');
                    }
                } else {
                    self.changeSize(defaultSidebarSize);
                    self.body.toggleClass('sidebar-enable');
                }
            });

            // sidebar - main menu
            if ($("#side-menu").length) {
                var navCollapse = $('#side-menu li .collapse');
                var navToggle = $("#side-menu [data-bs-toggle='collapse']");
                navToggle.on('click', function(e) {
                    return false;
                });
                // open one menu at a time only

                navCollapse.on({
                    'show.bs.collapse': function (event)  {
                        $('#side-menu .collapse.show').not(parent).collapse('hide');
                        var parent = $(event.target).parents('.collapse.show');
                    },
                });


                // activate the menu in left side bar (Vertical Menu) based on url
                $("#side-menu a").each(function () {
                    var pageUrl = window.location.href.split(/[?#]/)[0];
                    if (this.href == pageUrl) {
                        $(this).addClass("active");
                        $(this).parent().addClass("menuitem-active");
                        $(this).parent().parent().parent().addClass("show");
                        $(this).parent().parent().parent().parent().addClass("menuitem-active"); // add active to li of the current link

                        var firstLevelParent = $(this).parent().parent().parent().parent().parent().parent();
                        if (firstLevelParent.attr('id') !== 'sidebar-menu')
                            firstLevelParent.addClass("show");

                        $(this).parent().parent().parent().parent().parent().parent().parent().addClass("menuitem-active");

                        var secondLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent();
                        if (secondLevelParent.attr('id') !== 'wrapper')
                            secondLevelParent.addClass("show");

                        var upperLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                        if (!upperLevelParent.is('body'))
                            upperLevelParent.addClass("menuitem-active");
                    }
                });
            }


            // handling two columns menu if present
            var twoColSideNav = $("#two-col-sidenav-main");
            if (twoColSideNav.length) {
                var twoColSideNavItems = $("#two-col-sidenav-main .nav-link");
                var sideSubMenus = $(".twocolumn-menu-item");

                // showing/displaying tooltip based on screen size
                if (this.window.width() >= 585) {
                    //TODO : Wait for stable release of bootstrap 5
                    // twoColSideNavItems.each(function (idx,element){
                    //    new bootstrap.Tooltip(element);
                    // });
                } else {
                    // twoColSideNavItems.tooltip('disable');
                }

                var nav = $('.twocolumn-menu-item .nav-second-level');
                var navCollapse = $('#two-col-menu li .collapse');

                // open one menu at a time only
                navCollapse.on({
                    'show.bs.collapse': function () {
                        var nearestNav = $(this).closest(nav).closest(nav).find(navCollapse);
                        if (nearestNav.length)
                            nearestNav.not($(this)).collapse('hide');
                        else
                            navCollapse.not($(this)).collapse('hide');
                    }
                });



                twoColSideNavItems.on('click', function (e) {
                    var target = $($(this).attr('href'));

                    if (target.length) {
                        e.preventDefault();

                        twoColSideNavItems.removeClass('active');
                        $(this).addClass('active');

                        sideSubMenus.removeClass("d-block");
                        target.addClass("d-block");

                        // showing full sidebar if menu item is clicked
                        $.LayoutThemeApp.leftSidebar.changeSize('default');
                        return false;
                    }
                    return true;
                });

                // activate menu with no child
                var pageUrl = window.location.href.split(/[?#]/)[0];
                twoColSideNavItems.each(function () {
                    if (this.href == pageUrl) {
                        $(this).addClass('active');
                    }
                });



                // activate the menu in left side bar (Two column) based on url
                $("#two-col-menu a").each(function () {
                    if (this.href == pageUrl) {
                        $(this).addClass("active");
                        $(this).parent().addClass("menuitem-active");
                        $(this).parent().parent().parent().addClass("show");
                        $(this).parent().parent().parent().parent().addClass("menuitem-active"); // add active to li of the current link

                        var firstLevelParent = $(this).parent().parent().parent().parent().parent().parent();
                        if (firstLevelParent.attr('id') !== 'sidebar-menu')
                            firstLevelParent.addClass("show");

                        $(this).parent().parent().parent().parent().parent().parent().parent().addClass("menuitem-active");

                        var secondLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent();
                        if (secondLevelParent.attr('id') !== 'wrapper')
                            secondLevelParent.addClass("show");

                        var upperLevelParent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                        if (!upperLevelParent.is('body'))
                            upperLevelParent.addClass("menuitem-active");

                        // opening menu
                        var matchingItem = null;
                        var targetEl = '#' + $(this).parents('.twocolumn-menu-item').attr("id");
                        $("#two-col-sidenav-main .nav-link").each(function () {
                            if ($(this).attr('href') === targetEl) {
                                matchingItem = $(this);
                            }
                        });
                        if (matchingItem) matchingItem.trigger('click');
                    }
                });
            }
        },


        /**
         * Initilize the left sidebar size based on screen size
         */
        LeftSidebar.prototype.initLayout = function() {
            var self = this;
            // in case of small size, activate the small menu
            if ((this.window.width() >= 768 && this.window.width() <= 1028) || this.body.data('keep-enlarged')) {
                this.changeSize('condensed');
            } else {
                var layout = JSON.parse(this.body.attr('data-layout') ? this.body.attr('data-layout') : '{}');
                var sidebar = $.extend({}, layout ? layout.sidebar: {});
                var defaultSidebarSize = sidebar && sidebar.size ? sidebar.size : 'default';
                var sidebarSize = self.body.attr('data-sidebar-size');
                this.changeSize(defaultSidebarSize ? defaultSidebarSize :(sidebarSize ? sidebarSize : 'default'));
            }
        },

        /**
         * Initilizes the menu
         */
        LeftSidebar.prototype.init = function() {
            var self = this;
            this.initMenu();
            this.initLayout();

            // on window resize, make menu flipped automatically
            this.window.on('resize', function (e) {
                e.preventDefault();
                self.initLayout();
            });
        },

        $.LeftSidebar = new LeftSidebar, $.LeftSidebar.Constructor = LeftSidebar
}(window.jQuery),


    /**
     * Topbar
     * @param {*} $
     */
    function ($) {
        'use strict';

        var Topbar = function () {
            this.body = $('body'),
                this.window = $(window)
        };

        /**
         * Initilizes the menu
         */
        Topbar.prototype.initMenu = function() {
            // Serach Toggle
            $('#top-search').on('click', function (e) {
                $('#search-dropdown').addClass('d-block');
            });

            // hide search on opening other dropdown
            $('.topbar-dropdown').on('show.bs.dropdown', function () {
                $('#search-dropdown').removeClass('d-block');
            });

            //activate the menu in topbar(horizontal menu) based on url
            $(".navbar-nav a").each(function () {
                var pageUrl = window.location.href.split(/[?#]/)[0];
                if (this.href == pageUrl) {
                    $(this).addClass("active");
                    $(this).parent().addClass("active");
                    $(this).parent().parent().addClass("active");
                    $(this).parent().parent().parent().addClass("active");
                    $(this).parent().parent().parent().parent().addClass("active");
                    var el = $(this).parent().parent().parent().parent().addClass("active").prev();
                    if (el.hasClass("nav-link"))
                        el.addClass('active');
                }
            });

            // Topbar - main menu
            $('.navbar-toggle').on('click', function (event) {
                $(this).toggleClass('open');
                $('#navigation').slideToggle(400);
            });
        },

            /**
             * Changes the color of topbar
             * @param {*} color
             */
            Topbar.prototype.changeColor = function(color) {
                this.body.attr('data-topbar-color', color);
                this.parent.updateConfig("topbar", { "color": color });
            },

            /**
             * Initilizes the menu
             */
            Topbar.prototype.init = function() {
                this.initMenu();
            },
            $.Topbar = new Topbar, $.Topbar.Constructor = Topbar
    }(window.jQuery),


    /**
     * RightBar
     * @param {*} $
     */
    function ($) {
        'use strict';

        var RightBar = function () {
            this.body = $('body'),
                this.window = $(window)
        };

        /**
         * Select the option based on saved config
         */
        RightBar.prototype.selectOptionsFromConfig = function() {
            var self = this;

            var config = self.layout.getConfig();

            if (config) {
                $('.right-bar input[type=checkbox]').prop('checked',false);
                $('input[type=checkbox][name=color-scheme-mode][value=' + config.mode + ']').prop('checked', true);
                $('input[type=checkbox][name=width][value=' + config.width + ']').prop('checked', true);
                $('input[type=checkbox][name=menus-position][value=' + config.menuPosition + ']').prop('checked', true);

                $('input[type=checkbox][name=leftsidebar-color][value=' + config.sidebar.color + ']').prop('checked', true);
                $('input[type=checkbox][name=leftsidebar-size][value=' + config.sidebar.size + ']').prop('checked', true);
                $('input[type=checkbox][name=leftsidebar-user]').prop('checked', config.sidebar.showuser);

                $('input[type=checkbox][name=topbar-color][value=' + config.topbar.color + ']').prop('checked', true);
            }
        },

            /**
             * Toggles the right sidebar
             */
            RightBar.prototype.toggleRightSideBar = function() {
                var self = this;
                self.body.toggleClass('right-bar-enabled');
                self.selectOptionsFromConfig();
            },

            /**
             * Initilizes the right side bar
             */
            RightBar.prototype.init = function() {
                var self = this;

                // right side-bar toggle
                $(document).on('click', '.right-bar-toggle', function () {
                    self.toggleRightSideBar();
                });

                $(document).on('click', 'body', function (e) {
                    // hiding search bar
                    if($(e.target).closest('#top-search').length !== 1) {
                        $('#search-dropdown').removeClass('d-block');
                    }
                    if ($(e.target).closest('.right-bar-toggle, .right-bar').length > 0) {
                        return;
                    }

                    if ($(e.target).closest('.left-side-menu, .side-nav').length > 0 || $(e.target).hasClass('button-menu-mobile')
                        || $(e.target).closest('.button-menu-mobile').length > 0) {
                        return;
                    }

                    $('body').removeClass('right-bar-enabled');
                    $('body').removeClass('sidebar-enable');
                    return;
                });

                // overall color scheme
                $('input[type=checkbox][name=color-scheme-mode]').change(function () {
                    self.layout.changeMode($(this).val());
                    self.selectOptionsFromConfig();

                });

                // width mode
                $('input[type=checkbox][name=width]').change(function () {
                    self.layout.changeLayoutWidth($(this).val());
                    self.selectOptionsFromConfig();
                });

                // menus-position
                $('input[type=checkbox][name=menus-position]').change(function () {
                    self.layout.changeMenuPositions($(this).val());
                    self.selectOptionsFromConfig();

                });

                // left sidebar color
                $('input[type=checkbox][name=leftsidebar-color]').change(function () {
                    self.layout.leftSidebar.changeColor($(this).val());
                    self.selectOptionsFromConfig();

                });

                // left sidebar size
                $('input[type=checkbox][name=leftsidebar-size]').change(function () {
                    self.layout.leftSidebar.changeSize($(this).val());
                    self.selectOptionsFromConfig();

                });

                // left sidebar user information
                $('input[type=checkbox][name=leftsidebar-user]').change(function (e) {
                    self.layout.leftSidebar.showUser(e.target.checked);
                    self.selectOptionsFromConfig();

                });

                // topbar
                $('input[type=checkbox][name=topbar-color]').change(function () {
                    self.layout.topbar.changeColor($(this).val());
                    self.selectOptionsFromConfig();

                });

                // reset
                $('#resetBtn').on('click', function (e) {
                    e.preventDefault();
                    // reset to default
                    self.layout.reset();
                    self.selectOptionsFromConfig();
                });
            },

            $.RightBar = new RightBar, $.RightBar.Constructor = RightBar
    }(window.jQuery),


    /**
     * Layout and theme manager
     * @param {*} $
     */

    function ($) {
        'use strict';

        // Layout and theme manager

        var LayoutThemeApp = function () {
            this.body = $('body'),
                this.window = $(window),
                this.config = {},
                // styles
                this.defaultBSStyle = $("#bs-default-stylesheet"),
                this.defaultAppStyle = $("#app-default-stylesheet"),
                this.darkBSStyle = $("#bs-dark-stylesheet"),
                this.darkAppStyle = $("#app-dark-stylesheet");
        };

        /**
         * Preserves the config in memory
         */
        LayoutThemeApp.prototype._saveConfig = function(newConfig) {
            this.config = $.extend(this.config, newConfig);
            // NOTE: You can make ajax call here to save preference on server side or localstorage as well

        },

            /**
             * Update the config for given config
             * @param {*} param
             * @param {*} config
             */
            LayoutThemeApp.prototype.updateConfig = function(param, config) {
                var newObj = {};


                if (typeof config === 'object' && config !== null) {
                    var originalParam = this.config[param];
                    newObj[param] = $.extend(originalParam, config);
                } else {
                    newObj[param] = config;
                }
                this._saveConfig(newObj);

            }

        /**
         * Loads the config - takes from body if available else uses default one
         */
        LayoutThemeApp.prototype.loadConfig = function() {
            var bodyConfig = JSON.parse(this.body.attr('data-layout') ? this.body.attr('data-layout') : '{}');

            var config = $.extend({}, {
                mode: "light",
                width: "fluid",
                menuPosition: 'fixed',
                sidebar: {
                    color: "light",
                    size: "default",
                    showuser: false
                },
                topbar: {
                    color: "dark"
                },
                showRightSidebarOnPageLoad: false
            });
            if (bodyConfig) {
                config = $.extend({}, config, bodyConfig);
            };
            return config;
        },

            /**
             * Apply the config
             */
            LayoutThemeApp.prototype.applyConfig = function() {
                // getting the saved config if available
                this.config = this.loadConfig();
                var topbarConfig = $.extend({}, this.config.topbar);
                var sidebarConfig = $.extend({}, this.config.sidebar);

                // activate menus
                this.leftSidebar.init();
                this.topbar.init();

                this.leftSidebar.parent = this;
                this.topbar.parent = this;


                // mode
                this.changeMode(this.config.mode);

                // width
                this.changeLayoutWidth(this.config.width);

                // menu position
                this.changeMenuPositions(this.config.menuPosition);

                // left sidebar
                this.leftSidebar.changeColor(sidebarConfig.color);
                this.leftSidebar.changeSize(sidebarConfig.size);
                this.leftSidebar.showUser(sidebarConfig.showuser);

                // topbar
                this.topbar.changeColor(topbarConfig.color);
            },

            /**
             * Toggle dark or light mode
             * @param {*} mode
             */
            LayoutThemeApp.prototype.changeMode = function(mode, notChangeSidebar) {
                // sets the theme
                switch (mode) {
                    case "dark": {
                        this.defaultBSStyle.attr("disabled", true);
                        this.defaultAppStyle.attr("disabled", true);

                        this.darkBSStyle.attr("disabled", false);
                        this.darkAppStyle.attr("disabled", false);
                        if (notChangeSidebar)
                            this._saveConfig({ mode: mode });
                        else {
                            this.leftSidebar.changeColor("dark");
                            this._saveConfig({ mode: mode, sidebar: $.extend({}, this.config.sidebar, { color: 'dark' }) });
                        }
                        break;
                    }
                    default: {
                        this.defaultBSStyle.attr("disabled", false);
                        this.defaultAppStyle.attr("disabled", false);

                        this.darkBSStyle.attr("disabled", true);
                        this.darkAppStyle.attr("disabled", true);

                        if (notChangeSidebar)
                            this._saveConfig({ mode: mode });
                        else {
                            this.leftSidebar.changeColor("light");
                            this._saveConfig({ mode: mode, sidebar: $.extend({}, this.config.sidebar, { color: 'light' }) });
                        }
                        break;
                    }
                }

                this.rightBar.selectOptionsFromConfig();
            }

        /**
         * Changes the width of layout
         */
        LayoutThemeApp.prototype.changeLayoutWidth = function(width) {
            switch (width) {
                case "boxed": {
                    this.body.attr('data-layout-width', 'boxed');
                    // automatically activating condensed
                    $.LeftSidebar.changeSize("condensed");
                    this._saveConfig({ width: width });
                    break;
                }
                default: {
                    this.body.attr('data-layout-width', 'fluid');
                    // automatically activating provided size
                    var bodyConfig = JSON.parse(this.body.attr('data-layout') ? this.body.attr('data-layout') : '{}');
                    $.LeftSidebar.changeSize(bodyConfig && bodyConfig.sidebar ? bodyConfig.sidebar.size : "default");
                    this._saveConfig({ width: width });
                    break;
                }
            }
            this.rightBar.selectOptionsFromConfig();
        }

        /**
         * Changes menu positions
         */
        LayoutThemeApp.prototype.changeMenuPositions = function(position) {
            this.body.attr("data-layout-menu-position", position);
            this.updateConfig("menuPosition", position);

        }

        /**
         * Clear out the saved config
         */
        LayoutThemeApp.prototype.clearSavedConfig = function() {
            this.config = {};
        },

            /**
             * Gets the config
             */
            LayoutThemeApp.prototype.getConfig = function() {
                return this.config;
            },

            /**
             * Reset to default
             */
            LayoutThemeApp.prototype.reset = function() {
                this.clearSavedConfig();
                this.applyConfig();
            },

            /**
             * Init
             */
            LayoutThemeApp.prototype.init = function() {
                this.leftSidebar = $.LeftSidebar;
                this.topbar = $.Topbar;

                this.leftSidebar.parent = this;
                this.topbar.parent = this;

                // initilize the menu
                this.applyConfig();
            },

            $.LayoutThemeApp = new LayoutThemeApp, $.LayoutThemeApp.Constructor = LayoutThemeApp
    }(window.jQuery);;/*!
 * Waves v0.7.6
 * http://fian.my.id/Waves
 *
 * Copyright 2014-2018 Alfiana E. Sibuea and other contributors
 * Released under the MIT license
 * https://github.com/fians/Waves/blob/master/LICENSE
 */

;(function(window, factory) {
    'use strict';

    // AMD. Register as an anonymous module.  Wrap in function so we have access
    // to root via `this`.
    if (typeof define === 'function' && define.amd) {
        define([], function() {
            window.Waves = factory.call(window);
            return window.Waves;
        });
    }

    // Node. Does not work with strict CommonJS, but only CommonJS-like
    // environments that support module.exports, like Node.
    else if (typeof exports === 'object') {
        module.exports = factory.call(window);
    }

    // Browser globals.
    else {
        window.Waves = factory.call(window);
    }
})(typeof global === 'object' ? global : this, function() {
    'use strict';

    var Waves            = Waves || {};
    var $$               = document.querySelectorAll.bind(document);
    var toString         = Object.prototype.toString;
    var isTouchAvailable = 'ontouchstart' in window;


    // Find exact position of element
    function isWindow(obj) {
        return obj !== null && obj === obj.window;
    }

    function getWindow(elem) {
        return isWindow(elem) ? elem : elem.nodeType === 9 && elem.defaultView;
    }

    function isObject(value) {
        var type = typeof value;
        return type === 'function' || type === 'object' && !!value;
    }

    function isDOMNode(obj) {
        return isObject(obj) && obj.nodeType > 0;
    }

    function getWavesElements(nodes) {
        var stringRepr = toString.call(nodes);

        if (stringRepr === '[object String]') {
            return $$(nodes);
        } else if (isObject(nodes) && /^\[object (Array|HTMLCollection|NodeList|Object)\]$/.test(stringRepr) && nodes.hasOwnProperty('length')) {
            return nodes;
        } else if (isDOMNode(nodes)) {
            return [nodes];
        }

        return [];
    }

    function offset(elem) {
        var docElem, win,
            box = { top: 0, left: 0 },
            doc = elem && elem.ownerDocument;

        docElem = doc.documentElement;

        if (typeof elem.getBoundingClientRect !== typeof undefined) {
            box = elem.getBoundingClientRect();
        }
        win = getWindow(doc);
        return {
            top: box.top + win.pageYOffset - docElem.clientTop,
            left: box.left + win.pageXOffset - docElem.clientLeft
        };
    }

    function convertStyle(styleObj) {
        var style = '';

        for (var prop in styleObj) {
            if (styleObj.hasOwnProperty(prop)) {
                style += (prop + ':' + styleObj[prop] + ';');
            }
        }

        return style;
    }

    var Effect = {

        // Effect duration
        duration: 750,

        // Effect delay (check for scroll before showing effect)
        delay: 200,

        show: function(e, element, velocity) {

            // Disable right click
            if (e.button === 2) {
                return false;
            }

            element = element || this;

            // Create ripple
            var ripple = document.createElement('div');
            ripple.className = 'waves-ripple waves-rippling';
            element.appendChild(ripple);

            // Get click coordinate and element width
            var pos       = offset(element);
            var relativeY = 0;
            var relativeX = 0;
            // Support for touch devices
            if('touches' in e && e.touches.length) {
                relativeY   = (e.touches[0].pageY - pos.top);
                relativeX   = (e.touches[0].pageX - pos.left);
            }
            //Normal case
            else {
                relativeY   = (e.pageY - pos.top);
                relativeX   = (e.pageX - pos.left);
            }
            // Support for synthetic events
            relativeX = relativeX >= 0 ? relativeX : 0;
            relativeY = relativeY >= 0 ? relativeY : 0;

            var scale     = 'scale(' + ((element.clientWidth / 100) * 3) + ')';
            var translate = 'translate(0,0)';

            if (velocity) {
                translate = 'translate(' + (velocity.x) + 'px, ' + (velocity.y) + 'px)';
            }

            // Attach data to element
            ripple.setAttribute('data-hold', Date.now());
            ripple.setAttribute('data-x', relativeX);
            ripple.setAttribute('data-y', relativeY);
            ripple.setAttribute('data-scale', scale);
            ripple.setAttribute('data-translate', translate);

            // Set ripple position
            var rippleStyle = {
                top: relativeY + 'px',
                left: relativeX + 'px'
            };

            ripple.classList.add('waves-notransition');
            ripple.setAttribute('style', convertStyle(rippleStyle));
            ripple.classList.remove('waves-notransition');

            // Scale the ripple
            rippleStyle['-webkit-transform'] = scale + ' ' + translate;
            rippleStyle['-moz-transform'] = scale + ' ' + translate;
            rippleStyle['-ms-transform'] = scale + ' ' + translate;
            rippleStyle['-o-transform'] = scale + ' ' + translate;
            rippleStyle.transform = scale + ' ' + translate;
            rippleStyle.opacity = '1';

            var duration = e.type === 'mousemove' ? 2500 : Effect.duration;
            rippleStyle['-webkit-transition-duration'] = duration + 'ms';
            rippleStyle['-moz-transition-duration']    = duration + 'ms';
            rippleStyle['-o-transition-duration']      = duration + 'ms';
            rippleStyle['transition-duration']         = duration + 'ms';

            ripple.setAttribute('style', convertStyle(rippleStyle));
        },

        hide: function(e, element) {
            element = element || this;

            var ripples = element.getElementsByClassName('waves-rippling');

            for (var i = 0, len = ripples.length; i < len; i++) {
                removeRipple(e, element, ripples[i]);
            }

            if (isTouchAvailable) {
                element.removeEventListener('touchend', Effect.hide);
                element.removeEventListener('touchcancel', Effect.hide);
            }

            element.removeEventListener('mouseup', Effect.hide);
            element.removeEventListener('mouseleave', Effect.hide);
        }
    };

    /**
     * Collection of wrapper for HTML element that only have single tag
     * like <input> and <img>
     */
    var TagWrapper = {

        // Wrap <input> tag so it can perform the effect
        input: function(element) {

            var parent = element.parentNode;

            // If input already have parent just pass through
            if (parent.tagName.toLowerCase() === 'i' && parent.classList.contains('waves-effect')) {
                return;
            }

            // Put element class and style to the specified parent
            var wrapper       = document.createElement('i');
            wrapper.className = element.className + ' waves-input-wrapper';
            element.className = 'waves-button-input';

            // Put element as child
            parent.replaceChild(wrapper, element);
            wrapper.appendChild(element);

            // Apply element color and background color to wrapper
            var elementStyle    = window.getComputedStyle(element, null);
            var color           = elementStyle.color;
            var backgroundColor = elementStyle.backgroundColor;

            wrapper.setAttribute('style', 'color:' + color + ';background:' + backgroundColor);
            element.setAttribute('style', 'background-color:rgba(0,0,0,0);');

        },

        // Wrap <img> tag so it can perform the effect
        img: function(element) {

            var parent = element.parentNode;

            // If input already have parent just pass through
            if (parent.tagName.toLowerCase() === 'i' && parent.classList.contains('waves-effect')) {
                return;
            }

            // Put element as child
            var wrapper  = document.createElement('i');
            parent.replaceChild(wrapper, element);
            wrapper.appendChild(element);

        }
    };

    /**
     * Hide the effect and remove the ripple. Must be
     * a separate function to pass the JSLint...
     */
    function removeRipple(e, el, ripple) {

        // Check if the ripple still exist
        if (!ripple) {
            return;
        }

        ripple.classList.remove('waves-rippling');

        var relativeX = ripple.getAttribute('data-x');
        var relativeY = ripple.getAttribute('data-y');
        var scale     = ripple.getAttribute('data-scale');
        var translate = ripple.getAttribute('data-translate');

        // Get delay beetween mousedown and mouse leave
        var diff = Date.now() - Number(ripple.getAttribute('data-hold'));
        var delay = 350 - diff;

        if (delay < 0) {
            delay = 0;
        }

        if (e.type === 'mousemove') {
            delay = 150;
        }

        // Fade out ripple after delay
        var duration = e.type === 'mousemove' ? 2500 : Effect.duration;

        setTimeout(function() {

            var style = {
                top: relativeY + 'px',
                left: relativeX + 'px',
                opacity: '0',

                // Duration
                '-webkit-transition-duration': duration + 'ms',
                '-moz-transition-duration': duration + 'ms',
                '-o-transition-duration': duration + 'ms',
                'transition-duration': duration + 'ms',
                '-webkit-transform': scale + ' ' + translate,
                '-moz-transform': scale + ' ' + translate,
                '-ms-transform': scale + ' ' + translate,
                '-o-transform': scale + ' ' + translate,
                'transform': scale + ' ' + translate
            };

            ripple.setAttribute('style', convertStyle(style));

            setTimeout(function() {
                try {
                    el.removeChild(ripple);
                } catch (e) {
                    return false;
                }
            }, duration);

        }, delay);
    }


    /**
     * Disable mousedown event for 500ms during and after touch
     */
    var TouchHandler = {

        /* uses an integer rather than bool so there's no issues with
         * needing to clear timeouts if another touch event occurred
         * within the 500ms. Cannot mouseup between touchstart and
         * touchend, nor in the 500ms after touchend. */
        touches: 0,

        allowEvent: function(e) {

            var allow = true;

            if (/^(mousedown|mousemove)$/.test(e.type) && TouchHandler.touches) {
                allow = false;
            }

            return allow;
        },
        registerEvent: function(e) {
            var eType = e.type;

            if (eType === 'touchstart') {

                TouchHandler.touches += 1; // push

            } else if (/^(touchend|touchcancel)$/.test(eType)) {

                setTimeout(function() {
                    if (TouchHandler.touches) {
                        TouchHandler.touches -= 1; // pop after 500ms
                    }
                }, 500);

            }
        }
    };


    /**
     * Delegated click handler for .waves-effect element.
     * returns null when .waves-effect element not in "click tree"
     */
    function getWavesEffectElement(e) {

        if (TouchHandler.allowEvent(e) === false) {
            return null;
        }

        var element = null;
        var target = e.target || e.srcElement;

        while (target.parentElement) {
            if ( (!(target instanceof SVGElement)) && target.classList.contains('waves-effect')) {
                element = target;
                break;
            }
            target = target.parentElement;
        }

        return element;
    }

    /**
     * Bubble the click and show effect if .waves-effect elem was found
     */
    function showEffect(e) {

        // Disable effect if element has "disabled" property on it
        // In some cases, the event is not triggered by the current element
        // if (e.target.getAttribute('disabled') !== null) {
        //     return;
        // }

        var element = getWavesEffectElement(e);

        if (element !== null) {

            // Make it sure the element has either disabled property, disabled attribute or 'disabled' class
            if (element.disabled || element.getAttribute('disabled') || element.classList.contains('disabled')) {
                return;
            }

            TouchHandler.registerEvent(e);

            if (e.type === 'touchstart' && Effect.delay) {

                var hidden = false;

                var timer = setTimeout(function () {
                    timer = null;
                    Effect.show(e, element);
                }, Effect.delay);

                var hideEffect = function(hideEvent) {

                    // if touch hasn't moved, and effect not yet started: start effect now
                    if (timer) {
                        clearTimeout(timer);
                        timer = null;
                        Effect.show(e, element);
                    }
                    if (!hidden) {
                        hidden = true;
                        Effect.hide(hideEvent, element);
                    }

                    removeListeners();
                };

                var touchMove = function(moveEvent) {
                    if (timer) {
                        clearTimeout(timer);
                        timer = null;
                    }
                    hideEffect(moveEvent);

                    removeListeners();
                };

                element.addEventListener('touchmove', touchMove, false);
                element.addEventListener('touchend', hideEffect, false);
                element.addEventListener('touchcancel', hideEffect, false);

                var removeListeners = function() {
                    element.removeEventListener('touchmove', touchMove);
                    element.removeEventListener('touchend', hideEffect);
                    element.removeEventListener('touchcancel', hideEffect);
                };
            } else {

                Effect.show(e, element);

                if (isTouchAvailable) {
                    element.addEventListener('touchend', Effect.hide, false);
                    element.addEventListener('touchcancel', Effect.hide, false);
                }

                element.addEventListener('mouseup', Effect.hide, false);
                element.addEventListener('mouseleave', Effect.hide, false);
            }
        }
    }

    Waves.init = function(options) {
        var body = document.body;

        options = options || {};

        if ('duration' in options) {
            Effect.duration = options.duration;
        }

        if ('delay' in options) {
            Effect.delay = options.delay;
        }

        if (isTouchAvailable) {
            body.addEventListener('touchstart', showEffect, false);
            body.addEventListener('touchcancel', TouchHandler.registerEvent, false);
            body.addEventListener('touchend', TouchHandler.registerEvent, false);
        }

        body.addEventListener('mousedown', showEffect, false);
    };


    /**
     * Attach Waves to dynamically loaded inputs, or add .waves-effect and other
     * waves classes to a set of elements. Set drag to true if the ripple mouseover
     * or skimming effect should be applied to the elements.
     */
    Waves.attach = function(elements, classes) {

        elements = getWavesElements(elements);

        if (toString.call(classes) === '[object Array]') {
            classes = classes.join(' ');
        }

        classes = classes ? ' ' + classes : '';

        var element, tagName;

        for (var i = 0, len = elements.length; i < len; i++) {

            element = elements[i];
            tagName = element.tagName.toLowerCase();

            if (['input', 'img'].indexOf(tagName) !== -1) {
                TagWrapper[tagName](element);
                element = element.parentElement;
            }

            if (element.className.indexOf('waves-effect') === -1) {
                element.className += ' waves-effect' + classes;
            }
        }
    };


    /**
     * Cause a ripple to appear in an element via code.
     */
    Waves.ripple = function(elements, options) {
        elements = getWavesElements(elements);
        var elementsLen = elements.length;

        options          = options || {};
        options.wait     = options.wait || 0;
        options.position = options.position || null; // default = centre of element


        if (elementsLen) {
            var element, pos, off, centre = {}, i = 0;
            var mousedown = {
                type: 'mousedown',
                button: 1
            };
            var hideRipple = function(mouseup, element) {
                return function() {
                    Effect.hide(mouseup, element);
                };
            };

            for (; i < elementsLen; i++) {
                element = elements[i];
                pos = options.position || {
                    x: element.clientWidth / 2,
                    y: element.clientHeight / 2
                };

                off      = offset(element);
                centre.x = off.left + pos.x;
                centre.y = off.top + pos.y;

                mousedown.pageX = centre.x;
                mousedown.pageY = centre.y;

                Effect.show(mousedown, element);

                if (options.wait >= 0 && options.wait !== null) {
                    var mouseup = {
                        type: 'mouseup',
                        button: 1
                    };

                    setTimeout(hideRipple(mouseup, element), options.wait);
                }
            }
        }
    };

    /**
     * Remove all ripples from an element.
     */
    Waves.calm = function(elements) {
        elements = getWavesElements(elements);
        var mouseup = {
            type: 'mouseup',
            button: 1
        };

        for (var i = 0, len = elements.length; i < len; i++) {
            Effect.hide(mouseup, elements[i]);
        }
    };

    /**
     * Deprecated API fallback
     */
    Waves.displayEffect = function(options) {
        console.error('Waves.displayEffect() has been deprecated and will be removed in future version. Please use Waves.init() to initialize Waves effect');
        Waves.init(options);
    };

    return Waves;
});
;/*
Template Name: Minton - Admin & Dashboard Template
Author: CoderThemes
Website: https://coderthemes.com/
Contact: support@coderthemes.com
File: Main Js File
*/


!function ($) {
    "use strict";

    var Components = function () { };

    //initializing tooltip
    Components.prototype.initTooltipPlugin = function () {
        $.fn.tooltip && $('[data-bs-toggle="tooltip"]').tooltip()
    },

        //initializing popover
        Components.prototype.initPopoverPlugin = function () {
            $.fn.popover && $('[data-bs-toggle="popover"]').popover()
        },

        //initializing toast
        Components.prototype.initToastPlugin = function() {
            $.fn.toast && $('[data-bs-toggle="toast"]').toast()
        },

        //initializing form validation
        Components.prototype.initFormValidation = function () {
            $(".needs-validation").on('submit', function (event) {
                $(this).addClass('was-validated');
                if ($(this)[0].checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
                return true;
            });
        },

        // Counterup
        Components.prototype.initCounterUp = function() {
            var delay = $(this).attr('data-delay')?$(this).attr('data-delay'):100; //default is 100
            var time = $(this).attr('data-time')?$(this).attr('data-time'):1200; //default is 1200
            $('[data-plugin="counterup"]').each(function(idx, obj) {
                $(this).counterUp({
                    delay: delay,
                    time: time
                });
            });
        },

        //peity charts
        Components.prototype.initPeityCharts = function() {
            $('[data-plugin="peity-pie"]').each(function(idx, obj) {
                var colors = $(this).attr('data-colors')?$(this).attr('data-colors').split(","):[];
                var width = $(this).attr('data-width')?$(this).attr('data-width'):20; //default is 20
                var height = $(this).attr('data-height')?$(this).attr('data-height'):20; //default is 20
                $(this).peity("pie", {
                    fill: colors,
                    width: width,
                    height: height
                });
            });
            //donut
            $('[data-plugin="peity-donut"]').each(function(idx, obj) {
                var colors = $(this).attr('data-colors')?$(this).attr('data-colors').split(","):[];
                var width = $(this).attr('data-width')?$(this).attr('data-width'):20; //default is 20
                var height = $(this).attr('data-height')?$(this).attr('data-height'):20; //default is 20
                $(this).peity("donut", {
                    fill: colors,
                    width: width,
                    height: height
                });
            });

            $('[data-plugin="peity-donut-alt"]').each(function(idx, obj) {
                $(this).peity("donut");
            });

            // line
            $('[data-plugin="peity-line"]').each(function(idx, obj) {
                $(this).peity("line", $(this).data());
            });

            // bar
            $('[data-plugin="peity-bar"]').each(function(idx, obj) {
                var colors = $(this).attr('data-colors')?$(this).attr('data-colors').split(","):[];
                var width = $(this).attr('data-width')?$(this).attr('data-width'):20; //default is 20
                var height = $(this).attr('data-height')?$(this).attr('data-height'):20; //default is 20
                $(this).peity("bar", {
                    fill: colors,
                    width: width,
                    height: height
                });
            });
        },

        Components.prototype.initKnob = function() {
            $('[data-plugin="knob"]:not(.inited)').each(function(idx, obj) {
                $(this).addClass('inited');

                $(this).knob({
                    'format': function (value){
                        if($(obj).data('append')){
                            return value + $(obj).data('append');
                        }
                        return value;
                    }
                });
            });
        },

        Components.prototype.initTippyTooltips = function () {
            if($('[data-plugin="tippy"]').length > 0)
                tippy('[data-plugin="tippy"]');
        },

        Components.prototype.initShowPassword = function () {
            $("[data-password]").on('click', function() {
                if($(this).attr('data-password') == "false"){
                    $(this).siblings("input").attr("type", "text");
                    $(this).attr('data-password', 'true');
                    $(this).addClass("show-password");
                } else {
                    $(this).siblings("input").attr("type", "password");
                    $(this).attr('data-password', 'false');
                    $(this).removeClass("show-password");
                }
            });
        },

        Components.prototype.initMultiDropdown = function () {
            $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
                if (!$(this).next().hasClass('show')) {
                    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
                }
                var $subMenu = $(this).next(".dropdown-menu");
                $subMenu.toggleClass('show');

                return false;
            });
        },

        //initilizing
        Components.prototype.init = function () {
            this.initTooltipPlugin(),
            this.initPeityCharts(),
            this.initPopoverPlugin();
                //this.initToastPlugin(),
                //this.initFormValidation(),
                //this.initCounterUp(),
                //this.initKnob();
            //this.initTippyTooltips();
            //this.initShowPassword();
            //this.initMultiDropdown();
        },

        $.Components = new Components, $.Components.Constructor = Components

}(window.jQuery),

    function($) {
        "use strict";

        /**
         Portlet Widget
         */
        var Portlet = function() {
            this.$body = $("body"),
                this.$portletIdentifier = ".card",
                this.$portletCloser = '.card a[data-toggle="remove"]',
                this.$portletRefresher = '.card a[data-toggle="reload"]'
        };

        //on init
        Portlet.prototype.init = function() {
            // Panel closest
            var $this = this;
            $(document).on("click",this.$portletCloser, function (ev) {
                ev.preventDefault();
                var $portlet = $(this).closest($this.$portletIdentifier);
                var $portlet_parent = $portlet.parent();
                $portlet.remove();
                if ($portlet_parent.children().length == 0) {
                    $portlet_parent.remove();
                }
            });

            // Panel Reload
            $(document).on("click",this.$portletRefresher, function (ev) {
                ev.preventDefault();
                var $portlet = $(this).closest($this.$portletIdentifier);
                // This is just a simulation, nothing is going to be reloaded
                $portlet.append('<div class="card-disabled"><div class="card-portlets-loader"><div class="spinner-border text-primary m-2" role="status"></div></div></div>');
                var $pd = $portlet.find('.card-disabled');
                setTimeout(function () {
                    $pd.fadeOut('fast', function () {
                        $pd.remove();
                    });
                }, 500 + 300 * (Math.random() * 5));
            });
        },
            //
            $.Portlet = new Portlet, $.Portlet.Constructor = Portlet

    }(window.jQuery),


    function ($) {
        'use strict';

        var App = function () {
            this.$body = $('body'),
                this.$window = $(window)
        };

        /**
         * Initlizes the controls
         */
        App.prototype.initControls = function () {
            // remove loading
            setTimeout(function() {
                document.body.classList.remove('loading');
            }, 400);

            // Preloader
            /*
            $(window).on('load', function () {
                $('#status').fadeOut();
                $('#preloader').delay(350).fadeOut('slow');
            });
            */

            $('[data-toggle="fullscreen"]').on("click", function (e) {
                e.preventDefault();
                $('body').toggleClass('fullscreen-enable');
                if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.cancelFullScreen) {
                        document.cancelFullScreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    }
                }
            });
            document.addEventListener('fullscreenchange', exitHandler );
            document.addEventListener("webkitfullscreenchange", exitHandler);
            document.addEventListener("mozfullscreenchange", exitHandler);
            function exitHandler() {
                if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {
                    console.log('pressed');
                    $('body').removeClass('fullscreen-enable');
                }
            }
        },

            //initilizing
            App.prototype.init = function () {
                $.Portlet.init();
                $.Components.init();

                this.initControls();

                // init layout
                this.layout = $.LayoutThemeApp;
                this.rightBar = $.RightBar;
                this.rightBar.layout = this.layout;
                this.layout.rightBar = this.rightBar;

                this.layout.init();
                this.rightBar.init(this.layout);


                // showing the sidebar on load if user is visiting the page first time only
                var bodyConfig = this.$body.data('layout');
                if (window.sessionStorage && bodyConfig && bodyConfig.hasOwnProperty('showRightSidebarOnPageLoad') && bodyConfig['showRightSidebarOnPageLoad']) {
                    var alreadyVisited = sessionStorage.getItem("_MINTON_VISITED_");
                    if (!alreadyVisited) {
                        $.RightBar.toggleRightSideBar();
                        sessionStorage.setItem("_MINTON_VISITED_", true);
                    }
                }



                //Popovers
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl)
                })

                //Tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {

                    })
                })



                //Toasts
                var toastElList = [].slice.call(document.querySelectorAll('.toast'))
                var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl)
                })

                // Toasts Placement
                var toastPlacement = document.getElementById("toastPlacement");
                if (toastPlacement) {
                    document.getElementById("selectToastPlacement").addEventListener("change", function () {
                        if (!toastPlacement.dataset.originalClass) {
                            toastPlacement.dataset.originalClass = toastPlacement.className;
                        }
                        toastPlacement.className = toastPlacement.dataset.originalClass + " " + this.value;
                    });
                }

                //  RTL support js
                /*
                if(document.getElementById('bs-default-stylesheet').href.includes('bootstrap-rtl.min.css')){
                    document.getElementsByTagName('html')[0].dir="rtl";
                }
                */
            },

            $.App = new App, $.App.Constructor = App


    }(window.jQuery),
//initializing main application module
    function ($) {
        "use strict";
        $.App.init();
    }(window.jQuery);

// Waves Effect
Waves.init();
