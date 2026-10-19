(function(wp){
'use strict';const el=wp.element.createElement,{useBlockProps,MediaUpload,MediaUploadCheck}=wp.blockEditor,{TextControl,TextareaControl,SelectControl,Button,RangeControl}=wp.components;
const attrs={id:{type:'number',default:0},title:{type:'string',default:''},summary:{type:'string',default:''},image:{type:'number',default:0},url:{type:'string',default:''},badge:{type:'string',default:''},ids:{type:'array',default:[]},rows:{type:'array',default:[]},category:{type:'number',default:0},count:{type:'number',default:9}};
for(const [name,title]of Object.entries({'product-card':'Product Card',specifications:'Specifications',products:'Product Collection','product-comparison':'Product Comparison','product-details':'Product Details / CTA'}))wp.blocks.registerBlockType('piratez-editorial/'+name,{
 apiVersion:3,title:'PirateZ '+title,icon:'products',category:'widgets',attributes:attrs,usesContext:['postId'],
 edit({attributes:a,setAttributes,context}){
  const records=wp.data.useSelect(select=>window.pzEditorialCatalogue?select('core').getEntityRecords('postType','piratez_product',{per_page:100,status:'publish'}):null,[])||[];
  const terms=wp.data.useSelect(select=>window.pzEditorialCatalogue?select('core').getEntityRecords('taxonomy','piratez_product_category',{per_page:100}):null,[])||[];
  const options=[{label:name==='product-card'?'Manual product':'Current product',value:0},...records.map(p=>({label:wp.htmlEntities?wp.htmlEntities.decodeEntities(p.title.rendered):p.title.rendered.replace(/<[^>]*>/g,''),value:p.id}))];
  const fields=[];
  if(['product-card','specifications','product-details'].includes(name))fields.push(el(SelectControl,{key:'id',label:'Catalogue product (optional)',value:a.id,options,onChange:id=>setAttributes({id:Number(id)})}));
  if(name==='product-card'&&!a.id){for(const [key,label]of Object.entries({title:'Product name',summary:'Description',badge:'Badge',url:'Details / contact URL'}))fields.push(el(TextControl,{key,label,value:a[key],onChange:value=>setAttributes({[key]:value})}));fields.push(el(MediaUploadCheck,{key:'image'},el(MediaUpload,{allowedTypes:['image'],onSelect:image=>setAttributes({image:image.id}),render:({open})=>el(Button,{onClick:open,variant:'secondary'},'Choose image')})));}
  if(name==='specifications'){a.rows.forEach((row,i)=>fields.push(el('div',{key:i},el(TextControl,{label:'Label '+(i+1),value:row.label,onChange:label=>setAttributes({rows:a.rows.map((r,j)=>j===i?{...r,label}:r)})}),el(TextControl,{label:'Value '+(i+1),value:row.value,onChange:value=>setAttributes({rows:a.rows.map((r,j)=>j===i?{...r,value}:r)})}),el(Button,{isDestructive:true,onClick:()=>setAttributes({rows:a.rows.filter((_,j)=>j!==i)})},'Remove row'))));fields.push(el(Button,{key:'add',variant:'secondary',onClick:()=>setAttributes({rows:[...a.rows,{label:'',value:''}]})},'Add specification'));}
  if(name==='products')fields.push(el(SelectControl,{key:'cat',label:'Category filter',value:a.category,options:[{label:'All categories',value:0},...terms.map(t=>({label:t.name,value:t.id}))],onChange:category=>setAttributes({category:Number(category)})}),el(RangeControl,{key:'count',label:'Products per page',value:a.count,min:1,max:24,onChange:count=>setAttributes({count})}));
  if(name==='product-comparison')fields.push(el(SelectControl,{key:'ids',label:'Compare products (up to 6)',multiple:true,value:a.ids.map(String),options:options.slice(1),onChange:ids=>setAttributes({ids:ids.slice(0,6).map(Number)})}));
  return el('div',useBlockProps({className:'pz-product-editor'}),el('h3',null,title),...fields,el(wp.serverSideRender,{block:'piratez-editorial/'+name,attributes:a,urlQueryArgs:{post_id:context.postId}}));
 },save(){return null;}
});
})(window.wp);
