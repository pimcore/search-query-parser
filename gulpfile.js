var gulp = require('gulp');

require('project-semver')(gulp, 'composer.json', {
    files: ['package.json']
});
