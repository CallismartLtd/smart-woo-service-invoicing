document.addEventListener('DOMContentLoaded', ()=>{
    let paymentInfo = document.querySelector('.sw-payment-link-container');
    let introDiv = document.querySelector('.sw-payment-intro');
    if (paymentInfo && introDiv) {
        introDiv.style.display = 'flex';
        introDiv.style.flexDirection = 'row';
        introDiv.style.justifyContent = 'space-evenly';
        paymentInfo.style.display = 'none';
        let toggleBtn = document.createElement('button');
        toggleBtn.textContent = 'Show';
        toggleBtn.style.width = '20%';
        toggleBtn.style.backgroundColor = '#ffffff';
        toggleBtn.style.border = 'solid blue 1px';
        toggleBtn.style.color = '#000000';
        toggleBtn.style.cursor = 'pointer';
        toggleBtn.style.borderRadius = '5px';
        toggleBtn.style.fontWeight = 'bold';
        introDiv.appendChild(toggleBtn);
        toggleBtn.addEventListener('click', ()=>{
            if ( 'Show' === toggleBtn.textContent ) {
                toggleBtn.textContent = 'Hide';
                paymentInfo.style.display = 'block';
            } else {
                toggleBtn.textContent = 'Show';
                paymentInfo.style.display = 'none';

            }
        });
    }
});