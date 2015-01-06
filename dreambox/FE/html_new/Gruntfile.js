module.exports = function(grunt){
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks); 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        htmlhint: {
		    build: {
		        options: {
		            'tag-pair': true,
		            'tagname-lowercase': true,
		            'attr-lowercase': true,
		            'attr-value-double-quotes': true,
		            'doctype-first': true,
		            'spec-char-escape': true,
		            'id-unique': true,
		            'head-script-disabled': true,
		            'style-disabled': true
		        },
		        src: ['reg.html']
		    }
		},
		connect: {
		    server:{ 
		     	options: {
			        port: 9001,
			        hostname: 'localhost', //默认就是这个值，可配置为本机某个 IP，localhost 或域名
			        livereload: true   //声明给 watch 监听的端口
			      }
			}
	    },
		watch: {
			livereload: {
		        options: {
		          livereload: true  //监听前面声明的端口  35729
		        },

		        files: [  //下面文件的改变就会实时刷新网页
		        	'*.html',
		        	'build/css/{,*/}*.css',
		          	'build/js/{,*/}*.js',
		          	'build/images/{,*/}*.{png,jpg,gif}'
		        ]
      		},
		    html: {
		        files: ['reg.html'],
		        tasks: ['htmlhint']
		    },
		    css: {
		        files: ['assets/css/*.css'],
		        tasks: ['concat']
		    },
		    css_build:{
		    	files: ['build/css/*.css'],
		        tasks: ['cssmin']
		    },
		    js: {
		        files: ['assets/js/*.js'],
		        tasks: ['uglify']
		    },
		    images:{
		    	files: ['assets/images/*.{png,jpg,gif}'],
		        tasks: ['imagemin']
		    }
		},

		uglify: {
		    //文件头部输出信息
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
                compress:false,
                beautify:true
            },
            //具体任务配置
	      	buildall: {
                files: [{
                    expand:true,
                    cwd:'assets/js',//js目录下
                    src:'**/*.js',//所有js文件
                    dest:'build/js',//输出到此目录下
                    ext: '.min.js'
                }]
            }
		},

		cssc: {
		    build: {
		        options: {
		            consolidateViaDeclarations: true,
		            consolidateViaSelectors:    true,
		            consolidateMediaQueries:    true
		        },
		        files: [{
		        	expand: true,        // 启用下面的选项
				    cwd: 'build/css/',    // 指定待压缩的文件路径
				    src: ['*.css'],    // 匹配相对于cwd目录下的所有css文件(排除.min.css文件)
				    dest: 'build/css/',    // 生成的压缩文件存放的路径
		        }]
		    }
		},

		cssmin: {
		    minify: {
			    expand: true,        // 启用下面的选项
			    cwd: 'build/css/',    // 指定待压缩的文件路径
			    src: ['*.css', '!*.min.css'],    // 匹配相对于cwd目录下的所有css文件(排除.min.css文件)
			    dest: 'build/css/',    // 生成的压缩文件存放的路径
			    ext: '.min.css'        // 生成的文件都使用.min.css替换原有扩展名，生成文件存放于dest指定的目录中
		    }
		},
		concat: {
		    css_pages: {
		    	src: ['assets/css/common.css', 'assets/css/pages.css'],
		    	dest: 'build/css/pages.con.css'
		    },
		    css_person: {
		    	src: ['assets/css/common.css', 'assets/css/person.css'],
		    	dest: 'build/css/person.con.css'
		    },
		    css_index: {
		    	src: ['assets/css/common.css', 'assets/css/index.css'],
		    	dest: 'build/css/index.con.css'
		    },
		    css_base:{
		    	src: ['assets/css/base.css'],
		    	dest: 'build/css/base.css'
		    },
		    s_pages:{
		    	src: 'assets/css/pages.css',
		    	dest: '../../addons/theme/stv1/_static/css/pages.css'
		    },
		    s_person:{
		    	src: 'assets/css/person.css',
		    	dest: '../../addons/theme/stv1/_static/css/person.css'
		    },
		    s_index:{
		    	src: 'assets/css/index.css',
		    	dest: '../../addons/theme/stv1/_static/css/index.css'
		    },
		    s_common:{
		    	src: 'assets/css/common.css',
		    	dest: '../../addons/theme/stv1/_static/css/common.css'
		    },
		    s_base:{
		    	src: 'assets/css/base.css',
		    	dest: '../../addons/theme/stv1/_static/css/base.css'
		    }
	  	},
		imagemin: {
		    png: {
		      options: {
		        optimizationLevel: 7
		      },
		      files: [
		        {
		          // Set to true to enable the following options…
		          expand: true,
		          // cwd is 'current working directory'
		          cwd: 'assets/images/',
		          src: ['**/*.png'],
		          // Could also match cwd line above. i.e. project-directory/img/
		          dest: 'build/images/',
		          ext: '.png'
		        }
		      ]
		    },
		    jpg: {
		      options: {
		        progressive: true
		      },
		      files: [
		        {
		          // Set to true to enable the following options…
		          expand: true,
		          // cwd is 'current working directory'
		          cwd: 'assets/images/',
		          src: ['**/*.jpg'],
		          // Could also match cwd. i.e. project-directory/img/
		          dest: 'build/images/',
		          ext: '.jpg'
		        }
		      ]
		    }
		}      
    });
	// 加载指定插件任务
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
  	grunt.loadNpmTasks('grunt-htmlhint');
  	grunt.loadNpmTasks('grunt-contrib-imagemin');
  	grunt.loadNpmTasks('grunt-livereload');
  	grunt.loadNpmTasks('grunt-contrib-concat');
    // 默认执行的任务
	grunt.registerTask('default', ['connect','watch']);

};