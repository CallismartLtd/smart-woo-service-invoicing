document.addEventListener('DOMContentLoaded', function () {
	let currentStep = 0;
	const steps = document.querySelectorAll('.smartwoo-step');
	const nextBtn = document.querySelector('.next-step');
	const prevBtn = document.querySelector('.prev-step');
	const finishBtn = document.querySelector('.finish-step');

	function showStep(index) {
		steps.forEach((step, i) => {
			step.style.display = i === index ? 'block' : 'none';
		});

		prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
		nextBtn.style.display = index === steps.length - 1 ? 'none' : 'inline-block';
		finishBtn.style.display = index === steps.length - 1 ? 'inline-block' : 'none';

		currentStep = index;
	}

	nextBtn.addEventListener('click', function () {
		if (currentStep < steps.length - 1) {
			showStep(currentStep + 1);
		}
	});

	prevBtn.addEventListener('click', function () {
		if (currentStep > 0) {
			showStep(currentStep - 1);
		}
	});

	// Initialize first step
	showStep(0);
});
