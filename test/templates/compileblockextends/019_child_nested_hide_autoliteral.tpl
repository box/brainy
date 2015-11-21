{ extends file="019_parent.tpl" }

{ block name="index" }
   { block name="test2" }
      nested block.
      { $smarty.block.child }
   { /block }
{ /block }
