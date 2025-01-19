const { __ } = wp.i18n;
const { dispatch } = wp.data;
const { useEffect } = wp.element;
const { useEntityProp } = wp.coreData;
const { apiFetch } = wp;

const debounce = (func, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
};

const checkForDuplicateTitle = async(title, postId) => {
    try {
        const response = await apiFetch({
            path: '/duplicate-title-validate/v1/check-duplicate',
            method: 'POST',
            data: {
                title: title,
                post_id: postId,
            },
        });

        if (response.is_duplicate) {
            dispatch('core/notices').createNotice(
                'error',
                __(response.message, 'duplicate-title-validate'), {
                    id: 'duplicate-title-warning',
                    isDismissible: true,
                }
            );
        } else {
            dispatch('core/notices').removeNotice('duplicate-title-warning');
        }
    } catch (error) {
        console.error('Error checking duplicate title:', error);
    }
};

const fetchMatchingTitles = async(title, postId) => {
    try {
        const response = await apiFetch({
            path: '/duplicate-title-validate/v1/get-matching-titles',
            method: 'POST',
            data: {
                title: title,
                post_id: postId,
            },
        });

        const resultDiv = document.getElementById('duplicate-title-checker-result');

        if (response.is_duplicate && Array.isArray(response.titles) && response.titles.length > 0) {
            const duplicateTitles = response.titles.map(title => `<li>${title}</li>`).join('');
            resultDiv.innerHTML = `
                <p>${__(response.message, 'duplicate-title-validate')}</p>
                <ul>${duplicateTitles}</ul>
            `;
        } else {
            resultDiv.innerHTML = `<p>${__(response.no_duplicate_message, 'duplicate-title-validate')}</p>`;
        }
    } catch (error) {
        console.error('Error fetching matching titles:', error);
    }
};

const debouncedCheck = debounce(checkForDuplicateTitle, 500);
const debouncedFetch = debounce(fetchMatchingTitles, 500);

const DuplicateTitleValidator = () => {
    const [title] = useEntityProp('postType', 'post', 'title');
    const [postId] = useEntityProp('postType', 'post', 'id');

    useEffect(() => {
        if (title) {
            debouncedCheck(title, postId);
            debouncedFetch(title, postId);
        }
    }, [title, postId]);

    return null;
};

wp.plugins.registerPlugin('duplicate-title-validate-gutenberg', {
    render: DuplicateTitleValidator,
});