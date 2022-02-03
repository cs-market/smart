(function(_, $) {

    // [CONFIG]
    const config = {
        debug: false,
        mainContainer: 'pagination_contents',
        domName: {
            paginationContainer: '.ty-pagination-container',
            hideElm: '.ty-pagination__bottom',
            paginationBottom: '.ty-pagination',
            prefix: '.cm-infinite-pagination'
        },
        types: {
            compactList: {
                dom: ".ty-compact-list"
            },
            gridList: {
                dom: ".ty-grid-list"
            },
            productList: {
                dom: ".ty-product-list",
                getParent: function(container, selector){
                    return $(container).find(selector).parent();
                },
                setPageElm: function(dom) {
                    return $(dom).find(".ty-product-list");
                },
                getNewData: function(dom) {
                    //  leave div.ty-product-list & hr
                    dom.children('div').not('.ty-product-list').remove();
                    return dom.children();
                },
                putNewData: function(newData, loadType){
                    const setPageElments = getContetnElm(state.parent);

                    //  add <hr />
                    const hr = $('<hr />');
                    (loadType == 'next')
                    ? setPageElments.last().after(hr)
                    : setPageElments.first().before(hr);

                    (loadType == 'next')
                    ? newData.insertAfter(hr)
                    : newData.insertBefore(hr);
                },
            }
        },
        observer: {
            root: null,
            rootMargin: '10% 0px 10% 0px',
            threshold: [0, 1],
        }
    }
    const defaultState = {
        usePrev: true,
        useMore: false,
        morePage: 0,
        availability: false,
        paginationObserverLoadNext: {},
        paginationObserverLoadPrev: {},
        loadedPagesCount: {
            next: 0,
            prev: 0,
        },
        type: '',
        parent: $(),
        currentPage: 1,
        nextUrl: '',
        prevUrl: '',
        loadedPages: [],
        loadingAvailable: {
            next: true,
            prev: true,
        },
    }
    let state = {};
    // [/CONFIG]

    // [JQuery function]
    $.fn.hasAttr = function(name) {
     return this.attr(name) !== undefined;
    };
    // [/JQuery function]

    // [HELPER]
    const log = function() {
        if (config.debug) {
            console.log('Infinite Pagination: ',...arguments);
        }
    };

    const clearState = function() {
        state = $.extend(true, {}, defaultState);
    };

    //  call fnc from config or default
    const findInTypeOrDefault = function(data) {
        return config.types[state.type][data.name]
            ? config.types[state.type][data.name].apply(null, data.args)
            : data.default.apply(null, data.args);
    };

    const getContetnElm = function(dom) {
        return findInTypeOrDefault({
            name: 'setPageElm',
            default: function(dom){
                return $(dom).children();
            },
            args: [dom]
        });
    };

    const getParent = function(container, selector) {
        return findInTypeOrDefault({
            name: 'getParent',
            default: function(container, selector){
                return $(container).find(selector);
            },
            args: [container, selector]
        });
    };

    const getNewData = function(dom) {
        return findInTypeOrDefault({
            name: 'getNewData',
            default: function(dom){
                return $(dom).children();
            },
            args: [dom]
        });
    };

    const putNewData = function(newData, loadType) {
        return findInTypeOrDefault({
            name: 'putNewData',
            default: function(newData, loadType){
                const setPageElments = getContetnElm(state.parent);

                (loadType == 'next')
                ? newData.insertAfter(setPageElments.last())
                : newData.insertBefore(setPageElments.first());
            },
            args: [newData, loadType]
        });
    };

    const getMaxPage = function() {
        return Math.max( ...state.loadedPages );
    }

    const getMinPage = function() {
        return Math.min( ...state.loadedPages );
    }

    //  get from pagination block next url for load
    const setLoadUrls = function(dom) {
        dom = dom || config.domName.paginationContainer;

        const nextPage = getMaxPage() + 1;
        const nextLink = $(dom).find(config.domName.paginationBottom).find(`a[data-ca-page=${nextPage}]`);

        state.nextUrl = (nextLink.length)
            ? nextLink.attr('href')
            : '';

        if (state.usePrev) {
            const prevPage = getMinPage() - 1;

            if (prevPage > 0) {
                const prevLink = $(config.domName.paginationBottom).find(`a[data-ca-page=${prevPage}]`);

                state.prevUrl = (prevLink.length)
                    ? prevLink.attr('href')
                    : '';

            } else {
                state.usePrev = false;
                state.prevUrl = '';
            }
        } else {
            state.prevUrl = '';
        }
    };

    //  create block on bottom of pagination container
    const createLoadInfoBlock = function(loadType) {
        loadType = loadType || 'next';

        const baseClass = config.domName.prefix.replace(/^./, '');
        const infoClass = baseClass + '__info-block__' + loadType;
        const hideClass = 'hidden';

        let infoBlock;

        if ($('.' + infoClass).length) {
            infoBlock = $('.' + infoClass);
        } else {
            infoBlock = $(
                `<div class="${infoClass}">
                    <div class="${infoClass + '__more'} ${hideClass}">
                        ${_.tr('infinite_pagination_more')}
                    </div>
                </div>`
            );

            const doms = $(config.domName.paginationContainer)
                .find(config.types[state.type].dom);

            (loadType == 'next')
                ? doms.last().after(infoBlock)
                : doms.first().before(infoBlock);

            const more = infoBlock.find('.' + infoClass + '__more');
            $(more).click(function(){
                clickMore(this, loadType);
            });
        }

        if (state.useMore) {
            showUseMore(loadType);
        }
    };

    const showUseMore = function(loadType) {
        const moreClass = config.domName.prefix + '__info-block__' + loadType + '__more';

        $(moreClass).show();
        state.loadingAvailable[loadType] = false;
    }

    const preLoadContent = function(loadType) {
        const infoClass = config.domName.prefix + '__info-block__' + loadType;

        $(infoClass).children().hide();
        $(infoClass).find(infoClass + '__loading').show();

        log('preLoadContent', loadType);
    }

    const postLoadContent = function(loadType) {
        const infoClass = config.domName.prefix + '__info-block__' + loadType;

        const info = $(infoClass);
        info.children().hide();

        const url = (loadType == 'next') ? state.nextUrl : state.prevUrl;
        if (!url) {
            $(infoClass + '__loaded').show();
        } else {
            if (state.useMore) {
                showUseMore(loadType);
            } else if (state.morePage) {
                if (!(state.loadedPagesCount[loadType] % state.morePage)) {
                    showUseMore(loadType);
                }
            }
        }

        log('postLoadContent', loadType);
    }

    const clearObserve = function(loadType) {
        if (!loadType || loadType == 'next') {
            if (state.paginationObserverLoadNext instanceof IntersectionObserver ) {
                state.paginationObserverLoadNext.disconnect();
                log('clearObserveNext');
            }
        }
        if (!loadType || loadType == 'prev') {
            if (state.paginationObserverLoadPrev instanceof IntersectionObserver ) {
                state.paginationObserverLoadPrev.disconnect();
                log('clearObservePrev');
            }
        }
    }
    // [/HELPER]

    // [FUNCTIONS]
    const checkAvailability = function() {
        if ($(config.domName.paginationContainer).attr('id') == config.mainContainer
            && $(config.domName.paginationContainer).find(`a[data-ca-target-id=${config.mainContainer}]`).hasAttr("href")
        ) {
            state.availability = true;
        } else {
            state.availability = false;
        }

        log('checkAvailability', state.availability.toString());
        return state.availability;
    };

    const getType = function() {
        $.each(config.types, function(index, data) {
            if ($(config.domName.paginationContainer).find(data.dom).length) {
                state.type = index;
                state.parent = getParent($(config.domName.paginationContainer), data.dom);

                return false;
            }
        });

        log('getType', state.type);
        return state.type;
    };

    const updateInfo =  function(dom, page) {
        dom = dom || config.domName.paginationContainer;
        page = page || state.currentPage;

        state.loadedPages.push(parseInt(page));

        //  not necessary start from 1 page
        if (state.usePrev && getMinPage() <= 1) {
            state.usePrev = false;
            log('updateInfo', 'usePrev Disabled');
        }

        setLoadUrls(dom);
        log(
            'updateInfo',
            'next:' + state.nextUrl,
            'prev:' + (state.usePrev ? state.prevUrl : 'usePrevDisabled')
        );
    };

    const initRoutine = function() {
        $(config.domName.hideElm).hide();
        log('hideElm', config.domName.hideElm);

        createLoadInfoBlock('next');

        if (state.usePrev) {
            createLoadInfoBlock('prev');
        }
    };

    const setDefaultData = function(bckData) {
        log('setDefaultDataBckData', bckData);
        state = $.extend(state, bckData);
        state.startPage = state.currentPage;
        log('setDefaultDataEnd', state);
    };

    const setPage = function(dom, page) {
        const setPageElments = getContetnElm(dom);
        page = page || state.currentPage;
        page = parseInt(page);

        setPageElments.data('caInfPageNumber', page);
        log('setPage', page);
    };

    const getPage = function(dom) {
        return dom.data('caInfPageNumber');
    };

    const clickMore = function(self, loadType) {
        $(self).hide();
        state.loadingAvailable[loadType] = true;

        loadContent({loadType: loadType});

        log('clickMore');
    };

    const loadContent = function(params) {
        const { loadType } = params;
        log('loadContent', loadType);

        const url = (loadType == 'next') ? state.nextUrl : state.prevUrl;

        if (!url) {
            log('loadContent', 'Empty Url');
            clearObserve(loadType);
            return {};
        }

        preLoadContent(loadType);

        state.loadingAvailable[loadType] = false;

        $.ceAjax('request', url, {
            hidden: true,
            caching: false,
            force_exec: true,
            callback: function(data) {
                if (!data.text) {
                    log('loadContent', 'Return Empty Data');
                    return;
                }

                //  Build your love
                const contentDom2 = $($.parseHTML($.trim(data.text)));
                const typeData = config.types[state.type];
                const parent = getParent(contentDom2, typeData.dom);

                const newPage = (loadType == 'next')
                    ? getMaxPage() + 1
                    : getMinPage() - 1;

                setPage(parent, newPage);
                updateInfo(contentDom2, newPage);

                const newData = getNewData(parent);
                putNewData(newData, loadType);

                state.loadedPagesCount[loadType]++;

                state.loadingAvailable[loadType] = true;

                postLoadContent(loadType);

                clearObserve();

                $.commonInit(newData);
            },
        });
    };
    // [/FUNCTIONS]

    //  [OBSERVER]
    const initObserverLoad = function() {
        const paginationObserverLoad = function(loadType) {
            return new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting
                            && state.loadingAvailable[loadType]
                        ) {
                            //  unobserve in call fnc
                            loadContent({loadType: loadType});
                        }
                    });
                },
                config.observer
            );
        };

        state.paginationObserverLoadNext = paginationObserverLoad('next');
        const containerNext = document.querySelector(config.domName.prefix + '__info-block__next');
        state.paginationObserverLoadNext.observe(containerNext);

        if (state.usePrev) {
            state.paginationObserverLoadPrev = paginationObserverLoad('prev');
            const containerPrev = document.querySelector(config.domName.prefix + '__info-block__prev');
            state.paginationObserverLoadPrev.observe(containerPrev);
        }
    }
    //  [/OBSERVER]

    var methods = {
        start: function(bckData, context) {
            //  if !find pagination block
            if (!checkAvailability()) {
                return;
            }

            bckData.currentPage = $(config.domName.paginationContainer).find(config.domName.paginationBottom).find('.ty-pagination__selected').html();

            //  clear old observe, if filter etc
            clearObserve();

            //  check from commonInit
            const type = getPage(context.first())
                ? 'infinite_pagination_load'
                : '';

            if (type != 'infinite_pagination_load') {
                clearState();

                //  set some state data from smarty
                setDefaultData(bckData);

                //  if !find type from config
                if (!getType()) {
                    return;
                }

                updateInfo();

                initRoutine();

                //  set curent page number for navigation
                setPage(state.parent);
            }

            //  track pagination block position to load new page
            initObserverLoad();
        }
    };

    $.extend({
        ceInfinitePagination: function (method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('ceInfinitePagination: method ' + method + ' does not exist');
            }
        }
    });

}(Tygh, Tygh.$));
