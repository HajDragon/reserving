import AOS from 'aos';
import 'aos/dist/aos.css';

const initAos = () => {
	if (document.querySelector('[data-aos]') === null) {
		return;
	}

	AOS.init({
		duration: 500,
		easing: 'ease-out-cubic',
		once: false,
		offset: 80,
	});
};

const refreshAos = () => {
	if (document.querySelector('[data-aos]') === null) {
		return;
	}

	AOS.refreshHard();
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initAos, { once: true });
} else {
	initAos();
}

document.addEventListener('livewire:navigated', () => {
	initAos();
	refreshAos();
});

document.addEventListener('products-appended', () => {
	refreshAos();
});

// ── Checkout confetti (school pride variant) ──────────────────────
const fireSchoolPrideConfetti = async () => {
	const { confetti } = await import("@tsparticles/confetti");

	// Two bursts from the bottom corners — classic school pride effect
	const defaults = {
		spread: 360,
		ticks: 100,
		gravity: 0,
		decay: 0.94,
		startVelocity: 20,
		shapes: ["square"],
		colors: ["#ff6b6b", "#ffd93d", "#6bcb77", "#4d96ff", "#ff922b"],
	};

	await Promise.all([
		confetti({
			...defaults,
			particleCount: 40,
			scalar: 1.2,
			shapes: ["square"],
			emitters: [
				{
					direction: "top-right",
					position: { x: 0, y: 100 },
					rate: { delay: 0, quantity: 2 },
					particles: {
						move: {
							speed: 10,
							angle: { min: -45, max: -30 },
						},
					},
				},
				{
					direction: "top-left",
					position: { x: 100, y: 100 },
					rate: { delay: 0, quantity: 2 },
					particles: {
						move: {
							speed: 10,
							angle: { min: 210, max: 225 },
						},
					},
				},
			],
		}),
	]);
};

// Trigger confetti when the checkout success flash is visible
const triggerCheckoutConfetti = () => {
	const el = document.querySelector('[data-checkout-success]');
	if (el) {
		el.removeAttribute('data-checkout-success');
		fireSchoolPrideConfetti().catch((err) => {
			console.error('Confetti failed', err);
		});
	}
};

document.addEventListener('DOMContentLoaded', triggerCheckoutConfetti);
document.addEventListener('livewire:navigated', () => {
	setTimeout(triggerCheckoutConfetti, 200);
});

const loginParticlesElement = document.getElementById("login-tsparticles");

if (loginParticlesElement) {
	const initLoginParticles = async () => {
		const [{ tsParticles }, { loadTrianglesPreset }] = await Promise.all([
			import("@tsparticles/engine"),
			import("@tsparticles/preset-triangles"),
		]);

		await loadTrianglesPreset(tsParticles);

		await tsParticles.load({
			id: "login-tsparticles",
			options: {
				preset: "triangles",
				fullScreen: {
					enable: false,
					zIndex: 5,
				},
				background: {
					color: {
						value: "transparent",
					},
				},
				fpsLimit: 60,
				detectRetina: true,
				particles: {
					number: {
						value: 70,
						density: {
							enable: true,
							area: 800,
						},
					},
					move: {
						enable: true,
						speed: 1.75,
					},
					color: {
						value: ["#0ea5e9", "rgb(187, 50, 187)", "#245"],
					},
					opacity: {
						value: 0.7,
					},
					links: {
						enable: true,
						distance: 130,
						opacity: 0.45,
						width: 1,
						color: "#424",
						triangles: {
							enable: true,
							opacity: 0.05,
						},
					},
				},
				interactivity: {
					detectsOn: "window",
					events: {
						onHover: {
							enable: true,
							mode: "repulse",
						},
						resize: {
							enable: true,
						},
					},
					modes: {
						repulse: {
							distance: 45,
							duration: 0.35,
						},
					},
				},
			},
		});
	};

	initLoginParticles().catch((error) => {
		console.error("Failed to load login particles", error);
	});
}
