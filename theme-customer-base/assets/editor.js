(function (wp) {
  for (const name of ['site-header','site-footer']) {
    const full = 'foundations/' + name;
    if (wp.blocks.getBlockType(full)) continue;
    wp.blocks.registerBlockType(full, {
      apiVersion: 3, title: name === 'site-header' ? 'Site header' : 'Site footer', category: 'theme',
      supports: {inserter:false,html:false,reusable:false},
      attributes: {variant:{type:'string',default:name==='site-header'?'inline':'columns'},[name==='site-header'?'showCta':'showContact']:{type:'boolean',default:true}},
      edit: function (props) { return wp.element.createElement('div',wp.blockEditor.useBlockProps(),wp.element.createElement(wp.serverSideRender.default || wp.serverSideRender,{block:full,attributes:props.attributes})); },
      save: function () { return null; }
    });
  }
})(window.wp);
