const { Client } = require('pg');
const bcrypt = require('bcryptjs');
require('dotenv').config();

const client = new Client({
  connectionString: process.env.DATABASE_URL,
  ssl: {
    rejectUnauthorized: false, // Needed for most cloud DBs, including Render
  }
});

async function seed() {
  try {
    await client.connect();
    console.log('✓ Connected to database...\n');
    
    // 1. Seed Roles
    console.log('📋 Seeding Roles...');
    const roles = ['superadmin', 'user', 'organizationadmin', 'dolphinadmin', 'salesperson'];
    for (const roleName of roles) {
      await client.query(`
        INSERT INTO roles (name, created_at, updated_at) 
        VALUES ($1, NOW(), NOW())
        ON CONFLICT (name) DO NOTHING;
      `, [roleName]);
    }
    const rolesCount = await client.query(`SELECT COUNT(*) as count FROM roles;`);
    console.log(`✓ Roles: ${rolesCount.rows[0].count} in database\n`);
    
    // 2. Seed Member Roles
    console.log('👥 Seeding Member Roles...');
    const memberRoles = ['Manager', 'CEO', 'Owner', 'Support'];
    for (const roleName of memberRoles) {
      await client.query(`
        INSERT INTO member_roles (name, created_at, updated_at) 
        VALUES ($1, NOW(), NOW())
        ON CONFLICT (name) DO NOTHING;
      `, [roleName]);
    }
    const memberRolesCount = await client.query(`SELECT COUNT(*) as count FROM member_roles;`);
    console.log(`✓ Member Roles: ${memberRolesCount.rows[0].count} in database\n`);
    
    // 3. Seed Organization Assessment Questions
    console.log('📝 Seeding Organization Assessment Questions...');
    const orgQuestions = [
      'What is your current role in the organization?',
      'How long have you been a member of the staff?',
      'What are your main responsibilities?',
      'What challenges do you face in your daily work?',
      'What support or resources would help you perform better?',
    ];
    for (const question of orgQuestions) {
      // Check if question already exists
      const exists = await client.query(`
        SELECT id FROM organization_assessment_questions WHERE text = $1 LIMIT 1;
      `, [question]);
      
      if (exists.rows.length === 0) {
        await client.query(`
          INSERT INTO organization_assessment_questions (text, created_at, updated_at) 
          VALUES ($1, NOW(), NOW());
        `, [question]);
      }
    }
    const questionsCount = await client.query(`SELECT COUNT(*) as count FROM organization_assessment_questions;`);
    console.log(`✓ Organization Assessment Questions: ${questionsCount.rows[0].count} in database\n`);
    
    // 4. Check Users (Laravel will handle user creation with proper auth)
    console.log('👤 Checking Users...');
    const usersCount = await client.query(`SELECT COUNT(*) as count FROM users;`);
    console.log(`✓ Current Users: ${usersCount.rows[0].count} in database`);
    console.log('ℹ️  Note: Use Laravel seeders for user creation (php artisan db:seed)\n');
    
    // 6. Seed Assessment Questions
    console.log('❓ Seeding Assessment Questions...');
    const options = [
      'Relaxed', 'Persuasive', 'Stable', 'Charismatic', 'Individualistic', 'Optimistic',
      'Conforming', 'Methodical', 'Serious', 'Friendly', 'Humble', 'Unrestrained',
      'Competitive', 'Docile', 'Restless',
    ];
    
    const questions = [
      'Q.1 Please select the words below that describe how other people expect you to be in most daily situations.',
      'Q.2 Please select the words below that describe how you really are, not how you are expected to be.',
    ];
    
    for (const question of questions) {
      // Check if question already exists
      const exists = await client.query(`
        SELECT id FROM questions WHERE question = $1 LIMIT 1;
      `, [question]);
      
      if (exists.rows.length === 0) {
        await client.query(`
          INSERT INTO questions (question, options, created_at, updated_at) 
          VALUES ($1, $2, NOW(), NOW());
        `, [question, JSON.stringify(options)]);
      }
    }
    const assessmentQuestionsCount = await client.query(`SELECT COUNT(*) as count FROM questions;`);
    console.log(`✓ Assessment Questions: ${assessmentQuestionsCount.rows[0].count} in database\n`);
    
    // 7. Check Organizations (optional seeding)
    console.log('🏢 Checking Organizations...');
    const orgCount = await client.query(`SELECT COUNT(*) as count FROM organizations;`);
    console.log(`✓ Organizations: ${orgCount.rows[0].count} in database\n`);
    
    // 8. Check Leads (optional seeding)
    console.log('📊 Checking Leads...');
    const leadsCount = await client.query(`SELECT COUNT(*) as count FROM leads;`);
    console.log(`✓ Leads: ${leadsCount.rows[0].count} in database\n`);
    
    await client.end();
    
    console.log('═══════════════════════════════════════════════');
    console.log('✅ SEEDING COMPLETE!');
    console.log('═══════════════════════════════════════════════');
    console.log('\nDatabase seeded successfully with:');
    console.log(`  • ${rolesCount.rows[0].count} Roles`);
    console.log(`  • ${memberRolesCount.rows[0].count} Member Roles`);
    console.log(`  • ${questionsCount.rows[0].count} Organization Assessment Questions`);
    console.log(`  • ${usersCount.rows[0].count} Users`);
    console.log(`  • ${assessmentQuestionsCount.rows[0].count} Assessment Questions`);
    console.log(`  • ${orgCount.rows[0].count} Organizations`);
    console.log(`  • ${leadsCount.rows[0].count} Leads`);
    console.log('\n📚 For complete seeding (users, organizations, etc):');
    console.log('   cd Dolphin_Backend && php artisan db:seed');
    console.log('\n👤 Create Super Admin:');
    console.log('   Email: sdolphin632@gmail.com');
    console.log('   Password: superadmin@123');
    
  } catch (err) {
    console.error('\n❌ Seeding failed:', err.message);
    console.error('\nFull error:', err);
    await client.end();
    process.exit(1);
  }
}

seed();