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
