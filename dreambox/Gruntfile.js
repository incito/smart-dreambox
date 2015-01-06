module.exports = function(grunt){
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks); 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
		watch: {
		    css: {
		        files: ['addons/theme/stv1/_static/css/*.css'],
		        tasks: ['concat']
		    }
		},

		concat: {
		    css_common: {
		    	src: 'addons/theme/stv1/_static/css/common.css',
		    	dest: 'FE/html_new/assets/css/common.css'
		    },
		    css_person: {
		    	src: 'addons/theme/stv1/_static/css/person.css',
		    	dest: 'FE/html_new/assets/css/person.css'
		    },
		    css_pages: {
		    	src: 'addons/theme/stv1/_static/css/pages.css',
		    	dest: 'FE/html_new/assets/css/pages.css'
		    },
		    css_index:{
		    	src: 'addons/theme/stv1/_static/css/index.css',
		    	dest: 'FE/html_new/assets/css/index.css'
		    },
		    css_base:{
		    	src: 'addons/theme/stv1/_static/css/base.css',
		    	dest: 'FE/html_new/assets/css/base.css'
		    }
	  	}     
    });
	// 加载指定插件任务
    grunt.loadNpmTasks('grunt-contrib-watch');
  	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.registerTask('default', ['watch']);

};   