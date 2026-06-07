const consentKey = 'melhores_sp_cookie_consent';

const cookieStorage = (() => {
    try {
        return window.localStorage ?? null;
    } catch {
        return null;
    }
})();

const cookieBanner = document.querySelector('[data-cookie-banner]');
const acceptButton = document.querySelector('[data-cookie-accept]');
const rejectButton = document.querySelector('[data-cookie-reject]');

const consentTemplates = () => Array.from(document.querySelectorAll('template[data-cookie-snippet]'));

const appendSnippet = (template) => {
    if (template.dataset.cookieLoaded === 'true') {
        return;
    }

    const position = template.dataset.cookiePosition || 'body_end';
    const target = position.startsWith('head') ? document.head : document.body;
    const html = template.textContent.trim();

    if (html === '') {
        return;
    }

    const range = document.createRange();
    range.selectNode(target);
    target.appendChild(range.createContextualFragment(html));
    template.dataset.cookieLoaded = 'true';
};

const applyCookieChoice = (choice) => {
    if (choice === 'accepted') {
        consentTemplates().forEach(appendSnippet);
    }

    if (cookieBanner) {
        cookieBanner.hidden = true;
    }
};

const storedChoice = (() => {
    try {
        return cookieStorage?.getItem(consentKey);
    } catch {
        return null;
    }
})();

const rememberCookieChoice = (choice) => {
    try {
        cookieStorage?.setItem(consentKey, choice);
    } catch {
        // Consent still applies for the current page view when storage is unavailable.
    }
};

if (storedChoice === 'accepted' || storedChoice === 'rejected') {
    applyCookieChoice(storedChoice);
} else if (cookieBanner) {
    cookieBanner.hidden = false;
}

acceptButton?.addEventListener('click', () => {
    rememberCookieChoice('accepted');
    applyCookieChoice('accepted');
});

rejectButton?.addEventListener('click', () => {
    rememberCookieChoice('rejected');
    applyCookieChoice('rejected');
});
