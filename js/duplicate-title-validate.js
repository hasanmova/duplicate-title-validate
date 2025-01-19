jQuery(document).ready(function($) {
    const MIN_TITLE_LENGTH = 3; // Minimum title length to trigger the check
    const DEBOUNCE_DELAY = 300; // Debounce delay in milliseconds

    // Create and cache DOM elements
    const $similarTitlesContainer = $('<div>', {
        id: 'dtv-similar-titles',
        css: {
            marginTop: '10px',
            padding: '10px',
            border: '1px solid #ddd',
            background: '#fff8f8',
            borderRadius: '5px',
            display: 'none'
        }
    }).append(
        $('<strong>', {
            text: dtv_ajax_object.similar_titles_label,
            css: { color: 'red' }
        }),
        $('<div>', { id: 'dtv-title-suggestions' })
    ).insertAfter('#title');

    const $titleInput = $('#title');
    const $titleSuggestions = $('#dtv-title-suggestions');

    // Debounce function to reduce AJAX requests
    let debounceTimer;
    function debounce(callback, delay) {
        return function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(callback, delay);
        };
    }

    // Function to check for similar titles
    function checkSimilarTitles(title) {
        $.ajax({
            url: dtv_ajax_object.ajaxurl,
            method: 'POST',
            data: {
                action: 'check_similar_titles',
                title: title,
                nonce: dtv_ajax_object.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    const suggestions = response.data.map(item => 
                        $('<li>', {
                            text: item,
                            css: { marginBottom: '5px' }
                        })
                    );
                    $titleSuggestions.empty().append($('<ul>', {
                        css: { paddingLeft: '20px' }
                    }).append(suggestions));
                    $similarTitlesContainer.fadeIn();
                } else {
                    $similarTitlesContainer.fadeOut();
                }
            },
            error: function() {
                $similarTitlesContainer.fadeOut();
            }
        });
    }

    // Input event with debounce
    $titleInput.on('input', debounce(function() {
        const title = $titleInput.val().trim();

        if (title.length >= MIN_TITLE_LENGTH) {
            checkSimilarTitles(title);
        } else {
            $similarTitlesContainer.fadeOut();
        }
    }, DEBOUNCE_DELAY));
});