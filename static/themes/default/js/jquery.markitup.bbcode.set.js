// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
var mySettings = {
	previewParserPath:	'', // path to your BBCode parser
	markupSet: [
		{name:'Bold', key:'B', openWith:'[b]', closeWith:'[/b]'},
		{name:'Italic', key:'I', openWith:'[i]', closeWith:'[/i]'},
		{name:'Underline', key:'U', openWith:'[u]', closeWith:'[/u]'},
		{name:'Strikethrough', openWith:'[s]', closeWith:'[/s]'},
		{name:'Font Size', openWith:'[size=[![Font size]!]]', closeWith:'[/size]',
		dropMenu :[
			{name:'<span style="line-height:16px;font-size:120%">Big</span>', openWith:'[size=120%]', closeWith:'[/size]' },
			{name:'<span style="line-height:16px;font-size:100%">Normal</span>', openWith:'[size=100%]', closeWith:'[/size]' },
			{name:'<span style="line-height:16px;font-size:80%">Small</span>', openWith:'[size=80%]', closeWith:'[/size]' }
		]},
		{	name:'Font Color', 
			className:'colors', 
			openWith:'[color=[![Font Color]!]]', 
			closeWith:'[/color]', 
				dropMenu: [
					{name:'Yellow',	openWith:'[color=yellow]', 	closeWith:'[/color]', className:'col1-1' },
					{name:'Orange',	openWith:'[color=orange]', 	closeWith:'[/color]', className:'col1-2' },
					{name:'Red', 	openWith:'[color=red]', 	closeWith:'[/color]', className:'col1-3' },
					
					{name:'Blue', 	openWith:'[color=blue]', 	closeWith:'[/color]', className:'col2-1' },
					{name:'Purple', openWith:'[color=purple]', 	closeWith:'[/color]', className:'col2-2' },
					{name:'Green', 	openWith:'[color=green]', 	closeWith:'[/color]', className:'col2-3' },
					
					{name:'White', 	openWith:'[color=white]', 	closeWith:'[/color]', className:'col3-1' },
					{name:'Gray', 	openWith:'[color=gray]', 	closeWith:'[/color]', className:'col3-2' },
					{name:'Black',	openWith:'[color=black]', 	closeWith:'[/color]', className:'col3-3' }
				]
		},
		{	name:'Font Background Color', 
			className:'bgcolors', 
			openWith:'[bgcolor=[![Font Background Color]!]]', 
			closeWith:'[/bgcolor]', 
				dropMenu: [
					{name:'Yellow',	openWith:'[bgcolor=yellow]', 	closeWith:'[/bgcolor]', className:'col1-1' },
					{name:'Orange',	openWith:'[bgcolor=orange]', 	closeWith:'[/bgcolor]', className:'col1-2' },
					{name:'Red', 	openWith:'[bgcolor=red]', 	closeWith:'[/bgcolor]', className:'col1-3' },
					
					{name:'Blue', 	openWith:'[bgcolor=blue]', 	closeWith:'[/bgcolor]', className:'col2-1' },
					{name:'Purple', openWith:'[bgcolor=purple]', 	closeWith:'[/bgcolor]', className:'col2-2' },
					{name:'Green', 	openWith:'[bgcolor=green]', 	closeWith:'[/bgcolor]', className:'col2-3' },
					
					{name:'White', 	openWith:'[bgcolor=white]', 	closeWith:'[/bgcolor]', className:'col3-1' },
					{name:'Gray', 	openWith:'[bgcolor=gray]', 	closeWith:'[/bgcolor]', className:'col3-2' },
					{name:'Black',	openWith:'[bgcolor=black]', 	closeWith:'[/bgcolor]', className:'col3-3' }
				]
		},
		{separator:'---------------' },
		{name:'Picture', key:'P', replaceWith:'[img][![Image url]!][/img]'},
		{name:'Link', key:'L', openWith:'[url=[![Link url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
		{separator:'---------------' },
		{name:'Bulleted list', openWith:'[list]\n', closeWith:'\n[/list]'},
		{name:'Numeric list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'}, 
		{name:'List item', openWith:'[*] '},
		{separator:'---------------' },
		{name:'Quotes', openWith:'[quote]', closeWith:'[/quote]'},
		{name:'Code', openWith:'[code]', closeWith:'[/code]'}, 
		{separator:'---------------' },
		{name:'Smiley', className:'smiley', call:'preview'},
		{name:'YouTube Video', className:'youtube', openWith:'[youtube]', closeWith:'[/youtube]'},
		{name:'TuDou Video', className:'tudou', openWith:'[tudou]', closeWith:'[/tudou]'}, 
		{separator:'---------------' },
		{name:'Clean', className:"clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } },
		{name:'Preview', className:"preview", call:'preview' },
		{name:'Help', className:"help", call:'preview' }
	]
};
