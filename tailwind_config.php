<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css" rel="stylesheet" type="text/css" />
<script>
	tailwind.config = {
		theme: {
			extend: {
				colors: {
					clifford: "#da373d",
				},
				fontFamily: {
					"neues-grotesque": ['"Neues Grotesque"', "sans-serif"],
				},
			},
		},
		plugins: [require("daisyui")],
		daisyui: {
			themes: ['dark'],
			base: true,
			styled: true,
			utils: true,
			prefix: "",
			logs: true,
			themeRoot: ":root",
		}
	};
</script>
<link rel="stylesheet" href="/styles/style.css" />