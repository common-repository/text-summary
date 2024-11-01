jQuery(document).ready(function($){
                $("body").on("click",".auto-ai",function(event){
                    event.preventDefault();
                    $(".text-summary-waiting").show();
                    $("#text-summary").val('Just fetching the AI summary');
                    //classic editor
                    //var textToBeSummarised = $(".wp-editor-area").val();
                    var textToBeSummarised = $(".wp-editor-area").val();
                    if(!textToBeSummarised)
                    {
                        //try gutenberg
                        textToBeSummarised = $(".wp-block-post-content").text();
                    }
                    var args={"action":"text-summary-public","text-to-be-summarised":textToBeSummarised,"nonce":text_summary_nonce}
                    console.log(args);
                    $.ajax( ajaxurl,
                    {
                        type: "POST",
                        data: args,
                        
                        success: function(res) {
                            $(".text-summary-waiting").hide();
                            console.log(res)
                            var summary = res.trim();
                            $("#text-summary").val(summary);
                        }
                    } );

                })
                $("body").on('click',".grab-text",function(event){
                    event.preventDefault();
                

                    var textToBeSummarised = $(".wp-editor-area").val();
                    if(!textToBeSummarised)
                    {
                        //try gutenberg
                        textToBeSummarised = $(".wp-block-post-content").text();
                    }

                    navigator.clipboard.writeText(textToBeSummarised);
                    console.log(textToBeSummarised);

                })
            })