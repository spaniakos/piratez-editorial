(function(wp){
'use strict';
const el=wp.element.createElement,{registerBlockType}=wp.blocks,{InnerBlocks,RichText,InspectorControls,useBlockProps}=wp.blockEditor,{PanelBody,SelectControl}=wp.components;
registerBlockType('piratez-editorial/callout',{
 apiVersion:3,title:'PirateZ Callout',icon:'format-aside',category:'text',attributes:{kind:{type:'string',default:'note'},title:{type:'string',default:'Note'}},supports:{html:false},
 edit({attributes:a,setAttributes}){return el(wp.element.Fragment,null,el(InspectorControls,null,el(PanelBody,{title:'Callout'},el(SelectControl,{label:'Type',value:a.kind,options:['note','take','warning','hard-truth'].map(v=>({label:v,value:v})),onChange:kind=>setAttributes({kind})}))),el('aside',useBlockProps({className:'piratez-callout piratez-'+a.kind}),el(RichText,{tagName:'p',className:'piratez-callout-title',value:a.title,onChange:title=>setAttributes({title}),allowedFormats:['core/bold','core/italic']}),el(InnerBlocks,{template:[['core/paragraph']]})));},
 save({attributes:a}){return el('aside',useBlockProps.save({className:'piratez-callout piratez-'+a.kind,role:'doc-tip'}),el(RichText.Content,{tagName:'p',className:'piratez-callout-title',value:a.title}),el('div',{className:'piratez-callout-content'},el(InnerBlocks.Content)));}
});
registerBlockType('piratez-editorial/code',{
 apiVersion:3,title:'PirateZ Code',icon:'editor-code',category:'text',attributes:{content:{type:'string',source:'text',selector:'code'},language:{type:'string',default:'plain'},filename:{type:'string',default:''}},supports:{html:false},
 edit({attributes:a,setAttributes}){return el('div',useBlockProps(),el(wp.components.TextControl,{label:'Filename (optional)',value:a.filename,onChange:filename=>setAttributes({filename})}),el(SelectControl,{label:'Language',value:a.language,options:['plain','bash','javascript','php','python','css','html','json','sql'].map(v=>({label:v,value:v})),onChange:language=>setAttributes({language})}),el(wp.components.TextareaControl,{label:'Code',value:a.content||'',onChange:content=>setAttributes({content}),rows:8}));},
 save({attributes:a}){return el('figure',useBlockProps.save({className:'pz-code'}),a.filename&&el('figcaption',null,a.filename),el('pre',null,el('code',{className:'language-'+a.language},a.content)));}
});
})(window.wp);
