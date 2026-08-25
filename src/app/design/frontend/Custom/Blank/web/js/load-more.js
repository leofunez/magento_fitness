define([], function() {
    'use strict';

    return function(config) {
        let page = 1;
        const button = document.getElementById('load-more');

        if (!button) return;

        button.addEventListener('click', function(){
            page++;

            fetch(`${config.url}?page=${page}`)
                .then(function(response) {
                    return response.json()
                })
                .then(function(data) {
                    document.querySelector(config.container)
                        .insertAdjacentHTML('beforeend', data.html);

                    if (!data.has_more) {
                        button.style.display = 'none';
                    }
                });
        });
    }
});