function safeHref(href) {
    try {
        const url = new URL(href);

        if (url.protocol !== 'http:' && url.protocol !== 'https:' && url.protocol !== 'mailto:') {
            return null;
        }

        return url.href;
    } catch {
        return null;
    }
}

function takeDelimited(text, i, open, close = open) {
    if (! text.startsWith(open, i)) {
        return null;
    }

    const start = i + open.length;
    const end = text.indexOf(close, start);

    if (end === -1) {
        return null;
    }

    const inner = text.slice(start, end);

    if (inner === '' || inner.includes('\n')) {
        return null;
    }

    return { inner, next: end + close.length };
}

function takeLink(text, i) {
    if (text[i] !== '[') {
        return null;
    }

    const labelEnd = text.indexOf('](', i + 1);

    if (labelEnd === -1 || text.slice(i, labelEnd).includes('\n')) {
        return null;
    }

    const urlEnd = text.indexOf(')', labelEnd + 2);

    if (urlEnd === -1 || text.slice(labelEnd, urlEnd).includes('\n')) {
        return null;
    }

    const label = text.slice(i + 1, labelEnd);
    const href = safeHref(text.slice(labelEnd + 2, urlEnd).trim());

    if (! href || label === '') {
        return null;
    }

    return { label, href, next: urlEnd + 1 };
}

function takeAutolink(text, i) {
    if (! text.startsWith('http://', i) && ! text.startsWith('https://', i)) {
        return null;
    }

    let end = i;

    while (end < text.length && ! /[\s<>)\]"'`]/.test(text[end])) {
        end++;
    }

    let href = text.slice(i, end).replace(/[.,;:!?]+$/, '');

    if (href.endsWith(')')) {
        href = href.slice(0, -1);
    }

    const safe = safeHref(href);

    if (! safe) {
        return null;
    }

    return { href: safe, next: i + href.length };
}

function canOpenEmphasis(text, i, delim) {
    const prev = i === 0 ? '' : text[i - 1];

    return ! (prev && /[A-Za-z0-9]/.test(prev) && (delim === '_' || delim === '*'));
}

function linkEl(href, label, autolinkLabel = true) {
    const a = document.createElement('a');
    a.href = href;
    a.target = '_blank';
    a.rel = 'noopener noreferrer';

    if (autolinkLabel) {
        appendInline(a, label, false);
    } else {
        a.appendChild(document.createTextNode(label));
    }

    return a;
}

function appendInline(parent, text, autolink = true) {
    let i = 0;
    let buf = '';

    const flush = () => {
        if (buf === '') {
            return;
        }

        parent.appendChild(document.createTextNode(buf));
        buf = '';
    };

    const push = (node) => {
        flush();
        parent.appendChild(node);
    };

    while (i < text.length) {
        if (text[i] === '`') {
            const taken = takeDelimited(text, i, '`');

            if (taken) {
                const code = document.createElement('code');
                code.textContent = taken.inner;
                push(code);
                i = taken.next;
                continue;
            }
        }

        if (text.startsWith('***', i) || text.startsWith('___', i)) {
            const delim = text.slice(i, i + 3);
            const taken = takeDelimited(text, i, delim);

            if (taken) {
                const strong = document.createElement('strong');
                const em = document.createElement('em');
                appendInline(em, taken.inner, autolink);
                strong.appendChild(em);
                push(strong);
                i = taken.next;
                continue;
            }
        }

        if (text.startsWith('**', i) || text.startsWith('__', i)) {
            const delim = text.slice(i, i + 2);
            const taken = takeDelimited(text, i, delim);

            if (taken) {
                const strong = document.createElement('strong');
                appendInline(strong, taken.inner, autolink);
                push(strong);
                i = taken.next;
                continue;
            }
        }

        if (text.startsWith('~~', i)) {
            const taken = takeDelimited(text, i, '~~');

            if (taken) {
                const del = document.createElement('del');
                appendInline(del, taken.inner, autolink);
                push(del);
                i = taken.next;
                continue;
            }
        }

        if ((text[i] === '*' || text[i] === '_') && canOpenEmphasis(text, i, text[i])) {
            const taken = takeDelimited(text, i, text[i]);

            if (taken && ! taken.inner.startsWith(' ') && ! taken.inner.endsWith(' ')) {
                const em = document.createElement('em');
                appendInline(em, taken.inner, autolink);
                push(em);
                i = taken.next;
                continue;
            }
        }

        if (text[i] === '[') {
            const link = takeLink(text, i);

            if (link) {
                push(linkEl(link.href, link.label));
                i = link.next;
                continue;
            }
        }

        if (autolink) {
            const auto = takeAutolink(text, i);

            if (auto) {
                push(linkEl(auto.href, auto.href, false));
                i = auto.next;
                continue;
            }
        }

        buf += text[i];
        i += 1;
    }

    flush();
}

function codeBlock(body, lang) {
    const pre = document.createElement('pre');
    const code = document.createElement('code');

    if (lang) {
        code.dataset.lang = lang;
    }

    code.textContent = body;
    pre.appendChild(code);

    return pre;
}

function listEl(ordered, items) {
    const list = document.createElement(ordered ? 'ol' : 'ul');

    for (const item of items) {
        const li = document.createElement('li');
        appendInline(li, item);
        list.appendChild(li);
    }

    return list;
}

const LIST = /^\s*(?:[-*+]|\d+\.)\s+/;
const QUOTE = /^\s*>\s?/;
const HEADING = /^\s*#{1,6}\s+\S/;
const RULE = /^\s*(?:-{3,}|\*{3,}|_{3,})\s*$/;

function paintBlocks(root, src) {
    const lines = src.replace(/\r\n/g, '\n').split('\n');
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];

        if (line.startsWith('```')) {
            const lang = line.slice(3).trim();
            const body = [];
            i += 1;

            while (i < lines.length && ! lines[i].startsWith('```')) {
                body.push(lines[i]);
                i += 1;
            }

            if (i < lines.length) {
                i += 1;
            }

            root.appendChild(codeBlock(body.join('\n'), lang));
            continue;
        }

        if (LIST.test(line)) {
            const ordered = /^\s*\d+\./.test(line);
            const items = [];

            while (i < lines.length && LIST.test(lines[i])) {
                items.push(lines[i].replace(LIST, ''));
                i += 1;
            }

            root.appendChild(listEl(ordered, items));
            continue;
        }

        if (QUOTE.test(line)) {
            const quoted = [];

            while (i < lines.length && QUOTE.test(lines[i])) {
                quoted.push(lines[i].replace(QUOTE, ''));
                i += 1;
            }

            const quote = document.createElement('blockquote');
            appendInline(quote, quoted.join('\n'));
            root.appendChild(quote);
            continue;
        }

        if (HEADING.test(line)) {
            const heading = document.createElement('p');
            const strong = document.createElement('strong');
            appendInline(strong, line.replace(/^\s*#{1,6}\s+/, ''));
            heading.appendChild(strong);
            root.appendChild(heading);
            i += 1;
            continue;
        }

        if (RULE.test(line)) {
            root.appendChild(document.createElement('hr'));
            i += 1;
            continue;
        }

        if (line.trim() === '') {
            i += 1;
            continue;
        }

        const rows = [];

        while (
            i < lines.length
            && lines[i].trim() !== ''
            && ! lines[i].startsWith('```')
            && ! LIST.test(lines[i])
            && ! QUOTE.test(lines[i])
            && ! HEADING.test(lines[i])
            && ! RULE.test(lines[i])
        ) {
            rows.push(lines[i]);
            i += 1;
        }

        const p = document.createElement('p');

        rows.forEach((row, index) => {
            if (index > 0) {
                p.appendChild(document.createElement('br'));
            }

            appendInline(p, row);
        });

        root.appendChild(p);
    }
}

export function renderMarkdown(text) {
    const root = document.createDocumentFragment();
    paintBlocks(root, String(text ?? ''));

    return root;
}

export function fillMarkdown(el, text) {
    el.replaceChildren(renderMarkdown(text));
}
