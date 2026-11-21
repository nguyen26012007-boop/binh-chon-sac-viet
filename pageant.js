const dialogCopy = JSON.parse(document.getElementById('voteUiCopy').textContent);

const modal = document.getElementById('voteModal');
const form = document.getElementById('voteForm');
const message = document.getElementById('voteMessage');
const confirmButton = document.getElementById('confirmVote');
const registrationForm = document.getElementById('registrationForm');
const registerButton = document.getElementById('registerButton');
const initialStep = document.getElementById('stepVote');
const retryStep = document.getElementById('retryVoteStep');
const retryForm = document.getElementById('retryVoteForm');
const retryMessage = document.getElementById('retryVoteMessage');
const retryDigits = [...retryForm.querySelectorAll('.participation-digit')];
const cards = [...document.querySelectorAll('.contestant-card')];
const voteButtons = [...document.querySelectorAll('[data-vote]')];
const state = { counts: {}, voted: null, open: true, ready: false, submitting: false, registered: false, blocked: false, viewer_name: null };
const formatter = new Intl.NumberFormat('vi-VN');
let previousFocus;
let toastTimer;
const codeDigits = [...form.querySelectorAll('.participation-digit')];
const delay = milliseconds => new Promise(resolve => setTimeout(resolve, milliseconds));

function clearParticipationCode() { [...codeDigits, ...retryDigits].forEach(input => { input.value = ''; }); }
function bindCodeInputs(inputs) {
    inputs.forEach((input, index) => {
        input.addEventListener('focus', () => input.select());
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '').slice(-1);
            if (input.value && index < 5) inputs[index + 1].focus();
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Backspace' && !input.value && index > 0) inputs[index - 1].focus();
        });
        input.addEventListener('paste', event => {
            event.preventDefault();
            const digits = event.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            if (!digits) return;
            clearParticipationCode();
            [...digits].forEach((digit, i) => { inputs[i].value = digit; });
            inputs[Math.min(digits.length, 5)].focus();
        });
    });
}
bindCodeInputs(codeDigits);
bindCodeInputs(retryDigits);

function toast(text) {
    const target = document.getElementById('siteToast');
    target.textContent = text;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { target.textContent = ''; }, 6000);
}

function updateButtons() {
    voteButtons.forEach(button => {
        const selected = String(state.voted) === button.dataset.vote;
        button.disabled = state.submitting || !state.ready || !state.open || state.blocked;
        button.classList.toggle('is-selected', selected);
        button.querySelector('span').textContent = !state.open ? 'Tạm đóng' : selected ? 'Bình chọn tiếp' : 'Bình chọn';
    });
}

function renderResults(data) {
    if (data.counts) state.counts = data.counts;
    if ('voted' in data) state.voted = data.voted;
    if ('open' in data) state.open = data.open;
    ['registered', 'blocked', 'viewer_name'].forEach(key => { if (key in data) state[key] = data[key]; });

    document.getElementById('memberStatus').textContent = state.blocked ? 'Hồ sơ đăng ký của bạn đã bị quản trị viên khóa bình chọn.' : state.registered ? 'Đã đăng ký: ' + state.viewer_name : 'Đăng ký họ tên và số điện thoại để gửi bình chọn.';

    document.querySelectorAll('[data-count]').forEach(node => { node.textContent = formatter.format(state.counts[node.dataset.count] || 0); });
    const total = Object.values(state.counts).reduce((sum, value) => sum + Number(value), 0);
    document.querySelectorAll('.total-votes').forEach(node => { node.textContent = formatter.format(total); });

    updateButtons();
    const rankings = [...document.querySelectorAll('[data-rank-id]')];
    rankings.sort((a, b) => (state.counts[b.dataset.rankId] || 0) - (state.counts[a.dataset.rankId] || 0)
        || Number(cards.find(card => card.dataset.id === a.dataset.rankId).dataset.number) - Number(cards.find(card => card.dataset.id === b.dataset.rankId).dataset.number));
    rankings.forEach((row, index) => {
        row.querySelector('.rank-position').textContent = String(index + 1).padStart(2, '0');
        row.querySelector('.rank-meter span').style.width = (total ? (state.counts[row.dataset.rankId] || 0) / total * 100 : 0) + '%';
        document.getElementById('rankingList').append(row);
    });
    filterCandidates();
}

async function loadResults() {
    try {
        const response = await fetch('vote.php', { cache: 'no-store' });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Chưa thể tải bình chọn.');
        state.ready = true;
        renderResults(data);
    } catch {
        state.ready = false;
        updateButtons();
        toast('Chưa thể tải kết quả. Hãy tải lại trang để thử lại.');
    }
}

function openVote(id) {
    if (state.submitting || !state.ready || !state.open || state.blocked) return;

    const card = cards.find(item => item.dataset.id === id);
    if (!card) return;
    previousFocus = document.activeElement;
    document.getElementById('selectedId').value = id;
    document.getElementById('selectedImage').src = card.querySelector('img').src;
    document.getElementById('selectedImage').alt = card.dataset.name;
    document.getElementById('selectedName').textContent = card.dataset.name;
    document.getElementById('selectedNumber').textContent = dialogCopy.dialog_number_prefix + ' ' + card.dataset.number;
    document.getElementById('selectedHometown').textContent = card.dataset.hometown;

    registrationForm.hidden = false;
    form.hidden = true;
    document.getElementById('registrationStatus').hidden = true;
    registrationForm.reset();
    initialStep.hidden = false;
    retryStep.hidden = true;
    retryForm.reset();
    retryMessage.textContent = '';
    modal.setAttribute('aria-labelledby', 'voteTitle');
    modal.setAttribute('aria-describedby', 'voteDescription');
    modal.querySelector('.close-modal').setAttribute('aria-label', dialogCopy.dialog_close_label);

    message.textContent = '';
    clearParticipationCode();

    confirmButton.disabled = false;
    confirmButton.querySelector('span').textContent = dialogCopy.dialog_confirm_button;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    document.getElementById('voterFullname').focus();
}

function closeVote() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
    previousFocus?.focus();
}

voteButtons.forEach(button => button.addEventListener('click', () => openVote(button.dataset.vote)));
modal.querySelector('.close-modal').addEventListener('click', closeVote);
modal.addEventListener('click', event => { if (event.target === modal) closeVote(); });

document.addEventListener('keydown', event => {
    if (modal.style.display !== 'flex') return;
    if (event.key === 'Escape') closeVote();
    if (event.key === 'Tab') {
        const targets = [...modal.querySelectorAll('button, input, select, textarea, a[href]')]
            .filter(node => !node.disabled && node.type !== 'hidden' && node.getClientRects().length);
        const first = targets[0], last = targets[targets.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
});

registrationForm.addEventListener('submit', async event => {
    event.preventDefault();
    if (state.submitting) return;
    state.submitting = true;
    updateButtons();
    registerButton.disabled = true;
    registerButton.querySelector('span').textContent = dialogCopy.dialog_register_busy;
    message.textContent = '';
    try {
        const [response] = await Promise.all([fetch('register.php', { method: 'POST', body: new FormData(registrationForm) }), delay(5000)]);
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Chưa thể đăng ký.');

        renderResults(data);

        registrationForm.hidden = true;
        form.hidden = false;
        document.getElementById('registrationStatus').hidden = false;
        document.getElementById('registrationStatus').textContent = dialogCopy.dialog_registered_prefix + ' ' + data.viewer_name;
        clearParticipationCode();
        codeDigits[0].focus();

    } catch (error) {
        message.style.color = '#a33b40';
        message.textContent = error.message;
    } finally {
        state.submitting = false;
        updateButtons();
        registerButton.disabled = false;
        registerButton.querySelector('span').textContent = dialogCopy.dialog_register_button;
    }
});

async function submitVote(event) {
    event.preventDefault();
    const activeForm = event.currentTarget;
    const isRetry = activeForm === retryForm;
    const codeDigits = [...activeForm.querySelectorAll('.participation-digit')];
    const message = activeForm === retryForm ? retryMessage : document.getElementById('voteMessage');
    const confirmButton = activeForm.querySelector('button[type=submit]');
    if (state.submitting) return;
    const code = codeDigits.map(input => input.value).join('');
    if (!/^[0-9]{6}$/.test(code)) { message.textContent = isRetry ? dialogCopy.dialog_retry_code_required : dialogCopy.dialog_code_required; return; }

    state.submitting = true;
    codeDigits.forEach(input => { input.disabled = true; });
    updateButtons();
    confirmButton.disabled = true;
    confirmButton.querySelector('span').textContent = isRetry ? dialogCopy.dialog_retry_code_busy : dialogCopy.dialog_code_busy;
    message.textContent = '';

    try {
        const payload = new FormData(form);
        payload.set('participation_code', code);

        // Delay 7 giây
        const [response] = await Promise.all([fetch('vote.php', { method: 'POST', body: payload }), delay(7000)]);
        const data = await response.json();

        if (!response.ok || !data.success) {
            const failure = new Error(data.message || 'Chưa thể ghi nhận bình chọn.');
            failure.code = data.error;
            throw failure;
        }

        renderResults(data);

        message.style.color = '#30623c';
        message.textContent = data.message;
        registrationForm.hidden = true;
        initialStep.hidden = false;
        retryStep.hidden = true;
        modal.setAttribute('aria-labelledby', 'voteTitle');
        modal.setAttribute('aria-describedby', 'voteDescription');
        modal.querySelector('.close-modal').setAttribute('aria-label', dialogCopy.dialog_close_label);
        form.hidden = false;
        document.getElementById('voteMessage').style.color = '#30623c';
        document.getElementById('voteMessage').textContent = data.message;
        clearParticipationCode();
        document.getElementById('confirmVote').querySelector('span').textContent = dialogCopy.dialog_repeat_button;
        toast(data.message);
    } catch (error) {
        const failureMessage = error.code === 'invalid_participation_code' ? dialogCopy.dialog_retry_code_invalid
            : isRetry && error.code === 'participation_code_limit' ? dialogCopy.dialog_retry_code_limit : error.message;

        if (error.code === 'invalid_participation_code') {
            initialStep.hidden = true;
            retryStep.hidden = false;
            retryMessage.style.color = '#a33b40';
            retryMessage.textContent = failureMessage;
            modal.setAttribute('aria-labelledby', 'retryVoteTitle');
            modal.setAttribute('aria-describedby', 'retryVoteDescription');
            modal.querySelector('.close-modal').setAttribute('aria-label', dialogCopy.dialog_retry_close_label);
        }

        message.style.color = '#a33b40';
        message.textContent = failureMessage;
        state.submitting = false;
        confirmButton.disabled = false;
        confirmButton.querySelector('span').textContent = isRetry ? dialogCopy.dialog_retry_confirm_button : dialogCopy.dialog_confirm_button;
        clearParticipationCode();
        if (error.message) await loadResults();
    } finally {
        state.submitting = false;
        confirmButton.disabled = false;
        codeDigits.forEach(input => { input.disabled = false; });
        (retryStep.hidden ? form : retryForm).querySelector('.participation-digit').focus();
        updateButtons();
    }
}

form.addEventListener('submit', submitVote);
retryForm.addEventListener('submit', submitVote);

function normalize(text) { return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase(); }
function filterCandidates() {
    const sort = document.getElementById('candidateSort').value;
    const ordered = [...cards].sort((a, b) => {
        if (sort === 'votes') return (state.counts[b.dataset.id] || 0) - (state.counts[a.dataset.id] || 0) || Number(a.dataset.number) - Number(b.dataset.number);
        if (sort === 'number') return Number(a.dataset.number) - Number(b.dataset.number);
        return cards.indexOf(a) - cards.indexOf(b);
    });
    ordered.forEach(card => card.parentElement.append(card));
    const query = normalize(document.getElementById('candidateSearch').value.trim());
    let visible = 0;
    cards.forEach(card => {
        card.hidden = !normalize(card.dataset.name + ' ' + card.dataset.number + ' ' + card.dataset.hometown).includes(query);
        if (!card.hidden) visible++;
    });
    document.getElementById('emptyResults').hidden = visible > 0;
    document.getElementById('resultsCaption').textContent = visible + ' / ' + cards.length + ' thí sinh · Bình chọn cộng đồng';
}

document.getElementById('candidateSearch').addEventListener('input', filterCandidates);
document.getElementById('candidateSort').addEventListener('change', filterCandidates);

const menuToggle = document.querySelector('.menu-toggle');
const nav = document.getElementById('mainNav');
if (menuToggle) {
    menuToggle.addEventListener('click', () => menuToggle.setAttribute('aria-expanded', String(nav.classList.toggle('is-open'))));
}

updateButtons();
loadResults();