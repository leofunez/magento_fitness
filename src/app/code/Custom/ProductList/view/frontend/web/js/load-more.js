/*
define(['jquery'], function($) {
    'use strict';

    return function(config) {
        let page = 1;
        const $button = $('#load-more');
        const $container = $(config.container)

        if (!$button) return;

        $button.on('click', function(){
            page++;

            $.ajax({
                url: config.url,
                type: 'GET',
                dataType: 'json',
                data: {
                    page: page
                }
            }).done(function(response){
                if (response.html) {
                    $container.append(response.html);
                }

                if (!response.has_more) {
                    $button.hide();
                }
            });
        });
    };
});
*/

define([], function() {
    'use strict';

    return function(config) {
        let page = 1;
        const productsPerPage    = 12;
        const $container         = document.querySelector('.product-list');
        const $loadMoreButton    = document.querySelector('.product-list__load-more');
        const $toolbarAmount     = document.querySelector('.toolbar__amount');
        const $toolbarOrder      = document.querySelector('.toolbar__order');
        const $toolbarDirection  = document.querySelector('.toolbar__direction');
        const $productListfooter = document.querySelector('.product-list__footer');

        let directionValue = 'asc';

        if (!$loadMoreButton) return;

        const fetchData = function() {
            const URL_TO_FETCH = `${config.url}?current_page=${page}&category_id=${config.category_id}&order=${$toolbarOrder.value}&direction=${directionValue}`;

            fetch(URL_TO_FETCH)
                .then(function(response) {
                    return response.json();
                })
                .then(function({ html, total = 0 }) {
                    if (html) {
                        $container.insertAdjacentHTML('beforeend', html);
                        // $loadMoreButton.innerHTML = 'Load More';

                        // Avoid override the total when new products are loaded after clicking on Load More button.
                        if (page === 1) $toolbarAmount.innerHTML = `${total} items`;

                        // Disable LoadMore button when all products are displayed
                        if (productsPerPage * page >= total) {
                            $loadMoreButton.style.display = 'none';
                            $productListfooter.classList.add('no-more');
                        } else {
                            $loadMoreButton.style.display = 'block';
                        }
                    }
                });
        };

        fetchData();

        $loadMoreButton.addEventListener('click', function(){
            $loadMoreButton.innerHTML = 'Loading...';
            page++;
            fetchData();
        });

        $toolbarOrder.addEventListener('change', function() {
            $container.innerHTML = "";
            fetchData();
        });
        
        $toolbarDirection.addEventListener('click', function() {
            $container.innerHTML = "";
            $toolbarDirection.classList.toggle('desc');
            
            directionValue = (directionValue === 'desc') ? 'asc' : 'desc';
            
            fetchData();
        });
    }
});