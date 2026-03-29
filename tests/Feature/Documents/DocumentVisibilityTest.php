<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Category;
use App\Models\Document;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DocumentVisibilityTest extends TestCase
{
    use RefreshDatabase;

/*
 *
 * refer to "schtutz modell.ppt" to understand the test scenarios
 *
 */
 
    protected User $u1ap, $u2a, $u3ab, $u4b, $u5;
    protected Team $ta, $tb;
    protected Category $c1, $c2, $c3, $c4, $c5, $ca1, $ca2, $cb;
    protected Document $d1, $d2, $d3, $d4, $d5, $da1, $da2, $db;
    protected Item $i1, $i2, $i3, $i4, $i5, $ia1, $ia2, $ib;

    protected function setUp(): void
    {
        parent::setUp();
        
        // users
        $this->u1ap = User::factory()->create();
        $this->u2a  = User::factory()->create();
        $this->u3ab = User::factory()->create();
        $this->u4b  = User::factory()->create();
        $this->u5   = User::factory()->create();

        // teams
        $this->ta = Team::factory()->create(['user_id' => $this->u2a->id, 'has_common_financials' => 1,]);
        $this->tb = Team::factory()->create(['user_id' => $this->u4b->id, 'has_common_financials' => 1,]);
        
        // memberships
        TeamUser::factory()->create(['team_id' => $this->ta->id, 'user_id' => $this->u1ap->id, 'reveal_private' => 1]);
        TeamUser::factory()->create(['team_id' => $this->ta->id, 'user_id' => $this->u2a->id]);
        TeamUser::factory()->create(['team_id' => $this->ta->id, 'user_id' => $this->u3ab->id]);
        TeamUser::factory()->create(['team_id' => $this->tb->id, 'user_id' => $this->u3ab->id]);
        TeamUser::factory()->create(['team_id' => $this->tb->id, 'user_id' => $this->u4b->id]);
        
        // categories
        $this->c1  = Category::factory()->create(['user_id' => $this->u1ap->id,]);
        $this->c2  = Category::factory()->create(['user_id' => $this->u2a->id,]);
        $this->c3  = Category::factory()->create(['user_id' => $this->u3ab->id,]);
        $this->c4  = Category::factory()->create(['user_id' => $this->u4b->id,]);
        $this->c5  = Category::factory()->create(['user_id' => $this->u5->id,]);
        $this->ca1 = Category::factory()->create(['user_id' => $this->u2a->id, 'team_id' => $this->ta->id,]);
        $this->ca2 = Category::factory()->create(['user_id' => $this->u2a->id, 'team_id' => $this->ta->id,]);
        $this->cb  = Category::factory()->create(['user_id' => $this->u4b->id, 'team_id' => $this->tb->id,]);
        
        // documents and items
        $this->d1 = Document::factory()->create(['user_id' => $this->u1ap->id,]);
        $this->i1 = Item::factory()->create(['document_id' => $this->d1->id, 'category_id' => $this->c1->id,]);

        $this->d2 = Document::factory()->create(['user_id' => $this->u2a->id,]);
        $this->i2 = Item::factory()->create(['document_id' => $this->d2->id, 'category_id' => $this->c2->id,]);

        $this->d3 = Document::factory()->create(['user_id' => $this->u3ab->id,]);
        $this->i3 = Item::factory()->create(['document_id' => $this->d3->id, 'category_id' => $this->c3->id,]);

        $this->d4 = Document::factory()->create(['user_id' => $this->u4b->id,]);
        $this->i4 = Item::factory()->create(['document_id' => $this->d4->id, 'category_id' => $this->c4->id,]);

        $this->d5 = Document::factory()->create(['user_id' => $this->u5->id,]);
        $this->i5 = Item::factory()->create(['document_id' => $this->d5->id, 'category_id' => $this->c5->id,]);

        $this->da1 = Document::factory()->create(['user_id' => $this->u1ap->id,]);
        $this->ia1 = Item::factory()->create(['document_id' => $this->da1->id, 'category_id' => $this->ca1->id,]);
        $this->ia1p = Item::factory()->create(['document_id' => $this->da1->id, 'category_id' => $this->c1->id,]);

        $this->da2 = Document::factory()->create(['user_id' => $this->u2a->id,]);
        $this->ia2 = Item::factory()->create(['document_id' => $this->da2->id, 'category_id' => $this->ca2->id,]);
        $this->ia2p = Item::factory()->create(['document_id' => $this->da2->id, 'category_id' => $this->c2->id,]);

        $this->db = Document::factory()->create(['user_id' => $this->u3ab->id,]);
        $this->ib = Item::factory()->create(['document_id' => $this->db->id, 'category_id' => $this->cb->id,]);
        $this->ibp = Item::factory()->create(['document_id' => $this->db->id, 'category_id' => $this->c3->id,]);
    }

    #[test]
    public function user_5_sees_own_document()
    {
        $this->actingAs($this->u5);
        
        $response = $this->get("/documents");

        $response->assertOk();

        // can see and edit own document and item
        $response->assertSee("edit-document-{$this->d5->id}");
        $response->assertSee("edit-item-{$this->i5->id}");

        // does NOT see any other documents
        foreach ([$this->d1, $this->d2, $this->d3, $this->d4, $this->da1, $this->da2, $this->db] as $doc) {
            $response->assertDontSee("edit-document-{$doc->id}");
            $response->assertDontSee("view-document-{$doc->id}");
        }

        // does NOT see any other items
        foreach ([$this->i1, $this->i2, $this->i3, $this->i4, $this->ia1, $this->ia2, $this->ib, $this->ia1p, $this->ia2p, $this->ibp] as $item) {
            $response->assertDontSee("edit-item-{$item->id}");
            $response->assertDontSee("view-item-{$item->id}");
        }
        
        // count documents and items
        $html = $response->getContent();

        $editDocumentCount = substr_count($html, 'edit-document-');
        $this->assertSame(1, $editDocumentCount);

        $editItemCount = substr_count($html, 'edit-item-');
        $this->assertSame(1, $editItemCount);

        $viewDocumentCount = substr_count($html, 'view-document-');
        $this->assertSame(0, $viewDocumentCount);

        $viewItemCount = substr_count($html, 'view-item-');
        $this->assertSame(0, $viewItemCount);
    }

    #[test]
    public function user_4_sees_own_document_and_team_items()
    {
        $this->actingAs($this->u4b);
        
        $response = $this->get("/documents");

        $response->assertOk();

        // can see and edit own document and item
        $response->assertSee("edit-document-{$this->d4->id}");
        $response->assertSee("edit-item-{$this->i4->id}");

        // can see team document and team item
        $response->assertSee("view-document-{$this->db->id}");
        $response->assertSee("view-item-{$this->ib->id}");

        // can NOT see private item in team document 
        $response->assertDontSee("edit-item-{$this->ibp->id}");
        $response->assertDontSee("view-item-{$this->ibp->id}");
        
        // does NOT see any other documents
        foreach ([$this->d1, $this->d2, $this->d3, $this->d5, $this->da1, $this->da2] as $doc) {
            $response->assertDontSee("edit-document-{$doc->id}");
            $response->assertDontSee("view-document-{$doc->id}");
        }

        // does NOT see any other items
        foreach ([$this->i1, $this->i2, $this->i3, $this->i5, $this->ia1, $this->ia2, $this->ia1p, $this->ia2p] as $item) {
            $response->assertDontSee("edit-item-{$item->id}");
            $response->assertDontSee("view-item-{$item->id}");
        }
        
        // count documents and items
        $html = $response->getContent();

        $editDocumentCount = substr_count($html, 'edit-document-');
        $this->assertSame(1, $editDocumentCount);

        $editItemCount = substr_count($html, 'edit-item-');
        $this->assertSame(1, $editItemCount);

        $viewDocumentCount = substr_count($html, 'view-document-');
        $this->assertSame(1, $viewDocumentCount);

        $viewItemCount = substr_count($html, 'view-item-');
        $this->assertSame(1, $viewItemCount);
    }

    #[test]
    public function user_1_sees_own_documents_and_team_items()
    {
        $this->actingAs($this->u1ap);
        
        $response = $this->get("/documents");

        $response->assertOk();

        // can see and edit own document and item
        $response->assertSee("edit-document-{$this->d1->id}");
        $response->assertSee("edit-item-{$this->i1->id}");
        
        $response->assertSee("edit-document-{$this->da1->id}");
        $response->assertSee("edit-item-{$this->ia1->id}");
        $response->assertSee("edit-item-{$this->ia1p->id}");

        // can see team document and team item
        $response->assertSee("view-document-{$this->da2->id}");
        $response->assertSee("view-item-{$this->ia2->id}");

        // can NOT see private item in team document 
        $response->assertDontSee("edit-item-{$this->ia2p->id}");
        $response->assertDontSee("view-item-{$this->ia2p->id}");
        
        // does NOT see any other documents
        foreach ([$this->d2, $this->d3, $this->d4, $this->d5, $this->db] as $doc) {
            $response->assertDontSee("edit-document-{$doc->id}");
            $response->assertDontSee("view-document-{$doc->id}");
        }

        // does NOT see any other items
        foreach ([$this->i2, $this->i3, $this->i4, $this->i5, $this->ib, $this->ibp] as $item) {
            $response->assertDontSee("edit-item-{$item->id}");
            $response->assertDontSee("view-item-{$item->id}");
        }
        
        // count documents and items
        $html = $response->getContent();

        $editDocumentCount = substr_count($html, 'edit-document-');
        $this->assertSame(2, $editDocumentCount);

        $editItemCount = substr_count($html, 'edit-item-');
        $this->assertSame(3, $editItemCount);

        $viewDocumentCount = substr_count($html, 'view-document-');
        $this->assertSame(1, $viewDocumentCount);

        $viewItemCount = substr_count($html, 'view-item-');
        $this->assertSame(1, $viewItemCount);
    }

    #[test]
    public function user_2_sees_own_documents_and_team_items_and_revealed_items()
    {
        $this->actingAs($this->u2a);
        
        $response = $this->get("/documents");

        $response->assertOk();

        // can see and edit own document and item
        $response->assertSee("edit-document-{$this->d2->id}");
        $response->assertSee("edit-item-{$this->i2->id}");
        
        $response->assertSee("edit-document-{$this->da2->id}");
        $response->assertSee("edit-item-{$this->ia2->id}");
        $response->assertSee("edit-item-{$this->ia2p->id}");

        // can see and edit user 1's document and item
        $response->assertSee("edit-document-{$this->d1->id}");
        $response->assertSee("edit-item-{$this->i1->id}");
        
        $response->assertSee("edit-document-{$this->da1->id}");
        $response->assertSee("edit-item-{$this->ia1->id}");
        $response->assertSee("edit-item-{$this->ia1p->id}");

        // does NOT see any other documents
        foreach ([$this->d3, $this->d4, $this->d5, $this->db] as $doc) {
            $response->assertDontSee("edit-document-{$doc->id}");
            $response->assertDontSee("view-document-{$doc->id}");
        }

        // does NOT see any other items
        foreach ([$this->i3, $this->i4, $this->i5, $this->ib, $this->ibp] as $item) {
            $response->assertDontSee("edit-item-{$item->id}");
            $response->assertDontSee("view-item-{$item->id}");
        }
        
        // count documents and items
        $html = $response->getContent();

        $editDocumentCount = substr_count($html, 'edit-document-');
        $this->assertSame(4, $editDocumentCount);

        $editItemCount = substr_count($html, 'edit-item-');
        $this->assertSame(6, $editItemCount);

        $viewDocumentCount = substr_count($html, 'view-document-');
        $this->assertSame(0, $viewDocumentCount);

        $viewItemCount = substr_count($html, 'view-item-');
        $this->assertSame(0, $viewItemCount);
    }

    #[test]
    public function user_3_sees_own_documents_and_both_team_items_and_revealed_items()
    {
        $this->actingAs($this->u3ab);
        
        $response = $this->get("/documents");

        $response->assertOk();

        // can see and edit own document and item
        $response->assertSee("edit-document-{$this->d3->id}");
        $response->assertSee("edit-item-{$this->i3->id}");
        
        $response->assertSee("edit-document-{$this->db->id}");
        $response->assertSee("edit-item-{$this->ib->id}");
        $response->assertSee("edit-item-{$this->ibp->id}");

        // can see and edit user 1's document and item
        $response->assertSee("edit-document-{$this->d1->id}");
        $response->assertSee("edit-item-{$this->i1->id}");
        
        $response->assertSee("edit-document-{$this->da1->id}");
        $response->assertSee("edit-item-{$this->ia1->id}");
        $response->assertSee("edit-item-{$this->ia1p->id}");

        // can see user 2's document and team item
        $response->assertSee("view-document-{$this->da2->id}");
        $response->assertSee("view-item-{$this->ia2->id}");

        // does NOT see any other documents
        foreach ([$this->d2, $this->d4, $this->d5] as $doc) {
            $response->assertDontSee("edit-document-{$doc->id}");
            $response->assertDontSee("view-document-{$doc->id}");
        }

        // does NOT see any other items
        foreach ([$this->i2, $this->i4, $this->i5] as $item) {
            $response->assertDontSee("edit-item-{$item->id}");
            $response->assertDontSee("view-item-{$item->id}");
        }
        
        // count documents and items
        $html = $response->getContent();

        $editDocumentCount = substr_count($html, 'edit-document-');
        $this->assertSame(4, $editDocumentCount);

        $editItemCount = substr_count($html, 'edit-item-');
        $this->assertSame(6, $editItemCount);

        $viewDocumentCount = substr_count($html, 'view-document-');
        $this->assertSame(1, $viewDocumentCount);

        $viewItemCount = substr_count($html, 'view-item-');
        $this->assertSame(1, $viewItemCount);
    }
}
