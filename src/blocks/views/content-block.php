<?=View::script('core/gila.min.js')?>
<?=View::script('lib/CodeMirror/codemirror.js')?>
<?=View::script('lib/CodeMirror/javascript.js')?>
<?=View::script('lib/vue/vue.min.js');?>
<?=View::script('blocks/vue-draggable.min.js');?>

<?=View::script("lib/tinymce/tinymce.min.js")?>
<?=View::script('core/admin/vue-components.js');?>
<?=View::script('core/admin/vue-editor.js');?>
<?=View::script('core/admin/media.js')?>
<?=View::script('core/lang/content/'.Config::config('language').'.js')?>

<?=View::css('lib/font-awesome/css/font-awesome.min.css')?>
<?=View::css('blocks/blocks.css')?>
<?=View::cssAsync('core/gila.min.css')?>
<?=View::cssAsync('core/admin/vue-editor.css')?>
<?=View::cssAsync('lib/CodeMirror/codemirror.css')?>

<?php
$cid = $contentType.'_'.$id.'_';
if(isset($title)) {
  echo '<h2>'.htmlentities($title).'</h2>';
  $mainGrid = 'grid-template-columns:1fr 240px';
  $clickToEdit = "";
} else {
  $mainGrid = 'grid-template-columns:1fr 68px';
  $clickToEdit = "@click='block_edit(\"".$cid."\"+pos ,w._type)'";
}
?>

<div style="display:grid; <?=$mainGrid?>">

  <div>
    <iframe src="blocks/display?t=<?=$contentType?>&id=<?=$id?>" id="blocks_preview"
    style="width:100%; height:100vh; border:1px solid lightgrey"></iframe>
  </div>

  <div class="block-container"
  style_="position:absolute; right:0;top:0;bottom:0;width:auto; background:grey; overflow-y:scroll;max-height:70vh">
  <div id="content_blocks_list" style="padding:0" v-drag-and-drop:options="ddoptions">
    <div v-if="draft" style="margin-left:2em">
      <button class='g-btn' @click='block_save()'><?=(isset($title)?__('Save'):'<i class="fa fa-save"></i>')?></button>
      <?=(isset($title)?'&nbsp;':'')?>
      <button class='g-btn-white' @click='block_discard()'><?=(isset($title)?__('Discard Draft'):'<i class="fa fa-trash"></i>')?></button>
      <br>
    </div>
    <div v-for="(w, pos) in blocks" class="block-li" :data-pos="pos">
      <button v-if="pos>0" class='btn-arrows-v' @click='block_pos("<?=$cid?>"+pos, pos-1)'>
        <i class='fa fa-arrows-v'></i>
      </button>
      <div class="block-div" @mouseover="blocks_preview.src='blocks/display?t=<?=$contentType?>&id=<?=$id?>#w'+pos" <?=$clickToEdit?>>
        <div v-html="blockIcon(w._type)" class="first-div"></div>
        <div :class="blockTitleClass(w)" v-if="<?=(isset($title)?'true':'false')?>"> {{blockTitle(w)}}</div>
        <button v-if="<?=(isset($title)?'true':'false')?>" class='btn-edit' @click='block_edit("<?=$cid?>"+pos ,w._type)'>
          <i class='fa fa-pencil'></i>
        </button>
      </div>
    </div>
    <div class="block-li" data-pos="add" >
      <div style="text-align:center;background:green;color:white" class="block-div" @click='block_add(blocks.length)'>
        <i class='fa fa-plus'></i>
      </div>
    </div>
  </div>
  </div>

</div>

<script>
<?php
$content_blocks = [];

foreach (Config::$widget as $k=>$w) {
  $c = ['name'=>$k];
  if (file_exists('src/'.$w.'/logo.png')) {
    $c['logo'] = $w.'/logo.png';
  }
  if (file_exists('src/'.$w.'/logo.svg')) {
    $c['logo'] = $w.'/logo.svg';
  }
  if (file_exists('src/'.$w.'/preview.png')) {
    $c['preview'] = $w.'/preview.png';
  }
  $content_blocks[$k] = $c;
}
?>
content_blocks = <?=json_encode($content_blocks)?>;

function getElementIndex (element) {
  return Array.from(element.parentNode.children).indexOf(element);
}

Vue.use(VueDraggable.default);

content_blocks_app = new Vue({
  el: "#content_blocks_list",
  data: {
    blocks: <?=json_encode($widgets??[])?>,
    ddoptions: {
      dropzoneSelector: '#content_blocks_list',
      draggableSelector: '.block-li',
      onDrop(event) {
        pos = event.items[0].getAttribute('data-pos')
        index = getElementIndex(event.items[0])-1
        if(pos==='add') {
          block_add(index)
          event.items[0].parentNode.appendChild(event.items[0]);
        } else {
          content_blocks_app.blocks = []
          block_pos("<?=$cid?>"+pos, index)
        }
      }
    },
    draft: <?=($isDraft?'true':'false')?>
  },
  methods: {
    blockIcon: function(type) {
      if(typeof content_blocks[type]!=='undefined'
          && typeof content_blocks[type].logo!=='undefined') {
        return '<img src="lzld/thumb?src=src/'+content_blocks[type].logo+'">'
      } else {
        return '<h4>'+type[0].toUpperCase()+'</h4>' 
      }
    },
    block_add: function(pos) {
      blocks_app.add_block = true;
      blocks_app.selected_pos = pos;
    },
    block_edit: function(id,type) {
      block_edit(id,type)
    },
    block_pos: function(id,pos) {
      block_pos(id,pos)
    },
    block_save: function() {
      _this = this
      g.loader()
      g.post('blocks/save', 'id=<?=$contentType.'_'.$id?>', function(data) {
        g.loader(false)
        _this.draft = false;
        g.alert("Saved", "success");
      });
    },
    block_discard: function() {
      _this = this
      g.loader()
      g.post('blocks/discard', 'id=<?=$contentType.'_'.$id?>', function(data) {
        g.loader(false)
        _this.draft = false;
        blocks_preview_reload(data)
      });
    },
    blockTitle: function(w) {
      if(typeof w.text !== 'undefined') {
        var temporalDivElement = document.createElement("div");
        temporalDivElement.innerHTML = w.text;
        return temporalDivElement.textContent || temporalDivElement.innerText || "";
      }
      return w._type
    },
    blockTitleClass: function(w) {
      if(typeof w.text !== 'undefined') {
        return "block-div-title";
      }
      return "block-div-type";
    }
  }
})

</script>

<div id='blocks_app'>
  <div v-if="add_block" id="add_block">
    <img src="assets/core/admin/close.svg" class="add-block-x" @click="add_block=false">
    <div class="add-block-grid centered">
      <div class="add-block-btn" v-for="b in blocks" @click="createBlock('<?=$contentType.'/'.$id?>', b.name, selected_pos)">
        <img v-if="b.preview" :src="'lzld/thumb?media_thumb=300&src=src/'+b.preview" class="preview">
        <div v-else class="logo">
          <img v-if="b.logo" :src="'lzld/thumb?src=src/'+b.logo">
          <h4 v-else>{{b.name[0].toUpperCase()}}</h4>
          <div>{{b.name}}<div>
        </div>
      </div>
    </div>
  </div>
</div>

<?=View::script("blocks/content-block.js")?>
