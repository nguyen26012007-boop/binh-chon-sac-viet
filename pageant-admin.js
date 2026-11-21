const editor = document.getElementById('editorForm');
const saveStatus = document.getElementById('saveStatus');
let dirty = false;
const previews = {contest_name:'previewName',hero_title:'previewTitle',hero_description:'previewDescription'};
editor.addEventListener('input', event => {
    dirty = true;
    saveStatus.textContent = 'Có thay đổi chưa lưu';
    saveStatus.style.color = '#a66a4d';
    if (previews[event.target.name]) {
        const target = document.getElementById(previews[event.target.name]);
        target.textContent = event.target.value.replace(/<br\s*\/?\s*>/gi, '\n');
        target.style.whiteSpace = 'pre-line';
    }
});
document.querySelectorAll('[data-photo]').forEach(input => {
    let imageUrl;
    input.addEventListener('change', () => {
        const preview = document.getElementById('photoPreview' + input.dataset.photo);
        const file = input.files[0];
        input.setCustomValidity('');
        if (imageUrl) URL.revokeObjectURL(imageUrl);
        if (!file) { preview.hidden = true; return; }
        if (!['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            input.setCustomValidity('Vui lòng chọn ảnh JPG, PNG hoặc WebP không quá 5 MB.');
            input.reportValidity();
            preview.hidden = true;
            return;
        }
        imageUrl = URL.createObjectURL(file);
        preview.src = imageUrl;
        preview.hidden = false;
    });
});
editor.addEventListener('submit', () => {
    dirty = false;
    document.getElementById('saveButton').disabled = true;
    saveStatus.textContent = 'Đang lưu thay đổi…';
});
window.addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
const links = [...document.querySelectorAll('.admin-sidebar a[href^="#"]')];
links.forEach(link => link.addEventListener('click', () => { links.forEach(item => item.classList.remove('active')); link.classList.add('active'); }));
