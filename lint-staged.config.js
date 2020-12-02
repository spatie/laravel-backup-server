module.exports = {
    'resources/{css,js}/**/*.{css,js}': ['prettier --write', 'git add'],
    'resources/**/*.{css,js}?(x)': () => ['yarn production', 'git add resources/dist'],
};
