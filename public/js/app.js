(() => {
    const modal = document.getElementById('signup-modal');
    if (!modal) return;

    const title = document.getElementById('signup-title');
    const body = document.getElementById('signup-body');
    const image = document.getElementById('signup-image');
    const cta = document.getElementById('signup-cta');
    const kicker = document.getElementById('signup-kicker');

    const open = (button) => {
        title.textContent = button.dataset.lockHeadline;
        body.textContent = button.dataset.lockBody;
        image.src = button.dataset.lockImage;
        image.alt = button.dataset.lockName;
        cta.href = button.dataset.lockUrl;
        cta.textContent = `Sign up to own ${button.dataset.lockName}`;
        kicker.textContent = `${button.dataset.lockName} · ${button.dataset.lockPrice}`;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-signup-lock]').forEach((button) => {
        button.addEventListener('click', () => open(button));
    });

    modal.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) close();
    });
})();

(() => {
    const form = document.getElementById('pay-form');
    if (!form) return;

    const names = form.querySelectorAll('[data-pay-name]');
    const numbers = form.querySelectorAll('[data-pay-number]');
    const accounts = form.querySelectorAll('[data-pay-account]');
    const tx = form.querySelector('[data-pay-tx]');
    const txInput = form.querySelector('#transaction_id');
    const copyButton = form.querySelector('[data-copy-number]');

    const selected = () => form.querySelector('input[name="payment_method"]:checked');

    const paint = () => {
        const method = selected();
        if (!method) return;

        names.forEach((el) => { el.textContent = method.dataset.name; });
        numbers.forEach((el) => { el.textContent = method.dataset.number; });
        accounts.forEach((el) => { el.textContent = method.dataset.account; });
        if (tx) tx.textContent = txInput?.value.trim() || '—';
    };

    form.querySelectorAll('input[name="payment_method"]').forEach((input) => {
        input.addEventListener('change', paint);
    });

    txInput?.addEventListener('input', paint);

    copyButton?.addEventListener('click', async () => {
        const method = selected();
        const value = method?.dataset.number;
        if (!value) return;

        try {
            await navigator.clipboard.writeText(value);
            copyButton.textContent = 'Copied';
            setTimeout(() => { copyButton.textContent = 'Copy'; }, 1600);
        } catch {
            copyButton.textContent = 'Copy failed';
            setTimeout(() => { copyButton.textContent = 'Copy'; }, 1600);
        }
    });

    paint();
})();

(() => {
    const modal = document.getElementById('profile-modal');
    if (!modal) {
        return;
    }

    const open = () => {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-open-profile]').forEach((button) => {
        button.addEventListener('click', open);
    });

    modal.querySelectorAll('[data-close-profile]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            close();
        }
    });

    if (!modal.hidden) {
        document.body.style.overflow = 'hidden';
    }
})();

(() => {
    const card = document.querySelector('[data-community-card]');
    if (!card) {
        return;
    }

    const url = card.dataset.feedUrl;
    const portrait = card.querySelector('[data-community-portrait]');
    const name = card.querySelector('[data-community-name]');
    const amount = card.querySelector('[data-community-amount]');
    const message = card.querySelector('[data-community-message]');
    const meta = card.querySelector('[data-community-meta]');
    const seconds = Number(card.dataset.refreshSeconds) || 30;

    const paintPortrait = (member) => {
        if (!portrait) {
            return;
        }

        if (member?.avatar) {
            const image = document.createElement('img');
            image.src = member.avatar;
            image.alt = member.name;
            portrait.replaceChildren(image);
            return;
        }

        const fallback = document.createElement('span');
        fallback.className = 'community-portrait-fallback';
        fallback.setAttribute('aria-hidden', 'true');
        fallback.textContent = member?.initials || '+';
        portrait.replaceChildren(fallback);
    };

    const paint = (data) => {
        const member = data.member || null;

        paintPortrait(member);

        if (name) {
            name.textContent = member?.name || 'Your photo here';
        }

        if (amount) {
            amount.textContent = member
                ? `${member.amount} from ${member.lock}`
                : '';
        }

        if (message) {
            message.textContent = data.message;
        }

        if (meta) {
            const count = Number(data.earner_count) || 0;
            meta.textContent = count > 0
                ? `${count} ${count === 1 ? 'member is' : 'members are'} collecting daily interest`
                : 'Waiting for the first 24-hour payout';
        }
    };

    const load = async () => {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                return;
            }
            paint(await response.json());
        } catch {
            // Keep the last painted community card if the live feed misses a beat.
        }
    };

    const delayUntilNextTick = () => {
        const remainder = Math.floor(Date.now() / 1000) % seconds;

        return (seconds - remainder) * 1000;
    };

    setTimeout(() => {
        load();
        setInterval(load, seconds * 1000);
    }, delayUntilNextTick());
})();

(() => {
    const box = document.getElementById('challenge-wait');
    if (!box) {
        return;
    }

    const poll = async () => {
        try {
            const response = await fetch(box.dataset.statusUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            if (data.status === 'approved' && data.redirect) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = data.redirect;
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = token;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        } catch {
            // Keep waiting.
        }
    };

    setInterval(poll, 2500);
})();

(() => {
    const form = document.getElementById('pay-form');
    const sources = form?.querySelectorAll('input[name="payment_source"]');
    if (!form || !sources?.length) {
        return;
    }

    const blocks = form.querySelectorAll('[data-pay-mobile]');
    const submit = form.querySelector('button[type="submit"]');

    const paint = () => {
        const chosen = form.querySelector('input[name="payment_source"]:checked')?.value || 'mobile_money';
        const byMobileMoney = chosen === 'mobile_money';

        blocks.forEach((block) => {
            block.hidden = !byMobileMoney;
            block.querySelectorAll('input').forEach((input) => {
                input.disabled = !byMobileMoney;
            });
        });

        if (submit) {
            submit.textContent = byMobileMoney ? 'Submit deposit' : 'Pay from balance';
        }
    };

    sources.forEach((input) => input.addEventListener('change', paint));
    paint();
})();

(() => {
    const widget = document.querySelector('[data-human-check]');
    if (!widget) {
        return;
    }

    const tick = widget.querySelector('[data-human-tick]');
    const token = widget.querySelector('input[name="human_token"]');
    const status = widget.querySelector('[data-human-status]');
    const seed = widget.dataset.humanSeed || '';

    tick?.addEventListener('change', () => {
        if (tick.checked) {
            token.value = seed.split('').reverse().join('');
            widget.classList.add('is-verified');
            if (status) status.textContent = 'Thank you — you are confirmed as a person.';
            return;
        }

        token.value = '';
        widget.classList.remove('is-verified');
        if (status) status.textContent = 'Tick the box so TSL knows a person is signing up.';
    });
})();

(() => {
    const button = document.querySelector('[data-copy-invite]');
    const input = document.getElementById('invite-link');
    if (!button || !input) {
        return;
    }

    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(input.value);
            button.textContent = 'Copied';
            setTimeout(() => { button.textContent = 'Copy invite link'; }, 1600);
        } catch {
            input.select();
        }
    });
})();
