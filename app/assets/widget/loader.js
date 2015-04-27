(function() {
/*
 * This file will be concatenated with all other source files, so config
 * becomes a global as far as all other files are concerned. Then we wrap
 * everything in an IIFE, so it doens't become a global in the browser.
 */

var config, exports, namespace;

// allow overriding default app host
var appHost = '//localhost:5000';
if ( typeof window !== 'undefined' && typeof window.simplechartAppHost !== 'undefined' ){
	appHost = window.simplechartAppHost;
}

config = {
  namespace: 'SimplechartGlobal',
  baseUrl: appHost + '/assets/widget'
};

if (typeof window !== 'undefined') {
  exports = window;
  if (!exports[config.namespace]) {
    exports[config.namespace] = {};
  }
  namespace = exports[config.namespace];
}

// This is so we can require() it in grunt.js
if (typeof module !== 'undefined') {
  module.exports = config;
}

var requirejs,require,define;!function(global){function isFunction(it){return"[object Function]"===ostring.call(it)}function isArray(it){return"[object Array]"===ostring.call(it)}function each(ary,func){if(ary){var i;for(i=0;i<ary.length&&(!ary[i]||!func(ary[i],i,ary));i+=1);}}function eachReverse(ary,func){if(ary){var i;for(i=ary.length-1;i>-1&&(!ary[i]||!func(ary[i],i,ary));i-=1);}}function hasProp(obj,prop){return hasOwn.call(obj,prop)}function getOwn(obj,prop){return hasProp(obj,prop)&&obj[prop]}function eachProp(obj,func){var prop;for(prop in obj)if(hasProp(obj,prop)&&func(obj[prop],prop))break}function mixin(target,source,force,deepStringMixin){return source&&eachProp(source,function(value,prop){(force||!hasProp(target,prop))&&(deepStringMixin&&"string"!=typeof value?(target[prop]||(target[prop]={}),mixin(target[prop],value,force,deepStringMixin)):target[prop]=value)}),target}function bind(obj,fn){return function(){return fn.apply(obj,arguments)}}function scripts(){return document.getElementsByTagName("script")}function getGlobal(value){if(!value)return value;var g=global;return each(value.split("."),function(part){g=g[part]}),g}function makeError(id,msg,err,requireModules){var e=new Error(msg+"\nhttp://requirejs.org/docs/errors.html#"+id);return e.requireType=id,e.requireModules=requireModules,err&&(e.originalError=err),e}function newContext(contextName){function trimDots(ary){var i,part;for(i=0;ary[i];i+=1)if(part=ary[i],"."===part)ary.splice(i,1),i-=1;else if(".."===part){if(1===i&&(".."===ary[2]||".."===ary[0]))break;i>0&&(ary.splice(i-1,2),i-=2)}}function normalize(name,baseName,applyMap){var pkgName,pkgConfig,mapValue,nameParts,i,j,nameSegment,foundMap,foundI,foundStarMap,starI,baseParts=baseName&&baseName.split("/"),normalizedBaseParts=baseParts,map=config.map,starMap=map&&map["*"];if(name&&"."===name.charAt(0)&&(baseName?(normalizedBaseParts=getOwn(config.pkgs,baseName)?baseParts=[baseName]:baseParts.slice(0,baseParts.length-1),name=normalizedBaseParts.concat(name.split("/")),trimDots(name),pkgConfig=getOwn(config.pkgs,pkgName=name[0]),name=name.join("/"),pkgConfig&&name===pkgName+"/"+pkgConfig.main&&(name=pkgName)):0===name.indexOf("./")&&(name=name.substring(2))),applyMap&&(baseParts||starMap)&&map){for(nameParts=name.split("/"),i=nameParts.length;i>0;i-=1){if(nameSegment=nameParts.slice(0,i).join("/"),baseParts)for(j=baseParts.length;j>0;j-=1)if(mapValue=getOwn(map,baseParts.slice(0,j).join("/")),mapValue&&(mapValue=getOwn(mapValue,nameSegment))){foundMap=mapValue,foundI=i;break}if(foundMap)break;!foundStarMap&&starMap&&getOwn(starMap,nameSegment)&&(foundStarMap=getOwn(starMap,nameSegment),starI=i)}!foundMap&&foundStarMap&&(foundMap=foundStarMap,foundI=starI),foundMap&&(nameParts.splice(0,foundI,foundMap),name=nameParts.join("/"))}return name}function removeScript(name){isBrowser&&each(scripts(),function(scriptNode){return scriptNode.getAttribute("data-requiremodule")===name&&scriptNode.getAttribute("data-requirecontext")===context.contextName?(scriptNode.parentNode.removeChild(scriptNode),!0):void 0})}function hasPathFallback(id){var pathConfig=getOwn(config.paths,id);return pathConfig&&isArray(pathConfig)&&pathConfig.length>1?(removeScript(id),pathConfig.shift(),context.require.undef(id),context.require([id]),!0):void 0}function splitPrefix(name){var prefix,index=name?name.indexOf("!"):-1;return index>-1&&(prefix=name.substring(0,index),name=name.substring(index+1,name.length)),[prefix,name]}function makeModuleMap(name,parentModuleMap,isNormalized,applyMap){var url,pluginModule,suffix,nameParts,prefix=null,parentName=parentModuleMap?parentModuleMap.name:null,originalName=name,isDefine=!0,normalizedName="";return name||(isDefine=!1,name="_@r"+(requireCounter+=1)),nameParts=splitPrefix(name),prefix=nameParts[0],name=nameParts[1],prefix&&(prefix=normalize(prefix,parentName,applyMap),pluginModule=getOwn(defined,prefix)),name&&(prefix?normalizedName=pluginModule&&pluginModule.normalize?pluginModule.normalize(name,function(name){return normalize(name,parentName,applyMap)}):normalize(name,parentName,applyMap):(normalizedName=normalize(name,parentName,applyMap),nameParts=splitPrefix(normalizedName),prefix=nameParts[0],normalizedName=nameParts[1],isNormalized=!0,url=context.nameToUrl(normalizedName))),suffix=!prefix||pluginModule||isNormalized?"":"_unnormalized"+(unnormalizedCounter+=1),{prefix:prefix,name:normalizedName,parentMap:parentModuleMap,unnormalized:!!suffix,url:url,originalName:originalName,isDefine:isDefine,id:(prefix?prefix+"!"+normalizedName:normalizedName)+suffix}}function getModule(depMap){var id=depMap.id,mod=getOwn(registry,id);return mod||(mod=registry[id]=new context.Module(depMap)),mod}function on(depMap,name,fn){var id=depMap.id,mod=getOwn(registry,id);!hasProp(defined,id)||mod&&!mod.defineEmitComplete?getModule(depMap).on(name,fn):"defined"===name&&fn(defined[id])}function onError(err,errback){var ids=err.requireModules,notified=!1;errback?errback(err):(each(ids,function(id){var mod=getOwn(registry,id);mod&&(mod.error=err,mod.events.error&&(notified=!0,mod.emit("error",err)))}),notified||req.onError(err))}function takeGlobalQueue(){globalDefQueue.length&&(apsp.apply(defQueue,[defQueue.length-1,0].concat(globalDefQueue)),globalDefQueue=[])}function cleanRegistry(id){delete registry[id]}function breakCycle(mod,traced,processed){var id=mod.map.id;mod.error?mod.emit("error",mod.error):(traced[id]=!0,each(mod.depMaps,function(depMap,i){var depId=depMap.id,dep=getOwn(registry,depId);!dep||mod.depMatched[i]||processed[depId]||(getOwn(traced,depId)?(mod.defineDep(i,defined[depId]),mod.check()):breakCycle(dep,traced,processed))}),processed[id]=!0)}function checkLoaded(){var map,modId,err,usingPathFallback,waitInterval=1e3*config.waitSeconds,expired=waitInterval&&context.startTime+waitInterval<(new Date).getTime(),noLoads=[],reqCalls=[],stillLoading=!1,needCycleCheck=!0;if(!inCheckLoaded){if(inCheckLoaded=!0,eachProp(registry,function(mod){if(map=mod.map,modId=map.id,mod.enabled&&(map.isDefine||reqCalls.push(mod),!mod.error))if(!mod.inited&&expired)hasPathFallback(modId)?(usingPathFallback=!0,stillLoading=!0):(noLoads.push(modId),removeScript(modId));else if(!mod.inited&&mod.fetched&&map.isDefine&&(stillLoading=!0,!map.prefix))return needCycleCheck=!1}),expired&&noLoads.length)return err=makeError("timeout","Load timeout for modules: "+noLoads,null,noLoads),err.contextName=context.contextName,onError(err);needCycleCheck&&each(reqCalls,function(mod){breakCycle(mod,{},{})}),expired&&!usingPathFallback||!stillLoading||!isBrowser&&!isWebWorker||checkLoadedTimeoutId||(checkLoadedTimeoutId=setTimeout(function(){checkLoadedTimeoutId=0,checkLoaded()},50)),inCheckLoaded=!1}}function callGetModule(args){hasProp(defined,args[0])||getModule(makeModuleMap(args[0],null,!0)).init(args[1],args[2])}function removeListener(node,func,name,ieName){node.detachEvent&&!isOpera?ieName&&node.detachEvent(ieName,func):node.removeEventListener(name,func,!1)}function getScriptData(evt){var node=evt.currentTarget||evt.srcElement;return removeListener(node,context.onScriptLoad,"load","onreadystatechange"),removeListener(node,context.onScriptError,"error"),{node:node,id:node&&node.getAttribute("data-requiremodule")}}function intakeDefines(){var args;for(takeGlobalQueue();defQueue.length;){if(args=defQueue.shift(),null===args[0])return onError(makeError("mismatch","Mismatched anonymous define() module: "+args[args.length-1]));callGetModule(args)}}var inCheckLoaded,Module,context,handlers,checkLoadedTimeoutId,config={waitSeconds:7,baseUrl:"./",paths:{},pkgs:{},shim:{},map:{},config:{}},registry={},undefEvents={},defQueue=[],defined={},urlFetched={},requireCounter=1,unnormalizedCounter=1;return handlers={require:function(mod){return mod.require?mod.require:mod.require=context.makeRequire(mod.map)},exports:function(mod){return mod.usingExports=!0,mod.map.isDefine?mod.exports?mod.exports:mod.exports=defined[mod.map.id]={}:void 0},module:function(mod){return mod.module?mod.module:mod.module={id:mod.map.id,uri:mod.map.url,config:function(){return config.config&&getOwn(config.config,mod.map.id)||{}},exports:defined[mod.map.id]}}},Module=function(map){this.events=getOwn(undefEvents,map.id)||{},this.map=map,this.shim=getOwn(config.shim,map.id),this.depExports=[],this.depMaps=[],this.depMatched=[],this.pluginMaps={},this.depCount=0},Module.prototype={init:function(depMaps,factory,errback,options){options=options||{},this.inited||(this.factory=factory,errback?this.on("error",errback):this.events.error&&(errback=bind(this,function(err){this.emit("error",err)})),this.depMaps=depMaps&&depMaps.slice(0),this.errback=errback,this.inited=!0,this.ignore=options.ignore,options.enabled||this.enabled?this.enable():this.check())},defineDep:function(i,depExports){this.depMatched[i]||(this.depMatched[i]=!0,this.depCount-=1,this.depExports[i]=depExports)},fetch:function(){if(!this.fetched){this.fetched=!0,context.startTime=(new Date).getTime();var map=this.map;return this.shim?void context.makeRequire(this.map,{enableBuildCallback:!0})(this.shim.deps||[],bind(this,function(){return map.prefix?this.callPlugin():this.load()})):map.prefix?this.callPlugin():this.load()}},load:function(){var url=this.map.url;urlFetched[url]||(urlFetched[url]=!0,context.load(this.map.id,url))},check:function(){if(this.enabled&&!this.enabling){var err,cjsModule,id=this.map.id,depExports=this.depExports,exports=this.exports,factory=this.factory;if(this.inited){if(this.error)this.emit("error",this.error);else if(!this.defining){if(this.defining=!0,this.depCount<1&&!this.defined){if(isFunction(factory)){if(this.events.error)try{exports=context.execCb(id,factory,depExports,exports)}catch(e){err=e}else exports=context.execCb(id,factory,depExports,exports);if(this.map.isDefine&&(cjsModule=this.module,cjsModule&&void 0!==cjsModule.exports&&cjsModule.exports!==this.exports?exports=cjsModule.exports:void 0===exports&&this.usingExports&&(exports=this.exports)),err)return err.requireMap=this.map,err.requireModules=[this.map.id],err.requireType="define",onError(this.error=err)}else exports=factory;this.exports=exports,this.map.isDefine&&!this.ignore&&(defined[id]=exports,req.onResourceLoad&&req.onResourceLoad(context,this.map,this.depMaps)),delete registry[id],this.defined=!0}this.defining=!1,this.defined&&!this.defineEmitted&&(this.defineEmitted=!0,this.emit("defined",this.exports),this.defineEmitComplete=!0)}}else this.fetch()}},callPlugin:function(){var map=this.map,id=map.id,pluginMap=makeModuleMap(map.prefix);this.depMaps.push(pluginMap),on(pluginMap,"defined",bind(this,function(plugin){var load,normalizedMap,normalizedMod,name=this.map.name,parentName=this.map.parentMap?this.map.parentMap.name:null,localRequire=context.makeRequire(map.parentMap,{enableBuildCallback:!0});return this.map.unnormalized?(plugin.normalize&&(name=plugin.normalize(name,function(name){return normalize(name,parentName,!0)})||""),normalizedMap=makeModuleMap(map.prefix+"!"+name,this.map.parentMap),on(normalizedMap,"defined",bind(this,function(value){this.init([],function(){return value},null,{enabled:!0,ignore:!0})})),normalizedMod=getOwn(registry,normalizedMap.id),void(normalizedMod&&(this.depMaps.push(normalizedMap),this.events.error&&normalizedMod.on("error",bind(this,function(err){this.emit("error",err)})),normalizedMod.enable()))):(load=bind(this,function(value){this.init([],function(){return value},null,{enabled:!0})}),load.error=bind(this,function(err){this.inited=!0,this.error=err,err.requireModules=[id],eachProp(registry,function(mod){0===mod.map.id.indexOf(id+"_unnormalized")&&cleanRegistry(mod.map.id)}),onError(err)}),load.fromText=bind(this,function(text,textAlt){var moduleName=map.name,moduleMap=makeModuleMap(moduleName),hasInteractive=useInteractive;textAlt&&(text=textAlt),hasInteractive&&(useInteractive=!1),getModule(moduleMap),hasProp(config.config,id)&&(config.config[moduleName]=config.config[id]);try{req.exec(text)}catch(e){return onError(makeError("fromtexteval","fromText eval for "+id+" failed: "+e,e,[id]))}hasInteractive&&(useInteractive=!0),this.depMaps.push(moduleMap),context.completeLoad(moduleName),localRequire([moduleName],load)}),void plugin.load(map.name,localRequire,load,config))})),context.enable(pluginMap,this),this.pluginMaps[pluginMap.id]=pluginMap},enable:function(){this.enabled=!0,this.enabling=!0,each(this.depMaps,bind(this,function(depMap,i){var id,mod,handler;if("string"==typeof depMap){if(depMap=makeModuleMap(depMap,this.map.isDefine?this.map:this.map.parentMap,!1,!this.skipMap),this.depMaps[i]=depMap,handler=getOwn(handlers,depMap.id))return void(this.depExports[i]=handler(this));this.depCount+=1,on(depMap,"defined",bind(this,function(depExports){this.defineDep(i,depExports),this.check()})),this.errback&&on(depMap,"error",this.errback)}id=depMap.id,mod=registry[id],hasProp(handlers,id)||!mod||mod.enabled||context.enable(depMap,this)})),eachProp(this.pluginMaps,bind(this,function(pluginMap){var mod=getOwn(registry,pluginMap.id);mod&&!mod.enabled&&context.enable(pluginMap,this)})),this.enabling=!1,this.check()},on:function(name,cb){var cbs=this.events[name];cbs||(cbs=this.events[name]=[]),cbs.push(cb)},emit:function(name,evt){each(this.events[name],function(cb){cb(evt)}),"error"===name&&delete this.events[name]}},context={config:config,contextName:contextName,registry:registry,defined:defined,urlFetched:urlFetched,defQueue:defQueue,Module:Module,makeModuleMap:makeModuleMap,nextTick:req.nextTick,configure:function(cfg){cfg.baseUrl&&"/"!==cfg.baseUrl.charAt(cfg.baseUrl.length-1)&&(cfg.baseUrl+="/");var pkgs=config.pkgs,shim=config.shim,objs={paths:!0,config:!0,map:!0};eachProp(cfg,function(value,prop){objs[prop]?"map"===prop?mixin(config[prop],value,!0,!0):mixin(config[prop],value,!0):config[prop]=value}),cfg.shim&&(eachProp(cfg.shim,function(value,id){isArray(value)&&(value={deps:value}),!value.exports&&!value.init||value.exportsFn||(value.exportsFn=context.makeShimExports(value)),shim[id]=value}),config.shim=shim),cfg.packages&&(each(cfg.packages,function(pkgObj){var location;pkgObj="string"==typeof pkgObj?{name:pkgObj}:pkgObj,location=pkgObj.location,pkgs[pkgObj.name]={name:pkgObj.name,location:location||pkgObj.name,main:(pkgObj.main||"main").replace(currDirRegExp,"").replace(jsSuffixRegExp,"")}}),config.pkgs=pkgs),eachProp(registry,function(mod,id){mod.inited||mod.map.unnormalized||(mod.map=makeModuleMap(id))}),(cfg.deps||cfg.callback)&&context.require(cfg.deps||[],cfg.callback)},makeShimExports:function(value){function fn(){var ret;return value.init&&(ret=value.init.apply(global,arguments)),ret||value.exports&&getGlobal(value.exports)}return fn},makeRequire:function(relMap,options){function localRequire(deps,callback,errback){var id,map,requireMod;return options.enableBuildCallback&&callback&&isFunction(callback)&&(callback.__requireJsBuild=!0),"string"==typeof deps?isFunction(callback)?onError(makeError("requireargs","Invalid require call"),errback):relMap&&hasProp(handlers,deps)?handlers[deps](registry[relMap.id]):req.get?req.get(context,deps,relMap):(map=makeModuleMap(deps,relMap,!1,!0),id=map.id,hasProp(defined,id)?defined[id]:onError(makeError("notloaded",'Module name "'+id+'" has not been loaded yet for context: '+contextName+(relMap?"":". Use require([])")))):(intakeDefines(),context.nextTick(function(){intakeDefines(),requireMod=getModule(makeModuleMap(null,relMap)),requireMod.skipMap=options.skipMap,requireMod.init(deps,callback,errback,{enabled:!0}),checkLoaded()}),localRequire)}return options=options||{},mixin(localRequire,{isBrowser:isBrowser,toUrl:function(moduleNamePlusExt){var ext,url,index=moduleNamePlusExt.lastIndexOf("."),segment=moduleNamePlusExt.split("/")[0],isRelative="."===segment||".."===segment;return-1!==index&&(!isRelative||index>1)&&(ext=moduleNamePlusExt.substring(index,moduleNamePlusExt.length),moduleNamePlusExt=moduleNamePlusExt.substring(0,index)),url=context.nameToUrl(normalize(moduleNamePlusExt,relMap&&relMap.id,!0),ext||".fake"),ext?url:url.substring(0,url.length-5)},defined:function(id){return hasProp(defined,makeModuleMap(id,relMap,!1,!0).id)},specified:function(id){return id=makeModuleMap(id,relMap,!1,!0).id,hasProp(defined,id)||hasProp(registry,id)}}),relMap||(localRequire.undef=function(id){takeGlobalQueue();var map=makeModuleMap(id,relMap,!0),mod=getOwn(registry,id);delete defined[id],delete urlFetched[map.url],delete undefEvents[id],mod&&(mod.events.defined&&(undefEvents[id]=mod.events),cleanRegistry(id))}),localRequire},enable:function(depMap){var mod=getOwn(registry,depMap.id);mod&&getModule(depMap).enable()},completeLoad:function(moduleName){var found,args,mod,shim=getOwn(config.shim,moduleName)||{},shExports=shim.exports;for(takeGlobalQueue();defQueue.length;){if(args=defQueue.shift(),null===args[0]){if(args[0]=moduleName,found)break;found=!0}else args[0]===moduleName&&(found=!0);callGetModule(args)}if(mod=getOwn(registry,moduleName),!found&&!hasProp(defined,moduleName)&&mod&&!mod.inited){if(!(!config.enforceDefine||shExports&&getGlobal(shExports)))return hasPathFallback(moduleName)?void 0:onError(makeError("nodefine","No define call for "+moduleName,null,[moduleName]));callGetModule([moduleName,shim.deps||[],shim.exportsFn])}checkLoaded()},nameToUrl:function(moduleName,ext){var paths,pkgs,pkg,pkgPath,syms,i,parentModule,url,parentPath;if(req.jsExtRegExp.test(moduleName))url=moduleName+(ext||"");else{for(paths=config.paths,pkgs=config.pkgs,syms=moduleName.split("/"),i=syms.length;i>0;i-=1){if(parentModule=syms.slice(0,i).join("/"),pkg=getOwn(pkgs,parentModule),parentPath=getOwn(paths,parentModule)){isArray(parentPath)&&(parentPath=parentPath[0]),syms.splice(0,i,parentPath);break}if(pkg){pkgPath=moduleName===pkg.name?pkg.location+"/"+pkg.main:pkg.location,syms.splice(0,i,pkgPath);break}}url=syms.join("/"),url+=ext||(/\?/.test(url)?"":".js"),url=("/"===url.charAt(0)||url.match(/^[\w\+\.\-]+:/)?"":config.baseUrl)+url}return config.urlArgs?url+((-1===url.indexOf("?")?"?":"&")+config.urlArgs):url},load:function(id,url){req.load(context,id,url)},execCb:function(name,callback,args,exports){return callback.apply(exports,args)},onScriptLoad:function(evt){if("load"===evt.type||readyRegExp.test((evt.currentTarget||evt.srcElement).readyState)){interactiveScript=null;var data=getScriptData(evt);context.completeLoad(data.id)}},onScriptError:function(evt){var data=getScriptData(evt);return hasPathFallback(data.id)?void 0:onError(makeError("scripterror","Script error",evt,[data.id]))}},context.require=context.makeRequire(),context}function getInteractiveScript(){return interactiveScript&&"interactive"===interactiveScript.readyState?interactiveScript:(eachReverse(scripts(),function(script){return"interactive"===script.readyState?interactiveScript=script:void 0}),interactiveScript)}var req,s,head,baseElement,dataMain,src,interactiveScript,currentlyAddingScript,mainScript,subPath,version="2.1.4",commentRegExp=/(\/\*([\s\S]*?)\*\/|([^:]|^)\/\/(.*)$)/gm,cjsRequireRegExp=/[^.]\s*require\s*\(\s*["']([^'"\s]+)["']\s*\)/g,jsSuffixRegExp=/\.js$/,currDirRegExp=/^\.\//,op=Object.prototype,ostring=op.toString,hasOwn=op.hasOwnProperty,ap=Array.prototype,apsp=ap.splice,isBrowser=!("undefined"==typeof window||!navigator||!document),isWebWorker=!isBrowser&&"undefined"!=typeof importScripts,readyRegExp=isBrowser&&"PLAYSTATION 3"===navigator.platform?/^complete$/:/^(complete|loaded)$/,defContextName="_",isOpera="undefined"!=typeof opera&&"[object Opera]"===opera.toString(),contexts={},cfg={},globalDefQueue=[],useInteractive=!1;if("undefined"==typeof define){if("undefined"!=typeof requirejs){if(isFunction(requirejs))return;cfg=requirejs,requirejs=void 0}"undefined"==typeof require||isFunction(require)||(cfg=require,require=void 0),req=requirejs=function(deps,callback,errback,optional){var context,config,contextName=defContextName;return isArray(deps)||"string"==typeof deps||(config=deps,isArray(callback)?(deps=callback,callback=errback,errback=optional):deps=[]),config&&config.context&&(contextName=config.context),context=getOwn(contexts,contextName),context||(context=contexts[contextName]=req.s.newContext(contextName)),config&&context.configure(config),context.require(deps,callback,errback)},req.config=function(config){return req(config)},req.nextTick="undefined"!=typeof setTimeout?function(fn){setTimeout(fn,4)}:function(fn){fn()},require||(require=req),req.version=version,req.jsExtRegExp=/^\/|:|\?|\.js$/,req.isBrowser=isBrowser,s=req.s={contexts:contexts,newContext:newContext},req({}),each(["toUrl","undef","defined","specified"],function(prop){req[prop]=function(){var ctx=contexts[defContextName];return ctx.require[prop].apply(ctx,arguments)}}),isBrowser&&(head=s.head=document.getElementsByTagName("head")[0],baseElement=document.getElementsByTagName("base")[0],baseElement&&(head=s.head=baseElement.parentNode)),req.onError=function(err){throw err},req.load=function(context,moduleName,url){var node,config=context&&context.config||{};return isBrowser?(node=config.xhtml?document.createElementNS("http://www.w3.org/1999/xhtml","html:script"):document.createElement("script"),node.type=config.scriptType||"text/javascript",node.charset="utf-8",node.async=!0,node.setAttribute("data-requirecontext",context.contextName),node.setAttribute("data-requiremodule",moduleName),!node.attachEvent||node.attachEvent.toString&&node.attachEvent.toString().indexOf("[native code")<0||isOpera?(node.addEventListener("load",context.onScriptLoad,!1),node.addEventListener("error",context.onScriptError,!1)):(useInteractive=!0,node.attachEvent("onreadystatechange",context.onScriptLoad)),node.src=url,currentlyAddingScript=node,baseElement?head.insertBefore(node,baseElement):head.appendChild(node),currentlyAddingScript=null,node):void(isWebWorker&&(importScripts(url),context.completeLoad(moduleName)))},isBrowser&&eachReverse(scripts(),function(script){return head||(head=script.parentNode),dataMain=script.getAttribute("data-main"),dataMain?(cfg.baseUrl||(src=dataMain.split("/"),mainScript=src.pop(),subPath=src.length?src.join("/")+"/":"./",cfg.baseUrl=subPath,dataMain=mainScript),dataMain=dataMain.replace(jsSuffixRegExp,""),cfg.deps=cfg.deps?cfg.deps.concat(dataMain):[dataMain],!0):void 0}),define=function(name,deps,callback){var node,context;"string"!=typeof name&&(callback=deps,deps=name,name=null),isArray(deps)||(callback=deps,deps=[]),!deps.length&&isFunction(callback)&&callback.length&&(callback.toString().replace(commentRegExp,"").replace(cjsRequireRegExp,function(match,dep){deps.push(dep)}),deps=(1===callback.length?["require"]:["require","exports","module"]).concat(deps)),useInteractive&&(node=currentlyAddingScript||getInteractiveScript(),node&&(name||(name=node.getAttribute("data-requiremodule")),context=contexts[node.getAttribute("data-requirecontext")])),(context?context.defQueue:globalDefQueue).push([name,deps,callback])},define.amd={jQuery:!0},req.exec=function(text){return eval(text)},req(cfg)}}(this),namespace.define||(namespace.define=define,namespace.require=require,namespace.requirejs=requirejs),SimplechartGlobal.define("utils",[],function(){var exports={};Array.prototype.indexOf||(Array.prototype.indexOf=function(obj,start){for(var i=start||0,j=this.length;j>i;i++)if(this[i]===obj)return i;return-1});var nativeForEach=Array.prototype.forEach,nativeKeys=Object.keys,slice=Array.prototype.slice,breaker={},has=function(obj,key){return Object.hasOwnProperty.call(obj,key)},each=(exports.keys=nativeKeys||function(obj){if(obj!==Object(obj))throw new TypeError("Invalid object");var keys=[];for(var key in obj)has(obj,key)&&(keys[keys.length]=key);return keys},exports.each=function(obj,iterator,context){if(null!=obj)if(nativeForEach&&obj.forEach===nativeForEach)obj.forEach(iterator,context);else if(obj.length===+obj.length){for(var i=0,l=obj.length;l>i;i++)if(i in obj&&iterator.call(context,obj[i],i,obj)===breaker)return}else for(var key in obj)if(has(obj,key)&&iterator.call(context,obj[key],key,obj)===breaker)return}),nativeIsArray=(exports.extend=function(obj){return each(slice.call(arguments,1),function(source){for(var prop in source)obj[prop]=source[prop]}),obj},Array.isArray),isArray=nativeIsArray||function(obj){return"[object Array]"==Object.prototype.toString.call(obj)},deparam=exports.deparam=function(params,coerce){var obj={},coerce_types={"true":!0,"false":!1,"null":null};return each(params.replace(/\+/g," ").split("&"),function(v){var val,param=v.split("="),key=decodeURIComponent(param[0]),cur=obj,i=0,keys=key.split("]["),keys_last=keys.length-1;if(/\[/.test(keys[0])&&/\]$/.test(keys[keys_last])?(keys[keys_last]=keys[keys_last].replace(/\]$/,""),keys=keys.shift().split("[").concat(keys),keys_last=keys.length-1):keys_last=0,2===param.length)if(val=decodeURIComponent(param[1]),coerce&&(val=val&&!isNaN(val)?+val:"undefined"===val?void 0:void 0!==coerce_types[val]?coerce_types[val]:val),keys_last)for(;keys_last>=i;i++)key=""===keys[i]?cur.length:keys[i],cur=cur[key]=keys_last>i?cur[key]||(keys[i+1]&&isNaN(keys[i+1])?{}:[]):val;else isArray(obj[key])?obj[key].push(val):obj[key]=void 0!==obj[key]?[obj[key],val]:val;else key&&(obj[key]=coerce?void 0:"")}),obj},makeUniqueId=(exports.parseQueryString=function(url){var a=document.createElement("a");a.href=url;var str=a.search.replace(/\?/,"");return deparam(str,!0)},exports.makeUniqueId=function(){for(var text="silp-",possible="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",length=5,i=0;length>i;i++)text+=possible.charAt(Math.floor(Math.random()*possible.length));return document.getElementById(text)?makeUniqueId():text}),getElementsByClassName=exports.getElementsByClassName=function(className,tag,elm){return(getElementsByClassName=document.getElementsByClassName?function(className,tag,elm){elm=elm||document;for(var current,elements=elm.getElementsByClassName(className),nodeName=tag?new RegExp("\\b"+tag+"\\b","i"):null,returnElements=[],i=0,il=elements.length;il>i;i+=1)current=elements[i],(!nodeName||nodeName.test(current.nodeName))&&returnElements.push(current);return returnElements}:document.evaluate?function(className,tag,elm){tag=tag||"*",elm=elm||document;for(var elements,node,classes=className.split(" "),classesToCheck="",xhtmlNamespace="http://www.w3.org/1999/xhtml",namespaceResolver=document.documentElement.namespaceURI===xhtmlNamespace?xhtmlNamespace:null,returnElements=[],j=0,jl=classes.length;jl>j;j+=1)classesToCheck+="[contains(concat(' ', @class, ' '), ' "+classes[j]+" ')]";try{elements=document.evaluate(".//"+tag+classesToCheck,elm,namespaceResolver,0,null)}catch(e){elements=document.evaluate(".//"+tag+classesToCheck,elm,null,0,null)}for(;node=elements.iterateNext();)returnElements.push(node);return returnElements}:function(className,tag,elm){tag=tag||"*",elm=elm||document;for(var current,match,classes=className.split(" "),classesToCheck=[],elements="*"===tag&&elm.all?elm.all:elm.getElementsByTagName(tag),returnElements=[],k=0,kl=classes.length;kl>k;k+=1)classesToCheck.push(new RegExp("(^|\\s)"+classes[k]+"(\\s|$)"));for(var l=0,ll=elements.length;ll>l;l+=1){current=elements[l],match=!1;for(var m=0,ml=classesToCheck.length;ml>m&&(match=classesToCheck[m].test(current.className),match);m+=1);match&&returnElements.push(current)}return returnElements})(className,tag,elm)},isMobile=exports.isMobile={Android:function(){return navigator.userAgent.match(/Android/i)},BlackBerry:function(){return navigator.userAgent.match(/BlackBerry/i)},iOS:function(){return navigator.userAgent.match(/iPhone|iPad|iPod/i)},Opera:function(){return navigator.userAgent.match(/Opera Mini/i)},Windows:function(){return navigator.userAgent.match(/IEMobile/i)},any:function(){return console.log(navigator.userAgent),isMobile.Android()||isMobile.BlackBerry()||isMobile.iOS()||isMobile.Opera()||isMobile.Windows()}};return exports}),SimplechartGlobal.define("namespace",[],function(){return namespace}),SimplechartGlobal.define("loader.div",["utils","namespace"],function(utils,namespace){var EMBED_DIV_CLASSNAME="simplechart-embed";namespace.foundEls||(namespace.foundEls=[]);var foundEls=namespace.foundEls;return function(loadOne){for(var els=utils.getElementsByClassName(EMBED_DIV_CLASSNAME),nEls=els.length,i=0;nEls>i;i++){var el=els[i],params=el.getAttribute("data")||"",paramsEmbed=utils.deparam(params);if(foundEls.indexOf(el)<0){foundEls.push(el);var id=el.id=utils.makeUniqueId();utils.extend(paramsEmbed,{el:el,element:el,element_id:id}),loadOne(paramsEmbed)}}}}),SimplechartGlobal.define("loader.script",["utils","namespace"],function(utils,namespace){namespace.foundEls||(namespace.foundEls=[]);var foundEls=namespace.foundEls,re=/.*widget\/loader\.([^/]+\.)?js/;return function(loadOne){for(var els=document.getElementsByTagName("script"),nEls=els.length,i=0;nEls>i;i++){var el=els[i];if(el.src.match(re)){{var paramsEmbed=utils.parseQueryString(el.src);utils.keys(paramsEmbed).length}if(foundEls.indexOf(el)<0){foundEls.push(el),utils.extend(paramsEmbed,{element:el,element_id:utils.makeUniqueId()});var div=document.createElement("div"),script_tag=paramsEmbed.element;div.id=paramsEmbed.element_id,script_tag.parentNode.insertBefore(div,script_tag),paramsEmbed.el=div,loadOne(paramsEmbed)}}}}});var QUERYSTRING_PREFIX="widget_",defaultParameters={base_url:config.baseUrl};SimplechartGlobal.require(["utils","loader.div","loader.script"],function(utils,loadDivEmbeds,loadScriptEmbeds){var paramsQueryString={},document_params=utils.parseQueryString(document.location)||{};utils.each(document_params,function(val,key){key.indexOf(QUERYSTRING_PREFIX)>=0&&(key=key.slice(QUERYSTRING_PREFIX.length),paramsQueryString[key]=val)});var loadOne=function(paramsEmbed){var parameters=utils.extend({},defaultParameters,paramsEmbed,paramsQueryString);parameters.base_url!==defaultParameters.base_url&&(exports.define=namespace.define,exports.require=namespace.require,exports.requirejs=namespace.requirejs),namespace.require.config({baseUrl:parameters.base_url+"/js"});var isMobile=utils.isMobile.any();if(isMobile&&parameters.element.dataset.image){var chart=JSON.parse(parameters.element.dataset.chart),simplechartWrapper=document.createElement("div");simplechartWrapper.className="simplechart-chart";var img=document.createElement("img");img.src=parameters.element.dataset.image;var title=document.createElement("h2");title.appendChild(document.createTextNode(decodeURIComponent(chart.meta.title)));var subtitle=document.createElement("h4");subtitle.appendChild(document.createTextNode(decodeURIComponent(chart.meta.subtitle)));var caption=document.createElement("p");caption.appendChild(document.createTextNode(decodeURIComponent(chart.meta.caption)));var attribution=document.createElement("h6");attribution.appendChild(document.createTextNode(decodeURIComponent(chart.meta.attribution))),simplechartWrapper.appendChild(title),simplechartWrapper.appendChild(subtitle),simplechartWrapper.appendChild(img),simplechartWrapper.appendChild(caption),simplechartWrapper.appendChild(attribution),parameters.el.appendChild(simplechartWrapper)}else if(isMobile){var text=document.createElement("h4");text.textContent="Charts are not supported on your device.",parameters.el.appendChild(text)}else namespace.require(["app"],function(App){new App(parameters)})};loadScriptEmbeds(loadOne),loadDivEmbeds(loadOne)}),SimplechartGlobal.define("main",function(){});
}());