var charts={
	isAjax:false,//请求数据状态
	bot:0.1,//请求数据高度界限
	scrollH:0,//上次滚动高度
	scrollTop:$('.map-con-div').scrollTop(),//window top
	wHeight:$('.map-con-div').height(),//窗口高度
	dHeight:$('.map-con-ul').height(),//文档高度
	curIndx: 0,
	mapType : [
		    'china',
		    // 23个省
		    '广东', '青海', '四川', '海南', '陕西', 
		    '甘肃', '云南', '湖南', '湖北', '黑龙江',
		    '贵州', '山东', '江西', '河南', '河北',
		    '山西', '安徽', '福建', '浙江', '江苏', 
		    '吉林', '辽宁', '台湾',
		    // 5个自治区
		    '新疆', '广西', '宁夏', '内蒙古', '西藏', 
		    // 4个直辖市
		    '北京', '天津', '上海', '重庆',
		    // 2个特别行政区
		    '香港', '澳门'
		],
	chartConfig: function (container, option) { //加载Echarts配置文件,container:为页面要渲染图表的容器，option为已经初始化好的图表类型的option配置
        var chart_path = THEME_URL+"/js/echarts/echarts"; //配置图表请求路径
        var map_path = THEME_URL+"/js/echarts/echarts-map";//配置地图的请求路径
        require.config({//引入常用的图表类型的配置
            paths: {
                echarts: chart_path,
                'echarts/chart/map': map_path
            }
        });
        this.option = { chart: {}, option: option, container: container};
        return this.option;
 
    },
    chartOptionTemplates: function(){//初始化常用的图表类型
    	var MapOption = {
		    title: {
		        text : '全国34个省市自治区',
		        subtext : 'china'
		    },
		    tooltip : {
		        trigger: 'item',
		        formatter: '{b}'
		    },
		    backgroundColor:'#fff',
		    series : [
		        {
		            name: '随机数据',
		            type: 'map',
		            mapType: 'china',
		            selectedMode : 'single',
		            itemStyle:{
		                normal:{label:{show:true}},
		                emphasis:{label:{show:true}}
		            },
		            data:[]
		        }
		    ]
		}
		return MapOption;

    },
    renderMap: function (option) {//渲染地图
		require([
			'echarts',
			'echarts/chart/map'
		],
		function (ec) {
				echarts = ec;
			if (option.chart && option.chart.dispose){//释放地图实例
				option.chart.dispose();
			}
			option.chart = echarts.init(option.container);
			window.onresize = option.chart.resize;
			option.chart.on('mapSelected', function (param){//点击地图
			    var len = charts.mapType.length;
			    var mt = charts.mapType[charts.curIndx % len];
			    var param1 = {
			    	province:'',
			    	city:''
			    }
			    
			    if (mt == 'china') {
			        // 全国选择时指定到选中的省份
			        var selected = param.selected;
			        for (var i in selected) {
			            if (selected[i]) {
			                mt = i;
			                while (len--) {
			                    if (charts.mapType[len] == mt) {
			                        charts.curIndx = len;
			                    }
			                }
			                break;
			            }
			        }
			        option.option.tooltip.formatter = '{b}';
			        param1.province = charts.mapType[charts.curIndx];
			    }
			    else {
			    	//选中市区
			        option.option.tooltip.formatter = '{b}';
			        param1.province = mt;
			        param1.city = param.target;
			    }

			    option.option.series[0].mapType = mt;
			    option.option.title.subtext = mt;
			    charts.getContent(param1,option);
			    //option.chart.setOption(option.option, true);
			});
			option.chart.setOption(option.option, true);
			$('.map-title').on('click', function(event) {//点击地图导航
				charts.mapTitleClick(option,event);
			});
			$('.map-search i.search').on('click', function(event) {//搜索
				charts.search();
			});
			$('#map-search-input').on('keyup', function(event) {//回车
				if(event.which == 108 || event.which == 13){
					charts.search(false);
				}
			});
			$('.map-con-div').scroll(function(event) {
				/* Act on the event */
				charts.scrollTop = $('.map-con-div').scrollTop();
				charts.wHeight = $('.map-con-div').height();
				charts.dHeight = $('.map-con-ul').height();
				if(charts.isAjax) return;
				if((charts.scrollTop+charts.bot*charts.dHeight) >= (charts.dHeight - charts.wHeight) && charts.scrollTop > charts.scrollH){
					charts.search(true);
				}

			});
		});
	},
	search:function(isScroll){
		var val = $('#map-search-input').val(),
			_ta = $('.map-title a'),
			pro = $('.map-title a').size() == 1?'全国':$('.map-title a').eq(1).text(),
			city = $('.map-title a').size() == 3?$('.map-title a').eq(2).text():'';
			param = {
				province:  pro,
				city: city,
				key: val,
				start:0
			};
			if(isScroll){
				param.start = $('.map-con-ul li').size();
			}
			charts.searchCon(param,isScroll);
	},
	searchCon:function(param,isScroll){
		$('.map-con-loading').fadeIn(400);
		charts.isAjax = true;
		$.ajax({
            url: U('dreambox/DreamCenter/filterArea'),
            data: param,
            type:"get",
            dataType:'json',
            success: function(json){
            	charts.insertContent(json,true,isScroll);
            },
            error:function(err){
				console.log('加载数据失败,请重试!');
            },
            complete:function(ss){
            	$('.map-con-loading').fadeOut(400);
            	charts.isAjax = false;
            }
        });
	},
	mapTitleClick:function(option,event){//地图导航点击事件
		var _this = $(event.target),param2;
			event.preventDefault();
			if(_this.is('a')){
				if(_this.hasClass('country')){
					param2 = {province:'全国'}
					option.option.series[0].mapType = 'china';
		    		option.option.title.subtext = '全国';
		    		charts.curIndx = 0;
				}else if(_this.hasClass('pro')){
	                var mt = _this.text(),
	                	len = charts.mapType.length;
	                while (len--) {
	                    if (charts.mapType[len] == mt) {
	                        charts.curIndx = len;
	                    }
	                }

	                option.chart.chart.map._selected[$('.map-title a.city').text()] = undefined;
					param2 = {province:_this.text()}
					option.option.series[0].mapType = _this.text();
					option.option.title.subtext = _this.text();
				}else{
					return;
				}
		    	charts.getContent(param2,option);
			}
	},
	getContent:function(param,option){
		option.chart.showLoading({//loading状态
		    text : '加载中...',
		    effect : 'spin',
		    textStyle : {
		        fontSize : 20
		    }
		});
		charts.isAjax = true;
		$.ajax({
            url: U('dreambox/DreamCenter/filterArea'),
            data: param,
            type:"get",
            dataType:'json',
            success: function(json){
            	if(json.status==0){
            		dreambox.alert(json.info);
            		return;
            	}
            	var str = '当前地区：<a href="javascript:;" class="cblue country">全国</a>';
            	charts.insertContent(json,false,false);
            	option.chart.setOption(option.option, true);
            	if(param.province != null && param.province != '' &&param.province != '全国'){//具有省份
            		str +='<span>&gt;</span><a href="javascript:;" class="cblue pro">'+param.province+'</a>';
            	}
            	if(param.city != null && param.city != ''){//具有市区
            		str +='<span>&gt;</span><a href="javascript:;" class="cblue city">'+param.city+'</a>';
            	}
            	$('.map-title').empty().append(str);
            	$('#map-search-input').val('');
            },
            error:function(err){
				console.log('加载数据失败,请重试!');
            },
            complete:function(ss){
            	option.chart.hideLoading();//关闭loading
            	charts.isAjax = false;
            }
        });
	},
	insertContent:function(json,isSearch,isScroll){
		var countInfo = json.data.countInfo,
			schoolInfo = new Array(),
			str = '';
		schoolInfo = json.data.schoolInfo;
		//if(!isSearch){//在搜索时不更改统计数据
			$('#mxzx').text(countInfo[0].count==null?0:countInfo[0].count);
			$('#mxjs').text(countInfo[0].tsum==null?0:countInfo[0].tsum);
			$('#syxs').text(countInfo[0].ssum==null?0:countInfo[0].ssum);
		//}
		for (var i = 0; i < schoolInfo.length; i++) {
			str += '<li class="'+(i%2==0?'odd':'')+'">';
			str += '<h6><a href="'+U('public/Profile/index')+'&uid='+schoolInfo[i].uid+'">'+schoolInfo[i].name+'</a></h6>';
			str += '<p>捐赠方：'+schoolInfo[i].sponsors+'</p>';
			str += '</li>';			
		};
		if(!isScroll){//在滚动时不删除原数据
			$('.map-con-ul').empty();
		}
		$('.map-con-ul').append(str);
	}
}